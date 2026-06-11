<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8"
     x-data="{
         dlModal: false,
         fmt: 'png',
         size: '',
         get isVector() { return ['svg','eps'].includes(this.fmt); },
         get isPrint() { return this.fmt === 'print'; },
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

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('qr-code.index') }}" class="flex items-center gap-1.5 text-sm font-bold text-indigo-600 hover:text-indigo-800">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
            Retour
        </a>
        <h1 class="text-2xl font-black tracking-tight text-slate-950">{{ $qrCode->name ?: 'Sans nom' }}</h1>
        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="toggleActive"
                class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold transition
                    {{ $qrCode->is_active ? 'border-amber-300 text-amber-700 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}">
                @if($qrCode->is_active)
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                    Suspendre
                @else
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    Activer
                @endif
            </button>
            <button @click="dlModal = true"
               class="flex items-center gap-2 rounded-xl border border-indigo-300 px-4 py-2 text-sm font-bold text-indigo-600 transition hover:bg-indigo-50">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                Télécharger
            </button>
            <a href="{{ route('qr-code.edit', $qrCode) }}"
               class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"/></svg>
                Modifier
            </a>
        </div>
    </div>

    {{-- Status badge --}}
    @if(!$qrCode->is_active)
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-800">
            Ce QR Code est suspendu — les scans sont enregistrés mais la redirection est bloquée.
        </div>
    @elseif($qrCode->isExpired())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-semibold text-red-800">
            La campagne est terminée ({{ $qrCode->campaign_end_at->format('d/m/Y') }}) — ce QR Code ne redirige plus.
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[340px_1fr]">

        {{-- Left: QR preview + downloads --}}
        <div class="space-y-4">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div id="qr-svg-preview" class="grid aspect-square place-items-center overflow-hidden rounded-2xl bg-slate-50 p-5">
                    {!! $previewSvg !!}
                </div>
                <button @click="dlModal = true"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-950 py-3 text-sm font-bold text-white transition hover:bg-indigo-600">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                    Télécharger / Imprimer
                </button>
                @if($qrCode->type === 'progressive')
                    <div class="mt-4"><x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" /></div>
                @endif
            </section>

            {{-- All-time total --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total de scans (all time)</p>
                <p class="mt-1 text-5xl font-black text-indigo-600">{{ $this->allTimeScans }}</p>
            </section>
        </div>

        {{-- Right: info + campagne --}}
        <div class="space-y-4">

            {{-- Info card --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase text-indigo-700">
                        {{ \App\Services\QrCodeService::TYPES[$qrCode->type] ?? $qrCode->type }}
                    </span>
                    <span class="text-xs text-slate-400">Créé le {{ $qrCode->created_at->format('d/m/Y à H:i') }}</span>
                </div>

                <dl class="mt-5 divide-y divide-slate-100">
                    <div class="flex items-start gap-4 py-3">
                        <dt class="w-36 shrink-0 text-sm font-semibold text-slate-500">Contenu</dt>
                        <dd class="break-all text-sm font-semibold text-slate-900">
                            @if(in_array($qrCode->type, ['url', 'progressive']))
                                <a href="{{ $qrCode->display_url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-indigo-600 underline underline-offset-2 hover:text-indigo-800">
                                    {{ $qrCode->display_url }}
                                </a>
                            @else
                                {{ $qrCode->content }}
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-start gap-4 py-3">
                        <dt class="w-36 shrink-0 text-sm font-semibold text-slate-500">Format</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ strtoupper($qrCode->format) }}</dd>
                    </div>
                    <div class="flex items-start gap-4 py-3">
                        <dt class="w-36 shrink-0 text-sm font-semibold text-slate-500">Taille</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ $qrCode->size }} px</dd>
                    </div>
                    <div class="flex items-start gap-4 py-3">
                        <dt class="w-36 shrink-0 text-sm font-semibold text-slate-500">Correction</dt>
                        <dd class="text-sm font-semibold text-slate-900">{{ data_get($qrCode->options, 'error_correction', 'medium') }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Campagne card --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-slate-950">Informations de campagne</h2>
                    @if(!$editingCampaign)
                        <button wire:click="$set('editingCampaign', true)"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Modifier</button>
                    @endif
                </div>

                @if($editingCampaign)
                    <form wire:submit="saveCampaign" class="mt-4 space-y-3">
                        @error('campaignEndAt')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                        <div class="grid grid-cols-2 gap-3">
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
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-500">Matériel d'impression</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $qrCode->print_material ?: '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-500">Nombre de copies</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $qrCode->print_copies ? number_format($qrCode->print_copies) : '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-500">Début de la campagne</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $qrCode->campaign_start_at?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-xs font-semibold text-slate-500">Fin de la campagne</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">{{ $qrCode->campaign_end_at?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- Analytics section --}}
    <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

        {{-- Controls --}}
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5">
                <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
                <input wire:model.live="dateFrom" type="date"
                    class="bg-transparent text-sm font-semibold text-slate-700 outline-none">
                <span class="text-slate-400">→</span>
                <input wire:model.live="dateTo" type="date"
                    class="bg-transparent text-sm font-semibold text-slate-700 outline-none">
            </div>

            <button wire:click="exportCsv"
                class="flex items-center gap-2 rounded-2xl border border-indigo-200 px-4 py-2.5 text-sm font-bold text-indigo-600 transition hover:bg-indigo-50">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a.75.75 0 0 1 .75.75v8.614l2.955-3.129a.75.75 0 1 1 1.09 1.03l-4.25 4.5a.75.75 0 0 1-1.09 0l-4.25-4.5a.75.75 0 1 1 1.09-1.03l2.955 3.129V2.75A.75.75 0 0 1 10 2ZM3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" clip-rule="evenodd"/></svg>
                Exporter CSV
            </button>

            <button wire:click="resetScans" wire:confirm="Supprimer tous les scans de ce QR Code ?"
                class="flex items-center gap-2 rounded-2xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">
                <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd"/></svg>
                Réinitialiser les scans
            </button>
        </div>

        {{-- KPI strip --}}
        <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
                <p class="text-xs font-semibold text-indigo-500">Scans sur la période</p>
                <p class="mt-1 text-3xl font-black text-indigo-700">{{ $this->totalScans }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-xs font-semibold text-blue-500">Scans uniques</p>
                <p class="mt-1 text-3xl font-black text-blue-700">{{ $this->uniqueScans }}</p>
            </div>
            <div class="col-span-2 rounded-2xl border border-slate-100 bg-slate-50 p-4 sm:col-span-1">
                <p class="text-xs font-semibold text-slate-500">Appareils</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    @forelse($this->deviceBreakdown as $device => $count)
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 shadow-sm">
                            {{ ucfirst($device ?: 'inconnu') }} · {{ $count }}
                        </span>
                    @empty
                        <span class="text-sm font-bold text-slate-400">—</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div class="mt-6">
            <h3 class="text-sm font-bold text-slate-700">Activités de scans</h3>
            <div class="mt-3 flex gap-4 text-xs font-semibold text-slate-500">
                <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-indigo-500"></span> Scans</span>
                <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-blue-400"></span> Scans uniques</span>
            </div>
            <div class="relative mt-3 h-52">
                <canvas id="scans-chart-{{ $qrCode->id }}" class="h-full w-full"></canvas>
            </div>
        </div>
    </section>

</div>

    {{-- Download modal --}}
    <div x-show="dlModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="dlModal = false"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl"
             @click.stop x-trap.noscroll="dlModal">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-950">Sélectionnez le format à télécharger</h2>
                <button @click="dlModal = false" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/></svg>
                </button>
            </div>

            <div class="mt-1 h-px bg-slate-100"></div>

            {{-- Format grid --}}
            <div class="mt-6 grid grid-cols-3 gap-3 sm:grid-cols-6">
                @foreach([
                    'png'   => ['label' => 'PNG',     'icon' => 'raster'],
                    'jpeg'  => ['label' => 'JPEG',    'icon' => 'raster'],
                    'svg'   => ['label' => 'SVG',     'icon' => 'vector'],
                    'pdf'   => ['label' => 'PDF',     'icon' => 'pdf'],
                    'eps'   => ['label' => 'EPS',     'icon' => 'eps'],
                    'print' => ['label' => 'Imprimer','icon' => 'print'],
                ] as $fmtKey => $fmtInfo)
                <button type="button"
                    @click="fmt = '{{ $fmtKey }}'"
                    :class="fmt === '{{ $fmtKey }}' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'"
                    class="relative flex flex-col items-center gap-2 rounded-2xl border p-3 transition">
                    <span x-show="fmt === '{{ $fmtKey }}'" class="absolute right-2 top-2 flex size-5 items-center justify-center rounded-full bg-indigo-500">
                        <svg class="size-3 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    </span>
                    @if($fmtInfo['icon'] === 'print')
                        <svg class="size-10 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    @elseif($fmtInfo['icon'] === 'pdf')
                        <svg class="size-10 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    @elseif($fmtInfo['icon'] === 'eps')
                        <svg class="size-10 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                    @elseif($fmtInfo['icon'] === 'vector')
                        <svg class="size-10 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-10.875 0h15.75c.621 0 1.125-.504 1.125-1.125V5.625c0-.621-.504-1.125-1.125-1.125H4.125C3.504 4.5 3 5.004 3 5.625v12.75c0 .621.504 1.125 1.125 1.125Z"/></svg>
                    @else
                        <svg class="size-10 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    @endif
                    <span class="text-xs font-bold text-slate-700">{{ $fmtInfo['label'] }}</span>
                </button>
                @endforeach
            </div>

            {{-- Size selector (hidden for vector formats and print) --}}
            <div class="mt-5" x-show="!isVector && !isPrint">
                <label class="block text-sm font-semibold text-slate-600">Taille du fichier</label>
                <select x-model="size"
                    class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">Par défaut ({{ $qrCode->size }} px)</option>
                    <option value="500">Petite — 500 px</option>
                    <option value="1000">Moyenne — 1 000 px</option>
                    <option value="2000">Grande — 2 000 px</option>
                    <option value="4000">Très grande — 4 000 px</option>
                </select>
            </div>

            {{-- Actions --}}
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
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153C17.99 6.754 19 8.021 19 9.5V17a1 1 0 0 1-1 1h-2v.25A1.75 1.75 0 0 1 14.25 20h-8.5A1.75 1.75 0 0 1 4 18.25V18H2a1 1 0 0 1-1-1V9.5c0-1.479 1.01-2.746 2.874-3.045.374-.056.75-.107 1.126-.153V2.75ZM6.5 4.5v-.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25V4.5h-7ZM8 17.5h4V15H8v2.5Zm-2 0V15H4.5v2.5H6Zm8.5 0V15H13v2.5h1.5ZM5 12.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 0 1.5h-8.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
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
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Scans uniques',
                        data: data.uniques,
                        borderColor: '#60a5fa',
                        backgroundColor: 'rgba(96,165,250,0.06)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, maxTicksLimit: 10 },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                    },
                },
            },
        });
    }

    buildChart(@json($this->chartData));

    $wire.on('chartDataUpdated', (data) => buildChart(data[0]));
</script>
@endscript
