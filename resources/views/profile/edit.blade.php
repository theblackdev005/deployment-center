<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Administration</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Mon compte</h1>
            <p class="mt-1 text-sm text-slate-500">Gérez vos informations de connexion et la sécurité du compte.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="ui-panel p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="ui-panel p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="ui-panel p-5 sm:p-7">
                @include('profile.partials.two-factor-authentication-form')
            </div>

            <div class="ui-panel p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
