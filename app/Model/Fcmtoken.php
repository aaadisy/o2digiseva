<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fcmtoken extends Model
{
    protected $fillable = ['user_id', 'token'];

    public $with = ['user'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    
}
