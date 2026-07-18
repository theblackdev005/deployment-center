<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Gestion centralisée</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Domaines Hostinger</h1>
                <p class="mt-1 text-sm text-slate-500">État, expiration et déploiements de tous vos domaines.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($accounts->isNotEmpty())
                    <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Actualisation...';">
                        @csrf
                        <button class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:border-emerald-300 hover:text-emerald-700 disabled:opacity-60">Tout actualiser</button>
                    </form>
                @endif
                <a href="{{ route('hostinger.accounts.index') }}" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Gérer les comptes</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        @if ($accounts->isEmpty())
            <div class="rounded-md border border-slate-200 bg-white px-5 py-12 text-center shadow-sm">
                <p class="text-base font-semibold text-slate-900">Connectez votre premier compte Hostinger</p>
                <p class="mt-2 text-sm text-slate-500">La synchronisation est en lecture seule et ne modifie aucun service.</p>
                <a href="{{ route('hostinger.accounts.index') }}" class="mt-5 inline-flex rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Ajouter un compte</a>
            </div>
        @else
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-emerald-700">Total domaines</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-950">{{ $domainCount }}</p>
                    <p class="mt-1 text-xs text-emerald-800">Sur {{ $accounts->count() }} {{ $accounts->count() > 1 ? 'comptes Hostinger' : 'compte Hostinger' }}</p>
                </div>
                <div class="rounded-md border {{ $expiringDomains->isNotEmpty() ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white' }} p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase {{ $expiringDomains->isNotEmpty() ? 'text-amber-700' : 'text-slate-500' }}">Expiration proche</p>
                    <p class="mt-2 text-3xl font-bold {{ $expiringDomains->isNotEmpty() ? 'text-amber-950' : 'text-slate-950' }}">{{ $expiringDomains->count() }}</p>
                    <p class="mt-1 text-xs {{ $expiringDomains->isNotEmpty() ? 'text-amber-800' : 'text-slate-500' }}">Dans les 30 prochains jours</p>
                </div>
                <div class="rounded-md border {{ $alerts->isNotEmpty() ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-white' }} p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase {{ $alerts->isNotEmpty() ? 'text-red-700' : 'text-emerald-700' }}">Problèmes détectés</p>
                    <p class="mt-2 text-3xl font-bold {{ $alerts->isNotEmpty() ? 'text-red-950' : 'text-emerald-950' }}">{{ $alerts->count() }}</p>
                    <p class="mt-1 text-xs {{ $alerts->isNotEmpty() ? 'text-red-800' : 'text-emerald-700' }}">{{ $alerts->isNotEmpty() ? 'Une vérification est nécessaire' : 'Aucune anomalie signalée' }}</p>
                </div>
            </div>

            @if ($alerts->isNotEmpty())
                <section class="mt-5 overflow-hidden rounded-md border border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 px-4 py-3">
                        <h2 class="text-sm font-semibold text-red-950">Alertes administrateur</h2>
                    </div>
                    <div class="divide-y divide-red-100">
                        @foreach ($alerts as $alert)
                            <div class="border-l-4 {{ $alert->severity === 'critical' ? 'border-red-500' : 'border-amber-400' }} px-4 py-3">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-950">{{ $alert->title }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $alert->message }}</p>
                                    </div>
                                    <p class="shrink-0 text-xs text-slate-500">{{ $alert->account->name }} · {{ $alert->last_detected_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-6" x-data="{
                search: '',
                account: 'all',
                visible(domain, accountId) {
                    const matchesText = domain.toLowerCase().includes(this.search.toLowerCase().trim());
                    const matchesAccount = this.account === 'all' || this.account === String(accountId);
                    return matchesText && matchesAccount;
                }
            }">
                <div class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_240px]">
                    <div>
                        <label for="domain_search" class="text-xs font-semibold uppercase text-slate-500">Rechercher</label>
                        <input id="domain_search" x-model.debounce.200ms="search" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Commencez à écrire un nom de domaine">
                    </div>
                    <div>
                        <label for="account_filter" class="text-xs font-semibold uppercase text-slate-500">Compte Hostinger</label>
                        <select id="account_filter" x-model="account" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="all">Tous les comptes</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
                    <div class="hidden grid-cols-[minmax(200px,1.4fr)_minmax(140px,1fr)_130px_130px_150px_110px] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase text-slate-500 lg:grid">
                        <span>Domaine</span>
                        <span>Compte</span>
                        <span>Enregistré le</span>
                        <span>Expire le</span>
                        <span>Dernier déploiement</span>
                        <span>État</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($domains as $row)
                            @php
                                $registration = $row['registration'];
                                $website = $row['website'];
                                $deployment = $row['deployment'];
                                $status = strtolower((string) ($registration?->status ?? ''));
                                $hasProblem = in_array($status, ['expired', 'suspended', 'failed'], true) || ($website && ! $website->is_enabled);
                                $expiresSoon = $registration?->expires_at?->isBetween(now(), now()->addDays(30));
                            @endphp
                            <article
                                x-show="visible(@js($row['domain']), @js($row['account']->id))"
                                x-transition.opacity.duration.150ms
                                class="relative grid gap-3 px-4 py-4 hover:bg-slate-50 lg:grid-cols-[minmax(200px,1.4fr)_minmax(140px,1fr)_130px_130px_150px_110px] lg:items-center lg:gap-4"
                            >
                                <div class="min-w-0">
                                    <p class="break-all text-sm font-semibold text-slate-950">{{ $row['domain'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $website?->username ? 'Hébergement '.$website->username : 'Domaine non hébergé sur ce compte' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase text-slate-400 lg:hidden">Compte</p>
                                    <p class="mt-1 text-sm text-slate-700 lg:mt-0">{{ $row['account']->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase text-slate-400 lg:hidden">Enregistré le</p>
                                    <p class="mt-1 text-sm text-slate-700 lg:mt-0">{{ $registration?->registered_at?->format('d/m/Y') ?? $website?->remote_created_at?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase text-slate-400 lg:hidden">Expire le</p>
                                    <p class="mt-1 text-sm {{ $expiresSoon || $status === 'expired' ? 'font-semibold text-amber-700' : 'text-slate-700' }} lg:mt-0">{{ $registration?->expires_at?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase text-slate-400 lg:hidden">Dernier déploiement</p>
                                    @if ($deployment)
                                        <a href="{{ route('deployments.show', $deployment) }}" class="mt-1 block text-sm font-medium text-emerald-700 hover:text-emerald-800 lg:mt-0">{{ $deployment->created_at->format('d/m/Y H:i') }}</a>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $deployment->project->name }}</p>
                                    @else
                                        <p class="mt-1 text-sm text-slate-500 lg:mt-0">Aucun</p>
                                    @endif
                                </div>
                                <div>
                                    @if ($hasProblem)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">À vérifier</span>
                                    @elseif ($expiresSoon)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">À renouveler</span>
                                    @elseif ($website?->is_enabled)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Actif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Enregistré</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach

                        @if ($domains->isEmpty())
                            <div class="px-5 py-10 text-center text-sm text-slate-500">Aucun domaine synchronisé.</div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
