<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $name = config('app.name', 'Deploy Center');

        return response()->json([
            'name' => $name,
            'short_name' => mb_strimwidth($name, 0, 18, ''),
            'description' => 'Gestion sécurisée des sites, domaines et déploiements.',
            'lang' => 'fr',
            'start_url' => '/dashboard',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#f4f5f7',
            'theme_color' => '#673de6',
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Vue d’ensemble', 'short_name' => 'Accueil', 'url' => '/dashboard'],
                ['name' => 'Clients et sites', 'short_name' => 'Sites', 'url' => '/clients-sites'],
                ['name' => 'Parc Hostinger', 'short_name' => 'Hostinger', 'url' => '/hostinger'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
