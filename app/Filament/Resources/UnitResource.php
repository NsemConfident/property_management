<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Property Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('unit_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('monthly_rent')
                    ->numeric()
                    ->prefix('₦')
                    ->required(),
                Forms\Components\TextInput::make('deposit')
                    ->numeric()
                    ->prefix('₦'),
                Forms\Components\TextInput::make('bedrooms')
                    ->numeric(),
                Forms\Components\TextInput::make('bathrooms')
                    ->numeric(),
                Forms\Components\TextInput::make('square_feet')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Maintenance',
                        'reserved' => 'Reserved',
                    ])
                    ->required()
                    ->default('available'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.name')
                    ->label('Property')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'info',
                        'maintenance' => 'warning',
                        'reserved' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Maintenance',
                        'reserved' => 'Reserved',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}

