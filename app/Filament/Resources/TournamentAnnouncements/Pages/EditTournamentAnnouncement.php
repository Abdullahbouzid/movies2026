<?php

namespace App\Filament\Resources\TournamentAnnouncements\Pages;

use App\Filament\Resources\TournamentAnnouncements\TournamentAnnouncementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTournamentAnnouncement extends EditRecord
{
    protected static string $resource = TournamentAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
