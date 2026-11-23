<?php

namespace App\Filament\Resources\EventPackages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الباقة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الباقة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('includes')
                            ->label('ما تشمله الباقة')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('people_count')
                            ->label('عدد الأشخاص')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        TextInput::make('capacity')
                            ->label('السعة (0 = بلا حد)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('أقصى عدد أشخاص في الفترة الواحدة'),
                        TextInput::make('price')
                            ->label(\App\Support\Money::label('السعر'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_default')
                            ->label('الباقة الافتراضية'),
                        Toggle::make('is_active')
                            ->label('مُفعّلة (تظهر على الموقع)')
                            ->default(true),
                        FileUpload::make('image')
                            ->label('صورة الباقة')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('events')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize((int) config('uploads.image_max_kb'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
