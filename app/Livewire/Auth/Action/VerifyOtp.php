<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\VerifyOtpForm;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Xác minh OTP')]
class VerifyOtp extends Component
{
    public VerifyOtpForm $form;

    public int $remainingTime = 180;

    public bool $timerExpired = false;

    public function mount()
    {
        if (! session()->has('reset_password_email')) {
            return $this->redirect(route('app.auth.password.request'));
        }

        $this->remainingTime = $this->form->getRemainingTime();
        $this->timerExpired = $this->remainingTime <= 0;
    }

    public function verify(): RedirectResponse|Redirector
    {
        $this->form->verifyProcess();

        flash()->use('theme.aurora')->success('Xác minh OTP thành công.');

        return redirect()->route('app.auth.password.reset');
    }

    public function resend(): void
    {
        $this->form->resendOtp();
        $this->remainingTime = 180;
        $this->timerExpired = false;

        flash()->use('theme.aurora')->success('Mã OTP đã được gửi lại.');
    }

    public function render()
    {
        return view('pages.auth.verify-otp')->layout('components.layouts.shop');
    }
}
