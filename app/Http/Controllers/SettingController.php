<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Circle;
use App\Model\Company;
use App\Model\Role;

class SettingController extends Controller
{
    public function index($id=0)
    {
        $data = [];
        if($id != 0){
            $data['user'] = User::find($id);
        }else{
            $data['user'] = \Auth::user();
        }

        if(\Myhelper::hasRole('admin')){
            $data['parents'] = User::whereHas('role', function ($q){
                $q->where('slug', '!=', 'retailer');
            })->get(['id', 'name', 'role_id', 'mobile']);

            $data['roles']   = Role::where('slug' , '!=' , 'admin')->get();
        }else{
            $data['parents'] = [];
            $data['roles']   = [];
        }

        $data['state'] = Circle::all(['state']);
        $data['company'] = Company::all();
        return view('profile.index')->with($data);
    }

    public function profileUpdate(\App\Http\Requests\Member $post)
    {
        if(\Myhelper::hasNotRole('admin') &&  (\Auth::id() != $post->id) && !in_array($post->id, \Myhelper::getParents(\Auth::id()))){
            return response()->json(['status' => "Permission Not Alloweds"], 400);
        }
        
        
        switch ($post->actiontype) {
            case 'walletpin':
                if(($post->id != \Auth::id())){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                //dd($post->all()); exit;
                if($post->walletpin != $post->walletpin_confirmation){
                    return response()->json(['status' => "Wallet Pin could not matched!"], 400);
                }
                $user = User::where('id', $post->id)->first();
                $oldwalletpin = User::where('walletpin', $post->walletpin)->where('id', $post->id)->count();
                if($oldwalletpin > 0){
                    return response()->json(['status' => "Old wallet pin could not matched"]);
                }



                $walletpinupdateotp = \App\Model\PortalSetting::where('code', 'walletpinupdateotp')->first();
                //dd($otprequired); exit;
                if($walletpinupdateotp->value == "yes") {
                    if($post->has('otp') && $post->otp != ""){
                        $otp = User::where('otpverify', $post->otp)->where('id', $post->id)->count();
                        if($otp > 0){
                            $flag = 1;
                        }else{
                            return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                        }
                    } else {

                        $otp = rand(000000, 999999);
                        User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
                        if (\Myhelper::is_template_active(2))
                        {
                            $msg = \Myhelper::get_whatsapp_content(2);
                            $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                            $send = \Myhelper::sms($user->mobile, $msg);
                            
                            //$send = 'success';
                        }

                        return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                       
                    }
                }






                User::where('id', $post->id)->update(['walletpin' => $post->walletpin]);

                return response()->json(['status'=>'success', 'message' => 'Wallet pin updated successfully!'], 200);

                break;
            case 'password':
                if(($post->id != \Auth::id()) && !\Myhelper::can('member_password_reset')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(($post->id == \Auth::id()) && !\Myhelper::can('password_reset')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(\Myhelper::hasNotRole('admin')){
                    $credentials = [
                        'mobile' => \Auth::user()->mobile,
                        'password' => $post->oldpassword
                    ];
            
                    if(!\Auth::validate($credentials)){
                        return response()->json(['errors' =>  ['oldpassword'=>'Please enter corret old password']], 422);
                    }
                }



                $user = User::where('id', $post->id)->first();
                
                $passwordupdateotp = \App\Model\PortalSetting::where('code', 'passwordupdateotp')->first();
                //dd($otprequired); exit;
                if($passwordupdateotp->value == "yes") {
                    if($post->has('otp') && $post->otp != ""){
                        $otp = User::where('otpverify', $post->otp)->where('id', $post->id)->count();
                        if($otp > 0){
                            $flag = 1;
                        }else{
                            return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                        }
                    } else {

                        $otp = rand(000000, 999999);
                        User::where('mobile', \Auth::user()->mobile)->update(['otpverify'=>$otp]);
                        if (\Myhelper::is_template_active(2))
                        {
                            $msg = \Myhelper::get_whatsapp_content(2);
                            $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                            $send = \Myhelper::sms(\Auth::user()->mobile, $msg);
                            
                            //$send = 'success';
                        }
                        //dd([$post->mobile, $msg]); exit;
                        return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                       
                    }
                }



                

                $post['passwordold'] = $post->password;
                $post['password'] = bcrypt($post->password);
                $post['resetpwd'] = "changed";

                break;
            
            case 'profile':
                if(($post->id != \Auth::id()) && !\Myhelper::can('member_profile_edit')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(($post->id == \Auth::id()) && !\Myhelper::can('profile_edit')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $user = User::where('id', $post->id)->first();
                if($user->role->slug == "whitelable" && $user->company_id != $post->company_id){
                    $users = \Myhelper::getParents($post->id);
                    User::whereIn('id', $users)->where('id', '!=', $post->id)->update(['company_id' => $post->company_id]);
                }
                
                break;

            case 'mstock' :
            case 'dstock' :
            case 'rstock' :
                if(!\Myhelper::can('member_stock_manager')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(\Myhelper::hasNotRole(['admin'])){
                    if($post->mstock > 0 && \Auth::user()->mstock < $post->mstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }

                    if($post->dstock > 0 && \Auth::user()->dstock < $post->dstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }
        
                    if($post->rstock > 0 && \Auth::user()->rstock < $post->rstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }
                }

                if($post->mstock != ''){
                    User::where('id', \Auth::id())->decrement('mstock', $post->mstock);
                    $response = User::where('id', $post->id)->increment('mstock', $post->mstock);
                }

                if($post->dstock != ''){
                    User::where('id', \Auth::id())->decrement('dstock', $post->dstock);
                    $response = User::where('id', $post->id)->increment('dstock', $post->dstock);
                }

                if($post->rstock != ''){
                    User::where('id', \Auth::id())->decrement('rstock', $post->rstock);
                    $response = User::where('id', $post->id)->increment('rstock', $post->rstock);
                }

                if($response){
                    return response()->json(['status'=>'success'], 200);
                }else{
                    return response()->json(['status'=>'fail'], 400);
                }

                break;

            case 'bankdata':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                break;

            case 'mapping':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $user = User::find($post->id);
                $parent = User::find($post->parent_id);

                if($parent->role->slug == "retailer"){
                    return response()->json(['status' => "Invalid mapping member"], 400);
                }

                switch ($user->role->slug) {
                    case 'retailer':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','distributor', 'md', 'whitelable'])->count();
                        break;

                    case 'distributor':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','md', 'whitelable'])->count();
                        break;
                    
                    case 'md':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','whitelable'])->count();
                        break;

                    case 'whitelable':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin'])->count();
                        break;
                }

                if(!$roles){
                    return response()->json(['status' => "Invalid mapping member"], 400);
                }
                break;

            case 'rolemanager':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $roles = Role::where('id', $post->role_id)->whereIn('slug', ['admin'])->count();
                if($roles){
                    return response()->json(['status' => "Invalid member role"], 400);
                }

                $user = User::find($post->id);
                switch ($user->role->slug) {
                    case 'retailer':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['distributor', 'md', 'whitelable'])->count();
                        break;

                    case 'distributor':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['md', 'whitelable'])->count();
                        break;
                    
                    case 'md':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['whitelable'])->count();
                        break;

                    case 'whitelable':
                        return response()->json(['status' => "Invalid member role"], 400);
                        break;
                }

                if(!$roles){
                    return response()->json(['status' => "Invalid member role"], 400);
                }
                break;

            case 'scheme':
                if (\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $users = \Myhelper::getParents($post->id);
                User::whereIn('id', $users)->where('id', '!=', $post->id)->update(['scheme_id' => $post->scheme_id]);
                break;
             case 'apidata':
                DB::table('apitokens')->where('id', 1)->update(['token' => $post->token, 'domain' => $post->domain,'ip'=>$post->ip]);
                break;
        }
        
        if($post->hasFile('pancardpics')){
                    $filename ='pancardpics'.\Auth::id().date('ymdhis').".".$post->file('pancardpics')->guessExtension();
                    $post->file('pancardpics')->move(public_path('kyc/'), $filename);
                    $post['pancardpic'] = $filename;
                    $post['kyc'] = 'submitted';
                }
        if($post->hasFile('aadharcardpics')){
                    $filename ='aadharcardpics'.\Auth::id().date('ymdhis').".".$post->file('aadharcardpics')->guessExtension();
                    $post->file('aadharcardpics')->move(public_path('kyc/'), $filename);
                    $post['aadharcardpic'] = $filename;
                }
        if($post->hasFile('videos')){
                    // if($post->file('videos')->getSize() > 2097152*10);
                    // {
                    //     return response()->json(['status' => "Video size cannot be more than 20MB"], 400);
                    // }
                    
                    $file = $post->file('videos');
                    $filenamevideo = 'videos'.\Auth::id().date('ymdhis').".".$file->guessExtension();
            $path = public_path().'/kyc/';
            $file->move($path, $filenamevideo);
            $post['video'] = $filenamevideo;
                }
       
        $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], $post->all());
        if($response){
            return response()->json(['status'=>'success'], 200);
        }else{
            return response()->json(['status'=>'fail'], 400);
        }
    }
}
