<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deployment;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use App\Models\ManagedSite;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientSiteController extends Controller
{
    public function index(): View
    {
        $customers = Customer::with(['sites' => fn ($query) => $query->with('project')->orderBy('name')])
            ->withCount('sites')
            ->orderBy('name')
            ->get();
        $sites = $customers->flatMap(fn (Customer $customer) => $customer->sites);
        $domains = $sites->pluck('domain');

        $deployments = Deployment::with(['project', 'domain'])
            ->whereHas('domain', fn ($query) => $query->whereIn('name', $domains))
            ->latest('id')
            ->get()
            ->unique(fn (Deployment $deployment) => $deployment->domain->name)
            ->keyBy(fn (Deployment $deployment) => $deployment->domain->name);

        $hostingerWebsites = HostingerWebsite::with('account')
            ->whereIn('domain', $domains)
            ->whereHas('account', fn ($query) => $query->where('is_active', true))
            ->get()
            ->keyBy('domain');
        $hostingerDomains = HostingerDomain::with('account')
            ->whereIn('domain', $domains)
            ->whereHas('account', fn ($query) => $query->where('is_active', true))
            ->get()
            ->keyBy('domain');

        return view('clients-sites.index', [
            'customers' => $customers,
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
            'deployments' => $deployments,
            'hostingerWebsites' => $hostingerWebsites,
            'hostingerDomains' => $hostingerDomains,
            'siteCount' => $customers->sum('sites_count'),
            'unassignedSiteCount' => $sites->whereNull('project_id')->count(),
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        Customer::create($this->customerData($request));

        return back()->with('success', 'Le client a été ajouté.');
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->customerData($request));

        return back()->with('success', 'La fiche client a été mise à jour.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        if ($customer->sites()->exists()) {
            return back()->with('error', 'Retirez d’abord les sites associés à ce client.');
        }

        $customer->delete();

        return back()->with('success', 'La fiche client a été supprimée.');
    }

    public function storeSite(Request $request): RedirectResponse
    {
        $request->merge(['domain' => $this->normalizeDomain($request->string('domain')->toString())]);
        ManagedSite::create($this->siteData($request));

        return back()->with('success', 'Le site a été ajouté à la gestion.');
    }

    public function updateSite(Request $request, ManagedSite $managedSite): RedirectResponse
    {
        $request->merge(['domain' => $this->normalizeDomain($request->string('domain')->toString())]);
        $managedSite->update($this->siteData($request, $managedSite));

        return back()->with('success', 'La fiche du site a été mise à jour.');
    }

    public function destroySite(ManagedSite $managedSite): RedirectResponse
    {
        $managedSite->delete();

        return back()->with('success', 'Le site a été retiré de la gestion sans affecter son hébergement.');
    }

    /** @return array<string, mixed> */
    private function customerData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);
    }

    /** @return array<string, mixed> */
    private function siteData(Request $request, ?ManagedSite $site = null): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9-]{2,63}$/i',
                Rule::unique('managed_sites', 'domain')->ignore($site),
            ],
        ]);

        $data['name'] = $this->siteNameFromDomain($data['domain']);

        return $data;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $domain) ?? $domain;
        $domain = preg_split('/[\/?#]/', $domain, 2)[0] ?? $domain;
        $domain = preg_replace('/:\d+$/', '', $domain) ?? $domain;
        $domain = trim($domain, '.');
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;

        if (function_exists('idn_to_ascii')) {
            $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $domain;
        }

        return $domain;
    }

    private function siteNameFromDomain(string $domain): string
    {
        $label = explode('.', $domain)[0];

        return Str::of($label)->replace(['-', '_'], ' ')->headline()->toString();
    }
}
