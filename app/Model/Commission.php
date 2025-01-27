<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['slab', 'type','admin', 'whitelable', 'md', 'distributor', 'retailer', 'scheme_id'];

    public $with = ['provider'];

    public function provider(){
        return $this->belongsTo('App\Model\Provider', 'slab');
    }
}
