<?php

namespace App\Filament\Resources\ClubBookings\Pages;

use App\Filament\Resources\ClubBookings\ClubBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListClubBookings extends ListRecords
{
    protected static string $resource = ClubBookingResource::class;
}
