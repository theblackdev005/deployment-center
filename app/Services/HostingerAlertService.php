<?php

namespace App\Services;

use App\Models\HostingerAccount;
use App\Models\HostingerAlert;
use App\Models\User;
use App\Notifications\HostingerProblemDetected;
use Illuminate\Support\Facades\Notification;
use Throwable;

class HostingerAlertService
{
    public function reconcile(HostingerAccount $account): void
    {
        $account->loadMissing(['domains', 'websites']);
        $issues = [];

        foreach ($account->domains as $domain) {
            $status = strtolower((string) $domain->status);

            if ($domain->expires_at?->isPast() || $status === 'expired') {
                $issues[] = $this->issue(
                    $account,
                    'domain_expired',
                    $domain->domain,
                    'critical',
                    'Domaine expiré : '.$domain->domain,
                    'Le domaine '.$domain->domain.' est arrivé à expiration.',
                );
            } elseif ($domain->expires_at?->isBetween(now(), now()->addDays(30))) {
                $issues[] = $this->issue(
                    $account,
                    'domain_expiring',
                    $domain->domain,
                    'warning',
                    'Expiration prochaine : '.$domain->domain,
                    'Le domaine '.$domain->domain.' expire le '.$domain->expires_at->format('d/m/Y').'.',
                );
            }

            if (in_array($status, ['suspended', 'failed'], true)) {
                $issues[] = $this->issue(
                    $account,
                    'domain_'.$status,
                    $domain->domain,
                    'critical',
                    'Problème de domaine : '.$domain->domain,
                    'Hostinger signale le domaine '.$domain->domain.' avec l’état « '.$status.' ».',
                );
            }
        }

        foreach ($account->websites->where('is_enabled', false) as $website) {
            $issues[] = $this->issue(
                $account,
                'website_disabled',
                $website->domain,
                'critical',
                'Site désactivé : '.$website->domain,
                'Le site '.$website->domain.' est actuellement signalé comme désactivé par Hostinger.',
            );
        }

        $activeFingerprints = [];

        foreach ($issues as $issue) {
            $alert = $this->persist($issue);
            $activeFingerprints[] = $alert->fingerprint;
            $this->notify($alert);
        }

        $query = HostingerAlert::where('hostinger_account_id', $account->id)->where('status', 'open');

        if ($activeFingerprints) {
            $query->whereNotIn('fingerprint', $activeFingerprints);
        }

        $query->update(['status' => 'resolved', 'resolved_at' => now()]);
    }

    public function recordSyncFailure(HostingerAccount $account, string $message): void
    {
        $alert = $this->persist($this->issue(
            $account,
            'sync_failed',
            null,
            'critical',
            'Synchronisation Hostinger impossible',
            'Le compte '.$account->name.' ne peut plus être synchronisé : '.$message,
        ));

        $this->notify($alert);
    }

    /** @return array<string, mixed> */
    private function issue(
        HostingerAccount $account,
        string $type,
        ?string $domain,
        string $severity,
        string $title,
        string $message,
    ): array {
        return [
            'hostinger_account_id' => $account->id,
            'domain' => $domain,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'fingerprint' => hash('sha256', $account->id.'|'.$type.'|'.$domain),
        ];
    }

    /** @param array<string, mixed> $issue */
    private function persist(array $issue): HostingerAlert
    {
        $alert = HostingerAlert::firstOrNew(['fingerprint' => $issue['fingerprint']]);
        $reopened = $alert->exists && $alert->status === 'resolved';
        $alert->fill($issue);
        $alert->status = 'open';
        $alert->last_detected_at = now();
        $alert->resolved_at = null;

        if (! $alert->exists || $reopened) {
            $alert->detected_at = now();
            $alert->notified_at = null;
        }

        $alert->save();

        return $alert->load('account');
    }

    private function notify(HostingerAlert $alert): void
    {
        if ($alert->notified_at) {
            return;
        }

        try {
            Notification::send(User::whereNotNull('email')->get(), new HostingerProblemDetected($alert));
            $alert->update(['notified_at' => now()]);
        } catch (Throwable) {
            // A failed email must not interrupt the Hostinger synchronization.
        }
    }
}
