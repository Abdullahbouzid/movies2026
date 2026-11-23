<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CinemaFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedMinimal(): array
    {
        $genre = Genre::create(['name' => 'أكشن']);
        $package = Package::create(['name' => 'الباقة العادية', 'seats_count' => 1, 'includes' => 'مقعد']);
        $movie = Movie::create([
            'name' => 'فيلم تجريبي', 'genre_id' => $genre->id, 'duration' => 120,
            'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE,
        ]);
        $movie->packages()->attach($package->id, ['price' => 35]);
        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'show_date' => now()->toDateString(),
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '11:30', 'end_time' => '13:50',
        ]);

        return compact('movie', 'package', 'showtime');
    }

    public function test_movies_index_renders(): void
    {
        $this->seedMinimal();

        $this->get('/movies')
            ->assertOk()
            ->assertSee('فيلم تجريبي');
    }

    public function test_booking_creates_pending_record_with_ref(): void
    {
        ['movie' => $movie, 'package' => $package, 'showtime' => $showtime] = $this->seedMinimal();

        $response = $this->post("/movies/{$movie->id}/book", [
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'show_date' => now()->toDateString(),
            'full_name' => 'أحمد محمد علي',
            'birthdate' => '1995-05-10',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ]);

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('movies.ticket', $booking->booking_ref));

        $this->assertSame('pending', $booking->payment_status);
        $this->assertSame('pending', $booking->attendance_status);
        $this->assertSame('35.00', (string) $booking->price);
        $this->assertStringStartsWith('CP-', $booking->booking_ref);

        $this->get(route('movies.ticket', $booking->booking_ref))
            ->assertOk()
            ->assertSee($booking->booking_ref);
    }

    public function test_booking_returns_json_when_requested(): void
    {
        ['movie' => $movie, 'package' => $package, 'showtime' => $showtime] = $this->seedMinimal();

        $response = $this->postJson("/movies/{$movie->id}/book", [
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'show_date' => now()->toDateString(),
            'full_name' => 'أحمد محمد علي',
            'birthdate' => '1995-05-10',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ]);

        $booking = Booking::firstOrFail();

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('reference', $booking->booking_ref)
            ->assertJsonPath('movie', 'فيلم تجريبي')
            ->assertJsonPath('price', 35);

        $this->assertStringContainsString($booking->booking_ref, $response->json('qr_url'));
    }

    public function test_movies_page_embeds_payload_for_the_frontend(): void
    {
        ['movie' => $movie, 'package' => $package] = $this->seedMinimal();

        $response = $this->get('/movies')->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('window.CINEMA', $html);
        $this->assertStringContainsString('css/movies.css', $html);
        $this->assertStringContainsString('js/movies.js', $html);

        preg_match('/window\.CINEMA = (\{.*?\});\s*<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'CINEMA payload not found');

        $payload = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($movie->id, $payload['movies'][0]['id']);
        $this->assertSame('فيلم تجريبي', $payload['movies'][0]['title']);
        $this->assertSame(35, $payload['movies'][0]['prices'][$package->id]);
        $this->assertNotEmpty($payload['movies'][0]['times'][0]);
        $this->assertCount(7, $payload['weekDays']);
    }

    public function test_full_name_must_be_three_parts(): void
    {
        ['movie' => $movie, 'package' => $package, 'showtime' => $showtime] = $this->seedMinimal();

        $this->from('/movies')->post("/movies/{$movie->id}/book", [
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'show_date' => now()->toDateString(),
            'full_name' => 'أحمد',
            'birthdate' => '1995-05-10',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ])->assertSessionHasErrors('full_name');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_coming_soon_movies_excluded_from_listing(): void
    {
        $genre = Genre::create(['name' => 'أكشن']);
        Movie::create([
            'name' => 'قريب قريبًا', 'genre_id' => $genre->id, 'duration' => 100,
            'rating' => 0, 'status' => Movie::STATUS_COMING_SOON,
        ]);

        $this->get('/movies')
            ->assertOk()
            ->assertSee('قريب قريبًا');
    }

    public function test_admin_panel_pages_render_for_authenticated_user(): void
    {
        $this->seedMinimal();
        $user = User::factory()->create();

        foreach (['/admin', '/admin/movies', '/admin/genres', '/admin/packages', '/admin/showtimes', '/admin/bookings'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_movie_edit_form_loads_package_prices(): void
    {
        ['movie' => $movie, 'package' => $package] = $this->seedMinimal();
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = \Livewire\Livewire::test(\App\Filament\Resources\Movies\Pages\EditMovie::class, ['record' => $movie->id])
            ->assertFormSet(['name' => 'فيلم تجريبي']);

        $rows = $component->get('data.package_prices');

        $this->assertCount(1, $rows);
        $this->assertSame($package->id, (int) reset($rows)['package_id']);
        $this->assertSame(35.0, (float) reset($rows)['price']);
    }
}
