<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['name', 'recharge1', 'recharge2', 'recharge3', 'api_id', 'type', 'status','min_amount','max_amount', 'mplan', 'roffer'];
    public $timestamps = false;

    public $with = ['api'];

    public function api()
    {
        return $this->belongsTo('App\Model\Api');
    }
}
