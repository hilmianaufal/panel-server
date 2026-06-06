@extends('layouts.admin')

@section('title', 'Setup 2FA')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="mb-8">
        <h3 class="text-3xl font-bold">Setup 2FA</h3>
        <p class="text-slate-400 mt-2">Scan QR code dengan Google Authenticator.</p>
    </div>

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl text-center">
        <div class="bg-white rounded-3xl p-5 inline-block">
            {!! $qrCodeUrl !!}
        </div>

        <p class="text-slate-400 text-sm mt-5">Secret Key:</p>
        <p class="font-mono text-green-400 break-all mt-2">{{ $user->google2fa_secret }}</p>

        <form method="POST" action="{{ route('admin.security-login.2fa.enable') }}" class="mt-6 space-y-4">
            @csrf

            <input name="otp" placeholder="Masukkan kode 6 digit"
                class="w-full text-center tracking-[0.4em] rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-3 font-semibold">
                Aktifkan 2FA
            </button>
        </form>
    </div>
</div>

@endsection