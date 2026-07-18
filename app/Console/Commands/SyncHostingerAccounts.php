<?php

namespace App\Console\Commands;

use App\Models\HostingerAccount;
use App\Services\HostingerSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncHostingerAccounts extends Command
{
    protected $signature = 'hostinger:sync';

    protected $description = 'Synchronise tous les comptes Hostinger enregistrés';

    public function handle(HostingerSyncService $sync): int
    {
        $failed = false;

        foreach (HostingerAccount::all() as $account) {
            try {
                $sync->sync($account);
                $this->info($account->name.' synchronisé.');
            } catch (Throwable $exception) {
                $failed = true;
                $this->error($account->name.' : '.$exception->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
