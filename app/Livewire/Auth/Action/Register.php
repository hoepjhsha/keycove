<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\RegisterForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Đăng ký')]
class Register extends Component
{
    public RegisterForm $form;

    public function register(): RedirectResponse|Redirector
    {
        $user = $this->form->storeUser();

        Auth::login($user);

        sweetalert()->success('Tạo tài khoản thành công. Chào mừng bạn!');

        return redirect()->route('app.shop.index');
    }

    public function render()
    {
        return view('pages.auth.register')->layout('components.layouts.shop', [
            'offsetHeader' => false,
        ]);
    }
}
