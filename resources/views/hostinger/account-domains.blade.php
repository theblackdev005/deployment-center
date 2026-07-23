<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="ui-eyebrow">Compte Hostinger</p>
                <h1 class="mt-1 truncate text-2xl font-bold text-slate-950">{{ $account->name }}</h1>
                <p class="mt-1 truncate text-sm text-slate-500">{{ $account->email ?: 'Email non renseigné' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary">
                    <i data-lucide="chevron-right" class="h-4 w-4 rotate-180" aria-hidden="true"></i>
                    Retour
                </a>
                <form method="POST" action="{{ route('hostinger.accounts.sync', $account) }}">
                    @csrf
                    <button class="ui-button-primary">
                        <i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>
                        Actualiser
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        <section x-data="{ search: '' }">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-950">Domaines du compte</h2>
                        <span class="rounded-full bg-[#ebe7ff] px-2.5 py-1 text-xs font-bold text-[#673de6]">{{ $domains->count() }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        Dernière synchronisation : {{ $account->last_synced_at?->format('d/m/Y à H:i') ?? 'jamais' }}
                    </p>
                </div>
                <div class="relative w-full sm:w-72">
                    <label for="account_domain_search" class="sr-only">Rechercher un domaine</label>
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" aria-hidden="true"></i>
                    <input id="account_domain_search" type="search" x-model.debounce.150ms="search" class="block min-h-11 w-full rounded-md border-slate-300 bg-white pl-10 text-sm shadow-sm focus:border-[#8062e8] focus:ring-[#8062e8]" placeholder="Rechercher un domaine...">
                </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                        class="flex min-w-0 flex-col rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                    >
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md {{ $hasProblem ? 'bg-red-50 text-red-600' : ($expiresSoon ? 'bg-amber-50 text-amber-600' : 'bg-slate-950 text-white') }}">
                                <i data-lucide="globe-2" class="h-4 w-4" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="break-all text-sm font-bold text-slate-950">{{ $row['domain'] }}</h3>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $resourceStatus['class'] }}">{{ $resourceStatus['label'] }}</span>
                                    @if ($website?->vhost_type === 'subdomain')
                                        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700">Sous-domaine</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                            <div class="min-w-0">
                                <dt class="text-[10px] font-bold uppercase text-slate-400">Échéance</dt>
                                <dd class="mt-1 text-sm {{ $expiresSoon || $isExpired ? 'font-bold text-red-700' : 'font-semibold text-slate-700' }}">
                                    {{ $registration?->expires_at?->format('d/m/Y') ?? 'Non disponible' }}
                                </dd>
                            </div>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-bold uppercase text-slate-400">Hébergement</dt>
                                <dd class="mt-1 truncate text-sm font-semibold text-slate-700">{{ $website?->username ?: 'Non associé' }}</dd>
                            </div>
                        </dl>

                        @if ($deployment)
                            <a href="{{ route('deployments.show', $deployment) }}" class="mt-3 inline-flex items-center gap-1.5 border-t border-slate-100 pt-3 text-xs font-bold text-[#673de6] hover:underline">
                                Publication du {{ $deployment->created_at->format('d/m/Y à H:i') }}
                                <i data-lucide="chevron-right" class="h-3.5 w-3.5" aria-hidden="true"></i>
                            </a>
                        @endif
                    </article>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-slate-300 bg-white px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">Aucun domaine synchronisé</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
