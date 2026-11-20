<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Models\Game;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات اللعبة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم اللعبة')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->columnSpanFull(),
                        TextInput::make('meta')
                            ->label('وصف مختصر (يظهر تحت الاسم)')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->label('الأيقونة (إيموجي)')
                            ->maxLength(8)
                            ->helperText('مثال: 🎮'),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        TextInput::make('rating')
                            ->label('التقييم')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.1)
                            ->default(0),
                        TextInput::make('ratings_count')
                            ->label('عدد المُقيّمين')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Select::make('badge')
                            ->label('الشارة')
                            ->options(Game::BADGES)
                            ->default(''),
                        Toggle::make('is_active')
                            ->label('مُفعّلة (تظهر على الموقع)')
                            ->default(true),
                        FileUpload::make('image')
                            ->label('صورة اللعبة')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('games')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('وصف اللعبة')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('ألوان البطاقة')
                    ->description('تدرّج لون البطاقة على واجهة الألعاب (اختياري — يوجد بديل تلقائي).')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        ColorPicker::make('color_from')
                            ->label('اللون (١)'),
                        ColorPicker::make('color_to')
                            ->label('اللون (٢)'),
                    ]),
                Section::make('أسعار اللعب والمدد')
                    ->description('أضف لكل لعبة أكثر من مدة لعب مع سعرها. (يمكن أيضًا إدارتها من تبويب «الأسعار والمدد» بعد الحفظ)')
                    ->schema([
                        Repeater::make('prices')
                            ->relationship()
                            ->hiddenLabel()
                            ->addActionLabel('إضافة مدة وسعر')
                            ->columns(4)
                            ->defaultItems(1)
                            ->schema([
                                TextInput::make('label')
                                    ->label('المدة')
                                    ->placeholder('مثال: ساعة')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('minutes')
                                    ->label('الدقائق')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('price')
                                    ->label('السعر (﷼)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->required(),
                                \Filament\Forms\Components\Toggle::make('is_default')
                                    ->label('المدة الافتراضية')
                                    ->inline(false),
                            ]),
                    ]),
            ]);
    }
}
