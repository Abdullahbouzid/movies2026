<?php

namespace Tests\Feature;

use App\Filament\Resources\Movies\Pages\EditMovie;
use App\Models\Club;
use App\Models\EventPackage;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\User;
use App\Support\HeroShowcase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HeroShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function movie(array $overrides = []): Movie
    {
        return Movie::create(array_merge([
            'name' => 'فيلم الاختبار',
            'genre_id' => Genre::create(['name' => 'أكشن'])->id,
            'description' => 'وصف الفيلم التجريبي',
            'duration' => 120,
            'rating' => 8.5,
            'age_rating' => '+12',
            'status' => Movie::STATUS_AVAILABLE,
            'image' => 'movies/poster.jpg',
        ], $overrides));
    }

    private function game(array $overrides = []): Game
    {
        $game = Game::create(array_merge([
            'name' => 'بلايستيشن',
            'icon' => '🎮',
            'meta' => 'شاشة 4K',
            'description' => 'وصف الجهاز التجريبي',
            'devices_count' => 3,
            'is_active' => true,
        ], $overrides));

        $game->prices()->create(['label' => 'ساعة', 'minutes' => 60, 'price' => 25, 'is_default' => true]);

        return $game;
    }

    public function test_movies_hero_shows_movie_name_details_and_description(): void
    {
        $movie = $this->movie();

        $response = $this->get('/movies')->assertOk();

        $response->assertSee('class="hs"', false)
            ->assertSee('أفلام تُعرض هذا الأسبوع')
            ->assertSee($movie->name)
            ->assertSee('120 دقيقة')
            ->assertSee('+12')
            ->assertSee('وصف الفيلم التجريبي')
            ->assertSee('storage/movies/poster.jpg', false);
    }

    public function test_games_hero_shows_game_name_details_and_description(): void
    {
        $game = $this->game();

        $this->get('/games')->assertOk()
            ->assertSee('تعرّف على أجهزتنا')
            ->assertSee($game->name)
            ->assertSee('3 أجهزة')
            ->assertSee('شاشة 4K')
            ->assertSee('من 25 د.ل')
            ->assertSee('وصف الجهاز التجريبي');
    }

    public function test_club_hero_shows_club_name_details_and_description(): void
    {
        $club = Club::create([
            'name' => 'النادي الشرقي',
            'location' => 'الطابق الأول',
            'description' => 'وصف النادي التجريبي',
            'is_active' => true,
        ]);
        $club->allPackages()->create([
            'name' => 'جلسة', 'people_count' => 2, 'start_time' => '16:00',
            'end_time' => '18:00', 'price' => 60, 'is_active' => true,
        ]);

        $this->get('/club')->assertOk()
            ->assertSee('أندية ومساحات متاحة')
            ->assertSee($club->name)
            ->assertSee('الطابق الأول')
            ->assertSee('من 60 د.ل')
            ->assertSee('وصف النادي التجريبي');
    }

    public function test_events_hero_shows_package_name_details_and_description(): void
    {
        $package = EventPackage::create([
            'name' => 'باقة الاجتماعات',
            'includes' => 'قاعة خاصة + ضيافة',
            'people_count' => 12,
            'price' => 300,
            'is_active' => true,
        ]);

        $this->get('/cafe')->assertOk()
            ->assertSee('تعرّف على باقات المناسبات')
            ->assertSee($package->name)
            ->assertSee('12 أشخاص')
            ->assertSee('من 300 د.ل')
            ->assertSee('قاعة خاصة + ضيافة');
    }

    public function test_each_hero_only_shows_its_own_specialty(): void
    {
        $this->movie(['name' => 'اسم فيلم مميز']);
        $this->game(['name' => 'اسم جهاز مميز']);
        Club::create(['name' => 'اسم نادٍ مميز', 'is_active' => true]);
        EventPackage::create([
            'name' => 'اسم باقة مميزة', 'price' => 100, 'is_active' => true,
        ]);

        $this->get('/movies')->assertSee('اسم فيلم مميز')->assertDontSee('اسم جهاز مميز');
        $this->get('/games')->assertSee('اسم جهاز مميز')->assertDontSee('اسم فيلم مميز');
        $this->get('/club')->assertSee('اسم نادٍ مميز')->assertDontSee('اسم باقة مميزة');
        $this->get('/cafe')->assertSee('اسم باقة مميزة')->assertDontSee('اسم نادٍ مميز');
    }

    public function test_showcase_omits_inactive_items(): void
    {
        $this->game(['name' => 'جهاز مخفي', 'is_active' => false]);
        Club::create(['name' => 'نادٍ مخفي', 'is_active' => false]);
        EventPackage::create(['name' => 'باقة مخفية', 'price' => 50, 'is_active' => false]);

        $this->get('/games')->assertDontSee('جهاز مخفي');
        $this->get('/club')->assertDontSee('نادٍ مخفي');
        $this->get('/cafe')->assertDontSee('باقة مخفية');
    }

    public function test_showcase_section_is_hidden_when_there_is_nothing_to_show(): void
    {
        $this->get('/games')->assertOk()->assertDontSee('class="hs"', false);
        $this->get('/club')->assertOk()->assertDontSee('class="hs"', false);
        $this->get('/cafe')->assertOk()->assertDontSee('class="hs"', false);
    }

    public function test_showcase_builder_normalises_each_specialty(): void
    {
        $movie = $this->movie();
        $game = $this->game();

        $slides = (new HeroShowcase())->movies(collect([$movie]))->slides();

        $this->assertCount(1, $slides);
        $this->assertSame($movie->name, $slides[0]['title']);
        $this->assertSame('🎬', $slides[0]['icon']);
        $this->assertStringContainsString('120 دقيقة', $slides[0]['meta']);
        $this->assertStringContainsString('storage/movies/poster.jpg', $slides[0]['image']);
        $this->assertStringContainsString('linear-gradient', $slides[0]['gradient']);

        $gameSlides = (new HeroShowcase())->games(Game::with('prices')->get())->slides();
        $this->assertSame($game->name, $gameSlides[0]['title']);
        $this->assertStringContainsString('من 25 د.ل', $gameSlides[0]['meta']);
    }

    public function test_movie_video_upload_accepts_files_up_to_150_mb(): void
    {
        $this->assertSame(153600, config('uploads.movie_video_max_kb'));
        $this->assertSame('max:153600', config('livewire.temporary_file_upload.rules')[1]);
        $this->assertSame(30, config('livewire.temporary_file_upload.max_upload_time'));

        $this->actingAs(User::factory()->create());

        $movie = $this->movie();

        Livewire::test(EditMovie::class, ['record' => $movie->getRouteKey()])
            ->assertFormFieldExists('video')
            ->assertFormFieldExists('trailer_url')
            ->fillForm(['video' => null, 'trailer_url' => 'https://example.test/trailer.mp4'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://example.test/trailer.mp4', $movie->fresh()->trailer_url);
        $this->assertSame('https://example.test/trailer.mp4', $movie->fresh()->videoUrl());
    }

    public function test_movie_show_page_renders_the_uploaded_video(): void
    {
        $movie = $this->movie(['video' => 'movies/videos/trailer.mp4']);

        $this->get("/movies/{$movie->id}")->assertOk()
            ->assertSee('<video', false)
            ->assertSee('storage/movies/videos/trailer.mp4', false);
    }
}
