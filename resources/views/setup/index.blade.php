<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Réglage unique</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Projets disponibles</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Ajoutez une fois les projets que vous déployez régulièrement. Le serveur et le domaine seront renseignés directement au moment du dépôt.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $hasProjects ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} text-sm font-bold">{{ $hasProjects ? 'OK' : '1' }}</span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-950">Ajouter les liens GitHub</h2>
                    <p class="mt-1 text-sm text-slate-500">Un nom et un lien par projet, rien de plus.</p>
                </div>
            </div>
            <a href="{{ route('projects.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ $hasProjects ? 'Gérer les projets' : 'Ajouter un projet' }}</a>
        </div>

        @if ($hasProjects)
            <div class="mt-5 flex flex-col gap-4 rounded-md border border-emerald-200 bg-emerald-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-emerald-900">Vous pouvez déployer</p>
                    <p class="mt-1 text-sm text-emerald-800">L’accès SSH et le domaine seront demandés sur le formulaire de dépôt.</p>
                </div>
                <a href="{{ route('deployments.create') }}" class="rounded-md bg-emerald-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-700">Déployer un projet</a>
            </div>
        @endif
    </div>
</x-app-layout>
