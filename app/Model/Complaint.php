<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['product', 'subject', 'description', 'solution', 'transaction_id', 'status', 'user_id', 'resolve_id'];

    public $with = ['user', 'resolver', 'complaintsubject','report_details'];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function resolver(){
        return $this->belongsTo('App\User', 'resolve_id');
    }

    public function complaintsubject(){
        return $this->belongsTo('App\Model\Complaintsubject', 'subject');
    }
    
    public function report_details(){
        return $this->belongsTo('App\Model\Report', 'transaction_id');
    }
}
