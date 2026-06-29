<?php

namespace Database\Factories;

use App\Enums\GameSessionStatus;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSession>
 */
class GameSessionFactory extends Factory
{
    protected $model = GameSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'room_master_id' => User::factory(),
            'status' => GameSessionStatus::Waiting,
            'started_at' => null,
            'finishing_at' => null,
            'completed_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this;
    }

    public function withRoomMasterJoined(): static
    {
        return $this->afterCreating(function (GameSession $session): void {
            $session->players()->syncWithoutDetaching([$session->room_master_id]);
        });
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameSessionStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    public function finishing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameSessionStatus::Finishing,
            'started_at' => now()->subHour(),
            'finishing_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameSessionStatus::Completed,
            'started_at' => now()->subHours(2),
            'finishing_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }
}
