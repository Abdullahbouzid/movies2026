<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameBooking;
use App\Models\PlayTime;
use App\Models\Tournament;
use App\Models\TournamentAnnouncement;
use App\Support\Countries;
use App\Support\HeroShowcase;
use App\Support\GameSchedule;
use App\Http\Controllers\Concerns\HandlesBookings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GameController extends Controller
{
    use HandlesBookings;

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
            'showcase' => (new HeroShowcase('#0ea5e9'))->games($games)->slides(),
            'defaultCountry' => config('cinema.default_country', '+218'),
            'payload' => $this->payload($games, $weekDays),
        ]);
    }

    public function book(Request $request, Game $game)
    {
        abort_unless($game->is_active, 404);

        $data = $request->validate([
            'game_price_id' => ['required', 'exists:game_prices,id'],
            'play_time_id' => ['required', 'exists:play_times,id'],
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

        $slot = $game->playTimes()->whereKey($data['play_time_id'])->where('is_active', true)->first();
        if ($slot === null) {
            return $this->bookingFailed($request, ['play_time_id' => 'وقت اللعب المختار غير متاح']);
        }

        $playDate = Carbon::parse($data['play_date']);

        if ($slot->day_of_week !== $playDate->dayOfWeek) {
            return $this->bookingFailed($request, ['play_date' => 'وقت اللعب المختار لا يطابق يوم التاريخ المحدد']);
        }

        if ($this->slotHasPassed($playDate->toDateString(), $slot->start_time)) {
            return $this->bookingFailed($request, ['play_time_id' => 'انتهى وقت هذه الفترة، يرجى اختيار فترة أخرى']);
        }

        try {
            $booking = DB::transaction(function () use ($game, $price, $slot, $playDate, $data) {
                $taken = GameSchedule::takenDevices($game, $playDate);
                $free = GameSchedule::freeDevices($game, $taken[$slot->id] ?? []);

                if ($free === []) {
                    throw ValidationException::withMessages([
                        'play_time_id' => 'هذا الوقت محجوز بالكامل، يرجى اختيار وقت آخر',
                    ]);
                }

                return GameBooking::create([
                    'booking_ref' => GameBooking::generateRef(),
                    'game_id' => $game->id,
                    'game_price_id' => $price->id,
                    'play_time_id' => $slot->id,
                    'device_number' => $free[0],
                    'play_date' => $playDate->toDateString(),
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
            });
        } catch (ValidationException $e) {
            return $this->bookingFailed($request, $e->errors());
        }

        $booking->ensureQr();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $booking->booking_ref,
                'qr_url' => $booking->qrUrl(),
                'game' => $game->name,
                'duration' => $price->label,
                'price' => (float) $booking->price,
                'play_date' => $booking->play_date->format('Y-m-d'),
                'device' => $booking->device_number,
                'slot' => $slot->start_time_short . ' - ' . $slot->end_time_short,
            ]);
        }

        return redirect()
            ->route('site.games')
            ->with('booked_ref', $booking->booking_ref);
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
     * @param  array<int, array{name:string, nameAr:string, date:string, iso:string, dow:int, isToday:bool}>  $weekDays
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
                'times' => GameSchedule::weekAvailability($g, $weekDays),
                'devices' => max(1, (int) $g->devices_count),
                'description' => $g->description,
            ])->values(),
        ];
    }

    /**
     * Day labels are English (Sunday, Monday, ...) with a numeric d/m/Y date.
     *
     * @return array<int, array{name:string, nameAr:string, date:string, iso:string, dow:int, isToday:bool}>
     */
}
