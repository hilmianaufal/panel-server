<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $user = auth()->user();

        if (! $user->google2fa_secret) {
            $google2fa = app('pragmarx.google2fa');

            $user->update([
                'google2fa_secret' => $google2fa->generateSecretKey(),
            ]);
        }

        $google2fa = app('pragmarx.google2fa');

        $qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        return view('admin.security-login.2fa-setup', compact('user', 'qrCodeUrl'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey(
            auth()->user()->google2fa_secret,
            $request->otp
        );

        if (! $valid) {
            return back()->with('error', 'Kode OTP tidak valid.');
        }

        auth()->user()->update([
            'google2fa_enabled' => true,
        ]);

        return redirect()
            ->route('admin.security-login.index')
            ->with('success', '2FA berhasil diaktifkan.');
    }

    public function disable()
    {
        auth()->user()->update([
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
        ]);

        return back()->with('success', '2FA berhasil dimatikan.');
    }
}