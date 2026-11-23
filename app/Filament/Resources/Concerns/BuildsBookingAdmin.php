<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

/**
 * Cinema, game, club and event bookings share the same read-only customer
 * block and the same payment/attendance workflow, so those pieces live here.
 */
trait BuildsBookingAdmin
{
    protected static function customerSection(): Section
    {
        return Section::make('بيانات العميل')
            ->columns(2)
            ->schema([
                TextInput::make('full_name')->label('الاسم الثلاثي')->disabled()->dehydrated(false),
                TextInput::make('country_name')->label('الدولة')->disabled()->dehydrated(false),
                TextInput::make('phone')->label('رقم الجوال')->disabled()->dehydrated(false),
                TextInput::make('whatsapp')->label('رقم الواتساب')->disabled()->dehydrated(false),
            ]);
    }

    protected static function branchColumn(): TextColumn
    {
        return TextColumn::make('branch.name')
            ->label('الفرع')
            ->badge()
            ->color('info')
            ->placeholder('—')
            ->searchable();
    }

    protected static function branchFilter(): SelectFilter
    {
        return SelectFilter::make('branch_id')
            ->label('الفرع')
            ->relationship('branch', 'name');
    }

    protected static function branchSection(): Section
    {
        return Section::make('الفرع')
            ->columns(2)
            ->schema([
                Select::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    protected static function statusSection(string $model): Section
    {
        return Section::make('الحالات')
            ->columns(2)
            ->schema([
                Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        $model::PAYMENT_PENDING => 'قيد الانتظار',
                        $model::PAYMENT_PAID => 'تم الدفع',
                    ])
                    ->required(),
                Select::make('attendance_status')
                    ->label('حالة الحضور')
                    ->options([
                        $model::ATTENDANCE_PENDING => 'قيد الانتظار',
                        $model::ATTENDANCE_ATTENDED => 'تم الحضور',
                    ])
                    ->required(),
            ]);
    }

    /**
     * @return array<int, TextColumn>
     */
    protected static function statusColumns(string $model): array
    {
        return [
            TextColumn::make('payment_status')
                ->label('الدفع')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === $model::PAYMENT_PAID ? 'تم الدفع' : 'قيد الانتظار')
                ->color(fn ($state) => $state === $model::PAYMENT_PAID ? 'success' : 'warning'),
            TextColumn::make('attendance_status')
                ->label('الحضور')
                ->badge()
                ->formatStateUsing(fn ($state) => $state === $model::ATTENDANCE_ATTENDED ? 'تم الحضور' : 'قيد الانتظار')
                ->color(fn ($state) => $state === $model::ATTENDANCE_ATTENDED ? 'success' : 'gray'),
        ];
    }

    /** Collection timestamp, so the admin can see when the money came in. */
    protected static function paidAtColumn(): TextColumn
    {
        return TextColumn::make('paid_at')
            ->label('تاريخ التحصيل')
            ->dateTime('d/m/Y H:i')
            ->placeholder('—')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /** Date-window filter over a date column, shared by every booking table. */
    protected static function createdBetweenFilter(string $column = 'created_at'): Filter
    {
        return Filter::make('created_between')
            ->label('تاريخ الحجز')
            ->form([
                DatePicker::make('from')->label('من')->native(false),
                DatePicker::make('to')->label('إلى')->native(false),
            ])
            ->query(fn ($query, array $data) => $query
                ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate($column, '>=', $d))
                ->when($data['to'] ?? null, fn ($q, $d) => $q->whereDate($column, '<=', $d)));
    }

    /**
     * @return array<int, SelectFilter>
     */
    protected static function statusFilters(string $model): array
    {
        return [
            SelectFilter::make('payment_status')
                ->label('حالة الدفع')
                ->options([
                    $model::PAYMENT_PENDING => 'قيد الانتظار',
                    $model::PAYMENT_PAID => 'تم الدفع',
                ]),
            SelectFilter::make('attendance_status')
                ->label('حالة الحضور')
                ->options([
                    $model::ATTENDANCE_PENDING => 'قيد الانتظار',
                    $model::ATTENDANCE_ATTENDED => 'تم الحضور',
                ]),
        ];
    }

    /**
     * @return array<int, Action>
     */
    protected static function statusActions(string $model): array
    {
        return [
            Action::make('markPaid')
                ->label('تأكيد الدفع')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn ($record) => $record->payment_status === $model::PAYMENT_PENDING)
                ->requiresConfirmation()
                ->action(fn ($record) => $record->markPaid()),
            Action::make('markAttended')
                ->label('تأكيد الحضور')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn ($record) => $record->attendance_status === $model::ATTENDANCE_PENDING)
                ->requiresConfirmation()
                ->action(fn ($record) => $record->update(['attendance_status' => $model::ATTENDANCE_ATTENDED])),
            Action::make('showQr')
                ->label('عرض QR')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->modalHeading('رمز التحقق')
                ->modalContent(fn ($record) => view('filament.qr-modal', [
                    'url' => $record->ensureQr() ? $record->qrUrl() : null,
                    'reference' => $record->booking_ref,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('إغلاق'),
        ];
    }
}
