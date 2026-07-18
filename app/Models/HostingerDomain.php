<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hostinger_account_id', 'external_id', 'domain', 'type', 'status', 'registered_at',
    'expires_at', 'last_synced_at',
])]
class HostingerDomain extends Model
{
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(HostingerAccount::class, 'hostinger_account_id');
    }
}
