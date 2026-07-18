<div
    x-data
    x-show="$store.pwa.helpOpen"
    x-cloak
    @keydown.escape.window="$store.pwa.helpOpen = false"
    class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="pwa-install-title"
>
    <button type="button" class="absolute inset-0 bg-slate-950/45" @click="$store.pwa.helpOpen = false" aria-label="Fermer"></button>
    <div class="relative w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 shadow-xl sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#f0edff] text-[#673de6]">
                    <i data-lucide="smartphone" class="h-5 w-5" aria-hidden="true"></i>
                </span>
                <div>
                    <h2 id="pwa-install-title" class="text-lg font-bold text-slate-950">Installer {{ config('app.name', 'Deploy Center') }}</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Accédez plus rapidement à votre espace de gestion.</p>
                </div>
            </div>
            <button type="button" @click="$store.pwa.helpOpen = false" class="flex h-9 w-9 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100" aria-label="Fermer">
                <i data-lucide="x" class="h-5 w-5" aria-hidden="true"></i>
            </button>
        </div>

        <div class="mt-5 rounded-md bg-slate-50 p-4 text-sm leading-6 text-slate-700">
            <template x-if="$store.pwa.isAppleMobile">
                <p>Dans Safari, ouvrez le menu <strong>Partager</strong>, puis choisissez <strong>Sur l’écran d’accueil</strong>.</p>
            </template>
            <template x-if="!$store.pwa.isAppleMobile">
                <p>Utilisez l’option <strong>Installer l’application</strong> de votre navigateur. Sur Mac avec Safari, choisissez <strong>Fichier</strong>, puis <strong>Ajouter au Dock</strong>.</p>
            </template>
        </div>

        <button type="button" @click="$store.pwa.helpOpen = false" class="ui-button-primary mt-5 w-full">J’ai compris</button>
    </div>
</div>
