<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyPaymentChartWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Payments (Last 6 Months)';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $payments = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        $monthlyData = $payments->groupBy(function ($payment) {
            return Carbon::parse($payment->payment_date)->format('Y-m');
        })->map(function ($group) {
            return $group->sum('amount');
        })->sortKeys();

        // Fill in missing months with 0
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = (float) ($monthlyData[$month] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Payments (₦)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
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

