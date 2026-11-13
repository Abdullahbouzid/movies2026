<?php

namespace App\Filament\Resources\Movies\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MovieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفيلم')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الفيلم')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('genre_id')
                            ->label('الفئة')
                            ->relationship('genre', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'available' => 'متوفر',
                                'coming_soon' => 'قريبًا',
                            ])
                            ->default('available')
                            ->required()
                            ->live(),
                        DatePicker::make('release_date')
                            ->label('تاريخ العرض')
                            ->visible(fn ($get) => $get('status') === 'coming_soon'),
                        TextInput::make('duration')
                            ->label('مدة العرض (دقيقة)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('rating')
                            ->label('تقييم الفيلم')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->step(0.1)
                            ->default(0)
                            ->required(),
                        TextInput::make('ratings_count')
                            ->label('عدد المُقيّمين')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Select::make('age_rating')
                            ->label('التصنيف العمري')
                            ->options([
                                'الجميع' => 'الجميع',
                                '+7' => '+7',
                                '+12' => '+12',
                                '+16' => '+16',
                                '+18' => '+18',
                            ])
                            ->default('الجميع'),
                        FileUpload::make('image')
                            ->label('صورة الفيلم')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('movies')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('وصف الفيلم')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('العرض المرئي (البطاقة)')
                    ->description('الشارة الترويجية وألوان البطاقة على واجهة الأفلام.')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Select::make('badge')
                            ->label('الشارة')
                            ->options([
                                '' => 'بدون',
                                'trending' => 'رائج 🔥',
                                'new' => 'جديد ✨',
                                'popular' => 'مميز ⭐',
                            ])
                            ->default(''),
                        ColorPicker::make('poster_from')
                            ->label('لون البطاقة (١)'),
                        ColorPicker::make('poster_to')
                            ->label('لون البطاقة (٢)'),
                        TextInput::make('accent_icon')
                            ->label('أيقونة العرض القادم')
                            ->maxLength(8)
                            ->helperText('إيموجي يظهر في بطاقة «قريبًا في السينما»، مثال: 🔥')
                            ->columnSpanFull(),
                    ]),
                Section::make('أسعار التذاكر حسب الباقة')
                    ->description('أضف سعر تذكرة الفيلم لكل باقة (يمكن إضافة أكثر من باقة).')
                    ->schema([
                        Repeater::make('package_prices')
                            ->hiddenLabel()
                            ->addActionLabel('إضافة باقة')
                            ->columns(2)
                            ->schema([
                                Select::make('package_id')
                                    ->label('الباقة')
                                    ->options(fn () => \App\Models\Package::query()->pluck('name', 'id'))
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('price')
                                    ->label('سعر التذكرة')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
