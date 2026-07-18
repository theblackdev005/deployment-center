<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Déploiement #{{ $deployment->id }}</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</h1>
            </div>
            <a href="{{ route('deployments.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Retour à l’historique</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-px overflow-hidden rounded-md border border-slate-200 bg-slate-200 sm:grid-cols-3">
            <div class="bg-white p-4">
                <p class="text-xs font-medium uppercase text-slate-500">État</p>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ match ($deployment->status) { 'succeeded' => 'Terminé', 'failed' => 'Échec', 'running' => 'En cours', default => 'En attente' } }}</p>
            </div>
            <div class="bg-white p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Domaine</p>
                <p class="mt-2 break-all text-sm font-semibold text-slate-950">{{ $deployment->domain->name }}</p>
            </div>
            <div class="bg-white p-4">
                <p class="text-xs font-medium uppercase text-slate-500">Date</p>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        @if ($deployment->error_message)
            <div class="mt-5 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $deployment->error_message }}</div>
        @endif

        <section class="mt-7">
            <h2 class="text-base font-semibold text-slate-950">Journal</h2>
            <pre class="mt-3 max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-200">{{ $deployment->log ?: 'Aucune information disponible.' }}</pre>
        </section>
    </div>
</x-app-layout>
