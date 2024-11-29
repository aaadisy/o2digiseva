<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Fundbank;
use App\Model\Api;
use App\Model\Role;
use App\User;
use App\Model\Provider;
use App\Model\PortalSetting;
use App\Model\Complaintsubject;
use App\Model\Fcmtoken;
use Illuminate\Validation\Rule;
use DB;

use App\Model\Whatsapptemplate;

class SetupController extends Controller
{
    public function index($type)
    {
        switch ($type) {
            case 'api':
                $permission = "setup_api";
                break;

            case 'bank':
                $permission = "setup_bank";
                break;

            case 'operator':
                $permission = "setup_operator";
                $data['apis'] = Api::where('status', '1')->whereIn('type', ['money', 'recharge', 'bill', 'pancard', 'fund','insurance','cashdeposit','wt'])->get(['id', 'product']);
                break;
            
            case 'complaintsub':
                $permission = "complaint_subject";
                break;

            case 'portalsetting':
                $data['settlementtype'] = PortalSetting::where('code', 'settlementtype')->first();
                $data['otplogin'] = PortalSetting::where('code', 'otplogin')->first();
                $data['walletpinupdateotp'] = PortalSetting::where('code', 'walletpinupdateotp')->first();
                $data['wotplogin'] = PortalSetting::where('code', 'wotplogin')->first();
                $data['cashdeposite'] = PortalSetting::where('code', 'cashdeposite')->first();
                $data['wt'] = PortalSetting::where('code', 'wt')->first();
                $data['walletpinupdateotp'] = PortalSetting::where('code', 'walletpinupdateotp')->first();
                $data['passwordupdateotp'] = PortalSetting::where('code', 'passwordupdateotp')->first();
                $data['manualpayoutotp'] = PortalSetting::where('code', 'manualpayoutotp')->first();
                
                $data['otpsendmailid']   = PortalSetting::where('code', 'otpsendmailid')->first();
                $data['otpsendmailname'] = PortalSetting::where('code', 'otpsendmailname')->first();
                $data['bcid']   = \App\Model\PortalSetting::where('code', 'bcid')->first();
                $data['cpid']   = \App\Model\PortalSetting::where('code', 'cpid')->first();
                $data['transactioncode']   = \App\Model\PortalSetting::where('code', 'transactioncode')->first();
                $data['settlementcharge']   = \App\Model\PortalSetting::where('code', 'settlementcharge')->first();
                $data['settlementcharge1k']   = \App\Model\PortalSetting::where('code', 'settlementcharge1k')->first();
                $data['settlementcharge25k']   = \App\Model\PortalSetting::where('code', 'settlementcharge25k')->first();
                $data['settlementcharge2l']   = \App\Model\PortalSetting::where('code', 'settlementcharge2l')->first();
                $data['banksettlementtype'] = PortalSetting::where('code', 'banksettlementtype')->first();
                $data['low_balance_amount'] = PortalSetting::where('code', 'low_balance_amount')->first();
                $data['cd_surcahrge_type'] = PortalSetting::where('code', 'cd_surcahrge_type')->first();
                $data['cd_surcahrge_value'] = PortalSetting::where('code', 'cd_surcahrge_value')->first();
                $data['wt_surcahrge_type'] = PortalSetting::where('code', 'wt_surcahrge_type')->first();
                $data['wt_surcahrge_value'] = PortalSetting::where('code', 'wt_surcahrge_value')->first();
                $data['batch'] = PortalSetting::where('code', 'batch')->first();
                $data['mainlocked'] = PortalSetting::where('code', 'mainlocked')->first();
                $data['aepslocked'] = PortalSetting::where('code', 'aepslocked')->first();
                $data['master_2fa_cost'] = PortalSetting::where('code', 'master_2fa_cost')->first();
                $data['matm_service'] = PortalSetting::where('code', 'matm_service')->first();
                $data['aeps_service'] = PortalSetting::where('code', 'aeps_service')->first();
                $data['payout_service'] = PortalSetting::where('code', 'payout_service')->first();
                $data['recharge_service'] = PortalSetting::where('code', 'recharge_service')->first();
                $data['bbps_service'] = PortalSetting::where('code', 'bbps_service')->first();
                $data['pancard_service'] = PortalSetting::where('code', 'pancard_service')->first();
                $data['moneytransfer_service'] = PortalSetting::where('code', 'moneytransfer_service')->first();
                $data['appsetting'] = PortalSetting::where('code', 'app_setting')->first();
                $data['morphossl'] = PortalSetting::where('code', 'morphossl')->first();
                $permission = "portal_setting";
                break;
            
            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }
        $data['type'] = $type;

        return view("setup.".$type)->with($data);
    }

    public function update(Request $post)
    {
        switch ($post->actiontype) {
            case 'api':
                $permission = "setup_api";
                break;

            case 'bank':
                $permission = "setup_bank";
                break;

            case 'operator':
                $permission = "setup_operator";
                break;

            case 'complaintsub':
                $permission = "complaint_subject";
                break;

            case 'portalsetting':
                $permission = "portal_setting";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'bank':
                $rules = array(
                    'name'    => 'sometimes|required',
                    'account'    => 'sometimes|required|numeric|unique:fundbanks,account'.($post->id != "new" ? ",".$post->id : ''),
                    'ifsc'    => 'sometimes|required',
                    'branch'    => 'sometimes|required'  
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $post['user_id'] = \Auth::id();
                $action = Fundbank::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            case 'api':
                

                $rules = array(
                    'product'    => 'sometimes|required',
                    'name'    => 'sometimes|required',
                    'code'    => 'sometimes|required|unique:apis,code'.($post->id != "new" ? ",".$post->id : ''),
                    'type' => ['sometimes', 'required', Rule::In(['recharge', 'bill', 'money', 'pancard', 'fund'])],
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
               
                $post['option1'] = $post->optional1;
                $post['api_name'] = $post->product;
                unset($post['optional1']);
                $action = Api::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'operator':
                $rules = array(
                    'name'    => 'sometimes|required',
                    'recharge1'    => 'sometimes|required',
                    'recharge2'    => 'sometimes|required',
                    'recharge3'    => 'sometimes|required',
                    'type' => ['sometimes', 'required', Rule::In(['mobile', 'dth', 'electricity', 'pancard', 'dmt', 'aeps', 'fund','insurance','cashdeposit','wt','aadharpay'])],
                    'api_id'    => 'sometimes|required|numeric',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Provider::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'complaintsub':
                $rules = array(
                    'subject'    => 'sometimes|required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Complaintsubject::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'portalsetting':
                $rules = array(
                    'value'    => 'required',
                    'name'     => 'required',
                    'code'     => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $action = PortalSetting::updateOrCreate(['code'=> $post->code], $post->all());;
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            default:
                # code...
                break;
        }
    }
    
    public function whatsapptemplate(Request $post)
    {
        
        $data['type'] = "whatsapptemplate";

        return view("setup.whatsapptemplate")->with($data);
    }
    
    public function video(Request $post)
    {
        
        $data['type'] = "video";
        $data['videos'] = DB::table('video')->get();

        return view("setup.video")->with($data);
    }
    
    public function videoadd(Request $post)
    {
        if($post->hasFile('video')){
        
        
            $file = $post->file('video');
            $filename = 'tutorial'.\Auth::id().date('ymdhis').".".$file->getClientOriginalName();
            $path = public_path().'/uploads/';
            $file->move($path, $filename);
            $name = $post->name;
            
             $update = DB::table('video')->insert([
                'name' => $name,
                'video' => $filename
                ]);
                
            if($update)
            {
                return  redirect()->back()->with('success','Tutorial Added SuccessFully');  
            }
            else
            {
                return  redirect()->back()->with('error','Tutorial Upload Failed');  
            }
            
            
             
        }
    }
    
    public function videodelete($id)
    {
                $action = DB::table('video')->where('id',$id)->delete();
                if($action)
            {
                return  redirect()->back()->with('success','Tutorial Deleted SuccessFully');  
            }
            else
            {
                return  redirect()->back()->with('error','Tutorial Delete Failed');  
            }
        
    }
    
    public function whatsapptemplateupdate(Request $post)
    {
                $rules = array(
                    'name'    => 'required',
                    'content'     => 'required',
                    'status'     => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $action = Whatsapptemplate::updateOrCreate(['id'=> $post->id], $post->all());;
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }}
                
    
                
    public function bulkmsg()
    {
        $data['type'] = "bulkmsg";
        
        $data['member_type'] = Role::get();

        return view("bulkmsg")->with($data);
    }
    
    public function sendbulkmsg(Request $post)
    {
        $rules = array(
                    'member_type'    => 'required',
                    'msg'     => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                
                if($post->hasFile('sent_img')){
                    $filename ='sent_img'.\Auth::id().date('ymdhis').".".$post->file('sent_img')->guessExtension();
                    $post->file('sent_img')->move(public_path('whatsappimg/'), $filename);
                    //$post['sent_img'] = $filename;
                    $post_sent_img = $filename;

                    $userlist = User::where('role_id',$post->member_type)->where('kyc', 'verified')->get();
                    foreach($userlist as $user)
                    {
                        $msg = urlencode($post->msg);
                        $send = \Myhelper::smsImg($user->mobile, $msg, $post_sent_img);
                    }
                    
                }else{
                    $userlist = User::where('role_id',$post->member_type)->where('kyc', 'verified')->get();
                    foreach($userlist as $user)
                    {
                        $msg = urlencode($post->msg);
                        $send = \Myhelper::sms($user->mobile, $msg);
                    }
                }

                
                
                
                if ($send) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Msg Sending Failed, please try again"], 200);
                }
    }
    
    public function apiswitching()
    {
        $data['type'] = "apiswitching";
        $data['apis'] = Api::whereIn('type', ['recharge', 'bill'])->where('status', '1')->get(['id', 'product']);
        $data['providers'] = Provider::whereIn('type', ['mobile', 'dth'])->where('status', '1')->get(['id', 'name']);

        return view("setup.apiswitching")->with($data);
    }
    
    public function saveapiswitching(request $post)
    {
        
        $api_id = $post->id; // if failed setich to this api
        $provider_id = $post->provider_id; //operator id
        $provider_id_api = $post->provider_id_api; //main api 
        
        
        $count = DB::table('api_switch')->where('provider_id_api',$provider_id_api)->where('provider_id',$provider_id)->count();
        
        //$count = DB::table('api_switch')->where('provider_id_api',$provider_id_api)->where('provider_id',$provider_id)->where('api_id',$api_id)->count();
        
       
        if($count == '0')
        {
           $update = DB::table('api_switch')->insert([
    'provider_id' => $provider_id,
    'api_id' => $api_id,
    'provider_id_api' => $provider_id_api
]);
        }
        else
        {
          $update =  DB::table('api_switch')->where('provider_id_api',$provider_id_api)->where('provider_id',$provider_id)->update([
    'provider_id' => $provider_id,
    'api_id' => $api_id,
    'provider_id_api' => $provider_id_api
]);
        }
        if($update){
            return response()->json(['status' => "success"], 200);
        }
        else{
            return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
            }

        
    }
    
    public function banners()
    {
        //echo "yes"; exit;
        $data['type'] = "banners";
        $data['banners'] = DB::table('banners')->get();

        return view("banners")->with($data);
    }
    
    public function notification()
    {
        $data['type'] = "notifications";
        $data['notifications'] = DB::table('notifications')->get();
        $data['users'] = DB::table('users')->where('fcm_token', '!=', '')->where('kyc', 'verified')->get();
        $data['member_type'] = Role::get();
        //dd($data); exit;
        return view("notifications")->with($data);
    }

    public function savenotification(Request $post){
        
        $this->validate($post, [
            'input_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
            'member_type' => 'required'
        ]);
        
        if($post->hasFile('input_img')){
            $image = $post->file('input_img');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/notifications');
            $image->move($destinationPath, $name);
            
            if(isset($post->member_type) && !empty($post->member_type)){
                $users = User::where('fcm_token', '!=', '')->where('role_id', $post->member_type)->where('kyc', 'verified')->get();

                if(isset($users) && !empty($users)){
                    $html = '';
                    foreach($users as $user){
                        $fcms = Fcmtoken::where('user_id', $user->id)->get();
                        foreach($fcms as $fcm){
                            $data = DB::table('notifications')->insert(['image' => url('/public/notifications').'/'.$name,'title'=>$post->title, 'content' => $post->content, 'user_id' => $user->id]);
                            //$fcm_token = "fi_KrasJQbOI14UQEzl36D:APA91bE_i-G-zwyg8UDhyCZJOFh55dmOrl1b2eACCn0E4GlxFvOwfjbEgSkkdaBF_vBpdlUIwnBycNxV76UE8HSoa7-dwFNPLI2CIkWR5I8-U1VeRCO1k7oN6LST2-uDzA0QkJZhooXz";
                            $res = $this->sendNotification($fcm->token, $post->title, $post->content, url('/public/notifications').'/'.$name);
                            $res = json_decode($res);

                            $html .= 'success';
                        }
                        
                    }
                }
            }
           return back()->with('success','Notification created successfully'.$html);
        }else{
            
            if(isset($post->member_type) && !empty($post->member_type)){
                $users = User::where('fcm_token', '!=', '')->where('role_id', $post->member_type)->where('kyc', 'verified')->get();

                if(isset($users) && !empty($users)){
                    $html = '';
                    foreach($users as $user){
                        $data = DB::table('notifications')->insert(['image' => "",'title'=>$post->title, 'content' => $post->content, 'user_id' => $user->id]);
                        //$fcm_token = "fi_KrasJQbOI14UQEzl36D:APA91bE_i-G-zwyg8UDhyCZJOFh55dmOrl1b2eACCn0E4GlxFvOwfjbEgSkkdaBF_vBpdlUIwnBycNxV76UE8HSoa7-dwFNPLI2CIkWR5I8-U1VeRCO1k7oN6LST2-uDzA0QkJZhooXz";
                        $res = $this->sendNotification($user->fcm_token, $post->title, $post->content, url('/public/notifications').'/'.$name);
                        $res = json_decode($res);
                        $html .= $res->success;
                    }
                }
            }
            
            //dd($res); exit;
           return back()->with('success','Notification created successfully'.$html);
        }
    }
    
    public function savebanner(request $request)
    {
        $this->validate($request, [
        'input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'type' => 'required',
    ]);


    if ($request->hasFile('input_img')) {
        $image = $request->file('input_img');
        $name = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/images');
        $image->move($destinationPath, $name);
       $type = $request->type;
       DB::table('banners')->insert([
    'banner' => $name,'type'=>$type]);
        
        return back()->with('success','Image Upload successfully');
    }
    }
    
    public function deletebanner($request)
    {
        DB::table('banners')->where('id',$request)->delete();
        return back()->with('success','Image Deleted successfully');
    }
    
    public function deletenotification($request)
    {
        DB::table('notifications')->where('id',$request)->delete();
        return back()->with('success','Notification Deleted successfully');
    }

    public function sendNotification($fcm_token, $title, $body, $image = null){
        if($image != null){
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{"to":"'.$fcm_token.'","notification":{"title":"'.$title.'","body":"'.$body.'","sound": "two.mp3","click_action": "NOTIFICATION_ACTIVITY", "image": "'.$image.'"}}',
              CURLOPT_HTTPHEADER => array(
                'Authorization: key=AAAAGCzzme8:APA91bGUXW2l7Zq0XRZExSwiham42dI5MeqQJs528Sw2k60MtQxfuePYCLf07ua4JSvzc9hYzaGeBc5f7wBD8UHJ5l7cQQMTu4ooHZKklm43-2Y-fb0lNlPKNQT199obLi4ZcltQhiB_',
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }else{
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'{"to":"'.$fcm_token.'","notification":{"title":"'.$title.'","sound": "two.mp3","click_action": "NOTIFICATION_ACTIVITY","body":"'.$body.'"}}',
              CURLOPT_HTTPHEADER => array(
                'Authorization: key=AAAAGCzzme8:APA91bGUXW2l7Zq0XRZExSwiham42dI5MeqQJs528Sw2k60MtQxfuePYCLf07ua4JSvzc9hYzaGeBc5f7wBD8UHJ5l7cQQMTu4ooHZKklm43-2Y-fb0lNlPKNQT199obLi4ZcltQhiB_',
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }
        

    }
}
