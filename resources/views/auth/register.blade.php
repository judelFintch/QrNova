@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16 sm:px-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Nouveau compte</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">Inscription</h1>
            <p class="mt-2 text-sm text-slate-500">Créez votre compte pour générer et gérer vos QR Codes.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="name" class="label">Nom</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="field">
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="label">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="label">Mot de passe</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="field">
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field">
                </div>

                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Créer mon compte</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Se connecter</a>
            </p>
        </div>
    </div>
@endsection
