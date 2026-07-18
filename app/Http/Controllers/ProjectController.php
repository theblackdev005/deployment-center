<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::withCount('deployments')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'repository_url' => ['required', 'url', 'max:500'],
            'github_token' => ['nullable', 'string', 'max:4096'],
        ]);

        $repositoryHost = strtolower((string) parse_url($validated['repository_url'], PHP_URL_HOST));

        if (! in_array($repositoryHost, ['github.com', 'www.github.com'], true)
            || parse_url($validated['repository_url'], PHP_URL_USER)
            || parse_url($validated['repository_url'], PHP_URL_PASS)) {
            throw ValidationException::withMessages([
                'repository_url' => 'Utilisez un lien GitHub sans mot de passe ni jeton intégré.',
            ]);
        }

        Project::create([
            ...$validated,
            'github_token' => filled($validated['github_token'] ?? null) ? trim($validated['github_token']) : null,
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'branch' => 'main',
            'excluded_paths' => ['.git', '.env', 'storage/logs/*', 'storage/framework/sessions/*'],
        ]);

        return back()->with('success', 'Projet ajouté.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'github_token' => ['nullable', 'string', 'max:4096'],
            'remove_github_token' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_github_token')) {
            $project->update(['github_token' => null]);

            return back()->with('success', 'L’accès privé GitHub a été supprimé.');
        }

        if (blank($validated['github_token'] ?? null)) {
            return back()->withErrors(['github_token' => 'Saisissez un jeton GitHub ou choisissez de supprimer l’accès existant.']);
        }

        $project->update(['github_token' => trim($validated['github_token'])]);

        return back()->with('success', 'L’accès au dépôt privé a été enregistré de manière chiffrée.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        if ($project->deployments()->exists()) {
            return back()->with('error', 'Ce projet possède un historique de déploiement.');
        }

        $project->delete();

        return back()->with('success', 'Projet supprimé.');
    }
}
