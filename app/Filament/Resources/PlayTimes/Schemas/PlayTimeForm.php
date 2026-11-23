<?php

namespace App\Filament\Resources\PlayTimes\Schemas;

use App\Models\PlayTime;
use App\Rules\PlayTimeConflictRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PlayTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('game_id')
                    ->label('اللعبة')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('day_of_week')
                    ->label('اليوم')
                    ->options(PlayTime::$dayNames)
                    ->live()
                    ->required(),
                TimePicker::make('start_time')
                    ->label('وقت البداية')
                    ->seconds(false)
                    ->live()
                    ->required(),
                TimePicker::make('end_time')
                    ->label('وقت النهاية')
                    ->seconds(false)
                    ->live()
                    ->required()
                    ->after('start_time')
                    ->rule(fn (Get $get, $livewire) => new PlayTimeConflictRule(
                        gameId: $get('game_id'),
                        dayOfWeek: $get('day_of_week'),
                        startTime: $get('start_time'),
                        ignoreId: $livewire->getRecord()?->getKey(),
                    )),
                Toggle::make('is_active')
                    ->label('مُفعّل')
                    ->default(true),
            ])
            ->columns(2);
    }
}
