<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Créez, personnalisez et téléchargez vos QR Codes professionnels.">
    <title>{{ $title ?? 'QrNova' }} · QrNova</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-black tracking-tight text-slate-950">
                <span class="grid size-10 place-items-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM15 14h2v2h-2zM19 14h1v3h-3v3h-3v-2"/></svg>
                </span>
                <span class="text-xl">Qr<span class="text-indigo-600">Nova</span></span>
            </a>
            <div class="flex items-center gap-2 text-sm font-semibold">
                @auth
                    <a href="{{ route('qr-code.generator') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100">Créer</a>
                    <a href="{{ route('qr-code.index') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100">Historique</a>
                    <span class="hidden px-3 py-2 text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-slate-200 px-3 py-2 transition hover:bg-slate-100">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 transition hover:bg-slate-100">Connexion</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-3 py-2 text-white transition hover:bg-indigo-700">Créer un compte</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>{{ $slot ?? '' }}@yield('content')</main>

    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>© {{ date('Y') }} QrNova. Vos QR Codes, simplement.</p>
            <p>Conçu par <a href="https://www.fintchweb.com/" target="_blank" rel="noopener noreferrer" class="font-bold text-indigo-600 transition hover:text-indigo-800">FintchWeb</a></p>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
