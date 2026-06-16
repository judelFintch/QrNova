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

            @php
                $attachedFiles = collect(data_get($qrCode->options, 'form_data.uploaded_files', []))
                    ->filter(fn ($f) => \Illuminate\Support\Facades\Storage::disk('public')->exists($f['path'] ?? ''))
                    ->values();
            @endphp
            @if ($attachedFiles->isNotEmpty())
                <div class="border-t border-slate-100 px-6 py-6 sm:px-10">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">Fichiers joints</p>
                    <div class="space-y-2">
                        @foreach ($attachedFiles as $file)
                            <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" rel="noopener" download class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50">
                                <span class="text-xl">
                                    @php $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); @endphp
                                    @if ($ext === 'pdf') 📄
                                    @elseif (in_array($ext, ['doc','docx'])) 📝
                                    @elseif (in_array($ext, ['xls','xlsx'])) 📊
                                    @elseif (in_array($ext, ['ppt','pptx'])) 📋
                                    @elseif (in_array($ext, ['png','jpg','jpeg','gif','webp'])) 🖼️
                                    @elseif ($ext === 'mp3') 🎵
                                    @elseif ($ext === 'mp4') 🎬
                                    @else 📁
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800">{{ $file['name'] }}</span>
                                @if (($file['size'] ?? 0) > 0)
                                    <span class="shrink-0 text-xs text-slate-400">{{ $file['size'] >= 1048576 ? round($file['size'] / 1048576, 1).' Mo' : round($file['size'] / 1024, 1).' Ko' }}</span>
                                @endif
                                <span class="shrink-0 text-xs font-bold text-indigo-600">Télécharger</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
        <div class="mt-6">
            <x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" />
        </div>
    </div>
@endsection
