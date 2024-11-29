<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use App\User;
use App\Model\Circle;
use App\Model\Report;
use App\Model\Api;
use App\Model\Aepsdata;
use App\Model\Aepsfundrequest;
use App\Model\Aepsreport;
use App\Model\Fingagent;
use App\Model\Setting;
use Illuminate\Validation\Rule;

class FingaepsController extends Controller
{
    public $aepsapi;
    public function __construct()
    {
        $this->aepsapi = Api::where('code', 'aeps')->first();
        //$this->matmapi = Api::where('code', 'matm')->first();
       
    }

    public function index($type)
    {

        switch ($type) {
            case 'aeps':
                if(\Myhelper::hasRole('admin') || !\Myhelper::can('aeps_service')){
                    abort(401);
                }
                $data['agent'] = Fingagent::where('user_id', \Auth::id())->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                $data['company'] = \Auth::user()->company;
                $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
                return view('service.fingaeps')->with($data);

            break;
            default: 
                ###code
            break;
        }
        
    }

    

    public function geoip($ip){
        $geoIP  = json_decode(file_get_contents("http://api.ipstack.com/$ip?access_key=49003c05bb4d851383468c238587d71c&format=1"), true);
        return $geoIP;
    }

    public function initiate(Request $post)
    {
        $post['user_id'] = \Auth::id();
        $post['superMerchantId'] = $this->aepsapi->option1;
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
        $gpsdata       =  $this->geoip($post->ip());
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
                //dd($json); exit;
                $header = [         
                    'Content-Type: text/xml',             
                    'trnTimestamp:'.date('d/m/Y H:i:s'),         
                    'hash:'.base64_encode(hash("sha256",json_encode($json), True)),   
                    'eskey:'.base64_encode($crypttext)         
                ];

                $url = $this->aepsapi->url."fpaepsweb/api/onboarding/merchant/creation/php/m1";
                //dd($url); exit;
                break;

            case 'BE':
            case 'CW':
            case 'MS':
            case 'M':
                $bank  = \DB::table('fingaepsbanks')->where('iinno', $post->nationalBankIdentificationNumber)->first();
                $agent = Fingagent::where('user_id', \Auth::id())->first();
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
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
                        'deviceIMEI:'.$headerarray['DeviceInfo']['additional_info']['Param'][0]['@attributes']['value'],         
                        'eskey:'.base64_encode($crypttext)         
                    ];
                }else{
                    $header = [         
                        'Content-Type: text/xml',             
                        'trnTimestamp:'.date('d/m/Y H:i:s'),         
                        'hash:'.base64_encode(hash("sha256",json_encode($json), True)),         
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

        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options=OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        $result = \Myhelper::curl($url, 'POST', $request, $header, "no", $post);
        //dd($result); exit;
        //dd([$url, $json, $result]);
        // $result['response'] = json_encode([
        //     "status" => true,
        //     "message" => "Request Completed",
        //     "data" => [
        //         "terminalId" => "FA315285",
        //         "requestTransactionTime" => "26/04/2020 18:22:47",
        //         "transactionAmount" => 0.0,
        //         "transactionStatus" => "successful",
        //         "balanceAmount" => 300.0,
        //         "bankRRN" => "011718380587",
        //         "transactionType" => "MS",
        //         "fpTransactionId" => "MSBC0529572260420182247583I",
        //         "merchantTxnId" => "MEA8964842393",
        //         "errorCode" => null,
        //         "errorMessage" => null,
        //         "merchantTransactionId" => null,
        //         "bankAccountNumber" => null,
        //         "ifscCode" => null,
        //         "bcName" => null,
        //         "transactionTime" => null,
        //         "agentId" => 0,
        //         "issuerBank" => null,
        //         "customerAadhaarNumber" => null,
        //         "customerName" => null,
        //         "stan" => null,
        //         "rrn" => null,
        //         "uidaiAuthCode" => null,
        //         "bcLocation" => null,
        //         "demandSheetId" => null,
        //         "mobileNumber" => null,
        //         "urnId" => null,
        //         "miniStatementStructureModel" => [["date" => "23/04",
        //         "txnType" => "Cr",
        //         "amount" => "300.0",
        //         "narration" => " MAT/F/585109     "]],
        //         "miniOffusStatementStructureModel" => null,
        //         "miniOffusFlag" => false,
        //         "transactionRemark" => null,
        //         "bankName" => null,
        //         "prospectNumber" => null,
        //         "internalReferenceNumber" => null,
        //         "biTxnType" => null,
        //         "subVillageName" => null,
        //         "userProfileResponseModel" => null,
        //         "hindiErrorMessage" => null,
        //         "loanAccNo" => null
        //     ],
        //     "statusCode" => 10000
        // ]);

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

                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'success',
                                    'charge' => $profit,
                                    "tds"    => $tds,
                                    "withdrawType" => $post->transactionType,
                                    'refno'  => $response->data->bankRRN,
                                    'payid'  => $response->data->fpTransactionId
                                ]);

                                if($post->transacg129tionType == "M"){
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
                                return response()->json([
                                    'status'   => 'success', 
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
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

    public function getTds($amount)
    {
        return round($amount * 5/100, 2);
    }
}
