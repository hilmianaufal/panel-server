<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Process\Process;

class SslController extends Controller
{
    public function index()
    {
        $websites = Website::latest()->get();

        return view('admin.ssl.index', compact('websites'));
    }

    public function generate(Website $website): RedirectResponse
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return back()->with('error', 'SSL hanya bisa dijalankan di Ubuntu/Linux Server.');
        }

        $command = "sudo certbot --nginx -d {$website->domain} --non-interactive --agree-tos -m admin@{$website->domain}";

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', $process->getErrorOutput() ?: $process->getOutput());
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->log('SSL certificate dibuat');

        return back()->with('success', 'SSL berhasil dibuat untuk ' . $website->domain);
    }

    public function renew(): RedirectResponse
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return back()->with('error', 'Renew SSL hanya bisa dijalankan di Ubuntu/Linux Server.');
        }

        $process = Process::fromShellCommandline('sudo certbot renew');
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', $process->getErrorOutput() ?: $process->getOutput());
        }

        return back()->with('success', 'Renew SSL berhasil dijalankan.');
    }
}