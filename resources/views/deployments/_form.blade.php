<form method="POST" action="{{ route('deployments.store') }}" x-data="{ showPassword: false, submitting: false }" @submit="submitting = true">
    @csrf

    <div class="grid gap-5 lg:grid-cols-2">
        <div>
            <label for="project_id" class="ui-label">Projet</label>
            <select id="project_id" name="project_id" required class="ui-input">
                <option value="">Sélectionner un projet</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
        </div>

        <div>
            <label for="domain" class="ui-label">Domaine de destination</label>
            <input id="domain" name="domain" value="{{ old('domain') }}" required autocomplete="off" class="ui-input" placeholder="exemple.com">
            <x-input-error :messages="$errors->get('domain')" class="mt-2" />
        </div>

        <div>
            <label for="ssh_command" class="ui-label">Accès SSH</label>
            <input id="ssh_command" name="ssh_command" value="{{ old('ssh_command') }}" required autocomplete="off" spellcheck="false" class="ui-input font-mono" placeholder="ssh -p 65002 utilisateur@serveur">
            <p class="mt-1.5 text-xs text-slate-500">Utilisez la commande affichée dans l’espace d’hébergement.</p>
            <x-input-error :messages="$errors->get('ssh_command')" class="mt-2" />
        </div>

        <div>
            <label for="ssh_password" class="ui-label">Mot de passe SSH</label>
            <div class="relative mt-1">
                <input id="ssh_password" name="ssh_password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" class="ui-input mt-0 pr-20" placeholder="Saisir le mot de passe">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showPassword ? 'Masquer' : 'Afficher'"></button>
            </div>
            <p class="mt-1.5 text-xs text-slate-500">Utilisé uniquement pendant cette publication.</p>
            <x-input-error :messages="$errors->get('ssh_password')" class="mt-2" />
        </div>
    </div>

    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500">La protection intégrée empêche le remplacement d’un site déjà en service.</p>
        <button :disabled="submitting || {{ $projects->isEmpty() ? 'true' : 'false' }}" class="ui-button-primary">
            <i data-lucide="rocket" class="h-4 w-4" aria-hidden="true"></i>
            <span x-show="!submitting">Publier le projet</span>
            <span x-show="submitting" x-cloak>Publication en cours...</span>
        </button>
    </div>
</form>
