<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'phone', 'notes'])]
class Customer extends Model
{
    public function sites(): HasMany
    {
        return $this->hasMany(ManagedSite::class);
    }
}
