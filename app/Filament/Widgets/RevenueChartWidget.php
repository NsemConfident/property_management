<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $reportService = app(ReportService::class);
        $revenueData = $reportService->getRevenueReport([
            'start_date' => now()->startOfYear(),
            'end_date' => now(),
        ]);

        $labels = [];
        $data = [];

        foreach ($revenueData['monthly_breakdown'] as $month => $amount) {
            $labels[] = Carbon::parse($month)->format('M Y');
            $data[] = (float) $amount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₦)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
