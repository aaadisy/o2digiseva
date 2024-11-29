<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Provider;
use App\User;
use App\Model\Report;
use Carbon\Carbon;

class BillpayController extends Controller
{
    public function providersList(Request $post)
    {
        $providers = Provider::where('type', $post->type)->where('status', "1")->get(['id', 'name']);
        return response()->json(['statuscode' => "TXN", 'message' => "Provider Fetched Successfully", 'data' => $providers]);
    }

    public function transaction(Request $post)
    {
        //dd($post->all()); exit;
        if(!in_array($post->type, ['validate', 'payment'])){
            return response()->json(['statuscode' => "ERR", "message" => "Type parameter request in invalid"]);
        }

        if($post->type == "validate"){
            $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
                'provider_id' => 'required|numeric',
                'number'      => 'required|min:8|max:15'
            );
        }else{
            $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
                'provider_id' => 'required|numeric',
                'number'      => 'required|min:8|max:15',
                'mobile'      => 'required|numeric|digits:10',
                'amount'      => 'required|numeric|min:10',
                'biller'      => 'required',
                'duedate'     => 'required'
            );
        }
        

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

        if (!\Myhelper::can('billpayment_service', $user->id)) {
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

        if(!$provider->api || $provider->api->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Recharge Service Currently Down."]);
        }

        switch ($post->type) {
            case 'validate':
                //dd($provider); exit;
                $url = "https://www.mplan.in/api/electricinfo.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$post->number."&operator=".$provider->recharge2;
                //dd($url); exit;
                //$url = $provider->api->url."/validate?token=".$provider->api->username."&operator=".$provider->recharge1."&number=".$post->number."&mobile=".$post->mobile;
                $result = \Myhelper::curl($url, "GET", "", [], "no");
                //dd($result); exit;
                if(!$result['error']){
                    return response($result['response']);
                }else{
                    return response()->json(['statuscode' => 'ERR', 'message' => $result['error']]);
                }
                break;

            case 'payment':
                if($user->mainwallet - $this->mainlocked() < $post->amount){
                    return response()->json(['statuscode' => "ERR", "message"=> 'Low Balance, Kindly recharge your wallet.']);
                }

                $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                if($previousrecharge > 0){
                    return response()->json(['statuscode' => "ERR", "message"=> 'Same Transaction allowed after 2 min.'], 400);
                }

                $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                $debit = User::where('id', $user->id)->decrement('mainwallet', $post->amount - $post->profit);
                if ($debit) {
                    do {
                        $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                    } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                    $insert = [
                        'number' => $post->number,
                        'mobile' => $post->mobile,
                        'provider_id' => $provider->id,
                        'api_id' => $provider->api->id,
                        'amount' => $post->amount,
                        'profit' => $post->profit,
                        'txnid' => $post->txnid,
                        'option1' => $post->biller,
                        'option2' => $post->duedate,
                        'status' => 'pending',
                        'user_id'    => $user->id,
                        'credit_by'  => $user->id,
                        'rtype'      => 'main',
                        'via'        => 'app',
                        'balance'    => $user->mainwallet,
                        'trans_type' => 'debit',
                        'product'    => 'billpay'
                    ];

                    $report = Report::create($insert);

                    $url = $provider->api->url."/payment";
                    $parameter = [
                        "token"=> $provider->api->username,
                        "operator" => $provider->recharge1,
                        "number" => $post->number,
                        "mobile" => $post->mobile,
                        "amount" => $post->amount,
                        "biller" => $post->biller,
                        "duedate" => $post->duedate,
                        "apitxnid" => $post->txnid,
                    ];

                    if (env('APP_ENV') != "local") {
                        $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "yes", "App\Model\Report", $post->txnid);
                    }else{
                        $result = [
                            'error' => false,
                            'response' => json_encode([
                                'statuscode' => 'TXN',
                                'message'  => 'local',
                                'data'  => ['txnid' => "local"]
                            ])
                        ];
                    }

                    if($result['error'] || $result['response'] == ''){
                        $update['status'] = "pending";
                        $update['payid'] = "pending";
                        $update['description'] = "billpayment pending";
                    }else{
                        $doc = json_decode($result['response']);
                        if(isset($doc->statuscode)){
                            if($doc->statuscode == "TXN"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->data->txnid;
                                $update['description'] = "Billpayment Accepted";
                            }elseif($doc->statuscode == "TXF"){
                                $update['status'] = "failed";
                                $update['payid'] = $doc->data->txnid;
                                $update['description'] = (isset($doc->message)) ? $doc->message : "failed";
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
                            $update['description'] = "billpayment pending";
                        }
                    }

                    if($update['status'] == "success" || $update['status'] == "pending"){
                        Report::where('id', $report->id)->update($update);
                        \Myhelper::commission($report);
                        $output['statuscode'] = "TXN";
                        $output['message'] = "Billpayment Request Submitted";
                    }else{
                        User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                        Report::where('id', $report->id)->update($update);
                        $output['statuscode'] = "TXF";
                        $output['message'] = $update['description'];    
                    }
                    $output['txnid']   = $post->txnid;
                    $output['rrn']     = $post->txnid;
                    return response()->json($output);
                }else{
                    return response()->json(['statuscode'=> "ERR" , "message" => 'Transaction Failed, please try again.']);
                }
                break;
        }
    }

    public function status(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
            'txnid'    => 'required|numeric'
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

        if (!\Myhelper::can('billpayment_status', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        $report = Report::where('id', $post->txnid)->first();

        if(!$report || !in_array($report->status , ['pending', 'success'])){
            return response()->json(['status' => "Recharge Status Not Allowed"], 400);
        }

        switch ($report->api->code) {
            case 'billpayment':
                $url = $report->api->url.'/status?token='.$report->api->username.'&apitxnid='.$report->txnid;
                break;
            
            default:
                return response()->json(['statuscode' => "ERR", "message" => "Recharge Status Not Allowed"]);
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
                        'message'=> 'local',
                        'data' => ['status'=> 'success', 'ref_no' => 'local']
                    ]) 
                ];
            }
        if($result['response'] != ''){
            switch ($report->api->code) {
                case 'billpayment':
                $doc = json_decode($result['response']);
                if($doc->statuscode == "TXN" && ($doc->data->status =="success" || $doc->data->status =="pending")){
                    $update['refno'] = $doc->data->ref_no;
                    $update['status'] = "success";

                    $output['statuscode'] = "TXN";
                    $output['txn_status'] = "success";
                    $output['message'] = $doc->message;
                    $output['refno'] = $doc->data->ref_no;

                }elseif($doc->statuscode == "TXN" && $doc->data->status =="reversed"){
                    $update['status'] = "reversed";
                    $update['refno'] = $doc->data->refno;

                    $output['statuscode'] = "TXR";
                    $output['txn_status'] = "reversed";
                    $output['message'] = $doc->message;
                    $output['refno'] = $doc->data->refno;
                }else{
                    $update['status'] = "Unknown";
                    $update['refno'] = $doc->message;

                    $output['statuscode'] = "TNF";
                    $output['txn_status'] = "unknown";
                    $output['message'] = $doc->message;
                    $output['refno'] = $doc->message;
                }
                break;
            }
            $product = "billpay";

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
