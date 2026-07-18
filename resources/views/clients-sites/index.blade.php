<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-eyebrow">Portefeuille</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Clients et sites</h1>
                <p class="mt-1 text-sm text-slate-500">Identifiez rapidement le propriétaire, le projet et l’hébergement de chaque site.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" x-data @click="$dispatch('open-client-modal')" class="ui-button-secondary">
                    <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                    Ajouter un client
                </button>
                <button type="button" x-data @click="$dispatch('open-site-modal')" class="ui-button-primary" @disabled($customers->isEmpty())>
                    <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                    Ajouter un site
                </button>
            </div>
        </div>
    </x-slot>

    <div
        class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8"
        x-data="{
            search: '',
            showClientModal: false,
            showSiteModal: false,
            siteCustomer: @js((string) old('customer_id', '')),
            matches(value) {
                return value.toLowerCase().includes(this.search.toLowerCase().trim());
            }
        }"
        @open-client-modal.window="showClientModal = true"
        @open-site-modal.window="siteCustomer = $event.detail && $event.detail.customerId ? String($event.detail.customerId) : ''; showSiteModal = true"
        @keydown.escape.window="showClientModal = false; showSiteModal = false"
    >
        @if ($errors->any())
            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-bold">Certaines informations doivent être corrigées.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="ui-panel px-5 py-4">
                <p class="text-sm font-semibold text-slate-500">Clients suivis</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $customers->count() }}</p>
            </div>
            <div class="ui-panel px-5 py-4">
                <p class="text-sm font-semibold text-slate-500">Sites gérés</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $siteCount }}</p>
            </div>
            <div class="ui-panel px-5 py-4">
                <p class="text-sm font-semibold text-slate-500">Sites sans projet associé</p>
                <p class="mt-1 text-2xl font-bold {{ $unassignedSiteCount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ $unassignedSiteCount }}</p>
            </div>
        </div>

        <section class="mt-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Portefeuille clients</h2>
                    <p class="mt-1 text-sm text-slate-500">Chaque site reste rattaché à une seule fiche client.</p>
                </div>
                <div class="relative w-full sm:max-w-sm">
                    <label for="portfolio_search" class="sr-only">Rechercher</label>
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-slate-400" aria-hidden="true"></i>
                    <input id="portfolio_search" type="search" x-model.debounce.150ms="search" class="ui-input mt-0 pl-10" placeholder="Client, site ou domaine...">
                </div>
            </div>

            <div class="mt-4 space-y-4">
                @forelse ($customers as $customer)
                    @php
                        $searchable = collect([$customer->name])
                            ->merge($customer->sites->flatMap(fn ($site) => [$site->name, $site->domain]))
                            ->filter()
                            ->implode(' ');
                    @endphp
                    <article x-show="matches(@js($searchable))" x-transition.opacity.duration.150ms class="ui-panel">
                        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-start gap-3.5">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]">
                                    <i data-lucide="user-round" class="h-5 w-5" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-950">{{ $customer->name }}</h3>
                                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">{{ $customer->sites_count }} {{ $customer->sites_count > 1 ? 'sites' : 'site' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="$dispatch('open-site-modal', { customerId: '{{ $customer->id }}' })" class="flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-600 transition hover:border-[#8c6cf0] hover:text-[#673de6]" title="Ajouter un site à ce client">
                                    <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                                </button>
                                <details class="group relative">
                                    <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-md border border-slate-300 bg-white text-slate-600 transition hover:border-[#8c6cf0] hover:text-[#673de6]" title="Modifier le client">
                                        <i data-lucide="pencil" class="h-4 w-4" aria-hidden="true"></i>
                                    </summary>
                                    <div class="mt-3 sm:absolute sm:right-0 sm:z-20 sm:w-80">
                                        <form method="POST" action="{{ route('clients.update', $customer) }}" class="rounded-md border border-slate-200 bg-white p-4 shadow-xl">
                                            @csrf
                                            @method('PATCH')
                                            <p class="text-sm font-bold text-slate-950">Modifier la fiche client</p>
                                            <div class="mt-3">
                                                <label for="customer_name_{{ $customer->id }}" class="ui-label">Nom du client</label>
                                                <input id="customer_name_{{ $customer->id }}" name="name" value="{{ $customer->name }}" required class="ui-input">
                                            </div>
                                            <button class="ui-button-primary mt-3">Enregistrer</button>
                                        </form>
                                    </div>
                                </details>
                                @if ($customer->sites_count === 0)
                                    <form method="POST" action="{{ route('clients.destroy', $customer) }}" onsubmit="return confirm('Supprimer cette fiche client ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-500 hover:border-red-300 hover:bg-red-50 hover:text-red-700" title="Supprimer le client">
                                            <i data-lucide="trash-2" class="h-4 w-4" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse ($customer->sites as $site)
                                @php
                                    $deployment = $deployments->get($site->domain);
                                    $hostingerWebsite = $hostingerWebsites->get($site->domain);
                                    $hostingerDomain = $hostingerDomains->get($site->domain);
                                    $hostingerAccount = $hostingerWebsite?->account ?? $hostingerDomain?->account;
                                @endphp
                                <div class="px-5 py-4">
                                    <div class="grid gap-4 lg:grid-cols-[minmax(240px,1.15fr)_minmax(190px,.75fr)_minmax(190px,.75fr)_auto] lg:items-center">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-950">{{ $site->name }}</p>
                                            <a href="https://{{ $site->domain }}" target="_blank" rel="noopener" class="mt-1 block break-all text-sm font-semibold text-[#673de6] hover:underline">{{ $site->domain }}</a>
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold uppercase text-slate-400">Hébergement</p>
                                            @if ($hostingerAccount)
                                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $hostingerAccount->name }}</p>
                                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $hostingerAccount->email ?: ($hostingerWebsite?->username ?: 'Domaine Hostinger détecté') }}</p>
                                            @else
                                                <p class="mt-1 text-sm text-slate-500">Non détecté dans Hostinger</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-[11px] font-bold uppercase text-slate-400">Projet et publication</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-700">{{ $site->project?->name ?? 'Aucun projet associé' }}</p>
                                            @if ($deployment)
                                                <a href="{{ route('deployments.show', $deployment) }}" class="mt-0.5 block text-xs font-semibold text-[#673de6] hover:underline">Publié le {{ $deployment->created_at->format('d/m/Y à H:i') }}</a>
                                            @else
                                                <p class="mt-0.5 text-xs text-slate-500">Aucune publication enregistrée</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 lg:justify-end">
                                            <details class="group lg:relative">
                                                <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-md border border-slate-300 text-slate-600 hover:border-[#8c6cf0] hover:text-[#673de6]" title="Modifier le site">
                                                    <i data-lucide="pencil" class="h-4 w-4" aria-hidden="true"></i>
                                                </summary>
                                                <div class="mt-3 lg:absolute lg:right-0 lg:z-20 lg:w-[440px]">
                                                    <form method="POST" action="{{ route('managed-sites.update', $site) }}" class="rounded-md border border-slate-200 bg-white p-4 shadow-xl">
                                                        @csrf
                                                        @method('PATCH')
                                                        <p class="text-sm font-bold text-slate-950">Modifier le site</p>
                                                        <p class="mt-1 text-xs text-slate-500">Le nom du site est généré automatiquement depuis le domaine.</p>
                                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                                            <div>
                                                                <label for="site_customer_{{ $site->id }}" class="ui-label">Client</label>
                                                                <select id="site_customer_{{ $site->id }}" name="customer_id" required class="ui-input">
                                                                    @foreach ($customers as $option)
                                                                        <option value="{{ $option->id }}" @selected($site->customer_id === $option->id)>{{ $option->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label for="site_domain_{{ $site->id }}" class="ui-label">Domaine</label>
                                                                <input id="site_domain_{{ $site->id }}" name="domain" value="{{ $site->domain }}" required class="ui-input">
                                                            </div>
                                                            <div>
                                                                <label for="site_project_{{ $site->id }}" class="ui-label">Projet GitHub</label>
                                                                <select id="site_project_{{ $site->id }}" name="project_id" class="ui-input">
                                                                    <option value="">Aucun projet associé</option>
                                                                    @foreach ($projects as $project)
                                                                        <option value="{{ $project->id }}" @selected($site->project_id === $project->id)>{{ $project->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <button class="ui-button-primary mt-3">Enregistrer</button>
                                                    </form>
                                                </div>
                                            </details>
                                            <form method="POST" action="{{ route('managed-sites.destroy', $site) }}" onsubmit="return confirm('Retirer ce site de la gestion ? Son hébergement ne sera pas modifié.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="flex h-9 w-9 items-center justify-center rounded-md border border-slate-300 text-slate-500 hover:border-red-300 hover:bg-red-50 hover:text-red-700" title="Retirer de la gestion">
                                                    <i data-lucide="trash-2" class="h-4 w-4" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-7 text-center">
                                    <p class="text-sm font-semibold text-slate-700">Aucun site associé</p>
                                    <p class="mt-1 text-sm text-slate-500">Ajoutez un site pour compléter cette fiche client.</p>
                                </div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="ui-panel px-5 py-12 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]"><i data-lucide="users" class="h-5 w-5" aria-hidden="true"></i></span>
                        <h3 class="mt-4 text-base font-bold text-slate-950">Commencez par ajouter un client</h3>
                        <p class="mt-1 text-sm text-slate-500">Ses sites seront ensuite regroupés sur une seule fiche.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div x-show="showClientModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" @click.self="showClientModal = false">
            <form method="POST" action="{{ route('clients.store') }}" class="w-full max-w-md rounded-lg bg-white p-5 shadow-2xl">
                @csrf
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Ajouter un client</h2>
                        <p class="mt-1 text-sm text-slate-500">Créez la fiche qui regroupera ses sites.</p>
                    </div>
                    <button type="button" @click="showClientModal = false" class="flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="Fermer"><i data-lucide="x" class="h-5 w-5" aria-hidden="true"></i></button>
                </div>
                <div class="mt-5">
                    <label for="new_customer_name" class="ui-label">Nom du client</label>
                    <input id="new_customer_name" name="name" value="{{ old('name') }}" required class="ui-input" placeholder="Nom ou entreprise">
                </div>
                <div class="mt-5 flex justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" @click="showClientModal = false" class="ui-button-secondary">Annuler</button>
                    <button class="ui-button-primary">Ajouter le client</button>
                </div>
            </form>
        </div>

        <div x-show="showSiteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/40 p-4" @click.self="showSiteModal = false">
            <form method="POST" action="{{ route('managed-sites.store') }}" class="my-auto w-full max-w-xl rounded-lg bg-white p-5 shadow-2xl">
                @csrf
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Ajouter un site</h2>
                        <p class="mt-1 text-sm text-slate-500">Le nom du site sera créé automatiquement depuis le domaine.</p>
                    </div>
                    <button type="button" @click="showSiteModal = false" class="flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="Fermer"><i data-lucide="x" class="h-5 w-5" aria-hidden="true"></i></button>
                </div>
                <div class="mt-5 space-y-4">
                    <div>
                        <label for="new_site_customer" class="ui-label">Client</label>
                        <select id="new_site_customer" name="customer_id" x-model="siteCustomer" required class="ui-input">
                            <option value="">Choisir un client</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="new_site_domain" class="ui-label">Nom de domaine</label>
                        <input id="new_site_domain" name="domain" value="{{ old('domain') }}" required class="ui-input" placeholder="https://www.exemple.com/page">
                        <p class="mt-1.5 text-xs text-slate-500">Vous pouvez coller l’adresse complète ; elle sera nettoyée automatiquement.</p>
                    </div>
                    <div>
                        <label for="new_site_project" class="ui-label">Projet GitHub</label>
                        <select id="new_site_project" name="project_id" class="ui-input">
                            <option value="">Aucun projet associé pour le moment</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" @click="showSiteModal = false" class="ui-button-secondary">Annuler</button>
                    <button class="ui-button-primary">Ajouter le site</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
