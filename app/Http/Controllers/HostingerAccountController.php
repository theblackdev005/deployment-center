<?php

namespace App\Http\Controllers;

use App\Models\HostingerAccount;
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
            'accounts' => HostingerAccount::withCount([
                'websites',
                'domains',
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
            'api_token' => ['required', 'string', 'max:4096'],
        ]);

        $account = HostingerAccount::create([
            'name' => $validated['name'],
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

    public function update(Request $request, HostingerAccount $hostingerAccount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'api_token' => ['nullable', 'string', 'max:4096'],
        ]);

        $values = ['name' => $validated['name']];

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

        foreach (HostingerAccount::all() as $account) {
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

    public function destroy(HostingerAccount $hostingerAccount): RedirectResponse
    {
        $hostingerAccount->delete();

        return redirect()->route('hostinger.accounts.index')
            ->with('success', 'Le compte Hostinger et ses données synchronisées ont été supprimés.');
    }
}
