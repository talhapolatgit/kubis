<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ─── Giriş Sayfası ──────────────────────────────────────────────────────────
    public function loginForm()
    {
        // Zaten giriş yapmışsa ana sayfaya yönlendir
        if (Auth::check()) {
            return redirect()->route('katalog.index');
        }

        return view('auth.login');
    }

    // ─── Giriş İşlemi ───────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'E-posta adresi zorunludur.',
            'email.email'       => 'Geçerli bir e-posta adresi girin.',
            'password.required' => 'Şifre zorunludur.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('katalog.index'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'E-posta adresi veya şifre hatalı.',
            ]);
    }

    // ─── Çıkış İşlemi ───────────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('logout', true);
    }
}
