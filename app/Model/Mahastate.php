<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Mahastate extends Model
{
    protected $fillable = ['stateid', 'statename'];
    public $timestamps = false;
}
