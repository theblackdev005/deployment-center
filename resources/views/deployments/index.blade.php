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
                @php
                    $status = match ($deployment->status) {
                        'succeeded' => ['label' => 'Terminé', 'class' => 'bg-emerald-50 text-emerald-700'],
                        'failed' => ['label' => 'Échec', 'class' => 'bg-red-50 text-red-700'],
                        'running' => ['label' => 'En cours', 'class' => 'bg-blue-50 text-blue-700'],
                        default => ['label' => 'En attente', 'class' => 'bg-amber-50 text-amber-700'],
                    };
                @endphp
                <div class="grid gap-3 border-b border-slate-100 px-4 py-4 last:border-0 hover:bg-slate-50 sm:grid-cols-[minmax(0,1fr)_150px_100px_120px] sm:items-center">
                    <div class="min-w-0">
                        <a href="{{ route('deployments.show', $deployment) }}" class="truncate text-sm font-semibold text-slate-950 hover:text-emerald-700">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</a>
                        <p class="mt-1 text-xs text-slate-500">Créé par {{ $deployment->user?->name ?? 'Système' }} le {{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <p class="text-sm text-slate-500">{{ $deployment->commit_hash ? substr($deployment->commit_hash, 0, 8) : 'Version actuelle' }}</p>
                    <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                    <div class="flex items-center sm:justify-end">
                        @if (in_array($deployment->status, ['failed', 'pending'], true))
                            <form method="POST" action="{{ route('deployments.retry', $deployment) }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Relance...';">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 disabled:cursor-wait disabled:opacity-60">Relancer</button>
                            </form>
                        @else
                            <a href="{{ route('deployments.show', $deployment) }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white">Voir</a>
                        @endif
                    </div>
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
