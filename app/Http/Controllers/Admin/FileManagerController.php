<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileManagerController extends Controller
{
    private string $basePath = '';

    public function __construct()
    {
        $this->basePath = base_path();
    }

    public function index(Request $request)
    {
        $path = $request->query('path', $this->basePath);
        $realPath = realpath($path);

        if (! $realPath || ! str_starts_with($realPath, $this->basePath)) {
            abort(403, 'Akses folder tidak diizinkan.');
        }

        $items = collect(File::directories($realPath))
            ->map(fn ($item) => [
                'name' => basename($item),
                'path' => $item,
                'type' => 'folder',
                'size' => '-',
                'modified' => date('Y-m-d H:i', filemtime($item)),
            ])
            ->merge(
                collect(File::files($realPath))->map(fn ($item) => [
                    'name' => $item->getFilename(),
                    'path' => $item->getRealPath(),
                    'type' => 'file',
                    'size' => number_format($item->getSize() / 1024, 2) . ' KB',
                    'modified' => date('Y-m-d H:i', $item->getMTime()),
                ])
            );

        return view('admin.files.index', [
            'items' => $items,
            'currentPath' => $realPath,
            'parentPath' => dirname($realPath),
            'basePath' => $this->basePath,
        ]);
    }

    public function show(Request $request)
    {
        $realPath = realpath($request->query('path'));

        if (! $realPath || ! str_starts_with($realPath, $this->basePath) || ! is_file($realPath)) {
            abort(403, 'File tidak diizinkan.');
        }

        return view('admin.files.show', [
            'path' => $realPath,
            'content' => File::get($realPath),
        ]);
    }

    public function edit(Request $request)
    {
        $realPath = realpath($request->query('path'));

        if (! $realPath || ! str_starts_with($realPath, $this->basePath) || ! is_file($realPath)) {
            abort(403, 'File tidak diizinkan.');
        }

        return view('admin.files.edit', [
            'path' => $realPath,
            'content' => File::get($realPath),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        $realPath = realpath($validated['path']);

        if (! $realPath || ! str_starts_with($realPath, $this->basePath) || ! is_file($realPath)) {
            abort(403, 'File tidak diizinkan.');
        }

        File::put($realPath, $validated['content'] ?? '');

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['path' => $realPath])
            ->log('File diedit');

        return redirect()
            ->route('admin.files.show', ['path' => $realPath])
            ->with('success', 'File berhasil disimpan.');
    }

    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'current_path' => ['required', 'string'],
            'folder_name' => ['required', 'string', 'max:100'],
        ]);

        $currentPath = realpath($validated['current_path']);

        if (! $currentPath || ! str_starts_with($currentPath, $this->basePath)) {
            abort(403, 'Path tidak diizinkan.');
        }

        $folderName = preg_replace('/[^a-zA-Z0-9-_ ]/', '', $validated['folder_name']);
        $newFolder = $currentPath . DIRECTORY_SEPARATOR . $folderName;

        if (File::exists($newFolder)) {
            return back()->with('error', 'Folder sudah ada.');
        }

        File::makeDirectory($newFolder);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['path' => $newFolder])
            ->log('Folder dibuat');

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'current_path' => ['required', 'string'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $currentPath = realpath($validated['current_path']);

        if (! $currentPath || ! str_starts_with($currentPath, $this->basePath)) {
            abort(403, 'Path tidak diizinkan.');
        }

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        $file->move($currentPath, $filename);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'path' => $currentPath,
                'file' => $filename,
            ])
            ->log('File diupload');

        return back()->with('success', 'File berhasil diupload.');
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $realPath = realpath($validated['path']);

        if (! $realPath || ! str_starts_with($realPath, $this->basePath)) {
            abort(403, 'Path tidak diizinkan.');
        }

        if ($realPath === $this->basePath) {
            return back()->with('error', 'Folder utama tidak boleh dihapus.');
        }

        if (is_dir($realPath)) {
            File::deleteDirectory($realPath);
        } else {
            File::delete($realPath);
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['path' => $realPath])
            ->log('File atau folder dihapus');

        return back()->with('success', 'Item berhasil dihapus.');
    }
}