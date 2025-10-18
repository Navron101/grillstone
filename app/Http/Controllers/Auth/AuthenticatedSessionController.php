<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse; // ✅ the correct one

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $remember = (bool)($credentials['remember'] ?? false);
        $id = $credentials['username'];

        // allow username or email
        $attempted = Auth::attempt(['username' => $id, 'password' => $credentials['password']], $remember)
            || Auth::attempt(['email' => $id, 'password' => $credentials['password']], $remember);

        if ($attempted) {
            $request->session()->regenerate();
            return redirect()->intended(route('pos.index')); // returns Illuminate\Http\RedirectResponse
        }

        return back()->withErrors([
            'username' => __('auth.failed'),
        ])->onlyInput('username');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
