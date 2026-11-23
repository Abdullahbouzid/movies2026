<?php

namespace App\Rules;

use App\Support\PlayTimeConflict;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Field-level companion to the guard on the PlayTime model: the model stops
 * overlapping rows from any code path, while this rule reports the clash on
 * the form field the user actually edits.
 */
class PlayTimeConflictRule implements ValidationRule
{
    public function __construct(
        private mixed $gameId,
        private mixed $dayOfWeek,
        private mixed $startTime,
        private ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->gameId || $this->dayOfWeek === null || $this->dayOfWeek === '') {
            return;
        }

        $clash = PlayTimeConflict::clash(
            (int) $this->gameId,
            $this->dayOfWeek,
            $this->startTime,
            $value,
            $this->ignoreId,
        );

        if ($clash) {
            $fail(sprintf(
                'يتعارض هذا الوقت مع وقت آخر لنفس اللعبة (%s - %s).',
                $clash->start_time_short,
                $clash->end_time_short,
            ));
        }
    }
}
