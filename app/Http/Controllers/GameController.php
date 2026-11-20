<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameBooking;
use App\Models\PlayTime;
use App\Models\Tournament;
use App\Models\TournamentAnnouncement;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $games = Game::query()
            ->active()
            ->with([
                'prices',
                'playTimes' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $weekDays = $this->weekDays();

        $announcement = TournamentAnnouncement::query()
            ->published()
            ->with('tournament.game')
            ->latest('published_at')
            ->first();

        $tournaments = Tournament::query()
            ->with('game')
            ->whereIn('status', ['upcoming', 'open', 'drawn', 'running'])
            ->orderBy('starts_on')
            ->get();

        $playTimes = PlayTime::query()
            ->where('is_active', true)
            ->with('game')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('games.index', [
            'games' => $games,
            'weekDays' => $weekDays,
            'announcement' => $announcement,
            'tournaments' => $tournaments,
            'countries' => Countries::all(),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'payload' => $this->payload($games, $weekDays),
        ]);
    }

    public function book(Request $request, Game $game)
    {
        $data = $request->validate([
            'game_price_id' => ['required', 'exists:game_prices,id'],
            'play_date' => ['required', 'date', 'after_or_equal:today'],
            'full_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:8'],
            'country_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp_country_code' => ['required', 'string', 'max:8'],
            'whatsapp' => ['required', 'string', 'max:30'],
        ]);

        if (count(preg_split('/\s+/', trim($data['full_name']))) < 3) {
            return $this->bookingFailed($request, ['full_name' => 'يرجى إدخال الاسم الثلاثي كاملاً']);
        }

        $price = $game->prices()->whereKey($data['game_price_id'])->first();
        abort_if($price === null, 422, 'المدة المختارة غير متاحة لهذه اللعبة');

        $booking = GameBooking::create([
            'booking_ref' => GameBooking::generateRef(),
            'game_id' => $game->id,
            'game_price_id' => $price->id,
            'play_date' => $data['play_date'],
            'duration_label' => $price->label,
            'duration_minutes' => $price->minutes,
            'price' => $price->price,
            'full_name' => $data['full_name'],
            'country_name' => $data['country_name'],
            'country_code' => $data['country_code'],
            'phone' => $data['phone'],
            'whatsapp_country_code' => $data['whatsapp_country_code'],
            'whatsapp' => $data['whatsapp'],
            'payment_status' => GameBooking::PAYMENT_PENDING,
            'attendance_status' => GameBooking::ATTENDANCE_PENDING,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'qr_url' => $this->qrUrl($booking),
                'game' => $game->name,
                'duration' => $price->label,
                'price' => (float) $booking->price,
                'play_date' => $booking->play_date->format('Y-m-d'),
            ]);
        }

        return redirect()
            ->route('site.games')
            ->with('booked_ref', $booking->booking_ref);
    }

    private function qrUrl(GameBooking $booking): string
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
     * JSON payload consumed by the games page booking modal.
     *
     * @param  \Illuminate\Support\Collection<int, Game>  $games
     * @param  array<int, array{name:string, date:string, iso:string, dow:int, isToday:bool}>  $weekDays
     * @return array<string, mixed>
     */
    private function payload($games, array $weekDays): array
    {
        return [
            'csrf' => csrf_token(),
            'bookUrl' => url('/games/__ID__/book'),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'countries' => Countries::all(),
            'weekDays' => $weekDays,
            'games' => $games->map(fn (Game $g) => [
                'id' => $g->id,
                'slug' => $g->slug,
                'name' => $g->name,
                'icon' => $g->icon ?: '🎮',
                'meta' => $g->meta,
                'g1' => $g->colorFrom(),
                'g2' => $g->colorTo(),
                'image' => $g->image ? asset('storage/' . $g->image) : null,
                'prices' => $g->prices->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => $p->label,
                    'minutes' => $p->minutes,
                    'price' => (float) $p->price,
                    'isDefault' => (bool) $p->is_default,
                ])->values()->all(),
                'times' => $this->timesByDay($g, $weekDays),
            ])->values(),
        ];
    }

    /**
     * @param  array<int, array{name:string, date:string, iso:string, dow:int, isToday:bool}>  $weekDays
     * @return array<int, array<int, string>>
     */
    private function timesByDay(Game $game, array $weekDays): array
    {
        $byDay = [];

        foreach ($weekDays as $i => $day) {
            $byDay[$i] = $game->playTimes
                ->where('day_of_week', $day['dow'])
                ->sortBy('start_time')
                ->map(fn ($t) => $t->start_time_short)
                ->values()
                ->all();
        }

        return $byDay;
    }

    /**
     * @return array<int, array{name:string, date:string, iso:string, dow:int, isToday:bool}>
     */
    private function weekDays(): array
    {
        $dayNames = PlayTime::$dayNames;
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
