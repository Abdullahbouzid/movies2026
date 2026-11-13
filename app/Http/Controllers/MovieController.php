<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Support\Countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $movies = Movie::query()
            ->available()
            ->with([
                'genre',
                'packages',
                'showtimes' => fn ($q) => $q->orderBy('start_time'),
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
        ]);
    }

    /**
     * JSON payload consumed by public/js/movies.js.
     *
     * @param  \Illuminate\Support\Collection<int, Movie>  $movies
     * @param  \Illuminate\Support\Collection<int, Package>  $packages
     * @param  array<int, array{name:string, date:string, iso:string, dow:int, isToday:bool}>  $weekDays
     * @return array<string, mixed>
     */
    private function payload($movies, $packages, array $weekDays, int $currentDay): array
    {

        return [
            'currentDay' => $currentDay,
            'dayNames' => array_values(Showtime::$dayNames),
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
            'movies' => $movies->map(function (Movie $m) use ($weekDays) {
                $byDay = [];
                foreach ($weekDays as $i => $day) {
                    $byDay[$i] = $m->showtimes
                        ->where('day_of_week', $day['dow'])
                        ->sortBy('start_time')
                        ->map(fn ($s) => ['id' => $s->id, 'time' => $s->start_time_short])
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

        $movie->load(['genre', 'packages', 'showtimes' => fn ($q) => $q->orderBy('day_of_week')->orderBy('start_time')]);

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

        $price = $movie->priceForPackage((int) $data['package_id']);
        abort_if($price === null, 422, 'الباقة المختارة غير متاحة لهذا الفيلم');

        $booking = Booking::create([
            'booking_ref' => Booking::generateRef(),
            'movie_id' => $movie->id,
            'showtime_id' => $showtime->id,
            'package_id' => $data['package_id'],
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'ticket_url' => route('movies.ticket', $booking->booking_ref),
                'qr_url' => $this->qrUrl($booking),
                'package' => $booking->package->name,
                'price' => (float) $booking->price,
                'movie' => $movie->name,
                'showtime' => $showtime->start_time_short,
                'show_date' => $booking->show_date->format('Y-m-d'),
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

        return view('movies.ticket', [
            'booking' => $booking,
            'qrUrl' => $this->qrUrl($booking),
        ]);
    }

    private function qrUrl(Booking $booking): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=0&data=' . urlencode($booking->qrPayload());
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
     * @return array<int, array{name:string, date:string, iso:string, dow:int, isToday:bool}>
     */
    private function weekDays(): array
    {
        $dayNames = Showtime::$dayNames;
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i);
            $days[] = [
                'name' => $dayNames[$date->dayOfWeek],
                'date' => $date->day . ' ' . $this->monthName($date->month),
                'iso' => $date->format('Y-m-d'),
                'dow' => $date->dayOfWeek,
                'isToday' => $i === 0,
            ];
        }

        return $days;
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][$month] ?? '';
    }
}
