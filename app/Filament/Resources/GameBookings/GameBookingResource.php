<?php

namespace App\Filament\Resources\GameBookings;

use App\Filament\Resources\GameBookings\Pages\EditGameBooking;
use App\Filament\Resources\GameBookings\Pages\ListGameBookings;
use App\Filament\Resources\GameBookings\Schemas\GameBookingForm;
use App\Filament\Resources\GameBookings\Tables\GameBookingsTable;
use App\Models\GameBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GameBookingResource extends Resource
{
    protected static ?string $model = GameBooking::class;

    protected static ?string $modelLabel = 'حجز لعبة';

    protected static ?string $pluralModelLabel = 'حجوزات الألعاب';

    protected static ?string $navigationLabel = 'حجوزات الألعاب';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return GameBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GameBookingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameBookings::route('/'),
            'edit' => EditGameBooking::route('/{record}/edit'),
        ];
    }
}
