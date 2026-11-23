<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBookings;
use App\Models\EventBooking;
use App\Models\EventPackage;
use App\Support\Countries;
use App\Support\HeroShowcase;
use App\Support\SlotAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventController extends Controller
{
    use HandlesBookings;

    /** Cafe events run in fixed three-hour blocks. */
    private const SLOTS = [
        ['12:00', '15:00'],
        ['15:00', '18:00'],
        ['18:00', '21:00'],
        ['21:00', '00:00'],
    ];

    public function index(): View
    {
        $packages = EventPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $weekDays = $this->weekDays();

        return view('events.index', [
            'packages' => $packages,
            'weekDays' => $weekDays,
            'countries' => Countries::all(),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'payload' => $this->payload($packages, $weekDays),
            'showcase' => (new HeroShowcase('#b45309'))->events($packages)->slides(),
        ]);
    }

    public function book(Request $request)
    {
        $data = $this->validateCustomer($request) + $request->validate([
            'event_package_id' => ['required', 'exists:event_packages,id'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'string', 'max:5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $package = EventPackage::whereKey($data['event_package_id'])->where('is_active', true)->first();

        if ($package === null) {
            return $this->bookingFailed($request, ['event_package_id' => 'الباقة المختارة غير متاحة']);
        }

        $slot = collect(self::SLOTS)->firstWhere(0, $data['start_time']);
        if ($slot === null) {
            return $this->bookingFailed($request, ['start_time' => 'الفترة المختارة غير متاحة']);
        }

        $eventDate = Carbon::parse($data['event_date']);

        if ($this->slotHasPassed($eventDate->toDateString(), $slot[0])) {
            return $this->bookingFailed($request, ['start_time' => 'انتهت هذه الفترة، يرجى اختيار فترة أخرى']);
        }

        if (! SlotAvailability::eventHasRoom($package, $eventDate->toDateString(), $slot[0])) {
            return $this->bookingFailed($request, [
                'start_time' => 'اكتمل العدد في هذه الفترة، يرجى اختيار فترة أخرى',
            ]);
        }

        $booking = DB::transaction(function () use ($package, $eventDate, $slot, $data) {
            $package = EventPackage::query()->lockForUpdate()->find($package->id);

            if (! SlotAvailability::eventHasRoom($package, $eventDate->toDateString(), $slot[0])) {
                return null;
            }

            return EventBooking::create([
                'booking_ref' => EventBooking::generateRef(),
                'event_package_id' => $package->id,
                'event_date' => $eventDate->toDateString(),
                'start_time' => $slot[0],
                'end_time' => $slot[1],
                'package_name' => $package->name,
                'people_count' => $package->people_count,
                'price' => $package->price,
                'notes' => $data['notes'] ?? null,
                'full_name' => $data['full_name'],
                'country_name' => $data['country_name'],
                'country_code' => $data['country_code'],
                'phone' => $data['phone'],
                'whatsapp_country_code' => $data['whatsapp_country_code'],
                'whatsapp' => $data['whatsapp'],
                'payment_status' => EventBooking::PAYMENT_PENDING,
                'attendance_status' => EventBooking::ATTENDANCE_PENDING,
            ]);
        });

        if (! $booking) {
            return $this->bookingFailed($request, [
                'start_time' => 'اكتمل العدد في هذه الفترة، يرجى اختيار فترة أخرى',
            ]);
        }

        $booking->ensureQr();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'qr_url' => $booking->qrUrl(),
                'package' => $package->name,
                'price' => (float) $booking->price,
                'event_date' => $booking->event_date->format('Y-m-d'),
                'time' => $slot[0] . ' - ' . $slot[1],
            ]);
        }

        return redirect()->route('site.cafe')->with('booked_ref', $booking->booking_ref);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, EventPackage>  $packages
     * @return array<string, mixed>
     */
    private function payload($packages, array $weekDays): array
    {
        return [
            'csrf' => csrf_token(),
            'bookUrl' => url('/events/book'),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'countries' => Countries::all(),
            'weekDays' => $weekDays,
            'slots' => collect(self::SLOTS)->map(fn ($s) => ['start' => $s[0], 'end' => $s[1]])->all(),
            'packages' => $packages->map(fn (EventPackage $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'includes' => $p->includes,
                'people' => $p->people_count,
                'price' => (float) $p->price,
                'image' => $p->image ? asset('storage/' . $p->image) : null,
                'isDefault' => (bool) $p->is_default,
            ])->values()->all(),
        ];
    }
}
