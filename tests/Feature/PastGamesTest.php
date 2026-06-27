<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PastGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_completed_game_history(): void
    {
        $master = User::factory()->create();
        $guest = User::factory()->create();

        $session = GameSession::factory()->completed()->create([
            'name' => 'Old Game',
            'room_master_id' => $master->id,
        ]);

        $session->players()->sync([
            $master->id => [
                'total_money' => -100,
                'money_submitted_at' => now(),
            ],
            $guest->id => [
                'total_money' => 100,
                'money_submitted_at' => now(),
            ],
        ]);

        $response = $this->actingAs($guest)->get(route('user.history'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('user/history')
            ->has('sessions', 1)
            ->where('sessions.0.name', 'Old Game')
            ->where('sessions.0.viewer_total_money', 100));
    }

    public function test_active_sessions_are_not_listed_in_history(): void
    {
        $user = User::factory()->create();

        $session = GameSession::factory()->inProgress()->create([
            'room_master_id' => $user->id,
        ]);
        $session->players()->sync([$user->id]);

        $this->actingAs($user)
            ->get(route('user.history'))
            ->assertInertia(fn ($page) => $page
                ->component('user/history')
                ->has('sessions', 0));
    }
}
