<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameBooking extends Model
{
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';

    public const ATTENDANCE_PENDING = 'pending';
    public const ATTENDANCE_ATTENDED = 'attended';

    protected $fillable = [
        'booking_ref', 'game_id', 'game_price_id', 'play_time_id', 'play_date',
        'duration_label', 'duration_minutes', 'price',
        'full_name', 'country_name', 'country_code', 'phone',
        'whatsapp_country_code', 'whatsapp',
        'payment_status', 'attendance_status',
    ];

    protected $casts = [
        'play_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gamePrice(): BelongsTo
    {
        return $this->belongsTo(GamePrice::class);
    }

    public function playTime(): BelongsTo
    {
        return $this->belongsTo(PlayTime::class);
    }

    public static function generateRef(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $ref = 'GM-';
            for ($i = 0; $i < 6; $i++) {
                $ref .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('booking_ref', $ref)->exists());

        return $ref;
    }

    public function qrPayload(): string
    {
        return implode("\n", [
            'Cinema Plus — Game Booking',
            'Ref: ' . $this->booking_ref,
            'Game: ' . ($this->game?->name ?? ''),
            'Duration: ' . $this->duration_label,
            'Total: ' . $this->price . ' LYD',
            'Name: ' . $this->full_name,
            'Phone: ' . $this->country_code . $this->phone,
        ]);
    }
}
