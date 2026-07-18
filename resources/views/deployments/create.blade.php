<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="ui-eyebrow">Nouvelle publication</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Déployer un projet</h1>
            <p class="mt-1 text-sm text-slate-500">Envoyez la dernière version du projet vers son domaine.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="ui-panel p-5 sm:p-7">
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-base font-bold text-slate-950">Destination et accès</h2>
                <p class="mt-1 text-sm text-slate-500">Sélectionnez le projet puis indiquez l’accès sécurisé à l’hébergement.</p>
            </div>

            <div class="mt-6">
                @include('deployments._form')
            </div>
        </div>
    </div>
</x-app-layout>
