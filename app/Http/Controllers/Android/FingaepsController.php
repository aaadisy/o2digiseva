<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;
use App\User;
use App\Model\Circle;
use App\Model\Report;
use App\Model\Api;
use App\Model\Aepsfundrequest;
use App\Model\Aepsreport;
use App\Model\Fingagent;
use App\Model\Provider;
use App\Model\Setting;
use Illuminate\Validation\Rule;
use DB;
use File;

class FingaepsController extends Controller
{
    public $aepsapi;
    public function __construct()
    {
        $this->aepsapi = Api::where('code', 'aeps')->first();
    }

    // public function geoip($ip){
    //     $geoIP  = json_decode(file_get_contents("http://api.ipstack.com/$ip?access_key=49003c05bb4d851383468c238587d71c&format=1"), true);
    //     return $geoIP;
    // }

    public function createFile($file, $data){
        $data = json_encode($data);
        $file = '5_matm'.$file.'_file.txt';
        $destinationPath=public_path()."/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function geoip(){
        
        $lat = rand(21, 27).'.'.rand(1300, 2500); 
        $long = rand(85, 89).'.'.rand(4820, 5304);

        //$geoIP = array('latitude' => \Auth::user()->lat, 'longitude' => \Auth::user()->long);
        $geoIP = array('latitude' => $lat, 'longitude' => $long);
                  

        return $geoIP;
    }

    public function usergeoip($user_id){
        $user = User::where('id', $user_id)->first();
        $lat = $user->lat; 
        $long = $user->long; 

        //$geoIP = array('latitude' => \Auth::user()->lat, 'longitude' => \Auth::user()->long);
         $geoIP = array('latitude' => number_format($lat, 7, '.', ''), 'longitude' => number_format($long, 7, '.', ''));
                  

        return $geoIP;
    }
    
   
    
    public function useronboardingstatus(Request $post)
    {
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if($user){
            $agent = Fingagent::where('user_id', $user->id)->first();
                if(!$agent){
                    return response()->json(['status' => 'success', 'isOnboarded' => 0, 'message'=>'User Not OnBoarded']);
                }elseif($agent->status != 'approved'){
                    return response()->json(['status' => 'success', 'isOnboarded' => 2, 'message'=>'User OnBoarding '.$agent->status]);
                }else{
                    return response()->json(['status' => 'success', 'isOnboarded' => 1, 'message'=>'User Already OnBoarded']);
                }
                
                
        } else {
            return response()->json(['status' => 'ERR', 'message' => 'App Token could not found']);
        }
        
        
    }
    
    public function userOnboardingDetails(Request $post)
{
    $user = User::where('id', $post->user_id)->where('apptoken', $post->apptoken)->first();

    if ($user) {
        $agent = Fingagent::where('user_id', $user->id)->first();

        if (!$agent) {
            return response()->json(['status' => 'success', 'message' => 'User Not OnBoarded']);
        } else {
            $state = DB::table('fingstate')->get()->keyBy('stateId'); // Assuming 'stateId' is the primary key

            // Add the state name along with the state ID
            $agent->shopStateName = $state[$agent->shopState]->state ?? 'Unknown';
            $agent->merchantStateName = $state[$agent->merchantState]->state ?? 'Unknown';

            return response()->json([
                'status' => 'success',
                'data' => $agent,
                'message' => 'User OnBoarding Details fetched Successfully'
            ]);
        }
    } else {
        return response()->json(['status' => 'ERR', 'message' => 'App Token could not be found']);
    }
}


    public function initiate(Request $post)
    {
        $this->createFile(1, $post->all());
        
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if($user){
            $post['user_id'] = $user->id; //$post->id;
        } else {
            return response()->json(['status' => 'ERR', 'message' => 'App Token could not found']);
        }
        $post['superMerchantId'] = $this->aepsapi->option1;

        $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();



        if (
            (
                ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d')))
            )
        ) {
            $post['transactionType']  =  'AUO';
            $post['auth_type']  =  'AEPS';
            
        }

        if(isset($post->transactionType) && $post->transactionType == 'AUO')
        {
        $post['transactionType'] == 'AUO';
        $post['auth_type'] == $post->auth_type;
        
        }
        $gpsdata       =  $this->usergeoip($post->user_id);
        switch ($post->transactionType) {
            case 'matmstatus':
                $agent = Fingagent::where('user_id', $post->user_id)->first();
                if(!$agent) {
                    return response()->json(['status' => 'ERR', 'message' => 'Agent onboarding could not found']);
                }

                if($agent->status == "pending") {
                    return response()->json(['status' => 'ERR', 'message' => 'Agent onboarding pending']);
                }
                
                /*
                    {"merchantLoginId":"FINGPAY1234","merchantPassword":"e6e061838856bf47e1de730719fb2609","superMerchantId":2,"superMerchantPassword":"796c3ee556ac31f3754a38cfd15b8044","merchantTranId":"123456","hash":"oeFNf527cE911LaCzS9wiYBo/7E5C7QsvwHqrAykpyU="}
                */
                $rules = array(
                    'transactionType' => 'required',
                );
                break;

             case 'getdata':
                $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $data]);
                break;

            case 'getbanks':
                $aepsbanks = \DB::table('fingaepsbanks')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $aepsbanks]);
                break;
            case 'useronboard':
                $rules = array(
                    'merchantName' => 'required',
                    'merchantAddress' => 'required',
                    'merchantState' => 'required',
                    'merchantCityName' => 'required',
                    'merchantDistrictName' => 'required',
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
                    $post['merchantLoginId']  = "DIGI".rand(1111111111, 9999999999);
                } while (Fingagent::where("merchantLoginId", "=", $post->merchantLoginId)->first() instanceof Fingagent);

                do {
                    $post['merchantLoginPin'] = "DIGI".rand(111111, 999999);
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
                    'bankName1' => 'required',
                    'txtPidData'   => 'required',
                    'superMerchantId'   => 'required',
                );
                break;

            case 'CW':
            case 'M':
                $rules = array(
                    'transactionType' => 'required',
                    'mobileNumber'    => 'required|numeric|digits:10',
                    'adhaarNumber'    => 'required|numeric|digits:12',
                    'bankName1' => 'required',
                    'txtPidData'   => 'required',
                    'transactionAmount' => 'required|numeric|min:1|max:10000',
                    'superMerchantId'   => 'required',
                );
                break;

            

            case 'AUO':
                    $rules = array(
                        'transactionType' => 'required',
                        'auth_type' => 'required',
                        'mobileNumber'    => 'required|numeric|digits:10',
                        'adhaarNumber'    => 'required|numeric|digits:12',
                        'txtPidData'   => 'required',
                        'superMerchantId'   => 'required',
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

        //$user = \Auth::user();
        $post['user_id'] = $user->id;
        $sessionkey = '';
        $mt_rand = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        foreach ($mt_rand as $chr)
        {             
            $sessionkey .= chr($chr);         
        }

        $iv =   '06f2f04cc530364f';
        $fp =fopen("fingpay_public_production.txt","r");
        $publickey =fread($fp,8192);         
        fclose($fp);         
        openssl_public_encrypt($sessionkey,$crypttext,$publickey);
        $gpsdata       =  $this->usergeoip($post->user_id);
        switch ($post->transactionType) {

            case 'matmstatus':
                $val = $post->txnid.$agent->merchantLoginId.'955';
                $json = [
                    "merchantLoginId" => $agent->merchantLoginId,
                    "merchantPassword" =>  md5($agent->merchantLoginPin),
                    "superMerchantId" => 955,
                    "superMerchantPassword" => md5("1234d"),
                    "merchantTranId" => $post->txnid,
                    "hash" => base64_encode(hash("sha256", $val, True)),
                ];
                //dd([$json]); exit;
                
                $url =  "https://fpma.tapits.in/fpcardwebservice/api/ma/statuscheck/cw";
                //https://fingpayap.tapits.in/fpaepsweb/api/onboarding/merchant/creation/php/m1
                $header = [         
                    'Content-Type: text/xml',             
                    'trnTimestamp:'.date('d/m/Y H:i:s'),         
                    'hash:'.base64_encode(hash("sha256",json_encode($json), True)),   
                    'eskey:'.base64_encode($crypttext)         
                ]; 
                
                $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
                $request = base64_encode($ciphertext_raw);
                $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);

                dd([$url, $json, $header, $result]); exit;
                dd($result); exit;
                break;
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

                

               // $url = $this->aepsapi->url."fpaepsweb/api/onboarding/merchant/creation/php/m1";
               
               $url = "https://fingpayap.tapits.in/fpaepsweb/api/onboarding/merchant/creation/php/m1";
                break;

            case 'BE':
            case 'CW':
            case 'MS':
            case 'M':

            case 'AUO':
                $bank  = \DB::table('fingaepsbanks')->where('iinno', $post->bankName1)->first();
                $agent = Fingagent::where('user_id', $user->id)->first();
                if($post->has('txtPidData'))
                {
                    $post['txtPidData'] = $post->txtPidData;
                }
                else{
                    $post['txtPidData'] = $post->biodata;
                }
                $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$post->txtPidData));
                $xml           =  simplexml_load_string($biodata);
                //dd($xml); exit;
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);

                do {
                    $post['txnid'] = "MEA".rand(1111111111, 9999999999);
                } while (Aepsreport::where("txnid", "=", $post->txnid)->first() instanceof Aepsreport);
                //dd($headerarray); exit;

                if(isset($headerarray['Resp']['@attributes']['errInfo']) && !empty($headerarray['Resp']['@attributes']['errInfo'])){
                    $errInfo = $headerarray['Resp']['@attributes']['errInfo'];
                }else{
                    $errInfo = "";
                }

                $txndate = date('d/m/Y H:i:s');
                

                $json =  [
                    "captureResponse" => [
                        "PidDatatype" =>  "X",
                        "Piddata"     =>  $headerarray['Data'],
                        "ci"          =>  $skeyci,
                        "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                        "dpID"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                        "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                        "errInfo"     =>  $errInfo,
                        "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                        "fType"       =>  $headerarray['Resp']['@attributes']['fType'],
                        "hmac"        =>  $headerarray['Hmac'],
                        "iCount"      =>  "0",
                        "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                        "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                        "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                        "pCount"      =>  "0",
                        "pType"       =>  "0",
                        "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                        "rdsID"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                        "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                        "sessionKey"  =>  $headerarray['Skey']
                    ],

                    "cardnumberORUID"       => [
                        'adhaarNumber'      => $post->adhaarNumber,
                        "indicatorforUID"   => "0",
                        "nationalBankIdentificationNumber" => $post->bankName1
                    ],
                    "languageCode"   => "en",
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "mobileNumber"   => $post->mobileNumber,
                    "paymentType"    => "B",
                    "requestRemarks" => "Aeps", 
                    "timestamp"      => Carbon::now()->format('d/m/Y H:i:s'),
                    "transactionType"   => $post->transactionType,
                    "merchantUserName"  => $agent->merchantLoginId,
                    "merchantPin"       => md5($agent->merchantLoginPin),               
                    "subMerchantId"     => ""
                ];

                if($post->transactionType == "BE"){
                    $json["merchantTransactionId"] = $post->txnid;
                    $json['transactionAmount'] = 0;
                    $json['superMerchantId']   = $post->superMerchantId;
                }elseif($post->transactionType == "MS"){
                    $json["merchantTranId"] = $post->txnid;
                } elseif ($post->transactionType == "AUO") {
                    $json["serviceType"] =  $post->auth_type;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                } else{
                    $json["transactionAmount"] = $post->transactionAmount;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                }

                $txndate = date('d/m/Y H:i:s');
                if($post->device == "MANTRA_PROTOBUF"){
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.$txndate,         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }elseif($post->device == "MORPHO_PROTOBUF_L1" || $post->device == "MORPHO_PROTOBUF_L1WS") {
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . $headerarray['additional_info']['Param'][0]['@attributes']['value'],
                        'eskey:' . base64_encode($crypttext)
                    ];
                    
                }else{
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.date('d/m/Y H:i:s'),         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }

                if($post->transactionType == "BE"){
                    $url = $this->aepsapi->url."fpaepsservice/api/balanceInquiry/merchant/php/getBalance";
                }elseif($post->transactionType == "MS"){
                    $url = "https://fingpayap.tapits.in/fpaepsservice/api/miniStatement/merchant/php/statement";
                }elseif ($post->transactionType == "AUO") {
                    $url = $this->aepsapi->url . "fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fpuat.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";

                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                } elseif($post->transactionType == "M"){
                    $url = $this->aepsapi->url."fpaepsservice/api/aadhaarPay/merchant/php/pay";
                }else{
                    $url = $this->aepsapi->url."fpaepsservice/api/cashWithdrawal/merchant/php/withdrawal";
                    
                }
                
               
                //dd([$json, $url]); 
                if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => "credit",
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => $bank->bankName,
                        'aepstype'=> $post->transactionType,
                        'withdrawType'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }
                break;
        }

        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
        //$result = ["response" => '{"status":true,"message":"Request Completed","data":{"terminalId":null,"requestTransactionTime":"09/03/2022 23:19:06","transactionAmount":100.0,"transactionStatus":"successful","balanceAmount":2936.11,"strMiniStatementBalance":null,"bankRRN":"206823266593","transactionType":"CW","fpTransactionId":"CWBF2715521090322231906229I","merchantTxnId":null,"errorCode":null,"errorMessage":null,"merchantTransactionId":"MEA5925054953","bankAccountNumber":null,"ifscCode":null,"bcName":null,"transactionTime":null,"agentId":0,"issuerBank":null,"customerAadhaarNumber":null,"customerName":null,"stan":null,"rrn":null,"uidaiAuthCode":null,"bcLocation":null,"demandSheetId":null,"mobileNumber":null,"urnId":null,"miniStatementStructureModel":null,"miniOffusStatementStructureModel":null,"miniOffusFlag":false,"transactionRemark":null,"bankName":null,"prospectNumber":null,"internalReferenceNumber":null,"biTxnType":null,"subVillageName":null,"userProfileResponseModel":null,"hindiErrorMessage":null,"loanAccNo":null,"responseCode":"00","fpkAgentId":null},"statusCode":10000}', "error" => "", "code" => 200];
        $allreq = ['request' => $post->all(), 'response' => $result];
        if($post->transactionType == "AUO"){
        $this->createFile('allreq', $allreq);
        }

        if($result['response'] == ''){
            switch ($post->transactionType) {
                case 'useronboardsubmit':
                    return response()->json(['status' => 'pending', 'message'=>'User onboard pending']);
                    break;

                    case 'AUO':

                        if ($post->auth_type == "AEPS") {
                            Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                        } else {
                            Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                        }
                        return response()->json(['status' => 'success', 'transactionType' => 'AUO', 'message' =>  ' ' . $post->auth_type . ' Authentication  Successfull']);
                        break;

                case 'CW':
                case 'M':
                    return response()->json([
                        'status'   => 'pending', 
                        'message'  => 'Transaction Pending',
                        'balance'  => '0',
                        'rrn'      => 'pending',
                        'errorMsg' => "pending",
                        "transactionType"   => $post->transactionType,
                        "title"    => ($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at'=> $report->created_at
                    ]);
                    break;
            }
        }

        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->status)){
                if (isset($response->status) && isset($response->data->responsecode)) {
                    if ($response->status == "false" && $response->data->responsecode == 'FP069') {
                        Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => NULL]);
                        Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => NULL]);

                        return response()->json(['status' => 'FP069', 'message' =>  ($response->data->responseMessage ?? $response->message)
                    ]);
                    }
                }
                
                switch ($post->transactionType) {
                    case 'useronboardsubmit':
                        if($response->status == "true"){
                            Fingagent::where('id', $post->id)->update(['status' => "approved"]);
                            return response()->json(['status' => 'success', 'message'=>'User onboard successfully']);
                        }else{
                            return response()->json(['status' => 'ERR', $response->message]);
                        }
                        break;
                    
                    case 'AUO':
                         \Myhelper::handleMaster2faCost($post->auth_type,$post->user_id);
                            if($response->status == "true")
                            {
                                if ($post->auth_type == "AEPS") {
                                    Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                                } else {
                                    Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                                }

                                return response()->json(['status' => 'success', 'transactionType' => 'AUO', 'message' =>  ' ' . $post->auth_type . ' '. $response->message]);
                            
                            }else{
                                return response()->json(['status' => 'ERR', 'message' => $response->message]);
                            }
                            
                           break;
                    
                    case 'BE':
                    case 'CW':
                    case 'MS':
                    case 'M':
                        if($response->status == true && isset($response->data) && in_array($response->data->errorCode, ['null', null])){
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                if($post->transactionType == "M"){
                                    $product = "aadharpay";
                                }else{
                                    $product = "fingaeps";
                                }
                                //commision set here
                                
                                if($post->transactionType == "M"){
                                    $product = "aadharpay";
                                    
                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aadharpay')->first();
                                    $post['provider_id'] = $provider->id;
                                    if($provider){
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = 0;//$this->getGst($profit);
                                        $profit = $profit;
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    $tds = 0;
                                    $gst = 0;
                                }elseif($post->transactionType == "MS"){
                                    $product = "ms";
                                    
                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'ms')->first();
                                    $post['provider_id'] = $provider->id;
      
                                    
            
                                    if($provider){
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit-($tds+$gst);
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    
                                }else{
                                    $product = "aeps";
                                    
                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aeps')->first();
                                    $post['provider_id'] = $provider->id;
      
                                    
            
                                    if($provider){
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit-($tds+$gst);
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    
                                }
                                //commision set here
                                
                                

                                if($post->transactionType == "CW"){
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                }elseif($post->transactionType == "M"){
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }else{
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }



                                // if($post->transactionType == "CW"){
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                // }elseif($post->transactionType == "MS"){
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                    
                                // }elseif($post->transactionType == "M"){
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    
                                // }else{
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                //     $tds = 0;
                                //     $gst = 0;
                                // }

                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'success',
                                    'amount'  => $post->transactionAmount,
                                    'charge'  => $profit,
                                    'tds'     => $tds,
                                    'refno'  => $response->data->bankRRN,
                                    'payid'  => $response->data->fpTransactionId
                                ]);

                                $insert = [
                                    'number'  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'mobile'  => $post->mobileNumber,
                                    'provider_id' => '87',
                                    'api_id'  => $this->aepsapi->id,
                                    'txnid'   => $post->txnid,
                                    'amount'  => $post->transactionAmount,
                                    'charge'  => $profit,
                                    'tds'     => $tds,
                                    'credit_by'=> $post->user_id,
                                    "user_id" => $post->user_id,
                                    "balance" => $user->mainwallet,
                                    'payid'   => $response->data->fpTransactionId,
                                    'refno'   => $response->data->bankRRN,
                                    'status'  => 'success',
                                    'rtype'   => 'commission',
                                    'trans_type' => "credit",
                                    'via'     => "app",
                                    'product' => "aeps"
                                ];

                                Report::create($insert);


                                if($post->transactionType == "CW")
                                {
                                    $report['provider_id'] = $provider->id;
                                    $report['apicode'] = 'aeps';
                                    \Myhelper::aepscommission($report);
                                }

                            
                            }

                            $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";
                    
                            $requestbody = [
                                [
                                    "merchantTransactionId" => $post->txnid,
                                    "fingpayTransactionId"  => $response->data->fpTransactionId,
                                    "transactionRrn"  => $response->data->bankRRN,
                                    "responseCode"    => "00",
                                    "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                                    "serviceType"     => $post->transactionType
                                ]
                            ];


                            // $headerbody = json_encode($requestbody)."";
                            
                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'trnTimestamp:'.$txndate,
                            //     'hash:'.base64_encode(hash("sha256",json_encode($headerbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:easypaisad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "yes", $post);
                            
                            // dd([
                            //     "Body"     => json_encode($requestbody),
                            //     "HashBody" => $headerbody,
                            //     "Header"   => $requestheader,
                            //     "Response" => $result
                            // ]);
                            
                            if($post->transactionType != "MS"){
                                if($post->transactionType == "BE"){
                                    $trc = "Balance Enquiry";
                                }elseif($post->transactionType == "CW"){
                                    $trc = "Cash Withdrawal";
                                }elseif($post->transactionType == "M"){
                                    $trc = "Aadhar Pay";
                                }elseif($post->transactionType == "CD"){
                                    $trc = "Cash Deposite";
                                }
                                return response()->json([
                                    'status'   => 'success', 
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => $trc, //(($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay")),
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                             if($post->transactionType != "CW" || $post->transactionType != "M"){

                                $profit = \Myhelper::getCommission(0, $user->scheme_id, '88', $user->role->slug);
                                $tds = $this->getTds($profit);
                                $gst = $this->getGst($profit);
                                $profit = $profit-($tds+$gst);
                                    
                                //User::where('id', $post->user_id)->increment('aepsbalance',$profit);
                                User::where('id', $post->user_id)->increment('mainwallet',$profit);
                                              



                                              
                                              $trtype = "credit";
                                                $insert = [
                                                    "mobile"  => $post->mobileNumber,
                                                    "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                                    "txnid"   => $post->txnid,
                                                    "amount"  => "0.00",
                                                    "charge"  => $profit,
                                                    "user_id" => $post->user_id,
                                                    "tds"     => $this->getTds($profit),
                                                    "balance" => $user->aepsbalance,
                                                    'type'    => $trtype,
                                                    'refno'   => 'Mini Statement' ,
                                                    'api_id'  => $this->aepsapi->id,
                                                    'credited_by' => $post->user_id,
                                                    'status'      => 'success',
                                                    'rtype'       => 'main',
                                                    'transtype'   => 'transaction',
                                                    'bank'     => $bank->bankName,
                                                    'aepstype'=> $post->transactionType,
                                                    'withdrawType'=> $post->transactionType
                                                ];

                                                $report = Aepsreport::create($insert);


                                                $insert = [
                                                    'number'  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                                    'mobile'  => $post->mobileNumber,
                                                    'provider_id' => '88',
                                                    'api_id'  => $this->aepsapi->id,
                                                    'txnid'   => $post->txnid,
                                                    'amount'  => "0.00",
                                                    'charge'  => $profit,
                                                    'tds'     => $this->getTds($profit),
                                                    'credit_by'=> $post->user_id,
                                                    "user_id" => $post->user_id,
                                                    "balance" => $user->mainwallet,
                                                    'payid'   => $post->txnid,
                                                    'refno'   => $response->data->bankRRN,
                                                    'status'  => 'success',
                                                    'rtype'   => 'commission',
                                                    'trans_type' => "credit",
                                                    'via'     => "app",
                                                    'product' => "aeps"
                                                ];
                
                                                Report::create($insert);




                                                if($post->transactionType == "MS")
                                                {
                                                    $report['provider_id'] = '88';
                                                    $report['apicode'] = 'aeps';
                                                    \Myhelper::aepscommission($report);
                                                }
                                          }

                                return response()->json([
                                    'status'   => 'success', 
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statementx",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        }else{
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'failed',
                                    'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                    'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                ]);
                            }

                            if($post->transactionType != "MS"){
                                if($response->message == 'Please do 2fa before initiating transaction'){

                                    Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => NULL]);

                                }

                                return response()->json([
                                    'status'   => 'failed', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => ($post->transactionType == "BE") ? "Balance Enquiry" : "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                if($response->message == 'Please do 2fa before initiating transaction'){

                                    Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => NULL]);

                                }
                                return response()->json([
                                    'status'   => 'failed', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        }
                        break;
                }
            }
        }

        if($post->transactionType == "AUO"){
            
            if ($response->status == "true") {
                if ($post->auth_type == "AEPS") {
                    Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                } else {
                    Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                }
                return response()->json([
                    'status' => 'success',
                    'auth_type' => $post->auth_type,
                    'message' => ($response->data->responseMessage ?? $response->message)
                ]);
            }
            else{
                return response()->json([
                    'status' => 'failed',
                    'auth_type' => $post->auth_type,
                    'message' => ($response->data->responseMessage ?? $response->message)
                ]);
            }

            
        }
        if($post->transactionType != "MS"){
            return response()->json([
                'status'   => 'pending', 
                'message'  => 'Transaction Under Process',
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Cash Withdrawal",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'amount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }else{
            return response()->json([
                'status'   => 'failed', 
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Mini Statement",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName,
                "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
            ]);
        }
    }

    public function old_initiate(Request $post)
    {
        $this->createFile(1, $post->all());
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        //dd($user->id); exit;
        $post['user_id'] = $user->id;//$post->id;
        $post['superMerchantId'] = $this->aepsapi->option1;
        $agent = Fingagent::where('user_id', $post->user_id)->first();
        $gpsdata       =  $this->usergeoip($post->user_id);        
        //dd($agent);

        switch ($post->transactionType) {
            case 'getdata':
                $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $data]);
                break;

            case 'getbanks':
                $aepsbanks = \DB::table('fingaepsbanks')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $aepsbanks]);
                break;
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
                    $post['merchantLoginId']  = "DIGI".rand(1111111111, 9999999999);
                } while (Fingagent::where("merchantLoginId", "=", $post->merchantLoginId)->first() instanceof Fingagent);

                do {
                    $post['merchantLoginPin'] = "DIGI".rand(111111, 999999);
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
                    'superMerchantId'   => 'required',
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
                    'superMerchantId'   => 'required',
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
        $post['user_id'] = $post->id;
        $sessionkey = '';
        //dd($post->transactionType); exit;
        $mt_rand = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        foreach ($mt_rand as $chr)
        {             
            $sessionkey .= chr($chr);         
        }

        $iv =   '06f2f04cc530364f';
        $fp =fopen("fingpay_public_production.txt","r");
        $publickey =fread($fp,8192);         
        fclose($fp);         
        openssl_public_encrypt($sessionkey,$crypttext,$publickey);
        $gpsdata       =  $this->usergeoip($post->user_id);
        //dd($post->all()); exit;
        //dd($post->transactionType); exit;
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

                

               // $url = $this->aepsapi->url."fpaepsweb/api/onboarding/merchant/creation/php/m1";
               
               $url = "https://fingpayap.tapits.in/fpaepsweb/api/onboarding/merchant/creation/php/m1";
                break;

            case 'BE':
            case 'CW':
            case 'MS':
            case 'M':
                $bank  = \DB::table('fingaepsbanks')->where('iinno', $post->nationalBankIdentificationNumber)->first();
                //dd($bank); exit;
                //$agent = Fingagent::where('user_id', $post->user_id)->first();
                $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$post->biodata));
                $xml           =  simplexml_load_string($biodata);
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
                //dd($headerarray['Resp']['@attributes']); exit;
                do {
                    $post['txnid'] = "MEA".rand(1111111111, 9999999999);
                } while (Aepsreport::where("txnid", "=", $post->txnid)->first() instanceof Aepsreport);

                if(isset($headerarray['Resp']['@attributes']['errInfo'])){
                    $headerarray['Resp']['@attributes']['errInfo'] = $headerarray['Resp']['@attributes']['errInfo'];
                }else{
                    $headerarray['Resp']['@attributes']['errInfo'] = 0;
                }
                
                $json =  [
                    "captureResponse" => [
                        "PidDatatype" =>  "X",
                        "Piddata"     =>  $headerarray['Data'],
                        "ci"          =>  $skeyci,
                        "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                        "dpID"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                        "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                        "errInfo"     =>  $headerarray['Resp']['@attributes']['errInfo'],
                        "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                        "fType"       =>  $headerarray['Resp']['@attributes']['fType'],
                        "hmac"        =>  $headerarray['Hmac'],
                        "iCount"      =>  "0",
                        "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                        "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                        "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                        "pCount"      =>  "0",
                        "pType"       =>  "0",
                        "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                        "rdsID"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                        "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                        "sessionKey"  =>  $headerarray['Skey']
                    ],

                    "cardnumberORUID"       => [
                        'adhaarNumber'      => $post->adhaarNumber,
                        "indicatorforUID"   => "0",
                        "nationalBankIdentificationNumber" => $post->nationalBankIdentificationNumber
                    ],
                    "languageCode"   => "en",
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "mobileNumber"   => $post->mobileNumber,
                    "paymentType"    => "B",
                    "requestRemarks" => "Aeps", 
                    "timestamp"      => Carbon::now()->format('d/m/Y H:i:s'),
                    "transactionType"   => $post->transactionType,
                    "merchantUserName"  => $agent->merchantLoginId,
                    "merchantPin"       => md5($agent->merchantLoginPin),//$agent->merchantLoginPin,//md5($agent->merchantLoginPin),               
                    "subMerchantId"     => ""
                ];
                //dd($json); exit;
                //return json_encode(); 
                if($post->transactionType == "BE"){
                    $json["merchantTransactionId"] = $post->txnid;
                    $json['transactionAmount'] = 0;
                    $json['superMerchantId']   = "955";//$post->superMerchantId;
                }elseif($post->transactionType == "MS"){
                    $json["merchantTranId"] = $post->txnid;
                }else{
                    $json["transactionAmount"] = $post->transactionAmount;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = "955"; //$post->superMerchantId;
                }

                $txndate = date('d/m/Y H:i:s');
                //dd($post->all()); exit;
                if($post->device == "MANTRA"){
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.$txndate,         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }else{
                    //dd($headerarray); exit;
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.date('d/m/Y H:i:s'),         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }

                if($post->transactionType == "BE"){
                    $url = $this->aepsapi->url."fpaepsservice/api/balanceInquiry/merchant/php/getBalance";
                }elseif($post->transactionType == "MS"){
                    $url = "https://fingpayap.tapits.in/fpaepsservice/api/miniStatement/merchant/php/statement";
                }elseif($post->transactionType == "M"){
                    $url = $this->aepsapi->url."fpaepsservice/api/aadhaarPay/merchant/php/pay";
                }else{
                    $url = $this->aepsapi->url."fpaepsservice/api/cashWithdrawal/merchant/php/withdrawal";
                    
                }
                
               

                if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => "credit",
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => $bank->bankName,
                        'aepstype'=> $post->transactionType,
                        'withdrawType'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }
                break;
        }

        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        
        $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
   
        if($result['response'] == ''){
            switch ($post->transactionType) {
                case 'useronboardsubmit':
                    return response()->json(['status' => 'pending', 'message'=>'User onboard pending']);
                    break;

                case 'CW':
                case 'M':
                    return response()->json([
                        'status'   => 'pending', 
                        'message'  => 'Transaction Pending',
                        'balance'  => '0',
                        'rrn'      => 'pending',
                        'errorMsg' => "pending",
                        "transactionType"   => $post->transactionType,
                        "title"    => ($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at'=> $report->created_at
                    ]);
                    break;
            }
        }

        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->status)){
                switch ($post->transactionType) {
                    case 'useronboardsubmit':
                        if($response->status == "true"){
                            Fingagent::where('id', $post->id)->update(['status' => "approved"]);
                            return response()->json(['status' => 'success', 'message'=>'User onboard successfully']);
                        }else{
                            return response()->json(['status' => 'ERR', $response->message]);
                        }
                        break;
                    
                    case 'BE':
                    case 'CW':
                    case 'MS':
                    case 'M':
                        if($response->status == true && isset($response->data) && in_array($response->data->errorCode, ['null', null])){
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                if($post->transactionType == "M"){
                                    $product = "aadharpay";
                                }else{
                                    $product = "fingaeps";
                                }
                                //commision set here
                                
                                if($post->transactionType == "M"){
                                    $product = "aadharpay";
                                    
                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aadharpay')->first();
                                    $post['provider_id'] = $provider->id;
                                    if($provider){
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit;
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    $tds = 0;
                                    $gst = 0;
                                }else{
                                    $product = "aeps";
                                    
                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aeps')->first();
                                    $post['provider_id'] = $provider->id;
      
                                    
            
                                    if($provider){
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit-($tds+$gst);
                                    }else{
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    
                                }
                                //commision set here
                                
                                
                                if($post->transactionType == "CW"){
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                }elseif($post->transactionType == "M"){
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }else{
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }


                                // if($post->transactionType == "CW"){
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                
                                // }else{
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    
                                //     $tds = 0;
                                //     $gst = 0;
                                // }

                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'success',
                                    'charge' => $profit,
                                    'gst' => $gst,
                                    "tds"    => $tds,
                                    'refno'  => $response->data->bankRRN,
                                    'payid'  => $response->data->fpTransactionId
                                ]);

                                if($post->transactionType == "CW")
                                {
                                    $report['provider_id'] = $provider->id;
                                    $report['apicode'] = 'aeps';
                                    \Myhelper::aepscommission($report);
                                }

                            
                            }

                            $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";
                    
                            $requestbody = [
                                [
                                    "merchantTransactionId" => $post->txnid,
                                    "fingpayTransactionId"  => $response->data->fpTransactionId,
                                    "transactionRrn"  => $response->data->bankRRN,
                                    "responseCode"    => "00",
                                    "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                                    "serviceType"     => $post->transactionType
                                ]
                            ];


                            // $headerbody = json_encode($requestbody)."";
                            
                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'trnTimestamp:'.$txndate,
                            //     'hash:'.base64_encode(hash("sha256",json_encode($headerbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:easypaisad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "yes", $post);
                            
                            // dd([
                            //     "Body"     => json_encode($requestbody),
                            //     "HashBody" => $headerbody,
                            //     "Header"   => $requestheader,
                            //     "Response" => $result
                            // ]);
                            
                            if($post->transactionType != "MS"){
                                return response()->json([
                                    'status'   => 'success', 
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay")),
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                             if($post->transactionType != "CW" || $post->transactionType != "M"){

                                $profit = \Myhelper::getCommission(0, $user->scheme_id, '88', $user->role->slug);
                                 User::where('id', $post->user_id)->increment('aepsbalance',$profit);
                                              
                                              $trtype = "credit";
                                                $insert = [
                                                    "mobile"  => $post->mobileNumber,
                                                    "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                                    "txnid"   => $post->txnid,
                                                    "amount"  => $profit,
                                                    "user_id" => $post->user_id,
                                                    "balance" => $user->aepsbalance,
                                                    'type'    => $trtype,
                                                    'refno'   => 'Mini Statement' ,
                                                    'api_id'  => $this->aepsapi->id,
                                                    'credited_by' => $post->user_id,
                                                    'status'      => 'success',
                                                    'rtype'       => 'main',
                                                    'transtype'   => 'transaction',
                                                    'bank'     => $bank->bankName,
                                                    'aepstype'=> $post->transactionType,
                                                    'withdrawType'=> $post->transactionType
                                                ];

                                                $report = Aepsreport::create($insert);

                                                if($post->transactionType == "MS")
                                                {
                                                    $report['provider_id'] = '88';
                                                    $report['apicode'] = 'aeps';
                                                    \Myhelper::aepscommission($report);
                                                }
                                          }

                                return response()->json([
                                    'status'   => 'success', 
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statementx",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        }else{
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'failed',
                                    'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                    'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                ]);
                            }

                            if($post->transactionType != "MS"){
                                return response()->json([
                                    'status'   => 'failed', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => ($post->transactionType == "BE") ? "Balance Enquiry" : "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                return response()->json([
                                    'status'   => 'failed', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        }
                        break;
                }
            }
        }

        if($post->transactionType != "MS"){
            return response()->json([
                'status'   => 'pending', 
                'message'  => 'Transaction Under Process',
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Cash Withdrawal",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'amount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }else{
            return response()->json([
                'status'   => 'failed', 
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Mini Statement",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName,
                "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
            ]);
        }
    }

    public function transaction(Request $post)
    {
        $this->createFile(1, $post->all());
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        $gpsdata       =  $this->usergeoip($post->user_id);
        if(!$user){
            $output['status']  = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('aeps_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

        $post['api_id']  =  $this->aepsapi->id;
        
        $post['superMerchantId'] = $this->aepsapi->option1;
        switch ($post->transactionType) {
            case 'getdata':
                $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $data]);
                break;

            case 'getbanks':
                $aepsbanks = \DB::table('fingaepsbanks')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $aepsbanks]);
                break;

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
                    'superMerchantId'   => 'required',
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
                    'superMerchantId'   => 'required',
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

        $sessionkey = '';
        $mt_rand = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        foreach ($mt_rand as $chr)
        {             
            $sessionkey .= chr($chr);         
        }

        $iv =   '06f2f04cc530364f';
        $fp =fopen("fingpay_public_production.txt","r");
        $publickey =fread($fp,8192);         
        fclose($fp);         
        openssl_public_encrypt($sessionkey,$crypttext,$publickey);
        $gpsdata       =  $this->usergeoip($post->user_id);
        
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
            case 'CW':
            case 'MS':
            case 'M':

                $agent = Fingagent::where('user_id', $post->user_id)->first();
                if(!$agent){
                    return response()->json(['status' => 'ERR', 'message'=>'Agent Onboard Pedning']);
                }
                
                if($agent->status != "approved"){
                    return response()->json(['status' => 'ERR', 'message'=>'Agent approval pending']);
                }

                $bank  = DB::table('fingaepsbanks')->where('iinno', $post->nationalBankIdentificationNumber)->first();
                
                $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$post->biodata));
                $xml           =  simplexml_load_string($biodata);
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);

                do {
                    $post['txnid'] = "MEA".rand(1111111111, 9999999999);
                } while (Aepsreport::where("txnid", "=", $post->txnid)->first() instanceof Aepsreport);


                $json =  [
                    "captureResponse" => [
                        "PidDatatype" =>  "X",
                        "Piddata"     =>  $headerarray['Data'],
                        "ci"          =>  $skeyci,
                        "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                        "dpID"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                        "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                        "errInfo"     =>  $headerarray['Resp']['@attributes']['errInfo'],
                        "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                        "fType"       =>  $headerarray['Resp']['@attributes']['fType'],
                        "hmac"        =>  $headerarray['Hmac'],
                        "iCount"      =>  "0",
                        "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                        "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                        "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                        "pCount"      =>  "0",
                        "pType"       =>  "0",
                        "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                        "rdsID"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                        "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                        "sessionKey"  =>  $headerarray['Skey']
                    ],

                    "cardnumberORUID"       => [
                        'adhaarNumber'      => $post->adhaarNumber,
                        "indicatorforUID"   => "0",
                        "nationalBankIdentificationNumber" => $bank->iinno
                    ],
                    "languageCode"   => "en",
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "mobileNumber"   => $post->mobileNumber,
                    "paymentType"    => "B",
                    "requestRemarks" => "Aeps", 
                    "timestamp"      => Carbon::now()->format('d/m/Y H:i:s'),
                    "transactionType"   => $post->transactionType,
                    "merchantUserName"  => $agent->merchantLoginId,
                    "merchantPin"       => md5($agent->merchantLoginPin),               
                    "subMerchantId"     => ""
                ];

                if($post->transactionType == "BE"){
                    $json["merchantTransactionId"] = $post->txnid;
                    $json['transactionAmount'] = 0;
                    $json['superMerchantId']   = $post->superMerchantId;
                }elseif($post->transactionType == "MS"){
                    $json["merchantTranId"] = $post->txnid;
                }else{
                    $json["transactionAmount"] = $post->transactionAmount;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                }

                $txndate = date('d/m/Y H:i:s');
                if($post->device == "MANTRA_PROTOBUF"){
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.$txndate,         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), true)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }else{
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.date('d/m/Y H:i:s'),         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), true)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }

                if($post->transactionType == "BE"){
                    $url = $this->aepsapi->url."fpaepsservice/api/balanceInquiry/merchant/php/getBalance";
                }elseif($post->transactionType == "MS"){
                    $url = "https://fingpayap.tapits.in/fpaepsservice/api/miniStatement/merchant/php/statement";
                }elseif($post->transactionType == "M"){
                    $url = $this->aepsapi->url."fpaepsservice/api/aadhaarPay/merchant/php/pay";
                }else{
                    $url = $this->aepsapi->url."fpaepsservice/api/cashWithdrawal/merchant/php/withdrawal";
                    
                }

                if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => "credit",
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => $bank->bankName,
                        'withdrawType'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }
                break;
        }

        $this->createFile(2, $json);
        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        //$result = \Myhelper::curl($url, 'POST', $request, $header, "no", $post);
        $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
        $this->createFile(3, $result);
        
        if($result['response'] == ''){
            switch ($post->transactionType) {
                case 'useronboardsubmit':
                    return response()->json(['status' => 'pending', 'message'=>'User onboard pending']);
                    break;

                case 'CW':
                case 'M':
                    return response()->json([
                        'status'   => 'TUP', 
                        'message'  => 'Transaction Pending',
                        'bankrrn'      => 'pending',
                        'errorMsg' => "pending",
                        "transactionType"   => $post->transactionType,
                        "title"    => ($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'ackno'       => $post->txnid,
                        'balanceamount'   => $post->transactionAmount,
                        'created_at'=> $report->created_at
                    ]);
                    break;
            }
        }

        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->status)){
                switch ($post->transactionType) {
                    case 'useronboardsubmit':
                        if($response->status == "true"){
                            Fingagent::where('id', $post->id)->update(['status' => "approved"]);
                            return response()->json(['status' => 'success', 'message'=>'User onboard successfully']);
                        }else{
                            return response()->json(['status' => 'ERR', $response->message]);
                        }
                        break;
                    
                    case 'BE':
                    case 'CW':
                    case 'MS':
                    case 'M':
                        if($response->status == true && isset($response->data) && in_array($response->data->errorCode, ['null', null])){
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
                                    }elseif($post->transactionAmount >5000 && $post->transactionAmount <=10000){
                                        $slab = "ptxn6";
                                    }

                                    $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $product, $slab);
                                    $tds = $this->getTds($profit);
                                }else{
                                    $profit = 0;
                                    $tds = 0;
                                }

                                if($post->transactionType == "CW"){
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                }elseif($post->transactionType == "M"){
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }else{
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }


                                // if($post->transactionType == "CW"){
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->increment('mainwallet', ($profit - $tds));
                                // }else{
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                //     $tds = 0;
                                // }

                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'success',
                                    'charge' => $profit,
                                    "tds"    => $tds,
                                    'refno'  => $response->data->bankRRN,
                                    'payid'  => $response->data->fpTransactionId
                                ]);

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

                            $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";
                    
                            $requestbody = [
                                [
                                    "merchantTransactionId" => $post->txnid,
                                    "fingpayTransactionId"  => $response->data->fpTransactionId,
                                    "transactionRrn"  => $response->data->bankRRN,
                                    "responseCode"    => "00",
                                    "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                                    "serviceType"     => $post->transactionType
                                ]
                            ];


                            // $headerbody = json_encode($requestbody)."";
                            
                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'trnTimestamp:'.$txndate,
                            //     'hash:'.base64_encode(hash("sha256",json_encode($headerbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:easypaisad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "yes", $post);
                            
                            // dd([
                            //     "Body"     => json_encode($requestbody),
                            //     "HashBody" => $headerbody,
                            //     "Header"   => $requestheader,
                            //     "Response" => $result
                            // ]);
                            
                            if($post->transactionType == "BE"){
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'balanceamount'  => $response->data->balanceAmount,
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Balance Enquiry",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }elseif($post->transactionType == "CW"){
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'balanceamount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'balanceamount'  => $response->data->balanceAmount,
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => $response->data->miniStatementStructureModel
                                ]);
                            }
                        }else{
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'failed',
                                    'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                    'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                ]);
                            }

                            if($post->transactionType == "BE"){
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Balance Enquiry",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }elseif($post->transactionType == "CW"){
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'balanceamount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => $response->data->miniStatementStructureModel
                                ]);
                            }
                        }
                        break;
                }
            }
        }

        if($post->transactionType == "BE"){
            return response()->json([
                'status'   => 'TUP', 
                'message'  => 'Transaction Under Process',
                'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Balance Enquiry",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'amount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }elseif($post->transactionType == "CW"){
            return response()->json([
                'status'   => 'TUP', 
                'message'  => 'Transaction Under Process',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Cash Withdrawal",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'balanceamount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }else{
            return response()->json([
                'status'   => 'ERR', 
                'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Mini Statement",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName,
                "data"     => $response->data->miniStatementStructureModel
            ]);
        }
    }

    public function check2fa(Request $post)
    {
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        $gpsdata       =  $this->usergeoip($post->user_id);   
        if(!$user){
            $output['status']  = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }
        $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();
        if (
            $data['agent']->status == 'approved' &&
            (
               
                ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d'))) ||
                ($data['agent']->ap_auth === NULL || strtotime($data['agent']->ap_auth) !== strtotime(date('Y-m-d')))
            )
        ) {
            $agent = $data['agent'];
            
            $output['status']  = "ERR";
            $output['is_aeps'] = ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d'))) ? false : true;
             $output['is_aadharpay'] = ($data['agent']->ap_auth === NULL || strtotime($data['agent']->ap_auth) !== strtotime(date('Y-m-d'))) ? false : true;
    
            $output['message'] = "Please Complete 2FA First to continue";
            if($output['is_aeps'] == $output['is_aadharpay'] && $output['is_aadharpay'] == false)
            {
                return response()->json($output);
            }
            else
            {
                $output['status']  = "TXN";
                $output['is_aeps'] = ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d'))) ? false : true;
                $output['is_aadharpay'] = ($data['agent']->ap_auth === NULL || strtotime($data['agent']->ap_auth) !== strtotime(date('Y-m-d'))) ? false : true;
    
                $output['message'] = "2FA AUthentication is present for the date";
                return response()->json($output);
            }

            
        }

            $output['status']  = "TXN";
            $output['is_aeps'] = ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d'))) ? false : true;
            $output['is_aadharpay'] = ($data['agent']->ap_auth === NULL || strtotime($data['agent']->ap_auth) !== strtotime(date('Y-m-d'))) ? false : true;
    
            $output['message'] = "2FA AUthentication is present for the date";
            return response()->json($output);
    }

    public function aepstransaction(Request $post)
    {
        $this->createFile(1, $post->all());
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        $gpsdata       =  $this->usergeoip($post->user_id);   
        if(!$user){
            $output['status']  = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('aeps_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

      

        $post['api_id']  =  $this->aepsapi->id;
        
        $post['superMerchantId'] = $this->aepsapi->option1;
        $request['transactionType'] = Request()->segment(4);
        $post['transactionType'] = Request()->segment(4);

        $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();

        
        if(Fingagent::where('user_id', $post->user_id)->count() > 0 &&  Fingagent::where('user_id', $post->user_id)->first()->status == 'approved') {
            if (
            (
                ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d')))
            )
            &&
            (
                $post->transactionType !== 'ekycsendotp' &&
                $post->transactionType !== 'ekycvalidateotp' &&
                $post->transactionType !== 'biometric'
            )
        ) {
            $post['transactionType']  =  'AUO';
            $post['auth_type']  =  'AEPS';
            
        }
        }
        
       
         
        
        switch ($post->transactionType) {
            case 'getdata':
                $data['agent'] = Fingagent::where('user_id', $post->user_id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $data]);
                break;

            case 'getbanks':
                $aepsbanks = \DB::table('fingaepsbanks')->get();
                return response()->json(['status' => 'TXN', 'message'=>'Data Fetched', "data" => $aepsbanks]);
                break;

            case 'useronboard':
    $rules = array(
        'merchantName' => 'required',
        'merchantAddress' => 'required',
        'merchantState' => 'required',
        'merchantCityName' => 'required',
        'merchantPhoneNumber' => 'required|numeric|digits:10|unique:fingagents,merchantPhoneNumber',
        'merchantAadhar' => 'required|numeric|digits:12|unique:fingagents,merchantAadhar',
        'userPan' => 'required|unique:fingagents,userPan',
        'merchantPinCode' => 'sometimes|numeric|digits:6',
        'superMerchantId' => 'required|numeric',
        'aadharPics' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
        'pancardPics' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
        'maskedAadharImages' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
        'backgroundImageOfShops' => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
        'mccCode' => 'required|numeric',
        'shopAddress' => 'required|string',     // Ensure shop address is required
        'shopCity' => 'required|string',        // Ensure shop city is required
        'shopDistrict' => 'required|string',    // Ensure shop district is required
        'shopState' => 'required|string',       // Ensure shop state is required
        'shopPincode' => 'required|numeric|digits:6', // Ensure shop pincode is required
    );

    $validator = \Validator::make($post->all(), $rules);
    if ($validator->fails()) {
        foreach ($validator->errors()->messages() as $key => $value) {
            $error = $value[0];
        }
        return response()->json(['status' => 'ERR', 'message' => $error]);
    }

    do {
        $post['merchantLoginId'] = "EPM" . rand(1111111111, 9999999999);
    } while (Fingagent::where("merchantLoginId", "=", $post->merchantLoginId)->first() instanceof Fingagent);

    do {
        $post['merchantLoginPin'] = "EPMP" . rand(111111, 999999);
    } while (Fingagent::where("merchantLoginPin", "=", $post->merchantLoginPin)->first() instanceof Fingagent);

    if ($post->hasFile('aadharPics')) {
        $post['aadharPic'] = $post->file('aadharPics')->store('fingkyc');
    }
    if ($post->hasFile('pancardPics')) {
        $post['pancardPic'] = $post->file('pancardPics')->store('fingkyc');
    }
    
    // Create the Fingagent record
    $agent = Fingagent::create($post->all());

    $user = User::where('id', $post->user_id)->first();
    $user->bank = $post->bank;
    $user->account = $post->account;
    $user->bankIfscCode = $post->ifsc;
    $user->save();

    $agent->companyBankName = $post->bank;
    $agent->bankBranchName = $post->branch;
    $agent->bankAccountName = $post->account_name;
    $agent->companyBankAccountNumber = $post->account;
    $agent->mccCode = $post->mccCode;
    $agent->ipAddress = $post->ip();
    
    // Save the agent record
    $agent->save();
    
    if ($agent) {
        return response()->json(['status' => 'TXN', 'message' => 'User onboard submitted, wait for approval']);
    } else {
        return response()->json(['status' => 'ERR', 'message' => 'Something went wrong']);
    }
    break;

                
            case 'useronboardresubmit':
    $rules = array(
        'merchantName' => 'required',
        'merchantAddress' => 'required',
        'merchantState' => 'required',
        'merchantCityName' => 'required',
        'merchantDistrictName' => 'required',
        'merchantPhoneNumber' => 'required|numeric|digits:10',
        'merchantAadhar' => 'required|numeric|digits:12',
        'userPan' => 'required',
        'merchantPinCode' => 'required|numeric|digits:6',
        'mccCode' => 'required|numeric',
        'bank' => 'required|string',            // Ensure bank field is required
        'account' => 'required|string',         // Ensure account field is required
        'ifsc' => 'required|string',            // Ensure IFSC code field is required
        'shopAddress' => 'required|string',     // Ensure shop address is required
        'shopCity' => 'required|string',        // Ensure shop city is required
        'shopDistrict' => 'required|string',    // Ensure shop district is required
        'shopState' => 'required|string',       // Ensure shop state is required
        'shopPincode' => 'required|numeric|digits:6', // Ensure shop pincode is required
        'aadharPics' => 'sometimes|mimes:jpg,jpeg,png,pdf|max:1024', // Optional
        'pancardPics' => 'sometimes|mimes:jpg,jpeg,png,pdf|max:1024', // Optional
        'maskedAadharImages' => 'sometimes|mimes:jpg,jpeg,png,pdf|max:1024', // Optional
        'backgroundImageOfShops' => 'sometimes|mimes:jpg,jpeg,png,pdf|max:1024', // Optional
    );

    $validator = \Validator::make($post->all(), $rules);
    if ($validator->fails()) {
        foreach ($validator->errors()->messages() as $key => $value) {
            $error = $value[0];
        }
        return response()->json(['status' => 'ERR', 'message' => $error]);
    }

    $agent = Fingagent::where('user_id', $post->user_id)->first();
    
    
    $agent->merchantName = $post->merchantName;
    $agent->merchantPhoneNumber = $post->merchantPhoneNumber;
    $agent->bankIfscCode = $post->ifsc;
    $agent->companyBankName = $post->bank;
    $agent->bankBranchName = $post->branch;
    $agent->bankAccountName = $post->account_name;
    $agent->companyBankAccountNumber = $post->account;
    $agent->mccCode = $post->mccCode;

    $agent->merchantAddress = $post->merchantAddress;
    $agent->merchantAddress2 = $post->merchantAddress2;
    $agent->merchantCityName = $post->merchantCityName;
    $agent->merchantDistrictName = $post->merchantDistrictName;
    $agent->merchantState = $post->merchantState;
    $agent->merchantPinCode = $post->merchantPinCode;

    $agent->shopAddress = $post->shopAddress;
    $agent->shopCity = $post->shopCity;
    $agent->shopDistrict = $post->shopDistrict;
    $agent->shopState = $post->shopState;
    $agent->shopPincode = $post->shopPincode;

    $agent->userPan = $post->userPan;
    $agent->merchantAadhar = $post->merchantAadhar;
    $agent->ipAddress = $post->ip();

    $agent->status = 'pending';
    // Add other fields as necessary
    $agent->save();

    if ($agent) {
        return response()->json(['status' => 'TXN', 'message' => 'User onboard Resubmitted, wait for approval']);
    } else {
        return response()->json(['status' => 'ERR', 'message' => 'Something went wrong']);
    }
    break;

                
                
                

            case 'useronboardsubmit':
                $rules = array(
                    'id' => 'required'
                );
                break;

            case 'ekycsendotp':
                    $rules = array(
                        'merchantPhoneNumber' => 'required',
                        'userPan' => 'required',
                        'merchantAadhar' => 'required',
                        'deviceimei' => 'required',
                    );
                    break;
    
            case 'ekycvalidateotp':
                    $rules = array(
                        'otp' => 'required',
                        'primaryKeyId' => 'required',
                        'encodeFPTxnId' => 'required',
                    );
                    break;
    
            case 'biometric':
                    $rules = array(
                        'biodata' => 'required',
                        'primaryKeyId' => 'required',
                        'encodeFPTxnId' => 'required',
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
                    'superMerchantId'   => 'required',
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
                    'superMerchantId'   => 'required',
                );
                break;

                case 'AUO':
                    $rules = array(
                        'transactionType' => 'required',
                        'auth_type' => 'required',
                        'mobileNumber'    => 'required|numeric|digits:10',
                        'adhaarNumber'    => 'required|numeric|digits:12',
                        'biodata'   => 'required',
                        'superMerchantId'   => 'required',
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

        $sessionkey = '';
        $mt_rand = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        foreach ($mt_rand as $chr)
        {             
            $sessionkey .= chr($chr);         
        }

        $iv =   '06f2f04cc530364f';
        $fp =fopen("fingpay_public_production.txt","r");
        $publickey =fread($fp,8192);         
        fclose($fp);         
        openssl_public_encrypt($sessionkey,$crypttext,$publickey);
        $gpsdata       =  $this->usergeoip($post->user_id);
        
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

            case 'ekycsendotp':
                    $agent = Fingagent::where('user_id', $post->user_id)->first();
                    if (!$agent) {
                        return response()->json(['status' => 'ERR', 'message' => 'Invalid Agent']);
                    }
    
                    if ($agent->status != "pending") {
                        //  return response()->json(['status' => 'ERR', 'message' => 'Already Onboard']);
                    }
    
                    $json =  [
                        "latitude"       => $gpsdata['latitude'],
                        "longitude"      => $gpsdata['longitude'],
                        "superMerchantId" => $this->aepsapi->option1,
                        "merchantLoginId"     => $agent->merchantLoginId,
                        "transactionType"     => 'EKY',
                        "matmSerialNumber" => $post->deviceimei,
                        "mobileNumber" => $agent->merchantPhoneNumber,
                        "aadharNumber" => $agent->merchantAadhar,
                        "panNumber" => $agent->userPan
                    ];
    
    
    
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'eskey:' . base64_encode($crypttext)
                    ];
    
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . $post->deviceimei,
                        'eskey:' . base64_encode($crypttext)
                    ];
    
    
                    $url = "https://fpekyc.tapits.in/fpekyc/api/ekyc/merchant/php/sendotp";
                    break;
            case 'ekycvalidateotp':
                    $agent = Fingagent::where('user_id', $post->user_id)->first();
                    if (!$agent) {
                        return response()->json(['status' => 'ERR', 'message' => 'Invalid Agent']);
                    }
    
                    if ($agent->status != "pending") {
                        //  return response()->json(['status' => 'ERR', 'message' => 'Already Onboard']);
                    }
    
                    $json =  [
                        "otp"       => $post->otp,
                        "primaryKeyId"       => $post->primaryKeyId,
                        "encodeFPTxnId"       => $post->encodeFPTxnId,
                        "superMerchantId" => $this->aepsapi->option1,
                        "merchantLoginId"     => $agent->merchantLoginId
                    ];
    
                    
    
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'eskey:' . base64_encode($crypttext)
                    ];
    
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . '2247I005725',
                        'eskey:' . base64_encode($crypttext)
                    ];
    
    
                    $url = "https://fpekyc.tapits.in/fpekyc/api/ekyc/merchant/php/validateotp";
                    break;
    
                
            case 'biometric':
                        $agent = Fingagent::where('user_id', $post->user_id)->first();
                        if (!$agent) {
                            return response()->json(['status' => 'ERR', 'message' => 'Invalid Agent']);
                        }
        
                        if ($agent->status != "pending") {
                            //  return response()->json(['status' => 'ERR', 'message' => 'Already Onboard']);
                        }
    
    
                    $agent = Fingagent::where('user_id', $post->user_id)->first();
                    $biodata       =  str_replace("&lt;", "<", str_replace("&gt;", ">", $post->biodata));
                    $xml           =  simplexml_load_string($biodata);
                    $skeyci        =  (string)$xml->Skey['ci'][0];
                    $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
                    if(isset($headerarray['Resp']['@attributes']['errInfo']) && !empty($headerarray['Resp']['@attributes']['errInfo'])){
                        $errInfo = $headerarray['Resp']['@attributes']['errInfo'];
                    }else{
                        $errInfo = "";
                    }
                    
                    
    
                    $json = [
                        "superMerchantId"    => $this->aepsapi->option1,
                        "merchantLoginId"  => $agent->merchantLoginId,
                        "primaryKeyId"   => $post->primaryKeyId,
                        "encodeFPTxnId"       => $post->encodeFPTxnId,
                        "requestRemarks" => "Biometric EYC",
                        "cardnumberORUID" => [
                            "nationalBankIdentificationNumber" => null, 
                            "indicatorforUID" => "0",
                            "adhaarNumber" => $post->merchantAadhar,
                        ],
                        "captureResponse" => [
                            "errCode" => $headerarray['Resp']['@attributes']['errCode'],
                            "errInfo"   =>  $errInfo,
                            "fCount"  =>  $headerarray['Resp']['@attributes']['fCount'],
                            "fType" =>  $headerarray['Resp']['@attributes']['fType'],
                            "iCount" => "0",
                            "iType" => null,
                            "pCount" => "0",
                            "pType" => "0",
                         
                            "qScore" =>  $headerarray['Resp']['@attributes']['qScore'],
                            "dpID" =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                             "rdsID" =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                             "rdsVer" =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                            "dc" =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                             "mi" =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                            "mc" =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                             "ci" =>  $skeyci,
                            "sessionKey"=>  $headerarray['Skey'],
                            "hmac" =>  $headerarray['Hmac'],
                             "PidDatatype" => "X",
                            "Piddata" =>  $headerarray['Data']
                            ]];
        
                    
        
        
        
                    
    
                        $txndate = date('d/m/Y H:i:s');
                    if ($post->device == "MANTRA_PROTOBUF") {
                        $header = [
                            'Content-Type: text/xml',
                            'trnTimestamp:' . $txndate,
                            'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                            'deviceIMEI:' . $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                            'eskey:' . base64_encode($crypttext)
                        ];
                    } elseif($post->device == "MORPHO_PROTOBUF_L1" || $post->device == "MORPHO_PROTOBUF_L1WS") {
                      
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                        'eskey:' . base64_encode($crypttext)
                    ];
                    
                } else {
                        $header = [
                            'Content-Type: text/xml',
                            'trnTimestamp:' . date('d/m/Y H:i:s'),
                            'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                            'deviceIMEI:' . $headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],
                            'eskey:' . base64_encode($crypttext)
                        ];
                        
                    }
        
        
                        $url = "https://fpekyc.tapits.in/fpekyc/api/ekyc/merchant/php/biometric";
                        break;
    
                

            case 'BE':
            case 'CW':
            case 'MS':
            case 'M':

            case 'AUO':

                $agent = Fingagent::where('user_id', $post->user_id)->first();
                if(!$agent){
                    return response()->json(['status' => 'ERR', 'message'=>'Agent Onboard Pedning']);
                }
                
                if($agent->status != "approved"){
                    return response()->json(['status' => 'ERR', 'message'=>'Agent onboarding '.$agent->status]);
                }

                $bank  = DB::table('fingaepsbanks')->where('iinno', $post->nationalBankIdentificationNumber)->first();
                if($post->has('txtPidData'))
                {
                    $post['txtPidData'] = $post->txtPidData;
                }
                else{
                    $post['txtPidData'] = $post->biodata;
                }
                $biodata       =  str_replace("&lt;","<",str_replace("&gt;",">",$post->txtPidData));
                $xml           =  simplexml_load_string($biodata);
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
                if(isset($headerarray['Resp']['@attributes']['errInfo']) && !empty($headerarray['Resp']['@attributes']['errInfo'])){
                    $errInfo = $headerarray['Resp']['@attributes']['errInfo'];
                }else{
                    $errInfo = "";
                }

                $post['auth_type'] = $post['auth_type'] ?? Request()->segment(5);

                do {
                    $post['txnid'] = "MEA".rand(1111111111, 9999999999);
                } while (Aepsreport::where("txnid", "=", $post->txnid)->first() instanceof Aepsreport);
                $json =  [
                    "captureResponse" => [
                        "PidDatatype" =>  "X",
                        "Piddata"     =>  $headerarray['Data'],
                        "ci"          =>  $skeyci,
                        "dc"          =>  $headerarray['DeviceInfo']['@attributes']['dc'],
                        "dpID"        =>  $headerarray['DeviceInfo']['@attributes']['dpId'],
                        "errCode"     =>  $headerarray['Resp']['@attributes']['errCode'],
                        "errInfo"     =>  $errInfo,
                        "fCount"      =>  $headerarray['Resp']['@attributes']['fCount'],
                        "fType"       =>  $headerarray['Resp']['@attributes']['fType'],
                        "hmac"        =>  $headerarray['Hmac'],
                        "iCount"      =>  "0",
                        "mc"          =>  $headerarray['DeviceInfo']['@attributes']['mc'],
                        "mi"          =>  $headerarray['DeviceInfo']['@attributes']['mi'],
                        "nmPoints"    =>  $headerarray['Resp']['@attributes']['nmPoints'],
                        "pCount"      =>  "0",
                        "pType"       =>  "0",
                        "qScore"      =>  $headerarray['Resp']['@attributes']['qScore'],
                        "rdsID"       =>  $headerarray['DeviceInfo']['@attributes']['rdsId'],
                        "rdsVer"      =>  $headerarray['DeviceInfo']['@attributes']['rdsVer'],
                        "sessionKey"  =>  $headerarray['Skey']
                    ],
                    
                    "cardnumberORUID"       => [
                        'adhaarNumber'      => $post->adhaarNumber,
                        "indicatorforUID"   => "0",
                        "nationalBankIdentificationNumber" => ($post->transactionType == 'AUO') ? null : $bank->iinno,

                    ],
                    "languageCode"   => "en",
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "mobileNumber"   => $post->mobileNumber,
                    "paymentType"    => "B",
                    "requestRemarks" => "Aeps", 
                    "timestamp"      => Carbon::now()->format('d/m/Y H:i:s'),
                    "transactionType"   => $post->transactionType,
                    "merchantUserName"  => $agent->merchantLoginId,
                    "merchantPin"       => md5($agent->merchantLoginPin),               
                    "subMerchantId"     => ""
                ];

                if($post->transactionType == "BE"){
                    $json["merchantTransactionId"] = $post->txnid;
                    $json['transactionAmount'] = 0;
                    $json['superMerchantId']   = $post->superMerchantId;
                }elseif($post->transactionType == "MS"){
                    $json["merchantTranId"] = $post->txnid;
                } elseif ($post->transactionType == "AUO") {
                    $json["serviceType"] =  $post->auth_type;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                } else{
                    $json["transactionAmount"] = $post->transactionAmount;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                }

                $txndate = date('d/m/Y H:i:s');
                if($post->device == "MANTRA_PROTOBUF"){
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.$txndate,         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), true)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }else{
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.date('d/m/Y H:i:s'),         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), true)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }

                if($post->transactionType == "BE"){
                    $url = $this->aepsapi->url."fpaepsservice/api/balanceInquiry/merchant/php/getBalance";
                }elseif($post->transactionType == "MS"){
                    $url = "https://fingpayap.tapits.in/fpaepsservice/api/miniStatement/merchant/php/statement";
                }elseif($post->transactionType == "M"){
                    $url = $this->aepsapi->url."fpaepsservice/api/aadhaarPay/merchant/php/pay";
                }elseif ($post->transactionType == "AUO") {
                    $url = $this->aepsapi->url . "fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fpuat.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";

                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                } else{
                    $url = $this->aepsapi->url."fpaepsservice/api/cashWithdrawal/merchant/php/withdrawal";
                    
                }

                if($post->transactionType == "CW" || $post->transactionType == "M"){
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => "credit",
                        'api_id'  => $this->aepsapi->id,
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        "bank"        => $bank->bankName,
                        'withdrawType'=> $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }
                break;
        }

        $this->createFile(2, $json);
        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
        $this->createFile(3, $result);
        
        if($result['response'] == ''){
            switch ($post->transactionType) {
                case 'useronboardsubmit':
                    return response()->json(['status' => 'pending', 'message'=>'User onboard pending']);
                    break;

                

                case 'AUO':

                        if ($post->auth_type == "AEPS") {
                            Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                        } else {
                            Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                        }
                        return response()->json(['status' => 'success', 'transactionType' => 'AUO', 'message' =>  ' ' . $post->auth_type . ' Authentication  Successfull']);
                        break;

                case 'CW':
                case 'M':
                    return response()->json([
                        'status'   => 'TUP', 
                        'message'  => 'Transaction Pending',
                        'bankrrn'      => 'pending',
                        'errorMsg' => "pending",
                        "transactionType"   => $post->transactionType,
                        "title"    => ($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay",
                        'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                        'ackno'       => $post->txnid,
                        'balanceamount'   => $post->transactionAmount,
                        'created_at'=> $report->created_at
                    ]);
                    break;
            }
        }

        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->status)){
                switch ($post->transactionType) {
                    case 'useronboardsubmit':
                        if($response->status == "true"){
                            Fingagent::where('id', $post->id)->update(['status' => "approved"]);
                            return response()->json(['status' => 'success', 'message'=>'User onboard successfully']);
                        }else{
                            return response()->json(['status' => 'ERR', $response->message]);
                        }
                        break;

                    

                    case 'ekycsendotp':
                            if ($response->status == "true") {
                                
                                return response()->json(['status' => 'TXNOTP', 'txntype' => 'ekycsendotp', 'message' => 'OTP Sent Successfully', 'data' => $response->data]);
                            } else {
                                if($response->statusCode == '10029')
                                {
                                    Fingagent::where('user_id', $post->user_id)->update(['ekyc' => '1']);
                                }
                                return response()->json(['status' => 'ERR', 'message' => $response->message]);
                            }
                            break;
    
    
    
                    case 'ekycvalidateotp':
                            if ($response->status == "true") {
                                return response()->json(['status' => 'success',  'txntype' => 'ekycvalidateotp',  'message' => $response->message, 'data' => $response->data]);
                            } else {
                                return response()->json(['status' => 'ERR', 'message' => $response->message]);
                            }
                            break;

                    case 'biometric':
                                if ($response->status == "true") {
                                    Fingagent::where('user_id', $post->user_id)->update(['ekyc' => '1']);
                                    return response()->json(['status' => 'success',  'txntype' => 'biometric',  'message' => $response->message, 'data' => $response->data]);
                                } else {
                                    return response()->json(['status' => 'ERR', 'message' => $response->message]);
                                }
                                break;

                        case 'AUO':

                            if ($post->auth_type == "AEPS") {
                                Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                            } else {
                                Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                            }
                            return response()->json(['status' => 'pending', 'message' => $post->auth_type . 'Authentication  Successfull']);
                            break;
                    
                    case 'BE':
                    case 'CW':
                    case 'MS':
                    case 'M':
                        if($response->status == true && isset($response->data) && in_array($response->data->errorCode, ['null', null])){
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
                                    }elseif($post->transactionAmount >5000 && $post->transactionAmount <=10000){
                                        $slab = "ptxn6";
                                    }

                                    $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $product, $slab);
                                    $tds = $this->getTds($profit);
                                }else{
                                    $profit = 0;
                                    $tds = 0;
                                }


                                if($post->transactionType == "CW"){
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                }elseif($post->transactionType == "M"){
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }else{
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }

                                // if($post->transactionType == "CW"){
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit - $tds);
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->increment('mainwallet', ($profit - $tds));
                                // }else{
                                //     User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                //     User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    
                                //     //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                //     $tds = 0;
                                // }

                                // Aepsreport::where('id', $report->id)->update([
                                //     'status' => 'success',
                                //     'charge' => $profit,
                                //     "tds"    => $tds,
                                //     'refno'  => $response->data->bankRRN,
                                //     'payid'  => $response->data->fpTransactionId
                                // ]);

                                $insert = [
                                    'number'  => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'mobile'  => $post->mobileNumber,
                                    'provider_id' => '87',
                                    'api_id'  => $this->aepsapi->id,
                                    'txnid'   => $post->txnid,
                                    'amount'  => $post->transactionAmount,
                                    "user_id" => $post->user_id,
                                    "balance" => $user->mainwallet,
                                    'payid'   => $response->data->fpTransactionId,
                                    'refno'   => $response->data->bankRRN,
                                    'status'  => 'success',
                                    'rtype'   => 'commission',
                                    'trans_type' => "credit",
                                    'via'     => "portal",
                                    'product' => "aeps"
                                ];

                                Report::create($insert);




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

                            $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";
                    
                            $requestbody = [
                                [
                                    "merchantTransactionId" => $post->txnid,
                                    "fingpayTransactionId"  => $response->data->fpTransactionId,
                                    "transactionRrn"  => $response->data->bankRRN,
                                    "responseCode"    => "00",
                                    "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                                    "serviceType"     => $post->transactionType
                                ]
                            ];


                            // $headerbody = json_encode($requestbody)."";
                            
                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'trnTimestamp:'.$txndate,
                            //     'hash:'.base64_encode(hash("sha256",json_encode($headerbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:easypaisad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "yes", $post);
                            
                            // dd([
                            //     "Body"     => json_encode($requestbody),
                            //     "HashBody" => $headerbody,
                            //     "Header"   => $requestheader,
                            //     "Response" => $result
                            // ]);
                            
                            if($post->transactionType == "BE"){
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'balanceamount'  => $response->data->balanceAmount,
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Balance Enquiry",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }elseif($post->transactionType == "CW"){
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'balanceamount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                return response()->json([
                                    'status'   => 'TXN', 
                                    'message'  => 'Transaction Successfull',
                                    'balanceamount'  => $response->data->balanceAmount,
                                    'bankrrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => $response->data->miniStatementStructureModel
                                ]);
                            }
                        }else{
                            if($post->transactionType == "CW" || $post->transactionType == "M"){
                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'failed',
                                    'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                    'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                ]);
                            }

                            if($post->transactionType == "BE"){
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Balance Enquiry",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }elseif($post->transactionType == "CW"){
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'balanceamount'   => $post->transactionAmount,
                                    'created_at'=> isset($report->created_at)?$report->created_at: date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            }else{
                                return response()->json([
                                    'status'   => 'ERR', 
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                                    'ackno'       => $post->txnid,
                                    'created_at'=> date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => $response->data->miniStatementStructureModel
                                ]);
                            }
                        }
                        break;
                }
            }
        }

        if($post->transactionType == "BE"){
            return response()->json([
                'status'   => 'TUP', 
                'message'  => 'Transaction Under Process',
                'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Balance Enquiry",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'amount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }elseif($post->transactionType == "CW"){
            return response()->json([
                'status'   => 'TUP', 
                'message'  => 'Transaction Under Process',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Cash Withdrawal",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'balanceamount'   => $post->transactionAmount,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        }else{
            return response()->json([
                'status'   => 'ERR', 
                'balanceamount'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'bankrrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Mini Statement",
                'aadhar'   => "XXXXXXXX".substr($post->adhaarNumber, -4),
                'ackno'       => $post->txnid,
                'created_at'=> date('d M Y H:i'),
                'bank'     => $bank->bankName,
                "data"     => $response->data->miniStatementStructureModel
            ]);
        }
    }
    public function matmstatus(Request $post) {
      
    $gpsdata = $this->usergeoip($post->user_id);
    
    $rules = array( 
        'apptoken' => 'required',
        'user_id'  => 'required|numeric',
        'txnid'    => 'required'
    );

    $validate = \Myhelper::FormValidator($rules, $post);
    if($validate != "no") {
        return $validate;
    }

    $user = User::where('id', $post->user_id)->where('apptoken', $post->apptoken)->first();
    
    if(!$user) {
        $output['status']  = "ERR";
        $output['message'] = "User details not matched";
        return response()->json($output);
    }

    if (!\Myhelper::can('aeps_service', $user->id)) {
        return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
    }
    
    $report = Aepsreport::where('txnid', $post->txnid)->first();
    if(!$report) {
        return response()->json(['status' => "ERR", "message" => "Report not found"]);
    }

    if($report->status != 'initiated') {
        return response()->json([
            'status'      => 'TXN', 
            'matm_status' => $report->status, 
            'message'     => $report->remark,
            'data'     => $report,
        ]);
    }

    $agent = Fingagent::where('user_id', $post->user_id)->first();
    if(!$agent) {
        return response()->json(['status' => "ERR", "message" => "Agent not found"]);
    }

    // Define request parameters
    $merchantTranId        = $post->txnid;
    $merchantLoginId       = $agent->merchantLoginId;
    $merchantPassword      = $agent->merchantLoginPin;
    $superMerchantId       = 955;
    $superMerchantPassword = "1234d";

    // Concatenate the string and convert to lowercase
    $concatenated_string = $merchantTranId . strtolower($merchantLoginId . $superMerchantId);

    // Generate the hash using SHA256 and base64 encode
    $hash = base64_encode(hash('sha256', $concatenated_string, true));

    // Create request data
    $requestData = [
        'merchantTranId'       => $merchantTranId,
        'merchantLoginId'      => $merchantLoginId,
        'merchantPassword'     => $merchantPassword,
        'superMerchantId'      => $superMerchantId,
        'superMerchantPassword'=> $superMerchantPassword,
        'hash'                 => $hash
    ];

    // Convert request data to JSON
    $jsonData = json_encode($requestData);

    // API URL
    $url = 'https://fpma.tapits.in/fpcardwebservice/api/ma/statuscheck/cw';

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json')
    );

    // Execute cURL session
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return response()->json(['status' => 'ERR', 'message' => "cURL Error: $error"]);
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $response = json_decode($response);
 $profit = 0;
            $tds = 0;
    if ($response && isset($response->status) && $response->status == true) {
       
                if($report->amount > 0){
            $provider = Provider::where('min_amount', '<=', $report->amount)->where('max_amount', '>=', $report->amount)->where('type', 'matm')->first();
            $product = "matm";
            $profit = \Myhelper::getCommission($report->amount, $user->scheme_id, $provider->id, $user->role->slug);
            $tds = $this->getTds($profit);
        }else{
            $profit = 0;
            $tds = 0;
        }
            




            $insert = [
            "mobile"  => $report->mobile,
            "provider_id" => "101",
            "txnid"   => $report->txnid,
            "amount"  => $report->amount, 
            "user_id" => $report->user_id,
            "balance" => $user->mainwallet,
            'refno'  => isset($response->data->fingpayTransactionId)?$report->data->fingpayTransactionId:'',
            'payid'  => isset($response->data->bankRRN)?$report->data->bankRRN:'',
            'remark'   => "Request Completed Successfuly",
            'number'  =>  isset($response->data->cardNumber)?$report->data->cardNumber:'',
            'option1'=>  isset($response->data->bankRRN)?$report->data->bankRRN:'',
            'trans_type'    => "credit",
            'api_id'  => '9',
            'credit_by' => $post->user_id,
            'charge' => $profit,
            "tds"    => $tds,
            "status" => "success",
            'rtype'       => 'commission',
            'option2'   => 'transaction',
            'product'   => 'matm',
            "option3"        => isset($response->data->cardNumber)?$report->data->cardNumber:'',
            'option4'=> ''
        ];
        $report_1 = Report::create($insert);
        $dd = Aepsreport::where('id', $report->id)->update([
                'charge' => $profit,
                "tds"    => $tds,
                "status" => "success"
            ]);

            
                User::where('id', $user->id)->increment('aepsbalance', $report->amount);
                User::where('id', $user->id)->increment('mainwallet', ($profit - $tds));
                try {
                    $aepsreport = Aepsreport::where('id', $report->id)->first();
                    \Myhelper::commission($aepsreport, $slab, "aeps");
                } catch (\Exception $e) {}
                
                $output['status']  = "TXN";
                $output['matm_status']   = 'success';
                $output['data']     = $report;
                $output['message'] = "Transaction Successfully";
                return response()->json($output);
            
        
            
    } else {
        // Handle failure case
         $dd = Aepsreport::where('id', $report->id)->update([
                'charge' => 0,
                "tds"    => 0,
                "status" => "failed"
            ]);
        $errorMessage = isset($response->message) ? $response->message : "Unknown error";
        return response()->json([
            'status'      => 'TXN',
            'matm_status' => 'failed',
            'data'     => $report,
            'message'     => $errorMessage,
        ]);
    }
}


    public function matminitiate(Request $post){
        
        
        $rules = array( 
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
            'mobileNumber' => 'required',
            'txnType' => 'required',
            'amount' => '', // No specific validation rule mentioned, assuming it's optional.
            'remarks' => '', // No specific validation rule mentioned, assuming it's optional.
            'imeiNumber' => '', // No specific validation rule mentioned, assuming it's optional.
            'latitude' => '', // No specific validation rule mentioned, assuming it's optional.
            'longitude' => '', // No specific validation rule mentioned, assuming it's optional.
        );
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no") {
            return $validate;
        }
        
        if (!\Myhelper::service_active('matm_service')) {
            
             $output['status']  = "ERR";
            $output['message'] = "Service Currently Deactive";
            return response()->json($output);
            
            
        }
        $gpsdata       =  $this->usergeoip($post->user_id);
        
        
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        
        if(!$user){
            $output['status']  = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('aeps_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

        do {
            $post['txnid'] = "MEA".rand(1111111111, 9999999999);
        } while (Aepsreport::where("txnid", "=", $post->txnid)->first() instanceof Aepsreport);


       


        if($post->transactionAmount > 99){
           

            $provider = Provider::where('min_amount', '<=', $post->transactionAmount )->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aeps')->first();
            //$post['provider_id'] = $provider->id;
            $product = "aeps";
            $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
            $tds = $this->getTds($profit);
        }else{
            $profit = 0;
            $tds = 0;
        }

        
            

        

        






        $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX".substr($post->mobileNumber, -4),
                        "txnid"   => $post->txnid,
                        "amount"  => $post->transactionAmount ?? 0,
                        "user_id" => $post->user_id,
                        "balance" => $user->aepsbalance,
                        'type'    => "credit",
                        'api_id'  => '9',
                        'credited_by' => $post->user_id,
                        'status'      => 'initiated',
                        'rtype'       => 'main',
                        'transtype'   => 'transaction',
                        'product'   => 'matm',
                        "bank"        => $post->bank,
                        'withdrawType'=> $post->txnType,
                        'aepstype'=> $post->txnType,
                    ];
     
                    $report = Aepsreport::create($insert);   

                    $dd = Aepsreport::where('id', $report->id)->update([
                        'charge' => $profit,
                        "tds"    => $tds
                    ]);
                    
        if($report){
            return response()->json([
                'status'   => 'TXN', 
                'txnid'   => $report->txnid, 
                'message'  => 'Transaction Under Process',
                'created_at'=> date('d M Y H:i')
            ]);
        }else{
            return response()->json([
                'status'   => 'ERR', 
                'txnid'   => null, 
                'message'  => 'Transaction Could not Process',
                'created_at'=> date('d M Y H:i')
            ]);
        }
        
    }

    public function matmstatusupdate(Request $post)
    {
        $this->createFile("1matm_log".rand(0000, 99999), $post->all());
        \DB::table('microlog')->insert(['response' => "fing".json_encode($post->all()), 'product' => $post->txnid]);
        $rules = array( 
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
            'txnid'   => 'required'
        );
        $gpsdata       =  $this->usergeoip($post->user_id);
        
        //dd($post->all()); exit;

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        // if (!\Myhelper::can('matm_service', $post->user_id)) {
        //     return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        // }
            
        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        
        if(!$user){
            $output['status']  = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }
        $response = json_decode($post->response, true);
        
        $rules = array(
            'status'    => 'required'
        );

        $validator = \Validator::make((array)$response, array_reverse($rules));
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(array(
                'status' => 'ERR',  
                'message' => $error
            ));
        }

        if($response['reqTransAmount'] > 0){
            $provider = Provider::where('min_amount', '<=', $response['reqTransAmount'])->where('max_amount', '>=', $response['reqTransAmount'])->where('type', 'matm')->first();
            $product = "matm";
            $profit = \Myhelper::getCommission($response['reqTransAmount'], $user->scheme_id, $provider->id, $user->role->slug);
            $tds = $this->getTds($profit);
        }else{
            $profit = 0;
            $tds = 0;
        }
        
        $report = Aepsreport::where('txnid', $post->txnid)->first();
    if(!$report) {
        $insert = [
            "mobile"  => $post->mobileNumber,
            "txnid"   => $post->txnid,
            "amount"  => isset($response['reqTransAmount'])?$response['reqTransAmount']:$response['reqTransAmount'],
            "user_id" => $post->user_id,
            "balance" => $user->aepsbalance,
            'refno'  => isset($response['terminalId'])?$response['terminalId']:'',
            'payid'  => isset($response['bankRrn'])?$response['bankRrn']:'',
            'remark'   => "Request Completed Successfuly",
            'aadhar'  => isset($response['cardNum'])?$response['cardNum']:'',
            'authcode'=> isset($response['bankName'])?$response['bankName']:'',
            'type'    => "credit",
            'api_id'  => '9',
            'credited_by' => $post->user_id,
            'status'      => 'initiated',
            'rtype'       => 'main',
            'transtype'   => 'transaction',
            'product'   => 'matm',
            "bank"        => isset($response['bankName'])?$response['bankName']:'',
            'withdrawType'=> $post->transactionType,
            'aepstype'=> $post->transactionType
        ];
        $report = Aepsreport::create($insert); 
    }
        
        if($response['status'] == "true" && $post->transactionType == "CW"){
            // $update['status'] = "success";
            // $credit = Aepsreport::where('id', $report->id)->update($update);
            




            $insert = [
            "mobile"  => $post->mobileNumber,
            "provider_id" => "101",
            "txnid"   => $post->txnid,
            "amount"  => $report->amount, //isset($response['reqTransAmount'])?$response['reqTransAmount']:$response['reqTransAmount'],
            "user_id" => $post->user_id,
            "balance" => $user->mainwallet,
            'refno'  => isset($response['terminalId'])?$response['terminalId']:'',
            'payid'  => isset($response['bankRrn'])?$response['bankRrn']:'',
            'remark'   => "Request Completed Successfuly",
            'number'  => isset($response['cardNum'])?$response['cardNum']:'',
            'option1'=> isset($response['bankName'])?$response['bankName']:'',
            'trans_type'    => "credit",
            'api_id'  => '9',
            'credit_by' => $post->user_id,
            'charge' => $profit,
            "tds"    => $tds,
            "status" => "success",
            'rtype'       => 'commission',
            'option2'   => 'transaction',
            'product'   => 'matm',
            "option3"        => isset($response['bankName'])?$response['bankName']:'',
            'option4'=> $post->transactionType
        ];
        $report_1 = Report::create($insert);







            $dd = Aepsreport::where('id', $report->id)->update([
                'charge' => $profit,
                "tds"    => $tds,
                "status" => "success",
                'aepstype' => $post->transactionType,
                'withdrawType' => $post->transactionType
            ]);

            if($dd){
                User::where('id', $user->id)->increment('aepsbalance', $report->amount);
                User::where('id', $user->id)->increment('mainwallet', ($profit - $tds));
                try {
                    $aepsreport = Aepsreport::where('id', $report->id)->first();
                    \Myhelper::commission($aepsreport, $slab, "aeps");
                } catch (\Exception $e) {}
                
                $output['status']  = "TXN";
                $output['message'] = "Transaction Successfully";
                return response()->json($output);
            }else{
                $output['status']  = "TXN";
                $output['message'] = "Technical Error";
                $output['created_at'] = date('d M Y H:i');
                return response()->json($output);
            }
        }elseif($response['status'] == "true" && $post->transactionType == "BE"){
                $output['status']  = "TXN";
                $output['message'] = "Transaction Successfully";
                return response()->json($output);
        }elseif($response['status'] == "false"){
            //print_r($response); exit;
            $update['status'] = "failed";
            
            $update['charge']  = $profit;
            $update['tds']  = $tds;
            $update['amount']  = isset($response['reqTransAmount'])?$response['reqTransAmount']:$response['reqTransAmount'];
            $update['refno']  = isset($response['terminalId'])?$response['terminalId']:'';
            $update['payid']  = isset($response['bankRrn'])?$response['bankRrn']:'';
            $update['remark']  = isset($response['message']) ? $response['message'] : 'Request Could not completed';
            Aepsreport::where('id', $report->id)->update($update);
            
            $output['status']  = "TXN";
            $output['message'] = $response['message'];
            $output['created_at'] = date('d M Y H:i');
            return response()->json($output);
        }else{
            $update['status'] = "failed";
            $update['charge']  = $profit;
            $update['tds']  = $tds;
            $update['amount']  = isset($response['reqTransAmount'])?$response['reqTransAmount']:$response['reqTransAmount'];
            $update['refno']  = isset($response['terminalId'])?$response['terminalId']:'';
            $update['payid']  = isset($response['bankRrn'])?$response['bankRrn']:'';
            $update['remark']  = isset($response['message']) ? $response['message'] : 'Request Could not completed';
            Aepsreport::where('id', $report->id)->update($update);
            
            $output['status']  = "TXN";
            $output['message'] = $response['message'];
            $output['created_at'] = date('d M Y H:i');

            return response()->json($output);
        }
    }

    // public function matmstatusupdate(Request $post){

    //     $this->createFile(1, $post->all());
    //     $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        
    //     if(!$user){
    //         $output['status']  = "ERR";
    //         $output['message'] = "User details not matched";
    //         return response()->json($output);
    //     }

    //     if (!\Myhelper::can('aeps_service', $user->id)) {
    //         return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
    //     }

        





    //     if($post->transactionType == "CW"){
    //         User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit - $tds);
    //     }else{
    //         User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
    //         $tds = 0;
    //     }



    //     $matmtxn = Aepsreport::where('txnid', $post->txnid)->update(['status' => $post->status, 'payid' => $post->payid, 'refno' => $post->refno, 'remark' => $post->remark, 'bank' => $post->bank, 'accbalance' => $post->accbalance, 'aadhar' => "XXXXXXXX".substr($post->adhaarNumber, -4)]);        

    //     //$profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $product, $slab);
    //       //                          $tds = $this->getTds($profit);











    //     if($matmtxn){
    //         return response()->json([
    //             'status'   => 'TXN', 
    //             'message'  => 'Transaction Status Updated',
    //             'created_at'=> date('d M Y H:i')
    //         ]);
    //     }else{
    //         return response()->json([
    //             'status'   => 'ERR', 
    //             'message'  => 'Transaction Could not Found',
    //             'created_at'=> date('d M Y H:i')
    //         ]);
    //     }
        
    // }

    public function getTds($amount)
    {
        return round($amount * 5/100, 2);
    }

    public function getGst($amount)
    {
        $gst = 0;//5;//\Auth::user()->gst;
        return round($amount * $gst/100, 2);
    }
}