<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->load('role');

            return match($user->role->name) {
                'admin'      => redirect()->route('admin.dashboard'),
                'pustakawan' => redirect()->route('pustakawan.dashboard'),
                default      => redirect()->route('anggota.dashboard'),
            };
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Rate limiting - max 5 percobaan per menit
        $key = 'login.' . Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akun Anda tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        return match($user->role->name) {
            'admin'      => redirect()->route('admin.dashboard'),
            'pustakawan' => redirect()->route('pustakawan.dashboard'),
            default      => redirect()->route('anggota.dashboard'),
        };

        ActivityLog::catat('auth.login', "User {$user->email} login");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}