<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Publication</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Nouveau déploiement</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('deployments.store') }}" class="rounded-md border border-slate-200 bg-white p-5 sm:p-7">
            @csrf
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-base font-semibold text-slate-950">Choisir la source et la destination</h2>
                <p class="mt-1 text-sm text-slate-500">Cette première version prépare l’opération. L’exécution SSH sera activée à l’étape suivante.</p>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="project_id" class="text-sm font-medium text-slate-700">Projet</label>
                    <select id="project_id" name="project_id" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Choisir un projet</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }} · {{ $project->branch }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                </div>
                <div>
                    <label for="domain_id" class="text-sm font-medium text-slate-700">Domaine</label>
                    <select id="domain_id" name="domain_id" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Choisir un domaine</option>
                        @foreach ($domains as $domain)
                            <option value="{{ $domain->id }}" @selected(old('domain_id') == $domain->id)>{{ $domain->name }} · {{ $domain->server->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('domain_id')" class="mt-2" />
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('deployments.index') }}" class="rounded-md border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</a>
                <button class="rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700" @disabled($projects->isEmpty() || $domains->isEmpty())>Préparer le déploiement</button>
            </div>
        </form>
    </div>
</x-app-layout>
