<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\ReminderTemplate;
use App\Models\Tenant;
use Carbon\Carbon;

class TemplateService
{
    /**
     * Replace template variables in a string
     */
    public function replaceVariables(string $template, array $data): string
    {
        $replacements = [];
        foreach ($data as $key => $value) {
            $replacements["{{{$key}}}"] = $value;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get template data for payment due/overdue reminders
     */
    public function getPaymentTemplateData(Invoice $invoice): array
    {
        $tenant = $invoice->tenant;
        $user = $tenant->user;
        $unit = $tenant->unit;
        $property = $unit->property;

        $daysUntilDue = max(0, now()->diffInDays($invoice->due_date, false));
        $daysOverdue = max(0, now()->diffInDays($invoice->due_date));

        return [
            'tenant_name' => $user->name ?? 'Tenant',
            'tenant_email' => $user->email ?? '',
            'property_name' => $property->name ?? 'Property',
            'unit_number' => $unit->unit_number ?? '',
            'property_address' => $property->address ?? '',
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date->format('F j, Y'),
            'due_date' => $invoice->due_date->format('F j, Y'),
            'amount_due' => '₦' . number_format($invoice->amount, 2),
            'amount_balance' => '₦' . number_format($invoice->balance, 2),
            'days_until_due' => $daysUntilDue,
            'days_overdue' => $daysOverdue,
        ];
    }

    /**
     * Get template data for lease expiry reminders
     */
    public function getLeaseTemplateData(Tenant $tenant): array
    {
        $user = $tenant->user;
        $unit = $tenant->unit;
        $property = $unit->property;

        $daysUntilExpiry = $tenant->lease_end_date 
            ? max(0, now()->diffInDays(Carbon::parse($tenant->lease_end_date), false))
            : 0;

        return [
            'tenant_name' => $user->name ?? 'Tenant',
            'tenant_email' => $user->email ?? '',
            'property_name' => $property->name ?? 'Property',
            'unit_number' => $unit->unit_number ?? '',
            'property_address' => $property->address ?? '',
            'lease_start_date' => $tenant->lease_start_date 
                ? Carbon::parse($tenant->lease_start_date)->format('F j, Y')
                : 'N/A',
            'lease_end_date' => $tenant->lease_end_date 
                ? Carbon::parse($tenant->lease_end_date)->format('F j, Y')
                : 'N/A',
            'monthly_rent' => '₦' . number_format($tenant->monthly_rent, 2),
            'days_until_expiry' => $daysUntilExpiry,
        ];
    }

    /**
     * Render a template with data
     */
    public function renderTemplate(ReminderTemplate $template, array $data): array
    {
        return [
            'subject' => $this->replaceVariables($template->subject, $data),
            'message' => $this->replaceVariables($template->message, $data),
        ];
    }
}

