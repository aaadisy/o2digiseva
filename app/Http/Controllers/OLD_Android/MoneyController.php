<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Mahabank;
use App\Model\Report;
use App\Model\Commission;
use App\User;
use Carbon\Carbon;

class MoneyController extends Controller
{
    protected $api;
    public function __construct()
    {
        $this->api = Api::where('code', 'dmt1')->first();
    }

    public function transaction(Request $post)
    {
        if(!$this->api || $this->api->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Money Transfer Service Currently Down"]);
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

        if (!\Myhelper::can('dmt1_service', $user->id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        switch ($post->type) {
            case 'getbank':
                $rules = array(
                    'type' => 'required'
                );
                break;

            case 'verification':
            case 'otp':
                $rules = array(
                    'type' => 'required',
                    'mobile' => 'required|numeric|digits:10'
                );
                break;

            case 'registration':
                $rules = array(
                    'type'   => 'required',
                    'mobile' => 'required|numeric|digits:10',
                    'fname'  => 'required',
                );
                break;

            case 'addbeneficiary':
                $rules = array(
                    'mobile'      => 'required|numeric|digits:10', 
                    'benebank'    => 'required', 
                    'beneifsc'    => "required", 
                    'beneaccount' => "required|numeric|digits_between:6,20", 
                    'benemobile'  => 'required|numeric|digits:10', 
                    'benename'    => "required|regex:/^[\pL\s\-]+$/u");
            break;

            case 'beneverify':
                $rules = array(
                    'mobile'      => 'required|numeric|digits:10',
                    'beneaccount' => 'required|numeric|digits_between:6,20', 
                    'benemobile'  => 'required|numeric|digits:10', 
                    'otp'         => 'required|numeric');
            break;

            case 'accountverification':
                $rules = array(
                    'mobile'      => 'required|numeric|digits:10', 
                    'benebank'    => 'required', 
                    'beneifsc'    => "required", 
                    'beneaccount' => "required|numeric|digits_between:6,20", 
                    'benemobile'  => 'required|numeric|digits:10', 
                    'benename'    => "required|regex:/^[\pL\s\-]+$/u",
                    'name'        => "required|regex:/^[\pL\s\-]+$/u"
                );
            break;

            case 'transfer':
                $rules = array(
                    'name' => 'required',
                    'mobile' => 'required|numeric|digits:10',
                    'benebank' => 'required', 
                    'beneifsc' => "required", 
                    'beneaccount' => "required|numeric|digits_between:6,20", 
                    'benemobile' => 'required|numeric|digits:10', 
                    'benename' => "required|regex:/^[\pL\s\-]+$/u",
                    'amount' => 'required|numeric|min:10|max:25000');
            break;
            
            default:
                return response()->json(['statuscode' => "ERR", "message" => "Bad Parameter Request"]);
                break;
        }

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $header = array("Content-Type: application/json");
        $parameter["token"] = $this->api->username;
        $parameter["type"] = $post->type;
        $parameter["mobile"] = $post->mobile;
        $url = $this->api->url."/transaction";
        switch ($post->type) {
            case 'getbank':
                $banks = Mahabank::get();
                return response()->json(['statuscode' => "TXN", "message" => "Bank details fetched", 'data' => $banks]);
                break;
                
            case 'getdistrict':
                $parameter["stateid"] = $post->stateid;
                break;
            
            case 'verification':
                break;
            
            case 'otp':
                break;
            
            case 'registration':
                $name = explode(" ", $post->fname);
                $parameter["fname"] = $name[0];
                $parameter["lname"] = (isset($name[1]) && $name[1] != '')? $name[1] : 'Kumar';
                $parameter["otp"] = $post->otp;
                $parameter["address"] = $user->address;
                $parameter["pincode"] = $user->pincode;
                break;
            
            case 'addbeneficiary':
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
                $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $provider->id, $user->role->slug);
                $post['provider_id'] = $provider->id;
                if($user->mainwallet - $this->mainlocked() < $post->amount + $post->charge){
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
                return $this->transfer($post);
                break;
            
            default:
                return response()->json(['statuscode'=> 'BPR','message'=> "Bad Parameter Request"]);
                break;
        }

        $result = \Myhelper::curl($url, "POST", json_encode($parameter), $header, "yes", 'App\Model\Report', '0');

        if ($result['error'] || $result['response'] == "") {
            return response()->json(['statuscode' => "ERR", "message" => "Technical Error, contact service provider"]);
        }
        $response = json_decode($result['response']);
        switch ($post->type) {
            case 'verification':
                if(isset($response->statuscode) && $response->statuscode == "TXN"){
                    $output['statuscode'] = "TXN";
                    $output['message']= "Transaction Successfull";
                    $output['name'] = $response->message->custfirstname." ".$response->message->custlastname;
                    $output['mobile']= $response->message->custmobile;
                    $output['totallimit']= $response->message->total_limit;
                    $output['usedlimit']= $response->message->used_limit;
                    $benedatas = [];
                    foreach ($response->message->Data as $value) {
                        $benedata['beneid'] = $value->id;
                        $benedata['benename'] = $value->benename;
                        $benedata['beneaccount'] = $value->beneaccno;
                        $benedata['benemobile'] = $value->benemobile;
                        $benedata['benebank'] = $value->bankname;
                        $benedata['beneifsc'] = $value->ifsc;
                        $benedata['benebankid'] = $value->bankid;
                        $benedata['benestatus'] = $value->status;
                        $benedatas[] = $benedata;
                    }
                    $output['beneficiary'] = $benedatas;
                }elseif(isset($response->statuscode) && $response->statuscode == "RNF"){
                    $parameter["bc_id"] = $post->bc_id;
                    $parameter["custno"] = $post->mobile;
                    $url = $this->api->url."AIRTEL/airtelOTP";
                    $header = array("Content-Type: application/json");
                    \Myhelper::curl($url, "POST", $parameter, $header, "no");

                    $output['statuscode'] = "RNF";
                    $output['message']= "Customer Not Found";
                }else{
                    $output['statuscode'] = "TXR";
                    $output['message']= isset($response->message->message) ? $response->message->message : 'Transaction Error';
                }
                break;

            case 'otp':
            case 'registration':
            case 'addbeneficiary':
            case 'beneverify':
                if(isset($response->statuscode) && $response->statuscode == "TXN"){
                    $output['statuscode'] = 'TXN';
                    $output['message']= "Transaction Successfully";
                }else{
                    $output['statuscode'] = 'TXR';
                    $output['message']= isset($response->message) ? $response->message : 'Transaction Error';
                }
                break;

            case 'accountverification':
                if(isset($response->statuscode) && $response->statuscode == "TXN"){
                    $insert = [
                        'api_id'      => $this->api->id,
                        'provider_id' => $post->provider_id,
                        'option1'     => $post->name,
                        'mobile'      => $post->mobile,
                        'number'      => $post->beneaccount,
                        'option2'     => isset($response->message) ? $response->message : $post->benename,
                        'option3'     => $post->benebank,
                        'option4'     => $post->beneifsc,
                        'txnid'       => $post->txnid,
                        'amount'      => $post->amount,
                        'charge'      => $post->charge,
                        'remark'      => "Money Transfer",
                        'status'      => 'pending',
                        'user_id'     => $user->id,
                        'credit_by'   => $user->id,
                        'product'     => 'dmt',
                        'via'         => 'app',
                        'balance'     => $user->mainwallet,
                        'description' => $post->benemobile,
                        'trans_type'  => "debit",
                    ];

                    User::where('id', $post->user_id)->decrement('mainwallet', $post->charge + $post->amount);
                    $report = Report::create($insert);
                    $output['statuscode']   = 'TXN';
                    $output['message']  = 'Transaction Successfull';
                    $output['benename'] = $response->message;
                }else{
                    $output['statuscode'] = 'TXR';
                    $output['message']= $response->message;
                }
                break;
        }
        return response()->json($output);
    }

    public function transfer($post)
    {
        $url = $this->api->url."/transaction";
        $parameter['type'] = $post->type;
        $parameter['token'] = $this->api->username;

        $amount = $post->amount;
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
            if($user->mainwallet - $this->mainlocked() < $post->amount + $post->charge){
                $outputs['data'][] = array(
                    'amount'  => $amount,
                    'status'  => 'IWB',
                    'message' => 'Insufficient Wallet Balance',
                );
            }else{
                
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
                $bank = Mahabank::where('bankid', $post->benebank)->first();

                $insert = [
                    'api_id'      => $this->api->id,
                    'provider_id' => $post->provider_id,
                    'option1'     => $post->name,
                    'mobile'      => $post->mobile,
                    'number'      => $post->beneaccount,
                    'option2'     => $post->benename,
                    'option3'     => $bank->bankname,
                    'option4'     => $post->beneifsc,
                    'txnid'       => $post->txnid,
                    'amount'      => $post->amount,
                    'charge'      => $post->charge,
                    'remark'      => "Money Transfer",
                    'status'      => 'pending',
                    'user_id'     => $user->id,
                    'credit_by'   => $user->id,
                    'product'     => 'dmt',
                    'via'         => "app",
                    'trans_type'  => "debit",
                    'balance'     => $user->mainwallet,
                    'description' => $post->benemobile
                ];

                $previousrecharge = Report::where('number', $post->beneaccount)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subSeconds(5)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                if($previousrecharge == 0){
                    $transaction = User::where('id', $user->id)->decrement('mainwallet', $post->amount + $post->charge);
                    if(!$transaction){
                        $outputs['data'][] = array(
                            'amount' => $amount,
                            'status' => 'TXF',
                            'message' => 'Transaction Failed'
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
                        
                        if(env('APP_ENV') != "local"){
                            $result = \Myhelper::curl($url, "POST", json_encode($parameter), $header, "yes", 'App\Model\Report', $post->txnid);
                        }else{
                            $result['error']    = true;
                            $result['response'] = '';
                        }

                        if($result['error'] || $result['response'] == ''){
                            $result['response'] = json_encode([ "statuscode" => "TXN", "message" => [
                                "message"       =>  "Pending",
                                "statuscode"    =>  "001",
                                "availlimit"    =>  "0",
                                "total_limit"   =>  "0",
                                "used_limit"    =>  "0",
                                "Data"          =>  [
                                    [   "fesessionid"   =>  "CP1801861S131436",
                                        "tranid"        =>  "pending",
                                        "rrn"           =>  "pending",
                                        "externalrefno" =>  "MH357381218131436",
                                        "amount"        =>  "0",
                                        "responsetimestamp" =>  "0",
                                        "benename"          =>  "",
                                        "messagetext"       =>  "Success",
                                        "code"              =>  "0",
                                        "errorcode"         =>  "1114",
                                        "mahatxnfee"        =>  "10.00"
                                    ]
                                ]
                            ]]);
                        }

                        $response = json_decode($result['response']);

                        if(isset($response->statuscode) && $response->statuscode == "TXN"){
                            $charge = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id,$user->role->slug);
                            User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $charge);
                            Report::where('id', $post->report)->update([
                                'status'=> "success",
                                'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                                'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                                'profit' => $report->charge - $charge
                            ]);
                            \Myhelper::commission($report);
                            $output = ['amount' => $amount, 'statuscode'=> 'TXN', 'message'=> 'success', "rrn" => (isset($response->rrn))? $response->rrn : "Success"];
                        }elseif(isset($response->statuscode) && $response->statuscode == "TUP"){
                            $charge = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id,$user->role->slug);
                            User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $charge);
                            Report::where('id', $post->report)->update([
                                'status'=> "pending",
                                'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                                'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                                'profit' => $report->charge - $charge
                            ]);
                            \Myhelper::commission($report);
                            $output = ['amount' => $amount, 'statuscode'=> 'TUP', 'message'=> 'pending', "rrn" => (isset($response->rrn))? $response->rrn : "Pending"];
                        }elseif(isset($response->statuscode) && $response->statuscode == "TXF" || $response->statuscode == "ERR"){
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
                            $output = ['amount' => $amount, 'statuscode'=> 'TXF', 'message'=> 'failed', "rrn" => (isset($response->rrn))? $response->rrn : "Failed"];
                        }else{
                            $charge = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id,$user->role->slug);
                            User::where('id', $post->user_id)->increment('mainwallet', $report->charge - $charge);
                            Report::where('id', $post->report)->update([
                                'status'=> "pending",
                                'payid' => (isset($response->payid))?$response->payid : "Pedning" ,
                                'refno' => (isset($response->rrn))?$response->rrn : "Pedning",
                                'profit' => $report->charge - $charge
                            ]);
                            \Myhelper::commission($report);
                            $output = ['amount' => $amount, 'statuscode'=> 'TUP', 'message'=> 'pending', "rrn" => (isset($response->rrn))? $response->rrn : "Pending"];
                        }
                        $outputs['data'][] = $output;
                    }
                }else{
                    $outputs['data'][] = array(
                        'amount' => $amount,
                        'status' => 'TXF',
                        "message" => "Same Transaction Repeat",
                        "rrn" => "Failed"
                    );
                }
            }
            sleep(1);
        }
        return response()->json($outputs);
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
}
