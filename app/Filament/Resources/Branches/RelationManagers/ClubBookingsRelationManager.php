<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\ClubBooking;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ClubBookingsRelationManager extends BranchBookingsRelationManager
{
    protected static string $relationship = 'clubBookings';

    protected static string $service = 'club';

    protected static ?string $title = 'حجوزات النادي';

    protected static ?string $modelLabel = 'حجز نادٍ';

    protected static ?string $pluralModelLabel = 'حجوزات النادي';

    protected static function columns(): array
    {
        return [
            TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
            TextColumn::make('full_name')->label('الاسم')->searchable(),
            TextColumn::make('club.name')->label('النادي'),
            TextColumn::make('package_name')->label('الباقة')->badge(),
            TextColumn::make('visit_date')->label('التاريخ')->date('Y-m-d')->sortable(),
            TextColumn::make('price')->label('السعر')->formatStateUsing(fn ($state) => \App\Support\Money::format($state)),
            TextColumn::make('payment_status')
                ->label('الدفع')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === ClubBooking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                ->color(fn ($state) => $state === ClubBooking::PAYMENT_PAID ? 'success' : 'warning'),
        ];
    }

    protected static function filters(): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    ClubBooking::PAYMENT_PENDING => 'قيد الانتظار',
                    ClubBooking::PAYMENT_PAID => 'تم الدفع',
                ]),
        ];
    }
}
