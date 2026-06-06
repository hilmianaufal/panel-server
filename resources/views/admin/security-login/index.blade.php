@extends('layouts.admin')

@section('title', 'Security Login')

@section('content')

<div class="mb-8">
    <h3 class="text-3xl font-bold">Security Login</h3>
    <div class="mb-6 flex gap-3">
    <a href="{{ route('admin.security-login.2fa') }}"
       class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
        <i data-lucide="key-round" class="w-4 h-4"></i>
        Setup 2FA
    </a>

    @if (auth()->user()->google2fa_enabled)
        <form method="POST" action="{{ route('admin.security-login.2fa.disable') }}">
            @csrf
            <button onclick="return confirm('Matikan 2FA?')"
                class="px-5 py-3 rounded-2xl bg-red-600 hover:bg-red-700 flex items-center gap-2">
                <i data-lucide="shield-x" class="w-4 h-4"></i>
                Disable 2FA
            </button>
        </form>
    @endif
</div>
    <p class="text-slate-400 mt-2">Pantau riwayat login berhasil dan gagal.</p>
</div>

<div class="rounded-3xl bg-white/10 border border-white/10 p-6 backdrop-blur-xl shadow-xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-400 border-b border-white/10">
                    <th class="py-4">Email</th>
                    <th class="py-4">IP Address</th>
                    <th class="py-4">Status</th>
                    <th class="py-4">Waktu</th>
                    <th class="py-4">User Agent</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($loginActivities as $activity)
                    <tr class="border-b border-white/5">
                        <td class="py-4">
                            {{ $activity->email ?? '-' }}
                        </td>

                        <td class="py-4 text-slate-400">
                            {{ $activity->ip_address ?? '-' }}
                        </td>

                        <td class="py-4">
                            @if ($activity->success)
                                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs">
                                    Success
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-xs">
                                    Failed
                                </span>
                            @endif
                        </td>

                        <td class="py-4 text-slate-400">
                            {{ $activity->created_at->format('Y-m-d H:i:s') }}
                        </td>

                        <td class="py-4 text-slate-500 max-w-md truncate">
                            {{ $activity->user_agent }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-slate-400">
                            <i data-lucide="shield-alert" class="w-12 h-12 mx-auto mb-4"></i>
                            Belum ada riwayat login.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $loginActivities->links() }}
    </div>
</div>

<script>
    lucide.createIcons();
</script>

@endsection