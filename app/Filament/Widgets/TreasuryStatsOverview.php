<?php

namespace App\Filament\Widgets;

use App\Support\Money;
use App\Support\Treasury;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TreasuryStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'الخزينة';

    protected ?string $description = 'أرقام اليوم مقابل إجمالي الشهر الحالي.';

    protected function getStats(): array
    {
        $today = today()->toDateString();
        $monthStart = today()->startOfMonth()->toDateString();

        $todaySummary = Treasury::summary($today, $today)['totals'];
        $monthSummary = Treasury::summary($monthStart, $today)['totals'];

        $trend = array_values(array_map(
            fn (float $total) => round($total, 2),
            Treasury::dailySeries(now()->subDays(13), now()),
        ));

        return [
            Stat::make('محصّل اليوم', Money::format($todaySummary['paid_total']))
                ->description($todaySummary['paid_count'].' حجز مدفوع')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($trend)
                ->chartColor('success')
                ->color('success'),

            Stat::make('محصّل هذا الشهر', Money::format($monthSummary['paid_total']))
                ->description($monthSummary['paid_count'].' حجز مدفوع')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('معلّق غير محصّل', Money::format($monthSummary['pending_total']))
                ->description($monthSummary['pending_count'].' حجز بانتظار الدفع')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('إجمالي الحجوزات', (string) $monthSummary['bookings'])
                ->description('كل الخدمات هذا الشهر')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
        ];
    }
}
