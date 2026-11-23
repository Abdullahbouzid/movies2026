@php
    use App\Support\Money;

    $summary = $this->getSummary();
    $rows = $summary['rows'];
    $totals = $summary['totals'];
    $topMovies = $this->getTopMovies();
    $topGames = $this->getTopGames();
    $series = $this->getDailySeries();
    $peak = count($series) ? max($series) : 0;
    $grand = $totals['paid_total'] + $totals['pending_total'];
    $rate = $grand > 0 ? round($totals['paid_total'] / $grand * 100) : 0;
@endphp

<x-filament-panels::page>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-treasury-stat
            label="المحصّل"
            :value="Money::format($totals['paid_total'])"
            :caption="$totals['paid_count'] . ' حجز مدفوع'"
            icon="heroicon-o-banknotes"
            tone="success"
            :progress="$rate"
        />
        <x-treasury-stat
            label="المعلّق"
            :value="Money::format($totals['pending_total'])"
            :caption="$totals['pending_count'] . ' حجز بانتظار الدفع'"
            icon="heroicon-o-clock"
            tone="warning"
            :progress="$grand > 0 ? 100 - $rate : 0"
        />
        <x-treasury-stat
            label="حجوزات مدفوعة"
            :value="number_format($totals['paid_count'])"
            caption="داخل الفترة المحددة"
            icon="heroicon-o-check-badge"
            tone="gray"
        />
        <x-treasury-stat
            label="إجمالي الحجوزات"
            :value="number_format($totals['bookings'])"
            :caption="'نسبة التحصيل ' . $rate . '%'"
            icon="heroicon-o-ticket"
            tone="primary"
            :progress="$rate"
        />
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">حسب الخدمة</h3>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">توزيع التحصيل على الخدمات.</p>
            </div>
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 dark:bg-white/5 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-2.5 font-semibold">الخدمة</th>
                        <th class="px-5 py-2.5 font-semibold">المحصّل</th>
                        <th class="px-5 py-2.5 font-semibold">المعلّق</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($rows as $row)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-5 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                        <x-filament::icon :icon="$row['icon']" class="h-4 w-4" />
                                    </span>
                                    <span class="text-gray-900 dark:text-white">{{ $row['label'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-2.5 font-medium text-emerald-600 dark:text-emerald-400">{{ Money::format($row['paid_total']) }}</td>
                            <td class="px-5 py-2.5 text-amber-600 dark:text-amber-400">{{ Money::format($row['pending_total']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold dark:bg-white/5">
                    <tr>
                        <td class="px-5 py-2.5 text-gray-900 dark:text-white">الإجمالي</td>
                        <td class="px-5 py-2.5 text-emerald-700 dark:text-emerald-300">{{ Money::format($totals['paid_total']) }}</td>
                        <td class="px-5 py-2.5 text-amber-700 dark:text-amber-300">{{ Money::format($totals['pending_total']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-baseline justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">التحصيل اليومي</h3>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">أعلى يوم: {{ Money::short($peak) }}</p>
                </div>
            </div>
            <div class="flex h-44 items-end gap-1 px-5 py-4" dir="ltr">
                @foreach ($series as $day => $total)
                    @php $height = $peak > 0 ? max(2, round($total / $peak * 100)) : 2; @endphp
                    <div class="group relative flex-1 rounded-t bg-primary-500/60 transition hover:bg-primary-500"
                        style="height: {{ $height }}%"
                        title="{{ $day }}: {{ Money::format($total) }}"></div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @foreach ([['أفضل الأفلام', $topMovies, 'heroicon-o-film'], ['أفضل الألعاب', $topGames, 'heroicon-o-puzzle-piece']] as [$heading, $items, $icon])
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <x-filament::icon :icon="$icon" class="h-4 w-4 text-gray-400" />
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $heading }}</h3>
                </div>
                @if (empty($items))
                    <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">لا توجد بيانات في هذه الفترة.</p>
                @else
                    <table class="w-full text-right text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($items as $index => $item)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="w-8 px-5 py-2.5 text-xs tabular-nums text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2.5 text-gray-900 dark:text-white">{{ $item['name'] }}</td>
                                    <td class="px-5 py-2.5 text-xs text-gray-500 dark:text-gray-400">{{ $item['bookings'] }} حجز</td>
                                    <td class="px-5 py-2.5 text-left font-medium tabular-nums text-gray-900 dark:text-white">
                                        {{ Money::format($item['total']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach
    </div>

    {{ $this->content }}
</x-filament-panels::page>
