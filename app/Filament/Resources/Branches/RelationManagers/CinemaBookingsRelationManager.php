<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\Booking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class CinemaBookingsRelationManager extends BranchBookingsRelationManager
{
    protected static string $relationship = 'bookings';

    protected static string $service = 'cinema';

    protected static ?string $title = 'حجوزات السينما';

    protected static ?string $modelLabel = 'حجز سينما';

    protected static ?string $pluralModelLabel = 'حجوزات السينما';

    protected static function columns(): array
    {
        return [
            TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
            TextColumn::make('full_name')->label('الاسم')->searchable(),
            TextColumn::make('movie.name')->label('الفيلم')->searchable(),
            TextColumn::make('show_date')->label('التاريخ')->date('Y-m-d')->sortable(),
            TextColumn::make('price')->label('السعر')->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),
            TextColumn::make('payment_status')
                ->label('الدفع')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === Booking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                ->color(fn ($state) => $state === Booking::PAYMENT_PAID ? 'success' : 'warning'),
        ];
    }

    protected static function filters(): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    Booking::PAYMENT_PENDING => 'قيد الانتظار',
                    Booking::PAYMENT_PAID => 'تم الدفع',
                ]),
        ];
    }
}
