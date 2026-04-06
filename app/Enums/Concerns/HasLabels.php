<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Trait HasLabels
 *
 * Provides localized labels for enum cases using translation files located at
 * resources/lang/{locale}/enums.php. Translation keys follow the pattern:
 *
 *   enums.{enum_snake_case}.{case_name}
 *
 * Example:
 *   resources/lang/en/enums.php
 *   return [
 *       'user_role' => [
 *           'User' => 'User',
 *           // ...
 *       ],
 *   ];
 *
 * Usage:
 *   UserRole::User->label('en'); // 'User'
 *   UserRole::User->label('vi'); // 'Người dùng'
 *   UserRole::labels('en'); // ['User' => 'User', 'Seller' => 'Seller', ...]
 */
trait HasLabels
{
    /**
     * Return localized label for this enum case.
     *
     * @param  string|null  $locale  Locale code (e.g., 'en', 'vi'). If null, uses app locale.
     */
    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $key = static::translationKey().'.'.$this->name;

        $value = Lang::get($key, [], $locale);

        if (is_array($value)) {
            // Unexpected: translation returned array; fall back to case name
            $label = $this->name;
        } elseif ($value === $key) {
            // No translation found: try English fallback
            $fallback = Lang::get($key, [], 'en');
            $label = ($fallback === $key) ? $this->name : $fallback;
        } else {
            $label = $value;
        }

        return $label;
    }

    /**
     * Return all labels for the enum as [caseName => label].
     *
     * @return array<string, string>
     */
    public static function labels(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $key = static::translationKey();

        $labels = Lang::get($key, [], $locale);

        if (is_array($labels) && ! empty($labels)) {
            return $labels;
        }

        // Try English fallback
        $fallback = Lang::get($key, [], 'en');
        if (is_array($fallback) && ! empty($fallback)) {
            return $fallback;
        }

        // Last resort: return case names
        $out = [];
        foreach (static::cases() as $case) {
            $out[$case->name] = $case->name;
        }

        return $out;
    }

    /**
     * Compute base translation key for the enum.
     * Example: App\Enums\UserRole => enums.user_role
     */
    protected static function translationKey(): string
    {
        return 'enums.'.Str::snake(class_basename(static::class));
    }
}
