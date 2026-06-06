<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups/databases');

        if (! is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $backups = collect(File::files($this->backupPath))
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'path' => $file->getRealPath(),
                'size' => number_format($file->getSize() / 1024, 2) . ' KB',
                'modified' => date('Y-m-d H:i', $file->getMTime()),
            ])
            ->sortByDesc('modified')
            ->values();

        return view('admin.backups.index', compact('backups'));
    }

    public function download(string $filename): BinaryFileResponse
    {
        $path = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);

        abort_unless(file_exists($path), 404);

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $path = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);

        abort_unless(file_exists($path), 404);

        File::delete($path);
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'file' => $filename,
            ])
            ->log('Backup dihapus');
        return back()->with('success', 'Backup berhasil dihapus.');
    }

    public function restore(Request $request, string $filename)
    {
        $validated = $request->validate([
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        if (PHP_OS_FAMILY !== 'Linux') {
            return back()->with('error', 'Restore database hanya bisa dijalankan di Ubuntu/Linux Server.');
        }

        $path = $this->backupPath . DIRECTORY_SEPARATOR . basename($filename);

        abort_unless(file_exists($path), 404);

        $database = $validated['database_name'];

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $passwordPart = $dbPass ? "-p{$dbPass}" : '';

        $command = "mysql -h {$dbHost} -u {$dbUser} {$passwordPart} {$database} < \"{$path}\"";

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', 'Restore gagal: ' . $process->getErrorOutput());
        }
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'database' => $database,
                'file' => $filename,
            ])
            ->log('Database direstore');
        return back()->with('success', "Restore berhasil ke database: {$database}");
    }
}