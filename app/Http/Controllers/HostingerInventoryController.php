<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerHostingPlan;
use App\Models\HostingerWebsite;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HostingerInventoryController extends Controller
{
    public function __invoke(): View
    {
        $accounts = HostingerAccount::with([
            'hostingPlans' => fn ($query) => $query->whereNotNull('expires_at')->orderBy('expires_at'),
        ])->withCount([
            'websites',
            'domains',
            'alerts as open_alerts_count' => fn ($query) => $query->where('status', 'open'),
        ])->orderBy('name')->get();
        $expirationNoticeMonths = (int) config('services.hostinger.expiration_notice_months', 2);
        $activeAccountIds = $accounts->where('is_active', true)->pluck('id');
        $websites = HostingerWebsite::with('account')->whereIn('hostinger_account_id', $activeAccountIds)->get();
        $registeredDomains = HostingerDomain::with('account')->whereIn('hostinger_account_id', $activeAccountIds)->get();
        $hostingPlans = HostingerHostingPlan::with('account')->whereIn('hostinger_account_id', $activeAccountIds)->get();
        $domains = $this->mergeDomains($websites, $registeredDomains)->sortBy('domain')->values();

        $latestDeployments = Deployment::with(['project', 'domain'])
            ->whereHas('domain', fn ($query) => $query->whereIn('name', $domains->pluck('domain')))
            ->latest('id')
            ->get()
            ->unique(fn (Deployment $deployment) => $deployment->domain->name)
            ->keyBy(fn (Deployment $deployment) => $deployment->domain->name);

        $domains = $domains->map(function (array $row) use ($latestDeployments) {
            $row['deployment'] = $latestDeployments->get($row['domain']);
            $row['is_subdomain'] = $row['website']?->vhost_type === 'subdomain';

            return $row;
        });

        $mainDomains = $domains->where('is_subdomain', false)->values();
        $subdomainCount = $domains->where('is_subdomain', true)->count();
        $lastSyncedAt = $accounts->pluck('last_synced_at')->filter()->sortDesc()->first();
        $expiringDomains = $registeredDomains
            ->filter(fn (HostingerDomain $domain) => $domain->account?->is_active && $domain->expires_at?->isBetween(now(), now()->addMonthsNoOverflow($expirationNoticeMonths)))
            ->sortBy('expires_at')
            ->values();
        $alerts = HostingerAlert::with('account')
            ->where('status', 'open')
            ->whereHas('account', fn ($query) => $query->where('is_active', true))
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 0 ELSE 1 END")
            ->latest('last_detected_at')
            ->get();

        return view('hostinger.index', [
            'accounts' => $accounts,
            'domains' => $domains,
            'expiringDomains' => $expiringDomains,
            'alerts' => $alerts,
            'openAlertCount' => $alerts->count(),
            'domainCount' => $mainDomains->count(),
            'subdomainCount' => $subdomainCount,
            'lastSyncedAt' => $lastSyncedAt,
            'expirationNoticeMonths' => $expirationNoticeMonths,
        ]);
    }

    private function mergeDomains(Collection $websites, Collection $domains): Collection
    {
        $rows = collect();

        foreach ($domains as $domain) {
            $key = $domain->hostinger_account_id.'|'.$domain->domain;
            $rows[$key] = [
                'domain' => $domain->domain,
                'account' => $domain->account,
                'registration' => $domain,
                'website' => null,
            ];
        }

        foreach ($websites as $website) {
            $key = $website->hostinger_account_id.'|'.$website->domain;
            $row = $rows->get($key, [
                'domain' => $website->domain,
                'account' => $website->account,
                'registration' => null,
                'website' => null,
            ]);
            $row['website'] = $website;
            $rows[$key] = $row;
        }

        return $rows->values();
    }
}
