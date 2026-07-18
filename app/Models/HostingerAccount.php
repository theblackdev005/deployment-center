<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'api_token', 'status', 'sync_error', 'last_synced_at'])]
class HostingerAccount extends Model
{
    protected function casts(): array
    {
        return [
            'api_token' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }

    public function websites(): HasMany
    {
        return $this->hasMany(HostingerWebsite::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(HostingerDomain::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(HostingerAlert::class);
    }
}
