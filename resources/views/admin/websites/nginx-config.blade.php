@extends('layouts.admin')

@section('title', 'Nginx Config')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h3 class="text-3xl font-bold">Nginx Config</h3>
            <p class="text-slate-400 mt-2">{{ $website->domain }}</p>
        </div>

        <a href="{{ route('admin.websites.index') }}"
           class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    <div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="text-xl font-bold">Preview konfigurasi</h4>
                <p class="text-sm text-slate-400">
                    File ini nanti dipasang ke /etc/nginx/sites-available/
                </p>
            </div>

            <button onclick="copyConfig()"
                class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-sm flex items-center gap-2">
                <i data-lucide="copy" class="w-4 h-4"></i>
                Copy
            </button>
        </div>

        <pre id="configBox" class="overflow-x-auto rounded-2xl bg-slate-950 border border-white/10 p-5 text-sm text-green-300"><code>{{ $config }}</code></pre>
    </div>

</div>

<script>
    function copyConfig() {
        const text = document.getElementById('configBox').innerText;
        navigator.clipboard.writeText(text);
        alert('Config berhasil disalin.');
    }

    lucide.createIcons();
</script>

@endsection