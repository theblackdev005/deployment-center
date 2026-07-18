<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-emerald-700">Publication</p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-950">Déployer un projet</h1>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-md border border-slate-200 bg-white p-5 sm:p-7">
            <div class="border-b border-slate-200 pb-5">
                <h2 class="text-base font-semibold text-slate-950">Informations du déploiement</h2>
                <p class="mt-1 text-sm text-slate-500">Renseignez les mêmes informations que lorsque vous demandez un dépôt manuel.</p>
            </div>

            <div class="mt-6">
                @include('deployments._form')
            </div>
        </div>
    </div>
</x-app-layout>
