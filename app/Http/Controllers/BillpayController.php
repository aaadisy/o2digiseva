<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provider;
use App\Model\Report;
use App\Model\Aepsreport;

use Carbon\Carbon;
use App\User;
use App\Model\Api;
use File;
use DB;
use App\Model\PortalSetting;
use App\Model\Mahabank;


class BillpayController extends Controller
{

    protected $api;

    public function __construct()
    {
        $this->api = Api::where('code', 'billpay')->first();
    }
    
    public function createFile($file, $data){
        $data = json_encode($data);
        $file = 'bill_pay_'.$file.'_file.txt';
        $destinationPath=public_path()."/bill_logs/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function index($type)
    {
        if(!\Myhelper::service_active('bbps_service'))
        {

            return  redirect()->back()->with('error','Service Currently Deactive');  

        }
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('billpayment_service')) {
            abort(403);
        }

        $data['type'] = $type;
        //$data['providers'] = Provider::where('type', $type)->where('status', "1")->get();
        /*$prre = [];
        $providers = $this->getOperator();
        //return json_encode($providers);
        if($providers->response_code == 1){
            $prre = $providers->data;
        }else{
            $prre = [];
        }
        $usr = DB::table('rproviders')->where('sertype', 'electricity')->get();
        $data['providers'] = $prre;*/
        $data['providers'] = Provider::where('type', $type)->where('status', "1")->get();
        if($type == 'wt'){
            $data['providers'] = Mahabank::get();

            //Provider::where('type', 'cashdeposit')->where('status', "1")->get();
            return view('service.wtbill')->with($data);
        } else {
            return view('service.billpayment')->with($data);
        }
    }

    public function geoip($ip){
        $ip='103.216.176.192';
        $geoIP  = json_decode(file_get_contents("http://api.ipstack.com/$ip?access_key=49003c05bb4d851383468c238587d71c&format=1"), false);
        return $geoIP;
    }

   public function getOperator(){
        $opertaor = [];
        
        $url = $this->api->url."bill/getoperator";
        //return $url;
        $res = JwtController::callApiWithoutParamGet($url);
        //return $res;
        $this->createFile('getoperator_', ['url' => $url, 'response' => $res]);
        $response = json_decode($res);
        dd($response); //exit;
        return $response;
    }

    public function payment(\App\Http\Requests\Billpay $post)
    {
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('billpayment_service')) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }
        
        $user = \Auth::user();
        $post['user_id'] = $user->id;
        if($user->status != "active"){
            return response()->json(['status' => "Your account has been blocked."], 400);
        }
        if($user->walletpin != $post->walletpin)
        {
            return response()->json(['status' => "Incorrect Wallet Pin"], 200);
        }
        $provider =  DB::table('providers')->where('id', $post->provider_id)->first();
        
        switch ($post->type) {
            case 'getbilldetails':
                $fetchapicharge = PortalSetting::where('code', 'fetchapicharge')->first()->value;
                if($user->mainwallet - $this->mainlocked() < $fetchapicharge){
                    return response()->json(['status'=> 'Low Balance, Kindly recharge your wallet.'], 400);
                }
                $api =  Api::where('code', 'billpay')->first();
                switch ($post->billtype) {
                    case 'electricity':
                        //$url = "https://swipecare.co.in/api/electricity?token=".$api->username."&type=getbilldetails&provider_id=".$provider->recharge1."&number=".$post->number."&mobile=".$post->mobile."";
                        $url = "https://www.mplan.in/api/electricinfo.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$post->number."&operator=".$provider->recharge2;
                        //dd($url); exit;
                        break;
                        
                    case 'insurance':
                        $url = "https://www.mplan.in/api/insurance.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$post->number."&mob=".$post->mobile."&operator=".$provider->recharge2;
                        //$url = "https://swipecare.co.in/api/insurance?token=".$api->username."&type=getbilldetails&provider_id=".$provider->recharge1."&number=".$post->number."&mobile=".$post->mobile."";
                        
                        break;
                        
                    
                }
                
                
                  $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $url,
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
                    
                $response = json_decode($response);
                
                $this->createFile('fetchbill_'.$post->number, ['url' => $url, 'response' => $response]);
                if($response->status != 1){
                    return response()->json(['status' => $response->message], 400);
                }elseif($response->status == 1){
                    return response()->json(['statuscode' => "TXN", 'status' => 'success', 'data' => $response->records[0]], 200);
                }else{
                    return response()->json(['status' => $response->message], 400);
                }
                
                break;
            
            case 'payment':
                
                switch ($post->billtype) {
                    case 'electricity':
                        $billpaytype = 'billpay';
                        break;
                    
                    case 'insurance':
                        $billpaytype = $post->billtype;
                        break;
                        
                    case 'cashdeposit':
                        $billpaytype = $post->billtype;

		$previousrecharge = Aepsreport::where('aadhar', $post->number)->where('credited_by',$user->id)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
        if($previousrecharge > 0){
            return response()->json(['status' => "ERR", "description" => 'Same Transaction allowed after 2 min.']);
        }
		
                        $cashdeposite = \App\Model\PortalSetting::where('code', 'cashdeposite')->first();
                        //dd($otprequired); exit;
                        $cashdeposite->value = "yes";
                        if($cashdeposite->value == "yes") {
                            if($post->has('otp') && $post->otp != ""){
                                $otp = User::where('otpverify', $post->otp)->where('id', $user->id)->count();
                                if($otp > 0){
                                    $flag = 1;
                                    $otp = rand(000000, 999999);
                                User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
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
                                //dd($msg); exit;
                                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number'.json_encode($send)]);
                               
                            }
                        }
                        


                        
                        break;
                        
                    case 'wt':
                        $billpaytype = $post->billtype;

                        $cashdeposite = \App\Model\PortalSetting::where('code', 'wt')->first();
                        //dd($otprequired); exit;
                        $cashdeposite->value = "yes";
                        if($cashdeposite->value == "yes") {
                            if($post->has('otp') && $post->otp != ""){
                                $otp = User::where('otpverify', $post->otp)->where('id', $user->id)->count();
                                if($otp > 0){
                                    $flag = 1;
                                    $otp = rand(000000, 999999);
                                User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
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
                                //dd($msg); exit;
                                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number'.json_encode($send)]);
                               
                            }
                        }
                        


                        
                        break;
                        
                    
                        
                    
                }
                $api =  Api::where('code', 'billpay')->first();
                switch ($post->billtype) {
                    case 'electricity':
                        $url = "https://swipecare.co.in/api/electricity?token=".$api->username."&type=payment&provider_id=".$provider->recharge1."&number=".$post->number."&mobile=".$post->mobile."&amount=".$post->amount."&biller=".$post->biller."&duedate=".$post->duedate."";
                        break;
                        
                    case 'insurance':
                        $url = "https://swipecare.co.in/api/insurance?token=".$api->username."&type=payment&provider_id=".$provider->recharge1."&number=".$post->number."&mobile=".$post->mobile."&amount=".$post->amount."&biller=".$post->biller."&duedate=".$post->duedate."";
                        
                        break;
                        
                    
                }
                
                if($post->billtype != 'cashdeposit' && $post->billtype != 'wt')
                {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => $url,
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
                     $response = json_decode($response);  
                     
                     if(\Auth::user()->mainwallet - $this->mainlocked() < $post->amount){
                        return response()->json(['status'=>  "Low Wallet balance to make this request"], 400);
                    }

                    $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge > 0){
                        return response()->json(['status'=> 'Same Transaction allowed after 2 min.'], 400);
                    }

                    $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                    $debit = User::where('id', $user->id)->decrement('mainwallet', $post->amount - $post->profit);
                    }
                else
                {
                    if($post->billtype == 'wt'){
                        if(\Auth::user()->mainwallet - $this->aepslocked() < $post->amount){
                            return response()->json(['status'=>  "Low Main balance to make this request"], 400);
                        }
                    }else{
                        if(\Auth::user()->aepsbalance - $this->aepslocked() < $post->amount){
                            return response()->json(['status'=>  "Low aeps balance to make this request"], 400);
                        }
                    }
                    
    
                    $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge > 0){
                        return response()->json(['status'=> 'Same Transaction allowed after 2 min.'], 400);
                    }
    
                    $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                    


                    if($post->billtype == 'wt'){
                        $wt_surcahrge_type = \Myhelper::wt_surcahrge_type();
                        $surchargevalue = \Myhelper::wt_surcahrge_value();
                        if($wt_surcahrge_type == 'percentage')
                        {
                             $surcharge = $post->amount*100/$surchargevalue;
                        }
                        else
                        {
                             $surcharge = $surchargevalue;
                        }
                    }else{
                        $cd_surcahrge_type = \Myhelper::cd_surcahrge_type();
                        $surchargevalue = \Myhelper::cd_surcahrge_value();
                        if($cd_surcahrge_type == 'percentage')
                        {
                             $surcharge = $post->amount*100/$surchargevalue;
                        }
                        else
                        {
                             $surcharge = $surchargevalue;
                        }
                    }






                    $totalpending = Report::where('user_id', \Auth::user()->id)->where('status', 'pending')->where('product', 'wt')->sum('amount');
                    
                    $amt = $post->amount + $totalpending + $surcharge;

                    if($post->billtype == 'wt'){
                        $cmpamt = User::where('id', \Auth::user()->id)->first()->mainwallet;
                        $amttype = "Main";
                    }else{
                        $amttype = "Aeps";
                        $cmpamt = User::where('id', \Auth::user()->id)->first()->aepsbalance;
                    }
                    //dd([$amt, $cmpamt]); exit;
                    if($amt > $cmpamt){
                        return response()->json(['status'=> $amttype.' Balance is not sufficient to make this Transaction'], 400);
                    }

                    if($cmpamt < $post->amount + $surcharge)
                    {
                        return response()->json(['status'=> $amttype.' Balance is not sufficient to make this Transaction'], 400);
                    }

                    if($post->billtype == 'wt'){
                        $debit = User::where('id', \Auth::user()->id)->decrement('mainwallet', $post->amount + $surcharge);
                    }else{
                        $debit = User::where('id', \Auth::user()->id)->decrement('aepsbalance', $post->amount + $surcharge);
                    }
                    
                    $post['profit'] = 0;
                }
                    
                 
                //dd($user->mainwallet); exit;
                
                
                $debit = true;
                if ($debit) {
                    do {
                        $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                    } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                    

                    if($post->billtype == 'wt'){
                        $bankname = Mahabank::where('id', $post->provider_id)->first();
                        $insert = [
                            'provider_id' => '87',//$post->provider_id,
                            'charge' => $surcharge,
                            'number' => $post->number,
                            'mobile' => $post->mobile,
                            'option1' => $bankname->bankname,
                            'option2' => $post->mobile,
                            'option3' => $post->number,
                            'option4' => $user->name,
                            'txnid' => $post->txnid,
                            'amount' => $post->amount,
                            'user_id'    => $user->id,
                            "balance" => $user->mainwallet,
                            'trans_type'    => "debit",
                            'api_id' => $this->api->id,
                            'credit_by'  => $user->id,
                            'status' => 'pending',
                            'rtype'      => 'main',
                            'product'    => 'wt'
                        ];
                        $report = Report::create($insert);
                    }else{
                        $insert = [

                            'test' => $post->biller,
                            'user_idew' => $post->duedate,
                            'charge' => $surcharge,
                            'provider_id' => $post->provider_id,
                            'aadhar' => $post->number,
                            'mobile' => $post->mobile,
                            'txnid' => $post->txnid,
                            'amount' => $post->amount,
                            'user_id'    => $user->id,
                            "balance" => $user->aepsbalance,
                            'type'    => "debit",
                            'api_id' => $this->api->id,
                            'credited_by'  => $user->id,
                            'status' => 'pending',
                            'rtype'      => 'main',
                            'trans_type' => 'transaction',
                            'bank'    => $provider->name,
                            'aepstype'=> 'CD',
                            'withdrawType'=> 'CD'
                        ];
                        $report = Aepsreport::create($insert);
                        //$update['status'] = "pending";
                        //$update['payid'] = $post->txnid;
                        //$update['description'] = "Billpayment Accepted";
                        //return response()->json(['status' => $update['status'], 'data' => $report, 'description' => $update['description']], 200);
                    }
                    
                    

                   //dd($post->billtype); exit;

                if($post->billtype != 'cashdeposit' && $post->billtype != 'wt')
                {
                    
                    $response = json_decode($response);                    
                    //print_r($response); exit;
                    
                    $this->createFile('paybill_', ['url' => $url, 'response' => $response]);
                    if($response->status == 'pending'){
                        
                        $update['status'] = "success";
                        $update['payid'] = $response->ackno;
                        $update['description'] = "Billpayment Accepted";
                        
                        Report::where('id', $report->id)->update($update);
                        \Myhelper::commission($report);

                    
                    return response()->json(['status' => $update['status'], 'data' => $report, 'description' => $update['description']], 200);
                    }elseif($response->status == 'pending'){
                        $update['status'] = "pending";
                        $update['payid'] = "pending";
                        $update['description'] = "billpayment pending";
                        return response()->json(['status'=> 'Transaction Failed, please try again.'], 400);
                    }else{
                        $update['status'] = "pending";
                        $update['payid'] = "pending";
                        $update['description'] = "billpayment pending";
                        return response()->json(['status'=> 'Transaction Failed, please try again.'], 400);
                    }

                    
                }
                else
                {

                    if($post->billtype == 'wt'){
                        $msg = "Hello Admin New Mainwallet Transfer Request is of Rs. ".$post->amount." subimmted by User Name: ".$user->name." User Mobile: ".$user->mobile." User ID: ".$user->id."";
                    
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Hello%20Admin%20New%20Cash%20Deposit%20Request%20is%20of%20Rs.%20'.$post->amount.'%20subimmted%20by%20User%20Name:%20'.urlencode($user->name).'%20User%20Mobile:%20'.urlencode($user->mobile).'%20User%20ID:%20'.urlencode($user->id).'',
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
                    


                    
                    
                        //$send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                        //dd($send); exit;
                        $update['status'] = 'pending';
                        $update['description'] = 'Mainwallet Transfer Submitted Successfully';

                    }else{
                        $msg = "Hello Admin New Cash Deposit Request is of Rs. ".$post->amount." subimmted by User Name: ".$user->name." User Mobile: ".$user->mobile." User ID: ".$user->id."";
                    
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Hello%20Admin%20New%20Cash%20Deposit%20Request%20is%20of%20Rs.%20'.$post->amount.'%20subimmted%20by%20User%20Name:%20'.urlencode($user->name).'%20User%20Mobile:%20'.urlencode($user->mobile).'%20User%20ID:%20'.urlencode($user->id).'',
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
                        


                        
                        
                        //$send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                        //dd($send); exit;
                        $update['status'] = 'pending';
                        $update['description'] = 'Cash Despost Submitted Successfully';
                    }


                    
                }
                    if($update['status'] == "success" || $update['status'] == "pending"){
                        Report::where('id', $report->id)->update($update);
                        if($post->billtype != 'cashdeposit' && $post->billtype != 'wt')
                        {
                            \Myhelper::commission($report);
                        }
                    }else{
                        User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                        Report::where('id', $report->id)->update($update);
                    }

                    return response()->json(['status' => $update['status'], 'data' => $report, 'description' => $update['description']], 200);
                }else{
                    return response()->json(['status'=> 'Transaction Failed, please try again.'], 400);
                }
                break;
        }
    }
}
