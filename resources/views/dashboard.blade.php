<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Exploitation</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Vue d’ensemble</h1>
            </div>
            <a href="{{ route('deployments.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Nouveau déploiement
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-slate-200 bg-slate-200 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Projets', 'value' => $projectCount, 'route' => 'projects.index'],
                ['label' => 'Serveurs', 'value' => $serverCount, 'route' => 'servers.index'],
                ['label' => 'Domaines', 'value' => $domainCount, 'route' => 'domains.index'],
                ['label' => 'Déploiements', 'value' => $deploymentCount, 'route' => 'deployments.index'],
            ] as $stat)
                <a href="{{ route($stat['route']) }}" class="bg-white p-5 hover:bg-slate-50">
                    <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $stat['value'] }}</p>
                </a>
            @endforeach
        </div>

        <section class="mt-8">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Activité récente</h2>
                    <p class="mt-1 text-sm text-slate-500">Dernières opérations enregistrées sur la plateforme.</p>
                </div>
                <a href="{{ route('deployments.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Tout afficher</a>
            </div>

            <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white">
                @forelse ($recentDeployments as $deployment)
                    <div class="flex flex-col gap-2 border-b border-slate-100 px-4 py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <span class="w-fit rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">En attente</span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-700">Aucun déploiement pour le moment</p>
                        <p class="mt-1 text-sm text-slate-500">Ajoutez d’abord un projet, un serveur et un domaine.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
