<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OccupancyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalUnits = Unit::count();
        $occupiedUnits = Unit::where('status', 'occupied')->count();
        $availableUnits = Unit::where('status', 'available')->count();
        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;

        return [
            Stat::make('Total Units', $totalUnits)
                ->description('All properties')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),
            Stat::make('Occupied Units', $occupiedUnits)
                ->description(number_format($occupancyRate, 1) . '% occupancy rate')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Available Units', $availableUnits)
                ->description('Ready for rent')
                ->descriptionIcon('heroicon-m-key')
                ->color('warning'),
        ];
    }
}

