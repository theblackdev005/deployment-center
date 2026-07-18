<?php

namespace App\Http\Controllers;

use App\Models\HostingerAccount;
use App\Models\HostingerDomain;
use App\Models\HostingerSubscription;
use App\Models\HostingerWebsite;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HostingerInventoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $accountId = $request->integer('account') ?: null;
        $search = trim((string) $request->query('q', ''));
        $accounts = HostingerAccount::withCount(['websites', 'domains'])->orderBy('name')->get();

        $websites = HostingerWebsite::with('account')
            ->when($accountId, fn ($query) => $query->where('hostinger_account_id', $accountId))
            ->get();
        $domains = HostingerDomain::with('account')
            ->when($accountId, fn ($query) => $query->where('hostinger_account_id', $accountId))
            ->get();

        $rows = $this->mergeDomains($websites, $domains)
            ->when($search !== '', fn (Collection $items) => $items->filter(
                fn (array $row) => str_contains($row['domain'], strtolower($search))
                    || str_contains(strtolower($row['account']->name), strtolower($search)),
            ))
            ->sortBy('domain')
            ->values();

        $page = max(1, $request->integer('page'));
        $perPage = 25;
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $expiringDomains = HostingerDomain::with('account')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->when($accountId, fn ($query) => $query->where('hostinger_account_id', $accountId))
            ->orderBy('expires_at')
            ->get();

        $subscriptions = HostingerSubscription::with('account')
            ->when($accountId, fn ($query) => $query->where('hostinger_account_id', $accountId))
            ->orderByRaw('expires_at IS NULL, expires_at ASC')
            ->limit(20)
            ->get();

        return view('hostinger.index', [
            'accounts' => $accounts,
            'domains' => $paginator,
            'expiringDomains' => $expiringDomains,
            'subscriptions' => $subscriptions,
            'selectedAccount' => $accountId,
            'search' => $search,
            'websiteCount' => $websites->count(),
            'domainCount' => $rows->count(),
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
