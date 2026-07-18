<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'repository_url', 'branch', 'excluded_paths', 'is_active'])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'excluded_paths' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }
}
