<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Historique</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Déploiements</h1>
            </div>
            <a href="{{ route('deployments.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Nouveau déploiement</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            @forelse ($deployments as $deployment)
                <div class="grid gap-3 border-b border-slate-100 px-4 py-4 last:border-0 sm:grid-cols-[minmax(0,1fr)_180px_130px] sm:items-center">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-950">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">Créé par {{ $deployment->user?->name ?? 'Système' }} le {{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <p class="text-sm text-slate-500">{{ $deployment->project->branch }}</p>
                    <span class="w-fit rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">En attente</span>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-700">Aucun déploiement enregistré</p>
                    <p class="mt-1 text-sm text-slate-500">Préparez votre première publication lorsque les cibles sont configurées.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $deployments->links() }}</div>
    </div>
</x-app-layout>
