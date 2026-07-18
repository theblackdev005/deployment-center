<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-950">Connexion</h1>
        <p class="mt-1 text-sm text-slate-500">Accédez à la gestion des déploiements.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf
        <div>
            <label for="email" class="text-sm font-medium text-slate-700">Adresse email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="text-sm font-medium text-slate-700">Mot de passe</label>
            <div class="relative mt-1">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" class="block w-full rounded-md border-slate-300 pr-20 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800" x-text="showPassword ? 'Masquer' : 'Afficher'"></button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between gap-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span class="ms-2 text-sm text-slate-600">Rester connecté</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Mot de passe oublié</a>
        </div>

        <button class="mt-6 w-full rounded-md bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Se connecter</button>
    </form>
</x-guest-layout>
