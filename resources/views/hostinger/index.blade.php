<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-700">Inventaire central</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Domaines Hostinger</h1>
                <p class="mt-1 text-sm text-slate-500">Tous vos comptes, sites et abonnements au même endroit.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($accounts->isNotEmpty())
                    <form method="POST" action="{{ route('hostinger.accounts.sync-all') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Actualisation...';">
                        @csrf
                        <button class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60">Tout actualiser</button>
                    </form>
                @endif
                <a href="{{ route('hostinger.accounts.index') }}" class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Gérer les comptes</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
        @if ($accounts->isEmpty())
            <div class="rounded-md border border-slate-200 bg-white px-5 py-12 text-center">
                <p class="text-base font-semibold text-slate-900">Connectez votre premier compte Hostinger</p>
                <p class="mt-2 text-sm text-slate-500">La synchronisation est en lecture seule et ne modifie aucun service.</p>
                <a href="{{ route('hostinger.accounts.index') }}" class="mt-5 inline-flex rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Ajouter un compte</a>
            </div>
        @else
            <div class="grid overflow-hidden rounded-md border border-slate-200 bg-white sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Comptes connectés', 'value' => $accounts->where('status', 'connected')->count()],
                    ['label' => 'Domaines regroupés', 'value' => $domainCount],
                    ['label' => 'Sites hébergés', 'value' => $websiteCount],
                ] as $stat)
                    <div class="border-b border-slate-200 px-5 py-4 last:border-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                        <p class="text-xs font-medium uppercase text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($expiringDomains->isNotEmpty())
                <section class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-sm font-semibold text-amber-950">{{ $expiringDomains->count() }} {{ $expiringDomains->count() > 1 ? 'domaines expirent' : 'domaine expire' }} dans les 30 prochains jours</h2>
                            <p class="mt-1 text-xs text-amber-800">Vérifiez le renouvellement dans le compte Hostinger concerné.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($expiringDomains->take(5) as $domain)
                                <span class="rounded-md bg-white px-2.5 py-1 text-xs font-medium text-amber-900">{{ $domain->domain }} · {{ $domain->expires_at->format('d/m/Y') }}</span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <section class="mt-6">
                <form method="GET" action="{{ route('hostinger.index') }}" class="grid gap-3 rounded-md border border-slate-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_220px_auto]">
                    <div>
                        <label for="q" class="sr-only">Rechercher un domaine</label>
                        <input id="q" name="q" value="{{ $search }}" class="block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Rechercher un domaine ou un compte">
                    </div>
                    <div>
                        <label for="account" class="sr-only">Compte Hostinger</label>
                        <select id="account" name="account" class="block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Tous les comptes</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected($selectedAccount === $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrer</button>
                </form>

                <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white">
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    @foreach (['Domaine', 'Compte', 'Hébergement', 'PHP', 'Expiration', 'État'] as $heading)
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-slate-500">{{ $heading }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($domains as $row)
                                    @php
                                        $registration = $row['registration'];
                                        $website = $row['website'];
                                        $expiresSoon = $registration?->expires_at?->isBetween(now(), now()->addDays(30));
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $row['domain'] }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $row['account']->name }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $website?->username ?? 'Non hébergé' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $website?->php_version_full ?? $website?->php_version ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm {{ $expiresSoon ? 'font-semibold text-amber-700' : 'text-slate-600' }}">{{ $registration?->expires_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($website && $website->is_enabled)
                                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">En ligne</span>
                                            @elseif ($registration)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', $registration->status ?? 'enregistré')) }}</span>
                                            @else
                                                <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Désactivé</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Aucun domaine ne correspond à la recherche.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-slate-100 md:hidden">
                        @forelse ($domains as $row)
                            @php($website = $row['website'])
                            @php($registration = $row['registration'])
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="break-all text-sm font-semibold text-slate-900">{{ $row['domain'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $row['account']->name }}</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $website?->is_enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $website?->is_enabled ? 'En ligne' : 'Domaine' }}</span>
                                </div>
                                <dl class="mt-3 grid grid-cols-2 gap-3 text-xs">
                                    <div><dt class="text-slate-500">Hébergement</dt><dd class="mt-1 font-medium text-slate-800">{{ $website?->username ?? 'Non hébergé' }}</dd></div>
                                    <div><dt class="text-slate-500">PHP</dt><dd class="mt-1 font-medium text-slate-800">{{ $website?->php_version_full ?? $website?->php_version ?? '—' }}</dd></div>
                                    <div><dt class="text-slate-500">Expiration</dt><dd class="mt-1 font-medium text-slate-800">{{ $registration?->expires_at?->format('d/m/Y') ?? '—' }}</dd></div>
                                    <div><dt class="text-slate-500">Compte SSH</dt><dd class="mt-1 font-medium text-slate-800">{{ $website?->username ?? '—' }}</dd></div>
                                </dl>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-slate-500">Aucun domaine ne correspond à la recherche.</div>
                        @endforelse
                    </div>
                </div>
                <div class="mt-4">{{ $domains->links() }}</div>
            </section>

            <section class="mt-8">
                <div class="border-b border-slate-200 pb-3">
                    <h2 class="text-base font-semibold text-slate-950">Abonnements</h2>
                    <p class="mt-1 text-sm text-slate-500">Expiration et renouvellement automatique signalés par Hostinger.</p>
                </div>
                <div class="mt-3 overflow-hidden rounded-md border border-slate-200 bg-white">
                    @forelse ($subscriptions as $subscription)
                        <div class="grid gap-3 border-b border-slate-100 px-4 py-4 last:border-0 sm:grid-cols-[minmax(0,1fr)_160px_150px_130px] sm:items-center">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $subscription->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $subscription->account->name }}</p>
                            </div>
                            <p class="text-sm text-slate-600">{{ $subscription->expires_at ? 'Expire le '.$subscription->expires_at->format('d/m/Y') : 'Sans date d’expiration' }}</p>
                            <span class="w-fit rounded-full px-2.5 py-1 text-xs font-semibold {{ $subscription->is_auto_renewed ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $subscription->is_auto_renewed ? 'Renouvellement actif' : 'Renouvellement inactif' }}</span>
                            <p class="text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $subscription->status ?? 'inconnu')) }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-slate-500">Aucun abonnement synchronisé.</div>
                    @endforelse
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
