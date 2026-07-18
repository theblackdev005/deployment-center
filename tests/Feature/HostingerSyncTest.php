<?php

namespace Tests\Feature;

use App\Models\HostingerAccount;
use App\Models\User;
use App\Services\HostingerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HostingerSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_token_is_encrypted_at_rest(): void
    {
        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
        ]);

        $storedToken = DB::table('hostinger_accounts')->where('id', $account->id)->value('api_token');

        $this->assertNotSame('hostinger-secret-token', $storedToken);
        $this->assertSame('hostinger-secret-token', $account->fresh()->api_token);
    }

    public function test_sync_imports_websites_domains_subscriptions_and_php_version(): void
    {
        Http::fake([
            '*/api/hosting/v1/websites*' => Http::response([
                'data' => [[
                    'domain' => 'example.com',
                    'vhost_type' => 'main',
                    'is_enabled' => true,
                    'username' => 'u123456789',
                    'client_id' => 10,
                    'order_id' => 20,
                    'created_at' => '2026-01-10T10:00:00Z',
                    'root_directory' => '/home/u123456789/domains/example.com/public_html',
                ]],
                'meta' => ['current_page' => 1, 'per_page' => 100, 'total' => 1],
            ]),
            '*/api/domains/v1/portfolio' => Http::response([[
                'id' => 55,
                'domain' => 'example.com',
                'type' => 'domain',
                'status' => 'active',
                'created_at' => '2025-01-01T00:00:00Z',
                'expires_at' => '2027-01-01T00:00:00Z',
            ]]),
            '*/api/billing/v1/subscriptions' => Http::response([[
                'id' => 'subscription-1',
                'name' => 'Hébergement Business',
                'status' => 'active',
                'is_auto_renewed' => true,
                'expires_at' => '2027-01-01T00:00:00Z',
            ]]),
            '*/api/hosting/v1/accounts/u123456789/websites/example.com/php/details' => Http::response([
                'php_version' => '8.3',
                'php_version_full' => '8.3.30',
            ]),
        ]);

        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
        ]);

        app(HostingerSyncService::class)->sync($account);

        $this->assertDatabaseHas('hostinger_websites', [
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'username' => 'u123456789',
            'php_version_full' => '8.3.30',
        ]);
        $this->assertDatabaseHas('hostinger_domains', [
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('hostinger_subscriptions', [
            'hostinger_account_id' => $account->id,
            'external_id' => 'subscription-1',
            'is_auto_renewed' => true,
        ]);
        $this->assertSame('connected', $account->fresh()->status);
    }

    public function test_hostinger_pages_require_authentication_and_tokens_are_not_flashed(): void
    {
        $this->get(route('hostinger.index'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('hostinger.index'))
            ->assertOk()
            ->assertSee('Connectez votre premier compte Hostinger');
        $this->actingAs($user)->get(route('hostinger.accounts.index'))
            ->assertOk()
            ->assertSee('Ajouter un compte');

        $response = $this->actingAs($user)->post(route('hostinger.accounts.store'), [
            'name' => '',
            'api_token' => 'hostinger-secret-token',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertArrayNotHasKey('api_token', session('_old_input', []));
    }
}
