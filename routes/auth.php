<?php

use App\Http\Controllers\Auth\Logout;
use App\Livewire\Auth\Action\ForgotPassword;
use App\Livewire\Auth\Action\Login;
use App\Livewire\Auth\Action\Register;
use App\Livewire\Auth\Action\ResetPassword;
use App\Livewire\Auth\Action\VerifyOtp;
use App\Livewire\Shop\Library\MyLibrary;
use App\Livewire\Shop\Profile\MyProfile;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->prefix('/auth')
    ->name('app.auth.')
    ->group(function () {
        Route::get('/login', Login::class)->name('login');
        Route::get('/register', Register::class)->name('register');
        Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
        Route::get('/verify-otp', VerifyOtp::class)->name('password.verify');
        Route::get('/reset-password', ResetPassword::class)->name('password.reset');
    });

Route::middleware('auth')
    ->prefix('/auth')
    ->name('app.auth.')
    ->group(function () {
        Route::get('/logout', [Logout::class, 'logout'])->name('logout');
    });

Route::get('/my-profile', MyProfile::class)
    ->middleware('auth')
    ->name('app.profile.show');

Route::get('/my-library', MyLibrary::class)
    ->middleware('auth')
    ->name('app.library.show');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()
        ->route('app.profile.show', ['section' => 'security'])
        ->with('profile-status', 'Your email address has been verified.');
})->middleware(['auth', 'signed'])->name('verification.verify');
