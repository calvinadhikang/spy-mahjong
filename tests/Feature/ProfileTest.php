<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo Player',
            'username' => 'demo',
        ]);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('user/profile')
                ->where('profile.name', 'Demo Player')
                ->where('profile.username', 'demo')
                ->where('profile.can_update_password', true)
                ->where('stats.total_games', 0));
    }

    public function test_profile_includes_game_stats(): void
    {
        $master = User::factory()->create();
        $guest = User::factory()->create();

        $winningSession = GameSession::factory()->completed()->create([
            'room_master_id' => $master->id,
            'completed_at' => now()->subDay(),
        ]);
        $winningSession->players()->sync([
            $master->id => [
                'total_money' => -50,
                'money_submitted_at' => now(),
            ],
            $guest->id => [
                'total_money' => 50,
                'money_submitted_at' => now(),
            ],
        ]);

        $losingSession = GameSession::factory()->completed()->create([
            'room_master_id' => $guest->id,
            'completed_at' => now(),
        ]);
        $losingSession->players()->sync([
            $guest->id => [
                'total_money' => -25,
                'money_submitted_at' => now(),
            ],
            $master->id => [
                'total_money' => 25,
                'money_submitted_at' => now(),
            ],
        ]);

        $this->actingAs($guest)
            ->get(route('user.profile'))
            ->assertInertia(fn ($page) => $page
                ->where('stats.total_games', 2)
                ->where('stats.wins', 1)
                ->where('stats.losses', 1)
                ->where('stats.break_even', 0)
                ->where('stats.win_rate', 50)
                ->where('stats.decided_win_rate', 50)
                ->where('stats.total_profit', 25)
                ->where('stats.average_profit', 12.5)
                ->where('stats.best_game', 50)
                ->where('stats.worst_game', -25)
                ->where('stats.games_as_room_master', 1)
                ->where('stats.games_as_guest', 1)
                ->where('stats.room_master_wins', 0)
                ->where('stats.room_master_losses', 1)
                ->where('stats.guest_wins', 1)
                ->where('stats.guest_losses', 0)
                ->where('stats.current_streak.type', 'loss')
                ->where('stats.current_streak.count', 1));
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'oldname',
        ]);

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => 'New Name',
                'username' => 'newname',
            ])
            ->assertRedirect(route('user.profile'))
            ->assertSessionHas('profile_updated', true);

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertSame('newname', $user->username);
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'username' => 'player1',
        ]);

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'current_password' => 'password',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertRedirect(route('user.profile'));

        $this->post(route('login'), [
            'username' => 'player1',
            'password' => 'new-password-1',
        ])->assertRedirect(route('user.dashboard'));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'username' => 'player1',
        ]);

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'current_password' => 'wrong-password',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_username_must_remain_unique(): void
    {
        User::factory()->create(['username' => 'taken']);
        $user = User::factory()->create(['username' => 'mine']);

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => $user->name,
                'username' => 'taken',
            ])
            ->assertSessionHasErrors('username');
    }
}
