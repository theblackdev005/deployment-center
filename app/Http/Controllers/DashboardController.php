<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $activeHostingerAccounts = HostingerAccount::where('is_active', true)->get();
        $activeHostingerAccountIds = $activeHostingerAccounts->pluck('id');
        $hostingerDomainCount = HostingerDomain::whereIn('hostinger_account_id', $activeHostingerAccountIds)
            ->pluck('domain')
            ->merge(HostingerWebsite::whereIn('hostinger_account_id', $activeHostingerAccountIds)->pluck('domain'))
            ->unique()
            ->count();

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
            'activeHostingerAccountCount' => $activeHostingerAccounts->count(),
            'hostingerDomainCount' => $hostingerDomainCount,
            'hostingerOpenAlertCount' => HostingerAlert::where('status', 'open')
                ->whereIn('hostinger_account_id', $activeHostingerAccountIds)
                ->count(),
            'hostingerLastSyncedAt' => $activeHostingerAccounts->pluck('last_synced_at')->filter()->sortDesc()->first(),
        ]);
    }
}
