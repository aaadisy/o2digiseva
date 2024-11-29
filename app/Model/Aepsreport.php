<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Aepsreport extends Model
{
    protected $fillable = ['mobile' ,'aadhar', 'txnid', 'api_id', 'amount', 'charge', 'bank', 'payid', 'tds', 'refno', 'mytxnid', 'terminalid', 'authcode', 'balance', 'status', 'type', 'remark', 'rtype', 'transtype', 'aepstype', 'TxnMedium', 'product', 'user_id', 'credited_by','provider_id','threeway'];

    public $appends = ['apicode', 'username'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function api(){
        return $this->belongsTo('App\Model\Api');
    }

    public function getApicodeAttribute()
    {
        $data = Api::where('id' , $this->api_id)->first(['code']);
        if($data){
            return $data->code;
        }else{
            return "";
        }
    }

    public function getUsernameAttribute()
    {
        $data = '';
        if($this->user_id){
            $user = \App\User::where('id' , $this->user_id)->first(['name', 'id', 'role_id']);
            $data = $user->name." (".$user->id.") (".$user->role->name.")";
        }
        return $data;
    }
}
