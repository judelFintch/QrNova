@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-10 text-center text-white sm:px-10">
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-100">Fichiers partagés</p>
                <h1 class="mt-2 text-3xl font-black">{{ $qrCode->name }}</h1>
                <p class="mt-2 text-sm text-indigo-200">{{ $files->count() }} fichier{{ $files->count() > 1 ? 's' : '' }} disponible{{ $files->count() > 1 ? 's' : '' }}</p>
            </div>

            <ul class="divide-y divide-slate-100">
                @foreach ($files as $file)
                    <li class="flex items-center gap-4 px-6 py-5 sm:px-10">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            @php
                                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                $icon = match(true) {
                                    in_array($ext, ['pdf']) => '📄',
                                    in_array($ext, ['doc', 'docx']) => '📝',
                                    in_array($ext, ['xls', 'xlsx']) => '📊',
                                    in_array($ext, ['ppt', 'pptx']) => '📋',
                                    in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) => '🖼️',
                                    in_array($ext, ['mp3']) => '🎵',
                                    in_array($ext, ['mp4']) => '🎬',
                                    default => '📁',
                                };
                            @endphp
                            <span class="text-xl">{{ $icon }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-900">{{ $file['name'] }}</p>
                            @if ($file['size'] > 0)
                                <p class="text-sm text-slate-500">
                                    @php
                                        $size = $file['size'];
                                        $formatted = $size >= 1048576
                                            ? round($size / 1048576, 1) . ' Mo'
                                            : ($size >= 1024 ? round($size / 1024, 1) . ' Ko' : $size . ' o');
                                    @endphp
                                    {{ $formatted }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ $file['url'] }}" target="_blank" rel="noopener" download class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">
                            Télécharger
                        </a>
                    </li>
                @endforeach
            </ul>
        </article>
    </div>
@endsection
