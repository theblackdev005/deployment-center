<?php

namespace Tests\Feature;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use App\Models\Server;
use App\Models\User;
use App\Services\DeploymentRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DeploymentFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_ssh_password_is_never_flashed_to_the_session(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Finance1',
            'slug' => 'finance1',
            'repository_url' => 'https://github.com/example/finance1.git',
            'branch' => 'main',
        ]);

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'project_id' => $project->id,
            'ssh_command' => 'commande invalide',
            'ssh_password' => 'SecretPassword',
            'domain' => 'example.com',
        ]);

        $response->assertSessionHasErrors('ssh_command');
        $this->assertArrayNotHasKey('ssh_password', session('_old_input', []));
    }

    public function test_deployment_form_is_restricted_to_authenticated_users(): void
    {
        $this->get(route('deployments.create'))->assertRedirect(route('login'));
    }

    public function test_failed_deployment_can_be_retried_without_losing_history(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Finance1',
            'slug' => 'finance1',
            'repository_url' => 'https://github.com/example/finance1.git',
            'branch' => 'main',
        ]);
        $server = Server::create([
            'name' => 'Serveur test',
            'host' => 'example.test',
            'port' => 22,
            'username' => 'tester',
        ]);
        $domain = Domain::create([
            'server_id' => $server->id,
            'name' => 'example.com',
            'document_root' => '/home/tester/domains/example.com/public_html',
        ]);
        $failed = Deployment::create([
            'project_id' => $project->id,
            'domain_id' => $domain->id,
            'user_id' => $user->id,
            'status' => 'failed',
            'error_message' => 'Erreur précédente',
        ]);

        $runner = Mockery::mock(DeploymentRunner::class);
        $runner->shouldReceive('run')->once()->andReturnUsing(function (Deployment $deployment): void {
            $deployment->update(['status' => 'succeeded']);
        });
        $this->app->instance(DeploymentRunner::class, $runner);

        $response = $this->actingAs($user)->post(route('deployments.retry', $failed));

        $retry = Deployment::latest('id')->firstOrFail();
        $response->assertRedirect(route('deployments.show', $retry));
        $this->assertNotSame($failed->id, $retry->id);
        $this->assertSame('failed', $failed->fresh()->status);
        $this->assertSame('succeeded', $retry->fresh()->status);
    }
}
