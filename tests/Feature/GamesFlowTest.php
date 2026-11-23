<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameBooking;
use App\Models\PlayTime;
use App\Models\Tournament;
use App\Models\TournamentAnnouncement;
use App\Models\TournamentParticipant;
use App\Models\User;
use App\Support\TournamentDraw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamesFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedGame(): array
    {
        $game = Game::create([
            'name' => 'PlayStation 5',
            'icon' => '🎮',
            'meta' => 'أحدث جهاز ألعاب',
            'rating' => 4.8,
            'badge' => 'trending',
        ]);

        $cheap = $game->prices()->create(['label' => '30 دقيقة', 'minutes' => 30, 'price' => 15, 'is_default' => true, 'sort_order' => 1]);
        $game->prices()->create(['label' => 'ساعة', 'minutes' => 60, 'price' => 25, 'sort_order' => 2]);

        $slot = $game->playTimes()->create([
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'is_active' => true,
        ]);

        return [$game, $cheap, $slot];
    }

    public function test_games_page_renders_from_database(): void
    {
        [$game] = $this->seedGame();

        $this->get('/games')
            ->assertOk()
            ->assertSee('PlayStation 5')
            ->assertSee('css/games.css')
            ->assertSee('js/games.js');
    }

    public function test_games_page_embeds_payload_for_the_frontend(): void
    {
        [$game] = $this->seedGame();

        $html = $this->get('/games')->assertOk()->getContent();

        $this->assertStringContainsString('window.GAMES', $html);

        preg_match('/window\.GAMES = (\{.*?\});\s*<\/script>/s', $html, $m);
        $this->assertNotEmpty($m, 'GAMES payload not found');

        $payload = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($game->id, $payload['games'][0]['id']);
        $this->assertSame('PlayStation 5', $payload['games'][0]['name']);
        $this->assertCount(2, $payload['games'][0]['prices']);
        $this->assertTrue($payload['games'][0]['prices'][0]['isDefault']);
        $this->assertCount(7, $payload['weekDays']);
        $this->assertNotEmpty($payload['games'][0]['times'][0]);
    }

    public function test_inactive_games_are_hidden_from_the_public_page(): void
    {
        Game::create(['name' => 'لعبة مخفية', 'is_active' => false]);

        $this->get('/games')->assertOk()->assertDontSee('لعبة مخفية');
    }

    public function test_booking_creates_pending_record_with_reference(): void
    {
        [$game, $price, $slot] = $this->seedGame();

        $response = $this->post("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
            'full_name' => 'أحمد محمد علي',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ]);

        $booking = GameBooking::firstOrFail();

        $response->assertRedirect(route('site.games'));
        $this->assertSame(GameBooking::PAYMENT_PENDING, $booking->payment_status);
        $this->assertSame(GameBooking::ATTENDANCE_PENDING, $booking->attendance_status);
        $this->assertSame('15.00', (string) $booking->price);
        $this->assertSame('30 دقيقة', $booking->duration_label);
        $this->assertSame(30, $booking->duration_minutes);
        $this->assertStringStartsWith('GM-', $booking->booking_ref);
    }

    public function test_booking_returns_json_with_qr_when_requested(): void
    {
        [$game, $price, $slot] = $this->seedGame();

        $response = $this->postJson("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
            'full_name' => 'أحمد محمد علي',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ]);

        $booking = GameBooking::firstOrFail();

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('reference', $booking->booking_ref)
            ->assertJsonPath('game', 'PlayStation 5')
            ->assertJsonPath('price', 15);

        $this->assertStringContainsString($booking->booking_ref, $response->json('qr_url'));
    }

    public function test_full_name_must_be_three_parts(): void
    {
        [$game, $price, $slot] = $this->seedGame();

        $this->from('/games')->post("/games/{$game->id}/book", [
            'game_price_id' => $price->id,
            'play_time_id' => $slot->id,
            'play_date' => now()->toDateString(),
            'full_name' => 'أحمد',
            'country_code' => '+218',
            'country_name' => 'ليبيا',
            'phone' => '0913504510',
            'whatsapp_country_code' => '+218',
            'whatsapp' => '0925271793',
        ])->assertSessionHasErrors('full_name');

        $this->assertDatabaseCount('game_bookings', 0);
    }

    public function test_tournament_draw_builds_single_elimination_bracket(): void
    {
        [$game] = $this->seedGame();

        $tournament = Tournament::create([
            'name' => 'بطولة FIFA',
            'game_id' => $game->id,
            'participants_count' => 4,
            'format' => 'knockout',
            'status' => 'open',
        ]);

        foreach (['علي', 'محمد', 'سعد', 'خالد'] as $name) {
            TournamentParticipant::create(['tournament_id' => $tournament->id, 'name' => $name]);
        }

        app(TournamentDraw::class)->draw($tournament);
        $tournament->refresh();

        $this->assertNotNull($tournament->drawn_at);
        $this->assertSame('drawn', $tournament->status);
        $this->assertSame(3, $tournament->matches()->count());
        $this->assertSame(2, $tournament->matches()->where('round', 1)->count());
        $this->assertSame(1, $tournament->matches()->where('round', 2)->count());
        $this->assertSame(4, $tournament->participants()->whereNotNull('seed')->count());

        $final = $tournament->matches()->where('round', 2)->first();
        $this->assertSame('النهائي', $final->round_label);
        $this->assertNull($final->player_one_id);
        $this->assertNull($final->player_two_id);
    }

    public function test_announcement_appears_on_games_page(): void
    {
        [$game] = $this->seedGame();

        $tournament = Tournament::create([
            'name' => 'بطولة الأسبوع', 'game_id' => $game->id,
            'participants_count' => 8, 'format' => 'knockout', 'status' => 'open',
        ]);

        TournamentAnnouncement::create([
            'tournament_id' => $tournament->id,
            'title' => 'انضم إلى البطولة',
            'body' => 'جوائز قيّمة للفائزين',
            'badge' => 'بطولة الأسبوع',
            'published_at' => now(),
            'is_published' => true,
        ]);

        $this->get('/games')->assertOk()->assertSee('انضم إلى البطولة');
    }

    public function test_admin_game_resources_render_for_authenticated_user(): void
    {
        [$game] = $this->seedGame();
        $user = User::factory()->create();

        foreach ([
            '/admin/games', '/admin/games/create', "/admin/games/{$game->id}/edit",
            '/admin/play-times', '/admin/game-bookings',
            '/admin/tournaments', '/admin/tournaments/create',
            '/admin/tournament-announcements',
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_play_time_day_names_cover_the_week(): void
    {
        $this->assertCount(7, PlayTime::$dayNames);
    }
}
