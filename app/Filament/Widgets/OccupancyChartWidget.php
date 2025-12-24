<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class OccupancyChartWidget extends ChartWidget
{
    protected ?string $heading = 'Unit Status Distribution';

    protected static ?int $sort = 8;

    protected function getData(): array
    {
        $units = Unit::all();

        $byStatus = $units->groupBy('status')
            ->map->count();

        $statusLabels = [
            'available' => 'Available',
            'occupied' => 'Occupied',
            'maintenance' => 'Maintenance',
            'reserved' => 'Reserved',
        ];

        $labels = [];
        $data = [];
        $colors = [
            'rgba(16, 185, 129, 0.8)',  // available - green
            'rgba(59, 130, 246, 0.8)',  // occupied - blue
            'rgba(245, 158, 11, 0.8)',   // maintenance - yellow
            'rgba(139, 92, 246, 0.8)',  // reserved - purple
        ];

        $colorIndex = 0;
        foreach ($byStatus as $status => $count) {
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $data[] = $count;
            $colorIndex++;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Units',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

