<?php

namespace App\Filament\Resources\ClubBookings\Tables;

use App\Filament\Resources\Concerns\BuildsBookingAdmin;
use App\Models\ClubBooking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClubBookingsTable
{
    use BuildsBookingAdmin;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_ref')->label('رقم الحجز')->searchable()->copyable()->weight('bold'),
                TextColumn::make('full_name')->label('الاسم')->searchable(),
                TextColumn::make('club.name')->label('النادي')->searchable(),
                TextColumn::make('package_name')->label('الباقة')->badge(),
                TextColumn::make('visit_date')->label('التاريخ')->date('d/m/Y')->sortable(),
                TextColumn::make('start_time')->label('من')->time('H:i'),
                TextColumn::make('end_time')->label('إلى')->time('H:i'),
                TextColumn::make('people_count')->label('الأشخاص')->badge(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->formatStateUsing(fn ($state) => \App\Support\Money::format($state))),
                self::branchColumn(),
                ...self::statusColumns(ClubBooking::class),
                self::paidAtColumn(),
            ])
            ->filters([
                SelectFilter::make('club_id')->label('النادي')->relationship('club', 'name'),
                self::branchFilter(),
                self::createdBetweenFilter(),
                ...self::statusFilters(ClubBooking::class),
            ])
            ->recordActions([
                ...self::statusActions(ClubBooking::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
