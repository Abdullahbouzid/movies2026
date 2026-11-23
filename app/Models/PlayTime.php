<?php

namespace App\Models;

use App\Support\PlayTimeConflict;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class PlayTime extends Model
{
    protected $fillable = ['game_id', 'day_of_week', 'start_time', 'end_time', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $slot): void {
            $clash = PlayTimeConflict::clash(
                (int) $slot->game_id,
                (int) $slot->day_of_week,
                $slot->start_time,
                $slot->end_time,
                $slot->getKey(),
            );

            if ($clash) {
                throw ValidationException::withMessages([
                    'end_time' => sprintf(
                        'يتعارض هذا الوقت مع وقت آخر لنفس اللعبة (%s - %s).',
                        $clash->start_time_short,
                        $clash->end_time_short,
                    ),
                ]);
            }
        });
    }

    /** Public schedule labels are English per the cinema's request. */
    public static array $dayNames = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public static array $dayNamesAr = [
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
        6 => 'السبت',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(GameBooking::class);
    }

    public function getDayNameAttribute(): string
    {
        return self::$dayNames[$this->day_of_week] ?? '';
    }

    public function getStartTimeShortAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndTimeShortAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }
}
