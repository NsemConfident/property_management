<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Invoice::with(['tenant.user', 'tenant.unit.property']);

        if (isset($this->filters['start_date'])) {
            $query->where('invoice_date', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('invoice_date', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('invoice_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Invoice Date',
            'Due Date',
            'Tenant Name',
            'Property',
            'Unit',
            'Amount',
            'Paid Amount',
            'Balance',
            'Status',
            'Description',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->invoice_date->format('Y-m-d'),
            $invoice->due_date->format('Y-m-d'),
            $invoice->tenant->user->name ?? 'N/A',
            $invoice->tenant->unit->property->name ?? 'N/A',
            $invoice->tenant->unit->unit_number ?? 'N/A',
            '₦' . number_format($invoice->amount, 2),
            '₦' . number_format($invoice->paid_amount, 2),
            '₦' . number_format($invoice->balance, 2),
            ucfirst($invoice->status),
            $invoice->description ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

