<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    

    public function companyPermission($permission)
    {
    	$admin = \App\User::where('company_id', \Auth::user()->company_id)->whereHas('role',  function ($q){
            $q->whereIn('slug', ['whitelable', 'admin']);
        })->first();

        if(!\Myhelper::can($permission, $admin->id)){
            return true;
        }else{
        	return false;
        }
    }
    
    public function transcode()
    {
    	$code = \DB::table('portal_settings')->where('code', 'transactioncode')->first(['value']);
    	return $code->value;
    }

    public function settlementcharge()
    {
        $code = \DB::table('portal_settings')->where('code', 'settlementcharge')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return 0;
        }
    }

    public function settlementcharge1k()
    {
        $code = \DB::table('portal_settings')->where('code', 'settlementcharge1k')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "0";
        }
    }

    public function settlementcharge25k()
    {
        $code = \DB::table('portal_settings')->where('code', 'settlementcharge25k')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "0";
        }
    }

    public function settlementcharge2l()
    {
        $code = \DB::table('portal_settings')->where('code', 'settlementcharge2l')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "0";
        }
    }
    
    public function settlementtype()
    {
        $code = \DB::table('portal_settings')->where('code', 'settlementtype')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "manual";
        }
    }

    public function banksettlementtype()
    {
        $code = \DB::table('portal_settings')->where('code', 'banksettlementtype')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "manual";
        }
    }
    
    public function batch()
    {
        $code = \DB::table('portal_settings')->where('code', 'batch')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "manual";
        }
    }
    
    public function mainlocked()
    {
        $code = \DB::table('portal_settings')->where('code', 'mainlocked')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "0";
        }
    }
    
    public function aepslocked()
    {
        $code = \DB::table('portal_settings')->where('code', 'aepslocked')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "0";
        }
    }
}
