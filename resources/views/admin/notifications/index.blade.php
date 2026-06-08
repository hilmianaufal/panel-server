@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')

<div class="mb-8">
    <h2 class="text-3xl font-bold">Notifications</h2>
    <p class="text-slate-400 mt-2">Kelola dan test notifikasi Telegram Panel Server.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center gap-4 mb-6">
            <div class="h-14 w-14 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="send" class="w-7 h-7"></i>
            </div>

            <div>
                <h3 class="text-2xl font-bold">Telegram Notification</h3>
                <p class="text-slate-400 text-sm">Notifikasi dikirim melalui bot Telegram.</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <p class="text-sm text-slate-400 mb-1">Bot Token</p>
                <p class="font-mono text-sm break-all">
                    {{ $botToken ? Str::limit($botToken, 18) . '********' : 'Belum dikonfigurasi' }}
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <p class="text-sm text-slate-400 mb-1">Chat ID</p>
                <p class="font-mono text-sm">
                    {{ $chatId ?: 'Belum dikonfigurasi' }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.notifications.test') }}" class="mt-6">
            @csrf

            <button class="rounded-2xl bg-blue-600 hover:bg-blue-700 px-6 py-3 font-semibold flex items-center gap-2">
                <i data-lucide="send-horizontal" class="w-5 h-5"></i>
                Test Telegram
            </button>
        </form>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <h3 class="text-xl font-bold mb-4">Status</h3>

        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>Bot Token</span>
                <span class="{{ $botToken ? 'text-green-400' : 'text-red-400' }}">
                    {{ $botToken ? 'Ready' : 'Missing' }}
                </span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>Chat ID</span>
                <span class="{{ $chatId ? 'text-green-400' : 'text-red-400' }}">
                    {{ $chatId ? 'Ready' : 'Missing' }}
                </span>
            </div>
        </div>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection