<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cutoff = now()->addMonthsNoOverflow((int) config('services.hostinger.expiration_notice_months', 2));

        DB::table('hostinger_alerts')
            ->where('status', 'open')
            ->whereIn('type', ['domain_expiring', 'hosting_expiring'])
            ->orderBy('id')
            ->each(function (object $alert) use ($cutoff): void {
                $expiresAt = $alert->type === 'domain_expiring'
                    ? DB::table('hostinger_domains')
                        ->where('hostinger_account_id', $alert->hostinger_account_id)
                        ->where('domain', $alert->domain)
                        ->value('expires_at')
                    : DB::table('hostinger_hosting_plans')
                        ->where('hostinger_account_id', $alert->hostinger_account_id)
                        ->where('order_id', (int) str_replace('hosting-', '', (string) $alert->domain))
                        ->value('expires_at');

                if (! $expiresAt || Carbon::parse($expiresAt)->isAfter($cutoff)) {
                    DB::table('hostinger_alerts')->where('id', $alert->id)->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Resolved alerts must not be reopened when rolling back this cleanup.
    }
};
