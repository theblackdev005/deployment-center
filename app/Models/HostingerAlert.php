<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'hostinger_account_id', 'domain', 'type', 'severity', 'title', 'message', 'status',
    'fingerprint', 'detected_at', 'last_detected_at', 'notified_at', 'resolved_at',
])]
class HostingerAlert extends Model
{
    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'last_detected_at' => 'datetime',
            'notified_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(HostingerAccount::class, 'hostinger_account_id');
    }
}
