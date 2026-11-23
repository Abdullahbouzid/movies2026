<?php

namespace App\Filament\Resources\Showtimes\Schemas;

use App\Models\Movie;
use App\Models\Showtime;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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

                DatePicker::make('show_date')
                    ->label('تاريخ العرض')
                    ->native(false)
                    ->displayFormat('l d/m/Y')
                    ->minDate(today())
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        if ($state) {
                            $set('day_of_week', Carbon::parse($state)->dayOfWeek);
                        }
                    })
                    ->helperText('اختر التاريخ — يُحدَّد اليوم تلقائيًا.')
                    ->required(),

                Select::make('day_of_week')
                    ->label('اليوم')
                    ->options(Showtime::$dayNames)
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                TimePicker::make('start_time')
                    ->label('توقيت البداية')
                    ->seconds(false)
                    ->minutesStep(5)
                    ->live()
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        // Prefill the end time from the movie duration so slots stay consistent.
                        if (! $state || $get('end_time')) {
                            return;
                        }

                        $movie = Movie::find($get('movie_id'));
                        if ($movie?->duration) {
                            $set('end_time', Carbon::parse($state)->addMinutes((int) $movie->duration)->format('H:i'));
                        }
                    })
                    ->required(),

                TimePicker::make('end_time')
                    ->label('توقيت النهاية')
                    ->seconds(false)
                    ->minutesStep(5)
                    ->required()
                    ->after('start_time'),

                TextInput::make('seats_capacity')
                    ->label('سعة العرض (عدد المقاعد)')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(5000)
                    ->default(60)
                    ->required()
                    ->helperText('الحد الأقصى للمقاعد المباعة في هذا العرض. يُخصم عدد مقاعد كل باقة عند الحجز.'),
            ]);
    }
}
