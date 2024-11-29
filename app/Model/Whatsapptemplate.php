<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Whatsapptemplate extends Model
{
    protected $fillable = ['name', 'content', 'status','created_at'];
}
