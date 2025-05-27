<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    protected $guarded=["id"];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'company_events')->withTimestamps();
    }

    public function companyEvents(): HasMany
    {
        return $this->hasMany(CompanyEvent::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function eventRequest()
    {
        return $this->hasMany(EventRequest::class);
    }
}
