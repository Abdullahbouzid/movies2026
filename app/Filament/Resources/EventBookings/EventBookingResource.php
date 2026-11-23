<?php

namespace App\Filament\Resources\EventBookings;

use App\Filament\Resources\EventBookings\Pages\EditEventBooking;
use App\Filament\Resources\EventBookings\Pages\ListEventBookings;
use App\Filament\Resources\EventBookings\Schemas\EventBookingForm;
use App\Filament\Resources\EventBookings\Tables\EventBookingsTable;
use App\Models\EventBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventBookingResource extends Resource
{
    protected static ?string $model = EventBooking::class;

    protected static ?string $modelLabel = 'حجز مناسبة';

    protected static ?string $pluralModelLabel = 'حجوزات المناسبات';

    protected static ?string $navigationLabel = 'حجوزات المناسبات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return EventBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventBookingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventBookings::route('/'),
            'edit' => EditEventBooking::route('/{record}/edit'),
        ];
    }
}
