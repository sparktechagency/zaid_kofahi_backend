<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function player(){
        return $this->belongsTo(User::class,'player_id');
    }

    public function event(){
        return $this->belongsTo(Event::class,'event_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function team(){
        return $this->belongsTo(Team::class,'team_id');
    }
}
