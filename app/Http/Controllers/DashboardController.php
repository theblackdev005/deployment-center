<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'projectCount' => Project::count(),
            'domainCount' => Domain::count(),
            'successfulDeploymentCount' => Deployment::where('status', 'succeeded')->count(),
            'failedDeploymentCount' => Deployment::where('status', 'failed')
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('deployments as newer_deployments')
                        ->whereColumn('newer_deployments.domain_id', 'deployments.domain_id')
                        ->whereColumn('newer_deployments.id', '>', 'deployments.id');
                })
                ->count(),
            'recentDeployments' => Deployment::with(['project', 'domain'])
                ->latest()
                ->limit(5)
                ->get(),
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
