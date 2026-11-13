<?php

namespace App\Filament\Resources\Movies\Pages;

use App\Filament\Resources\Movies\Concerns\ManagesPackagePrices;
use App\Filament\Resources\Movies\MovieResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMovie extends EditRecord
{
    use ManagesPackagePrices;

    protected static string $resource = MovieResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['package_prices'] = $this->packagePricesFromRecord();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractPackagePrices($data);
    }

    protected function afterSave(): void
    {
        $this->syncPackagePrices();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
