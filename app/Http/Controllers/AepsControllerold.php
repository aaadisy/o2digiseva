<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Model\Mahaagent;
use App\Model\Mahastate;
use App\Model\Aepsreport;
use App\Model\Report;
use App\Model\Commission;
use App\Model\Provider;
use App\Model\Api;
use App\Model\Aepsfundrequest;
use File;
use DB;

class AepsController extends Controller
{
    protected $api;
    public function __construct()
    {
        $this->api = Api::where('code', 'aeps')->first();
        $this->aepsapi = Api::where('code', 'aeps')->first();
        
    }

    public function geoip($ip){
        $geoIP  = json_decode(file_get_contents("http://api.ipstack.com/$ip?access_key=49003c05bb4d851383468c238587d71c&format=1"), true);
        return $geoIP;
    }

    public function index(Request $post)
    {
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('aeps_service')) {
            abort(403);
        }

        if(!$this->api || $this->api->status == 0){
            abort(405);
        }

        $agent = Mahaagent::where('user_id', \Auth::id())->first();

        $data['mahastate'] = Mahastate::get();
        $data['aepsbanks'] = \DB::table('aepsbanks')->get();
        $data['aadharbanks'] = \DB::table('aepsbanks')->get();
        $data['state'] = \DB::table('mahastates')->get();
        $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
        if($agent == null){
            $data['is_agent'] = 'yes';
            return view('service.aeps')->with($data);
        }else{
            $data['is_agent'] = 'yes';
            return view('service.aeps')->with($data);    
        }
        $data["bc_id"] = $agent->bc_id;
        $data["phone1"] = $agent->phone1;
        $data["token"] = $this->api->username;

        $url = $this->api->url."/initiate";
        $header = array("Content-Type: application/json");
        $result = \Myhelper::curl($url, "POST", json_encode($data), $header, "no");
        //dd([$url, $result]);
        if($result['response'] != ''){
            $datas = json_decode($result['response']);
            if(isset($datas->statuscode) && $datas->statuscode == "TXN"){
                return \Redirect::away($datas->data);
            }else{
                return redirect(url('dashboard'));
            }
        }else{
            return redirect(url('dashboard'));
        }
    }

    public function createFile($file, $data){
        $data = json_encode($data);
        $file = 'aeps_'.$file.'_file.txt';
        $destinationPath=public_path()."/aeps_logs/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function initiate(Request $post)
    {
        $this->createFile(1, $post->all());
        $post['user_id'] = \Auth::id();
        $post['superMerchantId'] = '531480';
        switch ($post->transactionType) {
            case 'useronboard':
                $rules = array(
                    'merchantName' => 'required',
                    'merchantAddress' => 'required',
                    'merchantState' => 'required',
                    'merchantCityName' => 'required',
                    'merchantPhoneNumber' => 'required|numeric|digits:10|unique:fingagents,merchantPhoneNumber',
                    'merchantAadhar' => 'required|numeric|digits:12|unique:fingagents,merchantAadhar',
                    'userPan'   => 'required|unique:fingagents,userPan',
                    'merchantPinCode' => 'sometimes|numeric|digits:6',
                    'superMerchantId'   => 'required|numeric',
                    'aadharPics'   => 'required|mimes:jpg,jpeg,pdf|max:1024',
                    'pancardPics'  => 'required|mimes:jpg,jpeg,pdf|max:1024',
                );

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status'=>'ERR', 'message'=> $error]);
                }

                do {
                    $post['merchantLoginId']  = "EPM".rand(1111111111, 9999999999);
                } while (Fingagent::where("merchantLoginId", "=", $post->merchantLoginId)->first() instanceof Fingagent);

                do {
                    $post['merchantLoginPin'] = "EPMP".rand(111111, 999999);
                } while (Fingagent::where("merchantLoginPin", "=", $post->merchantLoginPin)->first() instanceof Fingagent);

                if($post->hasFile('aadharPics')){
                    $post['aadharPic'] = $post->file('aadharPics')->store('fingkyc');
                }
                if($post->hasFile('pancardPics')){
                    $post['pancardPic'] = $post->file('pancardPics')->store('fingkyc');
                }
                    
                $agent = Fingagent::create($post->all());
                if($agent){
                    return response()->json(['status' => 'TXN', 'message'=>'User onboard submitted, wait for approval']);
                }else{
                    return response()->json(['status' => 'ERR', 'message'=>'Something went wrong']);
                }
                break;

            case 'useronboardsubmit':
                $rules = array(
                    'id' => 'required'
                );
                break;

            case 'BE':
            case 'MS':
                $rules = array(
                    'transactionType' => 'required',
                    'mobileNumber'    => 'required|numeric|digits:10',
                    'adhaarNumber'    => 'required|numeric|digits:12',
                    'nationalBankIdentificationNumber' => 'required',
                    'biodata'   => 'required',
                );
                break;

            case 'CW':
            case 'CD':
            case 'M':
                $rules = array(
                    'transactionType' => 'required',
                    'mobileNumber'    => 'required|numeric|digits:10',
                    'adhaarNumber'    => 'required|numeric|digits:12',
                    'nationalBankIdentificationNumber' => 'required',
                    'biodata'   => 'required',
                    'transactionAmount' => 'required|numeric|min:1|max:10000',
                );
                break;
            
            default:
                return response()->json(['status' => 'ERR', 'message'=>'Invalid Transaction Type']);
                break;
        }

        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(['status'=>'ERR', 'message'=> $error]);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        $sessionkey = '1609d631cf2bfedb';
        $iv =   'dc1c890980dea9f3';
        $gpsdata       =  $this->geoip($post->ip());
        $refId = rand(1000000, 999999999);
        $bank = DB::table('aepsbanks')->where('BankIIN', $post->nationalBankIdentificationNumber)->get();
        $bank = $bank[0];
        switch ($post->transactionType) {
            case 'useronboardsubmit':
                $agent = Fingagent::where('id', $post->id)->first();
                if(!$agent){
                    return response()->json(['status' => 'ERR', 'message'=>'Invalid Agent']);
                }
                
                if($agent->status != "pending"){
                    return response()->json(['status' => 'ERR', 'message'=>'Already Onboard']);
                }

                $json =  [
                    "username" => $this->aepsapi->username,
                    "password" => md5($this->aepsapi->password),
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "supermerchantId"=> $this->aepsapi->option1,
                    "merchants"      => [[
                        "merchantLoginId"     => $agent->merchantLoginId, 
                        "merchantLoginPin"    => $agent->merchantLoginPin,
                        "merchantName"        => $agent->merchantName,
                        "merchantPhoneNumber" => $agent->merchantPhoneNumber,
                        "merchantPinCode"     => $agent->merchantPinCode,
                        "merchantCityName"     => $agent->merchantCityName,
                        "merchantAddress"=> [
                            "merchantAddress" => $agent->merchantAddress,
                            "merchantState"   => $agent->merchantState
                        ],
                        "kyc"=> [
                            "userPan" => $agent->userPan
                        ]
                    ]],
                ];

                $header = [         
                    'Content-Type: text/xml',             
                    'trnTimestamp:'.date('d/m/Y H:i:s'),         
                    'hash:'.base64_encode(hash("sha256",json_encode($json), True)),   
                    'eskey:'.base64_encode($crypttext)         
                ];

                $url = $this->aepsapi->url."fpaepsweb/api/onboarding/merchant/creation/php/m1";
                break;

            case 'BE':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'SITE',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $post->biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "BE",
                    'submerchantid' => $post->superMerchantId
                ];
                $url = 'https://paysprint.in/service-api/api/v1/service/aeps/balanceenquiry/index';
        
                //dd($json); exit;
                break;
            case 'MS':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'SITE',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $post->biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "MS",
                    'submerchantid' => $post->superMerchantId
                ];
                $url = 'https://paysprint.in/service-api/api/v1/service/aeps/ministatement/index';
                break;
            case 'CW':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'SITE',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $post->biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "CW",
                    'submerchantid' => $post->superMerchantId,
                    'amount' => $post->transactionAmount
                ];
                $url = 'https://paysprint.in/service-api/api/v1/service/aeps/cashwithdraw/index';
                break;
            case 'CD':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'SITE',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $post->biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "BE"
                ];
                $url = 'https://paysprint.in/service-api/api/v1/service/aeps/balanceenquiry/index';
                break;
            case 'M':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'SITE',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $post->biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "M",
                    'submerchantid' => $post->superMerchantId,
                    'amount' => $post->transactionAmount
                ];
                $url = 'https://paysprint.in/service-api/api/v1/service/aadharpay/aadharpay/index';
                
                break;
        }
        //dd($json);




        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
       
        //$payload = ["mobile"=>"9936606998","bank3_flag"=>"yes"];
        //dd($payload); exit;
        $payload = ['body' => $request];
        $res = JwtController::callApi($payload, $url);
        $res_1 = $res;
        $res = json_decode($res);
        $post->txnid = $refId;
        //dd($res); exit;
        $this->createFile($post->transactionType.'_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $res_1]);
        

        if($res->response_code == 1){
            if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $trtype = "credit";
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => $trtype,
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => '$bank->bankName',
                        'aepstype'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }elseif($post->transactionType == "MS"){
                    return response()->json([
                        'status'   => 'success', 
                        'message'  => 'Transaction Successfull '.$res->message,
                        'balance'  => $res->amount,
                        'rrn'      => $res->bankrrn,
                        "transactionType"   => $post->transactionType,
                        "title"    => "Mini Statement ".json_encode($res),
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'created_at'=> date('d M Y H:i'),
                        'bank'     => '$bank->bankName',
                        "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                    ]);
                }else{
                    return response()->json([
                        'status'   => 'success', 
                        'message'  => 'Transaction Successfull Account Balance : '.$res->balanceamount,
                        'balance'  => $res->balanceamount,
                        'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'NA',
                        "transactionType"   => $post->transactionType,
                        "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                        'bank'     => '$bank->bankName'
                    ]);
                }

            /*response getting ready*/
                $response = $res;
                
                if(isset($response->status)){
                    switch ($post->transactionType) {
                        case 'BE':
                            return response()->json([
                                'status'   => 'success', 
                                'message'  => 'Transaction Successfull Account Balance : '.$res->balanceamount,
                                'balance'  => $res->balanceamount,
                                'rrn'      => $res->bankrrn,
                                "transactionType"   => $post->transactionType,
                                "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                'id'       => $post->txnid,
                                'amount'   => $post->transactionAmount,
                                'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                'bank'     => '$bank->bankName'
                            ]);
                            break;
                        case 'MS':
                            return response()->json([
                                'status'   => 'success', 
                                'message'  => 'Transaction Successfull '.$res->message,
                                'balance'  => $res->balanceamount,
                                'rrn'      => $response->rrn,
                                "transactionType"   => $post->transactionType,
                                "title"    => "Mini Statement".json_encode($res),
                                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                'id'       => $post->txnid,
                                'created_at'=> date('d M Y H:i'),
                                'bank'     => '$bank->bankName',
                                "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                            ]);
                            break;
                        case 'CW':
                        case 'M':
                            /*cash withdrawl status*/
                            $url = 'https://paysprint.in/service-api/api/v1/service/aeps/aepsquery/query';
                            $json = ['reference' => $refId];
                            $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                            $request = base64_encode($ciphertext_raw);
                            $payload = ['body' => $request];
                            $statusres = JwtController::callApi($payload, $url);
                            $statusres = json_decode($statusres);
                            
                            $this->createFile($post->transactionType.'_aepsquery_'.$refId, ['payload' => $json, 'payload_enc' => $payload, 'url' => $url, 'response' => $statusres]);
                            
                            
                            /*cash withdrawl status*/
                            if($res->status == true){
                                if($post->transactionType == "CW" || $post->transactionType == "M"){
                                    if($post->transactionType == "M"){
                                        $product = "aadharpay";
                                    }else{
                                        $product = "aeps";
                                    }

                                    if($post->transactionAmount > 99){
                                        if($post->transactionAmount >=100 && $post->transactionAmount <=1000){
                                            $slab = "ptxn1";
                                        }elseif($post->transactionAmount >1000 && $post->transactionAmount <=2000){
                                            $slab = "ptxn2";
                                        }elseif($post->transactionAmount >2000 && $post->transactionAmount <=3000){
                                            $slab = "ptxn3";
                                        }elseif($post->transactionAmount >3000 && $post->transactionAmount <=3500){
                                            $slab = "ptxn4";
                                        }elseif($post->transactionAmount >3500 && $post->transactionAmount <=5000){
                                            $slab = "ptxn5";
                                        }elseif($post->transactionAmount >5000 && $post->transactionAmount <=50000){
                                            $slab = "ptxn6";
                                        }

                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $product, $slab);
                                        $tds = $this->getTds($profit);
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                    }

                                    if($post->transactionType == "CW"){
                                        User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit - $tds);
                                    }else{
                                        User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                        $tds = 0;
                                    }

                                    
                                    if($res->response_code == '1'){
                                        Aepsreport::where('id', $report->id)->update([
                                            'status' => 'success',
                                            'charge' => $profit,
                                            "tds"    => $tds,
                                            "aepstype" => $post->transactionType,
                                            'refno'  => $res->bankrrn,
                                            'payid'  => $refId
                                        ]);  

                                        $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
                                        $json = ['reference' => $refId, 'status' => 'Sucess'];
                                        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                                        $request = base64_encode($ciphertext_raw);
                                        $payload = ['body' => $request];
                                        $rf = JwtController::callApi($payload, $url);
                                        
                                        $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $rf]);
                                        //$statusres = json_decode($statusres);
                                    }else{
                                        $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
                                        $json = ['reference' => $refId, 'status' => 'Failed'];
                                        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                                        $request = base64_encode($ciphertext_raw);
                                        $payload = ['body' => $request];
                                        $tf = JwtController::callApi($payload, $url);
                                        
                                        $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $tf]);
                                    }

                                    

                                    if($post->transactionType == "M"){
                                        if($post->transactionAmount > 99){
                                            $aepsreport = Aepsreport::where('id', $report->id)->first();
                                            \Myhelper::commission($aepsreport, $slab, $product);
                                        }
                                    }else{
                                        if($post->transactionAmount > 500){
                                            $aepsreport = Aepsreport::where('id', $report->id)->first();
                                            \Myhelper::commission($aepsreport, $slab, $product);
                                        }
                                    }
                                }

                                


                                
                                if($post->transactionType != "MS"){
                                    if($res->response_code == '1'){
                                        return response()->json([
                                            'status'   => 'success', 
                                            'message'  => 'Transaction Successfull Balance Amount : '.$res->balanceamount." Amount : ".$post->transactionAmount." Server: ".$res->message,
                                            'balance'  => $res->balanceamount,
                                            'rrn'      => $res->bankrrn,
                                            "transactionType"   => $post->transactionType,
                                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($statusres),
                                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                            'id'       => $post->txnid,
                                            'amount'   => $post->transactionAmount,
                                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                            'bank'     => '$bank->bankName'
                                        ]);
                                    }else{
                                        return response()->json([
                                            'status'   => 'success', 
                                            'message'  => 'Transaction Successfull '.$res->message,
                                            'balance'  => $res->balanceamount,
                                            'rrn'      => 'NA',
                                            "transactionType"   => $post->transactionType,
                                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($statusres),
                                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                            'id'       => $post->txnid,
                                            'amount'   => $post->transactionAmount,
                                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                            'bank'     => '$bank->bankName'
                                        ]);
                                    }
                                    
                                }else{
                                    return response()->json([
                                        'status'   => 'success', 
                                        'message'  => 'Transaction Successfull  Balance Amount : '.$res->balanceamount." Amount : ".$post->transactionAmount." Server: ".$res->message,
                                        'balance'  => $res->balanceamount,
                                        'rrn'      => $res->bankrrn,
                                        "transactionType"   => $post->transactionType,
                                        "title"    => "Mini Statement"." ".json_encode($res),
                                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                        'id'       => $post->txnid,
                                        'created_at'=> date('d M Y H:i'),
                                        'bank'     => '$bank->bankName',
                                        "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                    ]);
                                }
                            }else{
                                /*if($post->transactionType == "CW" || $post->transactionType == "M" || $post->transactionType == "CD"){
                                    Aepsreport::where('id', $report->id)->update([
                                        'status' => 'pending',
                                        'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                        'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                    ]);
                                }*/

                                if($post->transactionType != "MS"){





                                    ///////////////////////////////////////////////////////////////////////////////////////////////////
                                    if($post->transactionType == "CW" || $post->transactionType == "M"){
                                        if($post->transactionType == "M"){
                                            $product = "aadharpay";
                                        }else{
                                            $product = "aeps";
                                        }

                                        if($post->transactionAmount > 99){
                                            if($post->transactionAmount >=100 && $post->transactionAmount <=1000){
                                                $slab = "ptxn1";
                                            }elseif($post->transactionAmount >1000 && $post->transactionAmount <=2000){
                                                $slab = "ptxn2";
                                            }elseif($post->transactionAmount >2000 && $post->transactionAmount <=3000){
                                                $slab = "ptxn3";
                                            }elseif($post->transactionAmount >3000 && $post->transactionAmount <=3500){
                                                $slab = "ptxn4";
                                            }elseif($post->transactionAmount >3500 && $post->transactionAmount <=5000){
                                                $slab = "ptxn5";
                                            }elseif($post->transactionAmount >5000 && $post->transactionAmount <=50000){
                                                $slab = "ptxn6";
                                            }

                                            $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $product, $slab);
                                            $tds = $this->getTds($profit);
                                        }else{
                                            $profit = 0;
                                            $tds = 0;
                                        }

                                        if($post->transactionType == "CW"){
                                            User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit - $tds);
                                        }else{
                                            User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                            $tds = 0;
                                        }

                                        
                                        if($res->response_code == '1'){
                                            Aepsreport::where('id', $report->id)->update([
                                                'status' => 'success',
                                                'charge' => $profit,
                                                "tds"    => $tds,
                                                "aepstype" => $post->transactionType,
                                                'refno'  => $res->bankrrn,
                                                'payid'  => $refId
                                            ]);  

                                            $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
                                            $json = ['reference' => $refId, 'status' => 'Sucess'];
                                            $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                                            $request = base64_encode($ciphertext_raw);
                                            $payload = ['body' => $request];
                                            $rf = JwtController::callApi($payload, $url);
                                            
                                            $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $rf]);
                                            //$statusres = json_decode($statusres);
                                        }else{
                                            $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
                                            $json = ['reference' => $refId, 'status' => 'Failed'];
                                            $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                                            $request = base64_encode($ciphertext_raw);
                                            $payload = ['body' => $request];
                                            $tf = JwtController::callApi($payload, $url);
                                            
                                            $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $tf]);
                                        }

                                        

                                        if($post->transactionType == "M"){
                                            if($post->transactionAmount > 99){
                                                $aepsreport = Aepsreport::where('id', $report->id)->first();
                                                \Myhelper::commission($aepsreport, $slab, $product);
                                            }
                                        }else{
                                            if($post->transactionAmount > 500){
                                                $aepsreport = Aepsreport::where('id', $report->id)->first();
                                                \Myhelper::commission($aepsreport, $slab, $product);
                                            }
                                        }
                                    }

                                    


                                    
                                    if($post->transactionType != "MS"){
                                        if($res->response_code == '1'){
                                            return response()->json([
                                                'status'   => 'success', 
                                                'message'  => 'Transaction Successfull Balance Amount : '.$res->balanceamount." Amount : ".$post->transactionAmount." Server: ".$res->message,
                                                'balance'  => $res->balanceamount,
                                                'rrn'      => $res->bankrrn,
                                                "transactionType"   => $post->transactionType,
                                                "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($statusres),
                                                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                                'id'       => $post->txnid,
                                                'amount'   => $post->transactionAmount,
                                                'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                                'bank'     => '$bank->bankName'
                                            ]);
                                        }else{
                                            return response()->json([
                                                'status'   => 'success', 
                                                'message'  => 'Transaction Successfull '.$res->message,
                                                'balance'  => $res->balanceamount,
                                                'rrn'      => 'NA',
                                                "transactionType"   => $post->transactionType,
                                                "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($statusres),
                                                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                                'id'       => $post->txnid,
                                                'amount'   => $post->transactionAmount,
                                                'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                                'bank'     => '$bank->bankName'
                                            ]);
                                        }
                                        
                                    }else{
                                        return response()->json([
                                            'status'   => 'success', 
                                            'message'  => 'Transaction Successfull  Balance Amount : '.$res->balanceamount." Amount : ".$post->transactionAmount." Server: ".$res->message,
                                            'balance'  => $res->balanceamount,
                                            'rrn'      => $res->bankrrn,
                                            "transactionType"   => $post->transactionType,
                                            "title"    => "Mini Statement"." ".json_encode($res),
                                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                            'id'       => $post->txnid,
                                            'created_at'=> date('d M Y H:i'),
                                            'bank'     => '$bank->bankName',
                                            "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                        ]);
                                    }
                                    //////////////////////////////////////////////////////////////////////////////////////////////////















                                    return response()->json([
                                        'status'   => 'Pending', 
                                        'message'  => 'Transaction Successfull Balance Amount : '.$res->balanceamount." Amount : ".$post->transactionAmount." Server: ".$res->message,
                                        'balance'  => isset($res->balanceamount) ? $res->balanceamount : '0',
                                        'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'Failed',
                                        "transactionType"   => $post->transactionType,
                                        "title"    => ($post->transactionType == "BE") ? "Balance Enquiry" : "Cash Withdrawal"." ".json_encode($res),
                                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                        'id'       => $post->txnid,
                                        'amount'   => $post->transactionAmount,
                                        'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                        'bank'     => '$bank->bankName'
                                    ]);
                                }else{
                                    return response()->json([
                                        'status'   => 'failed', 
                                        'message'  => isset($res->message) ? $res->message : $res->message,
                                        'balance'  => isset($res->balanceamount) ? $res->balanceamount : '0',
                                        'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'Failed',
                                        "transactionType"   => $post->transactionType,
                                        "title"    => "Mini Statement"." ".json_encode($res),
                                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                        'id'       => $post->txnid,
                                        'created_at'=> date('d M Y H:i'),
                                        'bank'     => '$bank->bankName',
                                        "data"     => isset($res->miniStatementStructureModel) ? $res->miniStatementStructureModel : []
                                    ]);
                                }
                            }
                            break;
                    }
                }else{

                }
            /*response getting ready*/

        }elseif($res->response_code == 0){
            $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
            $json = ['reference' => $refId, 'status' => 'Failed'];
            $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
            $request = base64_encode($ciphertext_raw);
            $payload = ['body' => $request];
            $rf = JwtController::callApi($payload, $url);

            $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $rf]);
            

            if($post->transactionType == 'CW' || $post->transactionType == 'M'){
                $insert = [
                    "mobile"  => $post->mobileNumber,
                    "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                    "txnid"   => $post->txnid,
                    "amount"  => $post->transactionAmount,
                    "user_id" => $post->user_id,
                    "balance" => $user->aepsbalance,
                    'type'    => 'credit',
                    'api_id'  => $this->aepsapi->id,
                    'credited_by' => $post->user_id,
                    'status'      => 'failed',
                    'rtype'       => 'main',
                    'transtype'   => 'transaction',
                    "bank"        => '$bank->bankName',
                    'aepstype'=> $post->transactionType
                ];

                $report = Aepsreport::create($insert);
                return response()->json([
                        'status'   => 'failed', 
                        'message'  => 'Transaction Pending '.$res->message,
                        'balance'  => '0',
                        'rrn'      => 'pending',
                        'errorMsg' => "pending",
                        "transactionType"   => $post->transactionType,
                        "title"    => ($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i')
                    ]);
            }elseif($post->transactionType == 'MS'){
                return response()->json([
                    'status'   => 'failed', 
                    'balance'  => isset($res->balanceamount) ? $res->balanceamount : '0',
                    'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'Failed',
                    'errorMsg' => isset($res->message) ? $res->message : $res->message,
                    "transactionType"   => $post->transactionType,
                    "title"    => "Mini Statement",
                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                    'id'       => $post->txnid,
                    'created_at'=> date('d M Y H:i'),
                    'bank'     => '$bank->bankName',
                    "data"     => isset($res->miniStatementStructureModel) ? $res->miniStatementStructureModel : []
                ]);
            }else{
                return response()->json([
                    'status'   => 'failed', 
                    'message'  => $res->message,
                    'balance'  => '0',
                    'rrn'      => 'Failed',
                    "transactionType"   => $post->transactionType,
                    "title"    => ($post->transactionType == "BE") ? "Balance Enquiry" : "Cash Withdrawal",
                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                    'id'       => $post->txnid,
                    'amount'   => $post->transactionAmount,
                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                    'bank'     => 'bank'
                ]);
            }
        }elseif($res->response_code == 2){
            $url = 'https://paysprint.in/service-api/api/v1/service/aeps/aepsquery/query';
            $json = ['reference' => $refId];
            $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
            $request = base64_encode($ciphertext_raw);
            $payload = ['body' => $request];
            $res = JwtController::callApi($payload, $url);
            
            $this->createFile($post->transactionType.'_aepsquery_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $res]);
            
            $res = json_decode($res);
            if($res->response_code == 1){
                if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $trtype = "credit";
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => $trtype,
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => '$bank->bankName',
                        'aepstype'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }

                Aepsreport::where('id', $report->id)->update([
                    'status' => 'success',
                    'charge' => $profit,
                    "tds"    => $tds,
                    "aepstype" => $post->transactionType,
                    'refno'  => $res->bankrrn,
                    'payid'  => $refId
                ]);  

                $url = 'https://paysprint.in/service-api/api/v1/service/aeps/threeway/threeway';
                $json = ['reference' => $refId, 'status' => 'Sucess'];
                $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                $request = base64_encode($ciphertext_raw);
                $payload = ['body' => $request];
                $tg = JwtController::callApi($payload, $url);
                
                $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $tg]);
                
                if($post->transactionType == "M"){
                    if($post->transactionAmount > 99){
                        $aepsreport = Aepsreport::where('id', $report->id)->first();
                        \Myhelper::commission($aepsreport, $slab, $product);
                    }
                }else{
                    if($post->transactionAmount > 500){
                        $aepsreport = Aepsreport::where('id', $report->id)->first();
                        \Myhelper::commission($aepsreport, $slab, $product);
                    }
                }

                if($post->transactionType != "MS"){
                    if($res->response_code == '1'){
                        return response()->json([
                            'status'   => 'success', 
                            'message'  => 'Transaction Successfull '.$res->message,
                            'balance'  => $res->balanceamount,
                            'rrn'      => $res->bankrrn,
                            "transactionType"   => $post->transactionType,
                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                            'id'       => $post->txnid,
                            'amount'   => $post->transactionAmount,
                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                            'bank'     => '$bank->bankName'
                        ]);
                    }else{
                        return response()->json([
                            'status'   => 'success', 
                            'message'  => 'Transaction Successfull '.$res->message,
                            'balance'  => $res->balanceamount,
                            'rrn'      => 'NA',
                            "transactionType"   => $post->transactionType,
                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                            'id'       => $post->txnid,
                            'amount'   => $post->transactionAmount,
                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                            'bank'     => '$bank->bankName'
                        ]);
                    }
                    
                }else{
                    return response()->json([
                        'status'   => 'success', 
                        'message'  => 'Transaction Successfull '.$res->message,
                        'balance'  => $res->balanceamount,
                        'rrn'      => $res->bankrrn,
                        "transactionType"   => $post->transactionType,
                        "title"    => "Mini Statement"." ".json_encode($res),
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'created_at'=> date('d M Y H:i'),
                        'bank'     => '$bank->bankName',
                        "data"     => isset($res->miniStatementStructureModel) ? $res->miniStatementStructureModel : []
                    ]);
                }


            }
        }elseif($res->response_code == 24){
            return response()->json(['status' => 'pending', 'message'=>'User onboard pending '.json_encode($res)]);
        }else{
            return response()->json(['status' => 'pending', 'message'=>'Txn Failed '.json_encode($res)]);
        }
        

        
    }

    public function registration(Request $post)
    {
        //dd($post->all()); exit;

        /*$payload = ["mobile"=>"9936606998","bank3_flag"=>"yes"];
        //dd($payload); exit;
        $url = 'https://paysprint.in/service-api/api/v1/service/dmt/remitter/queryremitter';
        $res = JwtController::callApi($payload, $url);
        dd(json_decode($res)); exit;*/

        //$payload = ["mobile"=>"9936606998","bank3_flag"=>"yes"];
        //dd($payload); exit;
        $url = 'https://paysprint.in/service-api/api/v1/service/onboard/onboard/getonboardurl';
        $payload['merchantcode'] = rand(10000, 999999);
        $payload['mobile'] = $post->phone1;
        $payload['is_new'] = '0';
        $payload['email'] = $post->emailid;
        $payload['firm'] = 'PAYMONEY';
        $payload['callback'] = 'https://paysprint.softmatic.ml/paysprint-callback';
        $res = JwtController::callApi($payload, $url);
        
        $this->createFile($post->phone1.'_onboard_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $res]);
        
        $response = json_decode($res);
        //dd($response); exit;
        if(property_exists($response, 'redirecturl')){
            return \Redirect::away($response->redirecturl);
        }else{
            return redirect(url('aeps'));
        }
        
    }

    public function iciciaepslog(Request $post)
    {
        if(!$this->api || $this->api->status == 0){
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }

        $agent = Mahaagent::where('bc_id', $post->BcId)->first();
        $user = User::where('id', $agent->user_id)->first();

        if(!$agent){
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }

        $insert = [
            "mobile" => $post->EndCustMobile,
            "aadhar" => $post->BcId,
            "txnid"  => $post->TransactionId,
            "amount" => $post->Amount,
            "bank"   => $post->BankIIN,
            "user_id"=> $user->id,
            "balance" => $user->aepsbalance,
            'aepstype'=> $post->Txntype,
            'status'  => 'pending',
            'authcode'=> $post->Timestamp,
            'payid'=> $post->TerminalId,
            'TxnMedium'=> $post->TxnMedium,
            'credited_by' => $user->id,
            'api_id' => $this->api->id,
            'type' => 'credit',
            "mytxnid" => $post->mytxnid,
            "terminalid" => $post->terminalid,
            'balance' => $user->aepsbalance
        ];

        $transaction = Aepsreport::create($insert);
        if($transaction){
            $output['TRANSACTION_ID'] = $post->mytxnid;
            $output['VENDOR_ID'] = $post->terminalid;
            $output['STATUS'] = "SUCCESS";
            $output['MESSAGE'] = "Success";
            return response()->json($output);
        }else{
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }
    }

    public function iciciaepslogupdate(Request $post)
    {
        $report = Aepsreport::where('mytxnid', $post->TransactionId)->where('terminalid', $post->VenderId)->where('aadhar', $post->BcCode)->first();
        if(!$report){
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Report Not Found";
            return response()->json($output);
        }

        $user = User::where('id', $report->user_id)->first();

        if(isset($post->Status) && strtolower($post->Status) == "success"){
            if($report->aepstype == "CW"){
                if($report->amount > 500){
                    if($report->amount >= 100 && $report->amount <= 3000){
                        $provider = Provider::where('recharge1', 'aeps1')->first();
                    }elseif($report->amount>3000 && $report->amount<=10000){
                        $provider = Provider::where('recharge1', 'aeps2')->first();
                    }
                    $usercommission = \Myhelper::getCommission($report->amount, $user->scheme_id, $provider->id, $user->role->slug);
                    $providerid = $provider->id;
                }else{
                    $usercommission = 0;
                    $providerid = 0;
                }
                
                User::where('id', $report->user_id)->increment('aepsbalance', $report->amount + $usercommission);
            }else{
                $usercommission = 0;
                $providerid = 0;
            }

            Aepsreport::where('id', $report->id)->update([
                'status' => "success",
                "refno"  => $post->rrn,
                "balance" => $user->aepsbalance,
                'charge' => $usercommission,
                'provider_id' => $providerid
            ]);
            try {
                if($report->amount > 500){
                    $report = Aepsreport::where('id', $report->id)->first();
                    \Myhelper::commission($report);
                }
            } catch (\Exception $th) {}
                
        }else{
            Aepsreport::where('id', $report->id)->update([
                'status' => "failed",
            ]);
        }
        
        $output['STATUS'] = "SUCCESS";
        $output['MESSAGE'] = "SUCCESS";
        return response()->json($output);
    }
}
