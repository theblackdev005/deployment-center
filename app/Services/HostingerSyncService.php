<?php

namespace App\Services;

use App\Models\HostingerAccount;
use App\Models\HostingerDomain;
use App\Models\HostingerWebsite;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class HostingerSyncService
{
    public function __construct(private readonly HostingerAlertService $alerts) {}

    public function sync(HostingerAccount $account): void
    {
        $account->update(['status' => 'syncing', 'sync_error' => null]);

        try {
            $client = new HostingerApiClient($account->api_token);
            $websites = $client->websites();
            $domains = $client->domains();
            $syncedAt = now();

            DB::transaction(function () use ($account, $websites, $domains, $syncedAt) {
                $this->syncWebsites($account, $websites, $syncedAt);
                $this->syncDomains($account, $domains, $syncedAt);
            });

            $account->update([
                'status' => 'connected',
                'sync_error' => null,
                'last_synced_at' => $syncedAt,
            ]);
            $this->alerts->reconcile($account);
        } catch (Throwable $exception) {
            $account->update(['status' => 'error', 'sync_error' => $exception->getMessage()]);
            $this->alerts->recordSyncFailure($account, $exception->getMessage());

            throw $exception;
        }
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncWebsites(HostingerAccount $account, array $items, Carbon $syncedAt): void
    {
        $seen = [];

        foreach ($items as $item) {
            $domain = strtolower(trim((string) ($item['domain'] ?? '')));

            if ($domain === '') {
                continue;
            }

            $seen[] = $domain;
            HostingerWebsite::updateOrCreate(
                ['hostinger_account_id' => $account->id, 'domain' => $domain],
                [
                    'username' => $item['username'] ?? null,
                    'client_id' => $item['client_id'] ?? null,
                    'order_id' => $item['order_id'] ?? null,
                    'vhost_type' => $item['vhost_type'] ?? null,
                    'root_directory' => $item['root_directory'] ?? null,
                    'is_enabled' => (bool) ($item['is_enabled'] ?? true),
                    'remote_created_at' => $this->date($item['created_at'] ?? null),
                    'last_synced_at' => $syncedAt,
                ],
            );
        }

        $query = HostingerWebsite::where('hostinger_account_id', $account->id);
        $seen ? $query->whereNotIn('domain', $seen)->delete() : $query->delete();
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncDomains(HostingerAccount $account, array $items, Carbon $syncedAt): void
    {
        $seen = [];

        foreach ($items as $item) {
            $domain = strtolower(trim((string) ($item['domain'] ?? '')));

            if ($domain === '') {
                continue;
            }

            $seen[] = $domain;
            HostingerDomain::updateOrCreate(
                ['hostinger_account_id' => $account->id, 'domain' => $domain],
                [
                    'external_id' => $item['id'] ?? null,
                    'type' => $item['type'] ?? null,
                    'status' => $item['status'] ?? null,
                    'registered_at' => $this->date($item['created_at'] ?? null),
                    'expires_at' => $this->date($item['expires_at'] ?? null),
                    'last_synced_at' => $syncedAt,
                ],
            );
        }

        $query = HostingerDomain::where('hostinger_account_id', $account->id);
        $seen ? $query->whereNotIn('domain', $seen)->delete() : $query->delete();
    }

    private function date(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
