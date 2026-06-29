<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_displayed(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('admin/login'));
    }

    public function test_wrong_admin_password_is_rejected(): void
    {
        $this->post(route('admin.login'), [
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('password');

        $this->assertNotTrue(session('admin_console_authenticated'));
    }

    public function test_correct_admin_password_grants_console_access(): void
    {
        $this->post(route('admin.login'), [
            'password' => 'spy_admin',
        ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(session('admin_console_authenticated'));
    }

    public function test_guest_cannot_access_admin_users_page(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_console_authenticated_user_can_manage_admin_flags(): void
    {
        $player = User::factory()->create(['username' => 'player1']);

        $this->withSession(['admin_console_authenticated' => true])
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/users')
                ->where('users.0.username', 'player1')
                ->where('users.0.is_admin', false));

        $this->withSession(['admin_console_authenticated' => true])
            ->put(route('admin.users.update', $player), [
                'is_admin' => true,
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('admin_user_updated', true);

        $this->assertTrue($player->fresh()->is_admin);

        $this->withSession(['admin_console_authenticated' => true])
            ->put(route('admin.users.update', $player), [
                'is_admin' => false,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertFalse($player->fresh()->is_admin);
    }

    public function test_admin_console_can_be_signed_out(): void
    {
        $this->withSession(['admin_console_authenticated' => true])
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertNotTrue(session('admin_console_authenticated'));
    }

    public function test_authenticated_admin_login_redirects_to_users_when_already_signed_in(): void
    {
        $this->withSession(['admin_console_authenticated' => true])
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.users.index'));
    }
}
