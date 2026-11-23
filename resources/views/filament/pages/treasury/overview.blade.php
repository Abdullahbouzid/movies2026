@php
    use App\Support\Money;

    $summary = $this->getSummary();
    $rows = $summary['rows'];
    $totals = $summary['totals'];

    $grand = $totals['paid_total'] + $totals['pending_total'];
    $rate = $grand > 0 ? round($totals['paid_total'] / $grand * 100) : 0;
    $average = $totals['bookings'] > 0 ? $grand / $totals['bookings'] : 0;
@endphp

<x-filament-panels::page>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-treasury-stat
            label="إجمالي المحصّل"
            :value="Money::format($totals['paid_total'])"
            :caption="$totals['paid_count'] . ' حجز مدفوع'"
            icon="heroicon-o-banknotes"
            tone="success"
            :progress="$rate"
        />

        <x-treasury-stat
            label="مبالغ معلّقة"
            :value="Money::format($totals['pending_total'])"
            :caption="$totals['pending_count'] . ' حجز بانتظار الدفع'"
            icon="heroicon-o-clock"
            tone="warning"
            :progress="$grand > 0 ? 100 - $rate : 0"
        />

        <x-treasury-stat
            label="إجمالي الحجوزات"
            :value="number_format($totals['bookings'])"
            caption="كل الخدمات في الفترة"
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

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-white/10">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">تفصيل الخدمات</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">المحصّل مقابل المعلّق لكل خدمة خلال الفترة المحددة.</p>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                {{ count($rows) }} خدمات
            </span>
        </div>

        <table class="w-full text-right text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">الخدمة</th>
                    <th class="px-5 py-3 font-semibold">المحصّل</th>
                    <th class="px-5 py-3 font-semibold">المعلّق</th>
                    <th class="px-5 py-3 font-semibold">الحجوزات</th>
                    <th class="px-5 py-3 font-semibold">التحصيل</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($rows as $key => $row)
                    @php
                        $rowTotal = $row['paid_total'] + $row['pending_total'];
                        $rowRate = $rowTotal > 0 ? round($row['paid_total'] / $rowTotal * 100) : 0;
                    @endphp
                    <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                    <x-filament::icon :icon="$row['icon']" class="h-4 w-4" />
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $row['label'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-medium text-emerald-600 dark:text-emerald-400">
                            {{ Money::format($row['paid_total']) }}
                        </td>
                        <td class="px-5 py-3 text-amber-600 dark:text-amber-400">
                            {{ Money::format($row['pending_total']) }}
                        </td>
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ number_format($row['bookings']) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $rowRate }}%"></div>
                                </div>
                                <span class="text-xs tabular-nums text-gray-500 dark:text-gray-400">{{ $rowRate }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-semibold dark:bg-white/5">
                <tr>
                    <td class="px-5 py-3 text-gray-900 dark:text-white">الإجمالي</td>
                    <td class="px-5 py-3 text-emerald-700 dark:text-emerald-300">{{ Money::format($totals['paid_total']) }}</td>
                    <td class="px-5 py-3 text-amber-700 dark:text-amber-300">{{ Money::format($totals['pending_total']) }}</td>
                    <td class="px-5 py-3 text-gray-900 dark:text-white">{{ number_format($totals['bookings']) }}</td>
                    <td class="px-5 py-3 text-gray-900 dark:text-white">{{ $rate }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{ $this->content }}
</x-filament-panels::page>
