<?php

namespace App\Exports;

use App\Models\Tenant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TenantsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Tenant::with(['user', 'unit.property'])
            ->where('lease_status', 'active')
            ->orderBy('lease_start_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tenant Name',
            'Email',
            'Phone',
            'Property',
            'Unit Number',
            'Monthly Rent',
            'Lease Start Date',
            'Lease End Date',
            'Lease Status',
            'Emergency Contact Name',
            'Emergency Contact Phone',
        ];
    }

    public function map($tenant): array
    {
        return [
            $tenant->user->name ?? 'N/A',
            $tenant->user->email ?? 'N/A',
            $tenant->user->phone ?? 'N/A',
            $tenant->unit->property->name ?? 'N/A',
            $tenant->unit->unit_number ?? 'N/A',
            '₦' . number_format($tenant->monthly_rent, 2),
            $tenant->lease_start_date ? $tenant->lease_start_date->format('Y-m-d') : 'N/A',
            $tenant->lease_end_date ? $tenant->lease_end_date->format('Y-m-d') : 'N/A',
            ucfirst($tenant->lease_status),
            $tenant->emergency_contact_name ?? 'N/A',
            $tenant->emergency_contact_phone ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

