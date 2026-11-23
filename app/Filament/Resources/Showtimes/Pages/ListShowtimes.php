<?php

namespace App\Filament\Resources\Showtimes\Pages;

use App\Filament\Resources\Showtimes\ShowtimeResource;
use App\Support\WeekSchedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;

class ListShowtimes extends ListRecords
{
    protected static string $resource = ShowtimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateWeek')
                ->label('توليد جدول أسبوعي')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->modalHeading('توليد أوقات عرض لأسبوع كامل')
                ->modalDescription('يُنشئ لكل فيلم معروض موعدًا في كل يوم من الأيام المحددة، مع تعبئة تاريخ العرض واليوم تلقائيًا. المواعيد الموجودة مسبقًا لن تتكرر.')
                ->schema([
                    DatePicker::make('from')
                        ->label('يبدأ من تاريخ')
                        ->native(false)
                        ->displayFormat('l d/m/Y')
                        ->default(today())
                        ->minDate(today())
                        ->required(),
                    TagsInput::make('start_times')
                        ->label('أوقات البداية')
                        ->helperText('اكتب الوقت ثم اضغط Enter. مثال: 11:30، 14:00، 17:00')
                        ->default(['11:30', '14:00', '17:00', '20:00'])
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('days')
                        ->label('عدد الأيام')
                        ->numeric()
                        ->default(7)
                        ->minValue(1)
                        ->maxValue(30)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $created = WeekSchedule::generate(
                        $data['start_times'],
                        \Illuminate\Support\Carbon::parse($data['from']),
                        (int) $data['days'],
                    );

                    Notification::make()
                        ->title('تم توليد الجدول')
                        ->body("تمت إضافة {$created} وقت عرض جديد.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
