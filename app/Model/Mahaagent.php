<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Mahaagent extends Model
{
    protected $fillable = ['bc_id', 'bc_f_name', 'bc_m_name', 'bc_l_name', 'emailid', 'phone1', 'phone2', 'bc_dob', 'bc_state', 'bc_district', 'bc_address', 'bc_block', 'bc_city', 'bc_landmark', 'bc_loc', 'bc_mohhalla', 'bc_pan', 'bc_pincode', 'shopname', 'shopType', 'qualification', 'population', 'locationType', 'status', 'user_id'];

    public $with = ['user'];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
