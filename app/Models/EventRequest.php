<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class EventRequest extends Model
{
    use HasFactory; 
    protected $guarded=["id"];
     protected $casts = [
        'event_data' => 'array',
        'handled_at' => 'datetime',
    ];
    
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
