<?php

namespace Tests\Feature;

use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use App\Models\User;
use App\Notifications\HostingerProblemDetected;
use App\Services\HostingerAlertService;
use App\Services\HostingerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
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

    public function test_sync_imports_websites_and_domains(): void
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
        ]);
        $this->assertDatabaseHas('hostinger_domains', [
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);
        $this->assertSame('connected', $account->fresh()->status);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('hostinger.index'))
            ->assertOk()
            ->assertSee('example.com')
            ->assertSee('01/01/2025');
    }

    public function test_problems_create_one_admin_notification_until_their_state_changes(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
            'status' => 'connected',
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'suspended-example.com',
            'status' => 'suspended',
        ]);
        HostingerWebsite::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'disabled-example.com',
            'is_enabled' => false,
        ]);

        $service = app(HostingerAlertService::class);
        $service->reconcile($account);
        $service->reconcile($account->fresh());

        $this->assertDatabaseCount('hostinger_alerts', 2);
        $this->assertSame(2, HostingerAlert::where('status', 'open')->count());
        $this->assertCount(2, Notification::sent($admin, HostingerProblemDetected::class));
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
