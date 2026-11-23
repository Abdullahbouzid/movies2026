<?php

namespace App\Filament\Resources\EventBookings\Tables;

use App\Filament\Resources\Concerns\BuildsBookingAdmin;
use App\Models\EventBooking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventBookingsTable
{
    use BuildsBookingAdmin;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
                TextColumn::make('full_name')->label('الاسم')->searchable(),
                TextColumn::make('package_name')->label('الباقة')->badge(),
                TextColumn::make('event_date')->label('التاريخ')->date('d/m/Y')->sortable(),
                TextColumn::make('start_time')->label('من')->time('H:i'),
                TextColumn::make('end_time')->label('إلى')->time('H:i'),
                TextColumn::make('people_count')->label('الأشخاص')->badge(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->formatStateUsing(fn ($state) => \App\Support\Money::format($state))),
                self::branchColumn(),
                ...self::statusColumns(EventBooking::class),
                self::paidAtColumn(),
            ])
            ->filters([
                self::branchFilter(),
                self::createdBetweenFilter(),
                ...self::statusFilters(EventBooking::class),
            ])
            ->recordActions([
                ...self::statusActions(EventBooking::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
