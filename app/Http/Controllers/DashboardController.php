<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use App\Models\Server;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'projectCount' => Project::count(),
            'serverCount' => Server::count(),
            'domainCount' => Domain::count(),
            'deploymentCount' => Deployment::count(),
            'recentDeployments' => Deployment::with(['project', 'domain'])
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
