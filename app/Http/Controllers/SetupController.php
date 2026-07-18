<?php

namespace App\Http\Controllers;

use App\Models\HostingerAccount;
use App\Models\Project;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function __invoke(): View
    {
        return view('setup.index', [
            'hasProjects' => Project::exists(),
            'hasHostingerAccounts' => HostingerAccount::exists(),
        ]);
    }
}
