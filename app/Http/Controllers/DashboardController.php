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
            'deploymentCount' => Deployment::count(),
            'successfulDeploymentCount' => Deployment::where('status', 'succeeded')->count(),
            'failedDeploymentCount' => Deployment::where('status', 'failed')->count(),
            'recentDeployments' => Deployment::with(['project', 'domain'])
                ->latest()
                ->limit(8)
                ->get(),
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
