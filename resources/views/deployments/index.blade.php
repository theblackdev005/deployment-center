<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Historique</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Déploiements</h1>
                <p class="mt-1 text-sm text-slate-500">Suivez chaque publication et intervenez uniquement lorsque nécessaire.</p>
            </div>
            <a href="{{ route('deployments.create') }}" class="ui-button-primary"><i data-lucide="rocket" class="h-4 w-4" aria-hidden="true"></i>Nouveau déploiement</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="ui-panel overflow-hidden">
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
                        <a href="{{ route('deployments.show', $deployment) }}" class="truncate text-sm font-bold text-slate-950 hover:text-[#673de6]">{{ $deployment->project->name }} vers {{ $deployment->domain->name }}</a>
                        <p class="mt-1 text-xs text-slate-500">Lancé par {{ $deployment->user?->name ?? 'Système' }} le {{ $deployment->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <p class="text-sm text-slate-500">{{ $deployment->commit_hash ? substr($deployment->commit_hash, 0, 8) : 'Version actuelle' }}</p>
                    <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                    <div class="flex items-center sm:justify-end">
                        @if (in_array($deployment->status, ['failed', 'pending'], true))
                            <form method="POST" action="{{ route('deployments.retry', $deployment) }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Relance...';">
                                @csrf
                                <button type="submit" class="ui-button-secondary">Relancer</button>
                            </form>
                        @else
                            <a href="{{ route('deployments.show', $deployment) }}" class="ui-button-secondary">Consulter</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-semibold text-slate-700">Aucun déploiement enregistré</p>
                    <p class="mt-1 text-sm text-slate-500">Les publications effectuées apparaîtront dans cet historique.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-5">{{ $deployments->links() }}</div>
    </div>
</x-app-layout>
