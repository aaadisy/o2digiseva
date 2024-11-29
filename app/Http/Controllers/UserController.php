<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use File;
use DB;
use App\Model\Permission;
use App\Model\Aepsreport;
use App\Model\Api;
use App\Model\Role;
use App\Classes\Authenticator;
use Session;
use App\Model\Apilog;
use App\Model\Fingagent;
use Illuminate\Support\Facades\Log;

use App\Model\Report;
use App\Model\Provider;

class UserController extends Controller
{

     public function getTds($amount)
    {
        return round($amount * 5/100, 2);
    }

    public function getGst($amount)
    {
        $gst = 0;//5;//\Auth::user()->gst;
        return round($amount * $gst/100, 2);
    }

    function performThreeWayReconciliationAP()
    {
        $aepsapi = Api::where('code', 'aeps')->first();
        $url = 'https://fpuat.tapits.in/fpcollectservice_uat/api/threeway/aggregators';
        $url = 'https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators/ap';
        $superMerchantLoginId = 'Digisevad'; // Replace with your super merchant login id
        $superMerchantId = '955'; // Replace with your super merchant id
        $secretKey = '3bebc440a1be54903c3136616a42cbbae58036f847fd72f58e9faaf5aa28c958'; // Replace with your secret key provided by Fingpay team
        $serviceType = 'AP';

        
        
         $transactionsData = Aepsreport::where('threeway', '0')
        ->where('withdrawType', 'CW')
        ->where('aepstype', 'M')
        ->where('product', 'aeps')
        ->limit(1000)
        ->get();
        
       

        
        $transactions = [];

        foreach ($transactionsData as $data) {
            $responseCode = ($data->status == 'success') ? '00' : 'failed';
        
          $transactionRrn = strpos($data->refno, ' ') !== false ? null : $data->refno;
          
$formattedDate = date('d-m-Y', strtotime($data->created_at));

        $transaction = [
            "merchantTransactionId" => $data->txnid,
            "fingpayTransactionId" => $data->payid,
            "transactionRrn" => $transactionRrn,
            "responseCode" => $responseCode,
            "transactionDate" => date('d-m-Y'),
            "serviceType" => $serviceType
        ];
        
            $transactions[] = $transaction;
            
        }
        

        $headers = [
            'Content-Type: application/json',
            'txnDate: ' . date('d/m/Y H:i:s'), // date of the transaction
            'hash: ' . $this->generateHash($transactions, $superMerchantLoginId, $secretKey),
            'superMerchantLoginId: ' . $superMerchantLoginId,
            'superMerchantid: ' . $superMerchantId
        ];

        $body = json_encode($transactions);
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            Log::error('Error occurred during 3-way reconciliation: HTTP ' . $httpCode);
            return null;
        }

        curl_close($ch);
        
        $log = \Myhelper::apiLog($url, 'POST', 'AP3way',$headers, $body, $response);
        $responsearr = json_decode($response);
      if ($responsearr && isset($responsearr->apiStatus) && $responsearr->apiStatus == true) {
    // Extract merchantTransactionId values from $responsearr->data
    $transactionIds = array_map(function($data) {
        return $data->merchantTransactionId;
    }, $responsearr->data);

    // Perform bulk update using transactionIds
    Aepsreport::whereIn('txnid', $transactionIds)
        ->update(['threeway' => '1']);
}



        
        return json_decode($response, true);
    }
    function performThreeWayReconciliationMATM()
    {
        $aepsapi = Api::where('code', 'aeps')->first();
        $url = 'https://fpuat.tapits.in/fpcollectservice_uat/api/threeway/aggregators';
        $url = 'https://fpanalytics.tapits.in/fpcollectservice/api/ma/threeway/aggregators';
        $superMerchantLoginId = 'Digisevad'; // Replace with your super merchant login id
        $superMerchantId = '955'; // Replace with your super merchant id
        $secretKey = '3bebc440a1be54903c3136616a42cbbae58036f847fd72f58e9faaf5aa28c958'; // Replace with your secret key provided by Fingpay team
        $serviceType = 'CW';

        
        
       $transactionsData = Aepsreport::where('threeway', '0')
        ->where('withdrawType', 'CW')
        ->where('aepstype', 'CW')
        ->where('product', 'matm')
        ->limit(10000)
        ->get();
        
        

        
        $transactions = [];

        foreach ($transactionsData as $data) {
            $responseCode = ($data->status == 'success') ? '00' : 'failed';
        
            $transaction = [
                "merchantTransactionId" => $data->txnid,
                "fingpayTransactionId" => $data->payid,
                "transactionRrn" => $data->refno,
                "responseCode" => $responseCode,
                "transactionDate" => date('d-m-Y'),
                "serviceType" => $serviceType
            ];
        
            $transactions[] = $transaction;
            
        }
        

        $headers = [
            'Content-Type: application/json',
            'txnDate: ' . date('d/m/Y H:i:s'), // date of the transaction
            'hash: ' . $this->generateHash($transactions, $superMerchantLoginId, $secretKey),
            'superMerchantLoginId: ' . $superMerchantLoginId,
            'superMerchantid: ' . $superMerchantId
        ];

        $body = json_encode($transactions);
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
    
       
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            Log::error('Error occurred during 3-way reconciliation: HTTP ' . $httpCode);
            return null;
        }

        curl_close($ch);
        $log = \Myhelper::apiLog($url, 'POST', 'MATM3way',$headers, $body, $response);
        
        $responsearr = json_decode($response);
      if ($responsearr && isset($responsearr->apiStatus) && $responsearr->apiStatus == true) {
    // Extract merchantTransactionId values from $responsearr->data
    $transactionIds = array_map(function($data) {
        return $data->merchantTransactionId;
    }, $responsearr->data);

    // Perform bulk update using transactionIds
    Aepsreport::whereIn('txnid', $transactionIds)
        ->update(['threeway' => '1']);
}



        
        return json_decode($response, true);
    }
    function performThreeWayReconciliation()
    {
        $aepsapi = Api::where('code', 'aeps')->first();
        $url = 'https://fpuat.tapits.in/fpcollectservice_uat/api/threeway/aggregators';
        $url = 'https://fpanalytics.tapits.in/fpcollectservice/api/threeway/aggregators';
        $superMerchantLoginId = 'Digisevad'; // Replace with your super merchant login id
        $superMerchantId = '955'; // Replace with your super merchant id
        $secretKey = '3bebc440a1be54903c3136616a42cbbae58036f847fd72f58e9faaf5aa28c958'; // Replace with your secret key provided by Fingpay team
        $serviceType = 'CW';

        
        
        $transactionsData = Aepsreport::where('threeway', '0')
        ->where('withdrawType', 'CW')
        ->where('aepstype', 'CW')
        ->where('product', 'aeps')
        ->limit(10000)
        ->get();
        
        

        
        $transactions = [];

        foreach ($transactionsData as $data) {
            $responseCode = ($data->status == 'success') ? '00' : 'failed';
        
            $transaction = [
                "merchantTransactionId" => $data->txnid,
                "fingpayTransactionId" => $data->payid,
                "transactionRrn" => $data->refno,
                "responseCode" => $responseCode,
                "transactionDate" => date('d-m-Y'),
                "serviceType" => $serviceType
            ];
        
            $transactions[] = $transaction;
            
        }
        

        $headers = [
            'Content-Type: application/json',
            'txnDate: ' . date('d/m/Y H:i:s'), // date of the transaction
            'hash: ' . $this->generateHash($transactions, $superMerchantLoginId, $secretKey),
            'superMerchantLoginId: ' . $superMerchantLoginId,
            'superMerchantid: ' . $superMerchantId
        ];

        $body = json_encode($transactions);
       
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
     
       
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            Log::error('Error occurred during 3-way reconciliation: HTTP ' . $httpCode);
            return null;
        }

        curl_close($ch);
        $log = \Myhelper::apiLog($url, 'POST', 'CW3way',$headers, $body, $response);
        
        $responsearr = json_decode($response);
      if ($responsearr && isset($responsearr->apiStatus) && $responsearr->apiStatus == true) {
    // Extract merchantTransactionId values from $responsearr->data
    $transactionIds = array_map(function($data) {
        return $data->merchantTransactionId;
    }, $responsearr->data);

    // Perform bulk update using transactionIds
    Aepsreport::whereIn('txnid', $transactionIds)
        ->update(['threeway' => '1']);
}



        
        return json_decode($response, true);
    }

    function generateHash(array $transactions, string $superMerchantLoginId, string $secretKey)
    {
        // Step 1: Concatenate the values
        $requestBody = json_encode($transactions);
     
          $dataToHash = $requestBody . $superMerchantLoginId . $secretKey;
      
        // Step 2: Hash the concatenated string using SHA-256 algorithm
        $hashedData = hash('sha256', $dataToHash,true);

        // Step 3: Encode the hash using Base64 encoding
        $hashedDataEncoded = base64_encode($hashedData);

        return $hashedDataEncoded;
    }
    
    
     public function matmstatusCheckCallback(Request $post) {
      
    

    
  
    
    $reports= Aepsreport::where('product', 'matm')->where('status','success')->get();
    
    if(!$reports) {
        return response()->json(['status' => "ERR", "message" => "Report not found"]);
    }
    
    foreach($reports as $report){
        $user = User::where('id', $report->user_id)->first();
         $agent = Fingagent::where('user_id', $report->user_id)->first();
         
         

   
   

    // Define request parameters
    $merchantTranId        = $report->txnid;
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
        
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $response = json_decode($response);
 $profit = 0;
            $tds = 0;
    if ($response && isset($response->status) && $response->status == true && $response->data[0]->transactionStatus == '1') {
        
        print_r($response);
        exit;
        
       
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
            'refno'  => isset($response->data[0]->fingpayTransactionId)?$response->data[0]->fingpayTransactionId:'',
            'payid'  => isset($response->data[0]->bankRRN)?$response->data[0]->bankRRN:'',
            'remark'   => "Request Completed Successfuly",
            'number'  =>  isset($response->data[0]->cardNumber)?$report->data->cardNumber:'',
            'option1'=>  isset($response->data[0]->bankRRN)?$response->data[0]->bankRRN:'',
            'trans_type'    => "credit",
            'api_id'  => '9',
            'credit_by' => $post->user_id,
            'charge' => $profit,
            "tds"    => $tds,
            "status" => "success",
            'rtype'       => 'commission',
            'option2'   => 'transaction',
            'product'   => 'matm',
            "option3"        => isset($response->data[0]->cardNumber)?$response->data[0]->cardNumber:'',
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
                
                
            
        
            
    } else {
        // Handle failure case
         $dd = Aepsreport::where('id', $report->id)->update([
                'charge' => 0,
                "tds"    => 0,
                "status" => "failed"
            ]);
        
        
    }
    }

   
}


    public function login(Request $post)
    {
            

        
        $user = User::where('mobile', $post->mobile)->first();
        if(!$user){
            return response()->json(['status' => "Your aren't registred with us." ], 400);
        }
        $company = \App\Model\Company::where('id', $user->company_id)->first();
        $otprequired = \App\Model\PortalSetting::where('code', 'otplogin')->first();

        if(!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password])){
            return response()->json(['status'=> 'Username or password is incorrect'], 400);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password,'status'=> "active"])) {
            return response()->json(['status' => 'Your account currently de-activated, please contact administrator'], 400);
        }
        $wotprequired = \App\Model\PortalSetting::where('code', 'wotplogin')->first();
        //dd($otprequired); exit;
        //$wotprequired->value = "yes";
        if($wotprequired->value == "yes") {
            if($post->has('potp') && $post->potp != ""){
                $otp = User::where('otpverify', $post->potp)->where('mobile', $post->mobile)->count();
                if($otp > 0){
                    $flag = 1;
                }else{
                    return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                }
            } else {

               $otp = rand(100000, 999999);
                User::where('mobile', $post->mobile)->update(['otpverify'=>$otp]);
                if (\Myhelper::is_template_active(2))
                {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                    $send = \Myhelper::sms($post->mobile, $msg);
                    
                    //$send = 'success';
                }
                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
               
            }
        }   
        if($post->has('otp'))
        {
            $Authenticator = new Authenticator();
            $checkResult = $Authenticator->verifyCode(Session::get('auth_secret'), $post->otp, 2);    
            if($checkResult)
            {
                if (\Auth::attempt(['mobile' =>$post->mobile, 'password' =>$post->password, 'status'=> "active"])) {

                   User::where('mobile', $post->mobile)->update([
    'lat' => number_format($post->latitude, 7, '.', ''),
    'long' => number_format($post->longitude, 7, '.', '')
]);

                $user = explode(' ',ucwords(\Auth::user()->name))[0].' (Id - '.\Auth::id();
                $msg = ''.$user.' loggedin successfully';
                //\Myhelper::save_notification($msg);
                Session::forget('auth_secret');
                return response()->json(['status' => 'Login'], 200);
                }else{
                    return response()->json(['status' => 'Something went wrong, please contact administrator'], 400);
                }
                
            }
            else
            {
                return response()->json(['status' => 'otperror'], 400);
            }
            exit;
        }
        
        if($otprequired->value == "yes" && !$post->has('otp')){
            
                $Authenticator = new Authenticator();
                if (!Session::get('auth_secret')) {
                    $secret = $Authenticator->generateRandomSecret();
                    Session::put('auth_secret', $secret);

                }
                
                
                $qrCodeUrl = $Authenticator->getQR('DIGISeva', Session::get('auth_secret'));
                if($qrCodeUrl){
                    
                    return response()->json(['status' => 'otpsent','qrcode'=>$qrCodeUrl], 200);
                }else{
                    return response()->json(['status' => 'Please contact your service provider provider'], 400);
                }
            
        }else{
            if (\Auth::attempt(['mobile' =>$post->mobile, 'password' =>$post->password, 'status'=> "active"])) {

                
                User::where('mobile', $post->mobile)->update([
    'lat' => number_format($post->latitude, 7, '.', ''),
    'long' => number_format($post->longitude, 7, '.', '')
]);

                $user = explode(' ',ucwords(\Auth::user()->name))[0].' (Id - '.\Auth::id();
                $msg = ''.$user.' loggedin successfully';
                //\Myhelper::save_notification($msg);
                return response()->json(['status' => 'Login'], 200);
            }else{
                return response()->json(['status' => 'Something went wrong, please contact administrator'], 400);
            }
        }
    }
    
    public function sendOTP(Request $post)
{
    $user = User::where('mobile', $post->mobile)->first();
    
    if (!$user) {
        return response()->json(['status' => "NOT_REGISTERED", 'message' => "You aren't registered with us."], 500);
    }

    $otp = rand(100000, 999999);
    User::where('mobile', $post->mobile)->update(['otpverify' => $otp]);

    if (\Myhelper::is_template_active(2)) {
        $msg = \Myhelper::get_whatsapp_content(2);
        $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
        $send = \Myhelper::sms($post->mobile, $msg);
    }

    return response()->json(['status' => 'OTP_SENT', 'message' => 'OTP sent to your registered mobile number.']);
}


    
    public function deleteAccount(Request $post)
{
    $user = User::where('mobile', $post->mobile)->first();
    
    if (!$user) {
        return response()->json(['status' => "NOT_REGISTERED", 'message' => "You aren't registered with us."]);
    }

    if ($user->otpverify != $post->otp) {
        return response()->json(['status' => 'InvalidOTP', 'message' => 'Invalid OTP. Please try again.']);
    }

    // If OTP is correct, delete the account or proceed further
    return response()->json(['status' => 'Deleted', 'message' => 'Your account has been deleted successfully.']);
}




    public function logout(Request $request)
    {
        $user = explode(' ',ucwords(\Auth::user()->name))[0].' (Id - '.\Auth::id();
                $msg = ''.$user.' loggedout successfully';
                //\Myhelper::save_notification($msg);
        \Auth::guard()->logout();
        $request->session()->invalidate();
        return redirect('/');
    }

    public function passwordReset(Request $post)
    {
        $rules = array(
            'type' => 'required',
            'mobile'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        if($post->type == "request" ){
            $user = \App\User::where('mobile', $post->mobile)->first();
            if($user){
                $otp = rand(100000, 999999);
                $content = "Dear partner , your password reset token is ".$otp;
                if(\Myhelper::is_template_active(2))
                    {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg =    \Myhelper::filter_parameters($msg,"",$otp,$otp);
                    $send = \Myhelper::sms($post->mobile, $msg);
                    }
                    //dd($send); exit;
                $otpmailid = \App\Model\PortalSetting::where('code', 'otpsendmailid')->first();
                $otpmailname = \App\Model\PortalSetting::where('code', 'otpsendmailname')->first();
                //$mail = \Myhelper::mail('mail.password', ["token" => $otp, "name" => $user->name], $user->email, $user->name, $otpmailid->value, $otpmailname->value, "Reset Password");
                $mail = "success";
                if($send == "success" || $mail == "success"){
                    \App\User::where('mobile', $post->mobile)->update(['remember_token'=> $otp]);
                    return response()->json(['status' => 'preotp', 'message' => "Password reset token sent successfully"], 200);
                }else{
                    return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
                }
            }else{
                return response()->json(['status' => 'ERR', 'message' => "You aren't registered with us"], 400);
            }
        }
        
         else if($post->type == "newpass" )
        {
                     $user = \App\User::where('mobile', $post->mobile)->get();
            if($user->count() == 1){
                    $otp = rand(100000, 999999);
                   
                    if(\Myhelper::is_template_active(2))
                    {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg =    \Myhelper::filter_parameters($msg,"",$otp,$otp);
                    $send = \Myhelper::sms($post->mobile, $msg);
                    }
                    if($send == 'success'){
                        User::where('mobile', $post->mobile)->update(['password' => bcrypt($otp)]);
                        return response()->json(['status' => 'New Password Sent Successfully'], 400);
                    }else{
                        return response()->json(['status' => 'Please contact your service provider provider'], 400);
                    }
            }
            else{
                return response()->json(['status' => 'ERR', 'message' => "Number Not Registered With Us"], 400);
            }
        }
        
        else{
            $user = \App\User::where('mobile', $post->mobile)->where('remember_token' , $post->otp)->get();
            if($user->count() == 1){
                $update = \App\User::where('mobile', $post->mobile)->update(['password' => bcrypt($post->password), 'passwordold' => $post->password]);
                if($update){
                    return response()->json(['status' => "TXN", 'message' => "Password reset successfully"], 200);
                }else{
                    return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
                }
            }else{
                return response()->json(['status' => 'ERR', 'message' => "Please enter valid token"], 400);
            }
        }  
    }
    
    public function signupstore(Request $post)
    {
        $rules = array(
            'name' => 'required',
            'email' => 'required',
            'mobile'  =>'required|numeric',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'shopname' => 'required',
            'pancard' => 'required',
            'aadharcard' => 'required',
            'role_id'=> 'required'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }
         if(\App\User::where('mobile', $post->mobile)->count() > 0){
             return response()->json(['status'=>'Mobile Number Already Registed'], 200);
         }
         if(\App\User::where('email', $post->email)->count() > 0){
             return response()->json(['status'=>'Email Address Already Registed'], 200);
         }
        unset($post->mainwallet);
        unset($post->aepsbalance);
         $role = Role::where('id', $post->role_id)->first();
        $post['id'] = "new";
        $post['parent_id'] = 1;
        $post['kyc'] = "pending";
        $post['password'] = bcrypt($post->mobile);

       
        $post['company_id'] = '1';
        if($post->hasFile('aadharcardpics')){
            $filename ='addhar'.\Auth::id().date('ymdhis').".".$post->file('aadharcardpics')->guessExtension();
            $post->file('aadharcardpics')->move(public_path('kyc/'), $filename);
            $post['aadharcardpic'] = $filename;
        }

        if($post->hasFile('pancardpics')){
            $filename ='pan'.\Auth::id().date('ymdhis').".".$post->file('pancardpics')->guessExtension();
            $post->file('pancardpics')->move(public_path('kyc/'), $filename);
            $post['pancardpic'] = $filename;
        }

        if (!$post->has('scheme_id')) {
            $scheme = \DB::table('default_permissions')->where('type', 'scheme')->where('role_id', $post->role_id)->first();
            if($scheme){
                $post['scheme_id'] = $scheme->permission_id;
            }
        }

        $response = User::updateOrCreate(['id'=> $post->id], $post->all());
        
        if($response){
            
            
            $permissions = \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $post->role_id)->get();
            if(sizeof($permissions) > 0){
                foreach ($permissions as $permission) {
                    $insert = array('user_id'=> $response->id , 'permission_id'=> $permission->permission_id);
                    $inserts[] = $insert;
                }
                \DB::table('user_permissions')->insert($inserts);
            }
            
            if(\App\User::where('mobile', $response->mobile)->count() > 0){
                $newuser = \App\User::where('mobile', $response->mobile)->first();
                
                    
                    $otp = rand(100000, 999999);
                    User::where('mobile', $response->mobile)->update(['password' => bcrypt($otp)]);
                    
                    
                    if(\Myhelper::is_template_active(1))
                    {
                            $msg = \Myhelper::get_whatsapp_content(1);
                            $msg =    \Myhelper::filter_parameters($msg,$response->mobile,$otp,"");
                            $send = \Myhelper::sms($newuser->mobile, $msg);
                    }
                    
                    $msg = "Hello Admin New Member is registered on Portal with mobile number ".$newuser->mobile."";
                    $send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                    
                    
                    
            }
            
            
    		return response()->json(['status'=>'success'], 200);
    	}else{
    		return response()->json(['status'=>'fail'], 400);
    	}
        
         
    }
    
    public function websitecallback(Request $request)
    {
        
        $content = "Dear Admin , A website visitor is droped his number for enquiry Please call on this number for more details ".$request->number;
        $send = \Myhelper::sms(\Myhelper::adminphone(), $content);
        if($send)
        {
            return  redirect()->back()->with('success','Enquiry Submitted SuccessFully');  
        }
        else
        {
            return  redirect()->back()->with('error','Error While sending you Enquiry');  
        }
        
            
        
        
    }

    public function createFile($file, $data){
        $data = json_encode($data);
        $file = '2_'.$file.'_file.txt';
        $destinationPath=public_path()."/fingmatm/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function paysprintCallback(Request $post){
        $apilog=Apilog::truncate();
        return $apilog;
        $res = $apilog->delete(); //returns true/false
        return $res;
        $this->createFile("paysprintCallback", $post->all());
        if($post->event == "MERCHANT_ONBOARDING"){
            $this->createFile('MERCHANT_ONBOARDING', $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }elseif($post->event == "RECHARGE_SUCCESS"){
            $this->createFile('RECHARGE_SUCCESS_'.$post->referenceid, $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }elseif($post->event == "RECHARGE_FAILURE"){
            $this->createFile('RECHARGE_FAILURE_'.$post->referenceid, $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }elseif($post->event == "BILLPAY_SUCCESS"){
            $this->createFile('BILLPAY_SUCCESS_'.$post->referenceid, $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }elseif($post->event == "BILLPAY_FAILURE"){
            $this->createFile('BILLPAY_FAILURE_'.$post->referenceid, $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }elseif($post->event == "DMT"){
            $this->createFile('DMT_'.$post->referenceid, $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }else{
            $this->createFile('other_paysprintCallback', $post->all());
            return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);
        }
    }

    public function fingCallBack(Request $post){
        $this->createFile(rand(0000, 999999)."fingCallBack", $post->all());
        //$jsscd = json_decode(file_get_contents("php://input"));
        $dd = Aepsreport::where('txnid', $post->merchantRefNo)->update([
                'amount' => $post->amount
            ]);
        //dd($dd); exit;
        return response()->json(['status' => '200', 'message' => "Transaction completed successfully"], 200);   
    }
    
    public function unreadnotifications()
    {
       if(\Myhelper::hasRole('admin')) 
       {
           $notifications = DB::table('notifications')->orderBy('id', 'DESC')->where('seen',0)->limit('20')->get();
           $data['notifications_count'] = DB::table('notifications')->orderBy('id', 'DESC')->where('seen',0)->limit('10')->count();
           $data['notifications'] = "";
           foreach($notifications as $notification) {
          $data['notifications'] .=  '<div class = "sec new">
                   <a href = "#">
                   
                   <div class = "txt">'.$notification->content.'</div>
                  <div class = "txt sub">'.$notification->created_at.'</div>
                   </a>
                </div>';
           }
           
           return response()->json(['status' => '200', 'count' => $data['notifications_count'], 'notifications'=>$data['notifications']], 200);
       }
       else
       {
           $notifications = DB::table('notifications')->orderBy('id', 'DESC')->where('seen',0)->where('user_id',\Auth::id())->limit('10')->get();
           $data['notifications_count'] = DB::table('notifications')->orderBy('id', 'DESC')->where('seen',0)->where('user_id',\Auth::id())->limit('10')->count();
           $data['notifications'] = "";
           foreach($notifications as $notification) {
          $data['notifications'] .=  '<div class = "sec new">
                   <a href = "#">
                   
                   <div class = "txt">'.$notification->content.'</div>
                  <div class = "txt sub">'.$notification->created_at.'</div>
                   </a>
                </div>';
           }
           
           return response()->json(['status' => '200', 'count' => $data['notifications_count'], 'notifications'=>$data['notifications']], 200);
       }
       
       
    }
}
