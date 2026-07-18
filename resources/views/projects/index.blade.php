<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Sources</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Projets</h1>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-base font-semibold text-slate-950">Projets enregistrés</h2>
                <p class="mt-1 text-sm text-slate-500">Ajoutez simplement le lien GitHub de chaque projet.</p>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($projects as $project)
                    <article class="rounded-md border border-slate-200 bg-white p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="truncate text-sm font-semibold text-slate-950">{{ $project->name }}</h3>
                                </div>
                                <p class="mt-1 truncate text-sm text-slate-500">{{ $project->repository_url }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ $project->deployments_count }} déploiement(s)</p>
                            </div>
                            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce projet ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-medium text-red-600 hover:text-red-700">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun projet enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('projects.store') }}" class="rounded-md border border-slate-200 bg-white p-5">
                @csrf
                <h2 class="text-base font-semibold text-slate-950">Ajouter un projet</h2>
                <p class="mt-1 text-sm text-slate-500">La branche principale sera utilisée automatiquement.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="name" class="text-sm font-medium text-slate-700">Nom du projet</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Finance1">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="repository_url" class="text-sm font-medium text-slate-700">URL du dépôt GitHub</label>
                        <input id="repository_url" name="repository_url" type="url" value="{{ old('repository_url') }}" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="https://github.com/...">
                        <x-input-error :messages="$errors->get('repository_url')" class="mt-2" />
                    </div>
                </div>

                <button class="mt-5 w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Ajouter le projet</button>
            </form>
        </aside>
    </div>
</x-app-layout>
