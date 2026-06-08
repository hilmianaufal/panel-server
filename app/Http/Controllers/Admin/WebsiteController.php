<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudflareSetting;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class WebsiteController extends Controller
{
    public function index()
    {
        $websites = Website::latest()->get();

        return view('admin.websites.index', compact('websites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:websites,domain'],
            'project_folder' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9-_]+$/'],
            'php_version' => ['nullable', 'string', 'max:50'],
            'web_server' => ['required', 'string', 'in:nginx,apache'],
            'auto_tunnel' => ['nullable', 'boolean'],
        ]);

        $projectBasePath = '/var/www/projects';
        $projectPath = $projectBasePath . '/' . $validated['project_folder'];

        $validated['root_path'] = $projectPath . '/public';
        $validated['auto_tunnel'] = $request->boolean('auto_tunnel');
        $validated['tunnel_status'] = $validated['auto_tunnel'] ? 'pending' : null;
        $validated['status'] = PHP_OS_FAMILY === 'Linux' ? 'installing' : 'draft';

        unset($validated['project_folder']);

        $website = Website::create($validated);

        if (PHP_OS_FAMILY !== 'Linux') {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($website)
                ->withProperties($validated)
                ->log('Website ditambahkan dalam development mode');

            return back()->with(
                'success',
                'Website berhasil ditambahkan. Auto deploy Nginx dan Cloudflare Tunnel hanya berjalan di Ubuntu/Linux Server.'
            );
        }

        if ($website->web_server !== 'nginx') {
            $website->update(['status' => 'failed']);

            return back()->with('error', 'Auto setup saat ini hanya mendukung Nginx.');
        }

        $nginxResult = $this->installNginxWebsite($website);

        if ($nginxResult !== true) {
            $website->update(['status' => 'failed']);

            return back()->with('error', $nginxResult);
        }

        $website->update(['status' => 'active']);

        if ($website->auto_tunnel) {
            $tunnelResult = $this->createCloudflareHostname($website);

            if ($tunnelResult !== true) {
                $website->update([
                    'tunnel_status' => 'failed',
                ]);

                return back()->with(
                    'error',
                    "Website aktif, tapi gagal membuat hostname Cloudflare:\n" . $tunnelResult
                );
            }

            $website->update([
                'tunnel_status' => 'active',
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->withProperties([
                'domain' => $website->domain,
                'root_path' => $website->root_path,
                'auto_tunnel' => $website->auto_tunnel,
                'tunnel_status' => $website->tunnel_status,
            ])
            ->log('Website dibuat, Nginx otomatis dideploy, dan tunnel diproses');

        return back()->with(
            'success',
            'Website berhasil dibuat, Nginx aktif, dan Cloudflare Tunnel berhasil diproses.'
        );
    }

    public function edit(Website $website)
    {
        return view('admin.websites.edit', compact('website'));
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:websites,domain,' . $website->id],
            'root_path' => ['required', 'string', 'max:500'],
            'php_version' => ['nullable', 'string', 'max:50'],
            'web_server' => ['required', 'string', 'in:nginx,apache'],
            'auto_tunnel' => ['nullable', 'boolean'],
        ]);

        $validated['auto_tunnel'] = $request->boolean('auto_tunnel');
        $validated['status'] = 'draft';

        if ($validated['auto_tunnel'] && ! $website->tunnel_status) {
            $validated['tunnel_status'] = 'pending';
        }

        $website->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->withProperties($validated)
            ->log('Website diperbarui');

        return redirect()
            ->route('admin.websites.index')
            ->with('success', 'Website berhasil diperbarui.');
    }

    public function destroy(Website $website): RedirectResponse
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->withProperties([
                'name' => $website->name,
                'domain' => $website->domain,
            ])
            ->log('Website dihapus');

        $website->delete();

        return back()->with('success', 'Website berhasil dihapus.');
    }

    public function generateNginxConfig(Website $website)
    {
        $config = $this->makeNginxConfig($website);

        return view('admin.websites.nginx-config', compact('website', 'config'));
    }

    public function deployNginxConfig(Website $website): RedirectResponse
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return back()->with('error', 'Deploy config hanya bisa dijalankan di Ubuntu/Linux Server.');
        }

        if ($website->web_server !== 'nginx') {
            return back()->with('error', 'Website ini bukan menggunakan Nginx.');
        }

        $result = $this->installNginxWebsite($website);

        if ($result !== true) {
            $website->update(['status' => 'failed']);

            return back()->with('error', $result);
        }

        $website->update([
            'status' => 'active',
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->withProperties([
                'domain' => $website->domain,
                'root_path' => $website->root_path,
            ])
            ->log('Nginx config dideploy manual');

        return back()->with('success', 'Nginx config berhasil dideploy dan Nginx berhasil direload.');
    }

    private function installNginxWebsite(Website $website): true|string
    {
        $config = $this->makeNginxConfig($website);

        $filename = preg_replace('/[^a-zA-Z0-9.-]/', '', $website->domain);

        $availablePath = "/etc/nginx/sites-available/{$filename}";
        $enabledPath = "/etc/nginx/sites-enabled/{$filename}";
        $tempPath = storage_path("app/{$filename}.conf");

        file_put_contents($tempPath, $config);

        $projectDir = dirname($website->root_path);

        $commands = [
            "sudo mkdir -p {$projectDir}/public",
            "sudo chown -R www-data:www-data {$projectDir}",
            "sudo cp {$tempPath} {$availablePath}",
            "sudo ln -sf {$availablePath} {$enabledPath}",
            "sudo nginx -t",
            "sudo systemctl reload nginx",
        ];

        $logs = [];

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout(120);
            $process->run();

            $logs[] = '$ ' . $command;
            $logs[] = $process->getOutput();
            $logs[] = $process->getErrorOutput();

            if (! $process->isSuccessful()) {
                return implode("\n", $logs);
            }
        }

        return true;
    }

    private function makeNginxConfig(Website $website): string
    {
        $phpVersion = $website->php_version ?: 'php8.2-fpm';

        return <<<NGINX
server {
    listen 80;
    server_name {$website->domain};

    root {$website->root_path};
    index index.php index.html index.htm;

    access_log /var/log/nginx/{$website->domain}_access.log;
    error_log /var/log/nginx/{$website->domain}_error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/{$phpVersion}.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX;
    }

    private function createCloudflareHostname(Website $website): true|string
{
    $setting = CloudflareSetting::first();

    if (! $setting) {
        return 'Cloudflare Tunnel belum dikonfigurasi.';
    }

    if (! $setting->zone_id) {
        return 'Zone ID Cloudflare belum diisi.';
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Update Cloudflare Tunnel Ingress
    |--------------------------------------------------------------------------
    */
    $url = "https://api.cloudflare.com/client/v4/accounts/{$setting->account_id}/cfd_tunnel/{$setting->tunnel_id}/configurations";

    $response = Http::withToken($setting->api_token)
        ->acceptJson()
        ->get($url);

    if (! $response->successful()) {
        return 'Gagal mengambil konfigurasi tunnel: ' . $response->body();
    }

    $config = data_get($response->json(), 'result.config', []);
    $ingress = $config['ingress'] ?? [];

    $ingress = collect($ingress)
        ->reject(fn ($rule) => ! isset($rule['hostname']))
        ->reject(fn ($rule) => ($rule['hostname'] ?? null) === $website->domain)
        ->values()
        ->toArray();

    $ingress[] = [
        'hostname' => $website->domain,
        'service' => 'http://localhost:80',
    ];

    $ingress[] = [
        'service' => 'http_status:404',
    ];

    $update = Http::withToken($setting->api_token)
        ->acceptJson()
        ->put($url, [
            'config' => [
                'ingress' => $ingress,
            ],
        ]);

    if (! $update->successful()) {
        return 'Gagal update Cloudflare Tunnel: ' . $update->body();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Create / Update DNS Record Cloudflare
    |--------------------------------------------------------------------------
    */
    $dnsName = $website->domain;
    $dnsTarget = "{$setting->tunnel_id}.cfargotunnel.com";

    $searchDns = Http::withToken($setting->api_token)
        ->acceptJson()
        ->get(
            "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records",
            [
                'type' => 'CNAME',
                'name' => $dnsName,
            ]
        );

    if (! $searchDns->successful()) {
        return 'Gagal cek DNS Record: ' . $searchDns->body();
    }

    $existingDnsId = data_get($searchDns->json(), 'result.0.id');

    $payload = [
        'type' => 'CNAME',
        'name' => $dnsName,
        'content' => $dnsTarget,
        'ttl' => 1,
        'proxied' => true,
    ];

    if ($existingDnsId) {
        $dnsUpdate = Http::withToken($setting->api_token)
            ->acceptJson()
            ->put(
                "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records/{$existingDnsId}",
                $payload
            );

        if (! $dnsUpdate->successful()) {
            return 'Gagal update DNS Record: ' . $dnsUpdate->body();
        }
    } else {
        $dnsCreate = Http::withToken($setting->api_token)
            ->acceptJson()
            ->post(
                "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records",
                $payload
            );

        if (! $dnsCreate->successful()) {
            return 'Gagal membuat DNS Record: ' . $dnsCreate->body();
        }
    }

    return true;
}
    public function runTool(Request $request, Website $website): RedirectResponse
{
    if (PHP_OS_FAMILY !== 'Linux') {
        return back()->with('error', 'Laravel Tools hanya bisa dijalankan di Ubuntu/Linux Server.');
    }

    $validated = $request->validate([
        'tool' => ['required', 'string'],
    ]);

    $projectPath = dirname($website->root_path);

    if (! is_dir($projectPath)) {
        return back()->with('error', 'Project folder tidak ditemukan: ' . $projectPath);
    }

    $commands = [
        'optimize' => 'php artisan optimize',
        'optimize_clear' => 'php artisan optimize:clear',
        'migrate' => 'php artisan migrate --force',
        'storage_link' => 'php artisan storage:link',
        'composer_install' => 'composer install --no-dev --optimize-autoloader',
        'npm_build' => 'npm install && npm run build',
    ];

    if (! array_key_exists($validated['tool'], $commands)) {
        return back()->with('error', 'Tool tidak valid.');
    }

    $command = "cd {$projectPath} && " . $commands[$validated['tool']];

    $process = Process::fromShellCommandline($command);
    $process->setTimeout(600);
    $process->run();

    $output = trim($process->getOutput() . "\n" . $process->getErrorOutput());

    activity()
        ->causedBy(auth()->user())
        ->performedOn($website)
        ->withProperties([
            'tool' => $validated['tool'],
            'command' => $command,
            'output' => $output,
        ])
        ->log('Laravel tool dijalankan');

    if (! $process->isSuccessful()) {
        return back()->with('error', $output ?: 'Command gagal dijalankan.');
    }

    return back()->with('success', $output ?: 'Command berhasil dijalankan.');
}

public function analytics(Website $website)
{
    $projectPath = dirname($website->root_path);

    $data = [
        'project_path' => $projectPath,
        'root_path' => $website->root_path,
        'project_exists' => is_dir($projectPath),
        'public_exists' => is_dir($website->root_path),
        'env_exists' => file_exists($projectPath . '/.env'),
        'artisan_exists' => file_exists($projectPath . '/artisan'),
        'disk_usage' => PHP_OS_FAMILY === 'Linux' && is_dir($projectPath)
            ? trim(shell_exec('du -sh ' . escapeshellarg($projectPath) . ' 2>/dev/null') ?: '-')
            : '-',
        'file_count' => PHP_OS_FAMILY === 'Linux' && is_dir($projectPath)
            ? trim(shell_exec('find ' . escapeshellarg($projectPath) . ' -type f | wc -l') ?: '0')
            : '0',
        'last_modified' => is_dir($projectPath)
            ? date('Y-m-d H:i:s', filemtime($projectPath))
            : '-',
    ];

    return view('admin.websites.analytics', compact('website', 'data'));
}
}