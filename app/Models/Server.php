<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'host', 'port', 'username', 'base_path', 'ssh_key_path', 'fingerprint', 'is_active'])]
class Server extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
