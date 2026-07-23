<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-emerald-700">Espace Hostinger</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Gestion des domaines</h1>
                <p class="mt-1 text-sm text-slate-500">Tous vos comptes, domaines et alertes au même endroit.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($accounts->where('is_active', true)->isNotEmpty())
                    <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('[data-label]').textContent = 'Actualisation...';">
                        @csrf
                        <button class="inline-flex min-h-10 items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-400 hover:text-emerald-700 disabled:cursor-wait disabled:opacity-60">
                            <i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>
                            <span data-label>Actualiser</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        @if ($accounts->isEmpty())
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-l-4 border-emerald-500 px-6 py-10 text-center sm:px-10">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-emerald-100 text-lg font-bold text-emerald-700">H</span>
                    <h2 class="mt-4 text-lg font-bold text-slate-950">Connectez votre premier compte Hostinger</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">La synchronisation récupère vos domaines et leurs états sans modifier vos services.</p>
                    <a href="{{ route('hostinger.accounts.index') }}" class="mt-5 inline-flex rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Ajouter un compte</a>
                </div>
            </div>
        @else
            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="grid lg:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="flex gap-4 px-5 py-5 sm:px-6 sm:py-6">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]">
                            <i data-lucide="activity" class="h-5 w-5" aria-hidden="true"></i>
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-950">Votre espace d’hébergement</h2>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Synchronisé
                                </span>
                            </div>
                            <p class="mt-1.5 max-w-2xl text-sm leading-6 text-slate-600">
                                {{ $accounts->where('is_active', true)->count() }} {{ $accounts->where('is_active', true)->count() > 1 ? 'comptes Hostinger sont actifs' : 'compte Hostinger est actif' }}.
                                @if ($accounts->where('is_active', false)->isNotEmpty())
                                    {{ $accounts->where('is_active', false)->count() }} en pause.
                                @endif
                                Vos domaines, échéances et incidents sont regroupés ici.
                            </p>
                            <p class="mt-3 text-xs font-medium text-slate-400">Dernière synchronisation générale : {{ $lastSyncedAt?->format('d/m/Y à H:i') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 lg:justify-end lg:border-l lg:border-t-0">
                        <a href="{{ route('deployments.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-[#673de6] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#5530c9]">
                            <i data-lucide="rocket" class="h-4 w-4" aria-hidden="true"></i>
                            Déployer un site
                        </a>
                        <a href="{{ route('hostinger.accounts.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[#8c6cf0] hover:text-[#673de6]">
                            Comptes
                            <i data-lucide="chevron-right" class="h-4 w-4" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </section>

            <section
                class="ui-panel mt-5 overflow-hidden"
                x-data="{ selectedAccount: '{{ $accounts->where('is_active', true)->first()?->id }}' }"
            >
                <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Informations du compte</h2>
                        <p class="mt-1 text-sm text-slate-500">Choisissez le compte Hostinger que vous souhaitez consulter.</p>
                    </div>
                    <div class="flex w-full items-center gap-2 sm:w-auto">
                        <label for="summary_account" class="sr-only">Compte Hostinger</label>
                        <select id="summary_account" x-model="selectedAccount" class="ui-input mt-0 min-w-0 sm:w-64">
                            @foreach ($accounts->where('is_active', true) as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}{{ $account->email ? ' — '.$account->email : '' }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary shrink-0" title="Gérer les comptes">
                            <i data-lucide="settings-2" class="h-4 w-4" aria-hidden="true"></i>
                            <span class="hidden sm:inline">Gérer</span>
                        </a>
                    </div>
                </div>

                @foreach ($accounts->where('is_active', true) as $account)
                    @php
                        $nextPlan = $account->hostingPlans->first(fn ($plan) => $plan->expires_at?->isFuture());
                    @endphp
                    <div x-show="selectedAccount === '{{ $account->id }}'" x-transition.opacity.duration.150ms class="grid gap-5 px-5 py-5 sm:grid-cols-2 lg:grid-cols-[minmax(220px,1.15fr)_repeat(3,minmax(150px,.75fr))] lg:items-center">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]"><i data-lucide="server" class="h-5 w-5" aria-hidden="true"></i></span>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-950">{{ $account->name }}</h3>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $account->email ?: 'Email non renseigné' }}</p>
                                @if ($account->open_alerts_count > 0)
                                    <p class="mt-1 text-xs font-bold text-red-700">{{ $account->open_alerts_count }} {{ $account->open_alerts_count > 1 ? 'éléments à vérifier' : 'élément à vérifier' }}</p>
                                @else
                                    <p class="mt-1 text-xs font-bold text-emerald-700">Services opérationnels</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400">Domaines et sites</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $account->domains_count }} domaines · {{ $account->websites_count }} sites</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400">Échéance hébergement</p>
                            <p class="mt-1 text-sm font-bold {{ $nextPlan && $nextPlan->expires_at->lte(now()->addMonthsNoOverflow($expirationNoticeMonths)) ? 'text-amber-700' : 'text-slate-800' }}">{{ $nextPlan?->expires_at?->format('d/m/Y') ?? 'Non disponible' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase text-slate-400">Dernière synchronisation</p>
                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $account->last_synced_at?->format('d/m/Y à H:i') ?? 'Jamais' }}</p>
                        </div>
                    </div>
                @endforeach
            </section>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white px-5 py-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Domaines suivis</p>
                            <p class="mt-1.5 text-3xl font-bold text-slate-950">{{ $domainCount }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]">
                            <i data-lucide="globe-2" class="h-5 w-5" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500"><strong class="font-semibold text-slate-700">{{ $subdomainCount }}</strong> sous-domaines masqués par défaut</p>
                </div>
                <div class="rounded-lg border {{ $expiringDomains->isNotEmpty() ? 'border-amber-200' : 'border-slate-200' }} bg-white px-5 py-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Expiration proche</p>
                            <p class="mt-1.5 text-3xl font-bold {{ $expiringDomains->isNotEmpty() ? 'text-amber-700' : 'text-slate-950' }}">{{ $expiringDomains->count() }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                            <i data-lucide="calendar-clock" class="h-5 w-5" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Signalement dans les deux derniers mois</p>
                </div>
                <div class="rounded-lg border {{ $openAlertCount > 0 ? 'border-red-200' : 'border-slate-200' }} bg-white px-5 py-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">État des services</p>
                            <p class="mt-1.5 text-2xl font-bold {{ $openAlertCount > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ $openAlertCount > 0 ? $openAlertCount.' non résolu'.($openAlertCount > 1 ? 's' : '') : 'Opérationnel' }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-md {{ $openAlertCount > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                            <i data-lucide="{{ $openAlertCount > 0 ? 'alert-triangle' : 'check-circle-2' }}" class="h-5 w-5" aria-hidden="true"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">{{ $openAlertCount > 0 ? 'Ces problèmes restent suivis jusqu’à leur résolution' : 'Aucune anomalie active' }}</p>
                </div>
            </div>

            @if ($alerts->isNotEmpty())
                <section class="mt-5 overflow-hidden rounded-lg border border-red-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-red-100 bg-red-50 px-5 py-3.5">
                        <div>
                            <h2 class="text-base font-bold text-red-950">Tâches prioritaires</h2>
                            <p class="mt-0.5 text-xs text-red-700">Ces événements nécessitent une vérification.</p>
                        </div>
                        <span class="rounded-full bg-red-600 px-2.5 py-1 text-xs font-bold text-white">{{ $alerts->count() }}</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($alerts as $alert)
                            <div class="flex gap-3 px-5 py-4">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $alert->severity === 'critical' ? 'bg-red-500' : 'bg-amber-400' }}"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                        <p class="text-sm font-bold text-slate-950">{{ $alert->title }}</p>
                                        <p class="shrink-0 text-xs text-slate-500">{{ $alert->account->name }} · {{ $alert->last_detected_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $alert->message }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-6" x-data="{
                search: '',
                account: 'all',
                showSubdomains: false,
                visible(domain, accountId, isSubdomain) {
                    const matchesText = domain.toLowerCase().includes(this.search.toLowerCase().trim());
                    const matchesAccount = this.account === 'all' || this.account === String(accountId);
                    const matchesType = this.showSubdomains || !isSubdomain;
                    return matchesText && matchesAccount && matchesType;
                }
            }">
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Annuaire des domaines</h2>
                            <p class="mt-1 text-sm text-slate-500">Recherchez un domaine et consultez son état.</p>
                        </div>
                        @if ($subdomainCount > 0)
                            <button
                                type="button"
                                @click="showSubdomains = !showSubdomains"
                                :aria-pressed="showSubdomains"
                                class="mt-3 inline-flex min-h-9 items-center gap-2 rounded-md border px-3 py-1.5 text-sm font-semibold transition sm:mt-0"
                                :class="showSubdomains ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-400'"
                            >
                                <i data-lucide="eye" class="h-4 w-4" aria-hidden="true"></i>
                                <span class="relative h-4 w-7 rounded-full transition" :class="showSubdomains ? 'bg-[#673de6]' : 'bg-slate-300'">
                                    <span class="absolute top-0.5 h-3 w-3 rounded-full bg-white shadow-sm transition" :class="showSubdomains ? 'left-3.5' : 'left-0.5'"></span>
                                </span>
                                <span x-text="showSubdomains ? 'Masquer les sous-domaines' : 'Afficher les sous-domaines ({{ $subdomainCount }})'"></span>
                            </button>
                        @endif
                    </div>

                    <div class="grid gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_240px]">
                        <div class="relative">
                            <label for="domain_search" class="sr-only">Rechercher un domaine</label>
                            <i data-lucide="search" class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" aria-hidden="true"></i>
                            <input id="domain_search" type="search" x-model.debounce.150ms="search" class="block min-h-11 w-full rounded-md border-slate-300 bg-white pl-10 text-sm shadow-sm focus:border-[#8062e8] focus:ring-[#8062e8]" placeholder="Rechercher un domaine...">
                        </div>
                        <div>
                            <label for="account_filter" class="sr-only">Compte Hostinger</label>
                            <select id="account_filter" x-model="account" class="block min-h-11 w-full rounded-md border-slate-300 bg-white text-sm shadow-sm focus:border-[#8062e8] focus:ring-[#8062e8]">
                                <option value="all">Tous les comptes Hostinger</option>
                                @foreach ($accounts->where('is_active', true) as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}{{ $account->email ? ' — '.$account->email : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="hidden grid-cols-[minmax(280px,1.35fr)_minmax(200px,.8fr)_minmax(210px,.8fr)_130px] gap-6 border-b border-slate-200 bg-white px-5 py-3 text-xs font-bold uppercase text-slate-500 lg:grid">
                        <span>Site et hébergement</span>
                        <span>Cycle du domaine</span>
                        <span>Dernière publication</span>
                        <span>État</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($domains as $row)
                            @php
                                $registration = $row['registration'];
                                $website = $row['website'];
                                $deployment = $row['deployment'];
                                $status = strtolower((string) ($registration?->status ?? ''));
                                $isExpired = $status === 'expired' || $registration?->expires_at?->isPast();
                                $isSuspended = $status === 'suspended';
                                $hasFailed = $status === 'failed';
                                $websiteDisabled = $website && ! $website->is_enabled;
                                $hasProblem = $isExpired || $isSuspended || $hasFailed || $websiteDisabled;
                                $expiresSoon = $registration?->expires_at?->isBetween(now(), now()->addMonthsNoOverflow($expirationNoticeMonths));
                                $accountPaused = ! $row['account']->is_active;

                                $resourceStatus = match (true) {
                                    $accountPaused => ['label' => 'En pause', 'class' => 'bg-slate-100 text-slate-700', 'icon' => 'pause'],
                                    $isExpired => ['label' => 'Expiré', 'class' => 'bg-red-100 text-red-800', 'icon' => null],
                                    $isSuspended => ['label' => 'Suspendu', 'class' => 'bg-red-100 text-red-800', 'icon' => null],
                                    $hasFailed => ['label' => 'Erreur Hostinger', 'class' => 'bg-red-100 text-red-800', 'icon' => null],
                                    $websiteDisabled => ['label' => 'Site désactivé', 'class' => 'bg-red-100 text-red-800', 'icon' => null],
                                    $expiresSoon => ['label' => 'À renouveler', 'class' => 'bg-amber-100 text-amber-800', 'icon' => null],
                                    $status === 'pending' => ['label' => 'En attente', 'class' => 'bg-amber-100 text-amber-800', 'icon' => null],
                                    $website?->is_enabled => ['label' => 'Actif', 'class' => 'bg-emerald-100 text-emerald-800', 'icon' => null],
                                    $status === 'active' => ['label' => 'Domaine actif', 'class' => 'bg-emerald-100 text-emerald-800', 'icon' => null],
                                    default => ['label' => 'Enregistré', 'class' => 'bg-slate-100 text-slate-700', 'icon' => null],
                                };
                            @endphp
                            <article
                                x-show="visible(@js($row['domain']), @js($row['account']->id), @js($row['is_subdomain']))"
                                x-transition.opacity.duration.150ms
                                class="relative grid gap-5 px-5 py-5 transition hover:bg-slate-50 lg:grid-cols-[minmax(280px,1.35fr)_minmax(200px,.8fr)_minmax(210px,.8fr)_130px] lg:items-center lg:gap-6 {{ $accountPaused ? 'bg-slate-50/60' : '' }}"
                            >
                                <div class="flex min-w-0 items-start gap-3.5">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md {{ $accountPaused ? 'bg-slate-200 text-slate-500' : 'bg-slate-950 text-white' }}">
                                        <i data-lucide="globe-2" class="h-5 w-5" aria-hidden="true"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="h-2 w-2 shrink-0 rounded-full {{ $accountPaused ? 'bg-slate-400' : ($hasProblem ? 'bg-red-500' : ($expiresSoon ? 'bg-amber-400' : 'bg-emerald-500')) }}"></span>
                                            <p class="break-all text-sm font-bold text-slate-950">{{ $row['domain'] }}</p>
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">{{ $row['account']->name }}</span>
                                            @if ($row['is_subdomain'])
                                                <span class="rounded-md bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">Sous-domaine</span>
                                            @endif
                                        </div>
                                        <p class="mt-1.5 text-xs text-slate-500">{{ $website?->username ? 'Hébergement '.$website->username : 'Domaine sans site associé' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 lg:block">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase text-slate-400">Ajouté</p>
                                        <p class="mt-1 text-sm font-medium text-slate-700">{{ $registration?->registered_at?->format('d/m/Y') ?? $website?->remote_created_at?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                    <div class="lg:mt-2.5">
                                        <p class="text-[11px] font-bold uppercase text-slate-400">Échéance</p>
                                        <p class="mt-1 text-sm {{ $expiresSoon || $status === 'expired' ? 'font-bold text-red-700' : 'font-medium text-slate-700' }}">{{ $registration?->expires_at?->format('d/m/Y') ?? 'Non disponible' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold uppercase text-slate-400 lg:hidden">Dernière publication</p>
                                    @if ($deployment)
                                        <a href="{{ route('deployments.show', $deployment) }}" class="mt-1 inline-flex items-center gap-1.5 text-sm font-bold text-[#673de6] hover:underline lg:mt-0">
                                            {{ $deployment->created_at->format('d/m/Y à H:i') }}
                                            <i data-lucide="chevron-right" class="h-3.5 w-3.5" aria-hidden="true"></i>
                                        </a>
                                        <p class="mt-1 text-xs font-medium text-slate-500">{{ $deployment->project->name }}</p>
                                    @else
                                        <p class="mt-1 text-sm font-medium text-slate-500 lg:mt-0">Aucune publication</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $resourceStatus['class'] }}">
                                        @if ($resourceStatus['icon'])
                                            <i data-lucide="{{ $resourceStatus['icon'] }}" class="h-3.5 w-3.5" aria-hidden="true"></i>
                                        @endif
                                        {{ $resourceStatus['label'] }}
                                    </span>
                                </div>
                            </article>
                        @endforeach

                        @if ($domains->isEmpty())
                            <div class="px-5 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-700">Aucun domaine synchronisé</p>
                                <p class="mt-1 text-sm text-slate-500">Actualisez vos comptes Hostinger pour récupérer les domaines.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
