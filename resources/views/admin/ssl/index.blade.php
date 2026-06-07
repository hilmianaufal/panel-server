@extends('layouts.admin')

@section('title', 'SSL Manager')

@section('content')

<div class="mb-8">
    <h2 class="text-3xl font-bold">SSL Manager</h2>
    <p class="text-slate-400 mt-2">Kelola SSL certificate untuk website Nginx.</p>
</div>

@if(session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
        {{ session('error') }}
    </div>
@endif

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold">Website SSL</h3>
            <p class="text-slate-400 text-sm">Generate SSL menggunakan Certbot.</p>
        </div>

        <form method="POST" action="{{ route('admin.ssl.renew') }}">
            @csrf
            <button class="px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 font-semibold flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                Renew All
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($websites as $website)
            <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="lock-keyhole" class="w-6 h-6"></i>
                    </div>

                    <div>
                        <h4 class="font-bold text-lg">{{ $website->name }}</h4>
                        <p class="text-sm text-slate-400">{{ $website->domain }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $website->root_path }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.ssl.generate', $website) }}">
                    @csrf

                    <button onclick="return confirm('Generate SSL untuk {{ $website->domain }}?')"
                        class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 font-semibold flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        Generate SSL
                    </button>
                </form>
            </div>
        @empty
            <div class="text-center py-16 text-slate-400">
                <i data-lucide="globe-lock" class="w-12 h-12 mx-auto mb-4"></i>
                Belum ada website.
            </div>
        @endforelse
    </div>
</div>

<script>
    lucide.createIcons();
</script>

@endsection