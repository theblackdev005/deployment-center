<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    public function index(): View
    {
        return view('deployments.index', [
            'deployments' => Deployment::with(['project', 'domain', 'user'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('deployments.create', [
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
            'domains' => Domain::with('server')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'domain_id' => ['required', 'exists:domains,id'],
        ]);

        Deployment::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'log' => 'Déploiement créé. Le moteur SSH doit encore être configuré.',
        ]);

        return redirect()->route('deployments.index')
            ->with('success', 'Déploiement préparé.');
    }
}
