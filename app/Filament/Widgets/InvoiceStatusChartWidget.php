<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class InvoiceStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Invoice Status Distribution';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $invoices = Invoice::all();

        $byStatus = $invoices->groupBy('status')
            ->map->count();

        $statusLabels = [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];

        $labels = [];
        $data = [];
        $colors = [
            'draft' => 'rgba(156, 163, 175, 0.8)',     // gray
            'sent' => 'rgba(59, 130, 246, 0.8)',       // blue
            'paid' => 'rgba(16, 185, 129, 0.8)',       // green
            'overdue' => 'rgba(239, 68, 68, 0.8)',    // red
            'cancelled' => 'rgba(107, 114, 128, 0.8)', // gray
        ];

        $backgroundColor = [];

        foreach ($byStatus as $status => $count) {
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $data[] = $count;
            $backgroundColor[] = $colors[$status] ?? 'rgba(156, 163, 175, 0.8)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Invoices',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}

