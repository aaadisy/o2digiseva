<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Provider;
use App\User;
use App\Model\Report;
use Carbon\Carbon;

class RechargeController extends Controller
{
    public function providersList(Request $post)
    {
        //dd($post); exit;
    	$providers = Provider::where('type', $post->type)->where('status', "1")->get(['id', 'name']);
    	return response()->json(['statuscode' => "TXN", 'message' => "Provider Fetched Successfully", 'data' => $providers]);
    }

    public function transaction(Request $post)
    {
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'provider_id'      => 'required|numeric',
            'amount'      => 'required|numeric|min:10',
            'number' => 'required|numeric'
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

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        if($user->status != "active"){
            return response()->json(['statuscode' => "ERR", "message" => "Your account has been blocked."]);
        }

        $provider = Provider::where('id', $post->provider_id)->first();

        if(!$provider){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
        }

        // if(!$provider->api || $provider->api->status == 0){
        //     return response()->json(['statuscode' => "ERR", "message" => "Recharge Service Currently Down."]);
        // }

        if($user->mainwallet - $this->mainlocked() < $post->amount){
            return response()->json(['statuscode' => "ERR", "message"=> 'Low Balance, Kindly recharge your wallet.']);
        }

        switch ($provider->api->code) {
            case 'recharge1':
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);
                $url = $provider->api->url."/pay?token=".$provider->api->username."&number=".$post->number."&operator=".$provider->recharge1."&amount=".$post->amount."&apitxnid=".$post->txnid;
                break;
                
            case 'recharge2':
                $method = "POST";
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                $isstv = "false";
                if(in_array($provider->id, ['3', '4'])){
                    $isstv = "true";
                }
                $url = $provider->api->url;
                $url = $provider->api->url."?api_token=".$provider->api->username."&mobile_no=".$post->number."&amount=".$post->amount."&company_id=".$provider->recharge3."&order_id=".$post->txnid."&is_stv=".$isstv;
                break;
        }  

        $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
        if($previousrecharge > 0){
            return response()->json(['statuscode' => "ERR", "message"=> 'Same Transaction allowed after 2 min.'], 400);
        }

        $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
        $debit = User::where('id', $user->id)->decrement('mainwallet', $post->amount - $post->profit);
        if($debit){

            $insert = [
                'number' => $post->number,
                'mobile' => $user->mobile,
                'provider_id' => $provider->id,
                'api_id' => $provider->api->id,
                'amount' => $post->amount,
                'profit' => $post->profit,
                'txnid'  => $post->txnid,
                'status' => 'pending',
                'user_id'=> $user->id,
                'credit_by' => $user->id,
                'rtype' => 'main',
                'via'   => 'app',
                'balance' => $user->mainwallet,
                'trans_type' => 'debit',
                'product'    => 'recharge'
            ];

            $report = Report::create($insert);

            if (env('APP_ENV') != "local") {
                $result = \Myhelper::curl($url, "GET", "", [], "yes", "App\Model\Report", $post->txnid);
            }else{
                $result = [
                    'error' => false,
                    'response' => json_encode([
                    	'status' => 'TXN',
                    	'payid'  => 'local',
                    	'refno'  => 'local',
                    	'message'=> 'local'
                    ]) 
                ];
            }

            if($result['error'] || $result['response'] == ''){
                $update['status'] = "pending";
                $update['payid'] = "pending";
                $update['refno'] = "pending";
            }else{
                switch ($provider->api->code) {
                    case 'recharge1':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "TXN" || $doc->status == "TUP"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->payid;
                                $update['refno'] = $doc->refno;
                            }elseif($doc->status == "TXF"){
                                $update['status'] = "failed";
                                $update['payid'] = $doc->payid;
                                $update['refno'] = (isset($doc->message)) ? $doc->message : "failed";
                            }else{
                                $update['status'] = "failed";
                                if(isset($doc->message) && $doc->message == "Insufficient Wallet Balance"){
                                    $update['refno'] = "Service down for sometime.";
                                }else{
                                    $update['refno'] = (isset($doc->message)) ? $doc->message : "failed";
                                }
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                        }
                        break;

                    case 'recharge2':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "success" || $doc->status == "pending"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->id;
                                //$update['refno'] = isset($doc->tnx_id) ? $doc->tnx_id : isset($doc->response) ? $doc->response : 'pending';
                                $update['description'] = "Recharge Accepted";

                                $output['status'] = "TXN";
                                $output['message'] = "Recharge Accepted";
                                $output['payid'] = $report->id;
                                $output['refno'] = $doc->tnx_id;
                            }else{
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->id)?$doc->id:'failed';
                                $update['refno'] = (isset($doc->response)) ? $doc->response : "Failed";
                                $update['description'] = isset($doc->response) ? $doc->response : "Recharge Failed";

                                $output['status'] = "TXF";
                                $output['message'] = $update['description'];
                                $output['payid'] = $report->id;
                                $output['refno'] = "failed";
                            }
                        }elseif(isset($doc->error) && $doc->error){
                            $update['status'] = "failed";
                            $update['payid'] = isset($doc->id)?$doc->id:'failed';
                            $update['refno'] = (isset($doc->errorMessage)) ? $doc->errorMessage : "Failed";

                            $output['status'] = "TXF";
                            $output['message'] = $update['refno'];
                            $output['payid'] = $report->id;
                            $output['refno'] = $update['refno'];
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                            $update['description'] = "recharge pending";

                            $output['status'] = "TUP";
                            $output['message'] = "Recharge Pending";
                            $output['payid'] = $report->id;
                            $output['refno'] = "pending";
                        }
                        break;
                }
            }

            if($update['status'] == "success" || $update['status'] == "pending"){
                Report::where('id', $report->id)->update($update);
                \Myhelper::commission($report);
                $output['statuscode'] = "TXN";
                $output['message'] = "Recharge Accepted";
            }else{
                User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                Report::where('id', $report->id)->update($update);
                $output['statuscode'] = "TXF";
                $output['message'] = $update['refno'];
            }
            $output['txnid'] = $post->txnid;
            $output['rrn'] = $update['refno'];
            return response()->json($output);
        }else{
            return response()->json(['statuscode' => "ERR", "message" => "Something went wrong"]);
        }
    }

    public function status(Request $post)
    {
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'txnid'      => 'required|numeric'
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

        if (!\Myhelper::can('recharge_status', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        if(!$report || !in_array($report->status , ['pending', 'success'])){
            return response()->json(['status' => "Recharge Status Not Allowed"], 400);
        }

        $report = Report::where('id', $post->txnid)->first();

		switch ($report->api->code) {
			case 'recharge1':
				$url = $report->api->url.'/status?token='.$report->api->username.'&apitxnid='.$report->txnid;
				break;
			
			default:
				return response()->json(['statuscode' => "ERR", "message" => "Recharge Status Not Allowed"]);
				break;
		}

		$method = "GET";
		$parameter = "";
		$header = [];

		if (env('APP_ENV') != "local") {
                $result = \Myhelper::curl($url, $method, $parameter, $header);
            }else{
                $result = [
                    'error' => false,
                    'response' => json_encode([
                    	'statuscode' => 'TXN',
                    	'trans_status'  => 'success',
                    	'refno'  => 'local',
                    	'message'=> 'local'
                    ]) 
                ];
            }
		if($result['response'] != ''){
			switch ($report->api->code) {
				case 'recharge1':
    				$doc = json_decode($result['response']);
    				if($doc->statuscode == "TXN" && ($doc->trans_status =="success" || $doc->trans_status =="pending")){
    					$update['refno'] = $doc->refno;
    					$update['status'] = "success";
    
    					$output['statuscode'] = "TXN";
    					$output['txn_status'] = "success";
    	        		$output['refno'] = $doc->refno;
    
    				}elseif($doc->statuscode == "TXN" && $doc->trans_status =="reversed"){
    					$update['status'] = "reversed";
    					$update['refno'] = $doc->refno;
    
    					$output['statuscode'] = "TXR";
    					$output['txn_status'] = "reversed";
    	        		$output['refno'] = $doc->refno;
    				}else{
    					$update['status'] = "Unknown";
    					$update['refno'] = $doc->message;
    
    					$output['statuscode'] = "TNF";
    					$output['txn_status'] = "unknown";
    	        		$output['refno'] = $doc->message;
    				}
    				break;
    				
    			case 'recharge2':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "success" || $doc->status == "pending"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->txid;
                                $update['refno'] = $doc->opid;
                            }else{
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->txid)?$doc->txid:'Failed';
                                $update['refno'] = "failed";
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                        }
                        break;

                    case 'recharge3':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "success" || $doc->status == "pending"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->id;
                                //$update['refno'] = isset($doc->tnx_id) ? $doc->tnx_id : isset($doc->response) ? $doc->response : 'pending';
                                $update['description'] = "Recharge Accepted";

                                $output['status'] = "TXN";
                                $output['message'] = "Recharge Accepted";
                                $output['payid'] = $report->id;
                                $output['refno'] = $doc->tnx_id;
                            }else{
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->id)?$doc->id:'failed';
                                $update['refno'] = (isset($doc->response)) ? $doc->response : "Failed";
                                $update['description'] = isset($doc->response) ? $doc->response : "Recharge Failed";

                                $output['status'] = "TXF";
                                $output['message'] = $update['description'];
                                $output['payid'] = $report->id;
                                $output['refno'] = "failed";
                            }
                        }elseif(isset($doc->error) && $doc->error){
                            $update['status'] = "failed";
                            $update['payid'] = isset($doc->id)?$doc->id:'failed';
                            $update['refno'] = (isset($doc->errorMessage)) ? $doc->errorMessage : "Failed";

                            $output['status'] = "TXF";
                            $output['message'] = $update['refno'];
                            $output['payid'] = $report->id;
                            $output['refno'] = $update['refno'];
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                            $update['description'] = "recharge pending";

                            $output['status'] = "TUP";
                            $output['message'] = "Recharge Pending";
                            $output['payid'] = $report->id;
                            $output['refno'] = "pending";
                        }
                        break;
			}
			$product = "recharge";

			if ($update['status'] != "Unknown") {
				$reportupdate = Report::where('id', $report->id)->update($update);
				if ($reportupdate && $update['status'] == "reversed") {
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
