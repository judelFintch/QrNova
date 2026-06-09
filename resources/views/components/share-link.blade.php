@props([
    'url',
    'title' => 'Partager ce lien',
    'text' => 'Découvrez ce profil QrNova',
    'compact' => false,
])

<div
    x-data="{
        copied: false,
        async copyLink() {
            await navigator.clipboard.writeText(@js($url));
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
        async shareLink() {
            if (navigator.share) {
                await navigator.share({ title: @js($title), text: @js($text), url: @js($url) });
                return;
            }

            await this.copyLink();
        }
    }"
    @class(['rounded-2xl border border-slate-200 bg-white p-5' => ! $compact])
>
    @if ($compact)
        <button type="button" x-on:click="shareLink" class="w-full rounded-xl border border-emerald-200 px-4 py-2.5 text-center text-sm font-bold text-emerald-700 transition hover:bg-emerald-50">Partager</button>
    @else
    <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Partager le lien</p>
    <div class="mt-3 flex gap-2">
        <input value="{{ $url }}" readonly class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-600">
        <button type="button" x-on:click="copyLink" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
            <span x-show="!copied">Copier</span>
            <span x-cloak x-show="copied">Copié</span>
        </button>
    </div>
    <div class="mt-3 grid grid-cols-3 gap-2">
        <button type="button" x-on:click="shareLink" class="rounded-xl bg-indigo-600 px-3 py-2.5 text-center text-xs font-bold text-white transition hover:bg-indigo-700">Partager</button>
        <a href="https://wa.me/?text={{ rawurlencode($text.' '.$url) }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-emerald-600 px-3 py-2.5 text-center text-xs font-bold text-white transition hover:bg-emerald-700">WhatsApp</a>
        <a href="mailto:?subject={{ rawurlencode($title) }}&body={{ rawurlencode($text."\n\n".$url) }}" class="rounded-xl bg-slate-950 px-3 py-2.5 text-center text-xs font-bold text-white transition hover:bg-slate-800">E-mail</a>
    </div>
    @endif
</div>
