<?php

namespace App\Filament\Resources\TournamentAnnouncements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TournamentAnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الإعلان')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان الإعلان')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('badge')
                            ->label('شارة الإعلان')
                            ->placeholder('مثال: بطولة الأسبوع')
                            ->maxLength(255),
                        Select::make('tournament_id')
                            ->label('البطولة المرتبطة')
                            ->relationship('tournament', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->seconds(false),
                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true),
                        FileUpload::make('image')
                            ->label('صورة الإعلان')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('announcements')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label('نص الإعلان')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
