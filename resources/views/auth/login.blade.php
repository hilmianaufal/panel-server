<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel Server</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-[#020617] text-white overflow-hidden">

    <div class="absolute inset-0">
        <div class="absolute -top-32 -left-32 h-[520px] w-[520px] rounded-full bg-blue-600/25 blur-[130px]"></div>
        <div class="absolute -bottom-32 -right-32 h-[520px] w-[520px] rounded-full bg-violet-600/25 blur-[130px]"></div>
        <div class="absolute top-1/3 left-1/2 h-[380px] w-[380px] rounded-full bg-cyan-500/10 blur-[110px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:38px_38px]"></div>
    </div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-6 py-10">
        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

            <section class="hidden lg:block">
                <div class="rounded-[36px] border border-white/10 bg-white/5 backdrop-blur-2xl p-10 shadow-[0_0_90px_rgba(37,99,235,0.18)]">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/10 text-green-400 text-sm border border-green-500/20">
                        <span class="h-2 w-2 rounded-full bg-green-400"></span>
                        Server Online
                    </div>

                    <h1 class="text-5xl font-black leading-tight mt-8">
                        Private Server<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-violet-400">
                            Control Panel
                        </span>
                    </h1>

                    <p class="text-slate-400 mt-5 text-lg max-w-md">
                        Kelola website, database, backup, file, service, dan deployment server Ubuntu kamu dari satu dashboard premium.
                    </p>

                    <div class="grid grid-cols-3 gap-4 mt-10">
                        <div class="rounded-3xl bg-slate-950/60 border border-white/10 p-5">
                            <i data-lucide="globe-2" class="w-6 h-6 text-blue-400 mb-4"></i>
                            <p class="text-3xl font-bold">12</p>
                            <p class="text-xs text-slate-400 mt-1">Websites</p>
                        </div>

                        <div class="rounded-3xl bg-slate-950/60 border border-white/10 p-5">
                            <i data-lucide="database" class="w-6 h-6 text-violet-400 mb-4"></i>
                            <p class="text-3xl font-bold">8</p>
                            <p class="text-xs text-slate-400 mt-1">Databases</p>
                        </div>

                        <div class="rounded-3xl bg-slate-950/60 border border-white/10 p-5">
                            <i data-lucide="shield-check" class="w-6 h-6 text-emerald-400 mb-4"></i>
                            <p class="text-3xl font-bold">2FA</p>
                            <p class="text-xs text-slate-400 mt-1">Protected</p>
                        </div>
                    </div>

                    <div class="mt-10 rounded-3xl bg-slate-950/60 border border-white/10 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-400">Deployment Pipeline</p>
                                <p class="font-semibold mt-1">GitHub → Ubuntu Server → Nginx</p>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-blue-600/20 text-blue-400 flex items-center justify-center">
                                <i data-lucide="rocket" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div class="mx-auto w-full max-w-md rounded-[36px] border border-white/10 bg-white/5 backdrop-blur-2xl p-8 shadow-[0_0_90px_rgba(37,99,235,0.22)]">

                    <div class="flex justify-center">
                        <div class="h-20 w-20 rounded-3xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center shadow-lg shadow-blue-600/30">
                            <i data-lucide="server-cog" class="w-10 h-10"></i>
                        </div>
                    </div>

                    <div class="text-center mt-7">
                        <h2 class="text-3xl font-black">Welcome Back</h2>
                        <p class="text-slate-400 mt-2">Masuk ke private server dashboard</p>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-500/20 bg-red-500/10 text-red-400 px-5 py-4 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mt-6 rounded-2xl border border-green-500/20 bg-green-500/10 text-green-400 px-5 py-4 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm text-slate-300 mb-2">Email Address</label>
                            <div class="relative">
                                <i data-lucide="mail" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       autofocus
                                       autocomplete="username"
                                       placeholder="admin@panel.local"
                                       class="w-full rounded-2xl bg-slate-950/70 border border-white/10 pl-12 pr-4 py-4 text-white placeholder:text-slate-600 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-sm text-slate-300">Password</label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm text-blue-400 hover:text-blue-300">
                                        Lupa password?
                                    </a>
                                @endif
                            </div>

                            <div class="relative">
                                <i data-lucide="lock-keyhole" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500"></i>
                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       autocomplete="current-password"
                                       placeholder="••••••••"
                                       class="w-full rounded-2xl bg-slate-950/70 border border-white/10 pl-12 pr-4 py-4 text-white placeholder:text-slate-600 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-3 text-sm text-slate-400">
                                <input type="checkbox"
                                       name="remember"
                                       class="rounded border-white/10 bg-slate-950 text-blue-600 focus:ring-blue-500">
                                Remember me
                            </label>

                            <div class="flex items-center gap-2 text-xs text-emerald-400">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                2FA Ready
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full rounded-2xl bg-gradient-to-r from-blue-600 to-violet-600 py-4 font-bold shadow-lg shadow-blue-600/25 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            Sign In
                        </button>
                    </form>

                    <div class="mt-8 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-white/5 border border-white/10 p-3 text-center">
                            <p class="text-lg font-bold">99%</p>
                            <p class="text-[11px] text-slate-500">Uptime</p>
                        </div>

                        <div class="rounded-2xl bg-white/5 border border-white/10 p-3 text-center">
                            <p class="text-lg font-bold">SSL</p>
                            <p class="text-[11px] text-slate-500">Secure</p>
                        </div>

                        <div class="rounded-2xl bg-white/5 border border-white/10 p-3 text-center">
                            <p class="text-lg font-bold">Git</p>
                            <p class="text-[11px] text-slate-500">Deploy</p>
                        </div>
                    </div>

                    <p class="text-center text-xs text-slate-600 mt-8">
                        Private infrastructure panel. Authorized access only.
                    </p>
                </div>
            </section>

        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>