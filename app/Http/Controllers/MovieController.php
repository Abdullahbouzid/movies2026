<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Support\Countries;
use App\Support\HeroShowcase;
use App\Support\SeatAvailability;
use App\Http\Controllers\Concerns\HandlesBookings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MovieController extends Controller
{
    use HandlesBookings;

    public function index(Request $request): View
    {
        $horizon = today()->addDays(6);

        $movies = Movie::query()
            ->available()
            ->with([
                'genre',
                'packages',
                'showtimes' => fn ($q) => $q
                    ->whereNotNull('show_date')
                    ->whereBetween('show_date', [today()->toDateString(), $horizon->toDateString()])
                    ->orderBy('start_time'),
            ])
            ->orderByDesc('rating')
            ->get();

        $packages = Package::orderBy('id')->get();
        $weekDays = $this->weekDays();
        $todayIndex = collect($weekDays)->search(fn ($d) => $d['isToday']);
        $currentDay = $todayIndex === false ? 0 : $todayIndex;

        $weeklyShowings = $movies->sum(fn ($m) => $m->showtimes->count());

        return view('movies.index', [
            'movies' => $movies,
            'genres' => Genre::orderBy('name')->get(),
            'packages' => $packages,
            'weekDays' => $weekDays,
            'currentDay' => $currentDay,
            'weeklyShowings' => $weeklyShowings,
            'comingSoon' => Movie::query()->comingSoon()->with('genre')->orderBy('release_date')->get(),
            'countries' => Countries::all(),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'payload' => $this->payload($movies, $packages, $weekDays, $currentDay),
            'showcase' => (new HeroShowcase())->movies($movies)->slides(),
        ]);
    }

    /**
     * JSON payload consumed by public/js/movies.js.
     *
     * @param  \Illuminate\Support\Collection<int, Movie>  $movies
     * @param  \Illuminate\Support\Collection<int, Package>  $packages
     * @param  array<int, array{name:string, nameAr:string, date:string, iso:string, dow:int, isToday:bool}>  $weekDays
     * @return array<string, mixed>
     */
    private function payload($movies, $packages, array $weekDays, int $currentDay): array
    {
        $availability = SeatAvailability::weekGrid($movies, $weekDays);

        return [
            'currentDay' => $currentDay,
            'dayNames' => array_values(Showtime::$dayNames),
            'dayNamesAr' => array_values(Showtime::$dayNamesAr),
            'weekDays' => $weekDays,
            'defaultCountry' => config('cinema.default_country', '+218'),
            'csrf' => csrf_token(),
            'bookUrl' => url('/movies/__ID__/book'),
            'defaultMovieId' => $movies->firstWhere('name', 'النجم الأخير')?->id ?? $movies->first()?->id,
            'countries' => Countries::all(),
            'packages' => $packages->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'includes' => $p->includes,
                'seats' => $p->seats_count,
                'isVip' => str_contains($p->name, 'VIP'),
            ])->values(),
            'movies' => $movies->map(function (Movie $m) use ($weekDays, $availability) {
                $byDay = [];
                foreach ($weekDays as $i => $day) {
                    $byDay[$i] = $m->showtimes
                        ->filter(fn ($s) => $s->show_date?->toDateString() === $day['iso'])
                        ->sortBy('start_time')
                        // Duplicate rows for one slot would otherwise repeat the
                        // same time in the customer's dropdown.
                        ->unique(fn ($s) => substr((string) $s->start_time, 0, 5))
                        ->map(fn ($s) => [
                            'id' => $s->id,
                            'time' => $s->start_time_short,
                            'end' => $s->end_time_short,
                            'seatsLeft' => $availability[$m->id][$day['iso']][$s->id] ?? (int) $s->seats_capacity,
                        ])
                        ->values()
                        ->all();
                }

                $prices = [];
                foreach ($m->packages as $pkg) {
                    $prices[$pkg->id] = $pkg->pivot->price !== null ? (float) $pkg->pivot->price : null;
                }

                return [
                    'id' => $m->id,
                    'title' => $m->name,
                    'genre' => $m->genre->name,
                    'rating' => (float) $m->rating,
                    'ratingsCount' => $m->ratings_count,
                    'duration' => $m->duration,
                    'cert' => $m->age_rating,
                    'badge' => $m->badge,
                    'g1' => $m->posterFrom(),
                    'g2' => $m->posterTo(),
                    'poster' => $m->image ? asset('storage/' . $m->image) : null,
                    'minPrice' => $m->minPrice(),
                    'prices' => $prices,
                    'times' => $byDay,
                ];
            })->values(),
        ];
    }

    public function show(Movie $movie): View
    {
        abort_unless($movie->status === Movie::STATUS_AVAILABLE, 404);

        $movie->load([
            'genre',
            'packages',
            'showtimes' => fn ($q) => $q->whereNotNull('show_date')
                ->whereDate('show_date', '>=', today())
                ->orderBy('show_date')
                ->orderBy('start_time'),
        ]);

        return view('movies.show', [
            'movie' => $movie,
            'countries' => Countries::all(),
        ]);
    }

    public function book(Request $request, Movie $movie)
    {
        $data = $request->validate([
            'showtime_id' => ['required', 'exists:showtimes,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'show_date' => ['required', 'date', 'after_or_equal:today'],
            'full_name' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before:today'],
            'country_code' => ['required', 'string', 'max:8'],
            'country_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp_country_code' => ['required', 'string', 'max:8'],
            'whatsapp' => ['required', 'string', 'max:30'],
        ]);

        if (count(preg_split('/\s+/', trim($data['full_name']))) < 3) {
            return $this->bookingFailed($request, ['full_name' => 'يرجى إدخال الاسم الثلاثي كاملاً']);
        }

        $showtime = Showtime::where('id', $data['showtime_id'])
            ->where('movie_id', $movie->id)
            ->firstOrFail();

        $showDate = \Illuminate\Support\Carbon::parse($data['show_date']);

        if ($showtime->show_date && ! $showtime->show_date->isSameDay($showDate)) {
            return $this->bookingFailed($request, ['showtime_id' => 'وقت العرض المختار لا يطابق التاريخ المحدد']);
        }

        if (! $showtime->show_date && (int) $showtime->day_of_week !== $showDate->dayOfWeek) {
            return $this->bookingFailed($request, ['showtime_id' => 'وقت العرض المختار لا يطابق يوم التاريخ المحدد']);
        }

        if ($this->slotHasPassed($showDate->toDateString(), $showtime->start_time)) {
            return $this->bookingFailed($request, ['showtime_id' => 'انتهى وقت هذا العرض، يرجى اختيار عرض آخر']);
        }

        $price = $movie->priceForPackage((int) $data['package_id']);
        abort_if($price === null, 422, 'الباقة المختارة غير متاحة لهذا الفيلم');

        $package = Package::find($data['package_id']);
        $seats = max(1, (int) $package->seats_count);

        if (! SeatAvailability::hasRoomFor($showtime, $data['show_date'], $seats)) {
            return $this->bookingFailed($request, [
                'showtime_id' => 'لا توجد مقاعد كافية في هذا العرض، يرجى اختيار وقت آخر',
            ]);
        }

        $booking = DB::transaction(function () use ($movie, $showtime, $data, $price, $seats) {
            // Re-check inside the transaction so two simultaneous requests cannot
            // both pass the capacity check and oversell the showing.
            $showtime = Showtime::query()->lockForUpdate()->find($showtime->id);

            if (! SeatAvailability::hasRoomFor($showtime, $data['show_date'], $seats)) {
                return null;
            }

            $booking = Booking::create([
                'booking_ref' => Booking::generateRef(),
                'movie_id' => $movie->id,
                'showtime_id' => $showtime->id,
                'package_id' => $data['package_id'],
                'seats' => $seats,
                'show_date' => $data['show_date'],
                'full_name' => $data['full_name'],
                'birthdate' => $data['birthdate'],
                'country_name' => $data['country_name'],
                'country_code' => $data['country_code'],
                'phone' => $data['phone'],
                'whatsapp_country_code' => $data['whatsapp_country_code'],
                'whatsapp' => $data['whatsapp'],
                'price' => $price,
                'payment_status' => Booking::PAYMENT_PENDING,
                'attendance_status' => Booking::ATTENDANCE_PENDING,
            ]);

            $booking->ensureQr();

            return $booking;
        });

        if (! $booking) {
            return $this->bookingFailed($request, [
                'showtime_id' => 'لا توجد مقاعد كافية في هذا العرض، يرجى اختيار وقت آخر',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'ticket_url' => route('movies.ticket', $booking->booking_ref),
                'qr_url' => $booking->qrUrl(),
                'package' => $booking->package->name,
                'seats' => $booking->seats,
                'price' => (float) $booking->price,
                'movie' => $movie->name,
                'showtime' => $showtime->start_time_short,
                'show_date' => $booking->show_date->format('Y-m-d'),
                'whatsapp_url' => $booking->whatsappUrl(),
            ]);
        }

        return redirect()
            ->route('movies.ticket', $booking->booking_ref)
            ->with('booked', true);
    }

    public function ticket(string $ref): View
    {
        $booking = Booking::with(['movie', 'package', 'showtime'])
            ->where('booking_ref', $ref)
            ->firstOrFail();

        $booking->ensureQr();

        return view('movies.ticket', [
            'booking' => $booking,
            'qrUrl' => $booking->qrUrl(),
        ]);
    }

    /**
     * @param  array<string, string>  $errors
     */
    private function bookingFailed(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'errors' => $errors], 422);
        }

        return back()->withErrors($errors)->withInput();
    }

    /**
     * Day labels are English (Sunday, Monday, ...) with a numeric d/m/Y date.
     *
     * @return array<int, array{name:string, nameAr:string, date:string, iso:string, dow:int, isToday:bool}>
     */
}
