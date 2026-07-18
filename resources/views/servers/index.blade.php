<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Infrastructure</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Serveurs</h1>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-base font-semibold text-slate-950">Connexions enregistrées</h2>
                <p class="mt-1 text-sm text-slate-500">Les clés privées restent hors de la base de données.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($servers as $server)
                    <article class="rounded-md border border-slate-200 bg-white p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-950">{{ $server->name }}</h3>
                                <p class="mt-1 break-all text-sm text-slate-500">{{ $server->username }}@{{ $server->host }}:{{ $server->port }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ $server->domains_count }} domaine(s)</p>
                            </div>
                            <form method="POST" action="{{ route('servers.destroy', $server) }}" onsubmit="return confirm('Supprimer ce serveur ?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-700">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun serveur enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('servers.store') }}" class="rounded-md border border-slate-200 bg-white p-5">
                @csrf
                <h2 class="text-base font-semibold text-slate-950">Ajouter un serveur</h2>
                <p class="mt-1 text-sm text-slate-500">Connexion SSH dédiée aux déploiements.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach ([
                        ['name' => 'name', 'label' => 'Nom', 'value' => old('name'), 'placeholder' => 'Hostinger principal'],
                        ['name' => 'host', 'label' => 'Adresse du serveur', 'value' => old('host'), 'placeholder' => '82.25.113.52'],
                        ['name' => 'username', 'label' => 'Utilisateur SSH', 'value' => old('username'), 'placeholder' => 'u123456789'],
                        ['name' => 'base_path', 'label' => 'Chemin de base', 'value' => old('base_path'), 'placeholder' => '/home/u123456789/domains'],
                        ['name' => 'ssh_key_path', 'label' => 'Chemin de la clé SSH', 'value' => old('ssh_key_path'), 'placeholder' => '/secure/keys/hostinger'],
                    ] as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
                            <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] }}" {{ in_array($field['name'], ['name', 'host', 'username']) ? 'required' : '' }} class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="{{ $field['placeholder'] }}">
                            <x-input-error :messages="$errors->get($field['name'])" class="mt-2" />
                        </div>
                    @endforeach
                    <div>
                        <label for="port" class="text-sm font-medium text-slate-700">Port SSH</label>
                        <input id="port" name="port" type="number" min="1" max="65535" value="{{ old('port', 65002) }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <x-input-error :messages="$errors->get('port')" class="mt-2" />
                    </div>
                </div>
                <button class="mt-5 w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Ajouter le serveur</button>
            </form>
        </aside>
    </div>
</x-app-layout>
