<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provider;
use App\Model\Report;
use App\User;
use Carbon\Carbon;
//use MiladRahimi\Jwt\Cryptography\Algorithms\Hmac\HS256;
//use MiladRahimi\Jwt\JwtGenerator;

class RechargeController extends Controller
{
    public function index($type)
    {
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('recharge_service')) {
            abort(403);
        }
        $data['type'] = $type;
        $data['providers'] = Provider::where('type', $type)->where('status', "1")->get();
        return view('service.recharge')->with($data);
    }

    public function providersList(Request $post)
    {
        $providers = Provider::where('type', $post->type)->where('status', "1")->get(['id', 'name', "logo"]);
        return response()->json(['statuscode' => "TXN", 'message' => "Provider Fetched Successfully", 'data' => $providers]);
    }

    public function payment(Request $post)
    {
        // if ($this->pinCheck($post) == "fail") {
        //     return response()->json(['statuscode' => "ERR", "message" => "Transaction Pin is incorrect"]);
        // }
        $user = \Auth::user();
        if($user->walletpin != $post->walletpin)
        {
            return response()->json(['status' => "Incorrect Wallet Pin"], 200);
        }

        $rules = [
            'provider_id'      => 'required|numeric',
            'amount'      => 'required|numeric|min:10',
        ];

        if($post->has('type') && $post->type == "mobile"){
            $rules = array_merge($rules , ['number' => 'required|numeric|digits:10']);
        }else{
            $rules = array_merge($rules , ['number' => 'required|numeric|digits_between:8,15']);
        }

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }
                
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('recharge_service')) {
            return response()->json(['statuscode' => "ERR", "message" => "Permission Not Allowed"]);
        }
        
        //$user = User::where('id', $post->user_id)->first();
        if($user->status != "active"){
            return response()->json(['statuscode' => "ERR", "message" => "Your account has been blocked."]);
        }

        $provider = Provider::where('id', $post->provider_id)->first();

        if(!$provider){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
        }

        if(!$provider->api || $provider->api->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Recharge Service Currently Down."]);
        }

        if($user->mainwallet < $post->amount){
            return response()->json(['statuscode' => "ERR", "message" => "Low Balance, Kindly recharge your wallet."]);
        }

        $previousrecharge = Report::where('number', $post->number)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
        if($previousrecharge > 0){
            return response()->json(['statuscode' => "ERR", "message" => 'Same Transaction allowed after 2 min.']);
        }

        do {
            $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
        } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);
        $method = "POST";

        switch ($provider->api->code) {
            case 'recharge1':
                $url = $provider->api->url."recharge/dorecharge";

                $parameter = [
                    "operator" => $provider->recharge1,
                    "canumber" => $post->number,
                    "amount"   => $post->amount,
                    "referenceid" => $post->txnid
                ];

                $token = ""; //$this->getToken($post->user_id.Carbon::now()->timestamp, $provider->api);
                $header = array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                    "Token: ".$token['token'],
                    "Authorisedkey: ".$provider->api->optional1
                );

                $query = json_encode($parameter);
                break;
            
            case 'recharge2':
                $method = "POST";
                $isstv = "false";
                if(in_array($provider->id, ['5'])){
                    $isstv = "true";
                }
                $url = $provider->api->url;

                $parameter['api_token']  = $provider->api->username;
                $parameter['mobile_no']  = $post->number;
                $parameter['company_id'] = $provider->recharge2;
                $parameter['amount']     = $post->amount;
                $parameter['order_id']   = $post->txnid;
                $parameter['is_stv']     = $isstv;
                $query = http_build_query($parameter);
                $url = $provider->api->url."?".$query;
                //dd($url); exit;
                $header = [];
                break;

            case 'recharge3':

                $url = $provider->api->url."/TransactionAPI";

                $parameter['UserID']  = $provider->api->username;
                $parameter['Token']   = $provider->api->password;
                $parameter['Account'] = $post->number;
                $parameter['SPKey']   = $provider->recharge3;
                $parameter['Amount']  = $post->amount;
                $parameter['APIRequestID']   = $post->txnid;
                $parameter['GEOCode'] = "26.850000,80.949997";
                $parameter['CustomerNumber'] = $user->mobile;
                $parameter['pincode'] = $user->pincode;
                $parameter['Format']  = "1";
                $query = http_build_query($parameter);
                $url = $url."?".$query;
                $header = [];
                $method = "GET";
                break; 
        }

        $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
        $debit = User::where('id', $user->id)->decrement('mainwallet', $post->amount - $post->profit);
        if($debit){
            $insert = [
                'number' => $post->number,
                'mobile' => $user->mobile,
                'provider_id' => $provider->id,
                'api_id' => $provider->api->id,
                'amount' => $post->amount,
                'profit' => $post->profit,
                'txnid'  => $post->txnid,
                'status' => 'pending',
                'user_id'=> $user->id,
                'credit_by' => $user->id,
                'rtype' => 'main',
                'via'   => 'portal',
                'balance' => $user->mainwallet,
                'trans_type' => 'debit',
                'product'    => 'recharge',
                'create_time'=> $user->id."/".Carbon::now()->format('Y-m-d H:i:s')
            ];

            try {
                $report = Report::create($insert);
            } catch (\Exception $e) {
                User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                return response()->json(['statuscode' => "ERR", "message" => 'Same Transaction allowed after 2 min.']);
            }

            if (env('APP_ENV') == "server") {
                //dd([$url, $method, $query, $header, "yes", "App\Model\Report", $post->txnid]); exit;
                $result = \Myhelper::curl($url, $method, $query, $header, "yes", "App\Model\Report", $post->txnid);
                //dd($result); exit;
            }else{
                $result = [
                    'error'    => true,
                    'response' => '' 
                ];
            }
            
            // \DB::table('rp_log')->insert([
            //     'ServiceName' => "Recharge",
            //     'header' => json_encode($header),
            //     'body' => $query,
            //     'response' => $result['response'],
            //     'url' => $url,
            //     'created_at' => date('Y-m-d H:i:s')
            // ]);

            if($result['error'] || $result['response'] == ''){
                $update['status'] = "pending";
                $update['payid']  = "pending";
                $update['refno']  = "pending";
            }else{
                switch ($provider->api->code) {
                    case 'recharge1':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status) && in_array($doc->status, [1, 3])){
                            $update['status'] = "success";
                            $update['payid']  =  $doc->ackno;
                            $update['refno']  =  ($doc->operatorid != "")? $doc->operatorid : $doc->ackno;
                        }elseif(isset($doc->response_code) && in_array($doc->response_code, [4, 5,6,7,8,9,10,11,12,16,18])){
                            $update['status'] = "failed";
                            $update['refno'] =  $doc->message;
                            if($doc->message == "Insufficient fund in your account. Please topup your wallet before initiating transaction."){
                                $update['refno'] =  "Service down for sometime";
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['refno']  = "Please wait for status change or contact service provider";
                        }
                        break;
                    
                    case 'recharge2':
                        $doc = json_decode($result['response']);
                        //dd($doc); exit;
                        if(isset($doc->status)){
                            if(isset($doc->status) && in_array(strtolower($doc->status), ['success', 'pending'])){
                                $update['status'] = "success";
                                $update['payid'] = $doc->id;
                                $update['refno'] = $doc->tnx_id;
                            }elseif(isset($doc->status) && in_array(strtolower($doc->status), ['failed', 'failure'])){
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->id)?$doc->id:'failed';
                                $update['refno'] = (isset($doc->tnx_id)) ? $doc->tnx_id : "Failed";
                            }else{
                                $update['status'] = "pending";
                                $update['payid'] = "pending";
                                $update['refno'] = "pending";
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                        }
                        break;

                    case 'recharge3':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "1" || $doc->status == "2"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->rpid;
                                $update['refno'] = $doc->opid;
                                $update['description'] = "Recharge Accepted";
                            }elseif($doc->status == "3"){
                                $update['status'] = "failed";
                                $update['payid'] = $doc->rpid;
                                $update['refno'] = "failed";
                                $update['description'] = "failed";
                            }else{
                                $update['status'] = "pending";
                                $update['payid']  = (isset($doc->rpid)) ? $doc->rpid : "pending";
                                $update['refno']  = (isset($doc->opid)) ? $doc->opid : "pending";
                                $update['description'] = "recharge pending";
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                            $update['description'] = "recharge pending";
                        }
                        break; 
                }
            }

            if($update['status'] == "success" || $update['status'] == "pending"){
                Report::where('id', $report->id)->update($update);
                \Myhelper::commission($report);
                $output['statuscode'] = "TXN";
                $output['message']    = "Recharge Accepted";
            }else{
                User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                Report::where('id', $report->id)->update($update);
                $output['statuscode'] = "TXF";
                $output['message']    = $update['refno'];
            }
            $output['txnid'] = $post->txnid;
            $output['rrn']   = $update['refno'];
            $output['date']  = date('d-M-y H:i A');
            return response()->json($output);

        }else{
            return response()->json(['statuscode' => "ERR", "message" => "Something went wrong"]);
        }
    }

    // public function getToken($uniqueid, $api)
    // {
    //     $payload =  [
    //         "timestamp" => time(),
    //         "partnerId" => $api->username,
    //         "reqid"     => $uniqueid
    //     ];
        
    //     $key = $api->password;
    //     $signer = new HS256($key);
    //     $generator = new JwtGenerator($signer);
    //     return ['token' => $generator->generate($payload), 'payload' => $payload];
    // }

    public function getoperator(Request $post)
    {
        $url = "https://api.paysprint.in/api/v1/service/recharge/hlrapi/hlrcheck";
        $parameter = [
            "number" =>  $post->number,
            "type"   =>  $post->type
        ];
        $api = \App\Model\Api::where('code', "recharge1")->first();

        $token = "";//$this->getToken($post->user_id.Carbon::now()->timestamp, $api);
        $header = array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Token: ".$token['token'],
            "Authorisedkey: ".$api->optional1
        );

        $query = json_encode($parameter);
        $method = "POST"; 
        
        $result = \Myhelper::curl($url, $method, $query, $header, "no");
        
        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->response_code) && $response->response_code == "1"){
                $provider = Provider::where('name', 'like', '%'.strtolower($response->info->operator).'%')->where('type', $post->type)->first();
                //dd($result,$url,$parameter, $provider);
                return response()->json(['status' => "success", "data" => $provider->id, "circle" => $response->info->circle, "providername" => $response->info->operator], 200);
            }
            return response()->json(['status' => "failed", "message" => "Something went wrong"]);
        }else{
            return response()->json(['status' => "failed", "message" => "Something went wrongs"]);
        }
    }

    public function getdthinfo(Request $post)
    {
        $provider = Provider::where('id', $post->operator)->first();
        
        $url = "https://api.paysprint.in/api/v1/service/recharge/hlrapi/dthinfo";
        $parameter = [
            "canumber"   =>  $post->number,
            "op" =>  $provider->recharge3
        ];
        
        $api = \App\Model\Api::where('code', "recharge1")->first();
        $token = "";//$this->getToken($post->user_id.Carbon::now()->timestamp, $api);
        $header = array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Token: ".$token['token'],
            "Authorisedkey: ".$api->optional1
        );

        $query = json_encode($parameter);
        $method = "POST"; 
        
        $result = \Myhelper::curl($url, $method, $query, $header, "no");
        //dd($result,$url,$parameter);
        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->response_code) && $response->response_code == "1"){
                return response()->json(['status' => "success", "data" => $response->info[0]], 200);
            }
            return response()->json(['status' => "failed", "message" => "Something went wrong"]);
        }else{
            return response()->json(['status' => "failed", "message" => "Something went wrongs"]);
        }
    }
    
    public function getplan(Request $post)
    {
        $url = "https://api.paysprint.in/api/v1/service/recharge/hlrapi/browseplan";
        $parameter = [
            "op" =>  $post->providername,
            "circle" =>  $post->circle
        ];  
        $api = \App\Model\Api::where('code', "recharge1")->first();

        $token = ""; ///$this->getToken($post->user_id.Carbon::now()->timestamp, $api);
        $header = array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Token: ".$token['token'],
            "Authorisedkey: ".$api->optional1
        );

        $query = json_encode($parameter);
        $method = "POST"; 
        
        $result = \Myhelper::curl($url, $method, $query, $header, "no");
        if($result['response'] != ''){
            $response = json_decode($result['response']);
            if(isset($response->response_code) && $response->response_code == "1"){
              
              	if(isset($response->response_code) && $response->response_code == "1"){
                $keys = [];
                $values = [];
                foreach ($response->info as $key => $value) {
                    $keys[] = $key;
                    $values[] = $value;
                }

                return response()->json(['status' => "success", "data" => $response->info, 'key' => $keys, 'value' => $values], 200);
            }
            }
            return response()->json(['status' => "failed", "message" => "Something went wrong"]);
        }else{
            return response()->json(['status' => "failed", "message" => "Something went wrongs"]);
        }
    }

    public function roffer(Request $post){
        //dd($post->all()); exit;
        $provider = Provider::where('id', $post->provider_id)->first();
        
        $url = "https://www.mplan.in/api/plans.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$post->number."&operator=".$provider->recharge3;
        

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
        return $response;
    }
}
