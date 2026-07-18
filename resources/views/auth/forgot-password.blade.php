<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-bold text-slate-950">Mot de passe oublié</h1>
        <p class="mt-1 text-sm text-slate-500">Indiquez votre adresse email pour recevoir un lien de réinitialisation.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Adresse email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Envoyer le lien
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
