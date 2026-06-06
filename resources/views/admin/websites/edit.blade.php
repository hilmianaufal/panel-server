@extends('layouts.admin')

@section('title', 'Edit Website')

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h3 class="text-3xl font-bold">Edit Website</h3>
            <p class="text-slate-400 mt-2">{{ $website->domain }}</p>
        </div>

        <a href="{{ route('admin.websites.index') }}"
           class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <form method="POST" action="{{ route('admin.websites.update', $website) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm text-slate-400 mb-2">Nama Website</label>
                <input name="name" value="{{ old('name', $website->name) }}"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">Domain</label>
                <input name="domain" value="{{ old('domain', $website->domain) }}"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">Root Path</label>
                <input name="root_path" value="{{ old('root_path', $website->root_path) }}"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">PHP Version</label>
                <input name="php_version" value="{{ old('php_version', $website->php_version) }}"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-2">Web Server</label>
                <select name="web_server"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="nginx" @selected(old('web_server', $website->web_server) === 'nginx')>Nginx</option>
                    <option value="apache" @selected(old('web_server', $website->web_server) === 'apache')>Apache</option>
                </select>
            </div>

            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-3 font-semibold flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Perubahan
            </button>
        </form>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection