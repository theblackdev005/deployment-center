<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Compte Hostinger</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">{{ $account->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $account->email ?: 'Email non renseigné' }}</p>
            </div>
            <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary">
                <i data-lucide="chevron-right" class="h-4 w-4 rotate-180" aria-hidden="true"></i>
                Retour aux comptes
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        <section class="ui-panel overflow-hidden">
            <div class="grid gap-5 px-5 py-5 sm:grid-cols-2 lg:grid-cols-[minmax(240px,1.2fr)_repeat(3,minmax(150px,.8fr))] lg:items-center">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]">
                        <i data-lucide="server" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-950">{{ $account->name }}</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-700">Compte connecté</p>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400">Domaines répertoriés</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ $domains->count() }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase text-slate-400">Échéance hébergement</p>
                    <p class="mt-1 text-sm font-bold {{ $nextPlan && $nextPlan->expires_at->lte(now()->addMonthsNoOverflow($expirationNoticeMonths)) ? 'text-amber-700' : 'text-slate-800' }}">{{ $nextPlan?->expires_at?->format('d/m/Y') ?? 'Non disponible' }}</p>
                </div>
                <div class="flex items-end justify-between gap-3 lg:block">
                    <div>
                        <p class="text-[11px] font-bold uppercase text-slate-400">Dernière synchronisation</p>
                        <p class="mt-1 text-sm font-semibold text-slate-700">{{ $account->last_synced_at?->format('d/m/Y à H:i') ?? 'Jamais' }}</p>
                    </div>
                    <form method="POST" action="{{ route('hostinger.accounts.sync', $account) }}" class="lg:mt-3">
                        @csrf
                        <button class="ui-button-secondary">
                            <i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>
                            Actualiser
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="mt-6" x-data="{ search: '' }">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Domaines du compte</h2>
                        <p class="mt-1 text-sm text-slate-500">Tous les domaines rattachés à ce compte Hostinger.</p>
                    </div>
                    <div class="relative w-full sm:w-72">
                        <label for="account_domain_search" class="sr-only">Rechercher un domaine</label>
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" aria-hidden="true"></i>
                        <input id="account_domain_search" type="search" x-model.debounce.150ms="search" class="block min-h-11 w-full rounded-md border-slate-300 bg-white pl-10 text-sm shadow-sm focus:border-[#8062e8] focus:ring-[#8062e8]" placeholder="Rechercher un domaine...">
                    </div>
                </div>

                <div class="hidden grid-cols-[minmax(280px,1.35fr)_minmax(200px,.8fr)_minmax(210px,.8fr)_150px] gap-6 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-bold uppercase text-slate-500 lg:grid">
                    <span>Domaine et hébergement</span>
                    <span>Cycle du domaine</span>
                    <span>Dernière publication</span>
                    <span>État</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($domains as $row)
                        @php
                            $registration = $row['registration'];
                            $website = $row['website'];
                            $deployment = $row['deployment'];
                            $status = strtolower((string) ($registration?->status ?? ''));
                            $isExpired = $status === 'expired' || $registration?->expires_at?->isPast();
                            $isSuspended = $status === 'suspended';
                            $hasFailed = $status === 'failed';
                            $websiteDisabled = $website && ! $website->is_enabled;
                            $expiresSoon = $registration?->expires_at?->isBetween(now(), now()->addMonthsNoOverflow($expirationNoticeMonths));
                            $hasProblem = $isExpired || $isSuspended || $hasFailed || $websiteDisabled;
                            $resourceStatus = match (true) {
                                $isExpired => ['label' => 'Expiré', 'class' => 'bg-red-100 text-red-800'],
                                $isSuspended => ['label' => 'Suspendu', 'class' => 'bg-red-100 text-red-800'],
                                $hasFailed => ['label' => 'Erreur Hostinger', 'class' => 'bg-red-100 text-red-800'],
                                $websiteDisabled => ['label' => 'Site désactivé', 'class' => 'bg-red-100 text-red-800'],
                                $expiresSoon => ['label' => 'À renouveler', 'class' => 'bg-amber-100 text-amber-800'],
                                $status === 'pending' => ['label' => 'En attente', 'class' => 'bg-amber-100 text-amber-800'],
                                $website?->is_enabled => ['label' => 'Actif', 'class' => 'bg-emerald-100 text-emerald-800'],
                                $status === 'active' => ['label' => 'Domaine actif', 'class' => 'bg-emerald-100 text-emerald-800'],
                                default => ['label' => 'Enregistré', 'class' => 'bg-slate-100 text-slate-700'],
                            };
                        @endphp
                        <article
                            x-show="@js($row['domain']).toLowerCase().includes(search.toLowerCase().trim())"
                            x-transition.opacity.duration.150ms
                            class="grid gap-5 px-5 py-5 transition hover:bg-slate-50 lg:grid-cols-[minmax(280px,1.35fr)_minmax(200px,.8fr)_minmax(210px,.8fr)_150px] lg:items-center lg:gap-6"
                        >
                            <div class="flex min-w-0 items-start gap-3.5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-950 text-white">
                                    <i data-lucide="globe-2" class="h-5 w-5" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="h-2 w-2 shrink-0 rounded-full {{ $hasProblem ? 'bg-red-500' : ($expiresSoon ? 'bg-amber-400' : 'bg-emerald-500') }}"></span>
                                        <p class="break-all text-sm font-bold text-slate-950">{{ $row['domain'] }}</p>
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @if ($website?->vhost_type === 'subdomain')
                                            <span class="rounded-md bg-sky-50 px-2 py-1 text-[11px] font-semibold text-sky-700">Sous-domaine</span>
                                        @endif
                                        <span class="text-xs text-slate-500">{{ $website?->username ? 'Hébergement '.$website->username : 'Domaine sans site associé' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 lg:block">
                                <div>
                                    <p class="text-[11px] font-bold uppercase text-slate-400">Ajouté</p>
                                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $registration?->registered_at?->format('d/m/Y') ?? $website?->remote_created_at?->format('d/m/Y') ?? '—' }}</p>
                                </div>
                                <div class="lg:mt-2.5">
                                    <p class="text-[11px] font-bold uppercase text-slate-400">Échéance</p>
                                    <p class="mt-1 text-sm {{ $expiresSoon || $isExpired ? 'font-bold text-red-700' : 'font-medium text-slate-700' }}">{{ $registration?->expires_at?->format('d/m/Y') ?? 'Non disponible' }}</p>
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
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $resourceStatus['class'] }}">{{ $resourceStatus['label'] }}</span>
                            </div>
                        </article>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700">Aucun domaine synchronisé</p>
                            <p class="mt-1 text-sm text-slate-500">Actualisez ce compte pour récupérer ses domaines.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
