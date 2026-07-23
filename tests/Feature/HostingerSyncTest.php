<?php

namespace Tests\Feature;

use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerHostingPlan;
use App\Models\HostingerWebsite;
use App\Models\User;
use App\Notifications\HostingerProblemDetected;
use App\Services\HostingerAlertService;
use App\Services\HostingerSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
            '*/api/hosting/v1/orders*' => Http::response([
                'data' => [[
                    'id' => 20,
                    'subscription_id' => 'hosting-subscription-1',
                    'created_at' => '2025-01-01T00:00:00Z',
                    'plan' => ['name' => 'Business Web Hosting'],
                    'status' => 'active',
                ]],
                'meta' => ['current_page' => 1, 'per_page' => 100, 'total' => 1],
            ]),
            '*/api/billing/v1/subscriptions' => Http::response([[
                'id' => 'hosting-subscription-1',
                'name' => 'Business Web Hosting',
                'status' => 'active',
                'created_at' => '2025-01-01T00:00:00Z',
                'expires_at' => null,
                'next_billing_at' => '2027-06-01T00:00:00Z',
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
        $this->assertDatabaseHas('hostinger_hosting_plans', [
            'hostinger_account_id' => $account->id,
            'order_id' => 20,
            'subscription_id' => 'hosting-subscription-1',
            'name' => 'Business Web Hosting',
        ]);
        $this->assertSame('2027-06-01', HostingerHostingPlan::first()->expires_at->format('Y-m-d'));
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

        HostingerDomain::where('hostinger_account_id', $account->id)->update(['status' => 'active']);
        HostingerWebsite::where('hostinger_account_id', $account->id)->update(['is_enabled' => true]);
        $service->reconcile($account->fresh());

        $this->assertSame(0, HostingerAlert::where('status', 'open')->count());
    }

    public function test_expiration_alerts_start_two_months_before_the_expiration_date(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-07-18 12:00:00');

        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
            'status' => 'connected',
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'expires-soon.com',
            'status' => 'active',
            'expires_at' => now()->addMonthsNoOverflow(2),
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'expires-later.com',
            'status' => 'active',
            'expires_at' => now()->addMonthsNoOverflow(2)->addDay(),
        ]);

        app(HostingerAlertService::class)->reconcile($account);

        $this->assertDatabaseHas('hostinger_alerts', [
            'domain' => 'expires-soon.com',
            'type' => 'domain_expiring',
            'status' => 'open',
        ]);
        $this->assertDatabaseMissing('hostinger_alerts', [
            'domain' => 'expires-later.com',
            'type' => 'domain_expiring',
        ]);

        Carbon::setTestNow();
    }

    public function test_inventory_displays_the_real_resource_statuses(): void
    {
        Carbon::setTestNow('2026-07-23 12:00:00');
        $user = User::factory()->create();
        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
            'status' => 'connected',
        ]);

        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'suspended-example.com',
            'status' => 'suspended',
            'expires_at' => now()->addYear(),
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'expired-example.com',
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
        HostingerWebsite::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'disabled-example.com',
            'is_enabled' => false,
        ]);

        $this->actingAs($user)->get(route('hostinger.index'))
            ->assertOk()
            ->assertSee('Suspendu')
            ->assertSee('Expiré')
            ->assertSee('Site désactivé');

        Carbon::setTestNow();
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
            ->assertSee('Connecter un compte');

        $response = $this->actingAs($user)->post(route('hostinger.accounts.store'), [
            'name' => '',
            'email' => 'admin@example.com',
            'api_token' => 'hostinger-secret-token',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertArrayNotHasKey('api_token', session('_old_input', []));
    }

    public function test_each_active_account_is_available_in_the_account_summary_selector(): void
    {
        $user = User::factory()->create();
        $first = HostingerAccount::create([
            'name' => 'Compte Alpha',
            'email' => 'alpha@example.com',
            'api_token' => 'alpha-token',
            'status' => 'connected',
            'last_synced_at' => '2026-07-18 10:00:00',
        ]);
        $second = HostingerAccount::create([
            'name' => 'Compte Beta',
            'email' => 'beta@example.com',
            'api_token' => 'beta-token',
            'status' => 'connected',
            'last_synced_at' => '2026-07-18 11:00:00',
        ]);
        HostingerHostingPlan::create([
            'hostinger_account_id' => $first->id,
            'order_id' => 101,
            'name' => 'Alpha Hosting',
            'status' => 'active',
            'expires_at' => '2027-01-10 00:00:00',
        ]);
        HostingerHostingPlan::create([
            'hostinger_account_id' => $second->id,
            'order_id' => 202,
            'name' => 'Beta Hosting',
            'status' => 'active',
            'expires_at' => '2027-02-20 00:00:00',
        ]);

        $this->actingAs($user)->get(route('hostinger.index'))
            ->assertOk()
            ->assertSee('Compte Alpha')
            ->assertSee('alpha@example.com')
            ->assertSee('Compte Beta')
            ->assertSee('beta@example.com')
            ->assertSee('10/01/2027')
            ->assertSee('20/02/2027')
            ->assertSee('Choisissez le compte Hostinger que vous souhaitez consulter.');

        $this->actingAs($user)->get(route('hostinger.accounts.index'))
            ->assertOk()
            ->assertSee('Ouvrir')
            ->assertSee(route('hostinger.index', ['account' => $first->id]).'#domains', false)
            ->assertSee(route('hostinger.index', ['account' => $second->id]).'#domains', false);

        $this->actingAs($user)->get(route('hostinger.index', ['account' => $second->id]))
            ->assertOk()
            ->assertSee("account: '".$second->id."'", false);
    }

    public function test_account_can_be_paused_and_reactivated_without_losing_its_data(): void
    {
        $user = User::factory()->create();
        $account = HostingerAccount::create([
            'name' => 'Compte principal',
            'api_token' => 'hostinger-secret-token',
            'status' => 'connected',
        ]);
        HostingerDomain::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'status' => 'suspended',
        ]);
        app(HostingerAlertService::class)->reconcile($account);

        $this->actingAs($user)
            ->patch(route('hostinger.accounts.status', $account), ['is_active' => false])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($account->fresh()->is_active);
        $this->assertDatabaseHas('hostinger_domains', ['domain' => 'example.com']);
        $this->assertDatabaseMissing('hostinger_alerts', [
            'hostinger_account_id' => $account->id,
            'status' => 'open',
        ]);
        $this->actingAs($user)
            ->get(route('hostinger.index'))
            ->assertOk()
            ->assertDontSee('example.com');
        $this->actingAs($user)
            ->get(route('hostinger.accounts.index'))
            ->assertOk()
            ->assertSee('Informations masquées jusqu’à la réactivation du compte.');

        $this->actingAs($user)
            ->post(route('hostinger.accounts.sync', $account))
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->patch(route('hostinger.accounts.status', $account), ['is_active' => true])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($account->fresh()->is_active);
    }
}
