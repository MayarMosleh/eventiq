<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
 use HasFactory;
    protected $guarded = ['id'];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
