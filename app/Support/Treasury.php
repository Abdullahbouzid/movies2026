<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\ClubBooking;
use App\Models\EventBooking;
use App\Models\GameBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for money figures.
 *
 * Every booking type stores the same payment columns, so revenue is derived
 * from the booking tables rather than duplicated into a ledger that could drift.
 * "Collected" always means `payment_status = paid` dated by `paid_at`.
 */
class Treasury
{
    /**
     * Activity key => [label, model, icon, color, service].
     *
     * @return array<string, array{label: string, model: class-string<Model>, icon: string, color: string, service: string}>
     */
    public static function activities(): array
    {
        return [
            'cinema' => [
                'label' => 'تذاكر السينما',
                'model' => Booking::class,
                'icon' => 'heroicon-o-film',
                'color' => 'danger',
                'service' => 'cinema',
            ],
            'games' => [
                'label' => 'صالة الألعاب',
                'model' => GameBooking::class,
                'icon' => 'heroicon-o-puzzle-piece',
                'color' => 'info',
                'service' => 'games',
            ],
            'club' => [
                'label' => 'النادي الاجتماعي',
                'model' => ClubBooking::class,
                'icon' => 'heroicon-o-user-group',
                'color' => 'success',
                'service' => 'club',
            ],
            'events' => [
                'label' => 'مناسبات المقهى',
                'model' => EventBooking::class,
                'icon' => 'heroicon-o-cake',
                'color' => 'warning',
                'service' => 'events',
            ],
        ];
    }

    public static function model(string $activity): ?string
    {
        return self::activities()[$activity]['model'] ?? null;
    }

    public static function label(string $activity): string
    {
        return self::activities()[$activity]['label'] ?? $activity;
    }

    /**
     * Revenue figures for one activity inside a date window.
     *
     * @return array{paid_count: int, paid_total: float, pending_count: int, pending_total: float, bookings: int}
     */
    public static function forActivity(string $activity, ?string $from = null, ?string $to = null, ?int $branchId = null): array
    {
        $model = self::model($activity);

        if ($model === null) {
            return self::emptyFigures();
        }

        /** @var Builder $query */
        $query = $model::query()->when($branchId, fn (Builder $q, $id) => $q->where('branch_id', $id));

        $paid = (clone $query)->paidBetween($from, $to);
        $pending = (clone $query)->where('payment_status', $model::PAYMENT_PENDING);

        return [
            'paid_count' => (clone $paid)->count(),
            'paid_total' => (float) (clone $paid)->sum('price'),
            'pending_count' => (clone $pending)->count(),
            'pending_total' => (float) (clone $pending)->sum('price'),
            'bookings' => (clone $query)->count(),
        ];
    }

    /**
     * Every activity plus a grand total for the same window.
     *
     * @return array{rows: array<string, array<string, mixed>>, totals: array<string, float|int>}
     */
    public static function summary(?string $from = null, ?string $to = null, ?int $branchId = null): array
    {
        $rows = [];
        $totals = [
            'paid_count' => 0,
            'paid_total' => 0.0,
            'pending_count' => 0,
            'pending_total' => 0.0,
            'bookings' => 0,
        ];

        foreach (self::activities() as $key => $meta) {
            $figures = self::forActivity($key, $from, $to, $branchId);

            $rows[$key] = [...$meta, ...$figures];

            foreach ($totals as $field => $value) {
                $totals[$field] = $value + $figures[$field];
            }
        }

        return ['rows' => $rows, 'totals' => $totals];
    }

    /**
     * Collected cash per day, for the revenue chart. Missing days are zero-filled
     * so the line does not skip quiet days.
     *
     * @return array<string, float>
     */
    public static function dailySeries(Carbon $from, Carbon $to, ?string $activity = null): array
    {
        $series = [];

        for ($day = $from->copy()->startOfDay(); $day->lessThanOrEqualTo($to); $day->addDay()) {
            $series[$day->toDateString()] = 0.0;
        }

        $activities = $activity !== null
            ? array_intersect_key(self::activities(), [$activity => true])
            : self::activities();

        foreach ($activities as $meta) {
            $model = $meta['model'];

            $rows = $model::query()
                ->paidBetween($from->toDateString(), $to->toDateString())
                ->selectRaw('date(paid_at) as day, sum(price) as total')
                ->groupBy('day')
                ->pluck('total', 'day');

            foreach ($rows as $day => $total) {
                $key = (string) Carbon::parse($day)->toDateString();

                if (array_key_exists($key, $series)) {
                    $series[$key] += (float) $total;
                }
            }
        }

        return $series;
    }

    /** Collected total for one activity today, used by the dashboard cards. */
    public static function todayTotal(?string $activity = null): float
    {
        $today = today()->toDateString();

        if ($activity !== null) {
            return self::forActivity($activity, $today, $today)['paid_total'];
        }

        return (float) self::summary($today, $today)['totals']['paid_total'];
    }

    /**
     * Pending collections older than the given days — money owed that nobody
     * has chased yet. Useful as a dashboard warning.
     */
    public static function stalePending(int $days = 7): array
    {
        $cutoff = now()->subDays($days);

        $rows = [];

        foreach (self::activities() as $key => $meta) {
            $model = $meta['model'];

            $query = $model::query()
                ->where('payment_status', $model::PAYMENT_PENDING)
                ->where('created_at', '<=', $cutoff);

            $rows[$key] = [
                'label' => $meta['label'],
                'count' => (clone $query)->count(),
                'total' => (float) (clone $query)->sum('price'),
            ];
        }

        return $rows;
    }

    /**
     * Combined booking rows for the main treasury table, newest collection first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function recentPayments(int $limit = 50, ?string $activity = null): Collection
    {
        $activities = $activity !== null
            ? array_intersect_key(self::activities(), [$activity => true])
            : self::activities();

        $rows = collect();

        foreach ($activities as $key => $meta) {
            $model = $meta['model'];

            $model::query()
                ->where('payment_status', $model::PAYMENT_PAID)
                ->latest('paid_at')
                ->limit($limit)
                ->get()
                ->each(function (Model $booking) use ($rows, $key, $meta) {
                    $rows->push([
                        'activity' => $key,
                        'activity_label' => $meta['label'],
                        'reference' => $booking->booking_ref,
                        'customer' => $booking->full_name,
                        'price' => (float) $booking->price,
                        'paid_at' => $booking->paid_at,
                        'branch' => $booking->branch?->name,
                    ]);
                });
        }

        return $rows->sortByDesc('paid_at')->take($limit)->values();
    }

    /** @return array{paid_count: int, paid_total: float, pending_count: int, pending_total: float, bookings: int} */
    private static function emptyFigures(): array
    {
        return [
            'paid_count' => 0,
            'paid_total' => 0.0,
            'pending_count' => 0,
            'pending_total' => 0.0,
            'bookings' => 0,
        ];
    }
}
