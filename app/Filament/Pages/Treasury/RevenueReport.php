<?php

namespace App\Filament\Pages\Treasury;

use App\Models\Booking;
use App\Models\GameBooking;
use App\Support\CsvExport;
use App\Support\Treasury;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Revenue report: totals per activity, best-selling titles, and a CSV export of
 * the same window. The print action renders the same numbers for saving as PDF.
 */
class RevenueReport extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'الخزينة والتقارير';

    protected static ?string $navigationLabel = 'تقرير الإيرادات';

    protected static ?int $navigationSort = 14;

    protected static ?string $slug = 'revenue-report';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.pages.treasury.report';

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from ??= today()->startOfMonth()->toDateString();
        $this->to ??= today()->toDateString();
    }

    public function getTitle(): string
    {
        return 'تقرير الإيرادات';
    }

    public function getSubheading(): ?string
    {
        return 'ملخّص مالي للفترة المحددة، مع إمكانية التصدير إلى CSV أو الطباعة.';
    }

    /** @return array{rows: array<string, array<string, mixed>>, totals: array<string, float|int>} */
    public function getSummary(): array
    {
        return Treasury::summary($this->from, $this->to);
    }

    /** @return array<string, float> */
    public function getDailySeries(): array
    {
        $from = $this->from ? Carbon::parse($this->from) : today()->startOfMonth();
        $to = $this->to ? Carbon::parse($this->to) : today();

        return Treasury::dailySeries($from, $to);
    }

    /**
     * Best-selling movies in the window, by collected revenue.
     *
     * @return array<int, array{name: string, bookings: int, total: float}>
     */
    public function getTopMovies(): array
    {
        return Booking::query()
            ->paidBetween($this->from, $this->to)
            ->selectRaw('movie_id, count(*) as bookings, sum(price) as total')
            ->groupBy('movie_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('movie:id,name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->movie?->name ?? '—',
                'bookings' => (int) $row->bookings,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    /**
     * Best-selling games in the window, by collected revenue.
     *
     * @return array<int, array{name: string, bookings: int, total: float}>
     */
    public function getTopGames(): array
    {
        return GameBooking::query()
            ->paidBetween($this->from, $this->to)
            ->selectRaw('game_id, count(*) as bookings, sum(price) as total')
            ->groupBy('game_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('game:id,name')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->game?->name ?? '—',
                'bookings' => (int) $row->bookings,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('تصدير CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn (): StreamedResponse => $this->exportCsv()),
            Action::make('print')
                ->label('طباعة / PDF')
                ->icon(Heroicon::OutlinedPrinter)
                ->color('gray')
                ->url(fn () => route(
                    // Resolved by panel id because header actions also render
                    // outside a panel request (e.g. Livewire tests).
                    Filament::getPanel('admin')->generateRouteName('revenue-report.print'),
                    ['from' => $this->from, 'to' => $this->to],
                ))
                ->openUrlInNewTab(),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $summary = $this->getSummary();

        $rows = [];

        foreach ($summary['rows'] as $row) {
            $rows[] = [
                $row['label'],
                $row['paid_count'],
                number_format($row['paid_total'], 2, '.', ''),
                $row['pending_count'],
                number_format($row['pending_total'], 2, '.', ''),
                $row['bookings'],
            ];
        }

        $rows[] = [
            'الإجمالي',
            $summary['totals']['paid_count'],
            number_format($summary['totals']['paid_total'], 2, '.', ''),
            $summary['totals']['pending_count'],
            number_format($summary['totals']['pending_total'], 2, '.', ''),
            $summary['totals']['bookings'],
        ];

        return CsvExport::stream(
            'revenue-'.($this->from ?? 'all').'-to-'.($this->to ?? 'all').'.csv',
            ['الخدمة', 'حجوزات مدفوعة', 'المحصّل', 'حجوزات معلّقة', 'المعلّق', 'إجمالي الحجوزات'],
            $rows,
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الفترة')
                ->columns(2)
                ->schema([
                    DatePicker::make('from')->label('من تاريخ')->native(false)->live(),
                    DatePicker::make('to')->label('إلى تاريخ')->native(false)->live(),
                ]),
        ]);
    }
}
