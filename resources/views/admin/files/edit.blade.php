@extends('layouts.admin')

@section('title', 'Edit File')

@section('content')

<div class="mb-8 flex items-center justify-between">
    <div>
        <h3 class="text-3xl font-bold">Edit File</h3>
        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4">
                {{ session('success') }}
            </div>
        @endif
        <p class="text-slate-400 mt-2 break-all">{{ $path }}</p>
    </div>

    <a href="{{ route('admin.files.show', ['path' => $path]) }}"
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
    <form method="POST" action="{{ route('admin.files.update') }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="path" value="{{ $path }}">

        <textarea name="content"
            class="w-full min-h-[600px] rounded-2xl bg-slate-950 border border-white/10 p-5 font-mono text-sm text-green-300 focus:ring-2 focus:ring-blue-500 outline-none">{{ $content }}</textarea>

        <div class="mt-5 flex justify-end">
            <button class="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 font-semibold flex items-center gap-2">
                <i data-lucide="save" class="w-5 h-5"></i>
                Simpan File
            </button>
        </div>
    </form>
</div>

<script>
    lucide.createIcons();
</script>

@endsection