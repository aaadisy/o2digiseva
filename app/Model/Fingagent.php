<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fingagent extends Model
{
    protected $fillable = [
        'merchantLoginId', 'merchantLoginPin', 'merchantName', 'merchantAddress', 'merchantAddress2',
        'merchantDistrictName', 'merchantCityName', 'merchantState', 'merchantPhoneNumber', 'userPan',
        'merchantPinCode', 'merchantAadhar', 'shopAddress', 'shopCity', 'shopDistrict', 'shopState',
        'shopPincode', 'shopLatitude', 'shopLongitude', 'backgroundImageOfShop', 'maskedAadharImage',
        'aadharPic', 'pancardPic', 'status', 'user_id', 'bankIfscCode', 'companyBankName',
        'bankBranchName', 'bankAccountName', 'companyBankAccountNumber', 'mccCode', 'ipAddress'
    ];

    // Eager load the user relationship by default
    protected $with = ['user'];

    // Append custom attributes to the model's JSON form
    protected $appends = ['user_name', 'merchant_state_name', 'shop_state_name', 'merchant_company_type'];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    // Define the relationship with the CompanyType model for mccCode
    public function merchantCompanyTypeTable()
    {
        return $this->belongsTo('App\Model\CompanyType', 'mccCode', 'mccCode');
    }

    // Define the relationship with the Fingstate model for merchantState
    public function merchantStateTable()
    {
        return $this->belongsTo('App\Model\Fingstate', 'merchantState', 'stateId');
    }

    // Define the relationship with the Fingstate model for shopState
    public function shopStateTable()
    {
        return $this->belongsTo('App\Model\Fingstate', 'shopState', 'stateId');
    }

    // Accessor for user_name
    public function getUserNameAttribute()
    {
        $user = \App\User::where('id', $this->user_id)->with('role')->first(['name', 'id', 'role_id', 'mobile']);
        if ($user) {
            return $user->name . "( " . $user->id . " )<br>" . $user->mobile . "<br>" . $user->role->role_title;
        } else {
            return "Not Found";
        }
    }

    // Accessor for merchant_state_name
    public function getMerchantStateNameAttribute()
    {
        return $this->merchantStateTable ? $this->merchantStateTable->state : 'Unknown';
    }

    // Accessor for shop_state_name
    public function getShopStateNameAttribute()
    {
        return $this->shopStateTable ? $this->shopStateTable->state : 'Unknown';
    }

    // Accessor for merchant_company_type
    public function getMerchantCompanyTypeAttribute()
    {
        return $this->merchantCompanyTypeTable ? $this->merchantCompanyTypeTable->mccDescription : 'Unknown';
    }
}
