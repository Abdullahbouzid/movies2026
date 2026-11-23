<?php

namespace App\Filament\Resources\EventBookings\Schemas;

use App\Filament\Resources\Concerns\BuildsBookingAdmin;
use App\Models\EventBooking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventBookingForm
{
    use BuildsBookingAdmin;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الحجز')
                    ->columns(2)
                    ->schema([
                        TextInput::make('booking_ref')->label('رقم الحجز')->disabled()->dehydrated(false),
                        Select::make('branch_id')->label('الفرع')->relationship('branch', 'name')->searchable()->preload(),
                        DatePicker::make('event_date')->label('تاريخ المناسبة')->disabled()->dehydrated(false),
                        TextInput::make('package_name')->label('الباقة')->disabled()->dehydrated(false),
                        TextInput::make('people_count')->label('عدد الأشخاص')->disabled()->dehydrated(false),
                        TextInput::make('start_time')->label('من')->disabled()->dehydrated(false),
                        TextInput::make('end_time')->label('إلى')->disabled()->dehydrated(false),
                        TextInput::make('price')->label('السعر')->numeric()->disabled()->dehydrated(false),
                        Textarea::make('notes')->label('ملاحظات')->disabled()->dehydrated(false)->columnSpanFull(),
                    ]),
                self::customerSection(),
                self::statusSection(EventBooking::class),
            ]);
    }
}
