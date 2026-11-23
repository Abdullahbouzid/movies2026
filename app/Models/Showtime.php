<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Showtime extends Model
{
    protected $fillable = ['movie_id', 'show_date', 'day_of_week', 'start_time', 'end_time', 'seats_capacity'];

    protected $casts = [
        'show_date' => 'date',
        'seats_capacity' => 'integer',
    ];

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

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $showtime) {
            if ($showtime->show_date) {
                $showtime->day_of_week = $showtime->show_date->dayOfWeek;
            }
        });
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
