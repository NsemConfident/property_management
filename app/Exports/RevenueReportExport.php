<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport implements FromArray, WithHeadings, WithStyles
{
    protected $reportData;
    protected $reportService;

    public function __construct(array $filters = [])
    {
        $this->reportService = app(ReportService::class);
        $this->reportData = $this->reportService->getRevenueReport($filters);
    }

    public function array(): array
    {
        $data = [];
        
        // Summary row
        $data[] = [
            'Total Revenue',
            '₦' . number_format($this->reportData['total_revenue'], 2),
            '',
            '',
        ];
        $data[] = [
            'Total Payments',
            $this->reportData['total_payments'],
            '',
            '',
        ];
        $data[] = [
            'Average Payment',
            '₦' . number_format($this->reportData['average_payment'], 2),
            '',
            '',
        ];
        $data[] = ['', '', '', '']; // Empty row
        
        // Monthly breakdown
        $data[] = ['Month', 'Revenue', '', ''];
        foreach ($this->reportData['monthly_breakdown'] as $month => $amount) {
            $data[] = [
                \Carbon\Carbon::parse($month)->format('F Y'),
                '₦' . number_format($amount, 2),
                '',
                '',
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Item',
            'Value',
            '',
            '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]], // Monthly breakdown header
        ];
    }
}

