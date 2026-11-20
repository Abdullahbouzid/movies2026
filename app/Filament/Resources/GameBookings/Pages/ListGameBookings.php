<?php

namespace App\Filament\Resources\GameBookings\Pages;

use App\Filament\Resources\GameBookings\GameBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListGameBookings extends ListRecords
{
    protected static string $resource = GameBookingResource::class;
}
