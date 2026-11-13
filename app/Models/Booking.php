<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';

    public const ATTENDANCE_PENDING = 'pending';
    public const ATTENDANCE_ATTENDED = 'attended';

    protected $fillable = [
        'booking_ref', 'movie_id', 'showtime_id', 'package_id', 'show_date',
        'full_name', 'birthdate', 'country_name', 'country_code', 'phone',
        'whatsapp_country_code', 'whatsapp', 'price',
        'payment_status', 'attendance_status',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'show_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public static function generateRef(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $ref = 'CP-';
            for ($i = 0; $i < 6; $i++) {
                $ref .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('booking_ref', $ref)->exists());

        return $ref;
    }

    public function qrPayload(): string
    {
        return implode("\n", [
            'Cinema Plus Ticket',
            'Ref: ' . $this->booking_ref,
            'Movie: ' . ($this->movie?->name ?? ''),
            'Package: ' . ($this->package?->name ?? ''),
            'Date: ' . $this->show_date?->format('Y-m-d'),
            'Time: ' . ($this->showtime?->start_time_short ?? ''),
            'Name: ' . $this->full_name,
            'Phone: ' . $this->country_code . $this->phone,
        ]);
    }
}
