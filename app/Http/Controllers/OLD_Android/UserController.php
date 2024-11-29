<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Mahaagent;
use App\Model\Api;
use App\Model\Utiid;

class UserController extends Controller
{
    public function login(Request $post)
    {
        $rules = array(
            'password' => 'required',
            'mobile'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('mobile', $post->mobile)->with(['role'])->first();
        if(!$user){
            return response()->json(['status' => 'ERR', 'message' => "Your aren't registred with us." ]);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password])) {
            return response()->json(['status' => 'ERR', 'message' => 'Username and Password is incorrect']);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password, 'status' => "active"])) {
            return response()->json(['status' => 'ERR', 'message' => 'Your account currently de-activated, please contact administrator']);
        }

        if($user->apptoken == "none"){
            do {
                $string = str_random(40);
            } while (User::where("apptoken", "=", $string)->first() instanceof User);
            User::where('mobile', $post->mobile)->update(['apptoken' => $string]);
        }

        $user = User::where('mobile', $post->mobile)->with(['role'])->first();
        $utiid = Utiid::where('user_id', $user->id)->first();
        if($utiid){
            $user['utiid'] = $utiid->vleid;
            $user['utiidtxnid'] = $utiid->id;
            $user['utiidstatus'] = $utiid->status;
        }else{
            $user['utiid'] = 'no';
            $user['utiidstatus'] = 'no';
            $user['utiidtxnid'] = 'no';
        }
        $user['tokenamount'] = '107';
        return response()->json(['status' => 'TXN', 'message' => 'User details matched successfully', 'userdata' => $user]);
    }

    public function getbalance(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first(['mainwallet','aepsbalance']);
        if($user){
            $output['status'] = "TXN";
            $output['message'] = "Balance Fetched Successfully";
            $output['data'] = [ "mainwallet" => $user->mainwallet , "aepsbalance" => $user->aepsbalance];
        }else{
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }
        return response()->json($output);
    }

    public function aepsInitiate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        if (!\Myhelper::can('aeps_service', $post->user_id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->count();
        if($user){
            $agent = Mahaagent::where('user_id', $post->user_id)->first();

            if($agent){
                $api = Api::where('code', 'aeps')->first();

                $data["bc_id"] = $agent->bc_id;
                $data["code"]  = $api->optional1;
                $data["token"] = $api->username;

                $url = $api->url."/transaction";
                $header = array("Content-Type: application/json");
                $result = \Myhelper::curl($url, "POST", json_encode($data), $header, "no");
                //dd([$url, json_encode($data), $result]);
        
                if($result['response'] != ''){
                    $datas = json_decode($result['response']);
                    if(isset($datas->statuscode) && $datas->statuscode == "TXN"){
                        $output['status'] = "TXN";
                        $output['message'] = "Deatils Fetched Successfully";
                        $output['data'] = [ 
                                            "saltKey"   => $datas->data->saltkey, 
                                            "secretKey" => $datas->data->secretkey,
                                            "BcId"      => $agent->bc_id,
                                            "UserId"    => $post->user_id,
                                            "bcEmailId" => $agent->emailid,
                                            "Phone1"    => $agent->phone1
                                        ];
                    }else{
                        $output['status'] = "ERR";
                        $output['message'] = "Technical Error, Contact Service Provider";
                    }
                }else{
                    $output['status'] = "ERR";
                    $output['message'] = "Technical Error, Contact Service Provider";
                }
            }else{
                $output['status'] = "ERR";
                $output['message'] = "Aeps registration pending";
            }
        }else{
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }
    
    
        public function passwordResetRequest(Request $post)
    {
        $rules = array(
            'mobile'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = \App\User::where('mobile', $post->mobile)->first();
        if($user){
            $otp = rand(11111111, 99999999);
            $content = "Dear partner , your password reset token is ".$otp;
            $sms = \Myhelper::sms($post->mobile, $content);
            $otpmailid = \App\Model\PortalSetting::where('code', 'otpsendmailid')->first();
            $otpmailname = \App\Model\PortalSetting::where('code', 'otpsendmailname')->first();
            $mail = \Myhelper::mail('mail.password', ["token" => $otp, "name" => $user->name], $user->email, $user->name, $otpmailid->value, $otpmailname->value, "Reset Password");
            if($sms == "success" || $mail == "success"){
                \App\User::where('mobile', $post->mobile)->update(['remember_token'=> $otp]);
                return response()->json(['status' => 'TXN', 'message' => "Password reset token sent successfully"]);
            }else{
                return response()->json(['status' => 'ERR', 'message' => "Something went wrong"]);
            }
        }else{
            return response()->json(['status' => 'ERR', 'message' => "You aren't registered with us"]);
        } 
    }

    public function passwordReset(Request $post)
    {
        $rules = array(
            'mobile'  =>'required|numeric',
            'password'  =>'required',
            'token'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = \App\User::where('mobile', $post->mobile)->where('remember_token' , $post->token)->get();
        if($user->count() == 1){
            $update = \App\User::where('mobile', $post->mobile)->update(['password' => bcrypt($post->password),'passwordold' => $post->password]);
            if($update){
                return response()->json(['status' => "TXN", 'message' => "Password reset successfully"], 200);
            }else{
                return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
            }
        }else{
            return response()->json(['status' => 'ERR', 'message' => "Please enter valid token"], 400);
        }
    }

    public function changepassword(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'oldpassword'  =>'required|min:8',
            'password'  =>'required|min:8',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->first();
        if(!\Myhelper::can('password_reset', $post->user_id)){
            return response()->json(['status' => 'ERR', 'message' => "Permission Not Allowed"]);
        }

        if(\Myhelper::hasNotRole('admin')){
            $credentials = [
                'mobile' => $user->mobile,
                'password' => $post->oldpassword
            ];
    
            if(!\Auth::validate($credentials)){
                return response()->json(['status' => 'ERR', 'message' => "Please enter corret old password"]);
            }
        }

        $post['passwordold'] = $post->password;
        $post['password'] = bcrypt($post->password);

        $response = User::where('id', $post->user_id)->updateOrCreate(['password' => bcrypt($post->password)]);
        if($response){
            return response()->json(['status' => 'TXN', 'message' => 'User password changed successfully']);
        }else{
            return response()->json(['status' => 'ERR', 'message' => "Something went wrong"]);
        }
    }
    
    
    public function getState()
    {
        $state = \App\Model\Circle::all(['state']);
        return response()->json(['status' => 'TXN', 'message' => 'State Details', 'data' => $state]);
    }

    public function changeProfile(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'name'     =>'required',
            'email'    =>'required|email',
            'address'  =>'required',
            'pincode'  =>'required|numeric|digits:6',
            'pancard'     =>'required',
            'aadharcard'  =>'required|numeric|digits:12',
            'shopname'    =>'required',
            'city'    =>'required',
            'state'   =>'required'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->count();

        if($user == 0){
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        $update = User::where('id', $post->user_id)->update(array(
            'name'     => $post->name,
            'email'    => $post->email,
            'address'  => $post->address,
            'pincode'  => $post->pincode,
            'pancard'     => $post->pancard,
            'aadharcard'  => $post->aadharcard,
            'shopname'    => $post->shopname,
            'city'    => $post->city,
            'state'   => $post->state
        ));

        if($update){
            return response()->json(['status' => 'TXN', 'message' => 'User profile updated successfully']);
        }else{
            return response()->json(['status' => 'ERR', 'message' => "Something went wrong"]);
        }
    }
}
