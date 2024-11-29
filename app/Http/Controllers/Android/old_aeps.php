<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtController;

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
use App\Model\PortalSetting;
use DB;

class AepsController extends Controller
{
    protected $api;
    protected $bankdata;
    protected $cred;
    public function __construct()
    {
        $this->api = Api::where('code', 'aeps')->first();
        $this->aepsapi = Api::where('code', 'aeps')->first();
        
    }
    

    public function geoip($ip){
        $geoIP  = json_decode(file_get_contents("http://api.ipstack.com/$ip?access_key=83919eb4a273d4a1b631bb2a842145c4&format=1"), true);
        return $geoIP;
    }

    public function index(Request $post)
    {
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
        	$output['statuscode'] = "ERR";
	        $output['message'] = "User details not matched";
	        return response()->json($output);
        }
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('aeps_service')) {
            abort(403);
        }

        if(!$this->api || $this->api->status == 0){
            abort(405);
        }

        $agent = Mahaagent::where('user_id', $post->user_id)->where('status', 'success')->first();

        $data['mahastate'] = Mahastate::get();
        $data['aepsbanks'] = \DB::table('aepsbanks')->get();
        $data['aadharbanks'] = \DB::table('aepsbanks')->get();
        $data['state'] = \DB::table('mahastates')->get();
        $data['fundrequest'] = Aepsfundrequest::where('user_id', $post->user_id)->where('status', 'pending')->first();
        if($agent == null){
            $data['is_agent'] = 'no';
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
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
        	$output['statuscode'] = "ERR";
	        $output['message'] = "User details not matched";
	        return response()->json($output);
        }
        $this->createFile('aeps_log_app_'.rand(10000, 999999), $post->all());
        $post['user_id'] = $post->user_id;
        $maha = Mahaagent::where('user_id', $post->user_id)->where('status', 'success')->first();
        $post['superMerchantId'] = $maha->merchant_id;
        
        switch ($post->transactionType) {
            case 'bank_list_aeps':
                $bank = DB::table('aepsbanks')->get();
                $output['statuscode'] = 'TXN';
                $output['message'] = "Bank List Fetched Successfully";
                $output['data'] = $bank;
                
                return response()->json($output);
                break;
            case 'bank_list_adharpe':
                $bank = DB::table('aepsbanks')->get();
                $output['statuscode'] = 'TXN';
                $output['message'] = "Bank List Fetched Successfully";
                $output['data'] = $bank;
                
                return response()->json($output);
                break;
            case 'user_kyc':
                    $user_id = $post->user_id;
                    $mahaag = Mahaagent::where('user_id', $user_id)->first();
                    
                    $flag_is = 0;
                    if($mahaag === null){
                        $refId= rand(10000, 999999);   
                    }else{
                        $refId = $mahaag->merchant_id; 
                        $flag_is = 1;
                    }
                    
                    
                    $url = $this->api->url.'onboard/onboard/getonboardurl';
                    $payload['merchantcode'] = $refId;
                    $payload['mobile'] = $post->phone;
                    $payload['is_new'] = '0';
                    $payload['email'] = $post->email;
                    $payload['firm'] = 'PAYMONEY';
                    $payload['is_icici_kyc'] = 1;
                    $payload['callback'] = JwtController::cred('callback_url');
                    $res = JwtController::callApi($payload, $url);
                    
                    $this->createFile($post->phone.'_onboard_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $res]);
                    
                    $insert = [
                        'email' => $post->email,
                        'mobile' => $post->phone,
                        'status' => 'pending',
                        'user_id' => $post->user_id,
                        'merchant_id' => $refId
                    ];
                    if($flag_is !=1){
                        $transaction = Mahaagent::create($insert);
                    }
                    
                    
                    return $res;
                    exit;
                    /*$response = json_decode($res);
                    return response
                    //dd(['res' => $response, 'req' => $payload]); exit;
                    if(property_exists($response, 'redirecturl')){
                        return \Redirect::away($response->redirecturl);
                    }else{
                        return \Redirect::back()->with([$response->message]);
                    }*/
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

        $user = User::where('id', $post->user_id)->first();
        $post['user_id'] = $user->id;
        $sessionkey = JwtController::cred('session_key');
        $iv =   JwtController::cred('iv');
        $gpsdata       =  $this->geoip($post->ip());
        $refId = rand(1000000, 999999999);
        $bank = DB::table('aepsbanks')->where('BankIIN', $post->nationalBankIdentificationNumber)->get();
        $bank = $bank[0];
        $this->bankdata = $bank;
        
        //$biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",urldecode($post->biodata)));

        
        $biodata       =  $post->biodata;//str_replace("&lt;","<",str_replace("&gt;",">",$post->biodata));
                



        switch ($post->transactionType) {
            case 'BE':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'APP',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "BE",
                    'submerchantid' => $post->superMerchantId
                ];
                $url = $this->api->url.'aeps/balanceenquiry/index';
                break;
            case 'MS':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'APP',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "MS",
                    'submerchantid' => $post->superMerchantId
                ];
                $url = $this->api->url.'aeps/ministatement/index';
                break;
            case 'CW':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'APP',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "CW",
                    'submerchantid' => $post->superMerchantId,
                    'amount' => $post->transactionAmount
                ];
                $url = $this->api->url.'aeps/cashwithdraw/index';
                break;
            case 'CD':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'APP',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "BE"
                ];
                $url = $this->api->url.'aeps/balanceenquiry/index';
                break;
            case 'M':
                $json = [
                    'latitude' => $gpsdata['latitude'],
                    'longitude' => $gpsdata['longitude'],
                    'mobilenumber' => $post->mobileNumber,
                    'referenceno' => $refId,
                    'ipaddress' => $post->ip(),
                    'adhaarnumber' => $post->adhaarNumber,
                    'accessmodetype' => 'APP',
                    'nationalbankidentification' => $post->nationalBankIdentificationNumber,
                    'requestremarks' => "Payment using AEPS",
                    'data' => $biodata,
                    'pipe' => 'bank1',
                    'timestamp' => date('Y-m-d H:m:i'),
                    'transactiontype' => "M",
                    'submerchantid' => $post->superMerchantId,
                    'amount' => $post->transactionAmount
                ];
                $url = $this->api->url.'aadharpay/aadharpay/index';
                
                break;
        }


        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        $payload = ['body' => $request];
        $res = JwtController::callApi($payload, $url);
        //dd($res); exit;
        //dd(['payload' => $payload, 'res'=> $res, 'url' => $url]); exit;
        $res_1 = $res;
        $res = json_decode($res);
        $post->txnid = $refId;
        $this->createFile($post->transactionType.'_'.$refId, ['payload' => $json, 'url' => $url, 'response' => $res_1]);

        //aadharpayoutput
         /*$jayParsedAry = [
           "status" => true, 
           "message" => "Request Completed", 
           "ackno" => 36381, 
           "amount" => 100, 
           "balanceamount" => "6889.08", 
           "bankrrn" => "112618794075", 
           "bankiin" => "607161", 
           "response" => 200, 
           "response_code" => 1 
        ]; */
        
        //BE output
        // $jayParsedAry = [
        //   "status" => true, 
        //   "message" => "Request Completed", 
        //   "ackno" => 3045391, 
        //   "amount" => 0, 
        //   "balanceamount" => "7089.08", 
        //   "bankrrn" => "112611271195", 
        //   "bankiin" => "607161", 
        //   "response_code" => 1, 
        //   "errorcode" => "00" 
        // ];
        
        //CW output
        // $jayParsedAry = [
        //   "status" => true, 
        //   "message" => "Request Completed", 
        //   "ackno" => 3037327, 
        //   "amount" => 100, 
        //   "balanceamount" => "6989.08", 
        //   "bankrrn" => "112611276188", 
        //   "bankiin" => "607161", 
        //   "response_code" => 1, 
        //   "errorcode" => "00" 
        // ]; 
        
        // $jayParsedAry = [
        //   "status" => true, 
        //   "message" => "Request Completed", 
        //   "ackno" => 36385, 
        //   "amount" => 100, 
        //   "balanceamount" => "6391.08", 
        //   "bankrrn" => "112620939549", 
        //   "bankiin" => "607161", 
        //   "response" => 200, 
        //   "response_code" => 1 
        // ]; 
        
 
 
        //$res = (object) $jayParsedAry;
        

        //return response()->json(['status' => 'ERR', 'message'=>'data from server '.json_encode($res)]);
        
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
                        "bank"        => $this->bankdata->BankName,
                        'aepstype'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);


                    //for getting transaction status
                    $url = $this->api->url.'aeps/aepsquery/query';
                    $json = ['reference' => $refId];
                    $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                    $request = base64_encode($ciphertext_raw);
                    $payload = ['body' => $request];
                    $statusres = JwtController::callApi($payload, $url);
                    $statusres = json_decode($statusres);
                    $this->createFile($post->transactionType.'_aepsquery_'.$refId, ['payload' => $json, 'payload_enc' => $payload, 'url' => $url, 'response' => $statusres]);
                    //for getting status
                    
                    if($post->transactionType == "M"){
                        $product = "aadharpay";
                        
                        if($post->transactionAmount >= 100 && $post->transactionAmount <= 10000){
                            $provider = Provider::where('recharge1', 'aadharpay1')->first();
                            $post['provider_id'] = $provider->id;
                        }elseif($post->transactionAmount>10001 && $post->transactionAmount<=20000){
                            $provider = Provider::where('recharge1', 'aadharpay2')->first();
                            $post['provider_id'] = $provider->id;
                        }elseif($post->transactionAmount>20001 && $post->transactionAmount<=30000){
                            $provider = Provider::where('recharge1', 'aadharpay3')->first();
                            $post['provider_id'] = $provider->id;
                        }elseif($post->transactionAmount>30001 && $post->transactionAmount<=40000){
                            $provider = Provider::where('recharge1', 'aadharpay4')->first();
                            $post['provider_id'] = $provider->id;
                        }elseif($post->transactionAmount>40001 && $post->transactionAmount<=50000){
                            $provider = Provider::where('recharge1', 'aadharpay5')->first();
                            $post['provider_id'] = $provider->id;
                        }
                        if($post->transactionAmount > 100){
                            $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                            $tds = $this->getTds($profit);
                        }else{
                            $profit = 0;
                            $tds = 0;
                        }
                        User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                        $tds = 0;
                    }else{
                        $product = "aeps";

                        if($post->transactionAmount >= 100 && $post->transactionAmount <= 3000){
                            $provider = Provider::where('recharge1', 'aeps1')->first();
                            $post['provider_id'] = $provider->id;
                        }elseif($post->transactionAmount>3000 && $post->transactionAmount<=10000){
                            $provider = Provider::where('recharge1', 'aeps2')->first();
                            $post['provider_id'] = $provider->id;
                        }

                        if($post->transactionAmount > 100){
                            $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                            $tds = $this->getTds($profit);
                        }else{
                            $profit = 0;
                            $tds = 0;
                        }
                        
                        User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit - $tds);
                    }
                    
                    
                    
                    
                    Aepsreport::where('id', $report->id)->update([
                        'status' => 'success',
                        'charge' => $profit,
                        "tds"    => $tds,
                        "aepstype" => $post->transactionType,
                        'refno'  => $res->bankrrn,
                        'payid'  => $refId
                    ]);  


                    
                    $url = $this->api->url.'aeps/threeway/threeway';
                    $json = ['reference' => $refId, 'status' => 'Sucess'];
                    $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                    $request = base64_encode($ciphertext_raw);
                    $payload = ['body' => $request];
                    $rf = JwtController::callApi($payload, $url);
                    $this->createFile($post->transactionType.'_threeway_'.$refId, ['payload' => $payload, 'url' => $url, 'response' => $rf]);





                    return response()->json([
                            'status'   => 'TXN', 
                            'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                            'balance'  => $res->balanceamount,
                            'rrn'      => $res->bankrrn,
                            "transactionType"   => $post->transactionType,
                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay"))),
                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                            'id'       => $post->txnid,
                            'amount'   => $post->transactionAmount,
                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                            'bank'     => $this->bankdata->BankName
                        ]);




                }elseif($post->transactionType == "MS"){
                    return response()->json([
                        'status'   => 'TXN', 
                        'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                        'balance'  => $res->balanceamount,
                        'rrn'      => $res->bankrrn,
                        "transactionType"   => $post->transactionType,
                        "title"    => "Mini Statement ",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'created_at'=> date('d M Y H:i'),
                        'bank'     => $this->bankdata->BankName,
                        "data"     => isset($res->ministatement) ? $res->ministatement : []
                    ]);
                }else{
                    return response()->json([
                        'status'   => 'TXN', 
                        'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                        'balance'  => $res->balanceamount,
                        'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'NA',
                        "transactionType"   => $post->transactionType,
                        "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                        'bank'     => $this->bankdata->BankName
                    ]);
                }


                 
            /*response getting ready*/

        }elseif($res->response_code == 0){
            $bank =  DB::table('aepsbanks')->where('BankIIN', $res->bankiin)->get();
            $bank = $bank[0];
            $url = $this->api->url.'aeps/threeway/threeway';
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
                    "bank"        => $this->bankdata->BankName,
                    'aepstype'=> $post->transactionType
                ];

                $report = Aepsreport::create($insert);
                return response()->json([
                        'status'   => 'TXF', 
                        'message'  => isset($res->message) ? $res->message : $res->message,
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
                    'status'   => 'TXF', 
                    'balance'  => isset($res->balanceamount) ? $res->balanceamount : '0',
                    'rrn'      => isset($res->bankrrn) ? $res->bankrrn : 'Failed',
                    'errorMsg' => isset($res->message) ? $res->message : $res->message,
                    "transactionType"   => $post->transactionType,
                    "title"    => "Mini Statement",
                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                    'id'       => $post->txnid,
                    'created_at'=> date('d M Y H:i'),
                    'bank'     => $this->bankdata->BankName,
                    "data"     => isset($res->miniStatementStructureModel) ? $res->miniStatementStructureModel : []
                ]);
            }else{
                return response()->json([
                    'status'   => 'TXF', 
                    'message'  => isset($res->message) ? $res->message : $res->message,
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
            $bank =  DB::table('aepsbanks')->where('BankIIN', $res->bankiin)->get();
            $bank = $bank[0];
            $url = $this->api->url.'aeps/aepsquery/query';
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
                        "bank"        => $this->bankdata->BankName,
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

                $url = $this->api->url.'aeps/threeway/threeway';
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
                            'status'   => 'TXN', 
                            'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                            'balance'  => $res->balanceamount,
                            'rrn'      => $res->bankrrn,
                            "transactionType"   => $post->transactionType,
                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                            'id'       => $post->txnid,
                            'amount'   => $post->transactionAmount,
                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                            'bank'     => $this->bankdata->BankName
                        ]);
                    }else{
                        return response()->json([
                            'status'   => 'TXN', 
                            'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                            'balance'  => $res->balanceamount,
                            'rrn'      => 'NA',
                            "transactionType"   => $post->transactionType,
                            "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : (($post->transactionType == "CD") ? "Cash Deposite" : "Aadhar Pay")))." ".json_encode($res),
                            'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                            'id'       => $post->txnid,
                            'amount'   => $post->transactionAmount,
                            'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                            'bank'     => $this->bankdata->BankName
                        ]);
                    }
                    
                }else{
                    return response()->json([
                        'status'   => 'TXN', 
                        'message'  => isset($res->message) ? $res->message : 'Transaction Successfull',
                        'balance'  => $res->balanceamount,
                        'rrn'      => $res->bankrrn,
                        "transactionType"   => $post->transactionType,
                        "title"    => "Mini Statement"." ".json_encode($res),
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'created_at'=> date('d M Y H:i'),
                        'bank'     => $this->bankdata->BankName,
                        "data"     => isset($res->miniStatementStructureModel) ? $res->miniStatementStructureModel : []
                    ]);
                }


            }
        }elseif($res->response_code == 24){
            return response()->json(['status' => 'ERR', 'message'=>'User onboard pending '.json_encode($res)]);
        }else{
            return response()->json(['status' => 'ERR', 'message'=>'Txn Failed '.json_encode($res)]);
        }
        

        
    }

    

    public function getTds($amount)
    {
        return $amount*5/100;
    }
}
