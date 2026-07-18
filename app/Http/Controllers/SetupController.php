<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Project;
use App\Models\Server;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function __invoke(): View
    {
        return view('setup.index', [
            'hasProjects' => Project::exists(),
            'hasServers' => Server::exists(),
            'hasDomains' => Domain::exists(),
        ]);
    }
}
