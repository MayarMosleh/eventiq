<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class venue extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(company::class);
    }
}
