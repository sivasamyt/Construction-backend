<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'address',
    ];

    public function domain(): HasOne
    {
        return $this->hasOne(Domain::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): ?User
    {
        return $this->users()->role('owner')->first();
    }

    public function hasOwner(): bool
    {
        return $this->users()->role('owner')->exists();
    }
}
