<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'username' => 'hoepjhsha',
            'email' => 'hoep@hoep',
            'password' => Hash::make('hoep'),
            'role' => UserRole::SuperAdmin,
            'status' => UserStatus::Active,
        ]);

        $this->call(UserSeeder::class);

        Category::factory()->seedGameGenres();
    }
}
