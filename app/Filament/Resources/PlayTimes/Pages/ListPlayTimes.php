<?php

namespace App\Filament\Resources\PlayTimes\Pages;

use App\Filament\Resources\PlayTimes\PlayTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayTimes extends ListRecords
{
    protected static string $resource = PlayTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
