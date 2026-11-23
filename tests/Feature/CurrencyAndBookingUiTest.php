<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyAndBookingUiTest extends TestCase
{
    use RefreshDatabase;

    private function seedMinimal(): array
    {
        $genre = Genre::create(['name' => 'خيال علمي']);

        $movie = Movie::create([
            'name' => 'فيلم تجريبي',
            'genre_id' => $genre->id,
            'description' => 'وصف',
            'duration' => 120,
            'rating' => 8.5,
            'ratings_count' => 100,
            'age_rating' => '+12',
            'status' => 'available',
        ]);

        $package = Package::create([
            'name' => 'الباقة العادية',
            'seats_count' => 1,
            'includes' => 'تذكرة واحدة',
        ]);

        $movie->packages()->attach($package->id, ['price' => 35]);

        Showtime::create([
            'movie_id' => $movie->id,
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '23:30',
            'end_time' => '23:59',
            'show_date' => now()->toDateString(),
            'seats_capacity' => 40,
        ]);

        return compact('movie', 'package');
    }

    public function test_money_helper_formats_libyan_dinar(): void
    {
        $this->assertSame('د.ل', Money::symbol());
        $this->assertSame('LYD', Money::code());
        $this->assertSame('1,250.00 د.ل', Money::format(1250));
        $this->assertSame('25 د.ل', Money::short(25));
        $this->assertSame('25.5 د.ل', Money::short(25.5));
        $this->assertSame('25.50 د.ل', Money::format(25.5));
        $this->assertSame('السعر (د.ل)', Money::label('السعر'));
    }

    public function test_currency_symbol_comes_from_config(): void
    {
        config(['currency.symbol' => 'ر.س']);

        $this->assertSame('10.00 ر.س', Money::format(10));
    }

    public function test_movies_page_uses_libyan_dinar_and_never_riyal(): void
    {
        $this->seedMinimal();

        $html = $this->get('/movies')->assertOk()->getContent();

        $this->assertStringContainsString('د.ل', $html);
        $this->assertStringNotContainsString('﷼', $html);
    }

    /**
     * Duplicate showtime rows for the same slot used to repeat the same time
     * over and over inside the customer's dropdown.
     */
    public function test_payload_lists_each_slot_once_even_with_duplicate_rows(): void
    {
        ['movie' => $movie] = $this->seedMinimal();

        foreach (range(1, 5) as $i) {
            Showtime::create([
                'movie_id' => $movie->id,
                'day_of_week' => now()->dayOfWeek,
                'start_time' => '10:00',
                'end_time' => '12:00',
                'show_date' => now()->toDateString(),
                'seats_capacity' => 40,
            ]);
        }

        $payload = $this->get('/movies')->assertOk()->viewData('payload');
        $times = collect($payload['movies'][0]['times'][$payload['currentDay']]);

        $this->assertSame(
            $times->pluck('time')->unique()->count(),
            $times->count(),
            'The dropdown must not repeat the same start time.',
        );
        $this->assertSame(1, $times->where('time', '10:00')->count());
    }

    public function test_booking_modal_renders_three_step_wizard(): void
    {
        $this->seedMinimal();

        $this->get('/movies')
            ->assertOk()
            ->assertSee('data-panel="1"', false)
            ->assertSee('data-panel="2"', false)
            ->assertSee('data-panel="3"', false)
            ->assertSee('id="bookingSteps"', false)
            ->assertSee('id="reviewCard"', false)
            ->assertSee('sameAsPhone', false)
            ->assertSee('review-card', false);
    }

    public function test_booking_still_succeeds_through_the_wizard_payload(): void
    {
        ['movie' => $movie, 'package' => $package] = $this->seedMinimal();

        $showtime = Showtime::where('movie_id', $movie->id)->firstOrFail();

        $response = $this->postJson("/movies/{$movie->id}/book", [
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'show_date' => now()->toDateString(),
            'full_name' => 'محمد أحمد العلي',
            'birthdate' => '1995-04-12',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913456789',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0913456789',
        ]);

        $response->assertOk()->assertJson(['ok' => true, 'price' => 35]);

        $this->assertSame(1, Booking::count());
        $this->assertStringStartsWith('CP-', Booking::first()->booking_ref);
    }

    public function test_treasury_pages_show_dinar_amounts(): void
    {
        $this->seedMinimal();

        $admin = \App\Models\User::factory()->create();

        foreach (['/admin/treasury', '/admin/revenue-report', '/admin/cinema-treasury'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('د.ل');
        }
    }

    public function test_admin_money_columns_never_render_raw_currency_code(): void
    {
        ['movie' => $movie, 'package' => $package] = $this->seedMinimal();

        Booking::create([
            'booking_ref' => 'CP-TEST01',
            'movie_id' => $movie->id,
            'showtime_id' => Showtime::first()->id,
            'package_id' => $package->id,
            'seats' => 1,
            'show_date' => now()->toDateString(),
            'full_name' => 'محمد أحمد العلي',
            'birthdate' => '1995-04-12',
            'country_name' => 'ليبيا',
            'country_code' => '+218',
            'phone' => '0913456789',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0913456789',
            'price' => 35,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $admin = \App\Models\User::factory()->create();

        $html = $this->actingAs($admin)->get('/admin/bookings')->assertOk()->getContent();

        $this->assertStringContainsString('CP-TEST01', $html);
        $this->assertStringNotContainsString('LYD ', $html);
        $this->assertStringContainsString('35.00 د.ل', $html);
    }
}
