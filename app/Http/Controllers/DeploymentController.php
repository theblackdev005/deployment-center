<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use App\Models\Server;
use App\Services\DeploymentRunner;
use App\Services\SshCommandParser;
use App\Services\SshKeyProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DeploymentController extends Controller
{
    public function index(): View
    {
        return view('deployments.index', [
            'deployments' => Deployment::with(['project', 'domain', 'user'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('deployments.create', [
            'projects' => Project::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Deployment $deployment): View
    {
        return view('deployments.show', [
            'deployment' => $deployment->load(['project', 'domain.server', 'user']),
        ]);
    }

    public function retry(Request $request, Deployment $deployment, DeploymentRunner $runner): RedirectResponse
    {
        if (! in_array($deployment->status, ['failed', 'pending'], true)) {
            return redirect()->route('deployments.show', $deployment)
                ->with('error', 'Ce déploiement ne peut pas être relancé dans son état actuel.');
        }

        $retry = Deployment::create([
            'project_id' => $deployment->project_id,
            'domain_id' => $deployment->domain_id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        set_time_limit(0);

        try {
            $runner->run($retry);

            return redirect()->route('deployments.show', $retry)
                ->with('success', 'Le déploiement a été relancé avec succès.');
        } catch (Throwable $exception) {
            return redirect()->route('deployments.show', $retry);
        }
    }

    public function store(
        Request $request,
        SshCommandParser $parser,
        SshKeyProvisioner $provisioner,
        DeploymentRunner $runner,
    ): RedirectResponse {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'ssh_command' => ['required', 'string', 'max:500'],
            'ssh_password' => ['nullable', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:253', 'regex:/^(?=.{4,253}$)(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,63}$/'],
        ]);

        try {
            $connection = $parser->parse($validated['ssh_command']);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['ssh_command' => $exception->getMessage()]);
        }

        $server = Server::where($connection)->first();
        $hasUsableKey = $server?->ssh_key_path && Storage::disk('local')->exists($server->ssh_key_path);

        if (! $hasUsableKey && blank($validated['ssh_password'])) {
            throw ValidationException::withMessages([
                'ssh_password' => 'Le mot de passe est nécessaire pour la première connexion à ce serveur.',
            ]);
        }

        try {
            if (! $hasUsableKey || filled($validated['ssh_password'])) {
                $key = $provisioner->provision(
                    $connection['host'],
                    $connection['port'],
                    $connection['username'],
                    $validated['ssh_password'],
                );

                $server = Server::updateOrCreate($connection, [
                    'name' => 'Hostinger '.$connection['host'],
                    'base_path' => '/home/'.$connection['username'].'/domains',
                    'ssh_key_path' => $key['key_path'],
                    'fingerprint' => $key['fingerprint'],
                    'is_active' => true,
                ]);
            }
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['ssh_password' => $exception->getMessage()]);
        }

        $domainName = strtolower(trim($validated['domain']));
        $domain = Domain::updateOrCreate(['name' => $domainName], [
            'server_id' => $server->id,
            'document_root' => rtrim($server->base_path, '/').'/'.$domainName.'/public_html',
        ]);

        $deployment = Deployment::create([
            'project_id' => $validated['project_id'],
            'domain_id' => $domain->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        set_time_limit(0);

        try {
            $runner->run($deployment);

            return redirect()->route('deployments.index')
                ->with('success', 'Le projet a été déployé sur '.$domainName.'.');
        } catch (Throwable $exception) {
            return redirect()->route('deployments.index')
                ->with('error', $exception->getMessage());
        }
    }
}
