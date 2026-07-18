<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Première utilisation</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Configuration</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Ces informations sont renseignées une seule fois. Deploy Center calculera ensuite automatiquement les chemins et paramètres de publication.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="space-y-3">
            @foreach ([
                ['number' => 1, 'title' => 'Connecter l’hébergement', 'text' => 'Ajoutez votre accès Hostinger.', 'done' => $hasServers, 'route' => 'servers.index', 'action' => 'Configurer'],
                ['number' => 2, 'title' => 'Ajouter les projets', 'text' => 'Indiquez les dépôts GitHub à publier.', 'done' => $hasProjects, 'route' => 'projects.index', 'action' => 'Ajouter'],
                ['number' => 3, 'title' => 'Ajouter les domaines', 'text' => 'Associez chaque domaine à son hébergement.', 'done' => $hasDomains, 'route' => 'domains.index', 'action' => 'Ajouter'],
            ] as $step)
                <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $step['done'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-sm font-bold">
                            {{ $step['done'] ? 'OK' : $step['number'] }}
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950">{{ $step['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $step['text'] }}</p>
                        </div>
                    </div>
                    <a href="{{ route($step['route']) }}" class="inline-flex shrink-0 items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        {{ $step['done'] ? 'Gérer' : $step['action'] }}
                    </a>
                </div>
            @endforeach
        </div>

        @if ($hasServers && $hasProjects && $hasDomains)
            <div class="mt-6 flex flex-col gap-4 rounded-md border border-emerald-200 bg-emerald-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-900">Configuration terminée</p>
                    <p class="mt-1 text-sm text-emerald-800">Vous pouvez préparer votre premier déploiement.</p>
                </div>
                <a href="{{ route('deployments.create') }}" class="rounded-md bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-700">Nouveau déploiement</a>
            </div>
        @endif
    </div>
</x-app-layout>
