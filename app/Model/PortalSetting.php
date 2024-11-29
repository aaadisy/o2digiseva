<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PortalSetting extends Model
{
    protected $fillable = ['name', 'code', 'value'];
    public $timestamps = false;
}
