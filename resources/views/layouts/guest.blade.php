<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#673de6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Deploy Center') }}">
        <title>{{ config('app.name', 'Deploy Center') }}</title>
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="{{ config('branding.favicon') }}">
        <link rel="apple-touch-icon" href="{{ config('branding.favicon') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <main class="flex min-h-screen items-center justify-center bg-[#f4f5f7] px-4 py-10">
            <div class="w-full max-w-md">
                <x-app-brand centered class="mb-6" />
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    {{ $slot }}
                </div>
                <div class="mt-5 flex items-center justify-center gap-3 text-xs text-slate-500">
                    <span>Accès réservé à l’administration.</span>
                    <button type="button" x-data x-show="!$store.pwa.installed" @click="$store.pwa.install()" class="font-semibold text-[#673de6] hover:underline">Installer l’application</button>
                </div>
            </div>
        </main>
        <x-pwa-install-dialog />
    </body>
</html>
