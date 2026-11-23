<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\GameBooking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class GamesBookingsRelationManager extends BranchBookingsRelationManager
{
    protected static string $relationship = 'gameBookings';

    protected static string $service = 'games';

    protected static ?string $title = 'حجوزات الألعاب';

    protected static ?string $modelLabel = 'حجز لعبة';

    protected static ?string $pluralModelLabel = 'حجوزات الألعاب';

    protected static function columns(): array
    {
        return [
            TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
            TextColumn::make('full_name')->label('الاسم')->searchable(),
            TextColumn::make('game.name')->label('اللعبة')->searchable(),
            TextColumn::make('duration_label')->label('المدة')->badge(),
            TextColumn::make('play_date')->label('التاريخ')->date('Y-m-d')->sortable(),
            TextColumn::make('price')->label('السعر')->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),
            TextColumn::make('payment_status')
                ->label('الدفع')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === GameBooking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                ->color(fn ($state) => $state === GameBooking::PAYMENT_PAID ? 'success' : 'warning'),
        ];
    }

    protected static function filters(): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    GameBooking::PAYMENT_PENDING => 'قيد الانتظار',
                    GameBooking::PAYMENT_PAID => 'تم الدفع',
                ]),
        ];
    }
}
