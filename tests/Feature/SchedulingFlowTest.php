<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubBooking;
use App\Models\ClubPackage;
use App\Models\EventBooking;
use App\Models\EventPackage;
use App\Models\Game;
use App\Models\GameBooking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\PlayTime;
use App\Models\Package;
use App\Models\Showtime;
use App\Models\User;
use App\Support\GameSchedule;
use App\Filament\Resources\PlayTimes\Pages\CreatePlayTime;
use App\Support\WeekSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class SchedulingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function movieWithShowtime(?string $date = null): array
    {
        $genre = Genre::create(['name' => 'أكشن']);
        $package = Package::create(['name' => 'عادية', 'seats_count' => 1, 'includes' => 'مقعد']);
        $movie = Movie::create([
            'name' => 'فيلم', 'genre_id' => $genre->id, 'duration' => 90,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);
        $movie->packages()->attach($package->id, ['price' => 35]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'show_date' => $date ?? now()->toDateString(),
            'start_time' => '11:30',
            'end_time' => '13:00',
        ]);

        return [$movie, $package, $showtime];
    }

    private function customer(): array
    {
        return [
            'full_name' => 'أحمد محمد علي',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ];
    }

    public function test_showtime_derives_its_weekday_from_the_date(): void
    {
        // 2026-11-20 is a Friday.
        [$movie] = $this->movieWithShowtime('2026-11-20');
        $showtime = Showtime::firstOrFail();

        $this->assertSame(5, $showtime->day_of_week);
        $this->assertSame('Friday', $showtime->day_name);
    }

    public function test_day_names_are_english_for_the_public_schedule(): void
    {
        $this->assertSame('Sunday', Showtime::$dayNames[0]);
        $this->assertSame('Friday', Showtime::$dayNames[5]);
        $this->assertSame('Saturday', Showtime::$dayNames[6]);
        $this->assertSame('الأحد', Showtime::$dayNamesAr[0]);
    }

    public function test_week_payload_uses_english_day_names_with_dates(): void
    {
        $this->movieWithShowtime();

        $html = $this->get('/movies')->assertOk()->getContent();
        preg_match('/window\.CINEMA = (\{.*?\});\s*<\/script>/s', $html, $m);
        $payload = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(now()->format('l'), $payload['weekDays'][0]['name']);
        $this->assertSame(now()->format('d/m/Y'), $payload['weekDays'][0]['date']);
        $this->assertMatchesRegularExpression('#^\d{2}/\d{2}/\d{4}$#', $payload['weekDays'][6]['date']);
    }

    public function test_booking_is_rejected_when_the_showtime_does_not_match_the_date(): void
    {
        [$movie, $package, $showtime] = $this->movieWithShowtime(now()->toDateString());
        $otherDate = now()->addDays(1)->toDateString();

        $this->from('/movies')->post("/movies/{$movie->id}/book", [
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'show_date' => $otherDate,
            'birthdate' => '1995-05-10',
        ] + $this->customer())->assertSessionHasErrors('showtime_id');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_weekly_schedule_generator_creates_dated_showtimes_without_duplicates(): void
    {
        $genre = Genre::create(['name' => 'أكشن']);
        Movie::create([
            'name' => 'فيلم', 'genre_id' => $genre->id, 'duration' => 100,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);

        $created = WeekSchedule::generate(['11:00', '15:00'], today(), 7);

        $this->assertSame(14, $created);
        $this->assertSame(14, Showtime::count());
        $this->assertSame('12:40', Showtime::where('start_time', '11:00')->firstOrFail()->end_time_short);

        // Re-running is idempotent.
        $this->assertSame(0, WeekSchedule::generate(['11:00', '15:00'], today(), 7));
    }

    public function test_generated_showtimes_get_the_configured_seat_capacity(): void
    {
        $genre = Genre::create(['name' => 'أكشن']);
        Movie::create([
            'name' => 'فيلم', 'genre_id' => $genre->id, 'duration' => 100,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);

        WeekSchedule::generate(['11:00'], today(), 2);

        $this->assertSame(2, Showtime::count());
        $this->assertSame(60, Showtime::firstOrFail()->seats_capacity);

        Showtime::query()->delete();
        WeekSchedule::generate(['11:00'], today(), 1, seatsCapacity: 120);

        $this->assertSame(120, Showtime::firstOrFail()->seats_capacity);
    }

    public function test_showtime_command_extends_the_grid_past_the_current_horizon(): void
    {
        $genre = Genre::create(['name' => 'أكشن']);
        Movie::create([
            'name' => 'فيلم', 'genre_id' => $genre->id, 'duration' => 100,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);

        $this->artisan('showtimes:generate', ['--days' => 10])->assertSuccessful();

        $expectedDays = 10;
        $this->assertSame($expectedDays, Showtime::query()->distinct()->count('show_date'));

        // Running again creates nothing but stays successful.
        $this->artisan('showtimes:generate', ['--days' => 10])->assertSuccessful();
        $this->assertSame($expectedDays, Showtime::query()->distinct()->count('show_date'));
    }

    public function test_showtime_command_prunes_past_slots(): void
    {
        $genre = Genre::create(['name' => 'أكشن']);
        $movie = Movie::create([
            'name' => 'فيلم', 'genre_id' => $genre->id, 'duration' => 100,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);

        Showtime::create([
            'movie_id' => $movie->id,
            'show_date' => now()->subDays(3)->toDateString(),
            'day_of_week' => now()->subDays(3)->dayOfWeek,
            'start_time' => '11:00', 'end_time' => '12:40',
        ]);

        $this->artisan('showtimes:generate', ['--days' => 2, '--prune' => true])->assertSuccessful();

        $this->assertSame(0, Showtime::query()->whereDate('show_date', '<', today()->toDateString())->count());
        $this->assertSame(2, Showtime::query()->distinct()->count('show_date'));
    }

    private function gameWithDevices(int $devices = 2): array
    {
        $game = Game::create([
            'name' => 'PS5', 'icon' => '🎮', 'rating' => 4.8,
            'devices_count' => $devices, 'is_active' => true,
        ]);
        $price = $game->prices()->create(['label' => '30 دقيقة', 'minutes' => 30, 'price' => 15, 'is_default' => true]);
        $slot = $game->playTimes()->create([
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        return [$game, $price, $slot];
    }

    public function test_game_booking_assigns_the_first_free_device(): void
    {
        [$game, $price, $slot] = $this->gameWithDevices(2);

        $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
        ] + $this->customer())->assertOk()->assertJsonPath('device', 1);

        $this->assertSame(1, GameBooking::firstOrFail()->device_number);
    }

    public function test_game_booking_prevents_two_bookings_on_the_same_device_and_slot(): void
    {
        [$game, $price, $slot] = $this->gameWithDevices(1);

        $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
        ] + $this->customer())->assertOk();

        $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
        ] + $this->customer())
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertSame(1, GameBooking::count());
    }

    public function test_a_second_booking_lands_on_the_next_free_device(): void
    {
        [$game, $price, $slot] = $this->gameWithDevices(2);

        foreach ([1, 2] as $expected) {
            $this->postJson("/games/{$game->id}/book", [
                'game_price_id' => $price->id,
                'play_time_id' => $slot->id,
                'play_date' => now()->toDateString(),
            ] + $this->customer())->assertOk()->assertJsonPath('device', $expected);
        }

        $this->assertSame([1, 2], GameBooking::orderBy('id')->pluck('device_number')->all());
    }

    public function test_game_slot_availability_drops_as_devices_are_booked(): void
    {
        [$game, $price, $slot] = $this->gameWithDevices(2);

        $this->assertSame(2, GameSchedule::freeDevices($game, []) === [1, 2] ? 2 : 0);

        GameBooking::create([
            'booking_ref' => GameBooking::generateRef(),
            'game_id' => $game->id,
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'device_number' => 1,
            'play_date' => now()->toDateString(),
            'duration_label' => '30 دقيقة',
            'duration_minutes' => 30,
            'price' => 15,
            'full_name' => 'أحمد محمد علي',
            'country_name' => 'ليبيا', 'country_code' => '+218', 'phone' => '0913504510',
            'whatsapp_country_code' => '+218', 'whatsapp' => '0925271793',
        ]);

        $taken = GameSchedule::takenDevices($game, now());
        $this->assertSame([1], $taken[$slot->id]);
        $this->assertSame([2], GameSchedule::freeDevices($game, $taken[$slot->id]));
    }

    public function test_game_booking_rejects_a_slot_from_another_weekday(): void
    {
        [$game, $price, $slot] = $this->gameWithDevices(1);

        $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->addDay()->toDateString(),
        ] + $this->customer())->assertStatus(422);

        $this->assertSame(0, GameBooking::count());
    }

    private function clubFixture(int $capacity = 0): array
    {
        $club = Club::create(['name' => 'النادي', 'is_active' => true]);
        $package = $club->allPackages()->create([
            'name' => 'جلسة', 'people_count' => 4, 'capacity' => $capacity, 'start_time' => '16:00',
            'end_time' => '19:00', 'price' => 60, 'is_default' => true, 'is_active' => true,
        ]);

        return [$club, $package];
    }

    public function test_club_package_capacity_is_enforced(): void
    {
        [$club, $package] = $this->clubFixture(capacity: 4);

        // First booking takes all 4 seats.
        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertOk();

        // Second booking for the same package/day is rejected.
        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertStatus(422)->assertJsonValidationErrors('club_package_id');

        $this->assertSame(1, ClubBooking::count());
    }

    public function test_club_package_without_capacity_stays_unlimited(): void
    {
        [$club, $package] = $this->clubFixture(capacity: 0);

        foreach (range(1, 3) as $ignored) {
            $this->postJson("/clubs/{$club->id}/book", [
                'club_package_id' => $package->id,
                'visit_date' => now()->toDateString(),
            ] + $this->customer())->assertOk();
        }

        $this->assertSame(3, ClubBooking::count());
    }

    public function test_club_capacity_resets_on_another_day(): void
    {
        [$club, $package] = $this->clubFixture(capacity: 4);

        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertOk();

        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->addDay()->toDateString(),
        ] + $this->customer())->assertOk();

        $this->assertSame(2, ClubBooking::count());
    }

    public function test_event_package_capacity_is_enforced(): void
    {
        $package = EventPackage::create([
            'name' => 'باقة', 'people_count' => 5, 'capacity' => 5, 'price' => 100, 'is_active' => true,
        ]);

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '15:00',
        ] + $this->customer())->assertOk();

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '15:00',
        ] + $this->customer())->assertStatus(422)->assertJsonValidationErrors('start_time');

        $this->assertSame(1, EventBooking::count());
    }

    public function test_event_capacity_is_per_time_slot(): void
    {
        $package = EventPackage::create([
            'name' => 'باقة', 'people_count' => 5, 'capacity' => 5, 'price' => 100, 'is_active' => true,
        ]);

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '15:00',
        ] + $this->customer())->assertOk();

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '18:00',
        ] + $this->customer())->assertOk();

        $this->assertSame(2, EventBooking::count());
    }

    public function test_club_page_renders_from_database(): void
    {
        $this->clubFixture();

        $this->get('/club')
            ->assertOk()
            ->assertSee('النادي الاجتماعي')
            ->assertSee('جلسة')
            ->assertSee('window.CLUBS', false);
    }

    public function test_club_booking_creates_record_with_qr(): void
    {
        [$club, $package] = $this->clubFixture();

        $response = $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer());

        $booking = ClubBooking::firstOrFail();

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('reference', $booking->booking_ref)
            ->assertJsonPath('package', 'جلسة');

        $this->assertStringStartsWith('CL-', $booking->booking_ref);
        $this->assertNotNull($booking->qr_path);
        $this->assertFileExists(storage_path('app/public/' . $booking->qr_path));
        $this->assertSame('16:00', substr((string) $booking->start_time, 0, 5));
    }

    public function test_club_booking_rejects_a_package_from_another_club(): void
    {
        [, $package] = $this->clubFixture();
        $other = Club::create(['name' => 'نادٍ آخر', 'is_active' => true]);

        $this->postJson("/clubs/{$other->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertStatus(422);

        $this->assertSame(0, ClubBooking::count());
    }

    public function test_inactive_club_is_hidden_and_cannot_be_booked(): void
    {
        $club = Club::create(['name' => 'نادي مغلق', 'is_active' => false]);
        $package = $club->allPackages()->create([
            'name' => 'باقة', 'people_count' => 2, 'start_time' => '10:00',
            'end_time' => '12:00', 'price' => 20, 'is_active' => true,
        ]);

        $this->get('/club')->assertOk()->assertDontSee('نادي مغلق');
        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertNotFound();
    }

    public function test_event_page_renders_and_booking_uses_a_valid_slot(): void
    {
        $package = EventPackage::create([
            'name' => 'باقة عيد ميلاد', 'includes' => 'كيك', 'people_count' => 10,
            'price' => 250, 'is_active' => true,
        ]);

        $this->get('/cafe')->assertOk()->assertSee('باقة عيد ميلاد');

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '15:00',
        ] + $this->customer())->assertOk()->assertJsonPath('time', '15:00 - 18:00');

        $booking = EventBooking::firstOrFail();
        $this->assertStringStartsWith('EV-', $booking->booking_ref);
        $this->assertNotNull($booking->qr_path);
    }

    public function test_event_booking_rejects_an_unknown_slot(): void
    {
        $package = EventPackage::create([
            'name' => 'باقة', 'people_count' => 5, 'price' => 100, 'is_active' => true,
        ]);

        $this->postJson('/events/book', [
            'event_package_id' => $package->id,
            'event_date' => now()->toDateString(),
            'start_time' => '03:00',
        ] + $this->customer())->assertStatus(422);

        $this->assertSame(0, EventBooking::count());
    }

    public function test_qr_verification_page_shows_the_booking(): void
    {
        [$club, $package] = $this->clubFixture();

        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $package->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer());

        $booking = ClubBooking::firstOrFail();

        $this->get(route('clubs.verify', $booking->booking_ref))
            ->assertOk()
            ->assertSee($booking->booking_ref)
            ->assertSee('أحمد محمد علي');
    }

    public function test_new_admin_resources_render(): void
    {
        [$club] = $this->clubFixture();
        EventPackage::create(['name' => 'باقة', 'people_count' => 5, 'price' => 100, 'is_active' => true]);
        $user = User::factory()->create();

        foreach ([
            '/admin/clubs', '/admin/clubs/create', "/admin/clubs/{$club->id}/edit",
            '/admin/club-bookings',
            '/admin/event-packages', '/admin/event-packages/create',
            '/admin/event-bookings',
            '/admin/showtimes',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_admin_schedule_generator_action_exists(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Resources\Showtimes\Pages\ListShowtimes::class, 'getHeaderActions'));
        $this->assertSame(7, count(WeekSchedule::countsByDate(7)));
    }

    public function test_overlapping_play_time_for_the_same_game_is_rejected(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        $this->expectException(ValidationException::class);

        PlayTime::create([
            'game_id' => $game->id,
            'day_of_week' => $slot->day_of_week,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'is_active' => true,
        ]);
    }

    public function test_overlapping_play_time_does_not_persist_anything(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        try {
            PlayTime::create([
                'game_id' => $game->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => '13:00',
                'end_time' => '15:00',
                'is_active' => true,
            ]);
            $this->fail('Overlapping play time should have been rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('end_time', $e->errors());
        }

        $this->assertSame(1, PlayTime::where('game_id', $game->id)->count());
    }

    public function test_adjacent_play_time_is_accepted(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        PlayTime::create([
            'game_id' => $game->id,
            'day_of_week' => $slot->day_of_week,
            'start_time' => '14:00',
            'end_time' => '16:00',
            'is_active' => true,
        ]);

        $this->assertSame(2, PlayTime::where('game_id', $game->id)->count());
    }

    public function test_same_play_time_slot_is_allowed_for_a_different_game(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);
        $other = Game::create([
            'name' => 'VR', 'icon' => '🥽', 'rating' => 4.5,
            'devices_count' => 2, 'is_active' => true,
        ]);

        PlayTime::create([
            'game_id' => $other->id,
            'day_of_week' => $slot->day_of_week,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        $this->assertSame(1, PlayTime::where('game_id', $other->id)->count());
    }

    public function test_same_play_time_slot_is_allowed_on_a_different_day(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        PlayTime::create([
            'game_id' => $game->id,
            'day_of_week' => ($slot->day_of_week + 1) % 7,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        $this->assertSame(2, PlayTime::where('game_id', $game->id)->count());
    }

    public function test_editing_a_play_time_does_not_conflict_with_itself(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        $slot->update(['end_time' => '13:00']);

        $this->assertSame('13:00', $slot->fresh()->end_time_short);
    }

    public function test_editing_a_play_time_into_another_slot_is_rejected(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);
        $second = PlayTime::create([
            'game_id' => $game->id,
            'day_of_week' => $slot->day_of_week,
            'start_time' => '16:00',
            'end_time' => '18:00',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        $second->update(['start_time' => '13:00']);
    }

    public function test_play_time_admin_form_blocks_an_overlapping_slot(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        Livewire::test(CreatePlayTime::class)
            ->fillForm([
                'game_id' => $game->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => '13:00',
                'end_time' => '15:00',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['end_time']);

        $this->assertSame(1, PlayTime::where('game_id', $game->id)->count());
    }

    public function test_play_time_admin_form_saves_a_free_slot(): void
    {
        [$game, , $slot] = $this->gameWithDevices(2);

        Livewire::test(CreatePlayTime::class)
            ->fillForm([
                'game_id' => $game->id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => '16:00',
                'end_time' => '18:00',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, PlayTime::where('game_id', $game->id)->count());
    }
}
