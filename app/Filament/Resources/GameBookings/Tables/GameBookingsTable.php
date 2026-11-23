<?php

namespace App\Filament\Resources\GameBookings\Tables;

use App\Filament\Resources\Concerns\BuildsBookingAdmin;
use App\Models\GameBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GameBookingsTable
{
    use BuildsBookingAdmin;

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
                self::branchColumn(),
                TextColumn::make('game.name')
                    ->label('اللعبة')
                    ->searchable(),
                TextColumn::make('duration_label')
                    ->label('المدة')
                    ->badge(),
                TextColumn::make('play_date')
                    ->label('تاريخ اللعب')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->summarize(Sum::make()->label('الإجمالي')->formatStateUsing(fn ($state) => \App\Support\Money::format($state))),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === GameBooking::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === GameBooking::PAYMENT_PAID ? 'success' : 'warning'),
                TextColumn::make('attendance_status')
                    ->label('الحضور')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === GameBooking::ATTENDANCE_ATTENDED ? 'تم الحضور' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === GameBooking::ATTENDANCE_ATTENDED ? 'success' : 'gray'),
                self::paidAtColumn(),
            ])
            ->filters([
                SelectFilter::make('game_id')
                    ->label('اللعبة')
                    ->relationship('game', 'name'),
                self::branchFilter(),
                self::createdBetweenFilter(),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        GameBooking::PAYMENT_PENDING => 'قيد الانتظار',
                        GameBooking::PAYMENT_PAID => 'تم الدفع',
                    ]),
                SelectFilter::make('attendance_status')
                    ->label('حالة الحضور')
                    ->options([
                        GameBooking::ATTENDANCE_PENDING => 'قيد الانتظار',
                        GameBooking::ATTENDANCE_ATTENDED => 'تم الحضور',
                    ]),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (GameBooking $record) => $record->payment_status === GameBooking::PAYMENT_PENDING)
                    ->requiresConfirmation()
                    ->action(fn (GameBooking $record) => $record->markPaid()),
                Action::make('markAttended')
                    ->label('تأكيد الحضور')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (GameBooking $record) => $record->attendance_status === GameBooking::ATTENDANCE_PENDING)
                    ->requiresConfirmation()
                    ->action(fn (GameBooking $record) => $record->update(['attendance_status' => GameBooking::ATTENDANCE_ATTENDED])),
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
