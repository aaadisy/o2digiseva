<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Mahabank extends Model
{
    protected $fillable = ['bankid', 'bankcode', 'bankname', 'masterifsc', 'url'];
    public $timestamps = false;
}
