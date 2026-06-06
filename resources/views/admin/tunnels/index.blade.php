@extends('layouts.admin')

@section('title', 'Tunnel Manager')

@section('content')

<div class="mb-8">
    <h2 class="text-3xl font-bold">Cloudflare Tunnel</h2>
    <p class="text-slate-400 mt-2">Kelola koneksi Cloudflare Tunnel dan hostname website.</p>
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

@if($errors->any())
    <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                <i data-lucide="cloud" class="w-6 h-6"></i>
            </div>

            <div>
                <h3 class="text-2xl font-bold">Tunnel Config</h3>
                <p class="text-slate-400 text-sm">Simpan kredensial Cloudflare API.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.tunnels.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block mb-2 text-sm text-slate-400">Tunnel Name</label>
                <input name="tunnel_name"
                    value="{{ old('tunnel_name', $setting->tunnel_name ?? '') }}"
                    placeholder="Home Server"
                    class="w-full rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-2 text-sm text-slate-400">Account ID</label>
                <input name="account_id"
                    value="{{ old('account_id', $setting->account_id ?? '') }}"
                    class="w-full rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-2 text-sm text-slate-400">Tunnel ID</label>
                <input name="tunnel_id"
                    value="{{ old('tunnel_id', $setting->tunnel_id ?? '') }}"
                    class="w-full rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-2 text-sm text-slate-400">API Token</label>
                <textarea name="api_token" rows="4"
                    class="w-full rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">{{ old('api_token', $setting->api_token ?? '') }}</textarea>
            </div>

            <button class="w-full rounded-2xl bg-blue-600 hover:bg-blue-700 px-6 py-3 font-semibold flex items-center justify-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan Konfigurasi
            </button>
        </form>

        @if($setting)
            <form method="POST" action="{{ route('admin.tunnels.test') }}" class="mt-3">
                @csrf
                <button class="w-full rounded-2xl bg-green-600 hover:bg-green-700 px-6 py-3 font-semibold flex items-center justify-center gap-2">
                    <i data-lucide="plug-zap" class="w-5 h-5"></i>
                    Test API Connection
                </button>
            </form>
        @endif
    </div>

    <div class="xl:col-span-2 rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <form method="POST" action="{{ route('admin.tunnels.hostnames.store') }}"
      class="mb-6 rounded-3xl bg-slate-950/60 border border-white/10 p-5">
    @csrf

    <h4 class="font-bold text-lg mb-4">Tambah Hostname</h4>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input name="hostname"
               placeholder="toko.domain.com"
               class="rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

        <input name="service"
               value="http://localhost:80"
               class="rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

        <button class="rounded-2xl bg-blue-600 hover:bg-blue-700 px-5 py-3 font-semibold flex items-center justify-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5"></i>
            Add Hostname
        </button>
    </div>
</form>
        <div class="flex items-center justify-between mb-6">
            
            <div>
                <h3 class="text-2xl font-bold">Tunnel Hostnames</h3>
                <p class="text-slate-400 text-sm">Daftar ingress hostname dari Cloudflare Tunnel.</p>
            </div>

            @if($apiStatus === 'connected')
                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-sm">Connected</span>
            @elseif($apiStatus === 'failed')
                <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-sm">Failed</span>
            @else
                <span class="px-3 py-1 rounded-full bg-yellow-500/10 text-yellow-400 text-sm">Not Configured</span>
            @endif
        </div>

        <div class="space-y-4">
            @forelse($ingress as $rule)
                @if(isset($rule['hostname']))
                    <div class="p-5 rounded-3xl bg-slate-950/60 border border-white/10 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                                <i data-lucide="globe-2" class="w-6 h-6"></i>
                            </div>

                            <div>
                                <h4 class="font-bold text-lg">{{ $rule['hostname'] }}</h4>
                                <p class="text-sm text-slate-400">{{ $rule['service'] ?? '-' }}</p>
                            </div>
                        </div>

                        <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">
                            Active
                        </span>
                    </div>
                @endif
            @empty
                <div class="text-center py-16 text-slate-400">
                    <i data-lucide="cloud-off" class="w-12 h-12 mx-auto mb-4"></i>
                    Belum ada hostname atau koneksi API belum aktif.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
    lucide.createIcons();
</script>

@endsection