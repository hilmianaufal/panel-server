@extends('layouts.admin')

@section('title', 'Deploy Manager')

@section('content')

<div class="mb-8">
    <h2 class="text-3xl font-bold">Deploy Manager</h2>
    @if (session('error'))
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
        {{ session('error') }}
    </div>
@endif
@if (session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4 whitespace-pre-line">
        {{ session('success') }}
    </div>
@endif
    <p class="text-slate-400 mt-2">Kelola Git deployment dari GitHub ke Ubuntu Server.</p>
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
                <i data-lucide="plus-circle" class="w-6 h-6"></i>
            </div>

            <div>
                <h3 class="text-2xl font-bold">Tambah Project</h3>
                <p class="text-slate-400 text-sm">Daftarkan project GitHub.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.deploy.store') }}" class="space-y-4">
            @csrf

            <input name="name" value="{{ old('name') }}" placeholder="Nama Project"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

            <input name="repository" value="{{ old('repository') }}" placeholder="https://github.com/user/repo.git"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

            <input name="branch" value="{{ old('branch', 'main') }}" placeholder="main"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

            <input name="project_path" value="{{ old('project_path') }}" placeholder="/var/www/project"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="auto_database" value="1">
                    <span>Auto Create Database</span>
                </label>
            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-3 font-semibold flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Tambah Project
            </button>
        </form>
    </div>

    <div class="xl:col-span-2 space-y-5">
        @forelse ($projects as $project)
            <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
                <div class="flex flex-col 2xl:flex-row 2xl:items-center justify-between gap-5">
                    <div class="flex items-start gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-green-600/20 text-green-400 flex items-center justify-center">
                            <i data-lucide="rocket" class="w-7 h-7"></i>
                        </div>

                        <div>
                            <h3 class="font-bold text-xl">{{ $project->name }}</h3>
                            <p class="text-slate-400 text-sm mt-1 break-all">{{ $project->repository }}</p>

                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 text-xs">
                                    Branch: {{ $project->branch }}
                                </span>

                                <span class="px-3 py-1 rounded-full bg-violet-500/10 text-violet-400 text-xs break-all">
                                    {{ $project->project_path }}
                                </span>

                                <span class="px-3 py-1 rounded-full bg-slate-500/10 text-slate-300 text-xs">
                                    Last Deploy:
                                    {{ $project->last_deployed_at ? $project->last_deployed_at->format('Y-m-d H:i') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('admin.deploy.run', $project) }}">
                            @csrf

                            <button onclick="return confirm('Deploy project {{ $project->name }}?')"
                                class="px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 font-semibold flex items-center gap-2">
                                <i data-lucide="rocket" class="w-4 h-4"></i>
                                Deploy
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.deploy.destroy', $project) }}">
                            @csrf
                            @method('DELETE')

                            <button onclick="return confirm('Hapus project deploy ini?')"
                                class="px-5 py-3 rounded-2xl bg-red-600/80 hover:bg-red-600 font-semibold flex items-center gap-2">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white/10 border border-white/10 p-16 text-center text-slate-400">
                <i data-lucide="rocket" class="w-12 h-12 mx-auto mb-4"></i>
                Belum ada project deploy.
            </div>
        @endforelse
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection