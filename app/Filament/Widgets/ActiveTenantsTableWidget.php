<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveTenantsTableWidget extends BaseWidget
{
    protected static ?int $sort = 14;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()
                    ->where('lease_status', 'active')
                    ->with(['user', 'unit.property'])
                    ->orderBy('lease_start_date', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Tenant Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.property.name')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_number')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->money('NGN')
                    ->sortable()
                    ->label('Monthly Rent'),
                Tables\Columns\TextColumn::make('lease_start_date')
                    ->date()
                    ->sortable()
                    ->label('Lease Start'),
                Tables\Columns\TextColumn::make('lease_end_date')
                    ->date()
                    ->sortable()
                    ->label('Lease End')
                    ->color(fn ($record) => $record->lease_end_date && $record->lease_end_date < now()->addMonths(3) ? 'warning' : null),
                Tables\Columns\TextColumn::make('lease_status')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->defaultSort('lease_start_date', 'desc')
            ->heading('Active Tenants');
    }
}

