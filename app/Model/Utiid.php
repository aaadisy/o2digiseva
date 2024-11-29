<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Utiid extends Model
{
    protected $fillable = ['vleid', 'vlepassword', 'name', 'location', 'contact_person', 'pincode', 'state', 'email', 'mobile', 'user_id', 'sender_id', 'status', 'api_id'];

    public $with = ['user', 'api'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function api(){
        return $this->belongsTo('App\Model\Api');
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d M y - h:i A', strtotime($value));
    }
    public function getCreatedAtAttribute($value)
    {
        return date('d M - H:i', strtotime($value));
    }
}
