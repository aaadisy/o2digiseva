<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provider;
use App\Model\Report;
use App\User;
use Carbon\Carbon;
use App\Model\Api;
use File;

class RechargeController extends Controller
{
    protected $api;

    public function __construct()
    {
        
        $this->api = Api::where('code', 'recharge1')->first();
    }

    public function index($type)
    {
        if(!\Myhelper::service_active('recharge_service'))
        {

            return  redirect()->back()->with('error','Service Currently Deactive');  

        }
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('recharge_service')) {
            abort(403);
        }
        $data['type'] = $type;
        $data['providers'] = Provider::where('type', $type)->where('status', "1")->get();
        //$providers = $this->getOperator();
        /*if($providers->responsecode == '1'){
            $data['providers'] = $providers->data;
        }else{
            $data['providers'] = [];
        }*/
        //dd($data); exit;

        return view('service.recharge')->with($data);
    }

    public function getOperator(){
        $opertaor = [];
        
        $url = $this->api->url."recharge/getoperator";
        
        $res = JwtController::callApiWithoutParamGet($url);
        
        $this->createFile('getoperator_', ['url' => $url, 'response' => $res]);
        return json_decode($res);
        $response = json_decode($res);
        
        dd($response); exit;
        return $opertaor;
    }
    
    public function createFile($file, $data){
        $data = json_encode($data);
        $file = 'recharge_'.$file.'_file.txt';
        $destinationPath=public_path()."/recharge_logs/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file,$data);
        return $destinationPath.$file;
    }

    public function payment(\App\Http\Requests\Recharge $post)
    {
        //dd($post->all()); exit;
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('recharge_service')) {
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
        $provider = Provider::where('id', $post->provider_id)->first();

        if(!$provider){
            return response()->json(['status' => "Operator Not Found"], 400);
        }

        if($provider->status == 0){
            return response()->json(['status' => "Operator Currently Down."], 400);
        }

        /*if(!$provider->api || $provider->api->status == 0){
            return response()->json(['status' => "Recharge Service Currently Down."], 400);
        }*/

        if($user->mainwallet - $this->mainlocked() < $post->amount){
            return response()->json(['status'=> 'Low Balance, Kindly recharge your wallet.'], 400);
        }
        
        
        switch ($provider->api->code) {
            case 'recharge1':
                do {
                    $post['txnid'] = $this->transcode().rand(1111111111, 9999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);
                
                
                $url = $provider->api->url."token=".$provider->api->username."&provider_id=".$provider->recharge1."&amount=".$post->amount."&number=".$post->number."&circle_id=".$post->circle_id."";
                
                break;
                
            
        }  
        
        $previousrecharge = Report::where('number', $post->number)->where('amount', $post->amount)->where('provider_id', $post->provider_id)->whereBetween('created_at', [Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
       if($previousrecharge > 0){
            return response()->json(['status'=> 'Same Transaction allowed after 2 min.'], 400);
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
                    $result = \Myhelper::curl($url, 'GET', "", [], "yes", "App\Model\Report", $post->txnid);
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
                        
                     
                        
                        //dd($doc); exit;
                        //"{"ApiTransID":"24AE926727","Status":"Pending","ErrorMessage":"recharge request was accepted.","OperatorRef":"","TransactionDate":"5/21/2021 1:51:07 PM"}"
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
            return response()->json($update, 200);
        }else{
            return response()->json(['status' => "failed", "description" => "Something went wrong"], 200);
        }
    }
    
     public function roffer()
    {
        $provider_id = $_GET['provider_id'];
        $number  = $_GET['number'];
        
        $provider = Provider::where('id', $provider_id)->first();
        
         $url = "https://swipecare.co.in/api/roffer?token=".$provider->api->username."&provider_id=".$provider->recharge1."&number=".$number."";
        
       // $url = 'https://www.mplan.in/api/plans.php?apikey=a61b89849a42a85b996016baea00f687&offer=roffer&tel='.$number.'&operator='.$provider_id.'';
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
       
        
        $doc = $doc->records;
       
        
        $content = '
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 
<script>
  $(document).ready(function(){
  
    $(".btnsave").on("click", function() {
  $("#amount").val($(this).data("amount"));
});
 });
</script>
<table class="table table-bordered" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Rupees</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>';
                                $i = 1;
                                    foreach($doc as $d)
        {
                                    
                                    $content .=    '<tr>
                                            <td>'.$i.'</td>
                                            <td>'.$d->rs.'</td>
                                            <td>'.$d->desc.'</td>
                                            <td>
                                            <button data-amount="'.$d->rs.'" type="button" class="btn btn-primary btnsave" data-dismiss="modal" >Select Plan</button></td>
                                        </tr>';
                                    
        $i++; }
          $content .=                      '</tbody>
                            </table>';
        
        return $content;
        
    }
}
