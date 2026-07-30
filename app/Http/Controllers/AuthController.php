<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $login, 'password' => $request->input('password')], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Invalid credentials.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        if (! Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors(['login' => 'Your account is inactive.']);
        }

        return redirect()->route('company.select');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
