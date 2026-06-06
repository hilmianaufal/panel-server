@extends('layouts.admin')

@section('title', 'View File')

@section('content')

<div class="mb-8 flex items-center justify-between">
    <div>
        <h3 class="text-3xl font-bold">View File</h3>
        <p class="text-slate-400 mt-2 break-all">{{ $path }}</p>
    </div>
    <a href="{{ route('admin.files.edit', ['path' => $path]) }}"
    class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
        <i data-lucide="pencil" class="w-4 h-4"></i>
        Edit
    </a>
    <a href="{{ route('admin.files.index', ['path' => dirname($path)]) }}"
       class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/15 flex items-center gap-2">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali
    </a>
</div>

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
    <pre class="overflow-x-auto rounded-2xl bg-slate-950 border border-white/10 p-5 text-sm text-green-300"><code>{{ $content }}</code></pre>
</div>

<script>
    lucide.createIcons();
</script>

@endsection