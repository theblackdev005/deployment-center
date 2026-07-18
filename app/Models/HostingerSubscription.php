<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hostinger_account_id', 'external_id', 'name', 'status', 'is_auto_renewed',
    'billing_period', 'billing_period_unit', 'currency_code', 'total_price',
    'renewal_price', 'remote_created_at', 'expires_at', 'next_billing_at', 'last_synced_at',
])]
class HostingerSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'is_auto_renewed' => 'boolean',
            'remote_created_at' => 'datetime',
            'expires_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(HostingerAccount::class, 'hostinger_account_id');
    }
}
