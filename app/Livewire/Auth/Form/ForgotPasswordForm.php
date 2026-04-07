<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Form;

use App\Mail\ResetPasswordOtp;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ForgotPasswordForm extends Form
{
    #[Validate([
        'required',
        'email',
        new Exists(table: User::class, column: 'email'),
    ])]
    public string $email = '';

    public function sendOtpProcess(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        $otp = random_int(100000, 999999);
        Cache::put('otp_reset_'.$this->email, $otp, now()->addMinutes(3));

        $email = $this->email;
        dispatch(function () use ($email, $otp) {
            Mail::to($email)->send(new ResetPasswordOtp($otp));
        });

        session()->put('reset_password_email', $this->email);
        session()->put('otp_sent_at', now()->timestamp);

        RateLimiter::hit($this->throttleKey(), 60);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());
        throw ValidationException::withMessages([
            'form.email' => 'Too many requests. Please try again in '.$seconds.' seconds.',
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate('forgot-password|'.request()->ip());
    }
}
