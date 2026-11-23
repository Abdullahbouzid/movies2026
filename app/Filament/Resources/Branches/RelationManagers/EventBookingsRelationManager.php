<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\EventBooking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class EventBookingsRelationManager extends BranchBookingsRelationManager
{
    protected static string $relationship = 'eventBookings';

    protected static string $service = 'events';

    protected static ?string $title = 'حجوزات المناسبات';

    protected static ?string $modelLabel = 'حجز مناسبة';

    protected static ?string $pluralModelLabel = 'حجوزات المناسبات';

    protected static function columns(): array
    {
        return [
            TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
            TextColumn::make('full_name')->label('الاسم')->searchable(),
            TextColumn::make('package_name')->label('الباقة')->badge(),
            TextColumn::make('event_date')->label('التاريخ')->date('Y-m-d')->sortable(),
            TextColumn::make('price')->label('السعر')->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),
            TextColumn::make('payment_status')
                ->label('الدفع')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === EventBooking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                ->color(fn ($state) => $state === EventBooking::PAYMENT_PAID ? 'success' : 'warning'),
        ];
    }

    protected static function filters(): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    EventBooking::PAYMENT_PENDING => 'قيد الانتظار',
                    EventBooking::PAYMENT_PAID => 'تم الدفع',
                ]),
        ];
    }
}
