<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Vue d’ensemble</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Pilotage des déploiements</h1>
                <p class="mt-1 text-sm text-slate-500">Publiez vos projets et contrôlez les dernières opérations.</p>
            </div>
            <a href="{{ route('deployments.create') }}" class="ui-button-primary">
                <i data-lucide="rocket" class="h-4 w-4" aria-hidden="true"></i>
                Nouveau déploiement
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        @if ($failedDeploymentCount > 0)
            <a href="{{ route('deployments.index') }}" class="mb-5 flex items-center gap-4 rounded-lg border border-red-200 bg-red-50 px-5 py-4 transition hover:bg-red-100">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i data-lucide="alert-triangle" class="h-5 w-5" aria-hidden="true"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-red-950">{{ $failedDeploymentCount }} {{ $failedDeploymentCount > 1 ? 'déploiements demandent' : 'déploiement demande' }} une vérification</p>
                    <p class="mt-0.5 text-sm text-red-700">Consultez le journal avant de relancer la publication.</p>
                </div>
                <i data-lucide="chevron-right" class="h-5 w-5 shrink-0 text-red-600" aria-hidden="true"></i>
            </a>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['label' => 'Projets disponibles', 'value' => $projectCount, 'icon' => 'folder-git-2', 'color' => 'bg-[#ebe7ff] text-[#673de6]'],
                ['label' => 'Domaines configurés', 'value' => $domainCount, 'icon' => 'globe-2', 'color' => 'bg-sky-50 text-sky-600'],
                ['label' => 'Publications réussies', 'value' => $successfulDeploymentCount, 'icon' => 'check-circle-2', 'color' => 'bg-emerald-50 text-emerald-600'],
            ] as $stat)
                <div class="ui-panel px-5 py-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-1.5 text-3xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-md {{ $stat['color'] }}">
                            <i data-lucide="{{ $stat['icon'] }}" class="h-5 w-5" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="ui-panel mt-6 overflow-hidden">
            <div class="flex flex-col gap-5 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]">
                        <i data-lucide="globe-2" class="h-5 w-5" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-base font-bold text-slate-950">Parc Hostinger</h2>
                            @if ($hostingerOpenAlertCount > 0)
                                <a href="{{ route('hostinger.index') }}" class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 hover:bg-red-100">
                                    {{ $hostingerOpenAlertCount }} {{ $hostingerOpenAlertCount > 1 ? 'alertes' : 'alerte' }} à vérifier
                                </a>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Aucune alerte</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $activeHostingerAccountCount }} {{ $activeHostingerAccountCount > 1 ? 'comptes actifs' : 'compte actif' }}
                            <span class="mx-1 text-slate-300">·</span>
                            {{ $hostingerDomainCount }} {{ $hostingerDomainCount > 1 ? 'domaines suivis' : 'domaine suivi' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Dernière synchronisation : {{ $hostingerLastSyncedAt?->format('d/m/Y à H:i') ?? 'aucune synchronisation effectuée' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 lg:justify-end">
                    @if ($activeHostingerAccountCount > 0)
                        <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}">
                            @csrf
                            <button class="ui-button-secondary">
                                <i data-lucide="refresh-cw" class="h-4 w-4" aria-hidden="true"></i>
                                Synchroniser
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary">
                        <i data-lucide="settings-2" class="h-4 w-4" aria-hidden="true"></i>
                        Gérer les comptes
                    </a>
                    <a href="{{ route('hostinger.index') }}" class="ui-button-primary">
                        <i data-lucide="globe-2" class="h-4 w-4" aria-hidden="true"></i>
                        Ouvrir le parc
                    </a>
                </div>
            </div>
        </section>

        <div class="mt-6">
            <section class="ui-panel overflow-hidden">
                <div class="ui-panel-header flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Activité récente</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Les cinq dernières publications.</p>
                    </div>
                    <a href="{{ route('deployments.index') }}" class="text-sm font-bold text-[#673de6] hover:text-[#5530c9]">Tout voir</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentDeployments as $deployment)
                        @php
                            $status = match ($deployment->status) {
                                'succeeded' => ['label' => 'Terminé', 'class' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
                                'failed' => ['label' => 'Échec', 'class' => 'bg-red-50 text-red-700', 'dot' => 'bg-red-500'],
                                'running' => ['label' => 'En cours', 'class' => 'bg-sky-50 text-sky-700', 'dot' => 'bg-sky-500'],
                                default => ['label' => 'En attente', 'class' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                            };
                        @endphp
                        <a href="{{ route('deployments.show', $deployment) }}" class="flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $status['dot'] }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $deployment->project->name }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $deployment->domain->name }} · {{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700">Aucune publication enregistrée</p>
                            <p class="mt-1 text-sm text-slate-500">Votre première opération apparaîtra ici.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
