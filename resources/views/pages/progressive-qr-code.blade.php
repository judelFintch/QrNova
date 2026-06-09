@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-10 text-center text-white sm:px-10">
                @if (filled($profile['photo_path'] ?? null))
                    <img src="{{ Storage::disk('public')->url($profile['photo_path']) }}" alt="{{ filled($profile['display_name'] ?? null) ? $profile['display_name'] : $qrCode->name }}" class="mx-auto size-32 rounded-full border-4 border-white/80 object-cover shadow-xl">
                @endif
                <p class="mt-5 text-xs font-bold uppercase tracking-widest text-indigo-100">Profil évolutif</p>
                <h1 class="mt-2 text-3xl font-black">{{ filled($profile['display_name'] ?? null) ? $profile['display_name'] : $qrCode->name }}</h1>
                @if (filled($profile['description'] ?? null))
                    <p class="mx-auto mt-3 max-w-lg leading-7 text-indigo-50">{{ $profile['description'] }}</p>
                @endif
            </div>

            <dl class="divide-y divide-slate-100">
                @foreach ([
                    'Téléphone' => ['value' => $profile['phone'] ?? null, 'href' => filled($profile['phone'] ?? null) ? 'tel:'.$profile['phone'] : null],
                    'E-mail' => ['value' => $profile['email'] ?? null, 'href' => filled($profile['email'] ?? null) ? 'mailto:'.$profile['email'] : null],
                    'Adresse' => ['value' => $profile['address'] ?? null, 'href' => null],
                    'Site web' => ['value' => $profile['website'] ?? null, 'href' => $profile['website'] ?? null],
                ] as $label => $item)
                    @if (filled($item['value']))
                        <div class="grid gap-2 px-6 py-5 sm:grid-cols-[140px_1fr] sm:px-10">
                            <dt class="text-sm font-bold text-slate-500">{{ $label }}</dt>
                            <dd class="break-words font-semibold text-slate-900">
                                @if ($item['href'])
                                    <a href="{{ $item['href'] }}" class="text-indigo-600 hover:text-indigo-800">{{ $item['value'] }}</a>
                                @else
                                    {{ $item['value'] }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
                @foreach ($profile['custom_fields'] ?? [] as $field)
                    @if (filled($field['label'] ?? null) && filled($field['value'] ?? null))
                        <div class="grid gap-2 px-6 py-5 sm:grid-cols-[140px_1fr] sm:px-10">
                            <dt class="text-sm font-bold text-slate-500">{{ $field['label'] }}</dt>
                            <dd class="whitespace-pre-line break-words font-semibold text-slate-900">{{ $field['value'] }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </article>
        <div class="mt-6">
            <x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" />
        </div>
    </div>
@endsection
