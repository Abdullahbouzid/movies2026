<?php

namespace App\Filament\Resources\Showtimes\Schemas;

use App\Models\Showtime;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ShowtimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('movie_id')
                    ->label('الفيلم')
                    ->relationship('movie', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('day_of_week')
                    ->label('اليوم')
                    ->options(Showtime::$dayNames)
                    ->required(),
                TimePicker::make('start_time')
                    ->label('توقيت البداية')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('توقيت النهاية')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
            ]);
    }
}
