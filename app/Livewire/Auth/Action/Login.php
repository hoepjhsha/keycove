<?php

declare(strict_types=1);

namespace App\Livewire\Auth\Action;

use App\Livewire\Auth\Form\LoginForm;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Title('Login')]
class Login extends Component
{
    public LoginForm $form;

    public function login(): RedirectResponse|Redirector
    {
        $this->form->authenticate();

        session()->regenerate();

        sweetalert()->success('Welcome back!');

        return redirect()->intended(route('app.shop.index'));
    }

    public function render()
    {
        return view('pages.auth.login')->layout('components.layouts.shop', [
            'offsetHeader' => false,
        ]);
    }
}
