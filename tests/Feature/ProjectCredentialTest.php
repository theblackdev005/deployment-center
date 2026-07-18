<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectCredentialTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_token_is_encrypted_and_hidden(): void
    {
        $project = Project::create([
            'name' => 'Projet privé',
            'slug' => 'projet-prive',
            'repository_url' => 'https://github.com/example/private-repository',
            'github_token' => 'github_pat_private_value',
            'branch' => 'main',
            'is_active' => true,
        ]);

        $storedValue = DB::table('projects')->where('id', $project->id)->value('github_token');

        $this->assertNotSame('github_pat_private_value', $storedValue);
        $this->assertSame('github_pat_private_value', $project->fresh()->github_token);
        $this->assertArrayNotHasKey('github_token', $project->fresh()->toArray());
    }
}
