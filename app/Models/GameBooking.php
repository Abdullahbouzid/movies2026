<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasBookingQr;
use App\Models\Concerns\HasPaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameBooking extends Model
{
    use BelongsToBranch;
    use HasBookingQr;
    use HasPaymentState;

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';

    public const ATTENDANCE_PENDING = 'pending';
    public const ATTENDANCE_ATTENDED = 'attended';

    protected static function refPrefix(): string
    {
        return 'GM';
    }

    public static function branchService(): string
    {
        return 'games';
    }

    protected $fillable = [
        'booking_ref', 'branch_id', 'game_id', 'game_price_id', 'play_time_id', 'device_number', 'play_date',
        'duration_label', 'duration_minutes', 'price', 'qr_path',
        'full_name', 'country_name', 'country_code', 'phone',
        'whatsapp_country_code', 'whatsapp',
        'payment_status', 'paid_at', 'attendance_status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'play_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::PAYMENT_PENDING);
    }

    public function verifyUrl(): string
    {
        return route('games.verify', $this->booking_ref);
    }

    public function qrPayload(): string
    {
        return implode("\n", [
            $this->booking_ref,
            'Cinema Plus — Game Booking',
            'Game: ' . ($this->game?->name ?? ''),
            'Device: ' . ($this->device_number ?: '-'),
            'Slot: ' . ($this->playTime?->start_time_short ?? '') . ' - ' . ($this->playTime?->end_time_short ?? ''),
            'Date: ' . $this->play_date?->format('Y-m-d'),
            'Duration: ' . $this->duration_label,
            'Total: ' . $this->price . ' LYD',
            'Name: ' . $this->full_name,
            'Phone: ' . $this->country_code . $this->phone,
        ]);
    }
}
