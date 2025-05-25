<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
     use HasFactory; 
    protected $guarded=["id"];
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_events')->withPivot('status')->withTimestamps();
    }

    public function companyEvents(): HasMany
    {
        return $this->hasMany(CompanyEvent::class);
    }



}
