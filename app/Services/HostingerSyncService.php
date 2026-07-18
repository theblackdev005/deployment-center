<?php

namespace App\Services;

use App\Models\HostingerAccount;
use App\Models\HostingerDomain;
use App\Models\HostingerSubscription;
use App\Models\HostingerWebsite;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class HostingerSyncService
{
    public function sync(HostingerAccount $account): void
    {
        $account->update(['status' => 'syncing', 'sync_error' => null]);

        try {
            $client = new HostingerApiClient($account->api_token);
            $websites = $client->websites();
            $domains = $client->domains();
            $subscriptions = $client->subscriptions();
            $php = $client->phpDetails($websites);
            $syncedAt = now();

            DB::transaction(function () use ($account, $websites, $domains, $subscriptions, $php, $syncedAt) {
                $this->syncWebsites($account, $websites, $php['details'], $syncedAt);
                $this->syncDomains($account, $domains, $syncedAt);
                $this->syncSubscriptions($account, $subscriptions, $syncedAt);
            });

            $account->update([
                'status' => 'connected',
                'sync_error' => $php['warnings'] ? implode(' ', array_slice($php['warnings'], 0, 5)) : null,
                'last_synced_at' => $syncedAt,
            ]);
        } catch (Throwable $exception) {
            $account->update(['status' => 'error', 'sync_error' => $exception->getMessage()]);

            throw $exception;
        }
    }

    /** @param array<int, array<string, mixed>> $items */
    private function syncWebsites(HostingerAccount $account, array $items, array $phpDetails, Carbon $syncedAt): void
    {
        $seen = [];

        foreach ($items as $item) {
            $domain = strtolower(trim((string) ($item['domain'] ?? '')));

            if ($domain === '') {
                continue;
            }

            $seen[] = $domain;
            $php = $phpDetails[$domain] ?? [];
            HostingerWebsite::updateOrCreate(
                ['hostinger_account_id' => $account->id, 'domain' => $domain],
                [
                    'username' => $item['username'] ?? null,
                    'client_id' => $item['client_id'] ?? null,
                    'order_id' => $item['order_id'] ?? null,
                    'vhost_type' => $item['vhost_type'] ?? null,
                    'root_directory' => $item['root_directory'] ?? null,
                    'is_enabled' => (bool) ($item['is_enabled'] ?? true),
                    'php_version' => $php['php_version'] ?? null,
                    'php_version_full' => $php['php_version_full'] ?? null,
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

    /** @param array<int, array<string, mixed>> $items */
    private function syncSubscriptions(HostingerAccount $account, array $items, Carbon $syncedAt): void
    {
        $seen = [];

        foreach ($items as $item) {
            $externalId = trim((string) ($item['id'] ?? ''));

            if ($externalId === '') {
                continue;
            }

            $seen[] = $externalId;
            HostingerSubscription::updateOrCreate(
                ['hostinger_account_id' => $account->id, 'external_id' => $externalId],
                [
                    'name' => $item['name'] ?? 'Abonnement Hostinger',
                    'status' => $item['status'] ?? null,
                    'is_auto_renewed' => (bool) ($item['is_auto_renewed'] ?? false),
                    'billing_period' => $item['billing_period'] ?? null,
                    'billing_period_unit' => $item['billing_period_unit'] ?? null,
                    'currency_code' => $item['currency_code'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                    'renewal_price' => $item['renewal_price'] ?? null,
                    'remote_created_at' => $this->date($item['created_at'] ?? null),
                    'expires_at' => $this->date($item['expires_at'] ?? null),
                    'next_billing_at' => $this->date($item['next_billing_at'] ?? null),
                    'last_synced_at' => $syncedAt,
                ],
            );
        }

        $query = HostingerSubscription::where('hostinger_account_id', $account->id);
        $seen ? $query->whereNotIn('external_id', $seen)->delete() : $query->delete();
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
