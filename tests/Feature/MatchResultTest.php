<?php

namespace Tests\Feature;

use App\Enums\GameSessionStatus;
use App\Enums\PlayerOutcome;
use App\Models\GameSession;
use App\Models\GameSessionPlayerResult;
use App\Models\Level;
use App\Models\User;
use App\Models\XpRewardSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_session_awards_xp_and_writes_results(): void
    {
        XpRewardSetting::query()->create([
            'first_place_xp' => 100,
            'second_place_xp' => 60,
            'third_place_xp' => 30,
            'fourth_place_xp' => 10,
            'loss_xp' => -5,
        ]);

        $master = User::factory()->create();
        $winner = User::factory()->create();
        $loser = User::factory()->create();

        $session = GameSession::factory()->finishing()->create([
            'room_master_id' => $master->id,
        ]);

        $session->players()->sync([
            $master->id => [
                'total_money' => 0,
                'money_submitted_at' => now(),
            ],
            $winner->id => [
                'total_money' => 200,
                'money_submitted_at' => now(),
            ],
            $loser->id => [
                'total_money' => -200,
                'money_submitted_at' => now(),
            ],
        ]);

        $this->actingAs($master)
            ->post(route('game-sessions.complete', $session))
            ->assertRedirect(route('game-sessions.show', $session));

        $session->refresh();
        $this->assertSame(GameSessionStatus::Completed, $session->status);
        $this->assertNotNull($session->scoring_rule_version);

        $winnerResult = GameSessionPlayerResult::query()
            ->where('game_session_id', $session->id)
            ->where('user_id', $winner->id)
            ->first();

        $loserResult = GameSessionPlayerResult::query()
            ->where('game_session_id', $session->id)
            ->where('user_id', $loser->id)
            ->first();

        $this->assertNotNull($winnerResult);
        $this->assertSame(1, $winnerResult->placement);
        $this->assertSame(PlayerOutcome::Win, $winnerResult->outcome);
        $this->assertSame(100, $winnerResult->xp_earned);

        $this->assertNotNull($loserResult);
        $this->assertSame(PlayerOutcome::Loss, $loserResult->outcome);
        $this->assertSame(-5, $loserResult->xp_earned);

        $this->assertSame(100, $winner->fresh()->total_xp);
        $this->assertSame(-5, $loser->fresh()->total_xp);
    }

    public function test_profile_shows_progression(): void
    {
        Level::factory()->create([
            'name' => 'Regular',
            'min_xp' => 0,
            'sort_order' => 1,
        ]);

        $user = User::factory()->create([
            'total_xp' => 150,
        ]);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertInertia(fn ($page) => $page
                ->where('progression.total_xp', 150)
                ->where('progression.level_name', 'Regular'));
    }
}
