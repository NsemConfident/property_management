<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $thisMonth = Payment::where('status', 'completed')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $lastMonth = Payment::where('status', 'completed')
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('amount');

        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        $monthlyChange = $lastMonth > 0 
            ? (($thisMonth - $lastMonth) / $lastMonth) * 100 
            : 0;

        return [
            Stat::make('Total Revenue', '₦' . number_format($totalRevenue, 2))
                ->description('All time revenue')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('This Month', '₦' . number_format($thisMonth, 2))
                ->description($monthlyChange >= 0 ? '+' . number_format($monthlyChange, 1) . '% from last month' : number_format($monthlyChange, 1) . '% from last month')
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),
            Stat::make('Total Payments', Payment::where('status', 'completed')->count())
                ->description('Completed payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}

