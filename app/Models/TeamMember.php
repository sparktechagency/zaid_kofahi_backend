<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function team()
    {
        return $this->belongsTo(Team::class, 'id');
    }
    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
        // যদি column নাম player_id না হয়ে user_id হয় তাহলে 'user_id' দাও
    }
}
