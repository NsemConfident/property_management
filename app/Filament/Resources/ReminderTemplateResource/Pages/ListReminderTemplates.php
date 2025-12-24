<?php

namespace App\Filament\Resources\ReminderTemplateResource\Pages;

use App\Filament\Resources\ReminderTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReminderTemplates extends ListRecords
{
    protected static string $resource = ReminderTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

