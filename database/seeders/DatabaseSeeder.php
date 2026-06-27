<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use App\Models\XpRewardSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        XpRewardSetting::current();

        Level::query()->insert([
            [
                'name' => 'Beginner',
                'min_xp' => 0,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Regular',
                'min_xp' => 100,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shark',
                'min_xp' => 500,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        User::factory()->create([
            'name' => 'Demo Player',
            'username' => 'demo',
        ]);
    }
}
