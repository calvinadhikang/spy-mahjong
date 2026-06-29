<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveSessionBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_shows_block_when_user_has_active_session(): void
    {
        $user = User::factory()->admin()->create();
        $session = GameSession::factory()->inProgress()->create([
            'room_master_id' => $user->id,
        ]);
        $session->players()->sync([$user->id]);

        $this->actingAs($user)
            ->get(route('game-sessions.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('sessions/create')
                ->where('showActiveSessionBlock', true)
                ->where('activeSession.id', $session->id));
    }

    public function test_store_redirects_with_block_flash_when_user_has_active_session(): void
    {
        $user = User::factory()->admin()->create();
        $session = GameSession::factory()->inProgress()->create([
            'room_master_id' => $user->id,
        ]);
        $session->players()->sync([$user->id]);

        $this->actingAs($user)
            ->post(route('game-sessions.store'), ['name' => 'Another Game'])
            ->assertRedirect(route('game-sessions.create'))
            ->assertSessionHas('active_session_block', true);

        $this->assertDatabaseMissing('game_sessions', [
            'name' => 'Another Game',
        ]);
    }

    public function test_viewing_another_session_shows_block(): void
    {
        $user = User::factory()->create();
        $otherMaster = User::factory()->create();

        $active = GameSession::factory()->inProgress()->create([
            'room_master_id' => $user->id,
        ]);
        $active->players()->sync([$user->id]);

        $other = GameSession::factory()->create([
            'room_master_id' => $otherMaster->id,
        ]);
        $other->players()->sync([$otherMaster->id]);

        $this->actingAs($user)
            ->get(route('game-sessions.show', $other))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('showActiveSessionBlock', true)
                ->where('activeSession.id', $active->id));
    }

    public function test_joining_another_session_redirects_with_block_flash(): void
    {
        $user = User::factory()->create();
        $otherMaster = User::factory()->create();

        $active = GameSession::factory()->inProgress()->create([
            'room_master_id' => $user->id,
        ]);
        $active->players()->sync([$user->id]);

        $other = GameSession::factory()->create([
            'room_master_id' => $otherMaster->id,
        ]);
        $other->players()->sync([$otherMaster->id]);

        $this->actingAs($user)
            ->post(route('game-sessions.join', $other))
            ->assertRedirect(route('user.dashboard'))
            ->assertSessionHas('active_session_block', true);

        $this->assertFalse($other->fresh()->hasPlayer($user));
    }

    public function test_room_master_with_unjoined_waiting_session_is_blocked_from_creating_another(): void
    {
        $user = User::factory()->admin()->create();
        GameSession::factory()->create([
            'room_master_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('game-sessions.store'), ['name' => 'Another Game'])
            ->assertRedirect(route('game-sessions.create'))
            ->assertSessionHas('active_session_block', true);

        $this->assertDatabaseMissing('game_sessions', [
            'name' => 'Another Game',
        ]);
    }

    public function test_room_master_cannot_add_player_with_active_session_elsewhere(): void
    {
        $master = User::factory()->create();
        $busyPlayer = User::factory()->create();
        $otherMaster = User::factory()->create();

        $busySession = GameSession::factory()->inProgress()->create([
            'room_master_id' => $otherMaster->id,
        ]);
        $busySession->players()->sync([$busyPlayer->id, $otherMaster->id]);

        $session = GameSession::factory()->create([
            'room_master_id' => $master->id,
        ]);
        $session->players()->sync([$master->id]);

        $this->actingAs($master)
            ->post(route('game-sessions.players.store', $session), [
                'user_id' => $busyPlayer->id,
            ])
            ->assertSessionHasErrors('user_id');

        $this->assertFalse($session->fresh()->hasPlayer($busyPlayer));
    }
}
