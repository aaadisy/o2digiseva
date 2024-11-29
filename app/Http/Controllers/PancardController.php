<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Utiid;
use App\Model\Report;
use App\Model\Provider;
use App\User;
use Carbon\Carbon;

class PancardController extends Controller
{
    public function index($type)
    {
        if(!\Myhelper::service_active('pancard_service'))
        {

            return  redirect()->back()->with('error','Service Currently Deactive');  

        }
        switch ($type) {
            case 'uti':
                $permission = "utipancard_service";
                break;
            
            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }
        $data['type'] = $type;

        switch ($type) {
            case 'uti':
                $data['vledata'] = Utiid::where('user_id', \Auth::id())->first();
                if($data['vledata'] && $data['vledata']->status == "pending"){
                    $provider = Provider::where('recharge1', 'utipancard')->first();
                    $url = $provider->api->url."/status";
                    $parameter['token'] = $provider->api->username;
                    $parameter['vleid'] = $data['vledata']->vleid;
                    //dd($parameter);
                    $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "no");
                    //dd($result);
                    if(!$result['error'] || $result['response'] != ''){
                        $doc = json_decode($result['response']);
                        if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
                            Utiid::where('user_id', \Auth::id())->update(['status' => 'success', 'remark' => "Done"]);
                        }elseif(isset($doc->statuscode) && $doc->statuscode == "TXF"){
                            Utiid::where('user_id', \Auth::id())->update(['status' => 'failed']);
                        }
                        $data['vledata'] = Utiid::where('user_id', \Auth::id())->first();
                    }
                }
                break;
        }

        return view("service.".$type)->with($data);
    }

    public function payment(Request $post)
    {
        switch ($post->actiontype) {
            case 'vleid':
            case 'purchase':
                $permission = "utipancard_service";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'vleid':
                $user = User::where('id', \Auth::id())->first();
                $post['user_id'] = \Auth::id();
                $post['type'] = "new";
                
                $rules = array(
                    'vlepassword'    => 'sometimes|required',
                    'name'    => 'required',
                    'location'    => 'required',
                    'contact_person'    => 'required',
                    'pincode'    => 'required|numeric|digits:6',
                    'state'    => 'required',
                    'email'    => 'required',
                    'mobile'    => 'required|numeric|digits_between:10,11',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status' => $error], 400);
                }

                $provider = Provider::where('recharge1', 'utipancard')->first();
                $post['api_id'] = $provider->api_id;
                if(!$provider){
                    return response()->json(['status' => "Operator Not Found"], 400);
                }
        
                if($provider->status == 0){
                    return response()->json(['status' => "Operator Currently Down."], 400);
                }
        
                if(!$provider->api || $provider->api->status == 0){
                    return response()->json(['status' => "Utipancard Service Currently Down."], 400);
                }

                $parameter['token'] = $provider->api->username;
                $parameter['name'] = $post->name;
                $parameter['location'] = $post->location;
                $parameter['contact_person'] = $post->contact_person;
                $parameter['pincode'] = $post->pincode;
                $parameter['state'] = $post->state;
                $parameter['email'] = $post->email;
                $parameter['mobile'] = $post->mobile;
                $url = $provider->api->url."/create";
                $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "yes", 'App\Model\Utiid' , $post->vleid);
                //dd($result);
                if(!$result['error'] || $result['response'] != ''){
                    $doc = json_decode($result['response']);
                    if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
                        $post['vleid'] = $doc->psaid;
                        $post['vlepassword'] = $doc->psaid;
                        $action = Utiid::create($post->all());
                        if ($action) {
                            return response()->json(['status' => "success"], 200);
                        }else{
                            return response()->json(['status' => "Task Failed, please try again"], 200);
                        }
                    }else{
                        return response()->json(['status' =>(isset($doc->message))? $doc->message : "Task Failed, please try again"], 200);
                    }
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'purchase':
                $rules = array(
                    'vleid'    => 'required',
                    'tokens'    => 'required|numeric|min:1',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $provider = Provider::where('recharge1', 'utipancard')->first();
                $post['provider_id'] = $provider->id;
                if(!$provider){
                    return response()->json(['status' => "Operator Not Found"], 400);
                }
        
                if($provider->status == 0){
                    return response()->json(['status' => "Operator Currently Down."], 400);
                }
        
                if(!$provider->api || $provider->api->status == 0){
                    return response()->json(['status' => "Utipancard Service Currently Down."], 400);
                }

                $user = \Auth::user();
                $post['user_id'] = $user->id;
                if($user->status != "active"){
                    return response()->json(['status' => "Your account has been blocked."], 400);
                }

                if($user->mainwallet - $this->mainlocked() < $post->tokens * 107){
                    return response()->json(['status'=> 'Low Balance, Kindly recharge your wallet.'], 400);
                }
                $vledata = Utiid::where('user_id', \Auth::id())->first();
                $post['amount'] = $post->tokens * 107;
                $post['profit'] = $post->tokens * \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                $previousrecharge = Report::where('number', $vledata->vleid)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                if($previousrecharge > 0){
                    return response()->json(['status'=> 'Same Transaction allowed after 2 min.'], 400);
                }

                $action = User::where('id', $post->user_id)->decrement('mainwallet', $post->amount - $post->profit);
                if ($action) {

                    $insert = [
                        'number' => $vledata->vleid,
                        'mobile' => $user->mobile,
                        'provider_id' => $provider->id,
                        'api_id' => $provider->api->id,
                        'amount' => $post->amount,
                        'profit' => $post->profit,
                        'txnid' => $post->txnid,
                        'option1' => $post->tokens,
                        'status' => 'pending',
                        'user_id'    => $user->id,
                        'credit_by'  => $user->id,
                        'rtype'      => 'main',
                        'via'        => 'portal',
                        'balance'    => $user->mainwallet,
                        'trans_type' => 'debit',
                        'product'    => 'utipancard'
                    ];

                    $report = Report::create($insert);
                    
                    $parameter['token'] = $provider->api->username;
                    $parameter['vleid'] = $vledata->vleid;
                    $parameter['pantokens'] = $post->tokens;
                    $parameter['apitxnid'] = $post->txnid;

                    if (env('APP_ENV') != "local") {
                        $url = $provider->api->url."/request";
                        $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "yes", "App\Model\Report", $post->txnid);
                    }else{
                        $result = [
                            'error' => true,
                            'response' => '' 
                        ];
                    }
        
                    if($result['error'] || $result['response'] == ''){
                        $update['status'] = "pending";
                        $update['payid'] = "pending";
                        $update['refno'] = "pending";
                        $update['description'] = "pan token request pending";
                    }else{
                        $doc = json_decode($result['response']);
                        if(isset($doc->statuscode)){
                            if($doc->statuscode == "TXN"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->txnid;
                                $update['description'] = "Pancard Token Request Accepted";
                            }else{
                                $update['status'] = "failed";
                                if($doc->status == "Insufficient Wallet Balance"){
                                    $update['description'] = "Service down for sometime.";
                                }else{
                                    $update['description'] = (isset($doc->message)) ? $doc->message : "failed";
                                }
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                            $update['description'] = "pan token request pending";
                        }
                    }
        
                    if($update['status'] == "success" || $update['status'] == "pending"){
                        Report::where('id', $report->id)->update($update);
                        \Myhelper::commission($report);
                    }else{
                        User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                        Report::where('id', $report->id)->update($update);
                    }
                    return response()->json($update, 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
        }
    }
}
