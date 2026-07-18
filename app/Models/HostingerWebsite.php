<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hostinger_account_id', 'domain', 'username', 'client_id', 'order_id', 'vhost_type',
    'root_directory', 'is_enabled', 'php_version', 'php_version_full', 'remote_created_at',
    'last_synced_at',
])]
class HostingerWebsite extends Model
{
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'remote_created_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(HostingerAccount::class, 'hostinger_account_id');
    }
}
