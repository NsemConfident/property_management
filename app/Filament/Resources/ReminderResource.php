<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReminderResource\Pages;
use App\Models\Reminder;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ReminderResource extends Resource
{
    protected static ?string $model = Reminder::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-bell';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Financial Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'id', fn ($query) => $query->with('user'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name ?? "Tenant #{$record->id}")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->options([
                        'payment_due' => 'Payment Due',
                        'payment_overdue' => 'Payment Overdue',
                        'lease_expiry' => 'Lease Expiry',
                        'custom' => 'Custom',
                    ])
                    ->required()
                    ->default('payment_due'),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('reminder_date')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Select::make('channel')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'both' => 'Both',
                    ])
                    ->required()
                    ->default('email'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.user.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reminder_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'payment_due' => 'Payment Due',
                        'payment_overdue' => 'Payment Overdue',
                        'lease_expiry' => 'Lease Expiry',
                        'custom' => 'Custom',
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
            'index' => Pages\ListReminders::route('/'),
            'create' => Pages\CreateReminder::route('/create'),
            'edit' => Pages\EditReminder::route('/{record}/edit'),
        ];
    }
}

