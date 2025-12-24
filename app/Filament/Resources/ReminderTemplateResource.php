<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReminderTemplateResource\Pages;
use App\Models\ReminderTemplate;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReminderTemplateResource extends Resource
{
    protected static ?string $model = ReminderTemplate::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Template Name')
                    ->helperText('A descriptive name for this template'),
                Forms\Components\Select::make('type')
                    ->options([
                        'payment_due' => 'Payment Due',
                        'payment_overdue' => 'Payment Overdue',
                        'lease_expiry' => 'Lease Expiry',
                        'custom' => 'Custom',
                    ])
                    ->required()
                    ->default('custom')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Update variables help when type changes
                        $variables = ReminderTemplate::getAvailableVariables($state);
                        $helpText = "Available variables:\n\n" . implode("\n", array_map(
                            fn($key, $desc) => "{$key} - {$desc}",
                            array_keys($variables),
                            $variables
                        ));
                        $set('variables_help', $helpText);
                    }),
                Forms\Components\TextInput::make('days_before')
                    ->numeric()
                    ->label('Days Before')
                    ->helperText('Days before due date/expiry to send this reminder (leave blank for overdue/custom)')
                    ->visible(fn ($get) => in_array($get('type'), ['payment_due', 'lease_expiry'])),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->label('Email Subject')
                    ->helperText('You can use template variables like {{tenant_name}}, {{invoice_number}}, etc.')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->rows(10)
                    ->label('Email Message')
                    ->helperText('You can use template variables like {{tenant_name}}, {{invoice_number}}, etc.')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Only active templates will be used'),
                Forms\Components\Textarea::make('variables_help')
                    ->label('Available Variables')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(8)
                    ->columnSpanFull()
                    ->helperText('These variables can be used in the subject and message fields')
                    ->default(function ($get) {
                        $type = $get('type') ?? 'custom';
                        $variables = \App\Models\ReminderTemplate::getAvailableVariables($type);
                        return "Available variables:\n\n" . implode("\n", array_map(
                            fn($key, $desc) => "{$key} - {$desc}",
                            array_keys($variables),
                            $variables
                        ));
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payment_due' => 'info',
                        'payment_overdue' => 'danger',
                        'lease_expiry' => 'warning',
                        'custom' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_before')
                    ->label('Days Before')
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'payment_due' => 'Payment Due',
                        'payment_overdue' => 'Payment Overdue',
                        'lease_expiry' => 'Lease Expiry',
                        'custom' => 'Custom',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All templates')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
            'index' => Pages\ListReminderTemplates::route('/'),
            'create' => Pages\CreateReminderTemplate::route('/create'),
            'edit' => Pages\EditReminderTemplate::route('/{record}/edit'),
        ];
    }
}

