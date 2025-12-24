<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate monthly invoice for a tenant
     */
    public function generateMonthlyInvoice(Tenant $tenant, Carbon $month = null): Invoice
    {
        if ($month === null) {
            $month = now();
        }

        // Check if invoice already exists for this month
        $existingInvoice = Invoice::where('tenant_id', $tenant->id)
            ->whereYear('invoice_date', $month->year)
            ->whereMonth('invoice_date', $month->month)
            ->where('description', 'like', '%' . $month->format('F Y') . '%')
            ->first();

        if ($existingInvoice) {
            return $existingInvoice;
        }

        $invoiceDate = $month->copy()->startOfMonth();
        $dueDate = $invoiceDate->copy()->addDays(7);

        $lineItems = [
            [
                'description' => 'Monthly Rent - ' . $month->format('F Y'),
                'amount' => $tenant->monthly_rent,
            ],
        ];

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'amount' => $tenant->monthly_rent,
            'paid_amount' => 0,
            'balance' => $tenant->monthly_rent,
            'status' => 'draft',
            'description' => 'Monthly rent for ' . $month->format('F Y'),
            'line_items' => $lineItems,
        ]);

        return $invoice;
    }

    /**
     * Generate invoices for all active tenants for a specific month
     */
    public function generateMonthlyInvoicesForAllTenants(Carbon $month = null): array
    {
        if ($month === null) {
            $month = now();
        }

        $activeTenants = Tenant::where('lease_status', 'active')
            ->with('user')
            ->get();

        $generated = [];
        $skipped = [];

        foreach ($activeTenants as $tenant) {
            try {
                $invoice = $this->generateMonthlyInvoice($tenant, $month);
                $generated[] = $invoice;
            } catch (\Exception $e) {
                $skipped[] = [
                    'tenant' => $tenant,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Mark invoice as sent
     */
    public function markInvoiceAsSent(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        return $invoice;
    }

    /**
     * Update invoice status based on due date
     */
    public function updateInvoiceStatus(Invoice $invoice): Invoice
    {
        if ($invoice->isPaid()) {
            return $invoice;
        }

        if ($invoice->due_date < now() && $invoice->status !== 'overdue') {
            $invoice->update(['status' => 'overdue']);
        }

        return $invoice;
    }

    /**
     * Update all overdue invoices
     */
    public function updateOverdueInvoices(): int
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $updated = 0;
        foreach ($invoices as $invoice) {
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Record payment against invoice
     */
    public function recordPayment(Invoice $invoice, float $amount, array $paymentData = []): void
    {
        $newPaidAmount = $invoice->paid_amount + $amount;
        $newBalance = max(0, $invoice->amount - $newPaidAmount);

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'status' => $newBalance <= 0 ? 'paid' : ($invoice->due_date < now() ? 'overdue' : 'sent'),
            'paid_at' => $newBalance <= 0 ? now() : null,
        ]);
    }
}
