<?php

namespace App\Filament\Resources\Tournaments\Schemas;

use App\Models\Tournament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TournamentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات البطولة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم البطولة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('game_id')
                            ->label('اللعبة')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('format')
                            ->label('هيكلية المنافسة')
                            ->options(Tournament::FORMATS)
                            ->default('knockout')
                            ->required(),
                        TextInput::make('participants_count')
                            ->label('عدد المشاركين المخطّط')
                            ->numeric()
                            ->minValue(2)
                            ->default(8)
                            ->required()
                            ->helperText('يُستخدم لتخطيط الهيكلية قبل القرعة.'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(Tournament::STATUSES)
                            ->default('upcoming')
                            ->required(),
                        DatePicker::make('starts_on')
                            ->label('تاريخ البداية'),
                        TimePicker::make('starts_at')
                            ->label('وقت البداية')
                            ->seconds(false),
                        TextInput::make('entry_fee')
                            ->label(\App\Support\Money::label('رسوم الاشتراك'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                        TextInput::make('prize')
                            ->label(\App\Support\Money::label('الجائزة'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),
                        FileUpload::make('image')
                            ->label('صورة البطولة')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('tournaments')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize((int) config('uploads.image_max_kb'))
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('وصف البطولة')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
