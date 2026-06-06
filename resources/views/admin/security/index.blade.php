@extends('layouts.admin')

@section('title', 'Security Center')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Security Center</h3>
    <p class="text-slate-400 mt-2">Pantau keamanan dasar server dan konfigurasi penting.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach ($checks as $check)
        <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
            <div class="flex items-start justify-between">
                <div class="h-14 w-14 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="{{ $check['icon'] }}" class="w-7 h-7"></i>
                </div>

                @if ($check['status'] === 'good')
                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-sm">Good</span>
                @elseif ($check['status'] === 'danger')
                    <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-sm">Danger</span>
                @else
                    <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-sm">Warning</span>
                @endif
            </div>

            <h4 class="text-xl font-bold mt-6">{{ $check['title'] }}</h4>

            <pre class="mt-4 whitespace-pre-wrap text-sm text-slate-300 bg-slate-950/70 border border-white/10 rounded-2xl p-4 max-h-44 overflow-auto">{{ $check['value'] }}</pre>
        </div>
    @endforeach
</div>

<script>
    lucide.createIcons();
</script>

@endsection