<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Bibliothèque de projets</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Projets GitHub</h1>
            <p class="mt-1 text-sm text-slate-500">Gérez les projets autorisés à être publiés depuis la plateforme.</p>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:px-8">
        <section class="min-w-0">
            <div class="border-b border-slate-200 pb-3">
                <h2 class="text-lg font-bold text-slate-950">Projets disponibles</h2>
                <p class="mt-1 text-sm text-slate-500">Chaque projet utilise automatiquement sa branche principale.</p>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($projects as $project)
                    <article class="ui-panel p-5 transition hover:shadow-md">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-[#ebe7ff] text-[#673de6]"><i data-lucide="folder-git-2" class="h-4 w-4" aria-hidden="true"></i></span>
                                    <h3 class="truncate text-sm font-bold text-slate-950">{{ $project->name }}</h3>
                                </div>
                                <p class="mt-1 truncate text-sm text-slate-500">{{ $project->repository_url }}</p>
                                <p class="mt-2 text-xs text-slate-400">{{ $project->deployments_count }} {{ $project->deployments_count > 1 ? 'publications' : 'publication' }}</p>
                                <p class="mt-1 text-xs font-semibold {{ filled($project->github_token) ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ filled($project->github_token) ? 'Accès privé configuré' : 'Dépôt public ou accès privé à configurer' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="ui-button-secondary" onclick="document.getElementById('github-access-{{ $project->id }}').showModal()">Accès GitHub</button>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce projet ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ui-button-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </article>
                    <dialog id="github-access-{{ $project->id }}" class="w-[min(92vw,480px)] rounded-lg border border-slate-200 p-0 shadow-xl backdrop:bg-slate-950/40">
                        <form method="POST" action="{{ route('projects.update', $project) }}" class="p-5">
                            @csrf
                            @method('PATCH')
                            <h3 class="text-base font-bold text-slate-950">Accès GitHub privé</h3>
                            <p class="mt-1 text-sm text-slate-500">Ajoutez un jeton GitHub avec un accès en lecture au contenu de ce dépôt.</p>
                            <div class="mt-5">
                                <label for="github_token_{{ $project->id }}" class="ui-label">Jeton GitHub</label>
                                <input id="github_token_{{ $project->id }}" name="github_token" type="password" autocomplete="new-password" class="ui-input" placeholder="github_pat_…">
                                <p class="mt-2 text-xs text-slate-500">Le jeton est chiffré avant son enregistrement.</p>
                            </div>
                            @if(filled($project->github_token))
                                <label class="mt-4 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_github_token" value="1" class="rounded border-slate-300"> Supprimer le jeton existant</label>
                            @endif
                            <div class="mt-5 flex justify-end gap-2">
                                <button type="button" class="ui-button-secondary" onclick="this.closest('dialog').close()">Annuler</button>
                                <button class="ui-button-primary">Enregistrer</button>
                            </div>
                        </form>
                    </dialog>
                @empty
                    <div class="rounded-md border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500">Aucun projet enregistré.</div>
                @endforelse
            </div>
        </section>

        <aside>
            <form method="POST" action="{{ route('projects.store') }}" class="ui-panel p-5">
                @csrf
                <h2 class="text-base font-bold text-slate-950">Ajouter un projet</h2>
                <p class="mt-1 text-sm text-slate-500">Renseignez le dépôt GitHub à publier.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="name" class="ui-label">Nom du projet</label>
                        <input id="name" name="name" value="{{ old('name') }}" required class="ui-input" placeholder="Finance1">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="repository_url" class="ui-label">Adresse du dépôt GitHub</label>
                        <input id="repository_url" name="repository_url" type="url" value="{{ old('repository_url') }}" required class="ui-input" placeholder="https://github.com/organisation/projet">
                        <x-input-error :messages="$errors->get('repository_url')" class="mt-2" />
                    </div>
                    <div>
                        <label for="github_token" class="ui-label">Jeton GitHub <span class="font-normal text-slate-400">(dépôt privé)</span></label>
                        <input id="github_token" name="github_token" type="password" autocomplete="new-password" class="ui-input" placeholder="Facultatif">
                        <p class="mt-2 text-xs text-slate-500">Nécessaire uniquement pour un dépôt privé.</p>
                        <x-input-error :messages="$errors->get('github_token')" class="mt-2" />
                    </div>
                </div>

                <button class="ui-button-primary mt-5 w-full">Ajouter le projet</button>
            </form>
        </aside>
    </div>
</x-app-layout>
