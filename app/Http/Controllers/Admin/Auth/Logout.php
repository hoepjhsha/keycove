<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Abstracts\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout extends Controller
{
    public function logout(): RedirectResponse
    {
        Auth::guard('admin')->logout();

        Session::invalidate();
        Session::regenerateToken();

        flash()->use('theme.aurora')->success('You have been logged out.');

        return redirect()->route('admin.auth.login');
    }
}
