<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverdueInvoicesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $overdueInvoices = Invoice::where('due_date', '<', now())
            ->whereIn('status', ['draft', 'sent', 'partially_paid'])
            ->get();

        $totalOverdue = $overdueInvoices->sum('balance');
        $count = $overdueInvoices->count();
        $averageOverdue = $count > 0 ? $totalOverdue / $count : 0;

        return [
            Stat::make('Overdue Invoices', $count)
                ->description('Requires attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Total Overdue', '₦' . number_format($totalOverdue, 2))
                ->description('Outstanding amount')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),
            Stat::make('Average Overdue', '₦' . number_format($averageOverdue, 2))
                ->description('Per invoice')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
        ];
    }
}

