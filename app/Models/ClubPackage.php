<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubPackage extends Model
{
    protected $fillable = [
        'club_id', 'name', 'includes', 'people_count',
        'start_time', 'end_time', 'price', 'capacity', 'is_default', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'people_count' => 'integer',
        'capacity' => 'integer',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function getStartTimeShortAttribute(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getEndTimeShortAttribute(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    public function durationLabel(): string
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);

        if ($start === false || $end === false) {
            return '';
        }

        $minutes = (int) round(($end - $start) / 60);
        if ($minutes < 0) {
            $minutes += 24 * 60;
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return trim(($hours ? $hours . ' ساعة' : '') . ($rest ? ' ' . $rest . ' دقيقة' : ''));
    }
}
