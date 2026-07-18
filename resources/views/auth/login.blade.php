<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-950">Connexion administrateur</h1>
        <p class="mt-1 text-sm text-slate-500">Accédez au pilotage de vos sites et déploiements.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf
        <div>
            <label for="email" class="ui-label">Adresse email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="ui-input">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="ui-label">Mot de passe</label>
            <div class="relative mt-1">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" class="ui-input mt-0 pr-20">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showPassword ? 'Masquer' : 'Afficher'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex justify-end">
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#673de6] hover:text-[#5530c9]">Mot de passe oublié</a>
        </div>

        <button class="ui-button-primary mt-6 w-full">Se connecter</button>
    </form>
</x-guest-layout>
