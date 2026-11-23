<?php

namespace App\Filament\Resources\ClubBookings\Pages;

use App\Filament\Resources\ClubBookings\ClubBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClubBooking extends EditRecord
{
    protected static string $resource = ClubBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
