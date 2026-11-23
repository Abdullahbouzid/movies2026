<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Payment state shared by every booking type.
 *
 * `paid_at` is stamped the moment a booking flips to paid, so the treasury can
 * report cash by the date it was actually collected instead of the booking date.
 */
trait HasPaymentState
{
    /**
     * Stamped on the model rather than at each call site, so payments confirmed
     * from a resource action, an edit form or a seeder all record a cash-in date.
     */
    protected static function bootHasPaymentState(): void
    {
        static::saving(function (self $booking): void {
            if ($booking->payment_status === static::PAYMENT_PAID) {
                $booking->paid_at ??= now();

                return;
            }

            $booking->paid_at = null;
        });
    }

    public function markPaid(): void
    {
        if ($this->payment_status === static::PAYMENT_PAID) {
            return;
        }

        $this->update(['payment_status' => static::PAYMENT_PAID]);
    }

    public function markPending(): void
    {
        if ($this->payment_status === static::PAYMENT_PENDING) {
            return;
        }

        $this->update(['payment_status' => static::PAYMENT_PENDING]);
    }

    public function scopePaidBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->where('payment_status', static::PAYMENT_PAID)
            ->when($from, fn (Builder $q, $date) => $q->whereDate('paid_at', '>=', $date))
            ->when($to, fn (Builder $q, $date) => $q->whereDate('paid_at', '<=', $date));
    }
}
