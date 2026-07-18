<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Deploy Center</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Tableau de bord</h1>
            <p class="mt-1 text-sm text-slate-500">Déployez un projet et suivez son état depuis un seul endroit.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="grid overflow-hidden rounded-md border border-slate-200 bg-white sm:grid-cols-3">
            @foreach ([
                ['label' => 'Projets', 'value' => $projectCount],
                ['label' => 'Domaines', 'value' => $domainCount],
                ['label' => 'Déploiements réussis', 'value' => $successfulDeploymentCount],
            ] as $stat)
                <div class="border-b border-slate-200 px-5 py-4 last:border-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                    <p class="text-xs font-medium uppercase text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        @if ($failedDeploymentCount > 0)
            <a href="{{ route('deployments.index') }}" class="mt-4 flex items-center justify-between rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 hover:bg-red-100">
                <span><strong>{{ $failedDeploymentCount }}</strong> {{ $failedDeploymentCount > 1 ? 'déploiements nécessitent' : 'déploiement nécessite' }} votre attention.</span>
                <span class="font-semibold">Consulter</span>
            </a>
        @endif

        <section class="mt-6 overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-base font-semibold text-slate-950">Nouveau déploiement</h2>
                <p class="mt-1 text-sm text-slate-500">Choisissez le projet et renseignez l’accès au domaine.</p>
            </div>
            <div class="p-5 sm:p-6">
                @include('deployments._form')
            </div>
        </section>

        <section class="mt-7">
            <div class="flex items-end justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Derniers déploiements</h2>
                    <p class="mt-1 text-sm text-slate-500">Les cinq opérations les plus récentes.</p>
                </div>
                <a href="{{ route('deployments.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Voir l’historique</a>
            </div>

            <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white">
                @forelse ($recentDeployments as $deployment)
                    @php
                        $status = match ($deployment->status) {
                            'succeeded' => ['label' => 'Terminé', 'class' => 'bg-emerald-50 text-emerald-700'],
                            'failed' => ['label' => 'Échec', 'class' => 'bg-red-50 text-red-700'],
                            'running' => ['label' => 'En cours', 'class' => 'bg-blue-50 text-blue-700'],
                            default => ['label' => 'En attente', 'class' => 'bg-amber-50 text-amber-700'],
                        };
                    @endphp
                    <a href="{{ route('deployments.show', $deployment) }}" class="grid gap-2 border-b border-slate-100 px-4 py-3.5 last:border-0 hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_150px_90px] sm:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <p class="text-xs text-slate-500 sm:text-sm">{{ $deployment->commit_hash ? 'Version '.substr($deployment->commit_hash, 0, 8) : 'Sans version' }}</p>
                        <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                    </a>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-700">Aucun déploiement pour le moment</p>
                        <p class="mt-1 text-sm text-slate-500">Le premier déploiement apparaîtra ici.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
