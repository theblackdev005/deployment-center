<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'api_token', 'status', 'is_active', 'sync_error', 'last_synced_at'])]
class HostingerAccount extends Model
{
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'api_token' => 'encrypted',
            'is_active' => 'boolean',
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

    public function hostingPlans(): HasMany
    {
        return $this->hasMany(HostingerHostingPlan::class);
    }
}
