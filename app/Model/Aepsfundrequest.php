<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Aepsfundrequest extends Model
{
    protected $fillable = ['amount', 'remark', 'status', 'type', 'user_id','account', 'bank','ifsc', 'pay_type', 'payoutid', 'payoutref','create_time'];

    public $with = ['user'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d M y - h:i:s A', strtotime($value));
    }

}

