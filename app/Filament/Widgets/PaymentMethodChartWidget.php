<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class PaymentMethodChartWidget extends ChartWidget
{
    protected ?string $heading = 'Payments by Method';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $payments = Payment::where('status', 'completed')
            ->whereYear('payment_date', now()->year)
            ->get();

        $byMethod = $payments->groupBy('payment_method')
            ->map(function ($group) {
                return $group->sum('amount');
            });

        $labels = $byMethod->keys()->map(fn ($method) => ucfirst(str_replace('_', ' ', $method)))->toArray();
        $data = $byMethod->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Amount (₦)',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                    ],
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

