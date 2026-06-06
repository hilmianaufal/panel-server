<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;


class WebsiteController extends Controller
{
    public function index()
    {
        $websites = Website::latest()->get();

        return view('admin.websites.index', compact('websites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:websites,domain'],
            'root_path' => ['required', 'string', 'max:500'],
            'php_version' => ['nullable', 'string', 'max:50'],
            'web_server' => ['required', 'string', 'in:nginx,apache'],
        ]);

        $validated['status'] = 'draft';

        $website = Website::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($website)
            ->withProperties($validated)
            ->log('Website ditambahkan');
        return back()->with('success', 'Website berhasil ditambahkan.');
    }

    public function destroy(Website $website)
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

    $config = $this->makeNginxConfig($website);

    $filename = preg_replace('/[^a-zA-Z0-9.-]/', '', $website->domain);
    $availablePath = "/etc/nginx/sites-available/{$filename}";
    $enabledPath = "/etc/nginx/sites-enabled/{$filename}";

    $tempPath = storage_path("app/{$filename}.conf");
    file_put_contents($tempPath, $config);

    $commands = [
        "sudo cp {$tempPath} {$availablePath}",
        "sudo ln -sf {$availablePath} {$enabledPath}",
        "sudo nginx -t",
        "sudo systemctl reload nginx",
    ];

    foreach ($commands as $command) {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (! $process->isSuccessful()) {
            return back()->with('error', "Command gagal: {$command}\n" . $process->getErrorOutput());
        }
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
            ->log('Nginx config dideploy');
    return back()->with('success', 'Nginx config berhasil dideploy dan Nginx berhasil direload.');
    }

    private function makeNginxConfig(Website $website): string
    {
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
            fastcgi_pass unix:/var/run/php/{$website->php_version}.sock;
        }

        location ~ /\.ht {
            deny all;
        }
    }
    NGINX;
    }

    public function edit(Website $website)
    {
        return view('admin.websites.edit', compact('website'));
    }

    public function update(Request $request, Website $website)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'unique:websites,domain,' . $website->id],
            'root_path' => ['required', 'string', 'max:500'],
            'php_version' => ['nullable', 'string', 'max:50'],
            'web_server' => ['required', 'string', 'in:nginx,apache'],
        ]);

        $validated['status'] = 'draft';

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

        return back()->with('success', 'File berhasil diupload.');
    }

    
}