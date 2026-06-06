<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;

class SecurityController extends Controller
{
    public function index()
    {
        $checks = [
            [
                'title' => 'Operating System',
                'value' => PHP_OS_FAMILY,
                'status' => PHP_OS_FAMILY === 'Linux' ? 'good' : 'warning',
                'icon' => 'monitor-cog',
            ],
            [
                'title' => 'Firewall UFW',
                'value' => $this->runCommand('sudo ufw status'),
                'status' => PHP_OS_FAMILY === 'Linux' ? 'good' : 'warning',
                'icon' => 'shield',
            ],
            [
                'title' => 'SSH Service',
                'value' => $this->runCommand('systemctl is-active ssh'),
                'status' => trim($this->runCommand('systemctl is-active ssh')) === 'active' ? 'good' : 'warning',
                'icon' => 'terminal',
            ],
            [
                'title' => 'Nginx Config Test',
                'value' => $this->runCommand('sudo nginx -t'),
                'status' => str_contains($this->runCommand('sudo nginx -t'), 'successful') ? 'good' : 'warning',
                'icon' => 'server-cog',
            ],
            [
                'title' => 'Storage Writable',
                'value' => is_writable(storage_path()) ? 'Writable' : 'Not Writable',
                'status' => is_writable(storage_path()) ? 'good' : 'danger',
                'icon' => 'folder-check',
            ],
            [
                'title' => '.env File',
                'value' => file_exists(base_path('.env')) ? 'Exists' : 'Missing',
                'status' => file_exists(base_path('.env')) ? 'warning' : 'good',
                'icon' => 'file-key',
            ],
        ];

        return view('admin.security.index', compact('checks'));
    }

    private function runCommand(string $command): string
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return 'Development mode - only available on Ubuntu/Linux server.';
        }

        $process = Process::fromShellCommandline($command);
        $process->run();

        return trim($process->getOutput() ?: $process->getErrorOutput()) ?: 'No output';
    }
}