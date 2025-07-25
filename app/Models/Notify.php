<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    use HasFactory;
    protected $casts = [
    'data' => 'array',
];
    protected $guarded = ['id'];

    public function user()
{
    return $this->belongsTo(User::class);
}

}
