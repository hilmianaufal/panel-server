@extends('layouts.admin')

@section('title', 'Service Manager')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Service Manager</h3>
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
            {{ session('error') }}
        </div>
    @endif
    <p class="text-slate-400 mt-2">Pantau status service utama server kamu secara langsung.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach ($services as $service)
        <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
            <div class="flex items-start justify-between">
                <div class="h-14 w-14 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="{{ $service['icon'] }}" class="w-7 h-7"></i>
                </div>

                @if ($service['status'] === 'active')
                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-sm">
                        Active
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-sm">
                        Inactive
                    </span>
                @endif
            </div>

            <div class="mt-6">
                <h4 class="text-2xl font-bold">{{ $service['name'] }}</h4>
                <p class="text-sm text-slate-400 mt-1">
                    systemctl service: {{ $service['service'] }}
                </p>
            </div>

            <div class="mt-6 flex gap-3">
                <form method="POST" action="{{ route('admin.services.restart', $service['service']) }}" class="flex-1">
                    @csrf
                    <button
                        onclick="return confirm('Restart service {{ $service['name'] }}?')"
                        class="w-full px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-sm flex items-center justify-center gap-2"
                    >
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                        Restart
                    </button>
                </form>

                <button class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-sm">
                    <i data-lucide="terminal" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    @endforeach
</div>

<script>
    lucide.createIcons();
</script>

@endsection 