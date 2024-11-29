<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Fundreport;
use App\Model\Aepsfundrequest;
use App\Model\Report;
use App\Model\Fundbank;
use App\Model\Paymode;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Aepsreport;
use App\Model\PortalSetting;
use Carbon\Carbon;

class FundController extends Controller
{
    public $fundapi, $admin;

    public function __construct()
    {
        $this->fundapi = Api::where('code', 'fund')->first();
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();

    }
    
    public function digipay(){
        
        header("Content-Type:application/json");

		//Variable to send back response
		$response_data = array();

		//Variable for encryption key
		$encryption_key = "";

		//Get request headers
		$request_header   = apache_request_headers();

		$data_request = [
                "AGGRID" => "OTOE0159",
                "AGGRNAME" => "DIGISEVA",                        
                "CORPID" => "572583168",
                "USERID" => "USER1",
                "URN" => "SR197281790",
                "DEBITACC" => "000405001611",
                "CREDITACC" => "20367692777",
                "IFSC" => "SBIN0000239",                        
                "CURRENCY" => "INR",
                "TXNTYPE" => "IFS",
                "AMOUNT" => "10",
                "PAYEENAME" => "Ajit Doval",
                "REMARKS" => "Bank Transfer from Digiseva Pay",
                "UNIQUEID" => rand(1000, 99999),
                "CUSTOMERINDUCED" => "N"                        
            ];
        
        
        
        
        
        // Convert to json
        $json_enc = json_encode($data_request);
        
        // Encrypt using public key
        $fp1 = fopen("ICICI_PUBLIC_CERT_PROD.txt", "r");
        $public_key = fread($fp1, 8192);
        fclose($fp1);
        
        openssl_public_encrypt($json_enc,$encrypted,$public_key, OPENSSL_PKCS1_PADDING);
        
        // Encode to base64
        $base64_enc = base64_encode($encrypted);
        
        // Create header
        $header = [
            'Content-type:text/plain',        
            'apikey:Qz9238nSqAT8kcfshS3YPNiWePY4O46O'
        ];  
        
        // Create url
        $url = 'https://apibankingone.icicibank.com/api/Corporate/CIB/v1/Transaction';
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,        
            CURLOPT_PORT => "8443",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $base64_enc,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_URL => $url
        ));
        
        // Get response
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        // Decode from base64
        $base64_dec = base64_decode($response);                        
        
        var_dump($base64_dec); exit;
    }

    public function index($type, $action="none")
    {
        $data = [];
        switch ($type) {
            case 'tr':
                $permission = ['fund_transfer', 'fund_return'];
                break;
            
            case 'request':
                $permission = 'fund_request';
                break;
            
            case 'requestview':
                $permission = 'setup_bank';
                break;
            
            case 'statement':
            case 'requestviewall':
                $permission = 'fund_report';
                break;

            case 'aeps':
                if(!\Myhelper::service_active('aeps_service'))
                {
        
                    return  redirect()->back()->with('error','Service Currently Deactive');  
        
                }
                $permission = 'aeps_fund_request';
                $data['settlementcharge'] = $this->settlementcharge();
                $data['settlementcharge1k'] = $this->settlementcharge1k();
                $data['settlementcharge25k'] = $this->settlementcharge25k();
                $data['settlementcharge2l'] = $this->settlementcharge2l();
                $data['aepslocked'] = $this->aepslocked();
                $data['batch'] = $this->batch();
                break;
            
            case 'aepsrequest':
            case 'payoutrequest':
                $permission = 'aeps_fund_view';
                break;

            case 'aepsfund':
            case 'aepsrequestall':
                $permission = 'aeps_fund_report';
                break;

            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }

        if ($this->fundapi->status == "0") {
            abort(503);
        }

        switch ($type) {
            case 'request':
                $data['banks'] = Fundbank::where('user_id', \Auth::user()->parent_id)->where('status', '1')->get();
                if(!\Myhelper::can('setup_bank', \Auth::user()->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', \Auth::user()->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $data['banks'] = Fundbank::where('user_id', $admin->id)->where('status', '1')->get();
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $data['banks'] = Fundbank::where('user_id', $admin->id)->where('status', '1')->get();
                    }
                }
                $data['paymodes'] = Paymode::where('status', '1')->get();
                break;
        }

        return view('fund.'.$type)->with($data);
    }
    
    public function onlinefundtransaction(Request $post)
    {
        $orderId 	= time();
    $orderId = $this->transcode().rand(111111111111, 999999999999);
     
    $txnAmount 	= $post->amount;
    $custId 	= \Auth::user()->id;
    $mobileNo 	= \Auth::user()->mobile;
    $email 		= \Auth::user()->email;
    
    $surcharge = \Myhelper::company_details()->surcharge;
    
    //$txnAmount = $txnAmount+($txnAmount*$surcharge/100);
  
    $paytmParams = array();
    $paytmParams["ORDER_ID"] 	= $orderId;
    $paytmParams["CUST_ID"] 	= $custId;
    $paytmParams["MOBILE_NO"] 	= $mobileNo;
    $paytmParams["EMAIL"] 		= $email;
    $paytmParams["TXN_AMOUNT"] 	= $txnAmount;
    $paytmParams["MID"] 		= \Myhelper::company_details()->paytm_mid;
    $paytmParams["CHANNEL_ID"] 	= 'WEB';
    $paytmParams["WEBSITE"] 	= 'DEFAULT';
    $paytmParams["INDUSTRY_TYPE_ID"] = 'PrivateEducation';
    $paytmParams["CALLBACK_URL"] = route('validate_payment');
    
   
    $data['paytmParams'] = $paytmParams;
    
    
    $data['paytmChecksum'] =  \Paytm::generateSignature( $paytmParams, \Myhelper::company_details()->paytm_key );
  
    $data['transactionURL'] = 'https://securegw.paytm.in/theia/processTransaction';
    
    return view('paytm_payment')->with($data);
    }
    
    public function validate_payment(Request $post)
    {
        $data['responce'] = $post;
        $STATUS = $post->STATUS;
        
        if($STATUS == 'TXN_FAILURE')
        {
            return redirect()->route('fund','request')->with('error',$post->RESPMSG);
        }
        elseif($STATUS == 'TXN_SUCCESS')
        {
            $surcharge = \Myhelper::company_details()->surcharge;
                    $txnAmount = $post->TXNAMOUNT-($post->TXNAMOUNT*$surcharge/100);
            $data['fundbank_id']= 1;
            $data['paymode']  = 'Paytm';
            $data['type'] = 'transfer';
            $data['status'] = 'success';
            $data['amount'] =  $txnAmount;
            $data['ref_no'] =  $post->ORDERID;
            $data['paydate'] = date('Y-m-d');
            $data['user_id'] = \Auth::id();
            $data['created_by'] = '1';
            $data['credited_by'] = \Auth::user()->parent_id;
                if(!\Myhelper::can('setup_bank', \Auth::user()->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', \Auth::user()->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $post['credited_by'] = $admin->id;
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $post['credited_by'] = $admin->id;
                    }
                }
                
            $action = Fundreport::create($data);
                if($action){
                    
                    User::where('id', \Auth::id())->increment('mainwallet', $txnAmount);
                    $insert = [
                        'number' => \Auth::user()->mobile,
                        'mobile' => \Auth::user()->mobile,
                        'provider_id' => 81,
                        'api_id' => 1,
                        'amount' => $txnAmount,
                        'charge' => '0.00',
                        'profit' => '0.00',
                        'gst' => '0.00',
                        'tds' => '0.00',
                        'apitxnid' => NULL,
                        'txnid' => date('ymdhis'),
                        'payid' => NULL,
                        'refno' => NULL,
                        'description' => NULL,
                        'remark' => 'Loaded By Paytm',
                        'option1' => NULL,
                        'option2' => NULL,
                        'option3' => NULL,
                        'option4' => NULL,
                        'status' => 'success',
                        'user_id' => \Auth::id(),
                        'credit_by' => \Auth::id(),
                        'rtype' => 'main',
                        'via' => 'portal',
                        'adminprofit' => '0.00',
                        'balance' => \Auth::user()->mainwallet,
                        'trans_type' => 'credit',
                        'product' => "fund transfer"
                    ];
                    $action = Report::create($insert);
                $user = explode(' ',ucwords(\Auth::user()->name))[0].' (Id - '.\Auth::id();
                $msg = ''.$user.' Added Money using PayTm of amount  '.$txnAmount.'';
                \Myhelper::save_notification($msg);
                }
            
                    
        return redirect()->route('fund','request')->with('success',$post->RESPMSG);
            
        }
    }

    public function transaction(Request $post)
    {
        if ($this->fundapi->status == "0") {
            return response()->json(['status' => "This function is down."],400);
        }
        $provide = Provider::where('recharge1', 'fund')->first();
        $post['provider_id'] = $provide->id;

        switch ($post->type) {
            case 'transfer':
                
            case 'return':
                if($post->type == "transfer" && !\Myhelper::can('fund_transfer')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                if($post->type == "return" && !\Myhelper::can('fund_return')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $rules = array(
                    'amount'    => 'required|numeric|min:1'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                
                if($post->has('otp'))
                    {
                         $post->otp;
                       $adminotp =  User::where('mobile', \Myhelper::adminphone())->first()->otpverify;
                       if($adminotp == $post->otp)
                       {
                             $otp = rand(111111, 999999);
                        User::where('mobile', \Myhelper::adminphone())->update(['otpverify' => $otp]);
                       }
                       else
                       {
                           return response()->json(['status' => 'Please provide correct OTP'], 400);
                       }
                    }
                    else
                    {
                        $otp = rand(111111, 999999);
                        User::where('mobile', \Myhelper::adminphone())->update(['otpverify' => $otp]);



                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Dear%20Admin%20Your%20Fund%20Transfer%20OTP%20is%20'.$otp,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        ));
    
                        $response = curl_exec($curl);
    
                        curl_close($curl);
                        $send = json_decode($response);
                        // $msg = urlencode("Dear Admin, your Fund Transfer otp is ".$otp);
                        // $send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                        return response()->json(['status' => 'otpsent'], 200);
                    }   
                    
                if($post->type == "transfer"){
                   
                    if(\Auth::user()->mainwallet - $this->mainlocked() < $post->amount){
                        return response()->json(['status' => "Insufficient wallet balance."],400);
                    }
                }else{
                    $user = User::where('id', $post->user_id)->first();
                    if($user->mainwallet - $this->mainlocked() < $post->amount){
                        return response()->json(['status' => "Insufficient balance in user wallet."],400);
                    }
                }
                $post['txnid'] = 0;
                $post['option1'] = 0;
                $post['option2'] = 0;
                $post['option3'] = 0;
                $post['refno'] = date('ymdhis');
                return $this->paymentAction($post);

                break;

            case 'requestview':
                
                if($post->has('otp'))
                    {
                         $post->otp;
                       $adminotp =  User::where('mobile', \Myhelper::adminphone())->first()->otpverify;
                       if($adminotp == $post->otp)
                       {
                             $otp = rand(111111, 999999);
                        User::where('mobile', \Myhelper::adminphone())->update(['otpverify' => $otp]);
                       }
                       else
                       {
                           return response()->json(['status' => 'Please provide correct OTP'], 400);
                       }
                    }
                    else
                    {
                        $otp = rand(111111, 999999);
                        User::where('mobile', \Myhelper::adminphone())->update(['otpverify' => $otp]);
                        $msg = urlencode("Dear Admin, your Fund Transfer otp is ".$otp);
                        $send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                        return response()->json(['status' => 'otpsent'], 200);
                    }
                if(!\Myhelper::can('setup_bank')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $fundreport = Fundreport::where('id', $post->id)->first();
                
                if($fundreport->status != "pending"){
                    return response()->json(['status' => "Request already approved"],400);
                }

                $post['amount'] = $fundreport->amount;
                $post['type'] = "request";
                $post['user_id'] = $fundreport->user_id;
                if ($post->status == "approved") {
                    if(\Auth::user()->mainwallet - $this->mainlocked() < $post->amount){
                        return response()->json(['status' => "Insufficient wallet balance."],200);
                    }
                    $action = Fundreport::where('id', $post->id)->update([
                        "status" => $post->status,
                        "remark" => $post->remark
                    ]);

                    $post['txnid'] = $fundreport->id;
                    $post['option1'] = $fundreport->fundbank_id;
                    $post['option2'] = $fundreport->paymode;
                    $post['option3'] = $fundreport->paydate;
                    $post['refno'] = $fundreport->ref_no;
                    return $this->paymentAction($post);
                }else{
                    $action = Fundreport::where('id', $post->id)->update([
                        "status" => $post->status,
                        "remark" => $post->remark
                    ]);

                    if($action){
                        return response()->json(['status' => "success"],200);
                    }else{
                        return response()->json(['status' => "Something went wrong, please try again."],200);
                    }
                }
                
                return $this->paymentAction($post);
                break;

            case 'request':
                if(!\Myhelper::can('fund_request')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $rules = array(
                    'fundbank_id'    => 'required|numeric',
                    'paymode'    => 'required',
                    'amount'    => 'required|numeric|min:100',
                    'ref_no'    => 'required|unique:fundreports,ref_no',
                    'paydate'    => 'required'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $post['user_id'] = \Auth::id();
                $post['credited_by'] = \Auth::user()->parent_id;
                if(!\Myhelper::can('setup_bank', \Auth::user()->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', \Auth::user()->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $post['credited_by'] = $admin->id;
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $post['credited_by'] = $admin->id;
                    }
                }
                
                $post['status'] = "pending";
                if($post->hasFile('payslips')){
                    $filename ='payslip'.\Auth::id().date('ymdhis').".".$post->file('payslips')->guessExtension();
                    $post->file('payslips')->move(public_path('deposit_slip/'), $filename);
                    $post['payslip'] = $filename;
                }
                $action = Fundreport::create($post->all());
                if($action){
                    return response()->json(['status' => "success"],200);
                }else{
                    return response()->json(['status' => "Something went wrong, please try again."],200);
                }
                break;

            case 'bank':
                $banksettlementtype = $this->banksettlementtype();

                if($banksettlementtype == "down"){
                    return response()->json(['status' => "Aeps Settlement Down For Sometime"],400);
                }

                $user = User::where('id',\Auth::user()->id)->first();

                $post['user_id'] = \Auth::id();

                if($user->account == '' && $user->bank == '' && $user->ifsc == ''){
                    $rules = array(
                        'amount'    => 'required|numeric|min:10',
                        'account'   => 'sometimes|required',
                        'bank'   => 'sometimes|required',
                        'ifsc'   => 'sometimes|required'
                    );
                }else{
                    $rules = array(
                        'amount'    => 'required|numeric|min:10'
                    );

                    $post['account'] = $user->account;
                    $post['bank']    = $user->bank;
                    $post['ifsc']    = $user->ifsc;
                }
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                if($user->account == '' && $user->bank == '' && $user->ifsc == ''){
                    User::where('id',\Auth::user()->id)->update(['account' => $post->account, 'bank' => $post->bank, 'ifsc'=>$post->ifsc]);
                }

                $settlerequest = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->count();
                if($settlerequest > 0){
                    return response()->json(['status'=> "One request is already submitted"], 400);
                }
                
                if($post->amount <= 1000){
                    $post['charge'] = $this->settlementcharge1k();
                }elseif($post->amount > 1000 && $post->amount <= 25000){
                    $post['charge'] = $this->settlementcharge25k();
                }else{
                    $post['charge'] = $this->settlementcharge2l();
                }

                if($user->aepsbalance - $this->aepslocked() < $post->amount + $post->charge){
                    return response()->json(['status'=>  "Low aeps balance to make this request."], 400);
                }
                


                $manualpayoutotp = \App\Model\PortalSetting::where('code', 'manualpayoutotp')->first();
                //dd($otprequired); exit;
                //$manualpayoutotp->value = "yes";
                if($manualpayoutotp->value == "yes") {
                    if($post->has('otp') && $post->otp != ""){
                        $otp = User::where('otpverify', $post->otp)->where('id', $user->id)->count();
                        if($otp > 0){
                            $flag = 1;
                        }else{
                            return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                        }
                    } else {

                        $otp = rand(000000, 999999);
                        User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
                        if (\Myhelper::is_template_active(2))
                        {
                            $msg = \Myhelper::get_whatsapp_content(2);
                            $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                            $send = \Myhelper::sms($user->mobile, $msg);
                            
                            //$send = 'success';
                        }
                        //dd([$user->mobile, $msg]); exit;
                        return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                       
                    }
                }



                if($banksettlementtype == "auto"){

                    $previousrecharge = Aepsfundrequest::where('account', $post->account)->where('amount', $post->amount)->where('user_id', $post->user_id)->whereBetween('created_at', [Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'), Carbon::now()->addSeconds(30)->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge){
                        return response()->json(['status'=> "Transaction Allowed After 1 Min."]);
                    } 

                    $api = Api::where('code', 'psettlement')->first();
                    
                    do {
                        $post['payoutid'] = $this->transcode().rand(111111111111, 999999999999);
                    } while (Aepsfundrequest::where("payoutid", "=", $post->payoutid)->first() instanceof Aepsfundrequest);

                    $post['status']   = "pending";
                    $post['pay_type'] = "payout";
                    $post['payoutid'] = $post->payoutid;
                    $post['payoutref']= $post->payoutid;
                    $post['create_time']= Carbon::now()->toDateTimeString();

                    try {
                        $aepsrequest = Aepsfundrequest::create($post->all());
                    } catch (\Exception $e) {
                        return response()->json(['status'=> "Duplicate Transaction Not Allowed, Please Check Transaction History"]);
                    }

                    $aepsreports['api_id'] = $api->id;
                    $aepsreports['payid']  = $aepsrequest->id;
                    $aepsreports['mobile'] = $user->mobile;
                    $aepsreports['refno']  = "success";
                    $aepsreports['aadhar'] = $post->account;
                    $aepsreports['amount'] = $post->amount;
                    $aepsreports['charge'] = $post->charge;
                    $aepsreports['bank']   = $post->bank."(".$post->ifsc.")";
                    $aepsreports['txnid']  = $post->payoutid;
                    $aepsreports['user_id']= $user->id;
                    $aepsreports['credited_by'] = $this->admin->id;
                    $aepsreports['aepstype '] = "MP";
                    
                    $aepsreports['balance']     = $user->aepsbalance;
                    $aepsreports['type']        = "debit";
                    $aepsreports['transtype']   = 'fund';
                    $aepsreports['status'] = 'success';
                    $aepsreports['remark'] = "Bank Settlement";

                    User::where('id', $aepsreports['user_id'])->decrement('aepsbalance',$aepsreports['amount']+$aepsreports['charge']);
                    //$myaepsreport = Aepsreport::create($aepsreports);
                    /*$url = $api->url;

                    $parameter = [
                        "apitxnid" => $post->payoutid,
                        "amount"   => $post->amount, 
                        "account"  => $post->account,
                        "name"     => $user->name,
                        "bank"     => $post->bank,
                        "ifsc"     => $post->ifsc,
                        "ip"       => $post->ip(),
                        "token"    => $api->username,
                        'callback' => url('api/callback/update/payout')
                    ];
                    $header = array("Content-Type: application/json");*/
                    //print_r(env('APP_ENV')); exit;
                    if(env('APP_ENV') != "local"){
                        //$result = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, 'yes', '\App\Model\Aepsfundrequest', $post->payoutid);
                        
                        $url = 'https://apibankingonetwo.icicibank.com/api/Corporate/CIB/v1/Transaction';
                        
                                        
                            $data_request = [
		                        "AGGRID" => "OTOE0159",
		                        "AGGRNAME" => "DIGISEVA",                        
		                        "CORPID" => "572583168",
		                        "USERID" => "USER1",
		                        "URN" => "SR197281790",
		                        "DEBITACC" => "000405001611",
		                        "CREDITACC" => $post->account,
		                        "IFSC" => $post->ifsc,                        
		                        "CURRENCY" => "INR",
		                        "TXNTYPE" => "IFS",
		                        "AMOUNT" => $post->amount,
		                        "PAYEENAME" => $user->name,
		                        "REMARKS" => "Bank Trasfer from DIGISEVA PAY",
		                        "UNIQUEID" => $post->txnid,
		                        "CUSTOMERINDUCED" => "N"                        
		                    ];
    
                        $json_enc = json_encode($data_request);
                        $fp1 = fopen("ICICI_PUBLIC_CERT_PROD_DEV.txt", "r");
		                $public_key = fread($fp1, 8192);
		                fclose($fp1);
                        openssl_public_encrypt($json_enc,$encrypted,$public_key, OPENSSL_PKCS1_PADDING);

		                    // Encode to base64
		                    $base64_enc = base64_encode($encrypted);
		                    
		                    // Create header
		                    $header = [
		                        'Content-type:text/plain',        
		                        'apikey:Qz9238nSqAT8kcfshS3YPNiWePY4O46O'
		                    ];  
		                    
		                    // Create url
		                    //$url = 'https://api.icicibank.com:8443/api/Corporate/CIB/v1/TransactionOTP';

		                    $curl = curl_init();
		                    
		                    curl_setopt_array($curl, array(
		                        CURLOPT_RETURNTRANSFER => true,        
		                        CURLOPT_PORT => "8443",
		                        CURLOPT_MAXREDIRS => 10,
		                        CURLOPT_TIMEOUT => 120,
		                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		                        CURLOPT_CUSTOMREQUEST => "POST",
		                        CURLOPT_POSTFIELDS => $base64_enc,
		                        CURLOPT_HTTPHEADER => $header,
		                        CURLOPT_URL => $url
		                    ));
		                    
		                    // Get response
		                    $response = curl_exec($curl);
		                    
		                    curl_close($curl);
		                    
		                    // Decode from base64
		                    $base64_dec = base64_decode($response);                        
                            //dd($base64_dec); exit;
		                    // Decrypt using private key
		                    //$fp2 = fopen("PAYVENUE_PRIVATE_KEY_CIB.txt", "r");
		                    $fp2 = fopen("ICICI_PUBLIC_CERT_PROD.txt", "r");
		                    $private_key = fread($fp2, 8192);
		                    fclose($fp2);
		                    
		                    openssl_private_decrypt($base64_dec,$decrypted,$private_key);

		                    //Log transaction
		                    $log = 'REQUEST - '.$json_enc."\n\n";
		                    $log .= 'RESPONSE - '.$decrypted."\n\n";
		                
		                    file_put_contents("logs/".$data["CORPID"].'_Transaction.txt', $log, FILE_APPEND | LOCK_EX);    
		                    
		                    $data_request['RESPONSE'] = $decrypted;
                        
                        
                        //$result = $this->callApi($url, $header, $rq);  
                        
                        dd($data_request); exit;
                        print_r($result); exit;
                        
                        
                        
                    }else{
                        $result = [
                            'error'    => true,
                            'response' => ''
                        ];
                    }

                    if($result['response'] == ''){
                        return response()->json(['status'=> "success"]);
                    }

                    $response = json_decode($result['response']);
                    if(isset($response->status) && in_array($response->status, ['TXN', 'TUP'])){
                        Aepsfundrequest::where('id', $aepsrequest->id)->update(['status' => "approved", "payoutref" => $response->rrn]);
                        return response()->json(['status'=>"success"], 200);
                    }elseif(isset($response->status) && in_array($response->status, ['ERR', 'TXF'])){
                        User::where('id', $aepsreports['user_id'])->increment('aepsbalance', $aepsreports['amount']+$aepsreports['charge']);
                        Aepsreport::where('id', $myaepsreport->id)->update(['status' => "failed", "refno" => isset($response->rrn) ? $response->rrn : $response->message]);

                        Aepsfundrequest::where('id', $aepsrequest->id)->update(['status' => "rejected"]);
                        return response()->json(['status'=> $response->message], 400);
                    }else{
                        return response()->json(['status'=> "success"]);
                    }
                }else{
                    $post['pay_type'] = "manual";
                    $request = Aepsfundrequest::create($post->all());

                    if($post->amount <= 1000){
                        $charge = $this->settlementcharge1k();
                    }elseif($post->amount > 1000 && $post->amount <= 25000){
                        $charge = $this->settlementcharge25k();
                    }else{
                        $charge = $this->settlementcharge2l();
                    }
                    User::where('id', $user->id)->decrement('aepsbalance',$post->amount+$charge);
                    
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Hello%20Admin%20New%20Cash%20Deposit%20Request%20is%20of%20Rs.%20'.$post->amount.'%20subimmted%20Manual%20Payout%20Web%20by%20User%20Name:%20'.urlencode($user->name).'%20User%20Mobile:%20'.urlencode($user->mobile).'%20User%20ID:%20'.urlencode($user->id).'',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);
                    $res = json_decode($response);




                }

                if($request){
                    return response()->json(['status'=>"success", 'message' => "Fund request successfully submitted"], 200);
                }else{
                    return response()->json(['status'=>"ERR", 'message' => "Something went wrong."], 400);
                }
                break;

            case 'wallet':
                if(!\Myhelper::can('aeps_fund_request')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }
                $settlementtype = $this->settlementtype();

                if($settlementtype == "down"){
                    return response()->json(['status' => "Aeps Settlement Down For Sometime"],400);
                }

                $rules = array(
                    'amount'    => 'required|numeric|min:1',
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $user = User::where('id',\Auth::user()->id)->first();

                $request = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->count();
                if($request > 0){
                    return response()->json(['status'=> "One request is already submitted"], 400);
                }

                if(\Auth::user()->aepsbalance - $this->aepslocked() < $post->amount){
                    return response()->json(['status'=>  "Low aeps balance to make this request"], 400);
                }

                $post['user_id'] = \Auth::id();

                if($settlementtype == "auto"){
                    $previousrecharge = Aepsfundrequest::where('type', $post->type)->where('amount', $post->amount)->where('user_id', $post->user_id)->whereBetween('created_at', [Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge > 0){
                        return response()->json(['status'=> "Transaction Allowed After 5 Min."]);
                    }

                    $post['status'] = "approved";
                    $load = Aepsfundrequest::create($post->all());
                    $payee = User::where('id', \Auth::id())->first();
                    User::where('id', $payee->id)->decrement('aepsbalance', $post->amount);
                    $inserts = [
                        "mobile"  => $payee->mobile,
                        "amount"  => $post->amount,
                        "bank"    => $payee->bank,
                        'txnid'   => date('ymdhis'),
                        'refno'   => $post->refno,
                        "user_id" => $payee->id,
                        "credited_by" => $user->id,
                        "balance"     => $payee->aepsbalance,
                        'type'        => "debit",
                        'transtype'   => 'fund',
                        'status'      => 'success',
                        'remark'      => "Move To Wallet Request",
                        'payid'       => "Wallet Transfer Request",
                        'aadhar'      => $payee->account
                    ];

                    Aepsreport::create($inserts);

                    if($post->type == "wallet"){
                        $provide = Provider::where('recharge1', 'aepsfund')->first();
                        User::where('id', $payee->id)->increment('mainwallet', $post->amount);
                        $insert = [
                            'number' => $payee->account,
                            'mobile' => $payee->mobile,
                            'provider_id' => $provide->id,
                            'api_id' => $this->fundapi->id,
                            'amount' => $post->amount,
                            'charge' => '0.00',
                            'profit' => '0.00',
                            'gst' => '0.00',
                            'tds' => '0.00',
                            'txnid' => $load->id,
                            'payid' => $load->id,
                            'refno' => $post->refno,
                            'description' =>  "Aeps Fund Recieved",
                            'remark' => $post->remark,
                            'option1' => $payee->name,
                            'status' => 'success',
                            'user_id' => $payee->id,
                            'credit_by' => $payee->id,
                            'rtype' => 'main',
                            'via' => 'portal',
                            'balance' => $payee->mainwallet,
                            'trans_type' => 'credit',
                            'product' => "fund request"
                        ];

                        Report::create($insert);
                    }
                }else{
                    $load = Aepsfundrequest::create($post->all());
                }

                if($load){
                    return response()->json(['status' => "success"],200);
                }else{
                    return response()->json(['status' => "fail"],200);
                }
                break;
                
            case 'aepstransfer':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $user = User::where('id',\Auth::user()->id)->first();
                if($user->aepsbalance - $this->aepslocked() < $post->amount){
                    return response()->json(['status' => "Insufficient Aeps Wallet Balance"],400);
                }

                $request = Aepsfundrequest::find($post->id);
                $action  = Aepsfundrequest::where('id', $post->id)->update(['status'=>$post->status, 'remark'=> $post->remark]);
                $payee = User::where('id', $request->user_id)->first();

                if($action){
                    if($post->status == "approved" && $request->status == "pending"){
                        $settlementcharge = $this->settlementcharge();
                        //User::where('id', $payee->id)->decrement('aepsbalance', $request->amount + $settlementcharge);

                        $inserts = [
                            "mobile"  => $payee->mobile,
                            "amount"  => $request->amount,
                            'charge'  => $settlementcharge,
                            "bank"    => $payee->bank,
                            'txnid'   => $request->id,
                            'refno'   => $post->refno,
                            "user_id" => $payee->id,
                            "credited_by" => $user->id,
                            "balance"     => $payee->aepsbalance,
                            'type'        => "debit",
                            'transtype'   => 'fund',
                            'aepstype'   => 'MP',
                            'status'      => 'success',
                            'remark'      => "Move To ".ucfirst($request->type)." Request",
                        ];

                        if($request->type == "wallet"){
                            $inserts['payid'] = "Wallet Transfer Request";
                            $inserts["aadhar"]= $payee->aadhar;
                        }else{
                            $inserts['payid'] = $payee->bank." ( ".$payee->ifsc." )";
                            $inserts['aadhar'] = $payee->account;
                        }

                        Aepsreport::create($inserts);

                        if($request->type == "wallet"){
                            $provide = Provider::where('recharge1', 'aepsfund')->first();
                            User::where('id', $payee->id)->increment('mainwallet', $request->amount);
                            $insert = [
                                'number' => $payee->mobile,
                                'mobile' => $payee->mobile,
                                'provider_id' => $provide->id,
                                'api_id' => $this->fundapi->id,
                                'amount' => $request->amount,
                                'charge' => '0.00',
                                'profit' => '0.00',
                                'gst' => '0.00',
                                'tds' => '0.00',
                                'txnid' => $request->id,
                                'payid' => $request->id,
                                'refno' => $post->refno,
                                'description' =>  "Aeps Fund Recieved",
                                'remark' => $post->remark,
                                'option1' => $payee->name,
                                'status' => 'success',
                                'user_id' => $payee->id,
                                'credit_by' => $user->id,
                                'rtype' => 'main',
                                'via' => 'portal',
                                'balance' => $payee->mainwallet,
                                'trans_type' => 'credit',
                                'product' => "fund request"
                            ];

                            Report::create($insert);
                            
                            $msg = urlencode('Your Wallet Creditd with amount '.$request->amount.' and your Updated amount is '.$payee->mainwallet.', Txn number is '.$request->txnid.'');
                            $send = \Myhelper::sms($payee->mobile, $msg);
                        }
                    }elseif($post->status == "rejected"){
                        $settlementcharge = $this->settlementcharge();
                        User::where('id', $payee->id)->increment('aepsbalance', $request->amount + $settlementcharge);
                        $inserts = [
                            "mobile"  => $payee->mobile,
                            "amount"  => $request->amount,
                            'charge'  => $settlementcharge,
                            "bank"    => $payee->bank,
                            'txnid'   => $request->id,
                            'refno'   => $post->refno,
                            "user_id" => $payee->id,
                            "credited_by" => $user->id,
                            "balance"     => $payee->aepsbalance,
                            'type'        => "credit",
                            'transtype'   => 'fund',
                            'aepstype'   => 'MP',
                            'status'      => 'refunded',
                            'remark'      => "Move To ".ucfirst($request->type)." Request",
                        ];

                        if($request->type == "wallet"){
                            $inserts['payid'] = "Wallet Transfer Request";
                            $inserts["aadhar"]= $payee->aadhar;
                        }else{
                            $inserts['payid'] = $payee->bank." ( ".$payee->ifsc." )";
                            $inserts['aadhar'] = $payee->account;
                        }

                        Aepsreport::create($inserts);
                    }
                    return response()->json(['status'=> "success"], 200);
                }else{
                    return response()->json(['status'=> "fail"], 400);
                }

                break;
            
            case 'loadwallet':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }
                $action = User::where('id', \Auth::id())->increment('mainwallet', $post->amount);
                if($action){
                    $insert = [
                        'number' => \Auth::user()->mobile,
                        'mobile' => \Auth::user()->mobile,
                        'provider_id' => $post->provider_id,
                        'api_id' => $this->fundapi->id,
                        'amount' => $post->amount,
                        'charge' => '0.00',
                        'profit' => '0.00',
                        'gst' => '0.00',
                        'tds' => '0.00',
                        'apitxnid' => NULL,
                        'txnid' => date('ymdhis'),
                        'payid' => NULL,
                        'refno' => NULL,
                        'description' => NULL,
                        'remark' => $post->remark,
                        'option1' => NULL,
                        'option2' => NULL,
                        'option3' => NULL,
                        'option4' => NULL,
                        'status' => 'success',
                        'user_id' => \Auth::id(),
                        'credit_by' => \Auth::id(),
                        'rtype' => 'main',
                        'via' => 'portal',
                        'adminprofit' => '0.00',
                        'balance' => \Auth::user()->mainwallet,
                        'trans_type' => 'credit',
                        'product' => "fund ".$post->type
                    ];
                    $action = Report::create($insert);
                    if($action){
                        return response()->json(['status' => "success"], 200);
                    }else{
                        return response()->json(['status' => "Technical error, please contact your service provider before doing transaction."],400);
                    }
                }else{
                    return response()->json(['status' => "Fund transfer failed, please try again."],400);
                }
                break;
            
            default:
                # code...
                break;
        }
    }

    public function paymentAction($post)
    {
        $user = User::where('id', $post->user_id)->first();

        if($post->type == "transfer" || $post->type == "request"){
            $action = User::where('id', $post->user_id)->increment('mainwallet', $post->amount);
        }else{
            $action = User::where('id', $post->user_id)->decrement('mainwallet', $post->amount);
        }

        if($action){
            if($post->type == "transfer" || $post->type == "request"){
                $post['trans_type'] = "credit";
            }else{
                $post['trans_type'] = "debit";
            }

            $insert = [
                'number' => $user->mobile,
                'mobile' => $user->mobile,
                'provider_id' => $post->provider_id,
                'api_id' => $this->fundapi->id,
                'amount' => $post->amount,
                'charge' => '0.00',
                'profit' => '0.00',
                'gst' => '0.00',
                'tds' => '0.00',
                'apitxnid' => NULL,
                'txnid' => $post->txnid,
                'payid' => NULL,
                'refno' => $post->refno,
                'description' => NULL,
                'remark' => $post->remark,
                'option1' => $post->option1,
                'option2' => $post->option2,
                'option3' => $post->option3,
                'option4' => NULL,
                'status' => 'success',
                'user_id' => $user->id,
                'credit_by' => \Auth::id(),
                'rtype' => 'main',
                'via' => 'portal',
                'adminprofit' => '0.00',
                'balance' => $user->mainwallet,
                'trans_type' => $post->trans_type,
                'product' => "fund ".$post->type
            ];
            $action = Report::create($insert);
            if($action){
                return $this->paymentActionCreditor($post);
            }else{
                return response()->json(['status' => "Technical error, please contact your service provider before doing transaction."],400);
            }
        }else{
            return response()->json(['status' => "Fund transfer failed, please try again."],400);
        }
    }

    public function paymentActionCreditor($post)
    {
        $payee = $post->user_id;
        $user = User::where('id', \Auth::id())->first();
        if($post->type == "transfer" || $post->type == "request"){
            $action = User::where('id', $user->id)->decrement('mainwallet', $post->amount);
        }else{
            $action = User::where('id', $user->id)->increment('mainwallet', $post->amount);
        }

        if($action){
            if($post->type == "transfer" || $post->type == "request"){
                $post['trans_type'] = "debit";
            }else{
                $post['trans_type'] = "credit";
            }

            $insert = [
                'number' => $user->mobile,
                'mobile' => $user->mobile,
                'provider_id' => $post->provider_id,
                'api_id' => $this->fundapi->id,
                'amount' => $post->amount,
                'charge' => '0.00',
                'profit' => '0.00',
                'gst' => '0.00',
                'tds' => '0.00',
                'apitxnid' => NULL,
                'txnid' => $post->txnid,
                'payid' => NULL,
                'refno' => $post->refno,
                'description' => NULL,
                'remark' => $post->remark,
                'option1' => $post->option1,
                'option2' => $post->option2,
                'option3' => $post->option3,
                'option4' => NULL,
                'status' => 'success',
                'user_id' => $user->id,
                'credit_by' => $payee,
                'rtype' => 'main',
                'via' => 'portal',
                'adminprofit' => '0.00',
                'balance' => $user->mainwallet,
                'trans_type' => $post->trans_type,
                'product' => "fund ".$post->type
            ];

            $action = Report::create($insert);
            if($action){
                return response()->json(['status' => "success"], 200);
            }else{
                return response()->json(['status' => "Technical error, please contact your service provider before doing transaction."],400);
            }
        }else{
            return response()->json(['status' => "Technical error, please contact your service provider before doing transaction."],400);
        }
    }
    
    
    
    
    
    
}
