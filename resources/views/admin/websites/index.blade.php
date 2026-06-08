@extends('layouts.admin')

@section('title', 'Website Manager')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="xl:col-span-1 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <h3 class="text-2xl font-bold">Tambah Website</h3>
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4 whitespace-pre-line">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
                {{ session('error') }}
            </div>
        @endif
        <p class="text-slate-400 text-sm mt-2">Daftarkan website baru ke panel.</p>

        <form method="POST" action="{{ route('admin.websites.store') }}" class="mt-6 space-y-4">
            @csrf

            <input name="name" placeholder="Nama Website"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

            <input name="domain" placeholder="contoh: app.local atau domain.com"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

           <div>
                <label class="block text-sm text-slate-400 mb-2">Project Folder</label>

                <input name="project_folder"
                    value="{{ old('project_folder') }}"
                    placeholder="contoh: desa-app"
                    class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

                <p class="text-xs text-slate-500 mt-2">
                    Otomatis menjadi: /var/www/projects/nama-folder/public
                </p>
            </div>

            <input name="php_version" placeholder="php8.2-fpm"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

            <select name="web_server"
                class="w-full rounded-2xl bg-slate-950/70 border border-white/10 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="nginx">Nginx</option>
                <option value="apache">Apache</option>
            </select>
                <label class="flex items-center gap-3 rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3">
                    <input type="checkbox" name="auto_tunnel" value="1"
                        class="rounded border-white/10 bg-slate-950 text-blue-600 focus:ring-blue-500"
                        checked>

                    <div>
                        <p class="font-semibold">Auto Create Cloudflare Hostname</p>
                        <p class="text-xs text-slate-500">
                            Otomatis tambah domain ke Cloudflare Tunnel.
                        </p>
                    </div>
                </label>
            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-4 py-3 font-semibold flex items-center justify-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Tambah Website
            </button>
        </form>
    </div>

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold">Daftar Website</h3>
                <p class="text-slate-400 text-sm">Website yang terdaftar di panel.</p>
            </div>
            <i data-lucide="globe-2" class="w-7 h-7 text-blue-400"></i>
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

        <div class="space-y-4">
            @forelse ($websites as $website)
                <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                            <i data-lucide="panel-top" class="w-6 h-6"></i>
                        </div>

                        <div>
                            <h4 class="font-bold text-lg">{{ $website->name }}</h4>
                            <p class="text-sm text-slate-400">{{ $website->domain }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $website->root_path }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-sm">
                            {{ ucfirst($website->status) }}
                        </span>
                            @if ($website->auto_tunnel)
                                @if ($website->tunnel_status === 'active')
                                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">
                                        Tunnel Active
                                    </span>
                                @elseif ($website->tunnel_status === 'failed')
                                    <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-xs">
                                        Tunnel Failed
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-xs">
                                        Tunnel Pending
                                    </span>
                                @endif
                            @endif
                                                    <a href="http://{{ $website->domain }}" target="_blank"
                           class="px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/15 text-sm">
                            Open
                        </a>

                        <a href="{{ route('admin.websites.edit', $website) }}"
                            class="px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/15 text-sm flex items-center gap-2">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                Edit
                            </a>

                            <a href="{{ route('admin.websites.nginx-config', $website) }}"
                            class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-sm">
                                Config
                            </a>
                        <form method="POST" action="{{ route('admin.websites.destroy', $website) }}">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus website ini dari panel?')"
                                class="px-4 py-2 rounded-2xl bg-red-600/80 hover:bg-red-600 text-sm">
                                Hapus
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.websites.tools.run', $website) }}">
                            @csrf
                            <input type="hidden" name="tool" value="optimize_clear">
                            <button class="px-4 py-2 rounded-2xl bg-violet-600 hover:bg-violet-700 text-sm">
                                Clear Cache
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.websites.tools.run', $website) }}">
                            @csrf
                            <input type="hidden" name="tool" value="migrate">
                            <button onclick="return confirm('Jalankan migration untuk {{ $website->name }}?')"
                                class="px-4 py-2 rounded-2xl bg-green-600 hover:bg-green-700 text-sm">
                                Migrate
                            </button>
                        </form>
                    </div>
                                <a href="{{ route('admin.websites.analytics', $website) }}"
                                class="px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/15 text-sm">
                                    Analytics
                                </a>
                </div>
            @empty
                <div class="text-center py-16 text-slate-400">
                    <i data-lucide="folder-open" class="w-12 h-12 mx-auto mb-4"></i>
                    Belum ada website.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection