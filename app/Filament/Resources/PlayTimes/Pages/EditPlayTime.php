<?php

namespace App\Filament\Resources\PlayTimes\Pages;

use App\Filament\Resources\PlayTimes\PlayTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayTime extends EditRecord
{
    protected static string $resource = PlayTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
