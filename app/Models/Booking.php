<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasBookingQr;
use App\Models\Concerns\HasPaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
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
        return 'CP';
    }

    public static function branchService(): string
    {
        return 'cinema';
    }

    protected $fillable = [
        'booking_ref', 'branch_id', 'movie_id', 'showtime_id', 'package_id', 'seats', 'show_date',
        'full_name', 'birthdate', 'country_name', 'country_code', 'phone',
        'whatsapp_country_code', 'whatsapp', 'price', 'qr_path',
        'payment_status', 'paid_at', 'attendance_status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'birthdate' => 'date',
        'show_date' => 'date',
        'price' => 'decimal:2',
        'seats' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

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
        return route('movies.verify', $this->booking_ref);
    }

    protected function whatsappSummary(): string
    {
        return implode(' · ', array_filter([
            $this->movie?->name,
            $this->package?->name,
            $this->show_date?->format('Y-m-d'),
            $this->showtime?->start_time_short,
            $this->seats ? $this->seats . ' مقعد' : null,
        ]));
    }

    public function qrPayload(): string
    {
        return implode("\n", [
            $this->booking_ref,
            'Cinema Plus Ticket',
            'Movie: ' . ($this->movie?->name ?? ''),
            'Package: ' . ($this->package?->name ?? ''),
            'Date: ' . $this->show_date?->format('Y-m-d'),
            'Time: ' . ($this->showtime?->start_time_short ?? ''),
            'Name: ' . $this->full_name,
            'Phone: ' . $this->country_code . $this->phone,
        ]);
    }
}
