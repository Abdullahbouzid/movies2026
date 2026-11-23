<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الحجز')
                    ->columns(2)
                    ->schema([
                        TextInput::make('booking_ref')
                            ->label('رقم الحجز')
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('show_date')
                            ->label('تاريخ العرض')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('branch_id')
                            ->label('الفرع')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('movie_id')
                            ->label('الفيلم')
                            ->relationship('movie', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('package_id')
                            ->label('الباقة')
                            ->relationship('package', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('بيانات العميل')
                    ->columns(2)
                    ->schema([
                        TextInput::make('full_name')
                            ->label('الاسم الثلاثي')
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('birthdate')
                            ->label('تاريخ الميلاد')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('country_name')
                            ->label('الدولة')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('phone')
                            ->label('رقم الجوال')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('whatsapp')
                            ->label('رقم الواتساب')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('الحالات')
                    ->columns(2)
                    ->schema([
                        Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options([
                                Booking::PAYMENT_PENDING => 'قيد الانتظار',
                                Booking::PAYMENT_PAID => 'تم الدفع',
                            ])
                            ->required(),
                        Select::make('attendance_status')
                            ->label('حالة الحضور')
                            ->options([
                                Booking::ATTENDANCE_PENDING => 'قيد الانتظار',
                                Booking::ATTENDANCE_ATTENDED => 'تم الحضور',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
