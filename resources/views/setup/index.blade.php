<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Configuration</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Préparer la plateforme</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Connectez les ressources nécessaires pour publier et surveiller vos sites.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="ui-panel flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $hasProjects ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-sm font-bold">{{ $hasProjects ? 'OK' : '1' }}</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-950">Sources des projets</h2>
                    <p class="mt-1 text-sm text-slate-500">Enregistrez les dépôts GitHub autorisés à être publiés.</p>
                </div>
            </div>
            <a href="{{ route('projects.index') }}" class="ui-button-secondary">{{ $hasProjects ? 'Gérer les projets' : 'Ajouter un projet' }}</a>
        </div>

        <div class="ui-panel mt-4 flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $hasHostingerAccounts ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-sm font-bold">{{ $hasHostingerAccounts ? 'OK' : '2' }}</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-950">Comptes d’hébergement</h2>
                    <p class="mt-1 text-sm text-slate-500">Centralisez les domaines, sites, échéances et alertes Hostinger.</p>
                </div>
            </div>
            <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary">{{ $hasHostingerAccounts ? 'Gérer les comptes' : 'Ajouter un compte' }}</a>
        </div>

        @if ($hasProjects)
            <div class="mt-5 flex flex-col gap-4 rounded-md border border-emerald-200 bg-emerald-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-emerald-900">La plateforme est prête</p>
                    <p class="mt-1 text-sm text-emerald-800">Vous pouvez maintenant publier un projet vers son domaine.</p>
                </div>
                <a href="{{ route('deployments.create') }}" class="ui-button-primary">Déployer un projet</a>
            </div>
        @endif
    </div>
</x-app-layout>
