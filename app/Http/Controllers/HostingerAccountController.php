<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use App\Services\HostingerSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class HostingerAccountController extends Controller
{
    public function index(): View
    {
        return view('hostinger.accounts', [
            'accounts' => HostingerAccount::with([
                'hostingPlans' => fn ($query) => $query->whereNotNull('expires_at')->orderBy('expires_at'),
            ])->withCount([
                'websites',
                'domains',
                'hostingPlans',
                'alerts as open_alerts_count' => fn ($query) => $query->where('status', 'open'),
            ])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, HostingerSyncService $sync): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'api_token' => ['required', 'string', 'max:4096'],
        ]);

        $account = HostingerAccount::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'api_token' => trim($validated['api_token']),
        ]);

        try {
            $sync->sync($account);

            return redirect()->route('hostinger.accounts.index')
                ->with('success', 'Le compte Hostinger a été ajouté et synchronisé.');
        } catch (Throwable $exception) {
            return redirect()->route('hostinger.accounts.index')
                ->with('error', $exception->getMessage());
        }
    }

    public function domains(HostingerAccount $hostingerAccount): View|RedirectResponse
    {
        if (! $hostingerAccount->is_active) {
            return redirect()->route('hostinger.accounts.index')
                ->with('error', 'Réactivez ce compte Hostinger pour consulter ses domaines.');
        }

        $registrations = HostingerDomain::where('hostinger_account_id', $hostingerAccount->id)->get()->keyBy('domain');
        $websites = HostingerWebsite::where('hostinger_account_id', $hostingerAccount->id)->get()->keyBy('domain');
        $domainNames = $registrations->keys()->merge($websites->keys())->unique()->sort()->values();
        $latestDeployments = Deployment::with(['project', 'domain'])
            ->whereHas('domain', fn ($query) => $query->whereIn('name', $domainNames))
            ->latest('id')
            ->get()
            ->unique(fn (Deployment $deployment) => $deployment->domain->name)
            ->keyBy(fn (Deployment $deployment) => $deployment->domain->name);

        $domains = $domainNames->map(fn (string $domain): array => [
            'domain' => $domain,
            'registration' => $registrations->get($domain),
            'website' => $websites->get($domain),
            'deployment' => $latestDeployments->get($domain),
        ]);

        return view('hostinger.account-domains', [
            'account' => $hostingerAccount,
            'domains' => $domains,
            'expirationNoticeMonths' => (int) config('services.hostinger.expiration_notice_months', 2),
        ]);
    }

    public function update(Request $request, HostingerAccount $hostingerAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'api_token' => ['nullable', 'string', 'max:4096'],
        ]);

        $values = [
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
        ];

        if (filled($validated['api_token'] ?? null)) {
            $values['api_token'] = trim($validated['api_token']);
            $values['status'] = 'pending';
            $values['sync_error'] = null;
        }

        $hostingerAccount->update($values);

        return redirect()->route('hostinger.accounts.index')
            ->with('success', 'Le compte Hostinger a été mis à jour.');
    }

    public function sync(HostingerAccount $hostingerAccount, HostingerSyncService $sync): RedirectResponse
    {
        if (! $hostingerAccount->is_active) {
            return back()->with('error', 'Réactivez ce compte Hostinger avant de l’actualiser.');
        }

        set_time_limit(0);

        try {
            $sync->sync($hostingerAccount);

            return back()->with('success', 'Les données Hostinger ont été actualisées.');
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function syncAll(HostingerSyncService $sync): RedirectResponse
    {
        set_time_limit(0);
        $errors = [];
        $accounts = HostingerAccount::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            return back()->with('error', 'Aucun compte Hostinger actif à actualiser.');
        }

        foreach ($accounts as $account) {
            try {
                $sync->sync($account);
            } catch (Throwable $exception) {
                $errors[] = $account->name.' : '.$exception->getMessage();
            }
        }

        if ($errors) {
            return back()->with('error', implode(' ', $errors));
        }

        return back()->with('success', 'Tous les comptes Hostinger ont été synchronisés.');
    }

    public function updateStatus(Request $request, HostingerAccount $hostingerAccount): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = (bool) $validated['is_active'];
        $hostingerAccount->update(['is_active' => $isActive]);

        if (! $isActive) {
            HostingerAlert::where('hostinger_account_id', $hostingerAccount->id)
                ->where('status', 'open')
                ->update(['status' => 'resolved', 'resolved_at' => now()]);

            return back()->with('success', 'Le compte Hostinger est en pause. Ses synchronisations et alertes sont suspendues.');
        }

        return back()->with('success', 'Le compte Hostinger a été réactivé. Vous pouvez maintenant l’actualiser.');
    }

    public function destroy(HostingerAccount $hostingerAccount): RedirectResponse
    {
        $hostingerAccount->delete();

        return redirect()->route('hostinger.accounts.index')
            ->with('success', 'Le compte Hostinger et ses données synchronisées ont été supprimés.');
    }
}
