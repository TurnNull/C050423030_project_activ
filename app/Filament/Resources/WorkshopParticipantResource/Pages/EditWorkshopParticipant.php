<?php

namespace App\Filament\Resources\WorkshopParticipantResource\Pages;

use App\Filament\Resources\WorkshopParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkshopParticipant extends EditRecord
{
    protected static string $resource = WorkshopParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
