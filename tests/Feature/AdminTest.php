<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\User;
use App\Models\XpRewardSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.xp-settings.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.levels.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->get(route('admin.xp-settings.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_and_update_xp_settings(): void
    {
        $admin = User::factory()->admin()->create();
        XpRewardSetting::current();

        $this->actingAs($admin)
            ->get(route('admin.xp-settings.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/xp-settings')
                ->where('settings.first_place_xp', 100)
                ->where('settings.loss_xp', 0));

        $this->actingAs($admin)
            ->put(route('admin.xp-settings.update'), [
                'first_place_xp' => 200,
                'second_place_xp' => 120,
                'third_place_xp' => 60,
                'fourth_place_xp' => 20,
                'loss_xp' => -15,
            ])
            ->assertRedirect(route('admin.xp-settings.edit'))
            ->assertSessionHas('xp_settings_updated', true);

        $settings = XpRewardSetting::current()->fresh();

        $this->assertSame(200, $settings->first_place_xp);
        $this->assertSame(-15, $settings->loss_xp);
    }

    public function test_admin_can_manage_levels(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.levels.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/levels')
                ->has('levels', 0));

        $this->actingAs($admin)
            ->post(route('admin.levels.store'), [
                'name' => 'Beginner',
                'min_xp' => 0,
            ])
            ->assertRedirect(route('admin.levels.index'));

        $level = Level::query()->first();

        $this->assertNotNull($level);
        $this->assertSame('Beginner', $level->name);

        $this->actingAs($admin)
            ->put(route('admin.levels.update', $level), [
                'name' => 'Novice',
                'min_xp' => 10,
            ])
            ->assertRedirect(route('admin.levels.index'));

        $this->assertSame('Novice', $level->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.levels.destroy', $level))
            ->assertRedirect(route('admin.levels.index'));

        $this->assertDatabaseMissing('levels', ['id' => $level->id]);
    }
}
