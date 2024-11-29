<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fundbank extends Model
{
    protected $fillable = ['name', 'account', 'ifsc', 'branch', 'status', 'user_id'];

    public $with = ['user'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
}
