<?php

namespace Tests\Feature;

use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_authenticated_administrator_can_open_dashboard(): void
    {
        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'secret-token',
            'status' => 'connected',
            'last_synced_at' => now(),
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
        ]);
        HostingerAlert::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'type' => 'domain_expiring',
            'severity' => 'warning',
            'title' => 'Expiration prochaine',
            'message' => 'Le domaine arrive à échéance.',
            'status' => 'open',
            'fingerprint' => 'domain-expiring-example',
            'detected_at' => now(),
            'last_detected_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Vue d’ensemble')
            ->assertSee('Parc Hostinger')
            ->assertSee('1 compte actif')
            ->assertSee('1 domaine suivi')
            ->assertSee('1 alerte à vérifier')
            ->assertSee(route('hostinger.accounts.sync-all'), false);
    }
}
