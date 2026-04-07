<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\ForgotPasswordForm;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Forgot Password')]
class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public function sendOtp(): RedirectResponse|Redirector
    {
        $this->form->sendOtpProcess();

        flash()->use('theme.aurora')->success('OTP has been sent to your email.');

        return redirect()->route('app.auth.password.verify');
    }

    public function render()
    {
        return view('pages.auth.forgot-password')->layout('components.layouts.auth');
    }
}
