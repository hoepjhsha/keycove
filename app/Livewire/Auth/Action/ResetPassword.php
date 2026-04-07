<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\ResetPasswordForm;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Reset Password')]
class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(): void
    {
        if (! session()->has('reset_password_email') || ! session('otp_verified')) {
            flash()->use('theme.aurora')->error('Invalid password reset request. Please try again.');

            $this->redirect(route('app.auth.login'));
        }
    }

    public function resetPassword(): RedirectResponse|Redirector
    {
        $this->form->updatePassword();

        session()->forget(['reset_password_email', 'otp_verified']);
        flash()->use('theme.aurora')->success('Your password has been reset successfully.');

        return redirect()->route('app.auth.login');
    }

    public function render()
    {
        return view('pages.auth.reset-password')->layout('components.layouts.auth');
    }
}
