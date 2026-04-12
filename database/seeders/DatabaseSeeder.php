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
        // Create SuperAdmin
        User::firstOrCreate(
            ['email' => 'hoep@hoep'],
            [
                'username' => 'hoepjhsha',
                'password' => Hash::make('hoep'),
                'role' => UserRole::SuperAdmin,
                'status' => UserStatus::Active,
            ]
        );

        // Seed game genres first
        Category::factory()->seedGameGenres();

        // Seed in order of dependencies
        $this->call([
            UserSeeder::class,           // Users, Profiles, Sellers, Wallets
            AttributeSeeder::class,      // Regions, Platforms, Operating Systems
            SystemConfigSeeder::class,   // System configurations
            ProductSeeder::class,        // Products, Variants, Listings, Keys
            OrderSeeder::class,          // Orders, OrderItems, Escrows, Transactions
            ReviewSeeder::class,         // Reviews, Review Responses
            DisputeSeeder::class,        // Complaints, Complaint Messages
            CartSeeder::class,           // Carts, Cart Items
            SettingSeeder::class,        // User settings
        ]);
    }
}
