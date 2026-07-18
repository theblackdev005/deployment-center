<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Cibles</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Domaines</h1>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_390px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-base font-semibold text-slate-950">Domaines enregistrés</h2>
                <p class="mt-1 text-sm text-slate-500">Le dossier de publication est calculé automatiquement.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($domains as $domain)
                    <article class="rounded-md border border-slate-200 bg-white p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-950">{{ $domain->name }}</h3>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Non vérifié</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $domain->server->name }}</p>
                                <p class="mt-1 break-all font-mono text-xs text-slate-400">{{ $domain->document_root }}</p>
                            </div>
                            <form method="POST" action="{{ route('domains.destroy', $domain) }}" onsubmit="return confirm('Supprimer ce domaine ?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-700">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun domaine enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('domains.store') }}" class="rounded-md border border-slate-200 bg-white p-5">
                @csrf
                <h2 class="text-base font-semibold text-slate-950">Ajouter un domaine</h2>
                <p class="mt-1 text-sm text-slate-500">Choisissez l’hébergement puis saisissez le domaine.</p>
                <div class="mt-5 space-y-4">
                    <div>
                        <label for="server_id" class="text-sm font-medium text-slate-700">Serveur</label>
                        <select id="server_id" name="server_id" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Choisir un serveur</option>
                            @foreach ($servers as $server)
                                <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('server_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="name" class="text-sm font-medium text-slate-700">Nom de domaine</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="exemple.com">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                </div>
                <button class="mt-5 w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" @disabled($servers->isEmpty())>Ajouter le domaine</button>
            </form>
        </aside>
    </div>
</x-app-layout>
