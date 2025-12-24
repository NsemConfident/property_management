<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PropertiesOverviewTableWidget extends BaseWidget
{
    protected static ?int $sort = 13;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Property::query()
                    ->with(['owner', 'manager'])
                    ->withCount(['units', 'units as occupied_units_count' => function ($query) {
                        $query->where('status', 'occupied');
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Property Name'),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->address),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('Manager')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Total Units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupied_units_count')
                    ->label('Occupied')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupancy_rate')
                    ->label('Occupancy Rate')
                    ->getStateUsing(function (Property $record): string {
                        $total = $record->units_count ?? 0;
                        $occupied = $record->occupied_units_count ?? 0;
                        $rate = $total > 0 ? ($occupied / $total) * 100 : 0;
                        return number_format($rate, 1) . '%';
                    })
                    ->color(function (Property $record): string {
                        $total = $record->units_count ?? 0;
                        $occupied = $record->occupied_units_count ?? 0;
                        $rate = $total > 0 ? ($occupied / $total) * 100 : 0;
                        return $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'maintenance' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
            ->heading('Properties Overview');
    }
}

