<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('domains.index', [
            'domains' => Domain::with('server')->withCount('deployments')->orderBy('name')->get(),
            'servers' => Server::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'server_id' => ['required', 'exists:servers,id'],
            'name' => ['required', 'string', 'max:255', 'unique:domains,name'],
        ]);

        $validated['name'] = Str::lower(trim($validated['name']));
        $server = Server::findOrFail($validated['server_id']);

        Domain::create([
            ...$validated,
            'document_root' => rtrim($server->base_path, '/').'/'.$validated['name'].'/public_html',
        ]);

        return back()->with('success', 'Domaine ajouté.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domain $domain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain): RedirectResponse
    {
        if ($domain->deployments()->exists()) {
            return back()->with('error', 'Ce domaine possède un historique de déploiement.');
        }

        $domain->delete();

        return back()->with('success', 'Domaine supprimé.');
    }
}
