<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\HostingerAccount;
use App\Models\HostingerWebsite;
use App\Models\ManagedSite;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSiteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_and_sites_page_requires_authentication(): void
    {
        $this->get(route('clients-sites.index'))->assertRedirect(route('login'));
    }

    public function test_customer_and_site_can_be_managed_from_the_portfolio(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Finance One',
            'slug' => 'finance-one',
            'repository_url' => 'git@github.com:example/finance-one.git',
            'branch' => 'main',
        ]);

        $this->actingAs($user)->post(route('clients.store'), [
            'name' => 'Groupe Exemple',
        ])->assertRedirect()->assertSessionHas('success');

        $customer = Customer::firstOrFail();

        $this->actingAs($user)->post(route('managed-sites.store'), [
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'domain' => 'htpp://www.example.com/espace-client?source=test',
        ])->assertRedirect()->assertSessionHas('success');

        $site = ManagedSite::firstOrFail();
        $this->assertSame('example.com', $site->domain);
        $this->assertSame('Example', $site->name);

        $this->actingAs($user)->post(route('managed-sites.store'), [
            'customer_id' => $customer->id,
            'domain' => 'https://www.second-example.fr/',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(2, $customer->sites()->count());
        $this->assertDatabaseHas('managed_sites', [
            'customer_id' => $customer->id,
            'name' => 'Second Example',
            'domain' => 'second-example.fr',
        ]);

        $account = HostingerAccount::create([
            'name' => 'Compte Hostinger principal',
            'api_token' => 'secret-token',
            'status' => 'connected',
        ]);
        HostingerWebsite::create([
            'hostinger_account_id' => $account->id,
            'domain' => 'example.com',
            'username' => 'u123456789',
            'is_enabled' => true,
        ]);

        $this->actingAs($user)->get(route('clients-sites.index'))
            ->assertOk()
            ->assertSee('Groupe Exemple')
            ->assertSee('Example')
            ->assertSee('example.com')
            ->assertSee('Compte Hostinger principal')
            ->assertSee('Finance One');

        $this->actingAs($user)->patch(route('managed-sites.update', $site), [
            'customer_id' => $customer->id,
            'project_id' => $project->id,
            'domain' => 'example.com',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('managed_sites', [
            'id' => $site->id,
            'name' => 'Example',
            'status' => 'active',
        ]);
    }

    public function test_customer_with_sites_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Client protégé']);
        ManagedSite::create([
            'customer_id' => $customer->id,
            'name' => 'Site protégé',
            'domain' => 'protected.example',
            'status' => 'active',
        ]);

        $this->actingAs($user)->delete(route('clients.destroy', $customer))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
