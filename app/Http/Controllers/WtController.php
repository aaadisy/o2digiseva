<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Mahabank;
use App\Model\Report;
use App\Model\Commission;
use App\User;
use Carbon\Carbon;
use DB;

use File;

class WtController extends Controller
{
    protected $api;
    public function __construct()
    {
        $this->api = Api::where('code', 'wt')->first();
    }

    public function index()
    {
        
        if(!\Myhelper::service_active('moneytransfer_service'))
        {

            return  redirect()->back()->with('error','Service Currently Deactive');  

        }
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('dmt1_service')) {
            abort(403);
        }

        $data['banks'] = DB::table('dmt_banklist')->get();
        return view('service.wt')->with($data);
    }

    public function payment(Request $post)
    {
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('dmt1_service')) {
            return \Response::json(['status' => "Permission not allowed"], 400);
        }

        if(!$this->api || $this->api->status == 0){
            return response()->json(['status' => "Money Transfer Service Currently Down."], 400);
        }

        $post['user_id'] = \Auth::id();
        $userdata = User::where('id', $post->user_id)->first();
        //dd($userdata); exit;
        if($post->type == "transfer"){
            $codes = ['dmt1', 'dmt2', 'dmt3', 'dmt4', 'dmt5'];
            $providerids = [];
            foreach ($codes as $value) {
                $providerids[] = Provider::where('recharge1', $value)->first(['id'])->id;
            }
            $commission = Commission::where('scheme_id', $userdata->scheme_id)->whereIn('slab', $providerids)->get();
            //dd($commission); exit;
            if(!$commission || sizeof($commission) < 5){
                return response()->json(['statuscode' => 'ERR', 'message' => "Money Transfer charges not set, contact administrator."], 400);
            }
        }
        
        $validate = $this->myvalidate($post);
        if($validate['status'] != 'NV'){
            return response()->json($validate, 422);
        }

        $url = $this->api->url;
        $header = array("Content-Type: application/json");
        //$parameter["token"] = $this->api->username;
        $parameter["type"] = $post->type;
        $parameter["mobile"] = $post->mobile;

        switch ($post->type) {
            case 'getdistrict':
                $parameter["stateid"] = $post->stateid;
                break;
            
            case 'verification':


                $url = $this->api->url."remitter/queryremitter";
                //$parameter["bank3_flag"] = "yes";
                //unset($parameter['type']);
                $payload = ["mobile"=>$post->mobile,"bank3_flag"=>"yes"];
                //return $url;
                $res = JwtController::callApi($payload, $url);
                $response = json_decode($res);
                $this->createFile('queryremitter_'.$post->mobile, ['payload' => $payload, 'url' => $url, 'response' => $response]);
                if($response->response_code == '1'){

                    $beneurl = $this->api->url."beneficiary/registerbeneficiary/fetchbeneficiary";
                    $benepayload = ["mobile"=>$post->mobile];
                    $beneres = JwtController::callApi($benepayload, $beneurl);
                    $beneresponse = json_decode($beneres);
                    if($beneresponse->response_code == '1'){
                        $response->data->benedata = $beneresponse->data; 
                    }else{
                        $response->data->benedata = []; 
                    }
                    return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> "Remitter details fetch successfully.", 'data' => $response->data]);
                }elseif($response->response_code == '0'){
                    return response()->json(['statuscode'=> 'RNF', 'status'=> 'success','message'=> "Remitter not registered OTP sent for new registration.", 'data' => $response]);
                }elseif($response->response_code == '2'){
                    return response()->json(['statuscode'=> 'RNF', 'status'=> 'success','message'=> "Remitter not registered OTP sent for new registration.", 'data' => $response]);
                }elseif($response->response_code == '3'){
                    return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> "Remitter details fetch successfully.", 'data'=>$response]);
                }elseif($response->response_code == '11'){
                    return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Authentication failed"]);
                }elseif($response->response_code == '13'){
                    return response()->json(['statuscode'=> 'TXN', 'status'=> 'Bad Parameter Request','message'=> $response->message]);
                }else{
                    return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> $response->message]);
                }
                dd($response); exit;
                break;
            
            case 'otp':
                break;
            
            case 'registration':
                $parameter["fname"] = $post->fname;
                $parameter["lname"] = $post->lname;
                $parameter["otp"] = $post->otp;
                $parameter["address"] = $userdata->address;
                $parameter["pincode"] = $post->pincode;

                $url = $url."remitter/registerremitter";
                $payload = ["mobile"=>$post->mobile,"firstname"=>$post->fname,"lastname"=>$post->lname,"address"=>$userdata->address,"otp"=>$post->otp,"pincode"=>$post->pincode,"stateresp"=>$post->stateresp, 'bank3_flag' => "yes", 'paytm_flag' => true, 'dob' => $post->dob, 'gst_state' => $post->gst_state];
                $res = JwtController::callApi($payload, $url);
                $response = json_decode($res);
                
                $this->createFile('registerremitter_'.$post->mobile, ['payload' => $payload, 'url' => $url, 'response' => $response]);
                
                //dd($response); exit;
                if($response->response_code == '1'){
                    return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> "Remitter details fetch successfully.", 'data'=>$response]);
                }elseif($response->response_code == '11'){
                    return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Authentication failed"]);
                }elseif($response->response_code == '3'){
                    return response()->json(['statuscode'=> 'BPR', 'status'=> $response->message,'message'=> "Authentication failed"]);
                }else{
                    return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Bad Parameter Request"]);
                }
                dd($response); exit;
                break;
            
            case 'addbeneficiary':

                $url = $url."beneficiary/registerbeneficiary";
                $payload = ["mobile"=>$post->mobile, "benename" => $post->benename, "bankid" => $post->benebank, "accno" => $post->beneaccount, "ifsccode" => $post->beneifsc, "verified" => '1', "gst_state" => "07", "dob" => $post->dob, "address" => $post->address, "pincode" => $post->pincode];
                $res = JwtController::callApi($payload, $url);
                $response = json_decode($res);
                
                $this->createFile('registerbeneficiary_'.$post->mobile, ['payload' => $payload, 'url' => $url, 'response' => $response]);
                
                if($response->response_code == '1'){
                    return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> "Receiver account successfully added.", 'data'=>$response]);
                }elseif($response->response_code == '3'){
                     return response()->json(['statuscode'=> 'ERR', 'status'=> 'Failed','message'=> $response->message]);
                }elseif($response->response_code == '11'){
                    return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Authentication failed"]);
                }else{
                     return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Bad Parameter Request"]);
                }




                dd($response); exit;
                $parameter["benebank"] = $post->benebank;
                $parameter["beneaccount"] = $post->beneaccount;
                $parameter["benemobile"] = $post->benemobile;
                $parameter["benename"] = $post->benename;
                $parameter["beneifsc"] = $post->beneifsc;
                break;

            case 'beneverify':
                $parameter["otp"] = $post->otp;
                $parameter["beneaccount"] = $post->beneaccount;
                $parameter["benemobile"] = $post->benemobile;
                break;
            
            case 'accountverification':
                $post['amount'] = 1;
                $provider = Provider::where('recharge1', 'dmt1accverify')->first();
                $post['charge'] = \Myhelper::getCommission($post->amount, $userdata->scheme_id, $provider->id, $userdata->role->slug);
                $post['provider_id'] = $provider->id;
                if($userdata->mainwallet < $post->amount + $post->charge){
                    return response()->json(["statuscode" => "IWB", 'status'=>'Low balance, kindly recharge your wallet.'], 400);
                }

                $parameter["benebank"] = $post->benebank;
                $parameter["beneaccount"] = $post->beneaccount;
                $parameter["benemobile"] = $post->benemobile;
                $parameter["benename"] = $post->benename;
                $parameter["beneifsc"] = $post->beneifsc;

                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                $parameter["apitxnid"] = $post->txnid;
                break;
            
            case 'transfer':
                return $this->transfer($post, $userdata);
                break;
            
            default:
                return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Bad Parameter Request"]);
                break;
        }        

        $result = \Myhelper::curl($url, "POST", json_encode($parameter), $header, "yes", 'App\Model\Report', '0');
        //dd([$url, $parameter , $result]);
        if ($result['error'] || $result['response'] == "") {
            if($post->type == "beneaccvalidate"){
                $response = [
                    "message"=>"Success",
                    "statuscode"=>"001",
                    "availlimit"=>"0",
                    "total_limit"=>"0",
                    "used_limit"=>"0",
                    "Data"=>[["fesessionid"=>"CP1801861S131436",
                    "tranid"=>"pending",
                    "rrn"=>"pending",
                    "externalrefno"=>"MH357381218131436",
                    "amount"=>"0",
                    "responsetimestamp"=>"0",
                    "benename"=>"",
                    "messagetext"=>"Success",
                    "code"=>"1",
                    "errorcode"=>"1114",
                    "mahatxnfee"=>"10.00"
                    ]]
                ];

                return $this->output($post, json_encode($response), $userdata);
            }

            return response()->json(["statuscode" => "ERR", 'status'=>'System Error'], 400);
        }

        return $this->output($post, $result['response'] , $userdata);
    }
    
    public function createFile($file, $data){
        $data = json_encode($data);
        $file = 'wt_'.$file.'_file.txt';
        $destinationPath=public_path()."/wt_logs/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function myvalidate($post)
    {
        $validate = "yes";
        switch ($post->type) {
            case 'getdistrict':
                $rules = array('stateid' => 'required|numeric');
            break;

            case 'verification':
            case 'otp':
                $rules = array('user_id' => 'required|numeric','mobile' => 'required|numeric|digits:10');
            break;
            
            case 'registration':
                $rules = array('user_id' => 'required|numeric','mobile' => 'required|numeric|digits:10', 'fname' => 'required|regex:/^[\pL\s\-]+$/u', 'lname' => 'required|regex:/^[\pL\s\-]+$/u', 'otp' => "required|numeric", 'pincode' => "required|numeric|digits:6", 'dob' => "required");
            break;

            case 'addbeneficiary':
                $rules = array('user_id' => 'required|numeric','mobile' => 'required|numeric|digits:10', 'benebank' => 'required', 'beneifsc' => "required", 'beneaccount' => "required|numeric|digits_between:6,20", "benemobile" => 'required|numeric|digits:10', "benename" => "required|regex:/^[\pL\s\-]+$/u");
            break;

            case 'beneverify':
                $rules = array('user_id' => 'required|numeric','mobile' => 'required|numeric|digits:10','beneaccount' => "required|numeric|digits_between:6,20", "benemobile" => 'required|numeric|digits:10', "otp" => 'required|numeric');
            break;

            case 'accountverification':
                $rules = array('user_id' => 'required|numeric','mobile' => 'required|numeric|digits:10', 'benebank' => 'required', 'beneifsc' => "required", 'beneaccount' => "required|numeric|digits_between:6,20", "benemobile" => 'required|numeric|digits:10', "benename" => "required|regex:/^[\pL\s\-]+$/u");
            break;

            case 'transfer':
                $rules = array('user_id' => 'required|numeric','name' => 'required','mobile' => 'required|numeric|digits:10', 'benebank' => 'required', 'beneifsc' => "required", 'beneaccount' => "required|numeric|digits_between:6,20", "benename" => "required|regex:/^[\pL\s\-]+$/u",'amount' => 'required|numeric|min:10|max:25000');
            break;

            default:
                return ['statuscode'=>'BPR', "status" => "Bad Parameter Request", 'message'=> "Invalid request format"];
            break;
        }

        if($validate == "yes"){
            $validator = \Validator::make($post->all(), $rules);
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $key => $value) {
                    $error = $value[0];
                }
                $data = ['statuscode'=>'BPR', "status" => "Bad Parameter Request", 'message'=> $error];
            }else{
                $data = ['status'=>'NV'];
            }
        }else{
            $data = ['status'=>'NV'];
        }
        return $data;
    }
    
    public function getTransactionStatus($ref){
        $url = $this->api->url."transact/transact/querytransact";
        $payload = ["referenceid"=>$ref];
        $stares = JwtController::callApi($payload, $url);
        $staresponse = json_decode($stares);
        $this->createFile('querytransact_'.$ref, ['payload' => $payload, 'url' => $url, 'response' => $staresponse]);
        return $staresponse;
    }

    public function transfer($post, $user)
    {   
        
        $amount = $post->amount;
        $smttra = $post->amount;
        for ($i=1; $i < 6; $i++) { 
            if(5000*($i-1) <= $amount  && $amount <= 5000*$i){
                if($amount == 5000*$i){
                    $n = $i;
                }else{
                    $n = $i-1;
                    $x = $amount - $n*5000;
                }
                break;
            }
        }

        $amounts = array_fill(0,$n,5000);
        if(isset($x)){
            array_push($amounts , $x);
        }

        foreach ($amounts as $amount) {
            
            $outputs['statuscode'] = "TXN";
            $post['amount'] = $amount;
            $user = User::where('id', $post->user_id)->first();
            $post['charge'] = $this->getCharge($post->amount);
            if($user->mainwallet < $post->amount + $post->charge){
                $outputs['data'][] = array(
                    'amount' => $amount,
                    'status' => 'TXF',
                    'data' => [
                        "statuscode" => "TXF",
                        "status" => "Insufficient Wallet Balance",
                    ]
                );
            }else{
                $post['amount'] = $amount;
                
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                if($post->amount >= 100 && $post->amount <= 1000){
                    $provider = Provider::where('recharge1', 'dmt1')->first();
                }elseif($amount>1000 && $amount<=2000){
                    $provider = Provider::where('recharge1', 'dmt2')->first();
                }elseif($amount>2000 && $amount<=3000){
                    $provider = Provider::where('recharge1', 'dmt3')->first();
                }elseif($amount>3000 && $amount<=4000){
                    $provider = Provider::where('recharge1', 'dmt4')->first();
                }else{
                    $provider = Provider::where('recharge1', 'dmt5')->first();
                }
                
                $post['provider_id'] = $provider->id;
                $bank = DB::table('dmt_banklist')->where('id', $post->benebank)->get();
                $bank = $bank[0];
                //$bank = Mahabank::where('bankid', $post->benebank)->first();
                $insert = [
                    'api_id' => $this->api->id,
                    'provider_id' => $post->provider_id,
                    'option1' => $post->name,
                    'mobile' => $post->mobile,
                    'number' => $post->beneaccount,
                    'option2' => $post->benename,
                    'option3' => $bank->bankname,
                    'option4' => $post->beneifsc,
                    'txnid' => $post->txnid,
                    'amount' => $post->amount,
                    'charge' => $post->charge,
                    'remark' => "Money Transfer",
                    'status' => 'pending',
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'product' => 'wt',
                    'via'   => "portal",
                    'balance' => $user->mainwallet,
                    'description' => $post->benemobile,
                    'trans_type' => 'debit'
                ];
                //dd($insert); exit;
                $previousrecharge = Report::where('number', $post->beneaccount)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subSeconds(5)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                
                

                   


                //dd(['url' => $url, 'payload' => $payload, 'response' => $response]); exit;

                if($previousrecharge == 0){
                    $transaction = User::where('id', $user->id)->decrement('mainwallet', $post->amount + $post->charge);
                    if(!$transaction){
                        $outputs['data'][] = array(
                            'amount' => $amount,
                            'status' => 'TXF',
                            'data' => [
                                "statuscode" => "TXF",
                                "status" => "Transaction Failed",
                            ]
                        );
                    }else{
                        $report = Report::create($insert);
                        $post['report'] = $report->id;
                        $post['amount'] = $amount;
                        $parameter["mobile"] = $post->mobile;
                        $parameter["name"] = $post->name;
                        $parameter["benebank"] = $post->benebank;
                        $parameter["beneaccount"] = $post->beneaccount;
                        $parameter["benemobile"] = $post->benemobile;
                        $parameter["benename"] = $post->benename;
                        $parameter["beneifsc"] = $post->beneifsc;
                        $parameter['amount'] = $amount;
                        $parameter["apitxnid"] = $post->txnid;
                        $header = array("Content-Type: application/json");

                        
                        $url = $this->api->url."transact/transact";
                        $int= mt_rand(1262055681,1262055681);
                        $dob = date("Y-m-d H:i:s",$int);
                        $ref = rand(1000000, 999999999);
                        $payload = ["mobile"=>$post->mobile, "referenceid" => $ref, "pipe" => 'bank1', "pincode" => $user->pincode, "address" => $user->address, "dob" => '13-09-1990', "gst_state" => "07", "bene_id" => $post->beneid, "txntype" => 'NEFT', "amount" => $post->amount];
                        $res = JwtController::callApi($payload, $url);
                        $response = json_decode($res);
                        
                        $this->createFile('transact_'.$ref, ['payload' => $payload, 'url' => $url, 'response' => $response]);
                        
                        if($response->response_code == '0'){
                            return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> "Balance transfer request accepted successfully!", 'data'=>$response]);
                        }if($response->response_code == '1'){
                            $response->amount = $smttra;
                            
                            //status Api Call
                            $trastatus = $this->getTransactionStatus($ref);
                            //status Api finished
                            
                            
                            return response()->json(['statuscode'=> 'TXN', 'status'=> 'success','message'=> $response->message.''.json_encode($trastatus), 'data'=>$response]);
                        }elseif($response->response_code == '25'){
                             return response()->json(['statuscode'=> 'ERR', 'status'=> 'Failed','message'=> $response->message]);
                        }else{
                             return response()->json(['statuscode'=> 'BPR', 'status'=> 'Bad Parameter Request','message'=> "Bad Parameter Request"]);
                        }



                        if($result['error'] || $result['response'] == ''){
                            $result['response'] = json_encode([
                                "message"=>"Pending",
                                "statuscode"=>"001",
                                "availlimit"=>"0",
                                "total_limit"=>"0",
                                "used_limit"=>"0",
                                "Data"=>[
                                    [
                                        "fesessionid"=>"CP1801861S131436",
                                        "tranid"=>"pending",
                                        "rrn"=>"pending",
                                        "externalrefno"=>"MH357381218131436",
                                        "amount"=>"0",
                                        "responsetimestamp"=>"0",
                                        "benename"=>"",
                                        "messagetext"=>"Success",
                                        "code"=>"1",
                                        "errorcode"=>"1114",
                                        "mahatxnfee"=>"10.00"
                                    ]
                                ]
                            ]);
                        }

                        $outputs['data'][] = array(
                            'amount' => $amount,
                            'status' => 'TXN',
                            'data' => $this->output($post, $result['response'], $user)
                        );
                    }
                }else{
                    $outputs['data'][] = array(
                        'amount' => $amount,
                        'status' => 'TXF',
                        'data' => [
                            "statuscode" => "TXF",
                            "status" => "Same Transaction Repeat",
                            "message" => "Same Transaction Repeat",
                        ]
                    );
                }
            }
            sleep(1);
        }
        return response()->json($outputs, 200);
    }

    public function output($post, $response, $userdata)
    {
        $response = json_decode($response);
        switch ($post->type) {
            case 'verification':
                if($response->statuscode == "RNF"){
                    $parameter["token"] = $this->api->username;
                    $parameter["mobile"] = $post->mobile;
                    $url = $this->api->url."/transaction";
                    $header = array("Content-Type: application/json");
                    \Myhelper::curl($url, "POST", $parameter, $header, "no");
                }
                break;

            case 'accountverification':
                if($response->statuscode == "TXN"){
                    $balance = User::where('id', $userdata->id)->first(['mainwallet']);
                    $insert = [
                        'api_id' => $this->api->id,
                        'provider_id' => $post->provider_id,
                        'option1' => $post->name,
                        'mobile' => $post->mobile,
                        'number' => $post->beneaccount,
                        'option2' => isset($response->message) ? $response->message : $post->benename,
                        'option3' => $post->benebank,
                        'option4' => $post->beneifsc,
                        'txnid' => $post->txnid,
                        'amount' => $post->amount,
                        'charge' => $post->charge,
                        'remark' => "Money Transfer",
                        'status' => 'pending',
                        'user_id' => $userdata->id,
                        'credit_by' => $userdata->id,
                        'product' => 'wt',
                        'via' => 'portal',
                        'balance' => $balance->mainwallet,
                        'description' => $post->benemobile,
                        'trans_type' => 'debit'
                    ];

                    User::where('id', $post->user_id)->decrement('mainwallet', $post->charge + $post->amount);
                    $report = Report::create($insert);
                }
                break;
            
            case 'transfer':
                $report = Report::where('id', $post->report)->first();
                if($response->statuscode == "TXN"){
                    $charge = \Myhelper::getCommission($post->amount, $userdata->scheme_id, $post->provider_id, $userdata->role->slug);
                    User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $post->gst - $charge);
                    Report::where('id', $post->report)->update([
                        'status'=> "success",
                        'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                        'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                        'profit' => $report->charge - $charge
                    ]);
                    \Myhelper::commission($report);
                }elseif($response->statuscode == "TUP"){
                    $charge = \Myhelper::getCommission($post->amount, $userdata->scheme_id, $post->provider_id, $userdata->role->slug);
                    User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $post->gst - $charge);
                    Report::where('id', $post->report)->update([
                        'status'=> "pending",
                        'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                        'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                        'profit' => $report->charge - $charge
                    ]);
                    \Myhelper::commission($report);
                }elseif($response->statuscode == "TXF" || $response->statuscode == "ERR"){
                    User::where('id', $post->user_id)->increment('mainwallet', $report->charge + $report->amount);
                    Report::where('id', $post->report)->update([
                        'status'=> 'failed',
                        'refno' => "failed",
                    ]);
                    try {
                        if(isset($response->status) && $response->status == "Insufficient Wallet Balance"){
                            $response->message = "Service Down for some time";
                        }
                    } catch (\Exception $th) {}
                }else{
                    $charge = \Myhelper::getCommission($post->amount, $userdata->scheme_id, $post->provider_id, $userdata->role->slug);
                    User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $post->gst - $charge);
                    Report::where('id', $post->report)->update([
                        'status'=> "pending",
                        'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                        'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                        'profit' => $report->charge - $charge
                    ]);
                    \Myhelper::commission($report);
                }
                break;
        }
        
        if($post->type == "transfer"){
            return $response;
        }else{
            return response()->json($response);
        }
    }

    public function getCommission($scheme, $slab, $amount)
    {
        if($amount < 1000){
            $amount = 1000;
        }
        $userslab = Commission::where('scheme_id', $scheme)->where('product', 'money')->where('slab', $slab)->first();
        if($userslab){
            if ($userslab->type == "percent") {
                $usercharge = $amount * $userslab->value / 100;
            }else{
                $usercharge = $userslab->value;
            }
        }else{
            $usercharge = 7;
        }

        return $usercharge;
    }

    public function getCharge($amount)
    {
        if($amount < 1000){
            return 10;
        }else{
            return $amount*1/100;
        }
    }

    public function getGst($amount)
    {
        return $amount*100/118;
    }

    public function getTds($amount)
    {
        return $amount*5/100;
    }

    public function statementLog($post, $amount)
    {
        $statement['transaction_type'] = $post->transtype;
        $statement['statement_type'] = "Money Transfer";
        $statement['amount'] = $amount;
        $statement['pre_balance'] = $post->userprebalance;
        $statement['current_balance'] = $post->userpostbalance;
        $statement['report_type'] = "Main";
        $statement['txnid'] = $post->reportid;
        $statement['user_id'] = $post->user_id;
        $statement['credited_by'] = $post->user_id;
        $statement['remark'] = $post->remark;
        \Myhelper::statementLog($statement);
    }
}
