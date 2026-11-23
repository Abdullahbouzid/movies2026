@php
    use App\Support\Money;

    $figures = $this->getFigures();
    $meta = $this->getActivityMeta();

    $total = $figures['paid_total'] + $figures['pending_total'];
    $rate = $total > 0 ? round($figures['paid_total'] / $total * 100) : 0;
    $average = $figures['bookings'] > 0 ? $total / $figures['bookings'] : 0;
@endphp

<x-filament-panels::page>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-treasury-stat
            label="المحصّل خلال الفترة"
            :value="Money::format($figures['paid_total'])"
            :caption="$figures['paid_count'] . ' حجز مدفوع'"
            :icon="$meta['icon'] ?? 'heroicon-o-banknotes'"
            tone="success"
            :progress="$rate"
        />

        <x-treasury-stat
            label="قيد الانتظار"
            :value="Money::format($figures['pending_total'])"
            :caption="$figures['pending_count'] . ' حجز بانتظار الدفع'"
            icon="heroicon-o-clock"
            tone="warning"
            :progress="$total > 0 ? 100 - $rate : 0"
        />

        <x-treasury-stat
            label="إجمالي الحجوزات"
            :value="number_format($figures['bookings'])"
            caption="كل الحالات"
            icon="heroicon-o-ticket"
            tone="gray"
        />

        <x-treasury-stat
            label="نسبة التحصيل"
            :value="$rate . '%'"
            :caption="'متوسط الحجز ' . Money::short($average)"
            icon="heroicon-o-chart-pie"
            tone="primary"
            :progress="$rate"
        />
    </div>

    {{ $this->content }}
</x-filament-panels::page>
