@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Bienvenue</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Connexion</h1>
            <p class="mt-2 text-sm text-slate-500">Accédez à votre espace de gestion de QR Codes.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="label">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="field">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="label">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="field">
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-3 text-sm font-semibold text-slate-600">
                    <input name="remember" type="checkbox" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Se souvenir de moi
                </label>

                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Se connecter</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Créer un compte</a>
            </p>
        </div>
    </div>
@endsection
