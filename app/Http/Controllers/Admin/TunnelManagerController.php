<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CloudflareSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            'account_id' => ['required'],
            'tunnel_id' => ['required'],
            'api_token' => ['required'],
            'tunnel_name' => ['nullable'],
        ]);

        CloudflareSetting::updateOrCreate(['id' => 1], $validated);

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

    private function cloudflare(CloudflareSetting $setting)
    {
        return Http::withToken($setting->api_token)
            ->acceptJson();
    }

public function addHostname(Request $request)
{
    $validated = $request->validate([
        'hostname' => ['required', 'string', 'max:255'],
        'service' => ['required', 'string', 'max:255'],
    ]);

    $setting = CloudflareSetting::firstOrFail();

    $url = "https://api.cloudflare.com/client/v4/accounts/{$setting->account_id}/cfd_tunnel/{$setting->tunnel_id}/configurations";

    $response = $this->cloudflare($setting)->get($url);

    if (! $response->successful()) {
        return back()->with('error', 'Gagal mengambil konfigurasi tunnel: ' . $response->body());
    }

    $config = data_get($response->json(), 'result.config', []);
    $oldIngress = $config['ingress'] ?? [];

    $ingress = collect($oldIngress)
        ->filter(fn ($rule) => isset($rule['hostname']) && isset($rule['service']))
        ->reject(fn ($rule) => $rule['hostname'] === $validated['hostname'])
        ->map(function ($rule) {
            return [
                'hostname' => $rule['hostname'],
                'service' => $rule['service'],
            ];
        })
        ->values()
        ->toArray();

    $ingress[] = [
        'hostname' => $validated['hostname'],
        'service' => $validated['service'],
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
        return back()->with('error', 'Gagal menambahkan hostname: ' . $update->body());
    }

    activity()
        ->causedBy(auth()->user())
        ->withProperties($validated)
        ->log('Cloudflare Tunnel hostname ditambahkan');

    return back()->with('success', 'Hostname berhasil ditambahkan ke Cloudflare Tunnel.');
}
}