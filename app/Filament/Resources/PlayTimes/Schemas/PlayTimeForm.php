<?php

namespace App\Filament\Resources\PlayTimes\Schemas;

use App\Models\PlayTime;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
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
                    ->required(),
                Select::make('day_of_week')
                    ->label('اليوم')
                    ->options(PlayTime::$dayNames)
                    ->required(),
                TimePicker::make('start_time')
                    ->label('وقت البداية')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('وقت النهاية')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
                Toggle::make('is_active')
                    ->label('مُفعّل')
                    ->default(true),
            ])
            ->columns(2);
    }
}
