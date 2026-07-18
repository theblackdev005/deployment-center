<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Déploiement #{{ $deployment->id }}</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if (in_array($deployment->status, ['failed', 'pending'], true))
                    <form method="POST" action="{{ route('deployments.retry', $deployment) }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Relance en cours...';">
                        @csrf
                        <button type="submit" class="ui-button-primary">Relancer la publication</button>
                    </form>
                @endif
                <a href="{{ route('deployments.index') }}" class="ui-button-secondary">Retour à l’historique</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-px overflow-hidden rounded-lg border border-slate-200 bg-slate-200 shadow-sm sm:grid-cols-3">
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
            <h2 class="text-base font-bold text-slate-950">Détails de l’exécution</h2>
            <p class="mt-1 text-sm text-slate-500">Informations utiles pour suivre ou diagnostiquer cette publication.</p>
            <pre class="mt-3 max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-slate-950 p-4 font-mono text-xs leading-6 text-slate-200">{{ $deployment->log ?: 'Aucune information disponible.' }}</pre>
        </section>
    </div>
</x-app-layout>
