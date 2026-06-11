<div x-data="{
    dlModal: false,
    fmt: 'png',
    size: '',
    get isVector() { return ['svg','eps'].includes(this.fmt); },
    get isPrint()  { return this.fmt === 'print'; },
    buildUrl() {
        const base = '{{ route('qr-code.download', $qrCode) }}';
        const p = new URLSearchParams({ format: this.fmt });
        if (this.size) p.set('size', this.size);
        return base + '?' + p.toString();
    },
    printQr() {
        const svg = document.getElementById('qr-svg-preview').innerHTML;
        const w = window.open('', '_blank', 'width=700,height=700');
        w.document.write('<html><head><title>{{ addslashes($qrCode->name ?: 'QR Code') }}</title><style>body{margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#fff}svg{max-width:80vmin;height:auto}@media print{@page{margin:0}}</style></head><body>' + svg + '</body></html>');
        w.document.close(); w.focus(); setTimeout(() => w.print(), 300);
        this.dlModal = false;
    }
}">

    {{-- ── PAGE HEADER ── --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-4 sm:px-6 lg:px-8">

            <a href="{{ route('qr-code.index') }}"
               class="mr-1 grid size-9 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
            </a>

            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2.5">
                <h1 class="truncate text-2xl font-black tracking-tight text-slate-950">
                    {{ $qrCode->name ?: 'Sans nom' }}
                </h1>
                @if(!$qrCode->is_active)
                    <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">Suspendu</span>
                @elseif($qrCode->isExpired())
                    <span class="shrink-0 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">Expiré</span>
                @else
                    <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">Actif</span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="toggleActive"
                    class="flex h-9 items-center gap-1.5 rounded-xl border px-4 text-sm font-bold transition
                        {{ $qrCode->is_active
                            ? 'border-slate-300 text-slate-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700'
                            : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    @if($qrCode->is_active)
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.25 3A2.25 2.25 0 0 0 3 5.25v9.5A2.25 2.25 0 0 0 5.25 17h9.5A2.25 2.25 0 0 0 17 14.75v-9.5A2.25 2.25 0 0 0 14.75 3h-9.5Z"/></svg>
                        Suspendre
                    @else
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 10a8 8 0 1 1 16 0 8 8 0 0 1-16 0Zm6.39-2.908a.75.75 0 0 1 .766.027l3.5 2.25a.75.75 0 0 1 0 1.262l-3.5 2.25A.75.75 0 0 1 8 12.25v-4.5a.75.75 0 0 1 .39-.658Z" clip-rule="evenodd"/></svg>
                        Activer
                    @endif
                </button>

                <button @click="dlModal = true"
                    class="flex h-9 items-center gap-1.5 rounded-xl border border-slate-300 px-4 text-sm font-bold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                    Télécharger
                </button>

                <a href="{{ route('qr-code.edit', $qrCode) }}"
                   class="flex h-9 items-center gap-1.5 rounded-xl bg-indigo-600 px-4 text-sm font-bold text-white transition hover:bg-indigo-700">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"/></svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">

        {{-- ── STATUS ALERT (suspended / expired only) ── --}}
        @if(!$qrCode->is_active)
            <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3">
                <svg class="size-5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-semibold text-amber-800">Ce QR Code est suspendu — les scans sont enregistrés mais la redirection est bloquée.</p>
            </div>
        @elseif($qrCode->isExpired())
            <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-3">
                <svg class="size-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-semibold text-red-800">Campagne terminée le {{ $qrCode->campaign_end_at->format('d/m/Y') }} — ce QR Code ne redirige plus.</p>
            </div>
        @endif

        {{-- ── MAIN GRID ── --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- ── QR PREVIEW CARD ── --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Hero gradient + QR --}}
                <div class="flex items-center justify-center bg-gradient-to-br from-indigo-50 via-slate-50 to-violet-50 px-8 py-10">
                    <div id="qr-svg-preview" class="w-44 overflow-hidden rounded-2xl bg-white p-3 shadow-lg ring-1 ring-slate-100">
                        {!! $previewSvg !!}
                    </div>
                </div>

                {{-- Meta --}}
                <div class="space-y-3 p-5">

                    {{-- Type badge + date --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-600">
                            {{ \App\Services\QrCodeService::TYPES[$qrCode->type] ?? $qrCode->type }}
                        </span>
                        <span class="text-xs text-slate-400">
                            Créé le {{ $qrCode->created_at->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- URL --}}
                    @if(in_array($qrCode->type, ['url', 'progressive']))
                        <a href="{{ $qrCode->display_url }}" target="_blank" rel="noopener noreferrer"
                           class="group flex items-start gap-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-slate-400 transition group-hover:text-indigo-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 0 0-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 0 0 .75-.75v-4a.75.75 0 0 1 1.5 0v4A2.25 2.25 0 0 1 12.75 17h-8.5A2.25 2.25 0 0 1 2 14.75v-8.5A2.25 2.25 0 0 1 4.25 4h5a.75.75 0 0 1 0 1.5h-5Zm6.75-3a.75.75 0 0 1 .75-.75h5.25a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L10.03 10.53a.75.75 0 0 1-1.06-1.06l5.72-5.72h-3.44a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                            <span class="min-w-0 truncate text-sm font-semibold text-indigo-600 group-hover:underline">
                                {{ $qrCode->display_url }}
                            </span>
                        </a>
                    @endif

                    {{-- Share for progressive --}}
                    @if($qrCode->type === 'progressive')
                        <x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" />
                    @endif
                </div>
            </div>

            {{-- ── RIGHT COLUMN ── --}}
            <div class="flex flex-col gap-5 lg:col-span-2">

                {{-- STAT CARDS ROW --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total</p>
                        <p class="mt-2 text-4xl font-black tabular-nums leading-none text-slate-950">{{ $this->allTimeScans }}</p>
                        <p class="mt-2 text-xs text-slate-400">scans au total</p>
                    </div>
                    <div class="flex flex-col rounded-2xl border border-indigo-100 bg-indigo-50 p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-400">Période</p>
                        <p class="mt-2 text-4xl font-black tabular-nums leading-none text-indigo-700">{{ $this->totalScans }}</p>
                        <p class="mt-2 text-xs text-indigo-400">scans filtrés</p>
                    </div>
                    <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Uniques</p>
                        <p class="mt-2 text-4xl font-black tabular-nums leading-none text-slate-950">{{ $this->uniqueScans }}</p>
                        <p class="mt-2 text-xs text-slate-400">IP distinctes</p>
                    </div>
                </div>

                {{-- DEVICES + CAMPAIGN row --}}
                <div class="grid flex-1 grid-cols-1 gap-5 sm:grid-cols-2">

                    {{-- Device breakdown --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Appareils</p>

                        @php
                            $devices = $this->deviceBreakdown;
                            $deviceTotal = max(1, array_sum($devices));
                            $deviceList = [
                                'mobile'  => ['label' => 'Mobile',   'color' => 'bg-indigo-500'],
                                'desktop' => ['label' => 'Desktop',  'color' => 'bg-blue-500'],
                                'tablet'  => ['label' => 'Tablette', 'color' => 'bg-violet-500'],
                            ];
                        @endphp

                        @if(array_sum($devices) > 0)
                            <div class="mt-4 space-y-3.5">
                                @foreach($deviceList as $dKey => $dItem)
                                    @php $dCount = $devices[$dKey] ?? 0; $dPct = round($dCount / $deviceTotal * 100); @endphp
                                    <div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-semibold text-slate-600">{{ $dItem['label'] }}</span>
                                            <span class="font-bold text-slate-700">{{ $dCount }} <span class="font-normal text-slate-400">({{ $dPct }}%)</span></span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                            <div class="{{ $dItem['color'] }} h-full rounded-full" style="width: {{ $dPct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 flex flex-col items-center justify-center gap-2 py-8 text-center">
                                <svg class="size-8 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 15.75h3"/></svg>
                                <p class="text-xs font-medium text-slate-400">Aucune donnée</p>
                            </div>
                        @endif
                    </div>

                    {{-- Campaign card --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Campagne</p>
                            @if(!$editingCampaign)
                                <button wire:click="$set('editingCampaign', true)"
                                        class="rounded-lg border border-indigo-200 px-2.5 py-1 text-xs font-bold text-indigo-600 transition hover:bg-indigo-50">
                                    Modifier
                                </button>
                            @endif
                        </div>

                        @if($editingCampaign)
                            <form wire:submit="saveCampaign" class="mt-4 flex flex-col gap-3">
                                @error('campaignEndAt')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Matériel d'impression</label>
                                    <input wire:model="printMaterial" type="text" placeholder="Affiche, flyer…"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Nombre de copies</label>
                                    <input wire:model="printCopies" type="number" min="1" placeholder="500"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Début de la campagne</label>
                                    <input wire:model="campaignStartAt" type="date"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-slate-500">Fin de la campagne</label>
                                    <input wire:model="campaignEndAt" type="date"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button type="submit"
                                        class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-700">
                                        Enregistrer
                                    </button>
                                    <button type="button" wire:click="$set('editingCampaign', false)"
                                        class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                        Annuler
                                    </button>
                                </div>
                            </form>
                        @else
                            @php
                            $cells = [
                                [
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>',
                                    'label' => "Matériel",
                                    'value' => $qrCode->print_material,
                                ],
                                [
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>',
                                    'label' => 'Copies',
                                    'value' => $qrCode->print_copies ? number_format($qrCode->print_copies) : null,
                                ],
                                [
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
                                    'label' => 'Début',
                                    'value' => $qrCode->campaign_start_at ? \Carbon\Carbon::parse($qrCode->campaign_start_at)->locale('fr')->isoFormat('D MMM YYYY') : null,
                                ],
                                [
                                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
                                    'label' => 'Fin',
                                    'value' => $qrCode->campaign_end_at ? \Carbon\Carbon::parse($qrCode->campaign_end_at)->locale('fr')->isoFormat('D MMM YYYY') : null,
                                ],
                            ];
                            @endphp
                            <div class="mt-4 space-y-3.5">
                                @foreach($cells as $cell)
                                <div class="flex items-center gap-3">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">{!! $cell['icon'] !!}</svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-400">{{ $cell['label'] }}</p>
                                        <p class="truncate text-sm font-semibold {{ $cell['value'] ? 'text-slate-900' : 'text-slate-300' }}">
                                            {{ $cell['value'] ?? '–' }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ANALYTICS CARD ── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Controls bar --}}
            <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                    <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
                    <input wire:model.live="dateFrom" type="date"
                        class="bg-transparent text-sm font-semibold text-slate-700 outline-none">
                    <span class="text-slate-300">–</span>
                    <input wire:model.live="dateTo" type="date"
                        class="bg-transparent text-sm font-semibold text-slate-700 outline-none">
                </div>

                <div class="ml-auto flex flex-wrap gap-2">
                    <button wire:click="exportCsv"
                        class="flex items-center gap-2 rounded-xl border border-indigo-200 px-4 py-2.5 text-sm font-bold text-indigo-600 transition hover:bg-indigo-50">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v8.614l2.955-3.129a.75.75 0 1 1 1.09 1.03l-4.25 4.5a.75.75 0 0 1-1.09 0l-4.25-4.5a.75.75 0 1 1 1.09-1.03l2.955 3.129V2.75A.75.75 0 0 1 10 2ZM3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" clip-rule="evenodd"/></svg>
                        Exporter
                    </button>

                    <button wire:click="resetScans" wire:confirm="Supprimer tous les scans de ce QR Code ?"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd"/></svg>
                        Réinitialiser
                    </button>
                </div>
            </div>

            {{-- Chart --}}
            <div class="px-6 py-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-slate-950">Activité de scans</h2>
                    <div class="flex items-center gap-5 text-xs font-semibold text-slate-500">
                        <span class="flex items-center gap-2">
                            <span class="inline-block h-2.5 w-4 rounded-sm bg-indigo-500"></span>Scans
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="inline-block h-2.5 w-4 rounded-sm bg-blue-400"></span>Uniques
                        </span>
                    </div>
                </div>

                <div class="relative mt-5 h-64">
                    <canvas id="scans-chart-{{ $qrCode->id }}" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- ── DOWNLOAD MODAL ── --}}
    <div x-show="dlModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="dlModal = false"></div>
        <div class="relative w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl" @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-950">Télécharger le QR Code</h2>
                <button @click="dlModal = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                </button>
            </div>

            <p class="mt-1 text-sm text-slate-400">Choisissez le format puis téléchargez.</p>
            <div class="mt-4 h-px bg-slate-100"></div>

            <div class="mt-5 grid grid-cols-3 gap-3 sm:grid-cols-6">
                @foreach(['png' => ['PNG','raster'], 'jpeg' => ['JPEG','raster'], 'svg' => ['SVG','vector'], 'pdf' => ['PDF','pdf'], 'eps' => ['EPS','eps'], 'print' => ['Imprimer','print']] as $fk => [$fl, $fi])
                <button type="button" @click="fmt = '{{ $fk }}'"
                    :class="fmt === '{{ $fk }}' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                    class="relative flex flex-col items-center gap-2 rounded-2xl border p-3 transition">
                    <span x-show="fmt === '{{ $fk }}'" class="absolute right-2 top-2 flex size-5 items-center justify-center rounded-full bg-indigo-500">
                        <svg class="size-3 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    </span>
                    @if($fi === 'print')
                        <svg class="size-8 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    @elseif($fi === 'pdf')
                        <svg class="size-8 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    @elseif($fi === 'eps')
                        <svg class="size-8 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                    @elseif($fi === 'vector')
                        <svg class="size-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    @else
                        <svg class="size-8 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    @endif
                    <span class="text-xs font-bold text-slate-700">{{ $fl }}</span>
                </button>
                @endforeach
            </div>

            <div class="mt-5" x-show="!isVector && !isPrint">
                <label class="block text-sm font-semibold text-slate-600">Taille du fichier</label>
                <select x-model="size" class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">Par défaut ({{ $qrCode->size }} px)</option>
                    <option value="500">Petite — 500 px</option>
                    <option value="1000">Moyenne — 1 000 px</option>
                    <option value="2000">Grande — 2 000 px</option>
                    <option value="4000">Très grande — 4 000 px</option>
                </select>
            </div>

            <div class="mt-6 flex gap-3">
                <template x-if="!isPrint">
                    <a :href="buildUrl()" @click="dlModal = false"
                       class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                        Télécharger
                    </a>
                </template>
                <template x-if="isPrint">
                    <button @click="printQr()"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153C17.99 6.754 19 8.021 19 9.5V17a1 1 0 0 1-1 1h-2v.25A1.75 1.75 0 0 1 14.25 20h-8.5A1.75 1.75 0 0 1 4 18.25V18H2a1 1 0 0 1-1-1V9.5c0-1.479 1.01-2.746 2.874-3.045.374-.056.75-.107 1.126-.153V2.75Z" clip-rule="evenodd"/></svg>
                        Imprimer
                    </button>
                </template>
                <button @click="dlModal = false"
                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

@script
<script>
    const ctx = document.getElementById('scans-chart-{{ $qrCode->id }}');
    let chart;

    function buildChart(data) {
        if (chart) chart.destroy();
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Scans',
                        data: data.totals,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.07)',
                        borderWidth: 2,
                        pointRadius: 2.5,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Scans uniques',
                        data: data.uniques,
                        borderColor: '#60a5fa',
                        backgroundColor: 'rgba(96,165,250,0.05)',
                        borderWidth: 2,
                        pointRadius: 2.5,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        titleColor: '#0f172a',
                        bodyColor: '#475569',
                        padding: 10,
                    },
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8', maxTicksLimit: 12 },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 }, color: '#94a3b8' },
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        border: { display: false },
                    },
                },
            },
        });
    }

    buildChart(@json($this->chartData));
    $wire.on('chartDataUpdated', (data) => buildChart(data[0]));
</script>
@endscript
