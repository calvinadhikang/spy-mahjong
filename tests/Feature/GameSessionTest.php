<?php

namespace Tests\Feature;

use App\Enums\GameSessionStatus;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_shows_active_session(): void
    {
        $master = User::factory()->create(['username' => 'master']);
        $player = User::factory()->create(['username' => 'guest']);

        $session = GameSession::factory()->create([
            'name' => 'Friday Game',
            'room_master_id' => $master->id,
        ]);

        $session->players()->sync([$master->id, $player->id]);

        $response = $this->actingAs($master)->get(route('user.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('user/dashboard')
            ->where('activeSession.name', 'Friday Game')
            ->where('activeSession.status', GameSessionStatus::Waiting->value));
    }

    public function test_user_can_create_game_session_without_players(): void
    {
        $master = User::factory()->create(['username' => 'master']);

        $response = $this->actingAs($master)->post(route('game-sessions.store'), [
            'name' => 'Spy Night',
        ]);

        $session = GameSession::query()->first();

        $this->assertNotNull($session);
        $this->assertSame('Spy Night', $session->name);
        $this->assertSame($master->id, $session->room_master_id);
        $this->assertSame(GameSessionStatus::Waiting, $session->status);
        $this->assertSame([$master->id], $session->players()->pluck('users.id')->all());

        $response->assertRedirect(route('game-sessions.show', $session));
    }

    public function test_room_master_can_add_players_before_game_starts(): void
    {
        $master = User::factory()->create();
        $player = User::factory()->create();
        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);

        $this->actingAs($master)
            ->post(route('game-sessions.players.store', $session), [
                'user_id' => $player->id,
            ])
            ->assertRedirect(route('game-sessions.show', $session));

        $this->assertTrue($session->fresh()->hasPlayer($player));
    }

    public function test_non_room_master_cannot_add_players(): void
    {
        $master = User::factory()->create();
        $other = User::factory()->create();
        $player = User::factory()->create();
        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id, $other->id]);

        $this->actingAs($other)
            ->post(route('game-sessions.players.store', $session), [
                'user_id' => $player->id,
            ])
            ->assertForbidden();
    }

    public function test_room_master_cannot_add_more_than_four_players(): void
    {
        $master = User::factory()->create();
        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);

        $extraPlayers = User::factory()->count(3)->create();
        $session->players()->sync([
            $master->id,
            ...$extraPlayers->pluck('id'),
        ]);

        $fourthGuest = User::factory()->create();

        $this->actingAs($master)
            ->post(route('game-sessions.players.store', $session), [
                'user_id' => $fourthGuest->id,
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertFalse($session->fresh()->hasPlayer($fourthGuest));
    }

    public function test_room_master_cannot_add_players_after_game_starts(): void
    {
        $master = User::factory()->create();
        $player = User::factory()->create();
        $session = GameSession::factory()->inProgress()->create([
            'room_master_id' => $master->id,
        ]);

        $this->actingAs($master)
            ->post(route('game-sessions.players.store', $session), [
                'user_id' => $player->id,
            ])
            ->assertSessionHasErrors('user_id');
    }

    public function test_room_master_can_move_session_through_finishing_to_completed(): void
    {
        $master = User::factory()->create();
        $guest = User::factory()->create();
        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id, $guest->id]);

        $this->actingAs($master)
            ->post(route('game-sessions.start', $session))
            ->assertRedirect(route('game-sessions.show', $session));

        $session->refresh();
        $this->assertSame(GameSessionStatus::InProgress, $session->status);

        $this->actingAs($master)
            ->post(route('game-sessions.finish', $session))
            ->assertRedirect(route('game-sessions.show', $session));

        $session->refresh();
        $this->assertSame(GameSessionStatus::Finishing, $session->status);
        $this->assertNotNull($session->finishing_at);

        $this->actingAs($guest)
            ->post(route('game-sessions.money.store', $session), [
                'total_money' => 150.50,
            ])
            ->assertRedirect(route('game-sessions.show', $session));

        $this->actingAs($master)
            ->post(route('game-sessions.complete', $session))
            ->assertSessionHasErrors('session');

        $this->actingAs($master)
            ->post(route('game-sessions.money.store', $session), [
                'user_id' => $master->id,
                'total_money' => -150.50,
            ])
            ->assertRedirect(route('game-sessions.show', $session));

        $this->actingAs($master)
            ->post(route('game-sessions.complete', $session))
            ->assertRedirect(route('game-sessions.show', $session));

        $session->refresh();
        $this->assertSame(GameSessionStatus::Completed, $session->status);
        $this->assertNotNull($session->completed_at);
    }

    public function test_player_can_only_submit_their_own_total(): void
    {
        $master = User::factory()->create();
        $guest = User::factory()->create();
        $session = GameSession::factory()->finishing()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id, $guest->id]);

        $this->actingAs($guest)
            ->post(route('game-sessions.money.store', $session), [
                'user_id' => $master->id,
                'total_money' => 100,
            ])
            ->assertForbidden();
    }

    public function test_room_master_can_submit_totals_for_any_player(): void
    {
        $master = User::factory()->create();
        $guest = User::factory()->create();
        $session = GameSession::factory()->finishing()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id, $guest->id]);

        $this->actingAs($master)
            ->post(route('game-sessions.money.store', $session), [
                'user_id' => $guest->id,
                'total_money' => 250,
            ])
            ->assertRedirect(route('game-sessions.show', $session));

        $guestPivot = $session->players()->whereKey($guest->id)->first()->pivot;

        $this->assertEquals(250, (float) $guestPivot->total_money);
        $this->assertNotNull($guestPivot->money_submitted_at);
    }

    public function test_non_room_master_cannot_start_session(): void
    {
        $master = User::factory()->create();
        $other = User::factory()->create();
        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id, $other->id]);

        $this->actingAs($other)
            ->post(route('game-sessions.start', $session))
            ->assertForbidden();
    }

    public function test_users_can_be_searched_by_name_or_username(): void
    {
        User::factory()->create([
            'name' => 'Alice Wong',
            'username' => 'alice',
        ]);

        $response = $this->get(route('users.search', ['q' => 'alice']));

        $response->assertOk();
        $response->assertJsonPath('users.0.username', 'alice');
    }
}
