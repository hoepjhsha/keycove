<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $settings = [
            ['key' => 'theme', 'value' => 'dark', 'description' => 'User interface theme preference'],
            ['key' => 'language', 'value' => 'en', 'description' => 'Preferred language for the interface'],
            ['key' => 'notifications_email', 'value' => 'true', 'description' => 'Enable email notifications'],
            ['key' => 'notifications_push', 'value' => 'false', 'description' => 'Enable push notifications'],
            ['key' => 'two_factor_enabled', 'value' => 'false', 'description' => 'Two-factor authentication status'],
            ['key' => 'newsletter_subscription', 'value' => 'true', 'description' => 'Subscribe to newsletter'],
            ['key' => 'privacy_profile_public', 'value' => 'false', 'description' => 'Make profile publicly visible'],
            ['key' => 'currency', 'value' => 'USD', 'description' => 'Preferred currency'],
            ['key' => 'timezone', 'value' => 'UTC', 'description' => 'User timezone'],
        ];

        $setting = fake()->randomElement($settings);

        return [
            'user_id' => User::factory(),
            'key' => $setting['key'],
            'value' => $setting['value'],
            'description' => $setting['description'],
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user?->id ?? User::factory()->create()->id,
            ];
        });
    }

    public function theme(string $theme = 'dark'): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'theme',
            'value' => $theme,
            'description' => 'User interface theme preference',
        ]);
    }

    public function language(string $lang = 'en'): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'language',
            'value' => $lang,
            'description' => 'Preferred language for the interface',
        ]);
    }

    public function notifications(bool $enabled = true): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => 'notifications_email',
            'value' => $enabled ? 'true' : 'false',
            'description' => 'Enable email notifications',
        ]);
    }
}
