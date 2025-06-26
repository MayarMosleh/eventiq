<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class venue extends Model
{
    protected $fillable = [
        'venue_name',
        'address',
        'capacity',
        'venue_price',
        'company_id'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(company::class);
    }

    public function venueImages(): HasMany
    {
        return $this->hasMany(venueImage::class);
    }
}
