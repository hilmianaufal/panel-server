@extends('layouts.admin')

@section('title', 'Backup Manager')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Backup Manager</h3>
    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 whitespace-pre-line">
            {{ session('error') }}
        </div>
    @endif
    <p class="text-slate-400 mt-2">Kelola file backup database server.</p>
</div>

@if (session('success'))
    <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
        {{ session('success') }}
    </div>
@endif

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold">Daftar Backup</h3>
            <p class="text-slate-400 text-sm">File tersimpan di storage/app/backups/databases.</p>
        </div>

        <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
            <i data-lucide="archive-restore" class="w-7 h-7"></i>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-white/10">
                    <th class="py-4">Nama File</th>
                    <th class="py-4">Ukuran</th>
                    <th class="py-4">Tanggal</th>
                    <th class="py-4 text-right">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($backups as $backup)
                    <tr class="border-b border-white/5">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <i data-lucide="file-archive" class="w-5 h-5 text-blue-400"></i>
                                <span>{{ $backup['name'] }}</span>
                            </div>
                        </td>

                        <td class="py-4 text-slate-400">{{ $backup['size'] }}</td>
                        <td class="py-4 text-slate-400">{{ $backup['modified'] }}</td>

                        <td class="py-4">
                            <div class="flex items-center justify-end gap-3">
                                <form method="POST" action="{{ route('admin.backups.restore', $backup['name']) }}"
                                    class="flex items-center gap-2">
                                    @csrf

                                    <input name="database_name" placeholder="db_tujuan"
                                        class="w-40 rounded-2xl bg-slate-950 border border-white/10 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">

                                    <button onclick="return confirm('Restore backup ini? Database tujuan bisa tertimpa.')"
                                        class="px-4 py-2 rounded-2xl bg-green-600 hover:bg-green-700 flex items-center gap-2">
                                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        Restore
                                    </button>
                                </form>
                                <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                   class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Download
                                </a>

                                <form method="POST" action="{{ route('admin.backups.destroy', $backup['name']) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button onclick="return confirm('Hapus backup ini?')"
                                        class="px-4 py-2 rounded-2xl bg-red-600/80 hover:bg-red-600 flex items-center gap-2">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center text-slate-400">
                            <i data-lucide="archive-x" class="w-12 h-12 mx-auto mb-4"></i>
                            Belum ada file backup.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    lucide.createIcons();
</script>

@endsection