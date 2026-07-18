<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'repository_url', 'github_token', 'branch', 'excluded_paths', 'is_active'])]
#[Hidden(['github_token'])]
class Project extends Model
{
    protected function casts(): array
    {
        return [
            'excluded_paths' => 'array',
            'github_token' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }
}
