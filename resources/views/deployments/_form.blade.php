<form method="POST" action="{{ route('deployments.store') }}" x-data="{ showPassword: false, submitting: false }" @submit="submitting = true">
    @csrf

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="project_id" class="text-sm font-medium text-slate-700">Projet à déposer</label>
            <select id="project_id" name="project_id" required class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Choisir le projet</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
        </div>

        <div>
            <label for="domain" class="text-sm font-medium text-slate-700">Nom de domaine</label>
            <input id="domain" name="domain" value="{{ old('domain') }}" required autocomplete="off" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="exemple.com">
            <x-input-error :messages="$errors->get('domain')" class="mt-2" />
        </div>
    </div>

    <div class="mt-5">
        <label for="ssh_command" class="text-sm font-medium text-slate-700">Chemin SSH</label>
        <input id="ssh_command" name="ssh_command" value="{{ old('ssh_command') }}" required autocomplete="off" spellcheck="false" class="mt-1 block w-full rounded-md border-slate-300 font-mono text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="ssh -p 65002 utilisateur@82.25.113.52">
        <p class="mt-1.5 text-xs text-slate-500">Collez la commande SSH fournie par Hostinger.</p>
        <x-input-error :messages="$errors->get('ssh_command')" class="mt-2" />
    </div>

    <div class="mt-5">
        <label for="ssh_password" class="text-sm font-medium text-slate-700">Mot de passe SSH</label>
        <div class="relative mt-1">
            <input id="ssh_password" name="ssh_password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" class="block w-full rounded-md border-slate-300 pr-20 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Nécessaire uniquement à la première connexion">
            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showPassword ? 'Masquer' : 'Afficher'"></button>
        </div>
        <p class="mt-1.5 text-xs text-slate-500">Le mot de passe n’est pas enregistré.</p>
        <x-input-error :messages="$errors->get('ssh_password')" class="mt-2" />
    </div>

    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">Un domaine contenant déjà un site ne sera jamais écrasé.</p>
        <button :disabled="submitting || {{ $projects->isEmpty() ? 'true' : 'false' }}" class="rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
            <span x-show="!submitting">Déployer le projet</span>
            <span x-show="submitting" x-cloak>Déploiement en cours...</span>
        </button>
    </div>
</form>
