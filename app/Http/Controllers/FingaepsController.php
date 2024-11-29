<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use App\User;
use App\Model\Circle;
use App\Model\Report;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Aepsfundrequest;
use App\Model\Aepsreport;
use App\Model\Fingagent;
use App\Model\Setting;
use Illuminate\Validation\Rule;
use App\Model\PortalSetting;
use DB;

class FingaepsController extends Controller
{
    public $aepsapi;
    public function __construct()
    {
        $this->aepsapi = Api::where('code', 'aeps')->first();
    }

    public function index()
    {

        if (!\Myhelper::service_active('aeps_service')) {

            return  redirect()->back()->with('error', 'Service Currently Deactive');
        }
        if ($this->companyPermission('aeps_service')) {
            return abort(401);
        }
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();

        $data['agent'] = Fingagent::where('user_id', \Auth::id())->first();
        $data['aepsbanks'] = \DB::table('fingaepsbanks')->orderBy('bankName', 'ASC')->get();

        $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
        $data['state'] = \DB::table('fingstate')->get();
        $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
        //  dd($data); exit;

        if (Fingagent::where('user_id', \Auth::id())->count() == 0) {
            return view('service.fingaeps')->with($data);
        }
        
        if (Fingagent::where('user_id', \Auth::id())->where('status','rejected')->count() > 0) {
            return view('service.fingaeps')->with($data);
        }
       
        if (Fingagent::where('user_id', \Auth::id())->where('ekyc',NULL)->count() > 0) {
            return view('service.fingaepsekyc')->with($data);
        }


        if (
            $data['agent']->status == 'approved' &&
            (
                ($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d'))))
                
        ) {
            return view('service.fing2fa')->with($data);
        }

        if(request()->segment(2) == 'ap2fa'){

            return view('service.fing2fa')->with($data);
        }
        if(request()->segment(2) == 'cw2fa'){

            return view('service.fing2fa')->with($data);
        }

        return view('service.fingaeps')->with($data);
    }

    public function geoip($ip)
    {

        $lat = \Auth::user()->lat;
        $long = \Auth::user()->long;

        //$geoIP = array('latitude' => \Auth::user()->lat, 'longitude' => \Auth::user()->long);
        
        $geoIP = array('latitude' => number_format($lat, 7, '.', ''), 'longitude' => number_format($long, 7, '.', ''));


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
                    'aadharPics'   => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
                    'pancardPics'  => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
                    'maskedAadharImages'   => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
                    'backgroundImageOfShops'  => 'required|mimes:jpg,jpeg,png,pdf|max:1024',
                    'mccCode'   => 'required|numeric',
                );
                
               

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status' => 'ERR', 'message' => $error]);
                }

                do {
                    $post['merchantLoginId']  = "DIGI" . rand(1111111111, 9999999999);
                } while (Fingagent::where("merchantLoginId", "=", $post->merchantLoginId)->first() instanceof Fingagent);

                do {
                    $post['merchantLoginPin'] = "DIGI" . rand(111111, 999999);
                } while (Fingagent::where("merchantLoginPin", "=", $post->merchantLoginPin)->first() instanceof Fingagent);

                if ($post->hasFile('aadharPics')) {
                    $post['aadharPic'] = $post->file('aadharPics')->store('fingkyc');
                }
                if ($post->hasFile('pancardPics')) {
                    $post['pancardPic'] = $post->file('pancardPics')->store('fingkyc');
                }
                if ($post->hasFile('maskedAadharImages')) {
                    $post['maskedAadharImage'] = $post->file('maskedAadharImages')->store('fingkyc');
                }
                if ($post->hasFile('backgroundImageOfShops')) {
                    $post['backgroundImageOfShop'] = $post->file('backgroundImageOfShops')->store('fingkyc');
                }
                
               

               $agent = new Fingagent($post->all());

    // Set additional fields
    
    $agent->companyBankName = $post->bank;
                $agent->bankIfscCode = $post->ifsc;
    $agent->bankBranchName = $post->branch;
    $agent->bankAccountName = $post->account_name;
    $agent->companyBankAccountNumber = $post->account;
    $agent->mccCode = $post->mccCode;
    $agent->ipAddress = $post->ip();
    $agent->save();

                $user = \Auth::user();
                $user->bank = $post->bank;
                $user->account = $post->account;
                $user->ifsc = $post->ifsc;
                                $user->save();
                



                if ($agent) {

                    return response()->json(['status' => 'TXN', 'message' => 'User onboard submitted, wait for approval']);
                } else {
                    return response()->json(['status' => 'ERR', 'message' => 'Something went wrong']);
                }
                break;
                
            case 'useronboardresubmit':
    // Define validation rules
    $rules = array(
        'merchantName' => 'required',
        'merchantAddress' => 'required',
        'merchantState' => 'required',
        'merchantCityName' => 'required',
        'merchantPhoneNumber' => 'required|numeric|digits:10',
        'merchantAadhar' => 'required|numeric|digits:12',
        'userPan' => 'required',
        'merchantPinCode' => 'required|numeric|digits:6',
        'mccCode' => 'required|numeric',
        'bank' => 'required|string',            // Ensure bank field is required
        'account' => 'required|string',         // Ensure account field is required
        'ifsc' => 'required|string',            // Ensure IFSC code field is required
    );

    // Validate the request
    $validator = \Validator::make($post->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['status' => 'ERR', 'message' => $validator->errors()->first()]);
    }

    // Update user information
    
    $user = Fingagent::where('user_id', \Auth::user()->id)->first();
    $user->merchantName = $post->merchantName;
    $user->merchantPhoneNumber = $post->merchantPhoneNumber;
    $user->bankIfscCode = $post->ifsc;
    $user->companyBankName = $post->bank;
    $user->bankBranchName = $post->branch;
    $user->bankAccountName = $post->account_name;
    $user->companyBankAccountNumber = $post->account;
    $user->mccCode = $post->mccCode;
    
    $user->merchantAddress = $post->merchantAddress;
    $user->merchantAddress2 = $post->merchantAddress2;
    $user->merchantCityName = $post->merchantCityName;
    $user->merchantDistrictName = $post->merchantDistrictName;
    $user->merchantState = $post->merchantState;
    $user->merchantPinCode = $post->merchantPinCode;
    
    $user->shopAddress = $post->shopAddress;
    $user->shopCity = $post->shopCity;
    $user->shopDistrict = $post->shopDistrict;
    $user->shopState = $post->shopState;
    $user->shopPincode = $post->shopPincode;
    
    
    $user->userPan = $post->userPan;
    $user->merchantAadhar = $post->merchantAadhar;
    $user->ipAddress = $post->ip();
    
    $user->status = 'pending';
    // Add other fields as necessary
    $user->save();

    // Handle file uploads
    if ($post->hasFile('aadharPics')) {
        $user->aadharPic = $post->file('aadharPics')->store('fingkyc');
    }
    if ($post->hasFile('pancardPics')) {
        $user->pancardPic = $post->file('pancardPics')->store('fingkyc');
    }
    if ($post->hasFile('maskedAadharImages')) {
        $user->maskedAadharImage = $post->file('maskedAadharImages')->store('fingkyc');
    }
    if ($post->hasFile('backgroundImageOfShops')) {
        $user->backgroundImageOfShop = $post->file('backgroundImageOfShops')->store('fingkyc');
    }

    $user->save();

    return response()->json(['status' => 'TXN', 'message' => 'User information updated successfully']);
    break;


            case 'useronboardsubmit':
                $rules = array(
                    'id' => 'required'
                );
                break;

            case 'ekycsendotp':
                $rules = array(
                    'id' => 'required',
                    'merchantPhoneNumber' => 'required',
                    'userPan' => 'required',
                    'merchantAadhar' => 'required',
                );
                break;

            case 'ekycvalidateotp':
                $rules = array(
                    'id' => 'required',
                    'otp' => 'required',
                    'primaryKeyId' => 'required',
                    'encodeFPTxnId' => 'required',
                );
                break;

            case 'biometric':
                $rules = array(
                    'id' => 'required',
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
                    'biodata'   => 'required',
                    'superMerchantId'   => 'required',
                );
                break;

            default:
                return response()->json(['status' => 'ERR', 'message' => 'Invalid Transaction Type']);
                break;
        }

        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(['status' => 'ERR', 'message' => $error]);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        $sessionkey = '';
        $mt_rand = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        foreach ($mt_rand as $chr) {
            $sessionkey .= chr($chr);
        }

        $iv =   '06f2f04cc530364f';
        $fp = fopen("fingpay_public_production.txt", "r");
        $publickey = fread($fp, 8192);
        fclose($fp);
        openssl_public_encrypt($sessionkey, $crypttext, $publickey);
        $gpsdata       =  $this->geoip($post->ip());
        switch ($post->transactionType) {

            case 'matmstatus':
                $agent = Fingagent::where('id', $post->id)->first();
                if (!$agent) {
                    return response()->json(['status' => 'ERR', 'message' => 'Agent onboarding could not found']);
                }

                if ($agent->status != "pending") {
                    return response()->json(['status' => 'ERR', 'message' => 'Agent onboarding pending']);
                }

                /*
                {"merchantLoginId":"FINGPAY1234","merchantPassword":"e6e061838856bf47e1de730719fb2609","superMerchantId":2,"superMerchantPassword":"796c3ee556ac31f3754a38cfd15b8044","merchantTranId":"123456","hash":"oeFNf527cE911LaCzS9wiYBo/7E5C7QsvwHqrAykpyU="}
            */

                $json = [
                    "merchantLoginId" => $agent->merchantLoginId,
                    "merchantPassword" =>  md5($this->aepsapi->password),
                    "superMerchantId" => $this->aepsapi->option1,
                    "superMerchantPassword" => $this->aepsapi->option1,
                    "merchantTranId" => $post->txnid,
                    "hash" => base64_encode(hash("sha256", $post->txnid + $agent->merchantLoginId + $this->aepsapi->option1, True)),
                ];
                $url =  "https://fpma.tapits.in/fpcardwebservice/api/ma/statuscheck/cw";
                $header = [
                    'Content-Type: text/xml',
                    'trnTimestamp:' . date('d/m/Y H:i:s'),
                    'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                    'eskey:' . base64_encode($crypttext)
                ];
                $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options = OPENSSL_RAW_DATA, $iv);
                $request = base64_encode($ciphertext_raw);
                $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
                dd($result);
                exit;
                break;
            case 'useronboardsubmit':
                $agent = Fingagent::where('id', $post->id)->first();
                
                $agentuser = User::where('id', $agent->user_id)->first();
                if (!$agent) {
                    return response()->json(['status' => 'ERR', 'message' => 'Invalid Agent']);
                }

                if ($agent->status != "pending") {
                    //  return response()->json(['status' => 'ERR', 'message' => 'Already Onboard']);
                }

                $json =  [
                    "username" => $this->aepsapi->username,
                    "password" => md5($this->aepsapi->password),
                    "latitude"       => $gpsdata['latitude'],
                    "longitude"      => $gpsdata['longitude'],
                    "supermerchantId" => $this->aepsapi->option1,
                    "merchants"      => [[
                        "merchantLoginId"     => $agent->merchantLoginId,
                        "merchantLoginPin"    => $agent->merchantLoginPin,
                        "merchantName"        => $agent->merchantName,
                        "merchantPhoneNumber" => $agent->merchantPhoneNumber,
                        "merchantPinCode"     => $agent->merchantPinCode,
                        "merchantCityName"     => $agent->merchantCityName,
                        "merchantAddress" => [
                            "merchantAddress" => $agent->merchantAddress,
                            "merchantState"   => $agent->merchantState
                        ],
                        "kyc" => [
                            "userPan" => $agent->userPan
                        ]
                    ]],
                ];

                // Get the full name from Auth::user()->name
                $fullName = $agent->merchantName;

                // Split the full name into an array of first name and last name
                $nameParts = explode(' ', $fullName);

                // Assuming the first name is the first element and the last name is the last element
                $firstName = $nameParts[0];
                $lastName = end($nameParts);

                $json =  [
                    "username" => $this->aepsapi->username,
                    "password" => md5($this->aepsapi->password),
                    "latitude" => $gpsdata['latitude'],
                    "longitude" => $gpsdata['longitude'],
                    "supermerchantId" => $this->aepsapi->option1,
                    "ipAddress" => $agent->ipAddress,
                    "merchant" => [
                        [
                            "merchantLoginId" => $agent->merchantLoginId,
                            "merchantLoginPin" => $agent->merchantLoginPin,
                            "firstName" => $firstName,
                            "lastName" => $lastName,
                            "merchantPhoneNumber" => $agent->merchantPhoneNumber,
                            "merchantAddress" => [
                                "merchantAddress1" => $agent->merchantAddress, // Corrected the key name from "merchantAddres1" to "merchantAddress1"
                                "merchantCityName" => $agent->merchantCityName,
                                "merchantDistrictName" => $agent->merchantDistrictName,
                                "merchantState" => $agent->merchantState,
                                "merchantPinCode" => $agent->merchantPinCode,
                            ],
                             "companyType" => $agent->mccCode, // (New) MCC Code for merchant shop category,
                             "certificateOfIncorporationImage" => "False",
                            "kyc" => [
                                "userPan" => $agent->userPan,
                                "aadhaarNumber" => $agent->merchantAadhar, 
                                "gstInNumber" => "19BYHPM1037M1Z3",// super merchant
                                "companyOrShopPan" => "BYHPM1037M",
                                "shopAndPanImage" => "False"
                            ],
                            "settlementV1" => [
                                        "companyBankAccountNumber" => $agent->companyBankAccountNumber, // (New) Bank account number
                                        "bankIfscCode" => $agent->bankIfscCode, // (New) IFSC code of the bank
                                        "companyBankName" => $agent->companyBankName, // (New) Bank name of the company
                                        "bankBranchName" => $agent->bankBranchName, // (New) Bank name of the company
                                        "bankAccountName" => $agent->bankAccountName // (New) Bank name of the company
                                    ],
                            "tradeBusinessProof" => "False",
                            "termsConditionCheck" => "True",
                            "cancelledChequeImages" => "False",
                            "physicalVerification" => "False",
                            "videoKycWithLatLongData" => "True",
                            "merchantKycAddressData" => [
                                "shopAddress" => $agent->shopAddress,
                                "shopCity" => $agent->shopCity,
                                "shopDistrict" => $agent->shopDistrict,
                                "shopState" => $agent->merchantState,
                                "shopPincode" => $agent->merchantPinCode,
                                "shopLatitude" => $gpsdata['latitude'],
                                "shopLongitude" => $gpsdata['longitude'],
                            ]
                        ]
                    ]
                ];
                $json = [
    "username" => $this->aepsapi->username,
    "password" => md5($this->aepsapi->password),
    "latitude" => $gpsdata['latitude'],
    "longitude" => $gpsdata['longitude'],
    "supermerchantId" => $this->aepsapi->option1,
    "ipAddress" => $agent->ipAddress,
    "merchant" => [
        "merchantLoginId" => $agent->merchantLoginId,
        "merchantLoginPin" => $agent->merchantLoginPin,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "merchantPhoneNumber" => $agent->merchantPhoneNumber,
        "merchantAddress" => [
            "merchantAddress1" => $agent->merchantAddress,
            "merchantCityName" => $agent->merchantCityName,
            "merchantDistrictName" => $agent->merchantDistrictName,
            "merchantState" => $agent->merchantState,
            "merchantPinCode" => $agent->merchantPinCode
        ],
        "companyLegalName" => "DigiSeva",
        "companyType" => $agent->mccCode,
        "emailId" => $agentuser->email,
        "certificateOfIncorporationImage" => "False",
        "kyc" => [
            "userPan" => $agent->userPan,
            "aadhaarNumber" => $agent->merchantAadhar,
            "gstinNumber" => "19BYHPM1037M1Z3",
            "companyOrShopPan" => "BYHPM1037M",
            "shopAndPanImage" => "False"
        ],
        "settlementV1" => [
            "companyBankAccountNumber" => $agent->companyBankAccountNumber,
            "bankIfscCode" => $agent->bankIfscCode,
            "companyBankName" => $agent->companyBankName,
            "bankBranchName" => $agent->bankBranchName,
            "bankAccountName" => $agent->bankAccountName
        ],
        "tradeBusinessProof" => "False",
        "termsConditionCheck" => "True",
        "cancelledChequeImages" => "False",
        "physicalVerification" => "False",
        "videoKycWithLatLongData" => "True",
        "merchantKycAddressData" => [
            "shopAddress" => $agent->shopAddress,
            "shopCity" => $agent->shopCity,
            "shopDistrict" => $agent->shopDistrict,
            "shopState" => $agent->merchantState,
            "shopPincode" => $agent->merchantPinCode,
            "shopLatitude" => $gpsdata['latitude'],
            "shopLongitude" => $gpsdata['longitude']
        ]
    ]
];

                
                

                $header = [
                    'Content-Type: text/xml',
                    'trnTimestamp:' . date('d/m/Y H:i:s'),
                    'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                    'eskey:' . base64_encode($crypttext)
                ];
                
            
                    
                







                // $url = $this->aepsapi->url."fpaepsweb/api/onboarding/merchant/creation/php/m1";
                $url = "https://fingpayap.tapits.in/fpaepsweb/api/onboarding/merchant/php/creation/v2";
                break;
            case 'ekycsendotp':
                $agent = Fingagent::where('id', $post->id)->first();
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
                    "matmSerialNumber" => '2247I005725',
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
                    'deviceIMEI:' . '2247I005725',
                    'eskey:' . base64_encode($crypttext)
                ];


                $url = "https://fpekyc.tapits.in/fpekyc/api/ekyc/merchant/php/sendotp";
                break;
            case 'ekycvalidateotp':
                $agent = Fingagent::where('id', $post->id)->first();
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
                    $agent = Fingagent::where('id', $post->id)->first();
                    if (!$agent) {
                        return response()->json(['status' => 'ERR', 'message' => 'Invalid Agent']);
                    }
    
                    if ($agent->status != "pending") {
                        //  return response()->json(['status' => 'ERR', 'message' => 'Already Onboard']);
                    }


                $agent = Fingagent::where('user_id', \Auth::id())->first();
                $biodata       =  str_replace("&lt;", "<", str_replace("&gt;", ">", $post->biodata));
                $xml           =  simplexml_load_string($biodata);
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);

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
                        "errInfo"   =>  $headerarray['Resp']['@attributes']['errInfo'],
                        "fCount"  =>  $headerarray['Resp']['@attributes']['fCount'],
                        "fType" =>  $headerarray['Resp']['@attributes']['fType'],
                        "iCount" => "0",
                        "iType" => null,
                        "pCount" => "0",
                        "pType" => "0",
                        "nmPoints" =>  $headerarray['Resp']['@attributes']['nmPoints'],
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
                        'deviceIMEI:' . $headerarray['additional_info']['Param'][0]['@attributes']['value'],
                        'eskey:' . base64_encode($crypttext)
                    ];
                    
                }
                else {
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'],
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
                $bank  = \DB::table('fingaepsbanks')->where('iinno', $post->nationalBankIdentificationNumber)->first();
                $agent = Fingagent::where('user_id', \Auth::id())->first();
                $biodata       =  str_replace("&lt;", "<", str_replace("&gt;", ">", $post->biodata));
                $xml           =  simplexml_load_string($biodata);
                $skeyci        =  (string)$xml->Skey['ci'][0];
                $headerarray   =  json_decode(json_encode((array)$xml), TRUE);
                
                $post['amount'] = PortalSetting::where('code', 'master_2fa_cost')->first()->value;
            
            if(\Auth::user()->mainwallet < $post->amount){
                        
            return response()->json([
                'status'   => 'failed',
                'message' => 'Insufficient Balance'
            ]);
        
                    }

                do {
                    $post['txnid'] = "MEA" . rand(1111111111, 9999999999);
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

                if ($post->transactionType == "BE") {
                    $json["merchantTransactionId"] = $post->txnid;
                    $json['transactionAmount'] = 0;
                    $json['superMerchantId']   = $post->superMerchantId;
                } elseif ($post->transactionType == "MS") {
                    $json["merchantTranId"] = $post->txnid;
                } elseif ($post->transactionType == "AUO") {
                    $json["serviceType"] = $post->auth_type;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                } else {
                    $json["transactionAmount"] = $post->transactionAmount;
                    $json["merchantTranId"] = $post->txnid;
                    $json['superMerchantId']   = $post->superMerchantId;
                }

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
                        'deviceIMEI:' . $headerarray['additional_info']['Param'][0]['@attributes']['value'],
                        'eskey:' . base64_encode($crypttext)
                    ];
                    
                }else {
                    $header = [
                        'Content-Type: text/xml',
                        'trnTimestamp:' . date('d/m/Y H:i:s'),
                        'hash:' . base64_encode(hash("sha256", json_encode($json), True)),
                        'deviceIMEI:' . $headerarray['DeviceInfo']['additional_info']['Param']['@attributes']['value'],
                        'eskey:' . base64_encode($crypttext)
                    ];
                    
                }

                if ($post->transactionType == "BE") {
                    $url = $this->aepsapi->url . "fpaepsservice/api/balanceInquiry/merchant/php/getBalance";
                } elseif ($post->transactionType == "MS") {
                    $url = "https://fingpayap.tapits.in/fpaepsservice/api/miniStatement/merchant/php/statement";
                } elseif ($post->transactionType == "M") {
                    $url = $this->aepsapi->url . "fpaepsservice/api/aadhaarPay/merchant/php/pay";
                } elseif ($post->transactionType == "AUO") {
                    $url = $this->aepsapi->url . "fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fpuat.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";

                    $url = "https://fingpayap.tapits.in/fpaepsservice/auth/tfauth/merchant/php/validate/aadhar";
                } else {
                    $url = $this->aepsapi->url . "fpaepsservice/api/cashWithdrawal/merchant/php/withdrawal";
                }



                if ($post->transactionType == "CW" || $post->transactionType == "M") {
                    $insert = [
                        "mobile"  => $post->mobileNumber,
                        "aadhar"  => "XXXXXXXX" . substr($post->adhaarNumber, -4),
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
                        'aepstype' => $post->transactionType,
                        'withdrawType' => $post->transactionType
                    ];

                    $report = Aepsreport::create($insert);
                }
                break;
        }

        $ciphertext_raw = openssl_encrypt(json_encode($json), 'AES-128-CBC', $sessionkey, $options = OPENSSL_RAW_DATA, $iv);
        $request = base64_encode($ciphertext_raw);
        $result = \Myhelper::curl($url, 'POST', $request, $header, "yes", $post, $post->txnid);
        if (\Myhelper::hasRole(['admin']) || \Auth::id() == '261') {

           

          
            // Log the request data
            $requestData = [
                'url' => $url,
                'method' => 'POST',
                'data' => $json,
                'response' => $result
            ];
            $logMessage = "cURL Request: " . json_encode($requestData);
            file_put_contents('curl_log.txt', $logMessage . PHP_EOL, FILE_APPEND);
        }


        //$result = ["response" => '{"status":true,"message":"Request Completed","data":{"terminalId":null,"requestTransactionTime":"09/03/2022 23:19:06","transactionAmount":100.0,"transactionStatus":"successful","balanceAmount":2936.11,"strMiniStatementBalance":null,"bankRRN":"206823266593","transactionType":"CW","fpTransactionId":"CWBF2715521090322231906229I","merchantTxnId":null,"errorCode":null,"errorMessage":null,"merchantTransactionId":"MEA5925054953","bankAccountNumber":null,"ifscCode":null,"bcName":null,"transactionTime":null,"agentId":0,"issuerBank":null,"customerAadhaarNumber":null,"customerName":null,"stan":null,"rrn":null,"uidaiAuthCode":null,"bcLocation":null,"demandSheetId":null,"mobileNumber":null,"urnId":null,"miniStatementStructureModel":null,"miniOffusStatementStructureModel":null,"miniOffusFlag":false,"transactionRemark":null,"bankName":null,"prospectNumber":null,"internalReferenceNumber":null,"biTxnType":null,"subVillageName":null,"userProfileResponseModel":null,"hindiErrorMessage":null,"loanAccNo":null,"responseCode":"00","fpkAgentId":null},"statusCode":10000}', "error" => "", "code" => 200];

        if ($result['response'] == '') {
            switch ($post->transactionType) {




                case 'useronboardsubmit':
                    return response()->json(['status' => 'pending', 'message' => 'User onboard pending']);
                    break;
                case 'AUO':

                    if ($post->auth_type == "AEPS") {
                        //  Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                    } else {
                        //  Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                    }
                    return response()->json(['status' => 'pending', 'message' => $post->auth_type . 'Authentication  Successfull']);
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
                        'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                        'id'       => $post->txnid,
                        'amount'   => $post->transactionAmount,
                        'created_at' => $report->created_at
                    ]);
                    break;
            }
        }

        if ($result['response'] != '') {
            $response = json_decode($result['response']);
            

            if (isset($response->status)) {
                if (isset($response) && isset($response->status) && isset($response->data->responseCode)) {
                    if ($response->status === false && $response->data->responseCode === 'FP069') {
                        Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => null, 'ap_auth' => null]);
                    }
                }
                
                

                switch ($post->transactionType) {
                    case 'useronboardsubmit':
                        
                        
                        if ($response->status == "true" || $response->status == 1) {
                            Fingagent::where('id', $post->id)->update(['status' => "approved"]);
                            return response()->json(['status' => 'success', 'message' => 'User onboard successfully']);
                        } else {
                            // Concatenate all messages and details into a single message
$combinedMessage = $response->message;

if (isset($response->data)) {
    $details = [];

    // Collect all details with labels
    if (!empty($response->data->merchantStatus)) {
        $details[] = "Merchant Status: " . $response->data->merchantStatus;
    }
    if (!empty($response->data->remarks)) {
        $details[] = "Remarks: " . $response->data->remarks;
    }
    if (!empty($response->data->superMerchantId)) {
        $details[] = "Super Merchant ID: " . $response->data->superMerchantId;
    }
    if (!empty($response->data->merchantLoginId)) {
        $details[] = "Merchant Login ID: " . $response->data->merchantLoginId;
    }
    if (!empty($response->data->errorCodes)) {
        $details[] = "Error Codes: " . $response->data->errorCodes;
    }

    // Append each detail to the combined message
    $combinedMessage .= ' | ' . implode(' | ', $details);
}

return response()->json([
    'status' => 'ERR',
    'message' => $combinedMessage,
]);

                            return response()->json(['status' => 'ERR', 'message' => $response->message]);
                        }
                        break;

                    case 'ekycsendotp':
                        if ($response->status == "true") {
                            return response()->json(['status' => 'success', 'txntype' => 'ekycsendotp', 'message' => $response->status, 'data' => $response->data]);
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
                            return response()->json(['status' => 'success',  'txntype' => 'ekycvalidateotp',  'message' => $response->status, 'data' => $response->data]);
                        } else {
                            return response()->json(['status' => 'ERR', 'message' => $response->message]);
                        }
                        break;
                    case 'biometric':
                            if ($response->status == "true") {
                                Fingagent::where('user_id', $post->user_id)->update(['ekyc' => '1']);
                                return response()->json(['status' => 'success',  'txntype' => 'biometric',  'message' => $response->status, 'data' => $response->data]);
                            } else {
                                return response()->json(['status' => 'ERR', 'message' => $response->message]);
                            }
                            break;

                        

                    case 'AUO':
                        
                        \Myhelper::handleMaster2faCost($post->auth_type,$post->user_id);
                        if ($response->status == "true") {
                            if ($post->auth_type == "AEPS") {
                                Fingagent::where('user_id', $post->user_id)->update(['aeps_auth' => now()->toDateString()]);
                            } else {
                                Fingagent::where('user_id', $post->user_id)->update(['ap_auth' => now()->toDateString()]);
                            }
                        }
                        if($response->status == '')
                        {
                            return response()->json([
                                'status' => 'bm',
                                'transactionType' => 'AUO',
                                'message' => $post->auth_type . ' ' . ($response->data->responseMessage ?? $response->message)
                            ]);
                            
                        }
                        else{
                            return response()->json(['status' => 'success', 'transactionType' => 'AUO', 'message' =>  ' ' . $post->auth_type . ' ' . $response->message]);
                       
                        }
                        break;

                    case 'BE':
                    case 'CW':
                    case 'MS':
                    case 'M':
                        if ($response->status == true && isset($response->data) && in_array($response->data->errorCode, ['null', null])) {
                            if ($post->transactionType == "CW" || $post->transactionType == "M") {
                                if ($post->transactionType == "M") {
                                    $product = "aadharpay";
                                } else {
                                    $product = "fingaeps";
                                }
                                //commision set here

                                if ($post->transactionType == "M") {
                                    $product = "aadharpay";

                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount)->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aadharpay')->first();
                                    $post['provider_id'] = $provider->id;
                                    if ($provider) {
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit;
                                    } else {
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                    $tds = 0;
                                    $gst = 0;
                                } else {
                                    $product = "aeps";

                                    $provider = Provider::where('min_amount', '<=', $post->transactionAmount)->where('max_amount', '>=', $post->transactionAmount)->where('type', 'aeps')->first();
                                    $post['provider_id'] = $provider->id;



                                    if ($provider) {
                                        $profit = \Myhelper::getCommission($post->transactionAmount, $user->scheme_id, $provider->id, $user->role->slug);
                                        $tds = $this->getTds($profit);
                                        $gst = $this->getGst($profit);
                                        $profit = $profit - ($tds + $gst);
                                    } else {
                                        $profit = 0;
                                        $tds = 0;
                                        $gst = 0;
                                    }
                                }
                                //commision set here


                                if ($post->transactionType == "CW") {
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount + $profit);
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);
                                } elseif ($post->transactionType == "M") {
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                } else {
                                    User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount);
                                    User::where('id', $post->user_id)->decrement('mainwallet', $profit);
                                    //User::where('id', $post->user_id)->increment('aepsbalance', $post->transactionAmount - $profit);
                                    $tds = 0;
                                    $gst = 0;
                                }

                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'success',
                                    'charge' => $profit,
                                    'gst' => $gst,
                                    "tds"    => $tds,
                                    'refno'  => $response->data->bankRRN,
                                    'payid'  => $response->data->fpTransactionId
                                ]);

                                if ($post->transactionType == "CW") {
                                    $report['provider_id'] = $provider->id;
                                    $report['apicode'] = 'aeps';
                                    \Myhelper::aepscommission($report);
                                }
                            }

                            // $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";
                            // //$threewayurl = "https://fpuat.tapits.in/fpcollectservice/api/threeway/aggregators";


                            // $requestbody = [
                            //     [
                            //         "merchantTransactionId" => "MEA6238378786",//$post->txnid,
                            //         "fingpayTransactionId"  => "CWBF4913287260523224435809I",//$response->data->fpTransactionId,
                            //         "transactionRrn"  => "314622510811",//$response->data->bankRRN,
                            //         "responseCode"    => "00",
                            //         "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                            //         "serviceType"     => "CW", //$post->transactionType
                            //     ]
                            // ];


                            // $headerbody = json_encode($requestbody)."digisevad3bebc440a1be54903c3136616a42cbbae58036f847fd72f58e9faaf5aa28c958";
                            // //base64_encode(hash("sha256",json_encode($json), True))
                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'hash:'.base64_encode(hash("sha256",json_encode($requestbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:digisevad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "no", $post);

                            // dd([
                            //     "Body"     => json_encode($requestbody),
                            //     "HashBody" => $headerbody,
                            //     "Header"   => $requestheader,
                            //     "Response" => $result,
                            //     "URL" => $threewayurl
                            // ]);

                            if ($post->transactionType != "MS") {
                                return response()->json([
                                    'status'   => 'success',
                                    'message'  => 'Transaction Successfull',
                                    'balance'  => $response->data->balanceAmount,
                                    'rrn'      => $response->data->bankRRN,
                                    "transactionType"   => $post->transactionType,
                                    "title"    => (($post->transactionType == "BE") ? "Balance Enquiry" : (($post->transactionType == "CW") ? "Cash Withdrawal" : "Aadhar Pay")),
                                    'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at' => isset($report->created_at) ? $report->created_at : date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            } else {
                                if ($post->transactionType != "CW" || $post->transactionType != "M") {

                                    $profit = \Myhelper::getCommission(0, $user->scheme_id, '88', $user->role->slug);
                                    $tds = $this->getTds($profit);
                                    $gst = $this->getGst($profit);
                                    $profit = $profit - ($tds + $gst);
                                    User::where('id', $post->user_id)->increment('mainwallet', $profit);

                                    $trtype = "credit";
                                    $insert = [
                                        "mobile"  => $post->mobileNumber,
                                        "aadhar"  => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                                        "txnid"   => $post->txnid,
                                        "amount"  => "0.00",
                                        "charge"  => $profit,
                                        "user_id" => $post->user_id,
                                        "tds"     => $this->getTds($profit),
                                        "balance" => $user->aepsbalance,
                                        'type'    => $trtype,
                                        'refno'   => 'Mini Statement',
                                        'api_id'  => $this->aepsapi->id,
                                        'credited_by' => $post->user_id,
                                        'status'      => 'success',
                                        'rtype'       => 'main',
                                        'transtype'   => 'transaction',
                                        'bank'     => $bank->bankName,
                                        'aepstype' => $post->transactionType,
                                        'withdrawType' => $post->transactionType
                                    ];

                                    $report = Aepsreport::create($insert);

                                    if ($post->transactionType == "MS") {
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
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at' => date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        } else {
                            if ($post->transactionType == "CW" || $post->transactionType == "M") {
                                Aepsreport::where('id', $report->id)->update([
                                    'status' => 'failed',
                                    'refno'  => isset($response->data->bankRRN) ? $response->data->bankRRN : $response->message,
                                    'remark' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message
                                ]);
                            }

                            // $threewayurl = "https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators";

                            // $requestbody = [
                            //     [
                            //         "merchantTransactionId" => $post->txnid,
                            //         "fingpayTransactionId"  => $response->data->fpTransactionId,
                            //         "transactionRrn"  => $response->data->bankRRN,
                            //         "responseCode"    => "00",
                            //         "transactionDate" => Carbon::createFromFormat('d/m/Y H:i:s', $txndate)->format('d-m-Y'),
                            //         "serviceType"     => $post->transactionType
                            //     ]
                            // ];


                            // $headerbody = json_encode($requestbody)."";

                            // $requestheader = [                 
                            //     'txnDate:'.$txndate,   
                            //     'trnTimestamp:'.$txndate,
                            //     'hash:'.base64_encode(hash("sha256",json_encode($headerbody), True)),         
                            //     'superMerchantId:'.$post->superMerchantId,
                            //     'superMerchantLoginId:easypaisad'      
                            // ];

                            // $result = \Myhelper::curl($threewayurl, 'POST', json_encode($requestbody), $requestheader, "yes", $post);


                            if ($post->transactionType != "MS") {
                                return response()->json([
                                    'status'   => 'failed',
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => ($post->transactionType == "BE") ? "Balance Enquiry" : "Cash Withdrawal",
                                    'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'amount'   => $post->transactionAmount,
                                    'created_at' => isset($report->created_at) ? $report->created_at : date('d M Y H:i'),
                                    'bank'     => $bank->bankName
                                ]);
                            } else {
                                return response()->json([
                                    'status'   => 'failed',
                                    'message'  => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                                    'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                                    'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                                    "transactionType"   => $post->transactionType,
                                    "title"    => "Mini Statement",
                                    'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                                    'id'       => $post->txnid,
                                    'created_at' => date('d M Y H:i'),
                                    'bank'     => $bank->bankName,
                                    "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
                                ]);
                            }
                        }
                        break;
                }
            }
        }

        if ($post->transactionType != "MS") {
            return response()->json([
                'status'   => 'pending',
                'message'  => 'Transaction Under Process',
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'pending',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Cash Withdrawal",
                'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'amount'   => $post->transactionAmount,
                'created_at' => date('d M Y H:i'),
                'bank'     => $bank->bankName
            ]);
        } else {
            return response()->json([
                'status'   => 'failed',
                'balance'  => isset($response->data->balanceAmount) ? $response->data->balanceAmount : '0',
                'rrn'      => isset($response->data->bankRRN) ? $response->data->bankRRN : 'Failed',
                'errorMsg' => isset($response->data->errorMessage) ? $response->data->errorMessage : $response->message,
                "transactionType"   => $post->transactionType,
                "title"    => "Mini Statement",
                'aadhar'   => "XXXXXXXX" . substr($post->adhaarNumber, -4),
                'id'       => $post->txnid,
                'created_at' => date('d M Y H:i'),
                'bank'     => $bank->bankName,
                "data"     => isset($response->data->miniStatementStructureModel) ? $response->data->miniStatementStructureModel : []
            ]);
        }
    }

    public function getTds($amount)
    {
        $tds = \Auth::user()->tds;
        return round($amount * $tds / 100, 2);
    }

    public function getGst($amount)
    {
        $gst = \Auth::user()->gst;
        return round($amount * $gst / 100, 2);
    }
}
