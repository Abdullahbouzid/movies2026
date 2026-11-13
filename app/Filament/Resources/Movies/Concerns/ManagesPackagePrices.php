<?php

namespace App\Filament\Resources\Movies\Concerns;

trait ManagesPackagePrices
{
    /** @var array<int, array{package_id: int|string, price: int|float|string}> */
    protected array $packagePrices = [];

    protected function extractPackagePrices(array $data): array
    {
        $this->packagePrices = array_values(array_filter(
            $data['package_prices'] ?? [],
            fn ($row) => ! empty($row['package_id']),
        ));

        unset($data['package_prices']);

        return $data;
    }

    protected function syncPackagePrices(): void
    {
        $sync = [];
        foreach ($this->packagePrices as $row) {
            $sync[$row['package_id']] = ['price' => $row['price']];
        }

        $this->record->packages()->sync($sync);
    }

    protected function packagePricesFromRecord(): array
    {
        return $this->record->packages
            ->map(fn ($package) => [
                'package_id' => $package->id,
                'price' => (float) $package->pivot->price,
            ])
            ->values()
            ->all();
    }
}
