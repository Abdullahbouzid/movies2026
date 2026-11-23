<?php

namespace Tests\Feature;

use App\Filament\Pages\Treasury\CinemaTreasury;
use App\Filament\Pages\Treasury\ClubTreasury;
use App\Filament\Pages\Treasury\EventsTreasury;
use App\Filament\Pages\Treasury\GamesTreasury;
use App\Filament\Pages\Treasury\RevenueReport;
use App\Filament\Pages\Treasury\TreasuryOverview;
use App\Filament\Widgets\ActivityRevenueChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\TreasuryStatsOverview;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Game;
use App\Models\GameBooking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Models\User;
use App\Support\Treasury;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TreasuryFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function branch(): Branch
    {
        return Branch::create([
            'name' => 'المركز الرئيسي',
            'services' => array_keys(Branch::SERVICES),
            'is_main' => true,
            'is_active' => true,
        ]);
    }

    private function cinemaBooking(array $attributes = []): Booking
    {
        // Reuse the fixture movie so repeated calls in one test do not collide
        // with the unique genre name index.
        $genre = Genre::firstOrCreate(['name' => 'أكشن']);
        $package = Package::firstOrCreate(['name' => 'عادية'], ['seats_count' => 1, 'includes' => 'مقعد']);

        $movie = Movie::firstOrCreate(
            ['name' => 'فيلم'],
            ['genre_id' => $genre->id, 'duration' => 90, 'rating' => 8.0, 'status' => Movie::STATUS_AVAILABLE],
        );

        if (! $movie->packages()->whereKey($package->id)->exists()) {
            $movie->packages()->attach($package->id, ['price' => 35]);
        }

        $showtime = Showtime::firstOrCreate([
            'movie_id' => $movie->id,
            'show_date' => now()->toDateString(),
            'start_time' => '11:30',
        ], ['end_time' => '13:00']);

        return Booking::create(array_merge([
            'booking_ref' => Booking::generateRef(),
            'movie_id' => $movie->id,
            'showtime_id' => $showtime->id,
            'package_id' => $package->id,
            'seats' => 1,
            'show_date' => now()->toDateString(),
            'full_name' => 'محمد علي حسن',
            'birthdate' => '1990-01-01',
            'country_name' => 'ليبيا',
            'country_code' => '218',
            'phone' => '0913504510',
            'whatsapp_country_code' => '218',
            'whatsapp' => '0913504510',
            'price' => 35,
            'payment_status' => Booking::PAYMENT_PENDING,
            'attendance_status' => Booking::ATTENDANCE_PENDING,
        ], $attributes));
    }

    public function test_paid_booking_records_collection_timestamp(): void
    {
        $booking = $this->cinemaBooking();

        $this->assertNull($booking->fresh()->paid_at);

        $booking->markPaid();

        $this->assertNotNull($booking->fresh()->paid_at);
        $this->assertSame(Booking::PAYMENT_PAID, $booking->fresh()->payment_status);
    }

    public function test_reverting_payment_clears_collection_timestamp(): void
    {
        $booking = $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID]);

        $this->assertNotNull($booking->fresh()->paid_at);

        $booking->markPending();

        $this->assertNull($booking->fresh()->paid_at);
    }

    public function test_summary_totals_collected_and_pending_per_activity(): void
    {
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 50]);
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 30]);
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PENDING, 'price' => 20]);

        $summary = Treasury::summary();

        $this->assertSame(80.0, $summary['rows']['cinema']['paid_total']);
        $this->assertSame(20.0, $summary['rows']['cinema']['pending_total']);
        $this->assertSame(2, $summary['rows']['cinema']['paid_count']);
        $this->assertSame(80.0, $summary['totals']['paid_total']);
    }

    public function test_summary_respects_the_date_window(): void
    {
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 40]);

        $old = $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 90]);
        $old->forceFill(['paid_at' => now()->subMonth()])->saveQuietly();

        $summary = Treasury::summary(today()->startOfMonth()->toDateString(), today()->toDateString());

        $this->assertSame(40.0, $summary['rows']['cinema']['paid_total']);
    }

    public function test_summary_covers_every_activity(): void
    {
        $branch = $this->branch();

        $game = Game::create(['name' => 'لعبة', 'is_active' => true]);
        GameBooking::create([
            'booking_ref' => GameBooking::generateRef(),
            'branch_id' => $branch->id,
            'game_id' => $game->id,
            'play_date' => now()->toDateString(),
            'duration_label' => 'ساعة',
            'duration_minutes' => 60,
            'price' => 25,
            'full_name' => 'سالم أحمد',
            'country_name' => 'ليبيا',
            'country_code' => '218',
            'phone' => '0910000000',
            'whatsapp_country_code' => '218',
            'whatsapp' => '0910000000',
            'payment_status' => GameBooking::PAYMENT_PAID,
            'attendance_status' => GameBooking::ATTENDANCE_PENDING,
        ]);

        $summary = Treasury::summary();

        $this->assertSame(25.0, $summary['rows']['games']['paid_total']);
        $this->assertSame(0.0, $summary['rows']['club']['paid_total']);
        $this->assertSame(25.0, $summary['totals']['paid_total']);
    }

    public function test_daily_series_zero_fills_quiet_days(): void
    {
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 60]);

        $series = Treasury::dailySeries(now()->subDays(3), now());

        $this->assertCount(4, $series);
        $this->assertSame(60.0, $series[now()->toDateString()]);
        $this->assertSame(0.0, $series[now()->subDays(3)->toDateString()]);
    }

    public function test_stale_pending_lists_unpaid_bookings_only(): void
    {
        $old = $this->cinemaBooking(['price' => 70]);
        $old->forceFill(['created_at' => now()->subDays(30)])->saveQuietly();

        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 10]);

        $stale = Treasury::stalePending(7);

        $this->assertSame(1, $stale['cinema']['count']);
        $this->assertSame(70.0, $stale['cinema']['total']);
    }

    public function test_admin_can_open_treasury_pages(): void
    {
        $user = $this->admin();
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID]);

        foreach (['/admin/treasury', '/admin/cinema-treasury', '/admin/games-treasury', '/admin/club-treasury', '/admin/events-treasury'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_overview_page_lists_paid_bookings(): void
    {
        $user = $this->admin();
        $booking = $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID]);

        Livewire::actingAs($user)
            ->test(TreasuryOverview::class)
            ->assertOk()
            ->assertSee($booking->booking_ref)
            ->assertSee('تذاكر السينما')
            ->assertSee($booking->full_name)
            ->assertSee('35.00');
    }

    public function test_activity_page_filters_by_payment_state(): void
    {
        $user = $this->admin();
        $paid = $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID]);
        $pending = $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PENDING]);

        Livewire::actingAs($user)
            ->test(CinemaTreasury::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$paid, $pending])
            ->filterTable('payment_status', Booking::PAYMENT_PAID)
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_revenue_report_page_renders_totals(): void
    {
        $user = $this->admin();
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 45]);

        $this->actingAs($user)
            ->get('/admin/revenue-report')
            ->assertOk()
            ->assertSee('تقرير الإيرادات')
            ->assertSee('45.00');
    }

    public function test_revenue_report_csv_export_has_bom_and_totals(): void
    {
        $user = $this->admin();
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 45]);

        $response = Livewire::actingAs($user)
            ->test(RevenueReport::class)
            ->call('exportCsv');

        $response->assertFileDownloaded();

        $content = base64_decode($response->effects['download']['content']);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('تذاكر السينما', $content);
        $this->assertStringContainsString('45.00', $content);
    }

    public function test_dashboard_widgets_render(): void
    {
        $user = $this->admin();
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 45]);

        $this->actingAs($user)->get('/admin')->assertOk();

        // Widgets are lazy-loaded, so their content only exists once mounted.
        Livewire::actingAs($user)
            ->test(TreasuryStatsOverview::class)
            ->assertOk()
            ->assertSee('محصّل اليوم')
            ->assertSee('45.00');

        Livewire::actingAs($user)->test(RevenueChart::class)->assertOk();
        Livewire::actingAs($user)->test(ActivityRevenueChart::class)->assertOk();
    }

    public function test_every_activity_treasury_page_renders(): void
    {
        $user = $this->admin();
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID]);

        $pages = [
            GamesTreasury::class => 'خزينة صالة الألعاب',
            ClubTreasury::class => 'خزينة النادي الاجتماعي',
            EventsTreasury::class => 'خزينة مناسبات المقهى',
        ];

        foreach ($pages as $page => $heading) {
            Livewire::actingAs($user)->test($page)->assertOk()->assertSee($heading, escape: false);
        }
    }

    public function test_printable_report_requires_auth_and_renders(): void
    {
        $this->cinemaBooking(['payment_status' => Booking::PAYMENT_PAID, 'price' => 60]);

        $this->get('/admin/revenue-report/print')->assertRedirect('/admin/login');

        $user = $this->admin();

        $this->actingAs($user)
            ->get('/admin/revenue-report/print')
            ->assertOk()
            ->assertSee('تقرير الإيرادات')
            ->assertSee('60.00');
    }
}
