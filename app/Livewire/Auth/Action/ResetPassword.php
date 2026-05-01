<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\ResetPasswordForm;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Đặt lại mật khẩu')]
class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(): void
    {
        if (! session()->has('reset_password_email') || ! session('otp_verified')) {
            flash()->use('theme.aurora')->error('Yêu cầu đặt lại mật khẩu không hợp lệ. Vui lòng thử lại.');

            $this->redirect(route('app.auth.login'));
        }
    }

    public function resetPassword(): RedirectResponse|Redirector
    {
        $this->form->updatePassword();

        session()->forget(['reset_password_email', 'otp_verified']);
        flash()->use('theme.aurora')->success('Mật khẩu của bạn đã được đặt lại thành công.');

        return redirect()->route('app.auth.login');
    }

    public function render()
    {
        return view('pages.auth.reset-password')->layout('components.layouts.shop');
    }
}
