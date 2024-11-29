<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Provider;
use App\Http\Controllers\JwtController;
use App\User;
use File;
use App\Model\Api;
use App\Model\Report;
use Carbon\Carbon;

class RechargeController extends Controller
{
    protected $api;

    public function __construct()
    {
        $this->api = Api::where('code', 'recharge1')->first();
    }
    
    public function providersList(Request $post)
    {
        //dd($post); exit;
    	$providers = Provider::where('type', $post->type)->where('status', "1")->get(['id', 'name', 'mplan', 'roffer']);
    	return response()->json(['statuscode' => "TXN", 'message' => "Provider Fetched Successfully", 'data' => $providers]);
    }
    
    public function createFile($file, $data){
        $data = json_encode($data);
        $file = 'aeps_'.$file.'_file.txt';
        $destinationPath=public_path()."/aeps_logs/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function transaction(Request $post)
    {
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'provider_id'      => 'required|numeric',
            'amount'      => 'required|numeric|min:10',
            'number' => 'required|numeric'
        );
        //dd($post->all()); exit;
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
        	return $validate;
        }

        $user = User::where('id', $post->user_id)->first();
        if(!$user){
        	$output['status'] = "ERR";
	        $output['message'] = "User details not matched";
	        return response()->json($output);
        }

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

        if($user->status != "active"){
            return response()->json(['status' => "ERR", "message" => "Your account has been blocked."]);
        }

        $provider = Provider::where('id', $post->provider_id)->first();

        if(!$provider){
            return response()->json(['status' => "ERR", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['status' => "ERR", "message" => "Operator Currently Down."]);
        }

        // if(!$provider->api || $provider->api->status == 0){
        //     return response()->json(['status' => "ERR", "message" => "Recharge Service Currently Down."]);
        // }

        if($user->mainwallet - $this->mainlocked() < $post->amount){
            return response()->json(['status' => "ERR", "message"=> 'Low Balance, Kindly recharge your wallet.']);
        }

         switch ($provider->api->code) {
            case 'recharge1':
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);
                
                
                $url = $provider->api->url."token=".$provider->api->username."&provider_id=".$provider->recharge1."&amount=".$post->amount."&number=".$post->number."&circle_id=".$post->circle_id."";
                
                break;
            case 'recharge2':
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);
                
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


                //$url = $provider->api->url."token=".$provider->api->username."&provider_id=".$provider->recharge1."&amount=".$post->amount."&number=".$post->number."&circle_id=".$post->circle_id."";
                
                break;
                
            
        }  
        
        $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
       if($previousrecharge > 0){
            //return response()->json(['status'=> 'Same Transaction allowed after 2 min.'], 400);
            return response()->json(['status' => "ERR", "message"=> 'Same Transaction allowed after 2 min.']);
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
                'product'    => 'recharge'
            ];

            $report = Report::create($insert);

            if (env('APP_ENV') != "local") {
                $result = array();
                if($provider->api->code == 'recharge1'){
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
                    //echo $response; exit;
                    //dd($response); exit;
                    $result['response'] = $response;
                    $result['error'] = false;
                    
                }
                else{
                    //$result = \Myhelper::curl($url, 'GET', "", [], "yes", "App\Model\Report", $post->txnid);
                    //dd([$url, "GET", $query, $header, "yes", "App\Model\Report", $post->txnid]); exit;
                    $result = \Myhelper::curl($url, "POST", $query, $header, "yes", "App\Model\Report", $post->txnid);
                    //dd($result); exit;
                }
                //dd($result); exit;
            }else{
                $result = [
                    'error' => true,
                    'response' => '' 
                ];
            }
            //dd($provider->api->code); exit;
            
            //cyrus
            //array:2 [
                //   "response" => "{"ApiTransID":"3AB598570C","Status":"Success","ErrorMessage":" ","OperatorRef":"1172456718","TransactionDate":"6/25/2021 7:30:55 PM"}"
                //   "error" => false
                // ]
                
            //mrobotics
            

            if($result['error'] || $result['response'] == ''){
                $update['status'] = "pending";
                $update['payid'] = "pending";
                $update['refno'] = "pending";
                $update['description'] = "recharge pending";
            }else{
                switch ($provider->api->code) {
                    case 'recharge1':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "success"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->payid;
                                $update['refno'] = $doc->refno;
                            }elseif($doc->status == "pending"){
                                $update['status'] = "pending";
                                $update['payid'] = $doc->payid;
                                $update['refno'] = $doc->refno;
                            }elseif($doc->status == "failed"){
                                $update['status'] = "failed";
                                $update['payid'] = $doc->status;
                                $update['refno'] = $doc->refno;
                                $update['profit'] = 0;
                                $debit = User::where('id', $user->user_id)->increment('mainwallet', $post->amount - $post->profit);
                                //working here =>
                                $update['description'] = (isset($doc->description)) ? $doc->description : "failed";
                            }else{
                                $update['status'] = "failed";
                                if(isset($doc->description) && $doc->description == "Insufficient Wallet Balance"){
                                    $update['refno'] = $doc->refno;
                            
                                }else{
                                    $update['refno'] = "failed";
                            
                                }
                                    $update['description'] = (isset($doc->ErrorMessage)) ? $doc->ErrorMessage : "failed";
                            
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                        }
                        break;
                    case 'recharge2':
                        $doc = json_decode($result['response']);
                        //dd($doc); exit;
                        if(isset($doc->status)){
                            switch($doc->status){
                                case 'success':
                                    $update['status'] = "success";
                                    $update['payid'] = $doc->id;
                                    $update['refno'] = $doc->tnx_id;
                                    $update['description'] = $doc->response;
                                    break;
                                case 'pending':
                                    $update['status'] = "pending";
                                    $update['payid'] = $doc->id;
                                    $update['refno'] = $doc->tnx_id;
                                    $update['description'] = $doc->response;
                                    break;
                                case 'failure':
                                    $update['status'] = "failed";
                                    $update['payid'] = "failed";
                                    $update['refno'] = $doc->errorMessage;
                                    $update['description'] = $doc->errorMessage;
                                    break;
                                default:
                                    $update['status'] = "failed";
                                    $update['payid'] = "failed";
                                    $update['refno'] = "failed";
                                    $update['description'] = $doc->errorMessage;
                                    break;
                            }
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                        }
                        break;
                        
                    
                }
            }
            
           
            $rechargestatus = $update['status'];
            $msg = urlencode('Recharge on '.$post->number.' is '.ucfirst($rechargestatus).', Txn number is '.$post->txnid.'');
            $send = \Myhelper::sms($user->mobile, $msg);
            
            
            \Myhelper::save_notification('Recharge on '.$post->number.' is '.ucfirst($rechargestatus).', Txn number is '.$post->txnid.'');
            if($update['status'] == "success" || $update['status'] == "pending"){
                Report::where('id', $report->id)->update($update);
                \Myhelper::commission($report);
            }else{
                User::where('id', $user->id)->increment('mainwallet', $post->amount - $post->profit);
                Report::where('id', $report->id)->update($update);
            }
            $update['txnid'] = $post->txnid;
            $update['provider_name'] = $provider->name;
            $update['date'] = $report->created_at;
            
            return response()->json($update, 200);
        }else{
            return response()->json(['status' => "failed", "description" => $res->message], 200);
        }
        
        
        
    }

    public function status(Request $post)
    {
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
            'txnid'      => 'required|numeric'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
        	return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
        	$output['status'] = "ERR";
	        $output['message'] = "User details not matched";
	        return response()->json($output);
        }

        if (!\Myhelper::can('recharge_status', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        $report = Report::where('id', $post->txnid)->first();

        $reportcount = Report::where('id', $post->txnid)->count();
        if($reportcount == 0)
        {
            return response()->json(['status' => "ERR", "message" => "Txn id does not exist"]);
        }
        if(!$report || !in_array($report->status , ['pending', 'success'])){
            return response()->json([ 'status' => "Recharge Status Not Allowed"], 400);
        }

        

		switch ($report->api->code) {
			case 'recharge1':
				$url = $report->api->url.'/status?token='.$report->api->username.'&apitxnid='.$report->txnid;
				break;
			
			default:
				return response()->json(['status' => "ERR", "message" => "Recharge Status Not Allowed"]);
				break;
		}

		$method = "GET";
		$parameter = "";
		$header = [];

		if (env('APP_ENV') != "local") {
                $result = \Myhelper::curl($url, $method, $parameter, $header);
            }else{
                $result = [
                    'error' => false,
                    'response' => json_encode([
                    	'status' => 'TXN',
                    	'trans_status'  => 'success',
                    	'refno'  => 'local',
                    	'message'=> 'local'
                    ]) 
                ];
            }
		if($result['response'] != ''){
			switch ($report->api->code) {
				case 'recharge1':
    				$doc = json_decode($result['response']);
    				if($doc->statuscode == "TXN" && ($doc->trans_status =="success" || $doc->trans_status =="pending")){
    					$update['refno'] = $doc->refno;
    					$update['status'] = "success";
    
    					$output['status'] = "TXN";
    					$output['txn_status'] = "success";
    	        		$output['refno'] = $doc->refno;
    
    				}elseif($doc->statuscode == "TXN" && $doc->trans_status =="reversed"){
    					$update['status'] = "reversed";
    					$update['refno'] = $doc->refno;
    
    					$output['status'] = "TXR";
    					$output['txn_status'] = "reversed";
    	        		$output['refno'] = $doc->refno;
    				}else{
    					$update['status'] = "Unknown";
    					$update['refno'] = $doc->message;
    
    					$output['status'] = "TNF";
    					$output['txn_status'] = "unknown";
    	        		$output['refno'] = $doc->message;
    				}
    				break;
    				
    			case 'recharge2':
                        $doc = json_decode($result['response']);
                        if(isset($doc->status)){
                            if($doc->status == "success" || $doc->status == "pending"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->txid;
                                $update['refno'] = $doc->opid;
                            }else{
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->txid)?$doc->txid:'Failed';
                                $update['refno'] = "failed";
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
                            if($doc->status == "success" || $doc->status == "pending"){
                                $update['status'] = "success";
                                $update['payid'] = $doc->id;
                                //$update['refno'] = isset($doc->tnx_id) ? $doc->tnx_id : isset($doc->response) ? $doc->response : 'pending';
                                $update['description'] = "Recharge Accepted";

                                $output['status'] = "TXN";
                                $output['message'] = "Recharge Accepted";
                                $output['payid'] = $report->id;
                                $output['refno'] = $doc->tnx_id;
                            }else{
                                $update['status'] = "failed";
                                $update['payid'] = isset($doc->id)?$doc->id:'failed';
                                $update['refno'] = (isset($doc->response)) ? $doc->response : "Failed";
                                $update['description'] = isset($doc->response) ? $doc->response : "Recharge Failed";

                                $output['status'] = "TXF";
                                $output['message'] = $update['description'];
                                $output['payid'] = $report->id;
                                $output['refno'] = "failed";
                            }
                        }elseif(isset($doc->error) && $doc->error){
                            $update['status'] = "failed";
                            $update['payid'] = isset($doc->id)?$doc->id:'failed';
                            $update['refno'] = (isset($doc->errorMessage)) ? $doc->errorMessage : "Failed";

                            $output['status'] = "TXF";
                            $output['message'] = $update['refno'];
                            $output['payid'] = $report->id;
                            $output['refno'] = $update['refno'];
                        }else{
                            $update['status'] = "pending";
                            $update['payid'] = "pending";
                            $update['refno'] = "pending";
                            $update['description'] = "recharge pending";

                            $output['status'] = "TUP";
                            $output['message'] = "Recharge Pending";
                            $output['payid'] = $report->id;
                            $output['refno'] = "pending";
                        }
                        break;
			}
			$product = "recharge";

			if ($update['status'] != "Unknown") {
				$reportupdate = Report::where('id', $report->id)->update($update);
				if ($reportupdate && $update['status'] == "reversed") {
					\Myhelper::transactionRefund($post->id, $product);
				}

				if($report->user->role->slug == "apiuser" && $report->status == "pending" && $post->status != "pending"){
					\Myhelper::callback($report, $product);
				}
			}
			return response()->json($output);
		}else{
			return response()->json(['status' => "ERR", "message" => "Something went wrong, contact your service provider"]);
		}
    }
    public function roffer(Request $post)
    {
        $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
                'provider_id'  =>'required|numeric',
                'number'  =>'required|numeric',
            );

            $validate = \Myhelper::FormValidator($rules, $post);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first();
            if($user){
        $provider_id = $_GET['provider_id'];
        $number  = $_GET['number'];
        $provider = Provider::where('id', $provider_id)->first();
        //$url = "https://swipecare.co.in/api/roffer?token=".$provider->api->username."&provider_id=".$provider->recharge1."&number=".$number."";
        $url = "https://www.mplan.in/api/plans.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$number."&operator=".$provider->recharge3;
        
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
          CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
          
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $doc = json_decode($response);
         $output['status'] = "TXN";
         $output['offers'] = $doc;
         $output['message'] = "Offer Fetched Successfully";


        }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);
        
        
        
        
        return $doc;
        
    }
    public function rofferdth(Request $post)
    {
        $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
                'provider_id'  =>'required|numeric',
                'number'  =>'required|numeric',
            );

            $validate = \Myhelper::FormValidator($rules, $post);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first();
            if($user){
        $provider_id = $_GET['provider_id'];
        $number  = $_GET['number'];
        $provider = Provider::where('id', $provider_id)->first();
        //$url = "https://swipecare.co.in/api/roffer?token=".$provider->api->username."&provider_id=".$provider->recharge1."&number=".$number."";
        //https://www.mplan.in/api/DthRoffer.php?apikey=[yourapikey]&offer=roffer&tel=[VCnumber]&operator=[operator](Given below)
        $url = "https://www.mplan.in/api/DthRoffer.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel=".$number."&operator=".$provider->recharge3;
        
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
          CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
          
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $doc = json_decode($response);
         $output['status'] = "TXN";
         $output['offers'] = $doc;
         $output['message'] = "Offer Fetched Successfully";


        }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);
        
        
        
        
        return $doc;
        
    }


    public function offer(Request $post)
    {
        $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
                'provider_id'  =>'required|numeric',
                'circle'  =>'required',
            );

            $validate = \Myhelper::FormValidator($rules, $post);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first();
            if($user){
        $provider_id = $_GET['provider_id'];
        $number  = $_GET['number'];
        $provider = Provider::where('id', $provider_id)->first();
        //$url = "https://swipecare.co.in/api/roffer?token=".$provider->api->username."&provider_id=".$provider->recharge1."&number=".$number."";
        //https://www.mplan.in/api/plans.php?apikey=[yourapikey]&cricle=[Gujarat](given below)&operator=[operator](BSNL,Idea,given below)
        $url = "https://www.mplan.in/api/plans.php?apikey=2a27133a47858620d0e485ec67d60d15&cricle=".$post->circle."&operator=".$provider->recharge3;
        
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
          CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
          
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        $doc = json_decode($response);
         $output['status'] = "TXN";
         $output['offers'] = $doc;
         $output['message'] = "Offer Fetched Successfully";


        }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);
        
        
        
        
        return $doc;
        
    }

    public function recentrecharge(Request $post)
    {
        $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
            );

            $validate = \Myhelper::FormValidator($rules, $post);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first();
            if($user){

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        
        $type = 'rechargestatement';
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        $data->where('reports.product', 'recharge')->where('reports.rtype', 'main');
        $data->limit('10');
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
        //$json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;

        }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);
        
    }
    
    public function rechargereport(Request $post)
    {
        $rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
            );

            $validate = \Myhelper::FormValidator($rules, $post);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$post->user_id)->where('apptoken',$post->apptoken)->first();
            if($user){

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        $fromdate = $post->fromdate;
        $todate  = $post->todate;
        $status = $post->status;
        $product = $post->provider_id;
        $type = 'rechargestatement';
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        
        $data->where('product', 'recharge')->where('rtype', 'main');
        if(isset($post->product) && !empty($post->product)){
            $data->where('provider_id', $post->provider_id);
        }
        if(isset($post->status) && !empty($post->status)){
            $data->where('status', $post->status);
        }
        if(!empty($post->fromdate)){
                $data->whereDate('created_at', $post->fromdate);
            }
        if((isset($post->fromdate) && !empty($post->fromdate)) 
                && (isset($post->todate) && !empty($post->todate))){
                if($post->fromdate == $post->todate){
                    $data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                }else{
                    $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
                }
            }
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
       // $json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;
        

        }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);
        
    }

    public function recentbillpay(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        
        $type = 'rechargestatement';
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        $data->where('product', 'billpay')->where('rtype', 'main');
        $data->limit('10');
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
         // $json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;
        
            return response()->json($output);
        
    }
    
    public function billpayreport(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        $fromdate = $post->fromdate;
        $todate  = $post->todate;
        $status = $post->status;
        $product = $post->provider_id;
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        
        $data->where('product', 'billpay')->where('rtype', 'main');
        if(isset($post->product) && !empty($post->product)){
            $data->where('provider_id', $post->provider_id);
        }
        if(isset($post->status) && !empty($post->status)){
            $data->where('status', $post->status);
        }
        if(!empty($post->fromdate)){
                $data->whereDate('created_at', $post->fromdate);
            }
        if((isset($post->fromdate) && !empty($post->fromdate)) 
                && (isset($post->todate) && !empty($post->todate))){
                if($post->fromdate == $post->todate){
                    $data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                }else{
                    $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
                }
            }
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
         // $json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;
        
            return response()->json($output);
        
    }

    public function recentinsurancebillpay(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        
        $type = 'rechargestatement';
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        $data->where('product', 'insurance')->where('rtype', 'main');
        $data->limit('10');
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
         // $json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;
        
            return response()->json($output);
        
    }
    
    public function insurancebillpayreport(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->where('apptoken',$post->apptoken)->first();
        if(!$user){
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        if (!\Myhelper::can('recharge_service', $user->id)) {
            return response()->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }
        
        $fromdate = $post->fromdate;
        $todate  = $post->todate;
        $status = $post->status;
        $product = $post->provider_id;
        
        $table = '\App\Model\Report';
        $data = $table::query();
        $data->select('*');

        
        $data->where('product', 'insurance')->where('rtype', 'main');
        if(isset($post->product) && !empty($post->product)){
            $data->where('provider_id', $post->provider_id);
        }
        if(isset($post->status) && !empty($post->status)){
            $data->where('status', $post->status);
        }
        if(!empty($post->fromdate)){
                $data->whereDate('created_at', $post->fromdate);
            }
        if((isset($post->fromdate) && !empty($post->fromdate)) 
                && (isset($post->todate) && !empty($post->todate))){
                if($post->fromdate == $post->todate){
                    $data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                }else{
                    $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
                }
            }
        $data->where('reports.user_id', $post->user_id);
        $totalData = $data->count();
        $data = $data->get();
        $json_data = array(
                "recordsTotal"    => intval( $totalData ),
                "data"            => $data
            );
         // $json_data = json_encode($json_data);
        $output['status'] = "TXN";
        $output['report'] = $json_data;
        
            return response()->json($output);
        
    }
}
