<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Reminder;
use App\Models\ReminderTemplate;
use App\Models\Tenant;
use App\Notifications\PaymentReminderNotification;
use App\Services\TemplateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    /**
     * Create a payment due reminder for an invoice
     */
    public function createPaymentDueReminder(Invoice $invoice, int $daysBeforeDue = 3): ?Reminder
    {
        // Don't create reminder if invoice is already paid
        if ($invoice->isPaid()) {
            return null;
        }

        // Don't create duplicate reminders
        $existingReminder = Reminder::where('invoice_id', $invoice->id)
            ->where('type', 'payment_due')
            ->where('status', 'pending')
            ->first();

        if ($existingReminder) {
            return $existingReminder;
        }

        $reminderDate = $invoice->due_date->copy()->subDays($daysBeforeDue);

        // Don't create reminder if the date has already passed
        if ($reminderDate->isPast()) {
            return null;
        }

        $tenant = $invoice->tenant;
        
        // Try to use template, fall back to default message
        $template = ReminderTemplate::getActiveForType('payment_due', $daysBeforeDue);
        $templateService = app(TemplateService::class);
        
        if ($template) {
            $data = $templateService->getPaymentTemplateData($invoice);
            $rendered = $templateService->renderTemplate($template, $data);
            $subject = $rendered['subject'];
            $message = $rendered['message'];
        } else {
            $subject = "Payment Reminder: Invoice {$invoice->invoice_number} Due Soon";
            $message = $this->generatePaymentDueMessage($invoice, $daysBeforeDue);
        }

        return Reminder::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'type' => 'payment_due',
            'subject' => $subject,
            'message' => $message,
            'reminder_date' => $reminderDate,
            'status' => 'pending',
            'channel' => 'email',
        ]);
    }

    /**
     * Create an overdue payment reminder for an invoice
     */
    public function createOverdueReminder(Invoice $invoice): ?Reminder
    {
        // Don't create reminder if invoice is already paid
        if ($invoice->isPaid()) {
            return null;
        }

        // Only create for overdue invoices
        if (!$invoice->isOverdue()) {
            return null;
        }

        // Don't create duplicate reminders for today
        $existingReminder = Reminder::where('invoice_id', $invoice->id)
            ->where('type', 'payment_overdue')
            ->where('status', 'pending')
            ->whereDate('reminder_date', today())
            ->first();

        if ($existingReminder) {
            return $existingReminder;
        }

        $tenant = $invoice->tenant;
        $daysOverdue = now()->diffInDays($invoice->due_date);
        
        // Try to use template, fall back to default message
        $template = ReminderTemplate::getActiveForType('payment_overdue');
        $templateService = app(TemplateService::class);
        
        if ($template) {
            $data = $templateService->getPaymentTemplateData($invoice);
            $rendered = $templateService->renderTemplate($template, $data);
            $subject = $rendered['subject'];
            $message = $rendered['message'];
        } else {
            $subject = "URGENT: Overdue Payment - Invoice {$invoice->invoice_number}";
            $message = $this->generateOverdueMessage($invoice, $daysOverdue);
        }

        return Reminder::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'type' => 'payment_overdue',
            'subject' => $subject,
            'message' => $message,
            'reminder_date' => today(),
            'status' => 'pending',
            'channel' => 'email',
        ]);
    }

    /**
     * Create a lease expiry reminder
     */
    public function createLeaseExpiryReminder(Tenant $tenant, int $daysBeforeExpiry = 30): ?Reminder
    {
        if (!$tenant->lease_end_date || $tenant->lease_status !== 'active') {
            return null;
        }

        $reminderDate = Carbon::parse($tenant->lease_end_date)->subDays($daysBeforeExpiry);

        // Don't create reminder if the date has already passed
        if ($reminderDate->isPast()) {
            return null;
        }

        // Don't create duplicate reminders
        $existingReminder = Reminder::where('tenant_id', $tenant->id)
            ->where('type', 'lease_expiry')
            ->where('status', 'pending')
            ->first();

        if ($existingReminder) {
            return $existingReminder;
        }

        // Try to use template, fall back to default message
        $template = ReminderTemplate::getActiveForType('lease_expiry', $daysBeforeExpiry);
        $templateService = app(TemplateService::class);
        
        if ($template) {
            $data = $templateService->getLeaseTemplateData($tenant);
            $rendered = $templateService->renderTemplate($template, $data);
            $subject = $rendered['subject'];
            $message = $rendered['message'];
        } else {
            $subject = "Lease Expiry Reminder - {$tenant->unit->property->name}";
            $message = $this->generateLeaseExpiryMessage($tenant, $daysBeforeExpiry);
        }

        return Reminder::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => null,
            'type' => 'lease_expiry',
            'subject' => $subject,
            'message' => $message,
            'reminder_date' => $reminderDate,
            'status' => 'pending',
            'channel' => 'email',
        ]);
    }

    /**
     * Send pending reminders that are due
     */
    public function sendPendingReminders(): array
    {
        $reminders = Reminder::where('status', 'pending')
            ->whereDate('reminder_date', '<=', today())
            ->with(['tenant.user', 'invoice'])
            ->get();

        $sent = [];
        $failed = [];

        foreach ($reminders as $reminder) {
            try {
                $this->sendReminder($reminder);
                $sent[] = $reminder;
            } catch (\Exception $e) {
                Log::error("Failed to send reminder {$reminder->id}: " . $e->getMessage());
                $failed[] = [
                    'reminder' => $reminder,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Send a single reminder
     */
    public function sendReminder(Reminder $reminder): void
    {
        $tenant = $reminder->tenant;
        $user = $tenant->user;

        if (!$user) {
            throw new \Exception("Tenant user not found for reminder {$reminder->id}");
        }

        // Send notification based on channel
        if (in_array($reminder->channel, ['email', 'both'])) {
            $user->notify(new PaymentReminderNotification($reminder));
        }

        // TODO: Add SMS notification if channel is 'sms' or 'both'
        // This would require an SMS service integration

        // Mark reminder as sent
        $reminder->markAsSent();
    }

    /**
     * Automatically create reminders for due invoices
     */
    public function createRemindersForDueInvoices(int $daysBeforeDue = 3): int
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '>=', today())
            ->where('due_date', '<=', today()->addDays($daysBeforeDue))
            ->with('tenant')
            ->get();

        $created = 0;
        foreach ($invoices as $invoice) {
            $reminder = $this->createPaymentDueReminder($invoice, $daysBeforeDue);
            if ($reminder) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Automatically create reminders for overdue invoices
     */
    public function createRemindersForOverdueInvoices(): int
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '<', today())
            ->with('tenant')
            ->get();

        $created = 0;
        foreach ($invoices as $invoice) {
            // Update invoice status to overdue if needed
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }

            $reminder = $this->createOverdueReminder($invoice);
            if ($reminder) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Automatically create reminders for expiring leases
     */
    public function createRemindersForExpiringLeases(int $daysBeforeExpiry = 30): int
    {
        $tenants = Tenant::where('lease_status', 'active')
            ->whereNotNull('lease_end_date')
            ->where('lease_end_date', '>=', today())
            ->where('lease_end_date', '<=', today()->addDays($daysBeforeExpiry))
            ->with(['user', 'unit.property'])
            ->get();

        $created = 0;
        foreach ($tenants as $tenant) {
            $reminder = $this->createLeaseExpiryReminder($tenant, $daysBeforeExpiry);
            if ($reminder) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Generate payment due message
     */
    protected function generatePaymentDueMessage(Invoice $invoice, int $daysBeforeDue): string
    {
        $tenant = $invoice->tenant;
        $property = $tenant->unit->property;

        return "Dear {$tenant->user->name},\n\n" .
            "This is a friendly reminder that your rent payment is due in {$daysBeforeDue} day(s).\n\n" .
            "Invoice Details:\n" .
            "Invoice Number: {$invoice->invoice_number}\n" .
            "Property: {$property->name}\n" .
            "Unit: {$tenant->unit->unit_number}\n" .
            "Amount Due: ₦" . number_format($invoice->balance, 2) . "\n" .
            "Due Date: {$invoice->due_date->format('F j, Y')}\n\n" .
            "Please ensure payment is made before the due date to avoid any late fees.\n\n" .
            "Thank you for your prompt attention to this matter.\n\n" .
            "Best regards,\n" .
            "Property Management Team";
    }

    /**
     * Generate overdue payment message
     */
    protected function generateOverdueMessage(Invoice $invoice, int $daysOverdue): string
    {
        $tenant = $invoice->tenant;
        $property = $tenant->unit->property;

        return "Dear {$tenant->user->name},\n\n" .
            "URGENT: Your rent payment is now {$daysOverdue} day(s) overdue.\n\n" .
            "Invoice Details:\n" .
            "Invoice Number: {$invoice->invoice_number}\n" .
            "Property: {$property->name}\n" .
            "Unit: {$tenant->unit->unit_number}\n" .
            "Amount Overdue: ₦" . number_format($invoice->balance, 2) . "\n" .
            "Due Date: {$invoice->due_date->format('F j, Y')}\n\n" .
            "Please make payment immediately to avoid further action. Late fees may apply.\n\n" .
            "If you have already made this payment, please contact us immediately.\n\n" .
            "Best regards,\n" .
            "Property Management Team";
    }

    /**
     * Generate lease expiry message
     */
    protected function generateLeaseExpiryMessage(Tenant $tenant, int $daysBeforeExpiry): string
    {
        $property = $tenant->unit->property;

        return "Dear {$tenant->user->name},\n\n" .
            "This is a reminder that your lease agreement will expire in {$daysBeforeExpiry} day(s).\n\n" .
            "Lease Details:\n" .
            "Property: {$property->name}\n" .
            "Unit: {$tenant->unit->unit_number}\n" .
            "Lease End Date: " . Carbon::parse($tenant->lease_end_date)->format('F j, Y') . "\n\n" .
            "Please contact us to discuss lease renewal or move-out procedures.\n\n" .
            "Best regards,\n" .
            "Property Management Team";
    }
}

