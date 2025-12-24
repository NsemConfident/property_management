<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Property Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'unit_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('lease_start_date')
                    ->required(),
                Forms\Components\DatePicker::make('lease_end_date'),
                Forms\Components\TextInput::make('monthly_rent')
                    ->numeric()
                    ->prefix('₦')
                    ->required(),
                Forms\Components\Select::make('lease_status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\TextInput::make('emergency_contact_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('emergency_contact_phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.unit_number')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.property.name')
                    ->label('Property')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'terminated' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lease_status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}

