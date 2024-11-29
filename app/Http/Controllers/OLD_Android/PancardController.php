<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Report;
use App\Model\Utiid;
use App\Model\Provider;
use Carbon\Carbon;

class PancardController extends Controller
{
    public function transaction (Request $post)
    {
        if(!in_array($post->type, ['utiid', 'token'])){
            return response()->json(['statuscode' => "ERR", "message" => "Type parameter request in invalid"]);
        }

		$rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
        	return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
        	$output['statuscode'] = "ERR";
	        $output['message'] = "User details not matched";
	        return response()->json($output);
        }

        if (!\Myhelper::can('utipancard_service', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        if($user->status != "active"){
            return response()->json(['statuscode' => "ERR", "message" => "Your account has been blocked."]);
        }

        $provider = Provider::where('recharge1', 'utipancard')->first();

        if(!$provider){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
        }

        if(!$provider->api || $provider->api->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Recharge Service Currently Down."]);
        }
        $post['api_id'] = $provider->api;

        switch ($post->type) {
            case 'utiid':
                $post['name'] = $user->name;
                $post['location'] = $user->city;
                $post['contact_person'] = $user->name;
                $post['pincode'] = $user->pincode;
                $post['state'] = $user->state;
                $post['email'] = $user->email;
                $post['mobile'] = $user->mobile;
                $post['user_id'] = $user->id;
                $post['type'] = "new";
                
                $rules = array(
	                'name'     => 'required',
	                'location' => 'required',
	                'contact_person' => 'required',
	                'pincode'  => 'required|numeric|digits:6',
	                'state'    => 'required',
	                'email'    => 'required',
	                'mobile'   => 'required|numeric|digits_between:10,11',
	            );

	            $validate = \Myhelper::FormValidator($rules, $post);
		        if($validate != "no"){
		        	return $validate;
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
                if (env('APP_ENV') != "local") {
                    $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "no");
                }else{
                    $result = [
                        'error' => false,
                        'response' => json_encode([
                            'statuscode' => 'TXN',
                            'message'  => 'local'
                        ])
                    ];
                }

                if(!$result['error'] || $result['response'] != ''){
                    $doc = json_decode($result['response']);
                    if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
                        $parameter['user_id'] = $post->user_id;
                        $parameter['api_id'] = $provider->api->id;
                        $parameter['vleid'] = $doc->psaid;
                        $action = Utiid::create($parameter);
                        if ($action) {
                            return response()->json(['statuscode' => "TXN", "message" => "Utiid creation request submitted"]);
                        }else{
                            return response()->json(['statuscode' => "TXF", "message" => "Task Failed, please try again"]);
                        }
                    }else{
                        return response()->json(['statuscode' => "TXF", "message" => (isset($doc->message))? $doc->message : "Task Failed, please try again"]);
                    }
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Task Failed, please try again"]);
                }
                break;

            case 'token':
                $rules = array(
                    'vleid'   => 'required',
                    'tokens'  => 'required|numeric|min:1',
                );
                
                $validate = \Myhelper::FormValidator($rules, $post);
		        if($validate != "no"){
		        	return $validate;
		        }

                if($user->mainwallet - $this->mainlocked() < $post->tokens * 107){
                    return response()->json(['statuscode' => "ERR", "message"=> 'Low Balance, Kindly recharge your wallet.']);
                }

                $vledata = Utiid::where('user_id', $user->id)->first();

                if(!$vledata){
                	return response()->json(['statuscode' => "ERR", "message"=> 'Vle id not found']);
                }

                $previousrecharge = Report::where('number', $vledata->vleid)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                if($previousrecharge > 0){
                    return response()->json(['statuscode' => "ERR", "message"=> 'Same Transaction allowed after 2 min.'], 400);
                }

                $post['amount'] = $post->tokens * 107;
                $post['profit'] = $post->tokens * \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                $action = User::where('id', $post->user_id)->decrement('mainwallet', $post->amount - $post->profit);
                if ($action) {

                    $insert = [
                        'number' => $vledata->vleid,
                        'mobile' => $user->mobile,
                        'provider_id' => $provider->id,
                        'api_id' => $provider->api->id,
                        'amount' => $post->amount,
                        'profit' => $post->profit,
                        'txnid'  => $post->txnid,
                        'option1' => $post->tokens,
                        'status'  => 'pending',
                        'user_id'    => $user->id,
                        'credit_by'  => $user->id,
                        'rtype'      => 'main',
                        'via'        => 'app',
                        'balance'    => $user->mainwallet,
                        'trans_type' => 'debit',
                        'product'    => 'utipancard'
                    ];

                    $report = Report::create($insert);
                    
                    $parameter['token'] = $provider->api->username;
                    $parameter['vleid'] = $vledata->vleid;
                    $parameter['pantokens'] = $post->tokens;
                    $parameter['apitxnid'] = $post->txnid;

                    if(env('APP_ENV') == "local") {
                        $url = $provider->api->url."/request";
                        $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "yes", "App\Model\Report", $post->txnid);
                    }else{
                        $result = [
                            'error' => false,
                            'response' => json_encode([
                                'statuscode' => 'TXN',
                                'status'     => 'pending',
                                'message'    => 'Transaction Successfull',
                                'txnid'      => 'local'
                            ])
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
                        $output['statuscode'] = "TXN";
                        $output['message'] = "Uti Pancard Token Request Submitted";
                    }else{
                        User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                        Report::where('id', $report->id)->update($update);
                        $output['statuscode'] = "TXF";
                        $output['message'] = $update['description'];  
                    }
                    $output['txnid']   = $post->txnid;
                    $output['rrn']     = $post->txnid;
                    return response()->json($output, 200);
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Task Failed, please try again"]);
                }
                break;
        }
    }

    public function status(Request $post)
    {
        if(!in_array($post->type, ['utiid', 'token'])){
            return response()->json(['statuscode' => "ERR", "message" => "Type parameter request in invalid"]);
        }

        $rules = array(
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
            'txnid'    => 'required|numeric',
            'type'     => 'required'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
            $output['statuscode'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('utipancard_status', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        switch ($post->type) {
            case 'utiid':
                $report = Utiid::where('id', $post->txnid)->first();
                $url = $report->api->url.'/status?token='.$report->api->username.'&vle_id='.$report->vleid;
                break;
            
            case 'token':
                $report = Report::where('id', $post->txnid)->first();
                if(!$report || !in_array($report->status , ['pending', 'success'])){
                    return response()->json(['status' => "Recharge Status Not Allowed"], 400);
                }
        
                $url = $report->api->url.'/request/status?token='.$report->api->username.'&txnid='.$report->txnid;
                break;
        }

        $method = "GET";
        $parameter = "";
        $header = [];

        if (env('APP_ENV') == "local") {
                $result = \Myhelper::curl($url, $method, $parameter, $header);
            }else{
                $result = [
                    'error' => false,
                    'response' => json_encode([
                        'statuscode' => 'TXN',
                        'message'=> 'local'
                    ]) 
                ];
            }
        if($result['response'] != ''){
            switch ($post->type) {
                case 'utiid':
                    $doc = json_decode($result['response']);
                    if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
                        $update['status'] = "success";
                        $update['remark'] = $doc->message;

                        $output['statuscode'] = "TXN";
                        $output['txn_status'] = "success";
                        $output['message'] = $doc->message;
                    }elseif(isset($doc->statuscode) && $doc->statuscode == "TXF"){
                        $update['status'] = "failed";
                        $update['remark'] = $doc->message;

                        $output['statuscode'] = "TXR";
                        $output['txn_status'] = "failed";
                        $output['message'] = $doc->message;

                    }else{
                        $update['status'] = "Unknown";

                        $output['statuscode'] = "TNF";
                        $output['txn_status'] = "unknown";
                        $output['message'] = $doc->message;
                    }
                    $product = "utiid";
                break;

                case 'token':
                    $doc = json_decode($result['response']);
                    if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
                        $update['status'] = "success";

                        $output['statuscode'] = "TXN";
                        $output['txn_status'] = "success";
                        $output['message']    = $doc->message;

                    }elseif(isset($doc->statuscode) && $doc->statuscode == "TXF"){
                        $update['status'] = "reversed";

                        $output['statuscode'] = "TXR";
                        $output['txn_status'] = "reversed";
                        $output['message']    = $doc->message;
                    }elseif(isset($doc->statuscode) && $doc->statuscode == "TUP"){
                        $update['status'] = "pending";

                        $output['statuscode'] = "TUP";
                        $output['txn_status'] = "pending";
                        $output['message']    = $doc->message;
                    }else{
                        $update['status'] = "Unknown";

                        $output['statuscode'] = "TNF";
                        $output['txn_status'] = "unknown";
                        $output['message'] = $doc->message;
                    }
                    $product = "utipancard";
                    break;
            }

            if ($update['status'] != "Unknown") {
                $reportupdate = Report::where('id', $report->id)->update($update);
                if ($reportupdate && $update['status'] == "reversed" && $product == "utipancard") {
                    \Myhelper::transactionRefund($post->id, $product);
                }

                if($report->user->role->slug == "apiuser" && $report->status == "pending" && $post->status != "pending"){
                    \Myhelper::callback($report, $product);
                }
            }
            return response()->json($output);
        }else{
            return response()->json(['statuscode' => "ERR", "message" => "Something went wrong, contact your service provider"]);
        }
    }
}
