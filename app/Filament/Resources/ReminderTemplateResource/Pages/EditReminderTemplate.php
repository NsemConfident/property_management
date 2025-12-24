<?php

namespace App\Filament\Resources\ReminderTemplateResource\Pages;

use App\Filament\Resources\ReminderTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReminderTemplate extends EditRecord
{
    protected static string $resource = ReminderTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

