<x-guest-layout>
    <div x-data="{ recovery: false }">
        <div class="mb-6">
            <p class="ui-eyebrow">Vérification de sécurité</p>
            <h1 class="mt-1 text-xl font-bold text-slate-950">Double authentification</h1>
            <p class="mt-2 text-sm text-slate-500" x-show="!recovery">Saisissez le code à 6 chiffres généré par votre application d’authentification.</p>
            <p class="mt-2 text-sm text-slate-500" x-show="recovery" x-cloak>Saisissez l’un de vos codes de secours. Il ne pourra être utilisé qu’une seule fois.</p>
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf
            <div x-show="!recovery">
                <label for="code" class="ui-label">Code de sécurité</label>
                <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus class="ui-input text-center font-mono text-xl tracking-[0.35em]" placeholder="000000">
            </div>
            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="ui-label">Code de secours</label>
                <input id="recovery_code" name="recovery_code" autocomplete="one-time-code" class="ui-input text-center font-mono uppercase" placeholder="XXXXX-XXXXX">
            </div>

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
            <button class="ui-button-primary mt-6 w-full">Vérifier et continuer</button>
        </form>

        <button type="button" @click="recovery = !recovery" class="mt-4 w-full text-center text-sm font-semibold text-[#673de6] hover:text-[#5530c9]" x-text="recovery ? 'Utiliser le code de l’application' : 'Utiliser un code de secours'"></button>
    </div>
</x-guest-layout>
