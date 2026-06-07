<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudflareSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class TunnelManagerController extends Controller
{
    public function index()
    {
        $setting = CloudflareSetting::first();
        $ingress = [];
        $apiStatus = null;

        if ($setting) {
            $response = $this->cloudflare($setting)
                ->get("https://api.cloudflare.com/client/v4/accounts/{$setting->account_id}/cfd_tunnel/{$setting->tunnel_id}/configurations");

            if ($response->successful()) {
                $apiStatus = 'connected';
                $ingress = data_get($response->json(), 'result.config.ingress', []);
            } else {
                $apiStatus = 'failed';
            }
        }

        return view('admin.tunnels.index', compact('setting', 'ingress', 'apiStatus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tunnel_name' => ['nullable', 'string', 'max:255'],
            'account_id' => ['required', 'string', 'max:255'],
            'zone_id' => ['required', 'string', 'max:255'],
            'tunnel_id' => ['required', 'string', 'max:255'],
            'api_token' => ['required', 'string'],
        ]);

        CloudflareSetting::updateOrCreate(
            ['id' => 1],
            $validated
        );

        return back()->with('success', 'Konfigurasi Cloudflare berhasil disimpan.');
    }

    public function test()
    {
        $setting = CloudflareSetting::firstOrFail();

        $response = $this->cloudflare($setting)
            ->get("https://api.cloudflare.com/client/v4/accounts/{$setting->account_id}/cfd_tunnel/{$setting->tunnel_id}");

        if (! $response->successful()) {
            return back()->with('error', 'Koneksi Cloudflare gagal: ' . $response->body());
        }

        return back()->with('success', 'Koneksi Cloudflare berhasil.');
    }

    public function addHostname(Request $request)
    {
        $validated = $request->validate([
            'hostname' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
        ]);

        $setting = CloudflareSetting::firstOrFail();

        $tunnelResult = $this->ensureTunnelIngress(
            $setting,
            $validated['hostname'],
            $validated['service']
        );

        if ($tunnelResult !== true) {
            return back()->with('error', $tunnelResult);
        }

        $dnsResult = $this->ensureDnsRecord(
            $setting,
            $validated['hostname']
        );

        if ($dnsResult !== true) {
            return back()->with('error', $dnsResult);
        }

        $this->restartCloudflared();

        activity()
            ->causedBy(auth()->user())
            ->withProperties($validated)
            ->log('Cloudflare Tunnel hostname dan DNS record ditambahkan');

        return back()->with('success', 'Hostname berhasil ditambahkan, DNS record dibuat, dan Cloudflared direstart.');
    }

    private function ensureTunnelIngress(CloudflareSetting $setting, string $hostname, string $service): true|string
    {
        $url = "https://api.cloudflare.com/client/v4/accounts/{$setting->account_id}/cfd_tunnel/{$setting->tunnel_id}/configurations";

        $response = $this->cloudflare($setting)->get($url);

        if (! $response->successful()) {
            return 'Gagal mengambil konfigurasi tunnel: ' . $response->body();
        }

        $config = data_get($response->json(), 'result.config', []);
        $oldIngress = $config['ingress'] ?? [];

        $ingress = collect($oldIngress)
            ->filter(fn ($rule) => isset($rule['hostname']) && isset($rule['service']))
            ->reject(fn ($rule) => $rule['hostname'] === $hostname)
            ->map(function ($rule) {
                return [
                    'hostname' => $rule['hostname'],
                    'service' => $rule['service'],
                ];
            })
            ->values()
            ->toArray();

        $ingress[] = [
            'hostname' => $hostname,
            'service' => $service,
        ];

        $ingress[] = [
            'service' => 'http_status:404',
        ];

        $payload = [
            'config' => [
                'ingress' => $ingress,
            ],
        ];

        $update = $this->cloudflare($setting)->put($url, $payload);

        if (! $update->successful()) {
            return 'Gagal update konfigurasi tunnel: ' . $update->body();
        }

        return true;
    }

    private function ensureDnsRecord(CloudflareSetting $setting, string $hostname): true|string
    {
        $target = $setting->tunnel_id . '.cfargotunnel.com';

        $search = $this->cloudflare($setting)->get(
            "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records",
            [
                'type' => 'CNAME',
                'name' => $hostname,
            ]
        );

        if (! $search->successful()) {
            return 'Gagal cek DNS record: ' . $search->body();
        }

        $existingId = data_get($search->json(), 'result.0.id');

        $payload = [
            'type' => 'CNAME',
            'name' => $hostname,
            'content' => $target,
            'ttl' => 1,
            'proxied' => true,
        ];

        if ($existingId) {
            $update = $this->cloudflare($setting)->put(
                "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records/{$existingId}",
                $payload
            );

            if (! $update->successful()) {
                return 'Gagal update DNS record: ' . $update->body();
            }

            return true;
        }

        $create = $this->cloudflare($setting)->post(
            "https://api.cloudflare.com/client/v4/zones/{$setting->zone_id}/dns_records",
            $payload
        );

        if (! $create->successful()) {
            return 'Gagal membuat DNS record: ' . $create->body();
        }

        return true;
    }

    private function restartCloudflared(): void
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return;
        }

        $process = Process::fromShellCommandline('sudo systemctl restart cloudflared');
        $process->setTimeout(60);
        $process->run();
    }

    private function cloudflare(CloudflareSetting $setting)
    {
        return Http::withToken($setting->api_token)
            ->acceptJson();
    }
}