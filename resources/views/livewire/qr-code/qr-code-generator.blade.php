<div>
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_#e0e7ff,_transparent_45%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-indigo-700">{{ $qrCode ? 'Modification du QR Code' : 'Studio QR professionnel' }}</span>
            <h1 class="mt-5 max-w-3xl text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">{{ $qrCode ? 'Modifiez et régénérez votre QR Code.' : 'Créez un QR Code qui vous ressemble.' }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $qrCode ? 'Ajustez le contenu ou le style sans créer une nouvelle entrée dans votre historique.' : 'Choisissez un type, personnalisez le rendu et téléchargez un fichier prêt à partager ou à imprimer.' }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

        <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">
            <form wire:submit="generate" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50">
                <div class="border-b border-slate-200 p-6 sm:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Étape 1</p><h2 class="mt-1 text-xl font-black">Type de contenu</h2></div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">{{ count($types) }} formats</span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($types as $value => $label)
                            <button type="button" wire:click="$set('type', '{{ $value }}')" class="rounded-xl border px-3 py-3 text-left text-sm font-bold transition {{ $type === $value ? 'border-indigo-600 bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6 p-6 sm:p-8">
                    <div><label class="label">Nom du QR Code</label><input wire:model="name" type="text" maxlength="100" placeholder="Ex. Menu du restaurant" class="field">@error('name')<p class="error">{{ $message }}</p>@enderror</div>

                    @if (in_array($type, ['url', 'social']))
                        <div><label class="label">{{ $type === 'social' ? 'Lien du profil social' : 'URL du site' }}</label><input wire:model="data.content" type="url" placeholder="https://example.com" class="field">@error('data.content')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'text')
                        <div><label class="label">Texte à encoder</label><textarea wire:model="data.content" rows="5" maxlength="4000" placeholder="Votre texte..." class="field"></textarea>@error('data.content')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'whatsapp')
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Numéro WhatsApp</label><input wire:model="data.phone" placeholder="+243..." class="field">@error('data.phone')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Message prérempli</label><input wire:model="data.message" placeholder="Bonjour..." class="field">@error('data.message')<p class="error">{{ $message }}</p>@enderror</div></div>
                    @elseif ($type === 'email')
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Adresse e-mail</label><input wire:model="data.email" type="email" placeholder="contact@example.com" class="field">@error('data.email')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Sujet</label><input wire:model="data.subject" class="field">@error('data.subject')<p class="error">{{ $message }}</p>@enderror</div></div>
                        <div><label class="label">Message</label><textarea wire:model="data.message" rows="4" class="field"></textarea>@error('data.message')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'phone')
                        <div><label class="label">Numéro de téléphone</label><input wire:model="data.phone" placeholder="+243..." class="field">@error('data.phone')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'wifi')
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Nom du réseau (SSID)</label><input wire:model="data.ssid" class="field">@error('data.ssid')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Sécurité</label><select wire:model="data.security" class="field"><option value="">Choisir</option><option value="WPA">WPA/WPA2</option><option value="WEP">WEP</option><option value="nopass">Aucune</option></select>@error('data.security')<p class="error">{{ $message }}</p>@enderror</div></div>
                        <div><label class="label">Mot de passe</label><input wire:model="data.password" type="password" class="field">@error('data.password')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'sms')
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Numéro</label><input wire:model="data.phone" placeholder="+243..." class="field">@error('data.phone')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Message</label><input wire:model="data.message" class="field">@error('data.message')<p class="error">{{ $message }}</p>@enderror</div></div>
                    @elseif ($type === 'vcard')
                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach (['first_name' => 'Prénom', 'last_name' => 'Nom', 'phone' => 'Téléphone', 'email' => 'E-mail', 'company' => 'Entreprise', 'job_title' => 'Fonction', 'website' => 'Site web', 'address' => 'Adresse'] as $key => $label)
                                <div><label class="label">{{ $label }}</label><input wire:model="data.{{ $key }}" class="field">@error('data.'.$key)<p class="error">{{ $message }}</p>@enderror</div>
                            @endforeach
                        </div>
                    @elseif ($type === 'location')
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Latitude</label><input wire:model="data.latitude" type="number" step="any" class="field">@error('data.latitude')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Longitude</label><input wire:model="data.longitude" type="number" step="any" class="field">@error('data.longitude')<p class="error">{{ $message }}</p>@enderror</div></div>
                    @elseif ($type === 'event')
                        <div><label class="label">Titre de l’événement</label><input wire:model="data.title" class="field">@error('data.title')<p class="error">{{ $message }}</p>@enderror</div>
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Début</label><input wire:model="data.starts_at" type="datetime-local" class="field">@error('data.starts_at')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Fin</label><input wire:model="data.ends_at" type="datetime-local" class="field">@error('data.ends_at')<p class="error">{{ $message }}</p>@enderror</div></div>
                        <div><label class="label">Lieu</label><input wire:model="data.location" class="field">@error('data.location')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Description</label><textarea wire:model="data.description" rows="3" class="field"></textarea>@error('data.description')<p class="error">{{ $message }}</p>@enderror</div>
                    @elseif ($type === 'progressive')
                        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4 text-sm leading-6 text-indigo-800">Ce QR Code conserve toujours la même adresse publique. Vous pourrez compléter ou modifier cette fiche plus tard sans réimprimer le QR.</div>
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">Nom affiché</label><input wire:model="data.display_name" placeholder="Nom ou organisation" class="field">@error('data.display_name')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Téléphone initial</label><input wire:model="data.phone" placeholder="+243..." class="field">@error('data.phone')<p class="error">{{ $message }}</p>@enderror</div></div>
                        <div class="grid gap-5 sm:grid-cols-2"><div><label class="label">E-mail optionnel</label><input wire:model="data.email" type="email" class="field">@error('data.email')<p class="error">{{ $message }}</p>@enderror</div><div><label class="label">Site web optionnel</label><input wire:model="data.website" type="url" placeholder="https://..." class="field">@error('data.website')<p class="error">{{ $message }}</p>@enderror</div></div>
                        <div><label class="label">Adresse optionnelle</label><input wire:model="data.address" class="field">@error('data.address')<p class="error">{{ $message }}</p>@enderror</div>
                        <div><label class="label">Description optionnelle</label><textarea wire:model="data.description" rows="4" class="field"></textarea>@error('data.description')<p class="error">{{ $message }}</p>@enderror</div>
                        <div><label class="label">Photo publique optionnelle</label><input wire:model="profilePhoto" type="file" accept=".png,.jpg,.jpeg,.webp" class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:font-bold file:text-white">@error('profilePhoto')<p class="error">{{ $message }}</p>@enderror</div>
                    @endif

                    <div class="border-t border-slate-200 pt-6">
                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Étape 2</p><h2 class="mt-1 text-xl font-black">Personnalisation</h2>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <div><label class="label">Couleur principale</label><input wire:model.live="foregroundColor" type="color" class="mt-2 h-12 w-full cursor-pointer rounded-xl border border-slate-300 bg-white p-1"></div>
                            <div><label class="label">Couleur de fond</label><input wire:model.live="backgroundColor" type="color" class="mt-2 h-12 w-full cursor-pointer rounded-xl border border-slate-300 bg-white p-1"></div>
                            <div><label class="label">Taille: {{ $size }} px</label><input wire:model.live.debounce.300ms="size" type="range" min="180" max="1200" step="20" class="mt-4 w-full accent-indigo-600"></div>
                            <div><label class="label">Marge: {{ $margin }} px</label><input wire:model.live.debounce.300ms="margin" type="range" min="0" max="50" class="mt-4 w-full accent-indigo-600"></div>
                            <div><label class="label">Correction d’erreur</label><select wire:model.live="errorCorrection" class="field"><option value="low">Faible</option><option value="medium">Moyenne</option><option value="quartile">Quartile</option><option value="high">Élevée</option></select></div>
                            <div><label class="label">Format initial</label><select wire:model="format" class="field"><option value="png">PNG</option><option value="svg">SVG</option><option value="pdf">PDF</option></select></div>
                        </div>
                        <div class="mt-5"><label class="label">Logo central optionnel</label><input wire:model="logo" type="file" accept=".png,.jpg,.jpeg,.webp" class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:font-bold file:text-white">@error('logo')<p class="error">{{ $message }}</p>@enderror</div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 sm:flex-row sm:justify-end sm:p-8">
                    <button type="button" wire:click="preview" wire:loading.attr="disabled" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50">Actualiser l’aperçu</button>
                    <button type="submit" wire:loading.attr="disabled" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700 disabled:opacity-50"><span wire:loading.remove wire:target="generate">{{ $qrCode ? 'Enregistrer les modifications' : 'Générer et sauvegarder' }}</span><span wire:loading wire:target="generate">{{ $qrCode ? 'Mise à jour...' : 'Génération...' }}</span></button>
                </div>
            </form>

            <aside class="top-24 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 lg:sticky">
                <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">Aperçu</p><h2 class="mt-1 text-xl font-black">Votre QR Code</h2></div><span wire:loading class="size-5 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent"></span></div>
                <div class="mt-6 grid aspect-square place-items-center overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    @if ($previewSvg)<div class="w-full">{!! $previewSvg !!}</div>@else<p class="text-center text-sm text-slate-500">Complétez le formulaire pour afficher l’aperçu.</p>@endif
                </div>
                <p class="mt-4 text-center text-xs leading-5 text-slate-500">Testez toujours le QR Code avant impression.</p>
                @if ($generatedId)
                    <div class="mt-5 grid grid-cols-3 gap-2">@foreach (['png' => 'PNG', 'svg' => 'SVG', 'pdf' => 'PDF'] as $value => $label)<a href="{{ route('qr-code.download', ['qrCode' => $generatedId, 'format' => $value]) }}" class="rounded-xl bg-slate-950 px-3 py-3 text-center text-xs font-bold text-white transition hover:bg-indigo-600">{{ $label }}</a>@endforeach</div>
                    <a href="{{ route('qr-code.show', $generatedId) }}" class="mt-3 block text-center text-sm font-bold text-indigo-600 hover:text-indigo-800">Voir la fiche détaillée →</a>
                @endif
            </aside>
        </div>
    </section>
</div>
