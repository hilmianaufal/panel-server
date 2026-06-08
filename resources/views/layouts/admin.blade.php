<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Server</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-[#020617] text-slate-100 overflow-x-hidden">

<div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[radial-gradient(circle_at_top_left,#1e3a8a33,transparent_35%),radial-gradient(circle_at_top_right,#7c3aed22,transparent_30%)]">

    {{-- Mobile Overlay --}}
    <div
        x-cloak
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden">
    </div>

    {{-- Sidebar Desktop + Mobile --}}
    <aside
        x-cloak
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed lg:sticky top-0 left-0 z-50 h-screen w-80 flex flex-col border-r border-white/10 bg-slate-950/90 backdrop-blur-xl transition-transform duration-300">

        <div class="p-7 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-600/30">
                    <i data-lucide="server" class="w-6 h-6"></i>
                </div>

                <div>
                    <h1 class="text-xl font-bold tracking-tight">HilmiDev Panel</h1>
                    <p class="text-xs text-slate-400">Private Server Control</p>
                </div>
            </div>

            <button @click="sidebarOpen = false" class="lg:hidden h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <nav class="flex-1 p-5 space-y-2 overflow-y-auto">

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.websites.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.websites.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="globe-2" class="w-5 h-5"></i>
                <span>Websites</span>
            </a>

                <a href="{{ route('admin.tunnels.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-300 hover:bg-white/10">

                    <i data-lucide="cloud" class="w-5 h-5"></i>

                    <span>Tunnel Manager</span>

                </a>
            <a href="{{ route('admin.files.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.files.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="folder-code" class="w-5 h-5"></i>
                <span>File Manager</span>
            </a>

            <a href="{{ route('admin.databases.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.databases.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="database" class="w-5 h-5"></i>
                <span>Database</span>
            </a>
            <a href="https://pma.hilmidev.my.id"
            target="_blank"
            class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-300 hover:bg-white/10 hover:text-white">
                <i data-lucide="database-zap" class="w-5 h-5"></i>
                <span>phpMyAdmin</span>
            </a>
            <a href="{{ route('admin.backups.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.backups.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="archive-restore" class="w-5 h-5"></i>
                <span>Backup</span>
            </a>

            <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.services.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="activity" class="w-5 h-5"></i>
                <span>Services</span>
            </a>
                <a href="{{ route('admin.ssl.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.ssl.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                    <span>SSL Manager</span>
                </a>
            <a href="{{ route('admin.deploy.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.deploy.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                <span>Deploy Manager</span>
            </a>

            <a href="{{ route('admin.security.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.security.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <span>Security</span>
            </a>

            <a href="{{ route('admin.security-login.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.security-login.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                <span>Security Login</span>
            </a>

            <a href="{{ route('admin.activities.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('admin.activities.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <i data-lucide="history" class="w-5 h-5"></i>
                <span>Activity Log</span>
            </a>
        </nav>

    </aside>

    {{-- Main --}}
    <main class="flex-1 min-w-0 lg:ml-0">

        <header class="h-20 border-b border-white/10 bg-slate-950/50 backdrop-blur-xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto flex h-full w-full max-w-7xl items-center justify-between gap-4">

                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden h-11 w-11 rounded-2xl bg-white/10 hover:bg-white/15 flex items-center justify-center">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div>
                        <p class="text-xs sm:text-sm text-slate-400">Control Center</p>
                        <h2 class="text-xl sm:text-2xl font-bold">@yield('title', 'Dashboard')</h2>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button class="h-11 w-11 rounded-2xl bg-white/10 hover:bg-white/15 flex items-center justify-center">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                    </button>

                    <div class="hidden sm:flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/10">
                        <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>

                        <div>
                            <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400">Super Admin</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="h-11 px-3 sm:px-4 rounded-2xl bg-red-600/90 hover:bg-red-600 flex items-center gap-2">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span class="hidden md:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <section class="p-4 sm:p-6 lg:p-8">
            <div class="mx-auto w-full max-w-7xl">
                @yield('content')
            </div>
        </section>

    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();
    });
</script>

</body>
</html>