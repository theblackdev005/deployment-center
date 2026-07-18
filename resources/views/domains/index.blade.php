<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Destinations</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Domaines de déploiement</h1>
            <p class="mt-1 text-sm text-slate-500">Associez chaque domaine au serveur qui doit recevoir le projet.</p>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_390px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-lg font-bold text-slate-950">Destinations enregistrées</h2>
                <p class="mt-1 text-sm text-slate-500">Le dossier de publication est déterminé automatiquement.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($domains as $domain)
                    <article class="ui-panel p-5 transition hover:shadow-md">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-950">{{ $domain->name }}</h3>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $domain->server->name }}</p>
                                <p class="mt-1 break-all font-mono text-xs text-slate-400">{{ $domain->document_root }}</p>
                            </div>
                            <form method="POST" action="{{ route('domains.destroy', $domain) }}" onsubmit="return confirm('Supprimer ce domaine ?')">
                                @csrf @method('DELETE')
                                <button class="ui-button-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun domaine enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('domains.store') }}" class="ui-panel p-5">
                @csrf
                <h2 class="text-base font-bold text-slate-950">Ajouter une destination</h2>
                <p class="mt-1 text-sm text-slate-500">Choisissez le serveur puis indiquez le domaine.</p>
                <div class="mt-5 space-y-4">
                    <div>
                        <label for="server_id" class="ui-label">Serveur</label>
                        <select id="server_id" name="server_id" required class="ui-input">
                            <option value="">Choisir un serveur</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('server_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="name" class="ui-label">Nom de domaine</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="ui-input" placeholder="exemple.com">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                </div>
                <button class="ui-button-primary mt-5 w-full" @disabled($servers->isEmpty())>Ajouter la destination</button>
            </form>
        </aside>
    </div>
</x-app-layout>
