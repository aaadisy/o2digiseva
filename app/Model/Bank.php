<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['state', 'plan_code'];
    public $timestamps = false;
}
