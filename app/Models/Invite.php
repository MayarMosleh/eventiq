<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    public function booking(): BelongsTo
    {
        return $this->belongsTo(booking::class);
    }
}
