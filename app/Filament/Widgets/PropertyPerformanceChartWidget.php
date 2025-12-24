<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\ChartWidget;

class PropertyPerformanceChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue by Property (This Year)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $reportService = app(ReportService::class);
        $propertyData = $reportService->getRevenueByProperty([
            'start_date' => now()->startOfYear(),
            'end_date' => now(),
        ]);

        $labels = [];
        $data = [];

        foreach ($propertyData as $property) {
            $labels[] = $property['name'];
            $data[] = (float) $property['total_revenue'];
        }

        // Limit to top 10 properties for better visualization
        if (count($labels) > 10) {
            $combined = array_combine($labels, $data);
            arsort($combined);
            $combined = array_slice($combined, 0, 10, true);
            $labels = array_keys($combined);
            $data = array_values($combined);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₦)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

