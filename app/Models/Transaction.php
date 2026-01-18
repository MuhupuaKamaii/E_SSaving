<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // This tells Laravel these columns can be saved via API
    protected $fillable = ['from', 'message', 'status'];
}