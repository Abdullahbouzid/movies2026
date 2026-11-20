<?php

namespace App\Filament\Resources\TournamentAnnouncements\Pages;

use App\Filament\Resources\TournamentAnnouncements\TournamentAnnouncementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTournamentAnnouncements extends ListRecords
{
    protected static string $resource = TournamentAnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
