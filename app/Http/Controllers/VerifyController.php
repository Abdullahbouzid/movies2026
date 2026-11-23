<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ClubBooking;
use App\Models\EventBooking;
use App\Models\GameBooking;
use App\Support\Mask;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public verification pages reached by scanning a booking QR code.
 * Staff can confirm the customer identity, payment state and attendance.
 */
class VerifyController extends Controller
{
    /**
     * Booking models reachable by QR, keyed by the public `{type}` route segment.
     *
     * @return array<string, class-string>
     */
    private static function models(): array
    {
        return [
            'ticket' => Booking::class,
            // Legacy alias: links generated before the segment was renamed.
            'cinema' => Booking::class,
            'game' => GameBooking::class,
            'club' => ClubBooking::class,
            'event' => EventBooking::class,
        ];
    }

    /**
     * Mark a booking as attended. Staff scan the QR and tap check-in; the
     * reference itself is the credential, so CSRF plus a throttle stand in
     * for a login that door staff do not have.
     */
    public function checkIn(string $type, string $ref): RedirectResponse
    {
        $model = self::models()[$type] ?? null;
        abort_if($model === null, 404);

        $booking = $model::where('booking_ref', $ref)->firstOrFail();

        if ($booking->attendance_status !== $model::ATTENDANCE_ATTENDED) {
            $booking->update(['attendance_status' => $model::ATTENDANCE_ATTENDED]);
        }

        return redirect()
            ->route("{$this->routePrefix($type)}.verify", $booking->booking_ref)
            ->with('checked_in', true);
    }

    private function routePrefix(string $type): string
    {
        return match ($type) {
            'ticket', 'cinema' => 'movies',
            'game' => 'games',
            'club' => 'clubs',
            'event' => 'events',
        };
    }

    public function movie(string $ref): View
    {
        $booking = Booking::with(['movie', 'package', 'showtime'])
            ->where('booking_ref', $ref)
            ->firstOrFail();

        return view('verify.booking', [
            'type' => 'ticket',
            'title' => 'تذكرة سينما',
            'reference' => $booking->booking_ref,
            'customer' => $booking->full_name,
            'phone' => Mask::phone($booking->country_code . $booking->phone),
            'whatsapp' => Mask::phone($booking->whatsapp_country_code . $booking->whatsapp),
            'item' => $booking->movie?->name,
            'package' => $booking->package?->name,
            'date' => $booking->show_date?->format('d/m/Y'),
            'time' => $booking->showtime?->start_time_short,
            'people' => null,
            'price' => (float) $booking->price,
            'payment_status' => $booking->payment_status,
            'attendance_status' => $booking->attendance_status,
            'created_at' => $booking->created_at,
        ]);
    }

    public function game(string $ref): View
    {
        $booking = GameBooking::with(['game', 'playTime'])
            ->where('booking_ref', $ref)
            ->firstOrFail();

        return view('verify.booking', [
            'type' => 'game',
            'title' => 'حجز صالة ألعاب',
            'reference' => $booking->booking_ref,
            'customer' => $booking->full_name,
            'phone' => Mask::phone($booking->country_code . $booking->phone),
            'whatsapp' => Mask::phone($booking->whatsapp_country_code . $booking->whatsapp),
            'item' => $booking->game?->name,
            'package' => $booking->duration_label,
            'date' => $booking->play_date?->format('d/m/Y'),
            'time' => $booking->playTime
                ? $booking->playTime->start_time_short . ' - ' . $booking->playTime->end_time_short
                : null,
            'people' => null,
            'price' => (float) $booking->price,
            'payment_status' => $booking->payment_status,
            'attendance_status' => $booking->attendance_status,
            'created_at' => $booking->created_at,
            'extra' => ['الجهاز' => $booking->device_number ? 'جهاز ' . $booking->device_number : null],
        ]);
    }

    public function club(string $ref): View
    {
        $booking = ClubBooking::with('club')->where('booking_ref', $ref)->firstOrFail();

        return view('verify.booking', [
            'type' => 'club',
            'title' => 'حجز النادي الاجتماعي',
            'reference' => $booking->booking_ref,
            'customer' => $booking->full_name,
            'phone' => Mask::phone($booking->country_code . $booking->phone),
            'whatsapp' => Mask::phone($booking->whatsapp_country_code . $booking->whatsapp),
            'item' => $booking->club?->name,
            'package' => $booking->package_name,
            'date' => $booking->visit_date?->format('d/m/Y'),
            'time' => substr((string) $booking->start_time, 0, 5) . ' - ' . substr((string) $booking->end_time, 0, 5),
            'people' => $booking->people_count,
            'price' => (float) $booking->price,
            'payment_status' => $booking->payment_status,
            'attendance_status' => $booking->attendance_status,
            'created_at' => $booking->created_at,
        ]);
    }

    public function event(string $ref): View
    {
        $booking = EventBooking::where('booking_ref', $ref)->firstOrFail();

        return view('verify.booking', [
            'type' => 'event',
            'title' => 'حجز مناسبات المقهى',
            'reference' => $booking->booking_ref,
            'customer' => $booking->full_name,
            'phone' => Mask::phone($booking->country_code . $booking->phone),
            'whatsapp' => Mask::phone($booking->whatsapp_country_code . $booking->whatsapp),
            'item' => 'مقهى الكلية',
            'package' => $booking->package_name,
            'date' => $booking->event_date?->format('d/m/Y'),
            'time' => substr((string) $booking->start_time, 0, 5) . ' - ' . substr((string) $booking->end_time, 0, 5),
            'people' => $booking->people_count,
            'price' => (float) $booking->price,
            'payment_status' => $booking->payment_status,
            'attendance_status' => $booking->attendance_status,
            'created_at' => $booking->created_at,
        ]);
    }
}
