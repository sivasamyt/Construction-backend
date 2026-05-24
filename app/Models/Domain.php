<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    protected $fillable = [
        'company_id',
        'domain',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
