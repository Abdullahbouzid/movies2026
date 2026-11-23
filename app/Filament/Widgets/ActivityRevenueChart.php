<?php

namespace App\Filament\Widgets;

use App\Support\Treasury;
use Filament\Widgets\ChartWidget;

class ActivityRevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'توزيع التحصيل حسب الخدمة';

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $summary = Treasury::summary(today()->startOfMonth()->toDateString(), today()->toDateString());

        $labels = [];
        $data = [];

        foreach ($summary['rows'] as $row) {
            $labels[] = $row['label'];
            $data[] = round($row['paid_total'], 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'المحصّل (' . \App\Support\Money::symbol() . ')',
                    'data' => $data,
                    'backgroundColor' => ['#dc2626', '#0ea5e9', '#16a34a', '#f59e0b'],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
