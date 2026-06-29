<?php

namespace Tests\Feature\Auth;

use App\Enums\IdentityProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_displayed(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register'), [
            'username' => 'newplayer',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::query()->where('username', 'newplayer')->first();

        $this->assertNotNull($user);
        $this->assertSame('newplayer', $user->username);

        $identity = $user->identities()->first();

        $this->assertNotNull($identity);
        $this->assertSame(IdentityProvider::Password, $identity->provider);

        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'taken',
        ]);

        $response = $this->post(route('register'), [
            'username' => 'taken',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_authenticated_users_can_visit_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertOk();
    }
}
