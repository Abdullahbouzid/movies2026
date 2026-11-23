<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use App\Models\Concerns\HasBookingQr;
use App\Models\Concerns\HasPaymentState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBooking extends Model
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
        return 'EV';
    }

    public static function branchService(): string
    {
        return 'events';
    }

    protected $fillable = [
        'booking_ref', 'branch_id', 'event_package_id', 'event_date', 'start_time', 'end_time',
        'package_name', 'people_count', 'price', 'qr_path', 'notes',
        'full_name', 'country_name', 'country_code', 'phone',
        'whatsapp_country_code', 'whatsapp',
        'payment_status', 'paid_at', 'attendance_status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'event_date' => 'date',
        'price' => 'decimal:2',
        'people_count' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(EventPackage::class, 'event_package_id');
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
        return route('events.verify', $this->booking_ref);
    }

    public function qrPayload(): string
    {
        return implode("\n", [
            $this->booking_ref,
            'Cinema Plus — Event Booking',
            'Package: ' . $this->package_name,
            'Date: ' . $this->event_date?->format('Y-m-d'),
            'Time: ' . substr((string) $this->start_time, 0, 5) . ' - ' . substr((string) $this->end_time, 0, 5),
            'People: ' . $this->people_count,
            'Total: ' . $this->price . ' LYD',
            'Name: ' . $this->full_name,
            'Phone: ' . $this->country_code . $this->phone,
        ]);
    }
}
