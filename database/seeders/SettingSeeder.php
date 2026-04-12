<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::get();

        if ($users->isEmpty()) {
            return;
        }

        $settingTemplates = [
            ['key' => 'theme', 'values' => ['light', 'dark', 'system']],
            ['key' => 'language', 'values' => ['en', 'vi']],
            ['key' => 'notifications.email_orders', 'values' => ['true', 'false']],
            ['key' => 'notifications.email_promotions', 'values' => ['true', 'false']],
            ['key' => 'notifications.email_disputes', 'values' => ['true', 'false']],
            ['key' => 'notifications.push_orders', 'values' => ['true', 'false']],
            ['key' => 'privacy.show_profile', 'values' => ['true', 'false']],
            ['key' => 'privacy.show_purchase_history', 'values' => ['true', 'false']],
            ['key' => 'security.two_factor_enabled', 'values' => ['true', 'false']],
        ];

        foreach ($users as $user) {
            foreach ($settingTemplates as $template) {
                Setting::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'key' => $template['key'],
                    ],
                    [
                        'value' => fake()->randomElement($template['values']),
                        'description' => 'User preference for '.$template['key'],
                    ]
                );
            }
        }
    }
}
