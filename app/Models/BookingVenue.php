<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingVenue extends Model
{
    protected $guarded = [];
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
