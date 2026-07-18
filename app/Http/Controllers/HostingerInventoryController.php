<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HostingerInventoryController extends Controller
{
    public function __invoke(): View
    {
        $accounts = HostingerAccount::withCount(['websites', 'domains'])->orderBy('name')->get();
        $websites = HostingerWebsite::with('account')->get();
        $registeredDomains = HostingerDomain::with('account')->get();
        $domains = $this->mergeDomains($websites, $registeredDomains)->sortBy('domain')->values();

        $latestDeployments = Deployment::with(['project', 'domain'])
            ->whereHas('domain', fn ($query) => $query->whereIn('name', $domains->pluck('domain')))
            ->latest('id')
            ->get()
            ->unique(fn (Deployment $deployment) => $deployment->domain->name)
            ->keyBy(fn (Deployment $deployment) => $deployment->domain->name);

        $domains = $domains->map(function (array $row) use ($latestDeployments) {
            $row['deployment'] = $latestDeployments->get($row['domain']);

            return $row;
        });

        $expiringDomains = $registeredDomains
            ->filter(fn (HostingerDomain $domain) => $domain->expires_at?->isBetween(now(), now()->addDays(30)))
            ->sortBy('expires_at')
            ->values();
        $alerts = HostingerAlert::with('account')
            ->where('status', 'open')
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 0 ELSE 1 END")
            ->latest('last_detected_at')
            ->get();

        return view('hostinger.index', [
            'accounts' => $accounts,
            'domains' => $domains,
            'expiringDomains' => $expiringDomains,
            'alerts' => $alerts,
            'domainCount' => $domains->count(),
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
