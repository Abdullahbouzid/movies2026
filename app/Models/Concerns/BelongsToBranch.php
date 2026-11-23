<?php

namespace App\Models\Concerns;

use App\Models\Branch;

/**
 * Keeps every booking attached to a branch that actually offers the service.
 *
 * Public booking flows never ask the customer to pick a branch, so a booking
 * created without one is attached to the main branch, falling back to the
 * first active branch offering the service.
 */
trait BelongsToBranch
{
    /** Service key from Branch::SERVICES that this booking belongs to. */
    abstract public static function branchService(): string;

    protected static function bootBelongsToBranch(): void
    {
        static::creating(function (self $booking): void {
            if (filled($booking->branch_id)) {
                return;
            }

            $booking->branch_id = static::defaultBranchId();
        });
    }

    protected static function defaultBranchId(): ?int
    {
        $service = static::branchService();

        $branches = Branch::query()->active()->orderByDesc('is_main')->orderBy('sort_order')->get();

        $match = $branches->first(fn (Branch $branch) => $branch->offers($service));

        return $match?->getKey() ?? $branches->firstWhere('is_main', true)?->getKey();
    }
}
