<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesBookings;
use App\Models\Club;
use App\Models\ClubBooking;
use App\Models\ClubPackage;
use App\Support\Countries;
use App\Support\HeroShowcase;
use App\Support\SlotAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClubController extends Controller
{
    use HandlesBookings;

    public function index(): View
    {
        $clubs = Club::query()
            ->active()
            ->with('packages')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $weekDays = $this->weekDays();

        return view('club.index', [
            'clubs' => $clubs,
            'weekDays' => $weekDays,
            'countries' => Countries::all(),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'payload' => $this->payload($clubs, $weekDays),
            'showcase' => (new HeroShowcase('#0f766e'))->clubs($clubs)->slides(),
        ]);
    }

    public function book(Request $request, Club $club)
    {
        abort_unless($club->is_active, 404);

        $data = $this->validateCustomer($request) + $request->validate([
            'club_package_id' => ['required', 'exists:club_packages,id'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $package = ClubPackage::whereKey($data['club_package_id'])
            ->where('club_id', $club->id)
            ->where('is_active', true)
            ->first();

        if ($package === null) {
            return $this->bookingFailed($request, ['club_package_id' => 'الباقة المختارة غير متاحة لهذا النادي']);
        }

        $visitDate = Carbon::parse($data['visit_date']);

        if ($this->slotHasPassed($visitDate->toDateString(), $package->start_time)) {
            return $this->bookingFailed($request, ['visit_date' => 'انتهت هذه الفترة، يرجى اختيار يوم أو باقة أخرى']);
        }

        if (! SlotAvailability::clubHasRoom($package, $visitDate->toDateString())) {
            return $this->bookingFailed($request, [
                'club_package_id' => 'اكتمل العدد في هذه الفترة، يرجى اختيار يوم أو باقة أخرى',
            ]);
        }

        $booking = DB::transaction(function () use ($club, $package, $visitDate, $data) {
            // Lock the package row so concurrent requests cannot both pass the
            // capacity check and oversell the slot.
            $package = ClubPackage::query()->lockForUpdate()->find($package->id);

            if (! SlotAvailability::clubHasRoom($package, $visitDate->toDateString())) {
                return null;
            }

            return ClubBooking::create([
                'booking_ref' => ClubBooking::generateRef(),
                'club_id' => $club->id,
                'club_package_id' => $package->id,
                'visit_date' => $visitDate->toDateString(),
                'package_name' => $package->name,
                'start_time' => $package->start_time,
                'end_time' => $package->end_time,
                'people_count' => $package->people_count,
                'price' => $package->price,
                'full_name' => $data['full_name'],
                'country_name' => $data['country_name'],
                'country_code' => $data['country_code'],
                'phone' => $data['phone'],
                'whatsapp_country_code' => $data['whatsapp_country_code'],
                'whatsapp' => $data['whatsapp'],
                'payment_status' => ClubBooking::PAYMENT_PENDING,
                'attendance_status' => ClubBooking::ATTENDANCE_PENDING,
            ]);
        });

        if (! $booking) {
            return $this->bookingFailed($request, [
                'club_package_id' => 'اكتمل العدد في هذه الفترة، يرجى اختيار يوم أو باقة أخرى',
            ]);
        }

        $booking->ensureQr();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'qr_url' => $booking->qrUrl(),
                'club' => $club->name,
                'package' => $package->name,
                'price' => (float) $booking->price,
                'visit_date' => $booking->visit_date->format('Y-m-d'),
                'time' => substr((string) $package->start_time, 0, 5) . ' - ' . substr((string) $package->end_time, 0, 5),
            ]);
        }

        return redirect()->route('site.club')->with('booked_ref', $booking->booking_ref);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Club>  $clubs
     * @return array<string, mixed>
     */
    private function payload($clubs, array $weekDays): array
    {
        return [
            'csrf' => csrf_token(),
            'bookUrl' => url('/clubs/__ID__/book'),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'countries' => Countries::all(),
            'weekDays' => $weekDays,
            'clubs' => $clubs->map(fn (Club $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'location' => $c->location,
                'description' => $c->description,
                'image' => $c->image ? asset('storage/' . $c->image) : null,
                'g1' => $c->colorFrom(),
                'g2' => $c->colorTo(),
                'packages' => $c->packages->map(fn (ClubPackage $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'includes' => $p->includes,
                    'people' => $p->people_count,
                    'start' => $p->start_time_short,
                    'end' => $p->end_time_short,
                    'duration' => $p->durationLabel(),
                    'price' => (float) $p->price,
                    'isDefault' => (bool) $p->is_default,
                ])->values()->all(),
            ])->values(),
        ];
    }
}
