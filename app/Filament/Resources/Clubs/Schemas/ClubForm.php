<?php

namespace App\Filament\Resources\Clubs\Schemas;

use App\Models\Club;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClubForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات النادي')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم النادي')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location')
                            ->label('الموقع داخل المبنى')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Select::make('badge')
                            ->label('الشارة')
                            ->options(['' => 'بدون', 'trending' => 'الأكثر طلبًا', 'new' => 'جديد', 'popular' => 'شائع'])
                            ->default(''),
                        Toggle::make('is_active')
                            ->label('مُفعّل (يظهر على الموقع)')
                            ->default(true),
                        FileUpload::make('image')
                            ->label('صورة النادي')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('clubs')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize((int) config('uploads.image_max_kb'))
                            ->columnSpanFull(),
                    ]),
                Section::make('ألوان البطاقة')
                    ->description('تدرّج لون البطاقة على الواجهة (اختياري — يوجد بديل تلقائي).')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        ColorPicker::make('color_from')->label('اللون (١)'),
                        ColorPicker::make('color_to')->label('اللون (٢)'),
                    ]),
            ]);
    }
}
