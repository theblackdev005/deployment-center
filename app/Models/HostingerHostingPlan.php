<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hostinger_account_id',
    'order_id',
    'subscription_id',
    'name',
    'status',
    'remote_created_at',
    'expires_at',
    'last_synced_at',
])]
class HostingerHostingPlan extends Model
{
    protected function casts(): array
    {
        return [
            'remote_created_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(HostingerAccount::class, 'hostinger_account_id');
    }
}
