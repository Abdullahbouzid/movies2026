<?php

namespace App\Filament\Resources\TournamentAnnouncements\Pages;

use App\Filament\Resources\TournamentAnnouncements\TournamentAnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTournamentAnnouncement extends CreateRecord
{
    protected static string $resource = TournamentAnnouncementResource::class;
}
