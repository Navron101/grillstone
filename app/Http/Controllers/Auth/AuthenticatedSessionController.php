<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    // GET /login
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->intended(route('pos.index'));
        }

        return Inertia::render('auth/Login'); // lowercase 'auth' matches your folder
    }

    // POST /login
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $remember = (bool)($credentials['remember'] ?? false);
        $id = $credentials['username'];

        $attempted = Auth::attempt(['username' => $id, 'password' => $credentials['password']], $remember)
            || Auth::attempt(['email' => $id, 'password' => $credentials['password']], $remember);

        if ($attempted) {
            $request->session()->regenerate();
            return redirect()->intended(route('pos.index'));
        }

        return back()->withErrors(['username' => __('auth.failed')])
                     ->onlyInput('username');
    }

    // POST /logout
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
