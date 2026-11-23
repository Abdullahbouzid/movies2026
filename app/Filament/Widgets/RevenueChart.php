<?php

namespace App\Filament\Widgets;

use App\Support\Treasury;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'التحصيل اليومي — آخر 30 يوماً';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $series = Treasury::dailySeries(now()->subDays(29)->startOfDay(), now()->endOfDay());

        return [
            'datasets' => [
                [
                    'label' => 'المحصّل (' . \App\Support\Money::symbol() . ')',
                    'data' => array_map(fn (float $total) => round($total, 2), array_values($series)),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_map(
                fn (string $day) => Carbon::parse($day)->format('d/m'),
                array_keys($series),
            ),
        ];
    }
}
