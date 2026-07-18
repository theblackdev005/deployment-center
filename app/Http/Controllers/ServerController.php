<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $servers = Server::withCount('domains')->orderBy('name')->get();
        $servers->each(fn (Server $server) => $server->setAttribute(
            'connection_ready',
            filled($server->ssh_key_path) && Storage::disk('local')->exists($server->ssh_key_path),
        ));

        return view('servers.index', [
            'servers' => $servers,
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
            'name' => ['required', 'string', 'max:100'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:100'],
        ]);

        Server::create([
            ...$validated,
            'base_path' => '/home/'.$validated['username'].'/domains',
        ]);

        return back()->with('success', 'Serveur ajouté.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server): RedirectResponse
    {
        if ($server->domains()->exists()) {
            return back()->with('error', 'Supprimez d’abord les domaines associés à ce serveur.');
        }

        $server->delete();

        return back()->with('success', 'Serveur supprimé.');
    }
}
