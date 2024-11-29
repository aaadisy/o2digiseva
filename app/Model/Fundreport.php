<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fundreport extends Model
{
    protected $fillable = ['type', 'fundbank_id', 'ref_no', 'paydate', 'payslip', 'remark', 'status', 'user_id', 'credited_by', 'paymode', 'amount'];

    public $with = ['fundbank', 'user', 'sender'];

    public function fundbank(){
        return $this->belongsTo('App\Model\Fundbank');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function sender(){
        return $this->belongsTo('App\User', 'credited_by');
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
