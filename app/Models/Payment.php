<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

     protected $casts = [
        'winners' => 'array', // JSON column কে auto array বানাবে
    ];
}
