<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function index()
    {
        $databases = collect(DB::select('SHOW DATABASES'))
            ->map(function ($item) {
                $array = (array) $item;
                return array_values($array)[0];
            })
            ->reject(fn ($name) => in_array($name, [
                'information_schema',
                'performance_schema',
                'mysql',
                'sys',
            ]))
            ->values();

        return view('admin.databases.index', compact('databases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'database_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        DB::statement('CREATE DATABASE `' . $validated['database_name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'database' => $validated['database_name'],
            ])
            ->log('Database dibuat');

        return back()->with('success', 'Database berhasil dibuat.');
    }

    public function destroy(string $database)
    {
        abort_if(in_array($database, [
            'information_schema',
            'performance_schema',
            'mysql',
            'sys',
        ]), 403);

        DB::statement('DROP DATABASE `' . str_replace('`', '', $database) . '`');
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'database' => $database,
            ])
            ->log('Database dihapus');
        return back()->with('success', 'Database berhasil dihapus.');
    }

    public function backup(string $database)
    {
        $safeDatabase = preg_replace('/[^a-zA-Z0-9_]/', '', $database);

        if ($safeDatabase !== $database) {
            abort(403, 'Nama database tidak valid.');
        }

        $backupDir = storage_path('app/backups/databases');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = $database . '_' . now()->format('Ymd_His') . '.sql';
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $passwordPart = $dbPass ? "-p{$dbPass}" : '';

        $command = "mysqldump -h {$dbHost} -u {$dbUser} {$passwordPart} {$database} > \"{$backupPath}\"";

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', 'Backup gagal: ' . $process->getErrorOutput());
        }
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'database' => $database,
                'file' => $filename,
            ])
            ->log('Database dibackup');
        return back()->with('success', "Backup berhasil dibuat: {$filename}");
    }
}