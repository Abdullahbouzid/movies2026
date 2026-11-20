<?php

namespace App\Filament\Resources\GameBookings\Pages;

use App\Filament\Resources\GameBookings\GameBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGameBooking extends EditRecord
{
    protected static string $resource = GameBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
