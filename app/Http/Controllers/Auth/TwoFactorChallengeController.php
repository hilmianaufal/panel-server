<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorChallengeController extends Controller
{
    public function show()
    {
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
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

        session(['two_factor_passed' => true]);

        return redirect()->intended('/admin/dashboard');
    }
}