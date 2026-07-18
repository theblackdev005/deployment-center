<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#673de6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Deploy Center') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="{{ config('branding.favicon') }}">
        <link rel="apple-touch-icon" href="{{ config('branding.favicon') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="min-h-screen bg-[#f4f5f7]">
            @include('layouts.navigation')

            <div class="min-w-0 lg:pl-64">
                @isset($header)
                    <header class="border-b border-slate-200 bg-white">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-6 lg:px-8">
                    @php
                        $statusMessages = [
                            'password-updated' => 'Votre mot de passe a été mis à jour.',
                            'profile-updated' => 'Votre profil a été mis à jour.',
                            'verification-link-sent' => 'Un nouveau lien de vérification vous a été envoyé par email.',
                            'two-factor-enabled' => 'La double authentification est maintenant active.',
                            'two-factor-disabled' => 'La double authentification a été désactivée.',
                            'two-factor-recovery-codes-regenerated' => 'De nouveaux codes de récupération ont été générés.',
                        ];
                        $status = session('status');
                        $statusMessage = is_string($status) ? ($statusMessages[$status] ?? $status) : null;
                    @endphp

                    <div class="space-y-3">
                    @if (session('success'))
                        <x-flash-message type="success" :message="session('success')" auto-dismiss />
                    @endif
                    @if (session('error'))
                        <x-flash-message type="error" :message="session('error')" />
                    @endif
                    @if (session('warning'))
                        <x-flash-message type="warning" :message="session('warning')" />
                    @endif
                    @if ($statusMessage)
                        <x-flash-message type="success" :message="$statusMessage" auto-dismiss />
                    @endif
                    </div>
                </div>

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-pwa-install-dialog />
    </body>
</html>
