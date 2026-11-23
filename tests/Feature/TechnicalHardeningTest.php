<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Support\AssetVersion;
use App\Support\Mask;
use App\Support\SeatAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function seedCinema(int $capacity = 10, int $packageSeats = 1): array
    {
        $genre = Genre::create(['name' => 'أكشن']);
        $package = Package::create([
            'name' => 'باقة', 'seats_count' => $packageSeats, 'includes' => 'مقعد',
        ]);
        $movie = Movie::create([
            'name' => 'فيلم السعة', 'genre_id' => $genre->id, 'duration' => 120,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);
        $movie->packages()->attach($package->id, ['price' => 30]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'show_date' => now()->toDateString(),
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '11:30', 'end_time' => '13:50',
            'seats_capacity' => $capacity,
        ]);

        return compact('movie', 'package', 'showtime');
    }

    private function bookingPayload(array $ctx): array
    {
        return [
            'showtime_id' => $ctx['showtime']->id,
            'package_id' => $ctx['package']->id,
            'show_date' => now()->toDateString(),
            'full_name' => 'أحمد محمد علي',
            'birthdate' => '1995-05-10',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0925551234',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ];
    }

    public function test_booking_records_seats_from_the_package(): void
    {
        $ctx = $this->seedCinema(capacity: 10, packageSeats: 3);

        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));

        $this->assertSame(3, Booking::firstOrFail()->seats);
    }

    public function test_booking_is_rejected_when_the_showing_is_full(): void
    {
        $ctx = $this->seedCinema(capacity: 2, packageSeats: 2);

        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))
            ->assertOk();

        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertSame(1, Booking::count());
    }

    public function test_seats_left_reaches_the_frontend_payload(): void
    {
        $ctx = $this->seedCinema(capacity: 8, packageSeats: 2);

        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))->assertOk();

        $html = $this->get('/movies')->assertOk()->getContent();
        preg_match('/window\.CINEMA = (\{.*?\});\s*<\/script>/s', $html, $m);
        $payload = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(6, $payload['movies'][0]['times'][0][0]['seatsLeft']);
    }

    public function test_seat_availability_helper_matches_sold_seats(): void
    {
        $ctx = $this->seedCinema(capacity: 10, packageSeats: 4);
        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))->assertOk();

        $date = now()->toDateString();

        $this->assertSame(4, SeatAvailability::sold($ctx['showtime'], $date));
        $this->assertSame(6, SeatAvailability::remaining($ctx['showtime'], $date));
        $this->assertTrue(SeatAvailability::hasRoomFor($ctx['showtime'], $date, 6));
        $this->assertFalse(SeatAvailability::hasRoomFor($ctx['showtime'], $date, 7));
    }

    public function test_asset_version_appends_filemtime(): void
    {
        $url = AssetVersion::url('css/movies.css');

        $this->assertStringContainsString('css/movies.css?v=', $url);
        $this->assertStringContainsString((string) filemtime(public_path('css/movies.css')), $url);
    }

    public function test_asset_version_leaves_missing_files_untouched(): void
    {
        $this->assertStringNotContainsString('?v=', AssetVersion::url('css/does-not-exist.css'));
    }

    public function test_public_pages_emit_description_and_og_tags(): void
    {
        $this->seedCinema();

        foreach (['/movies', '/games', '/club', '/cafe'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{40,}"/u', $html, $path);
            $this->assertMatchesRegularExpression('/<meta property="og:title" content="[^"]+"/u', $html, $path);
            $this->assertMatchesRegularExpression('/<meta property="og:image" content="[^"]+"/u', $html, $path);
        }
    }

    public function test_ticket_and_verify_pages_are_noindex_and_mask_the_phone(): void
    {
        $ctx = $this->seedCinema();
        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));
        $booking = Booking::firstOrFail();

        foreach ([
            route('movies.ticket', $booking->booking_ref),
            route('movies.verify', $booking->booking_ref),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('name="robots" content="noindex,nofollow"', $html);
            $this->assertStringNotContainsString('0925551234', $html);
            $this->assertStringContainsString('*', $html);
        }
    }

    public function test_mask_keeps_edges_and_hides_the_middle(): void
    {
        $this->assertSame('+218*******234', Mask::phone('+2180925551234'));
        $this->assertSame('', Mask::phone(''));
        $this->assertSame('****', Mask::phone('1234'));
    }

    public function test_staff_can_check_in_a_booking_from_the_qr_page(): void
    {
        $ctx = $this->seedCinema();
        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));
        $booking = Booking::firstOrFail();

        $this->post(route('verify.check-in', [
            'type' => 'ticket',
            'ref' => $booking->booking_ref,
        ]))->assertRedirect(route('movies.verify', $booking->booking_ref));

        $this->assertSame(Booking::ATTENDANCE_ATTENDED, $booking->fresh()->attendance_status);
    }

    public function test_check_in_flashes_a_confirmation_notice(): void
    {
        $ctx = $this->seedCinema();
        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));
        $booking = Booking::firstOrFail();

        $this->post("/verify/ticket/{$booking->booking_ref}/check-in")
            ->assertRedirect(route('movies.verify', $booking->booking_ref))
            ->assertSessionHas('checked_in', true);

        $this->get(route('movies.verify', $booking->booking_ref))
            ->assertOk()
            ->assertSee('تم تسجيل حضور هذا الحجز.');
    }

    public function test_check_in_accepts_the_legacy_cinema_segment(): void
    {
        $ctx = $this->seedCinema();
        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));
        $booking = Booking::firstOrFail();

        $this->post("/verify/cinema/{$booking->booking_ref}/check-in")
            ->assertRedirect(route('movies.verify', $booking->booking_ref));

        $this->assertSame(Booking::ATTENDANCE_ATTENDED, $booking->fresh()->attendance_status);
    }

    public function test_check_in_rejects_unknown_types(): void
    {
        $ctx = $this->seedCinema();
        $this->post("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx));
        $booking = Booking::firstOrFail();

        $this->post("/verify/bogus/{$booking->booking_ref}/check-in")->assertNotFound();
    }

    public function test_booking_json_exposes_a_whatsapp_share_link(): void
    {
        $ctx = $this->seedCinema();

        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('seats', 1);

        $url = $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))
            ->assertOk()
            ->json('whatsapp_url');

        $this->assertStringStartsWith('https://wa.me/218925271793?text=', $url);
    }

    public function test_booking_a_showtime_that_already_started_is_rejected(): void
    {
        $ctx = $this->seedCinema();

        // 23:00 on the same day is well past the 11:30 start time.
        $this->travelTo(now()->startOfDay()->addHours(23));

        $this->postJson("/movies/{$ctx['movie']->id}/book", $this->bookingPayload($ctx))
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('errors.showtime_id', 'انتهى وقت هذا العرض، يرجى اختيار عرض آخر');

        $this->assertSame(0, Booking::count());
    }

    public function test_booking_the_same_showtime_on_a_later_date_still_works(): void
    {
        $ctx = $this->seedCinema();

        $tomorrow = Showtime::create([
            'movie_id' => $ctx['movie']->id,
            'show_date' => now()->addDay()->toDateString(),
            'day_of_week' => now()->addDay()->dayOfWeek,
            'start_time' => '11:30',
            'end_time' => '13:50',
            'seats_capacity' => 10,
        ]);

        $this->travelTo(now()->startOfDay()->addHours(23));

        $payload = $this->bookingPayload($ctx);
        $payload['showtime_id'] = $tomorrow->id;
        $payload['show_date'] = now()->addDay()->toDateString();

        $this->postJson("/movies/{$ctx['movie']->id}/book", $payload)->assertOk();

        $this->assertSame(1, Booking::count());
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:string}>
     */
    public static function phoneNumberProvider(): array
    {
        return [
            'local number with trunk zero' => ['0925271793', '+218', '218925271793'],
            'pasted full international' => ['+218925271793', '+218', '218925271793'],
            'international without plus' => ['218925271793', '+218', '218925271793'],
            'national without zero' => ['925271793', '+218', '218925271793'],
            'double zero prefix' => ['00218925271793', '+218', '218925271793'],
            'another country' => ['0501234567', '+966', '966501234567'],
        ];
    }

    #[DataProvider('phoneNumberProvider')]
    public function test_whatsapp_numbers_are_not_double_prefixed(string $phone, string $code, string $expected): void
    {
        $this->assertSame($expected, Booking::internationalNumber($phone, $code));
    }

    public function test_whatsapp_link_does_not_repeat_the_country_code(): void
    {
        $ctx = $this->seedCinema();

        $payload = $this->bookingPayload($ctx);
        $payload['whatsapp'] = '+218925271793';

        $url = $this->postJson("/movies/{$ctx['movie']->id}/book", $payload)
            ->assertOk()
            ->json('whatsapp_url');

        $this->assertStringStartsWith('https://wa.me/218925271793?text=', $url);
        $this->assertStringNotContainsString('218218', $url);
    }
}
