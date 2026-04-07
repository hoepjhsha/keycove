<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Form;

use App\Mail\ResetPasswordOtp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class VerifyOtpForm extends Form
{
    public string $otp_code = '';

    public function verifyProcess(): void
    {
        $this->validate(['otp_code' => 'required|digits:6']);
        $this->ensureIsNotRateLimited();

        $email = session('reset_password_email');
        $cachedOtp = Cache::get('otp_reset_'.$email);

        if (! $cachedOtp || $cachedOtp != $this->otp_code) {
            RateLimiter::hit($this->throttleKey(), 300);
            throw ValidationException::withMessages([
                'otp_code' => 'Invalid or expired OTP code.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->put('otp_verified', true);
        Cache::forget('otp_reset_'.$email);
    }

    public function getRemainingTime(): int
    {
        $sentAt = session('otp_sent_at');
        if (! $sentAt) {
            return 0;
        }

        $elapsed = now()->timestamp - $sentAt;

        return max(0, 180 - $elapsed);
    }

    public function resendOtp(): void
    {
        $this->ensureIsNotRateLimited();

        $email = session('reset_password_email');
        if (! $email) {
            throw ValidationException::withMessages([
                'otp_code' => 'Session expired. Please start over.',
            ]);
        }

        $otp = random_int(100000, 999999);
        Cache::put('otp_reset_'.$email, $otp, now()->addMinutes(3));

        $emailAddress = $email;
        dispatch(function () use ($emailAddress, $otp) {
            Mail::to($emailAddress)->send(new ResetPasswordOtp($otp));
        });

        session()->put('otp_sent_at', now()->timestamp);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'otp_code' => 'Too many attempts. Please try again in 5 minutes.',
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate('verify-otp|'.session('reset_password_email').'|'.request()->ip());
    }
}
