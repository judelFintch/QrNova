<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('qr-code.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">← Retour à l’historique</a>
    <div class="mt-6 grid items-start gap-8 lg:grid-cols-[380px_1fr]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50">
            <div class="grid aspect-square place-items-center overflow-hidden rounded-3xl bg-slate-50 p-5">{!! $previewSvg !!}</div>
            <div class="mt-5 grid grid-cols-3 gap-2">@foreach (['png' => 'PNG', 'svg' => 'SVG', 'pdf' => 'PDF'] as $format => $label)<a href="{{ route('qr-code.download', ['qrCode' => $qrCode, 'format' => $format]) }}" class="rounded-xl bg-slate-950 px-3 py-3 text-center text-xs font-bold text-white transition hover:bg-indigo-600">{{ $label }}</a>@endforeach</div>
            @if ($qrCode->type === 'progressive')
                <div class="mt-5"><x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" /></div>
            @endif
        </section>
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
            <div class="border-b border-slate-200 p-6 sm:p-8"><div class="flex flex-wrap items-center justify-between gap-4"><span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase text-indigo-700">{{ \App\Services\QrCodeService::TYPES[$qrCode->type] ?? $qrCode->type }}</span><a href="{{ route('qr-code.edit', $qrCode) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">Modifier</a></div><h1 class="mt-4 text-3xl font-black tracking-tight">{{ $qrCode->name ?: 'Sans nom' }}</h1><p class="mt-2 text-sm text-slate-500">Créé le {{ $qrCode->created_at->format('d/m/Y à H:i') }}</p></div>
            <dl class="divide-y divide-slate-100">
                @foreach (['Contenu' => $qrCode->content, 'Format initial' => strtoupper($qrCode->format), 'Taille' => $qrCode->size.' px', 'Marge' => $qrCode->margin.' px', 'Couleur principale' => $qrCode->foreground_color, 'Couleur de fond' => $qrCode->background_color, 'Correction d’erreur' => data_get($qrCode->options, 'error_correction', 'medium')] as $label => $value)
                    <div class="grid gap-2 px-6 py-4 sm:grid-cols-[180px_1fr] sm:px-8"><dt class="text-sm font-bold text-slate-500">{{ $label }}</dt><dd class="break-all text-sm font-semibold text-slate-900">{{ $value }}</dd></div>
                @endforeach
            </dl>
        </section>
    </div>
</div>
