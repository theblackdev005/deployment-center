<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationAndPwaTest extends TestCase
{
    public function test_application_uses_french_system_messages(): void
    {
        $this->assertSame('fr', app()->getLocale());
        $this->assertSame(
            'Ces identifiants ne correspondent pas à nos enregistrements.',
            __('auth.failed'),
        );
        $this->assertSame(
            'Le champ mot de passe doit contenir au moins 8 caractères.',
            __('validation.min.string', ['attribute' => 'mot de passe', 'min' => 8]),
        );
    }

    public function test_pwa_files_are_present_and_manifest_is_valid(): void
    {
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));

        $manifest = $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json')
            ->json();

        $this->assertSame('Deploy Center', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/dashboard', $manifest['start_url']);
    }

    public function test_installer_contains_the_required_identity_fields(): void
    {
        $this->get('/installation')
            ->assertOk()
            ->assertSee('Nom du projet')
            ->assertSee('Adresse email')
            ->assertSee('Logo')
            ->assertSee('Favicon');
    }
}
