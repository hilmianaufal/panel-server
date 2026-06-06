@extends('layouts.admin')

@section('title', 'Database Manager')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Database Manager</h3>
    @if (session('error'))
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
        {{ session('error') }}
    </div>
@endif
    <p class="text-slate-400 mt-2">Kelola database MySQL/MariaDB dari panel.</p>
</div>

@if (session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="database" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold">Buat Database</h3>
                <p class="text-slate-400 text-sm">Database baru MySQL.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.databases.store') }}" class="space-y-4">
            @csrf

            <input name="database_name" placeholder="contoh: app_production"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-3 font-semibold flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Buat Database
            </button>
        </form>
    </div>

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold">Daftar Database</h3>
                <p class="text-slate-400 text-sm">Database yang tersedia.</p>
            </div>

            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="server" class="w-7 h-7"></i>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($databases as $database)
                <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-violet-600/20 text-violet-400 flex items-center justify-center">
                            <i data-lucide="database-zap" class="w-6 h-6"></i>
                        </div>

                        <div>
                            <h4 class="font-bold text-lg">{{ $database }}</h4>
                            <p class="text-sm text-slate-400">MySQL/MariaDB Database</p>
                        </div>
                    </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('admin.databases.backup', $database) }}">
                                @csrf

                                <button onclick="return confirm('Backup database {{ $database }}?')"
                                    class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-sm flex items-center gap-2">
                                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                    Backup
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.databases.destroy', $database) }}">
                                @csrf
                                @method('DELETE')

                                <button onclick="return confirm('Hapus database {{ $database }}? Semua data akan hilang.')"
                                    class="px-4 py-2 rounded-2xl bg-red-600/80 hover:bg-red-600 text-sm flex items-center gap-2">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    Hapus
                                </button>
                            </form>
                        </div>
                </div>
            @empty
                <div class="text-center py-16 text-slate-400">
                    <i data-lucide="database" class="w-12 h-12 mx-auto mb-4"></i>
                    Belum ada database.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection