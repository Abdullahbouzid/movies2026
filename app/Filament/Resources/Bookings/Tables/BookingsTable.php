<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_ref')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('movie.name')
                    ->label('الفيلم')
                    ->searchable(),
                TextColumn::make('package.name')
                    ->label('الباقة')
                    ->badge(),
                TextColumn::make('show_date')
                    ->label('تاريخ العرض')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('showtime.start_time')
                    ->label('الوقت')
                    ->time('H:i'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('LYD'),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === Booking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === Booking::PAYMENT_PAID ? 'success' : 'warning'),
                TextColumn::make('attendance_status')
                    ->label('الحضور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === Booking::ATTENDANCE_ATTENDED ? 'تم الحضور' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === Booking::ATTENDANCE_ATTENDED ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        Booking::PAYMENT_PENDING => 'قيد الانتظار',
                        Booking::PAYMENT_PAID => 'تم الدفع',
                    ]),
                SelectFilter::make('attendance_status')
                    ->label('حالة الحضور')
                    ->options([
                        Booking::ATTENDANCE_PENDING => 'قيد الانتظار',
                        Booking::ATTENDANCE_ATTENDED => 'تم الحضور',
                    ]),
                SelectFilter::make('movie_id')
                    ->label('الفيلم')
                    ->relationship('movie', 'name'),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Booking $record) => $record->payment_status === Booking::PAYMENT_PENDING)
                    ->requiresConfirmation()
                    ->action(fn (Booking $record) => $record->update(['payment_status' => Booking::PAYMENT_PAID])),
                Action::make('markAttended')
                    ->label('تأكيد الحضور')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Booking $record) => $record->attendance_status === Booking::ATTENDANCE_PENDING)
                    ->requiresConfirmation()
                    ->action(fn (Booking $record) => $record->update(['attendance_status' => Booking::ATTENDANCE_ATTENDED])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
