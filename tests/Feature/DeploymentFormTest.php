<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
