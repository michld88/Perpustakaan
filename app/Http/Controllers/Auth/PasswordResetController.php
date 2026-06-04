<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    // Halaman form input email
    public function requestForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    // Kirim link reset
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
        }

        return back()->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
    }

    // Halaman form reset password baru
    public function resetForm(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // Proses reset password
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
            ],
            'password_confirmation' => 'required',
        ], [
            'password.min'       => 'Password minimal 8 karakter.',
            'password.regex'     => 'Password harus mengandung huruf dan angka.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password berhasil direset. Silakan login.');
        }

        return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kadaluarsa.']);
    }
}