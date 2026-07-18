<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Comptes d’hébergement</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Comptes Hostinger</h1>
                <p class="mt-1 text-sm text-slate-500">Connectez chaque espace Hostinger pour réunir vos sites et leurs échéances.</p>
            </div>
            <a href="{{ route('hostinger.index') }}" class="ui-button-secondary">Voir le parc de domaines</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
        <section class="ui-panel overflow-hidden">
            <div class="ui-panel-header">
                <h2 class="text-base font-bold text-slate-950">Connecter un compte</h2>
                <p class="mt-1 text-sm text-slate-500">Utilisez le jeton API créé depuis la rubrique API de hPanel.</p>
            </div>
            <form method="POST" action="{{ route('hostinger.accounts.store') }}" class="p-5" x-data="{ showToken: false, submitting: false }" @submit="submitting = true">
                @csrf
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="name" class="ui-label">Nom du compte</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="ui-input" placeholder="Compte principal">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="email" class="ui-label">Email du compte</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="ui-input" placeholder="compte@exemple.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label for="api_token" class="ui-label">Jeton API Hostinger</label>
                        <div class="relative mt-1">
                            <input id="api_token" name="api_token" :type="showToken ? 'text' : 'password'" required autocomplete="new-password" class="ui-input mt-0 pr-20 font-mono" placeholder="Coller le jeton généré dans hPanel">
                            <button type="button" @click="showToken = !showToken" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showToken ? 'Masquer' : 'Afficher'"></button>
                        </div>
                        <x-input-error :messages="$errors->get('api_token')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Le jeton est conservé sous forme chiffrée.</p>
                    <button :disabled="submitting" class="ui-button-primary">
                        <span x-show="!submitting">Connecter le compte</span>
                        <span x-show="submitting" x-cloak>Connexion en cours...</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-7">
            <div class="flex items-end justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Comptes connectés</h2>
                    <p class="mt-1 text-sm text-slate-500">Chaque compte conserve ses propres sites et échéances.</p>
                </div>
                @if ($accounts->where('is_active', true)->isNotEmpty())
                    <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}">
                        @csrf
                        <button class="ui-button-secondary"><i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>Tout actualiser</button>
                    </form>
                @endif
            </div>

            <div class="mt-3 space-y-3">
                @forelse ($accounts as $account)
                    @php
                        $state = ! $account->is_active
                            ? ['label' => 'En pause', 'class' => 'bg-slate-100 text-slate-700']
                            : match ($account->status) {
                                'connected' => ['label' => 'Connecté', 'class' => 'bg-emerald-50 text-emerald-700'],
                                'error' => ['label' => 'À vérifier', 'class' => 'bg-red-50 text-red-700'],
                                'syncing' => ['label' => 'Synchronisation', 'class' => 'bg-blue-50 text-blue-700'],
                                default => ['label' => 'Non synchronisé', 'class' => 'bg-amber-50 text-amber-700'],
                            };
                    @endphp
                    <article class="ui-panel p-5 {{ $account->is_active ? '' : 'bg-slate-50/70' }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]"><i data-lucide="server" class="h-5 w-5" aria-hidden="true"></i></span>
                                <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-base font-bold text-slate-950">{{ $account->name }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $state['class'] }}">{{ $state['label'] }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm text-slate-600">{{ $account->email ?: 'Email non renseigné' }}</p>
                                @if ($account->is_active)
                                    <p class="mt-1 text-xs text-slate-500">{{ $account->domains_count }} domaines · {{ $account->websites_count }} sites détectés</p>
                                    @if ($account->hostingPlans->first()?->expires_at)
                                        <p class="mt-1.5 text-sm font-semibold text-slate-700">Prochaine échéance : <span class="text-amber-700">{{ $account->hostingPlans->first()->expires_at->format('d/m/Y') }}</span></p>
                                    @endif
                                    @if ($account->open_alerts_count > 0)
                                        <p class="mt-1 text-xs font-semibold text-red-700">{{ $account->open_alerts_count }} {{ $account->open_alerts_count > 1 ? 'problèmes détectés' : 'problème détecté' }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-500">{{ $account->last_synced_at ? 'Actualisé le '.$account->last_synced_at->format('d/m/Y à H:i') : 'Jamais actualisé' }}</p>
                                @else
                                    <p class="mt-1.5 text-sm font-medium text-slate-600">Informations masquées jusqu’à la réactivation du compte.</p>
                                @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($account->is_active)
                                    <form method="POST" action="{{ route('hostinger.accounts.sync', $account) }}">
                                        @csrf
                                        <button class="ui-button-secondary"><i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>Actualiser</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('hostinger.accounts.status', $account) }}" @if ($account->is_active) onsubmit="return confirm('Mettre ce compte Hostinger en pause ? Les synchronisations et alertes seront suspendues.')" @endif>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}">
                                    <button class="ui-button-secondary">
                                        <i data-lucide="{{ $account->is_active ? 'pause' : 'play' }}" class="h-4 w-4" aria-hidden="true"></i>
                                        {{ $account->is_active ? 'Mettre en pause' : 'Réactiver' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('hostinger.accounts.destroy', $account) }}" onsubmit="return confirm('Supprimer ce compte Hostinger de la plateforme ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ui-button-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>

                        @if ($account->is_active && $account->sync_error)
                            <p class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ $account->sync_error }}</p>
                        @endif

                        @if ($account->is_active)
                        <details class="mt-3 border-t border-slate-100 pt-3">
                            <summary class="cursor-pointer text-sm font-semibold text-[#673de6]">Modifier les informations du compte</summary>
                            <form method="POST" action="{{ route('hostinger.accounts.update', $account) }}" class="mt-3 grid gap-3 md:grid-cols-[1fr_1.3fr_1.7fr_auto] md:items-end">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="account_name_{{ $account->id }}" class="text-xs font-medium text-slate-600">Nom</label>
                                    <input id="account_name_{{ $account->id }}" name="name" value="{{ $account->name }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="account_email_{{ $account->id }}" class="text-xs font-medium text-slate-600">Email du compte</label>
                                    <input id="account_email_{{ $account->id }}" name="email" type="email" value="{{ $account->email }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label for="account_token_{{ $account->id }}" class="text-xs font-medium text-slate-600">Nouveau jeton, facultatif</label>
                                    <input id="account_token_{{ $account->id }}" name="api_token" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-slate-300 font-mono text-sm">
                                </div>
                                <button class="ui-button-secondary">Enregistrer</button>
                            </form>
                        </details>
                        @endif
                    </article>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-semibold text-slate-700">Aucun compte Hostinger connecté</p>
                        <p class="mt-1 text-sm text-slate-500">Connectez un compte pour afficher ses domaines et échéances.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
