<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Connexions</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Comptes Hostinger</h1>
                <p class="mt-1 text-sm text-slate-500">Un jeton par compte pour regrouper vos domaines en lecture seule.</p>
            </div>
            <a href="{{ route('hostinger.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Voir tous les domaines</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Ajouter un compte</h2>
                <p class="mt-1 text-sm text-slate-500">Créez le jeton depuis hPanel, dans Compte puis API.</p>
            </div>
            <form method="POST" action="{{ route('hostinger.accounts.store') }}" class="p-5" x-data="{ showToken: false, submitting: false }" @submit="submitting = true">
                @csrf
                <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
                    <div>
                        <label for="name" class="text-sm font-medium text-slate-700">Nom du compte</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Compte principal">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="api_token" class="text-sm font-medium text-slate-700">Jeton API Hostinger</label>
                        <div class="relative mt-1">
                            <input id="api_token" name="api_token" :type="showToken ? 'text' : 'password'" required autocomplete="new-password" class="block w-full rounded-md border-slate-300 pr-20 font-mono text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Collez le jeton généré dans hPanel">
                            <button type="button" @click="showToken = !showToken" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showToken ? 'Masquer' : 'Afficher'"></button>
                        </div>
                        <x-input-error :messages="$errors->get('api_token')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Le jeton est chiffré et ne sera jamais réaffiché.</p>
                    <button :disabled="submitting" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                        <span x-show="!submitting">Ajouter et synchroniser</span>
                        <span x-show="submitting" x-cloak>Connexion en cours...</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-7">
            <div class="flex items-end justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Comptes enregistrés</h2>
                    <p class="mt-1 text-sm text-slate-500">Chaque compte reste indépendant.</p>
                </div>
                @if ($accounts->isNotEmpty())
                    <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}">
                        @csrf
                        <button class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Tout actualiser</button>
                    </form>
                @endif
            </div>

            <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white">
                @forelse ($accounts as $account)
                    @php
                        $state = match ($account->status) {
                            'connected' => ['label' => 'Connecté', 'class' => 'bg-emerald-50 text-emerald-700'],
                            'error' => ['label' => 'À vérifier', 'class' => 'bg-red-50 text-red-700'],
                            'syncing' => ['label' => 'Synchronisation', 'class' => 'bg-blue-50 text-blue-700'],
                            default => ['label' => 'Non synchronisé', 'class' => 'bg-amber-50 text-amber-700'],
                        };
                    @endphp
                    <div class="border-b border-slate-100 p-4 last:border-0 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-950">{{ $account->name }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $state['class'] }}">{{ $state['label'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $account->domains_count }} domaines · {{ $account->websites_count }} sites détectés</p>
                                @if ($account->open_alerts_count > 0)
                                    <p class="mt-1 text-xs font-semibold text-red-700">{{ $account->open_alerts_count }} {{ $account->open_alerts_count > 1 ? 'problèmes détectés' : 'problème détecté' }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-500">{{ $account->last_synced_at ? 'Actualisé le '.$account->last_synced_at->format('d/m/Y à H:i') : 'Jamais actualisé' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('hostinger.accounts.sync', $account) }}">
                                    @csrf
                                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Actualiser</button>
                                </form>
                                <form method="POST" action="{{ route('hostinger.accounts.destroy', $account) }}" onsubmit="return confirm('Supprimer ce compte Hostinger de la plateforme ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-md px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        @if ($account->sync_error)
                            <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ $account->sync_error }}</p>
                        @endif

                        <details class="mt-3 border-t border-slate-100 pt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-slate-600">Modifier le nom ou remplacer le jeton</summary>
                            <form method="POST" action="{{ route('hostinger.accounts.update', $account) }}" class="mt-3 grid gap-3 sm:grid-cols-[1fr_2fr_auto] sm:items-end">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="account_name_{{ $account->id }}" class="text-xs font-medium text-slate-600">Nom</label>
                                    <input id="account_name_{{ $account->id }}" name="name" value="{{ $account->name }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="account_token_{{ $account->id }}" class="text-xs font-medium text-slate-600">Nouveau jeton, facultatif</label>
                                    <input id="account_token_{{ $account->id }}" name="api_token" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-slate-300 font-mono text-sm">
                                </div>
                                <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Enregistrer</button>
                            </form>
                        </details>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-700">Aucun compte Hostinger</p>
                        <p class="mt-1 text-sm text-slate-500">Ajoutez votre premier jeton pour commencer la synchronisation.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
