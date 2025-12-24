<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Payment::with(['tenant.user', 'invoice']);

        if (isset($this->filters['start_date'])) {
            $query->where('payment_date', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('payment_date', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('payment_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Payment Date',
            'Tenant Name',
            'Tenant Email',
            'Invoice Number',
            'Amount',
            'Payment Method',
            'Status',
            'Transaction Reference',
            'Notes',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date->format('Y-m-d'),
            $payment->tenant->user->name ?? 'N/A',
            $payment->tenant->user->email ?? 'N/A',
            $payment->invoice->invoice_number ?? 'N/A',
            '₦' . number_format($payment->amount, 2),
            ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')),
            ucfirst($payment->status),
            $payment->transaction_reference ?? 'N/A',
            $payment->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

