<?php

namespace App\Filament\Resources\ClubBookings;

use App\Filament\Resources\ClubBookings\Pages\EditClubBooking;
use App\Filament\Resources\ClubBookings\Pages\ListClubBookings;
use App\Filament\Resources\ClubBookings\Schemas\ClubBookingForm;
use App\Filament\Resources\ClubBookings\Tables\ClubBookingsTable;
use App\Models\ClubBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClubBookingResource extends Resource
{
    protected static ?string $model = ClubBooking::class;

    protected static ?string $modelLabel = 'حجز نادي';

    protected static ?string $pluralModelLabel = 'حجوزات النادي';

    protected static ?string $navigationLabel = 'حجوزات النادي';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return ClubBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubBookingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubBookings::route('/'),
            'edit' => EditClubBooking::route('/{record}/edit'),
        ];
    }
}
