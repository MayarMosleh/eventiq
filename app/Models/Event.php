<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_event')->withTimestamps();
    }

    public function companyEvents(): HasMany
    {
        return $this->hasMany(CompanyEvent::class);
    }



}
