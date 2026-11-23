<?php

namespace Tests\Feature;

use App\Filament\Resources\Branches\Pages\CreateBranch;
use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\RelationManagers\CinemaBookingsRelationManager;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Club;
use App\Models\ClubBooking;
use App\Models\EventBooking;
use App\Models\EventPackage;
use App\Models\Game;
use App\Models\GameBooking;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Package;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BranchFlowTest extends TestCase
{
    use RefreshDatabase;

    private function mainBranch(array $services = []): Branch
    {
        return Branch::create([
            'name' => 'المركز الرئيسي',
            'services' => $services ?: array_keys(Branch::SERVICES),
            'is_main' => true,
            'is_active' => true,
        ]);
    }

    private function cinemaFixture(): array
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
            'show_date' => now()->toDateString(),
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

    public function test_branch_stores_only_known_services(): void
    {
        $branch = Branch::create([
            'name' => 'فرع',
            'services' => ['cinema', 'games', 'not_a_service'],
        ]);

        $this->assertSame(['cinema', 'games'], $branch->fresh()->services);
        $this->assertTrue($branch->offers('cinema'));
        $this->assertFalse($branch->offers('club'));
        $this->assertSame(['السينما', 'الألعاب'], $branch->serviceLabels());
    }

    public function test_only_one_branch_can_be_the_main_center(): void
    {
        $first = $this->mainBranch();
        $second = Branch::create(['name' => 'فرع ثانٍ', 'services' => ['games'], 'is_main' => true]);

        $this->assertFalse($first->fresh()->is_main);
        $this->assertTrue($second->fresh()->is_main);
        $this->assertSame(1, Branch::where('is_main', true)->count());
        $this->assertSame($second->id, Branch::main()->id);
    }

    public function test_branch_seeder_creates_a_main_center_offering_every_service(): void
    {
        $this->seed(\Database\Seeders\BranchSeeder::class);

        $main = Branch::main();

        $this->assertNotNull($main);
        $this->assertSame('المركز الرئيسي', $main->name);
        $this->assertEqualsCanonicalizing(array_keys(Branch::SERVICES), $main->services);
        $this->assertSame(3, Branch::count());
    }

    public function test_cinema_booking_is_attached_to_a_branch_offering_cinema(): void
    {
        $this->mainBranch();
        [$movie, $package, $showtime] = $this->cinemaFixture();

        $this->postJson("/movies/{$movie->id}/book", [
            'package_id' => $package->id,
            'showtime_id' => $showtime->id,
            'show_date' => now()->toDateString(),
            'birthdate' => '1995-05-05',
        ] + $this->customer())->assertOk();

        $booking = Booking::firstOrFail();

        $this->assertNotNull($booking->branch_id);
        $this->assertTrue($booking->branch->offers('cinema'));
    }

    public function test_game_booking_falls_back_to_a_branch_that_offers_games(): void
    {
        $this->mainBranch(['cinema', 'club', 'events']);
        $gamesBranch = Branch::create(['name' => 'فرع الألعاب', 'services' => ['games'], 'is_active' => true]);

        $game = Game::create([
            'name' => 'بلايستيشن', 'icon' => '🎮', 'devices_count' => 2,
            'is_active' => true, 'rating' => 9, 'ratings_count' => 1,
        ]);
        $price = $game->prices()->create(['label' => 'ساعة', 'minutes' => 60, 'price' => 20, 'is_default' => true]);

        $playTime = $game->playTimes()->create([
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_date' => now()->toDateString(),
            'play_time_id' => $playTime->id,
        ] + $this->customer())->assertOk();

        $booking = GameBooking::firstOrFail();

        $this->assertSame($gamesBranch->id, $booking->branch_id);
        $this->assertTrue($booking->branch->offers('games'));
    }

    public function test_club_and_event_bookings_are_attached_to_the_main_center(): void
    {
        $main = $this->mainBranch();

        $club = Club::create(['name' => 'نادي', 'is_active' => true]);
        $clubPackage = $club->allPackages()->create([
            'name' => 'جلسة', 'people_count' => 2, 'start_time' => '16:00',
            'end_time' => '18:00', 'price' => 30, 'is_active' => true,
        ]);

        $this->postJson("/clubs/{$club->id}/book", [
            'club_package_id' => $clubPackage->id,
            'visit_date' => now()->toDateString(),
        ] + $this->customer())->assertOk();

        $eventPackage = EventPackage::create([
            'name' => 'باقة', 'people_count' => 5, 'price' => 100, 'is_active' => true,
        ]);

        $this->postJson('/events/book', [
            'event_package_id' => $eventPackage->id,
            'event_date' => now()->toDateString(),
            'start_time' => '15:00',
        ] + $this->customer())->assertOk();

        $this->assertSame($main->id, ClubBooking::firstOrFail()->branch_id);
        $this->assertSame($main->id, EventBooking::firstOrFail()->branch_id);
    }

    public function test_booking_without_any_branch_still_saves(): void
    {
        [$movie, $package, $showtime] = $this->cinemaFixture();

        $this->postJson("/movies/{$movie->id}/book", [
            'package_id' => $package->id,
            'showtime_id' => $showtime->id,
            'show_date' => now()->toDateString(),
            'birthdate' => '1995-05-05',
        ] + $this->customer())->assertOk();

        $this->assertNull(Booking::firstOrFail()->branch_id);
    }

    public function test_branch_admin_pages_render_and_create_branch_with_services(): void
    {
        $user = User::factory()->create();
        $branch = $this->mainBranch();

        $this->actingAs($user)->get('/admin/branches')->assertOk();
        $this->actingAs($user)->get('/admin/branches/create')->assertOk();
        $this->actingAs($user)->get("/admin/branches/{$branch->id}/edit")->assertOk();

        Livewire::actingAs($user)
            ->test(CreateBranch::class)
            ->fillForm([
                'name' => 'فرع جديد',
                'services' => ['cinema', 'club'],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $created = Branch::where('name', 'فرع جديد')->firstOrFail();
        $this->assertEqualsCanonicalizing(['cinema', 'club'], $created->services);
    }

    public function test_branch_form_requires_at_least_one_service(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBranch::class)
            ->fillForm(['name' => 'فرع بلا خدمات', 'services' => []])
            ->call('create')
            ->assertHasFormErrors(['services']);
    }

    public function test_promoting_a_branch_to_main_demotes_the_previous_main(): void
    {
        $user = User::factory()->create();
        $original = $this->mainBranch();
        $other = Branch::create(['name' => 'فرع آخر', 'services' => ['games'], 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(EditBranch::class, ['record' => $other->getRouteKey()])
            ->fillForm(['is_main' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($original->fresh()->is_main);
        $this->assertTrue($other->fresh()->is_main);
        $this->assertSame(1, Branch::where('is_main', true)->count());
    }

    public function test_booking_admin_tables_show_the_branch_column(): void
    {
        $user = User::factory()->create();
        $this->mainBranch();

        foreach ([
            '/admin/bookings',
            '/admin/game-bookings',
            '/admin/club-bookings',
            '/admin/event-bookings',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk()->assertSee('الفرع');
        }
    }

    public function test_branch_booking_tabs_are_gated_by_the_branch_services(): void
    {
        $user = User::factory()->create();
        $main = $this->mainBranch();
        $gamesOnly = Branch::create(['name' => 'فرع الألعاب', 'services' => ['games'], 'is_active' => true]);

        foreach (['cinema', 'games', 'club', 'events'] as $service) {
            $manager = $this->managerForService($service);

            $this->assertTrue($manager::canViewForRecord($main, EditBranch::class), "main should show {$service}");
            $this->assertSame(
                $service === 'games',
                $manager::canViewForRecord($gamesOnly, EditBranch::class),
                "games-only branch visibility for {$service}",
            );
        }

        $this->actingAs($user)->get("/admin/branches/{$gamesOnly->id}/edit")
            ->assertOk()
            ->assertSee('حجوزات الألعاب')
            ->assertDontSee('حجوزات السينما');
    }

    public function test_branch_edit_page_lists_bookings_attached_to_it(): void
    {
        $user = User::factory()->create();
        $main = $this->mainBranch();
        [$movie, $package, $showtime] = $this->cinemaFixture();

        $this->postJson("/movies/{$movie->id}/book", [
            'package_id' => $package->id,
            'showtime_id' => $showtime->id,
            'show_date' => now()->toDateString(),
            'birthdate' => '1995-05-05',
        ] + $this->customer())->assertOk();

        $booking = Booking::firstOrFail();
        $this->assertSame($main->id, $booking->branch_id);

        $this->actingAs($user)->get("/admin/branches/{$main->id}/edit")
            ->assertOk()
            ->assertSee('حجوزات السينما');

        Livewire::actingAs($user)
            ->test(CinemaBookingsRelationManager::class, [
                'ownerRecord' => $main,
                'pageClass' => EditBranch::class,
            ])
            ->assertCanSeeTableRecords([$booking]);
    }

    private function managerForService(string $service): string
    {
        return match ($service) {
            'cinema' => \App\Filament\Resources\Branches\RelationManagers\CinemaBookingsRelationManager::class,
            'games' => \App\Filament\Resources\Branches\RelationManagers\GamesBookingsRelationManager::class,
            'club' => \App\Filament\Resources\Branches\RelationManagers\ClubBookingsRelationManager::class,
            'events' => \App\Filament\Resources\Branches\RelationManagers\EventBookingsRelationManager::class,
        };
    }
}
