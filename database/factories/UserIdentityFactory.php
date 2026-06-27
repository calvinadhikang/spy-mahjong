<?php

namespace Database\Factories;

use App\Enums\IdentityProvider;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserIdentity>
 */
class UserIdentityFactory extends Factory
{
    protected $model = UserIdentity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => IdentityProvider::Password,
            'provider_id' => null,
            'secret' => 'password',
            'email' => null,
            'metadata' => null,
            'last_used_at' => null,
        ];
    }

    public function google(?string $providerId = null, ?string $email = null): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => IdentityProvider::Google,
            'provider_id' => $providerId ?? fake()->uuid(),
            'secret' => null,
            'email' => $email ?? fake()->safeEmail(),
        ]);
    }
}
