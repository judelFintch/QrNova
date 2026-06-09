<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Bibliothèque</p><h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Vos QR Codes</h1><p class="mt-2 text-slate-600">Retrouvez, téléchargez et gérez toutes vos créations.</p></div>
        <a href="{{ route('qr-code.generator') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">Créer un QR Code</a>
    </div>

    @if (session('success'))<div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

    <div class="mt-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_220px]">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Rechercher par nom ou contenu..." class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <select wire:model.live="type" class="rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-indigo-500"><option value="">Tous les types</option>@foreach($types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
    </div>

    <div wire:loading.class="opacity-50" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($qrCodes as $qrCode)
            <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-5 py-3"><span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $types[$qrCode->type] ?? $qrCode->type }}</span><span class="text-xs text-slate-500">{{ $qrCode->created_at->format('d/m/Y') }}</span></div>
                <div class="p-5"><h2 class="truncate text-lg font-black text-slate-950">{{ $qrCode->name ?: 'Sans nom' }}</h2><p class="mt-2 line-clamp-2 min-h-10 break-all text-sm leading-5 text-slate-500">{{ $qrCode->content }}</p><div class="mt-5 grid grid-cols-2 gap-2"><a href="{{ route('qr-code.show', $qrCode) }}" class="rounded-xl bg-slate-950 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-indigo-600">Ouvrir</a><a href="{{ route('qr-code.edit', $qrCode) }}" class="rounded-xl border border-indigo-200 px-4 py-2.5 text-center text-sm font-bold text-indigo-600 transition hover:bg-indigo-50">Modifier</a>@if ($qrCode->type === 'progressive')<div class="col-span-2"><x-share-link :url="$qrCode->content" :title="$qrCode->name" text="Découvrez ce profil" compact /></div>@endif<button type="button" wire:click="delete({{ $qrCode->id }})" wire:confirm="Supprimer définitivement ce QR Code ?" class="col-span-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">Supprimer</button></div></div>
            </article>
        @empty
            <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><p class="text-lg font-black">Aucun QR Code trouvé</p><p class="mt-2 text-sm text-slate-500">Créez votre premier QR Code ou modifiez vos filtres.</p></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $qrCodes->links() }}</div>
</div>
