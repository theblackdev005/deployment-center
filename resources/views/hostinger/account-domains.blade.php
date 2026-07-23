<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-slate-950">Domaines du compte</h1>
                <p class="mt-1 truncate text-sm text-slate-500">{{ $account->name }}</p>
            </div>
            <a href="{{ route('hostinger.accounts.index') }}" class="ui-button-secondary shrink-0">
                <i data-lucide="chevron-right" class="h-4 w-4 rotate-180" aria-hidden="true"></i>
                Retour
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="divide-y divide-slate-100">
                @forelse ($domains as $row)
                    @php
                        $registration = $row['registration'];
                        $website = $row['website'];
                        $status = strtolower((string) ($registration?->status ?? ''));
                        $isExpired = $status === 'expired' || $registration?->expires_at?->isPast();
                        $isSuspended = $status === 'suspended';
                        $hasFailed = $status === 'failed';
                        $websiteDisabled = $website && ! $website->is_enabled;
                        $expiresSoon = $registration?->expires_at?->isBetween(now(), now()->addMonthsNoOverflow($expirationNoticeMonths));
                        $resourceStatus = match (true) {
                            $isExpired => ['label' => 'Expiré', 'class' => 'bg-red-100 text-red-800', 'dot' => 'bg-red-500'],
                            $isSuspended => ['label' => 'Suspendu', 'class' => 'bg-red-100 text-red-800', 'dot' => 'bg-red-500'],
                            $hasFailed => ['label' => 'Erreur Hostinger', 'class' => 'bg-red-100 text-red-800', 'dot' => 'bg-red-500'],
                            $websiteDisabled => ['label' => 'Site désactivé', 'class' => 'bg-red-100 text-red-800', 'dot' => 'bg-red-500'],
                            $expiresSoon => ['label' => 'À renouveler', 'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-400'],
                            $status === 'pending' => ['label' => 'En attente', 'class' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-400'],
                            $website?->is_enabled => ['label' => 'Actif', 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
                            $status === 'active' => ['label' => 'Domaine actif', 'class' => 'bg-emerald-100 text-emerald-800', 'dot' => 'bg-emerald-500'],
                            default => ['label' => 'Enregistré', 'class' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-400'],
                        };
                    @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-5">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $resourceStatus['dot'] }}"></span>
                            <div class="min-w-0">
                                <p class="break-all text-sm font-bold text-slate-950">{{ $row['domain'] }}</p>
                                @if ($registration?->expires_at)
                                    <p class="mt-1 text-xs text-slate-500">Échéance : {{ $registration->expires_at->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $resourceStatus['class'] }}">{{ $resourceStatus['label'] }}</span>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-700">Aucun domaine synchronisé</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
