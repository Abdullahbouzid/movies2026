<?php

namespace App\Filament\Resources\Movies\Pages;

use App\Filament\Resources\Movies\Concerns\ManagesPackagePrices;
use App\Filament\Resources\Movies\MovieResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMovie extends CreateRecord
{
    use ManagesPackagePrices;

    protected static string $resource = MovieResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->extractPackagePrices($data);
    }

    protected function afterCreate(): void
    {
        $this->syncPackagePrices();
    }
}
