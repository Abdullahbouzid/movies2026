<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الباقة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('seats_count')
                    ->label('عدد المقاعد')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Textarea::make('includes')
                    ->label('ماذا تشمل')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
