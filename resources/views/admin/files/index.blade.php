@extends('layouts.admin')

@section('title', 'File Manager')

@section('content')

<div class="mb-8 flex items-center justify-between">
    <div>
        <h3 class="text-3xl font-bold">File Manager</h3>
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4">
                {{ $errors->first() }}
            </div>
        @endif
        <p class="text-slate-400 mt-2 break-all">{{ $currentPath }}</p>
    </div>

    <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
        <i data-lucide="folder-code" class="w-7 h-7"></i>
    </div>
</div>

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">

    @if ($currentPath !== $basePath)
        <a href="{{ route('admin.files.index', ['path' => $parentPath]) }}"
           class="mb-4 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/15">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Folder atas
        </a>
    @endif

    <div class="overflow-x-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <form method="POST" action="{{ route('admin.files.create-folder') }}"
                class="rounded-2xl bg-slate-950/60 border border-white/10 p-4">
                @csrf

                <input type="hidden" name="current_path" value="{{ $currentPath }}">

                <label class="block text-sm text-slate-400 mb-2">Buat Folder Baru</label>

                <div class="flex gap-3">
                    <input name="folder_name" placeholder="nama-folder"
                        class="flex-1 rounded-2xl bg-slate-950 border border-white/10 px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">

                    <button class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        Buat
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.files.upload') }}" enctype="multipart/form-data"
                class="rounded-2xl bg-slate-950/60 border border-white/10 p-4">
                @csrf

                <input type="hidden" name="current_path" value="{{ $currentPath }}">

                <label class="block text-sm text-slate-400 mb-2">Upload File</label>

                <div class="flex gap-3">
                    <input type="file" name="file"
                        class="flex-1 rounded-2xl bg-slate-950 border border-white/10 px-4 py-2 text-sm">

                    <button class="px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                        Upload
                    </button>
                </div>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-white/10">
                    <th class="py-4">Nama</th>
                    <th class="py-4">Tipe</th>
                    <th class="py-4">Ukuran</th>
                    <th class="py-4">Diubah</th>
                    <th class="py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-b border-white/5">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                @if ($item['type'] === 'folder')
                                    <i data-lucide="folder" class="w-5 h-5 text-yellow-400"></i>
                                @else
                                    <i data-lucide="file-code-2" class="w-5 h-5 text-blue-400"></i>
                                @endif

                                <span>{{ $item['name'] }}</span>
                            </div>
                        </td>

                        <td class="py-4 text-slate-400">{{ $item['type'] }}</td>
                        <td class="py-4 text-slate-400">{{ $item['size'] }}</td>
                        <td class="py-4 text-slate-400">{{ $item['modified'] }}</td>

                        <td class="py-4 text-right">
                           <div class="flex items-center justify-end gap-2">
                                @if ($item['type'] === 'folder')
                                    <a href="{{ route('admin.files.index', ['path' => $item['path']]) }}"
                                    class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700">
                                        Open
                                    </a>
                                @else
                                    <a href="{{ route('admin.files.show', ['path' => $item['path']]) }}"
                                    class="px-4 py-2 rounded-2xl bg-white/10 hover:bg-white/15">
                                        View
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('admin.files.destroy') }}">
                                    @csrf
                                    @method('DELETE')

                                    <input type="hidden" name="path" value="{{ $item['path'] }}">

                                    <button onclick="return confirm('Hapus item ini?')"
                                        class="px-4 py-2 rounded-2xl bg-red-600/80 hover:bg-red-600">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            Folder kosong.
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