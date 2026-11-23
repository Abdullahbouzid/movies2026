<?php

namespace App\Filament\Resources\EventBookings\Pages;

use App\Filament\Resources\EventBookings\EventBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListEventBookings extends ListRecords
{
    protected static string $resource = EventBookingResource::class;
}
