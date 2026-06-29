<?php

namespace Database\Factories;

use App\Enums\IdentityProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => strtolower(fake()->unique()->userName()),
            'is_admin' => false,
            'total_xp' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->identities()->create([
                'provider' => IdentityProvider::Password,
                'secret' => 'password',
            ]);
        });
    }

    public function withoutPasswordIdentity(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->identities()->delete();
        });
    }
}
