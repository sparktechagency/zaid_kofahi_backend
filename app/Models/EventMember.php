<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMember extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function player()
    {
        return $this->belongsTo(User::class,'id');
    }
}
