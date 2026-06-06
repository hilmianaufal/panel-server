@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Activity Log</h3>
    <p class="text-slate-400 mt-2">Riwayat aktivitas penting di panel.</p>
</div>

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
    <div class="space-y-4">
        @forelse ($activities as $activity)
            <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 flex items-start gap-4">
                <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h4 class="font-bold">{{ $activity->description }}</h4>
                        <span class="text-xs text-slate-500">
                            {{ $activity->created_at->format('Y-m-d H:i:s') }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-400 mt-1">
                        User: {{ optional($activity->causer)->name ?? 'System' }}
                    </p>

                    @if ($activity->properties && $activity->properties->count())
                        <pre class="mt-3 whitespace-pre-wrap text-xs text-green-300 bg-slate-950 border border-white/10 rounded-2xl p-4 overflow-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-slate-400">
                <i data-lucide="history" class="w-12 h-12 mx-auto mb-4"></i>
                Belum ada activity log.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $activities->links() }}
    </div>
</div>

<script>
    lucide.createIcons();
</script>

@endsection