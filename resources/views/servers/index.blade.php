<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Infrastructure</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Connexions de déploiement</h1>
            <p class="mt-1 text-sm text-slate-500">Retrouvez les hébergements auxquels la plateforme peut publier vos projets.</p>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-lg font-bold text-slate-950">Connexions enregistrées</h2>
                <p class="mt-1 text-sm text-slate-500">Une connexion prête peut être réutilisée sans ressaisir le mot de passe SSH.</p>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($servers as $server)
                    <article class="ui-panel p-5 transition hover:shadow-md">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-950">{{ $server->name }}</h3>
                                    @if ($server->connection_ready)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Prête</span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Configuration requise</span>
                                    @endif
                                </div>
                                <p class="mt-1 break-all text-sm text-slate-500">{{ $server->username }}@{{ $server->host }}:{{ $server->port }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ $server->domains_count }} {{ $server->domains_count > 1 ? 'domaines associés' : 'domaine associé' }}</p>
                            </div>
                            <form method="POST" action="{{ route('servers.destroy', $server) }}" onsubmit="return confirm('Supprimer ce serveur ?')">
                                @csrf @method('DELETE')
                                <button class="ui-button-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun serveur enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('servers.store') }}" class="ui-panel p-5">
                @csrf
                <h2 class="text-base font-bold text-slate-950">Enregistrer une connexion</h2>
                <p class="mt-1 text-sm text-slate-500">Vous pouvez aussi laisser le premier déploiement créer cette connexion automatiquement.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach ([
                        ['name' => 'name', 'label' => 'Nom', 'value' => old('name'), 'placeholder' => 'Hostinger principal'],
                        ['name' => 'host', 'label' => 'Adresse du serveur', 'value' => old('host'), 'placeholder' => '82.25.113.52'],
                        ['name' => 'username', 'label' => 'Utilisateur SSH', 'value' => old('username'), 'placeholder' => 'u123456789'],
                    ] as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="ui-label">{{ $field['label'] }}</label>
                            <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ $field['value'] }}" required class="ui-input" placeholder="{{ $field['placeholder'] }}">
                            <x-input-error :messages="$errors->get($field['name'])" class="mt-2" />
                        </div>
                    @endforeach
                    <div>
                        <label for="port" class="ui-label">Port SSH</label>
                        <input id="port" name="port" type="number" min="1" max="65535" value="{{ old('port', 65002) }}" required class="ui-input">
                        <x-input-error :messages="$errors->get('port')" class="mt-2" />
                    </div>
                </div>
                <button class="ui-button-primary mt-5 w-full">Enregistrer la connexion</button>
            </form>
        </aside>
    </div>
</x-app-layout>
