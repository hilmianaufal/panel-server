<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Process\Process;

class ServiceController extends Controller
{
    private array $allowedServices = [
        'nginx' => ['name' => 'Nginx', 'icon' => 'server-cog'],
        'apache2' => ['name' => 'Apache', 'icon' => 'globe'],
        'mysql' => ['name' => 'MySQL', 'icon' => 'database'],
        'mariadb' => ['name' => 'MariaDB', 'icon' => 'database-zap'],
        'php8.2-fpm' => ['name' => 'PHP-FPM', 'icon' => 'terminal-square'],
        'redis-server' => ['name' => 'Redis', 'icon' => 'memory-stick'],
    ];

    public function index()
    {
        $services = collect($this->allowedServices)->map(function ($data, $service) {
            return [
                'name' => $data['name'],
                'icon' => $data['icon'],
                'service' => $service,
                'status' => $this->checkStatus($service),
            ];
        });

        return view('admin.services.index', compact('services'));
    }

    public function restart(string $service): RedirectResponse
    {
        abort_unless(array_key_exists($service, $this->allowedServices), 403);

        if (PHP_OS_FAMILY !== 'Linux') {
            return back()->with('error', 'Restart service hanya bisa dijalankan di Ubuntu/Linux Server.');
        }

        $process = Process::fromShellCommandline("sudo systemctl restart {$service}");
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', "Gagal restart {$service}: " . $process->getErrorOutput());
        }

        return back()->with('success', "Service {$service} berhasil direstart.");
    }
        private function checkStatus(string $service): string
        {
            if (PHP_OS_FAMILY !== 'Linux') {
                return 'development';
            }

            $process = Process::fromShellCommandline("systemctl is-active {$service}");
            $process->run();
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'service' => $service,
                ])
                ->log('Service direstart');
            return trim($process->getOutput()) === 'active' ? 'active' : 'inactive';
        }
}