<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 admins
        User::factory()->count(3)->admin()->has(UserProfile::factory(), 'profile')->create();

        // Create 4 sellers
        User::factory()->count(4)->seller()->has(UserProfile::factory(), 'profile')->create();

        // Create 5 users
        User::factory()->count(5)->has(UserProfile::factory(), 'profile')->create();
    }
}
