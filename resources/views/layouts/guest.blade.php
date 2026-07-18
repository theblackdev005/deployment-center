<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Deploy Center') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10">
            <div class="w-full max-w-md">
                <div class="mb-6 flex items-center justify-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-md bg-emerald-600 text-sm font-bold text-white">DC</span>
                    <span class="text-xl font-semibold text-slate-950">Deploy Center</span>
                </div>
                <div class="rounded-md border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    {{ $slot }}
                </div>
                <p class="mt-5 text-center text-xs text-slate-500">Accès réservé à l’administration.</p>
            </div>
        </main>
    </body>
</html>
