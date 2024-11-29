<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','email','mobile','password','remember_token','nsdlwallet','lockedamount','role_id','parent_id','company_id','scheme_id','status','address','shopname','gstin','city','state','pincode','pancard','aadharcard','pancardpic','aadharcardpic','gstpic','profile','kyc','callbackurl','remark','resetpwd','otpverify','otpresend','account','bank','ifsc','apptoken','passwordold','walletpin','usernotice','gst','tds','video','lat','long'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $with = ['role', 'company'];
    protected $appends = ['parents'];

    public function role(){
        return $this->belongsTo('App\Model\Role');
    }
    
    public function company(){
        return $this->belongsTo('App\Model\Company');
    }
    
    public function setLatAttribute($value)
    {
        $this->attributes['lat'] = number_format($value, 7, '.', '');
    }

    // Mutator for longitude
    public function setLongAttribute($value)
    {
        $this->attributes['long'] = number_format($value, 7, '.', '');
    }

    public function getParentsAttribute() {
        $user = User::where('id', $this->parent_id)->first(['id', 'name', 'mobile', 'role_id']);
        if($user){
            return $user->name." (".$user->id.")<br>".$user->mobile."<br>".$user->role->name;
        }else{
            return "Not Found";
        }
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

