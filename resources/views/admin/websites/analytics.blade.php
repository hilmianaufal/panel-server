@extends('layouts.admin')

@section('title', 'Website Analytics')

@section('content')

<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-3xl font-bold">Website Analytics</h2>
        <p class="text-slate-400 mt-2">{{ $website->domain }}</p>
    </div>

    <a href="{{ route('admin.websites.index') }}"
       class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15">
        Kembali
    </a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4 whitespace-pre-line">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
        <p class="text-slate-400 text-sm">Disk Usage</p>
        <h3 class="text-2xl font-bold mt-2">{{ $data['disk_usage'] }}</h3>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
        <p class="text-slate-400 text-sm">Files</p>
        <h3 class="text-2xl font-bold mt-2">{{ $data['file_count'] }}</h3>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
        <p class="text-slate-400 text-sm">Project</p>
        <h3 class="text-2xl font-bold mt-2">
            {{ $data['project_exists'] ? 'Exists' : 'Missing' }}
        </h3>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
        <p class="text-slate-400 text-sm">Last Modified</p>
        <h3 class="text-lg font-bold mt-2">{{ $data['last_modified'] }}</h3>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6">
        <h3 class="text-2xl font-bold mb-5">Project Health</h3>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>Project Path</span>
                <span class="text-slate-400 break-all">{{ $data['project_path'] }}</span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>Public Path</span>
                <span class="text-slate-400 break-all">{{ $data['root_path'] }}</span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>.env File</span>
                <span class="{{ $data['env_exists'] ? 'text-green-400' : 'text-red-400' }}">
                    {{ $data['env_exists'] ? 'Exists' : 'Missing' }}
                </span>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-white/10">
                <span>Artisan</span>
                <span class="{{ $data['artisan_exists'] ? 'text-green-400' : 'text-red-400' }}">
                    {{ $data['artisan_exists'] ? 'Laravel Project' : 'Not Detected' }}
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
        <h3 class="text-2xl font-bold mb-5">Laravel Tools</h3>

        <div class="space-y-3">
            @foreach([
                'optimize' => 'Optimize',
                'optimize_clear' => 'Clear Cache',
                'migrate' => 'Migrate',
                'storage_link' => 'Storage Link',
                'composer_install' => 'Composer Install',
                'npm_build' => 'NPM Build',
            ] as $key => $label)
                <form method="POST" action="{{ route('admin.websites.tools.run', $website) }}">
                    @csrf
                    <input type="hidden" name="tool" value="{{ $key }}">

                    <button onclick="return confirm('Jalankan {{ $label }}?')"
                        class="w-full px-4 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 font-semibold">
                        {{ $label }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>

</div>

@endsection