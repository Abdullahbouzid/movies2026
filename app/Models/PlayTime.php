<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayTime extends Model
{
    protected $fillable = ['game_id', 'day_of_week', 'start_time', 'end_time', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static array $dayNames = [
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
