<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Auth\Action;

use App\Livewire\Admin\Auth\Form\LoginForm;
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

        sweetalert()->success('Welcome back, Admin!');

        return redirect()->intended(route('admin.dashboard.index'));
    }

    public function render()
    {
        return view('pages.admin.auth.login')->layout('components.layouts.auth');
    }
}
