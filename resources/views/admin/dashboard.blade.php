@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between">
            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="cpu" class="w-6 h-6"></i>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-green-500/10 text-green-400">Live</span>
        </div>
        <p class="text-slate-400 mt-5 text-sm">CPU Load</p>
        <h3 class="text-4xl font-bold mt-1">{{ $cpu }}</h3>
        <div class="mt-4 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full w-2/5 bg-blue-500 rounded-full"></div>
        </div>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between">
            <div class="h-12 w-12 rounded-2xl bg-violet-600/20 text-violet-400 flex items-center justify-center">
                <i data-lucide="memory-stick" class="w-6 h-6"></i>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-blue-500/10 text-blue-400">RAM</span>
        </div>
        <p class="text-slate-400 mt-5 text-sm">RAM Usage</p>
        <h3 class="text-4xl font-bold mt-1">{{ $ram }}%</h3>
        <div class="mt-4 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full bg-violet-500 rounded-full" style="width: {{ is_numeric($ram) ? $ram : 0 }}%"></div>
        </div>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between">
            <div class="h-12 w-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center">
                <i data-lucide="hard-drive" class="w-6 h-6"></i>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-amber-500/10 text-amber-400">Disk</span>
        </div>
        <p class="text-slate-400 mt-5 text-sm">Disk Usage</p>
        <h3 class="text-4xl font-bold mt-1">{{ $disk }}%</h3>
        <div class="mt-4 h-2 rounded-full bg-slate-800 overflow-hidden">
            <div class="h-full bg-amber-500 rounded-full" style="width: {{ is_numeric($disk) ? $disk : 0 }}%"></div>
        </div>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between">
            <div class="h-12 w-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="clock-3" class="w-6 h-6"></i>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400">Online</span>
        </div>
        <p class="text-slate-400 mt-5 text-sm">Server Uptime</p>
        <h3 class="text-3xl font-bold mt-1">{{ $uptime }}</h3>
        <div class="mt-4 flex items-center gap-2 text-sm text-emerald-400">
            <i data-lucide="wifi" class="w-4 h-4"></i>
            Server berjalan normal
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-8">

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold">Service Status</h3>
                <p class="text-sm text-slate-400">Pantau service utama server lokal</p>
            </div>
            <i data-lucide="radio-tower" class="w-6 h-6 text-blue-400"></i>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-green-500/20 text-green-400 flex items-center justify-center">
                        <i data-lucide="server-cog" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Web Server</p>
                        <p class="text-sm text-slate-400">Nginx / Apache</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-sm">Belum dicek</span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center">
                        <i data-lucide="database-zap" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Database Server</p>
                        <p class="text-sm text-slate-400">MySQL / MariaDB</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-sm">Belum dicek</span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-violet-500/20 text-violet-400 flex items-center justify-center">
                        <i data-lucide="terminal-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-semibold">PHP Runtime</p>
                        <p class="text-sm text-slate-400">PHP CLI / PHP-FPM</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-sm">Aktif</span>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-gradient-to-br from-blue-600 to-violet-700 p-6 shadow-xl shadow-blue-900/30">
        <div class="h-14 w-14 rounded-2xl bg-white/20 flex items-center justify-center">
            <i data-lucide="zap" class="w-7 h-7"></i>
        </div>

        <h3 class="text-2xl font-bold mt-6">Quick Actions</h3>
        <p class="text-blue-100 text-sm mt-2">Aksi cepat untuk mengelola server.</p>

        <div class="mt-6 space-y-3">
            <button class="w-full flex items-center justify-between px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20">
                <span class="flex items-center gap-2">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Restart Service
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>

            <button class="w-full flex items-center justify-between px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20">
                <span class="flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Add Website
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>

            <button class="w-full flex items-center justify-between px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20">
                <span class="flex items-center gap-2">
                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                    Create Backup
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection