<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Aeps_dispute extends Model
{
    protected $fillable = ['report_id', 'user_id', 'comment','solution', 'status', 'resolve_id'];

    public $with = ['user', 'resolver', 'report'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function resolver(){
        return $this->belongsTo('App\User', 'resolve_id');
    }
    
    public function report(){
        return $this->belongsTo('App\Model\Aepsreport', 'report_id');
    }

    
}
