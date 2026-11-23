<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفرع')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الفرع')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('location')
                            ->label('العنوان')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_main')
                            ->label('المركز الرئيسي')
                            ->helperText('عند تفعيله يُلغى تحديد المركز الرئيسي عن بقية الفروع تلقائيًا.')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('مُفعّل (يظهر على الموقع)')
                            ->default(true),
                        FileUpload::make('image')
                            ->label('صورة الفرع')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('branches')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize((int) config('uploads.image_max_kb'))
                            ->columnSpanFull(),
                    ]),
                Section::make('الخدمات المتوفرة')
                    ->description('اختر ما يضمه هذا الفرع. تظهر هذه الخدمات في الحجوزات التابعة للفرع.')
                    ->schema([
                        CheckboxList::make('services')
                            ->label('الخدمات')
                            ->options(Branch::SERVICES)
                            ->columns(2)
                            ->bulkToggleable()
                            ->required(),
                    ]),
            ]);
    }
}
