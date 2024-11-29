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
use App\Model\Aepstransaction;
use App\Model\Aepsreport;
use Illuminate\Validation\Rule;

class AepsController extends Controller
{
    public $aepsapi, $iciciaepsapi, $matmapi;
    public function __construct()
    {
        $this->aepsapi = Api::where('code', 'aeps')->first();
        $this->iciciaepsapi = Api::where('code', 'iciciaeps')->first();
        $this->matmapi = Api::where('code', 'matm')->first();
    }

    public function index($type)
    {
         if(!\Myhelper::service_active('aeps_service'))
        {

            return  redirect()->back()->with('error','Service Currently Deactive');  

        }
        switch ($type) {
            case 'aeps':
                if($this->companyPermission('aeps_service')){
                    abort(401);
                }

                

                if (!\Mycheck::can('aeps_service')) {
                    abort(401);
                }

                $aepsdata = Aepsdata::where('user_id', \Auth::id())->first();
                if(!$aepsdata || $aepsdata->status != "approved"){
                    return redirect(route('aeps', ['type' => 'kyc']));
                }
                $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
                return view('service.aeps')->with($data);
                break;

            case 'iaeps':
                if($this->companyPermission('aeps_service')){
                    abort(401);
                }

                if (!\Mycheck::can('aeps_service')) {
                    abort(401);
                }

                $aepsdata = Aepsdata::where('user_id', \Auth::id())->first();
                if(!$aepsdata || $aepsdata->status != "approved"){
                    return redirect(route('aeps', ['type' => 'kyc']));
                }
                $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
                return view('service.iaeps')->with($data);
                break;

            case 'matm':
                if($this->companyPermission('matm_service')){
                    abort(401);
                }

                if (!\Mycheck::can('matm_service')) {
                    abort(401);
                }

                $aepsdata = Aepsdata::where('user_id', \Auth::id())->first();
                if(!$aepsdata || $aepsdata->status != "approved"){
                    return redirect(route('aeps', ['type' => 'kyc']));
                }
                $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
                return view('service.matm')->with($data);
                break;

            case 'kyc':
                if (!\Mycheck::can(['matm_service', 'aeps_service'])) {
                    abort(401);
                }

                $data['aepsdata'] = Aepsdata::where('user_id', \Auth::id())->first();

                if($data['aepsdata'] && $data['aepsdata']->status != "approved"){
                    $url = $this->aepsapi->url."/agent/ekyc/status";
                    $parameters['txnid'] = $data['aepsdata']->txnid;
                    $parameters['token'] = $this->aepsapi->username;
                    $result = \Mycheck::curl($url, 'POST', json_encode($parameters), array("Content-Type: application/json"), 'no');
                    if($result['response'] != ''){
                        $responses = json_decode($result['response']);
                        if($responses->status == "TXN"){
                            Aepsdata::where('id', $data['aepsdata']->id)->update(['status' => 'approved', 'agentcode' => $responses->agentcode]);
                        }elseif($responses->status == "TXP"){
                            Aepsdata::where('id', $data['aepsdata']->id)->update(['status' => 'Kyc Submitted']);
                        }elseif($responses->status == "TUP"){
                            Aepsdata::where('id', $data['aepsdata']->id)->update(['status' => 'pending']);
                        }elseif($responses->status == "TXR"){
                            Aepsdata::where('id', $data['aepsdata']->id)->update(['status' => 'rejected', 'remark' => $responses->message]);
                        }
                    }
                    $data['aepsdata'] = Aepsdata::where('user_id', \Auth::id())->first();
                }

                if($data['aepsdata'] && $data['aepsdata']->status == "approved"){
                    return redirect(route('aeps', ['type' => 'aeps']));
                }
                return view('service.aepskyc')->with($data);
                break;
            default:
                abort(401);
                break;
        }
    }

    public function kyc(Request $post)
    {
        if($this->companyPermission('aeps_service')){
            return response()->json(['status' => 'ERR', 'message'=>'Permission Not Allowed'], 400);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        
        switch ($post->type) {
            case 'new':
                $rules = array(
                    'name'      => 'required',
                    'mobile'    => 'required|numeric|digits:10',
                    'email'     => 'required',
                    'city'      => 'required',
                    'address'   => 'required',
                    'pincode'   => 'required|numeric|digits:6',
                    'state'     => 'required',
                    'pancard'   => 'required',
                    'aadhar'    => 'required',
                    'shopname'     => 'required',
                    'aadharpics'   => 'required|mimes:jpg,jpeg,pdf|max:1024',
                    'pancardpics'  => 'required|mimes:jpg,jpeg,pdf|max:1024',
                );

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                $charge = \Mycheck::getCommission(0, $user->scheme_id,'aeps', 'id');
                if($charge && ($charge > $user->balance)){
                    return response()->json(['status' => 'ERR', 'message'=> 'Low Balance, Kindly recharge your wallet.'], 400);
                }

                do {
                    $post['agentcode'] = $user->company->aepscode.rand(1111111,9999999);
                } while (Aepsdata::where("agentcode", "=", $post->agentcode)->first() instanceof Aepsdata);

                if($post->hasFile('aadharpics')){
                    $post['aadharpic'] = $post->file('aadharpics')->store('aepskyc');
                }

                if($post->hasFile('pancardpics')){
                    $post['pancardpic'] = $post->file('pancardpics')->store('aepskyc');
                }

                if($post->hasFile('selfdeclares')){
                    $post['selfdeclare'] = $post->file('selfdeclares')->store('aepskyc');
                }
                        
                $parameters['agentcode']= $post->agentcode;
                $parameters['type']     = "new";
                $parameters['token']    = $this->aepsapi->username;
                $parameters['email']    = $post->email;
                $parameters['city']     = $post->city;
                $parameters['mobile']   = $post->mobile;
                $parameters['address']  = $post->address;
                $parameters['pincode']  = $post->pincode;
                $parameters['pancard']  = $post->pancard;
                $parameters['aadhar']   = $post->aadhar;
                $parameters['state']    = $post->state;
                $parameters['name']     = $post->name;
                $parameters['shopname'] = $post->shopname;
                $parameters['aadharpic'] = url('public')."/".$post->aadharpic;
                $parameters['pancardpic'] = url('public')."/".$post->pancardpic;
                $parameters['selfdeclare'] = url('public')."/".$post->selfdeclare;
                $url = $this->aepsapi->url."/agent/ekyc";
                $result = \Mycheck::curl($url, 'POST', json_encode($parameters), array("Content-Type: application/json"), 'no');

                if($result['response'] != ''){
                    $data = json_decode($result['response']);
                    if($data->status == "TXN"){
                        
                        $post['txnid'] = $data->txnid;
                        $user = Aepsdata::create($post->all());
                        if($user){
                            return response()->json(['status'=> 'success', 'message'=> "Transaction Successfull"]);
                        }else{
                            return response()->json(['status'=> 'ERR', 'message'=> isset($data->message) ? $data->message : "Transaction Failed"]);
                        }
                    }
                }
                return response()->json(['status'=> 'ERR', 'message'=> isset($data->message) ? $data->message : "Transaction Failed"]);
                break;
            
            case 're':
                $kyc = Aepsdata::where('id', $post->id)->first();
                $parameters['type']     = "re";
                $parameters['txnid']    = $kyc->txnid;
                $parameters['token']    = $this->aepsapi->username;
                $url = $this->aepsapi->url."/agent/ekyc";
                $result = \Mycheck::curl($url, 'POST', json_encode($parameters), array("Content-Type: application/json"), 'no');
                //dd([$url, $parameters, $result]);
                if($result['response'] != ''){
                    $responses = json_decode($result['response']);
                    if($responses->status == "TXN"){
                        try {
                            unlink(public_path('aepskyc/').$kyc->adhaarpic);
                            unlink(public_path('aepskyc/').$kyc->pancardpic);
                            unlink(public_path('aepskyc/').$kyc->selfdeclare);
                        } catch (\Exception $e) {
                        }

                        $delete = Aepsdata::where('id', $kyc->id)->delete();
                        if($delete){
                            return redirect(route('aeps', ['type' => 'kyc']));
                        }else{
                            return redirect()->back();
                        }
                    }
                }
                return redirect()->back();
                
                break;

            default:
                return response()->json(['status'=>'ERR', 'message' => "Bad Parameter Request"], 400);
                break;
        }
    }

    public function getTds($amount)
    {
        return $amount * 5/100;
    }

    
    public function yesAeps()
    {
        if($this->companyPermission('aeps_service')){
            abort(401);
        }

        $aepsdata = Aepsdata::where('user_id', \Auth::id())->first();
        if(!$aepsdata || $aepsdata->status != "approved"){
            return redirect(route('aeps', ['type' => 'kyc']));
        }
        $data['aepsbanks'] = \DB::table('aepsbanks')->get();
        $data['fundrequest'] = Aepsfundrequest::where('user_id', \Auth::user()->id)->where('status', 'pending')->first();
        return view('service.newaeps')->with($data);
    }
    
    public function aepsInitiate(Request $post)
    {
        if($this->companyPermission('aeps_service')){
            abort(401);
        }

        if (!\Mycheck::can(['aeps_service'])) {
            abort(401);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        $post['api_id'] = $this->aepsapi->id;

        if(env('APP_ENV') == "local"){
            $post['agentcode'] = "RS00789";
        }else{
            $aepsdata = Aepsdata::where('user_id', $post->user_id)->first();
            if(!$aepsdata){
                return response()->json(["status"=>"ERR", 'message'=>"Aeps registration pending"]);
            }
            $post['agentcode'] = $aepsdata->agentcode;
        }
        $sscode = Carbon::now()->timestamp*1000;
        if($post->service == "11"){
            $txnid = "AW".$sscode.rand(11,99);
        }else{
            $txnid = "AB".$sscode.rand(11,99);
            $post['amount'] = 0;
        }

        if($post->service == "11"){
            $insert = [
                "mobile"  => $post->mobile,
                "aadhar"  => $user->mobile,
                "txnid"   => $txnid,
                "amount"  => $post->amount,
                "user_id" => $post->user_id,
                "balance" => $user->aepsbalance,
                'type'    => "none",
                'api_id'  => $post->api_id,
                'credited_by' => $post->user_id,
                'status'  => 'initiated',
                'rtype'   => 'main',
                'transtype' => 'transaction'
            ];
            $report = Aepsreport::create($insert);
        }

        $parameters['token'] = $this->aepsapi->username;
        $parameters['mobile'] = $post->mobile;
        $parameters['agentcode'] = $post->agentcode;
        $parameters['amount'] = $post->amount;
        $parameters['service'] = $post->service;
        $parameters['txnid'] = $txnid;
        $query = http_build_query($parameters);
        $url = $this->aepsapi->url."/v1/transaction?".$query;
        $header = array('Content-Type: application/json');
        $result = \Mycheck::curl($url, 'POST', json_encode($parameters), $header, "no");
        if($result['response'] != ''){
            $doc = json_decode($result['response']);
            if($doc->status == "TXN"){
                return \Redirect::away($doc->message);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    public function iaepsInitiate(Request $post)
    {
        if($this->companyPermission('aeps_service')){
            abort(401);
        }

        if (!\Mycheck::can(['aeps_service'])) {
            abort(401);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        $post['api_id'] = $this->aepsapi->id;

        if(env('APP_ENV') == "local"){
            $post['agentcode'] = "RS00789";
        }else{
            $aepsdata = Aepsdata::where('user_id', $post->user_id)->first();
            if(!$aepsdata){
                return response()->json(["status"=>"ERR", 'message'=>"Aeps registration pending"]);
            }
            $post['agentcode'] = $aepsdata->agentcode;
        }
        $sscode = Carbon::now()->timestamp*1000;
        if($post->service == "16"){
            $txnid = "IAW".$sscode.rand(11,99);
        }else{
            $txnid = "IAB".$sscode.rand(11,99);
            $post['amount'] = 0;
        }

        if($post->service == "16"){
            $insert = [
                "mobile"  => $post->mobile,
                "aadhar"  => $user->mobile,
                "txnid"   => $txnid,
                "amount"  => $post->amount,
                "user_id" => $post->user_id,
                "balance" => $user->aepsbalance,
                'type'    => "none",
                'api_id'  => $post->api_id,
                'credited_by' => $post->user_id,
                'status'  => 'initiated',
                'rtype'   => 'main',
                'transtype' => 'transaction'
            ];
            $report = Aepsreport::create($insert);
        }

        $parameters['token'] = $this->iciciaepsapi->username;
        $parameters['mobile'] = $post->mobile;
        $parameters['agentcode'] = $post->agentcode;
        $parameters['amount'] = $post->amount;
        $parameters['service'] = $post->service;
        $parameters['txnid'] = $txnid;
        $query = http_build_query($parameters);
        $url = $this->iciciaepsapi->url."/v1/transaction?".$query;
        $header = array('Content-Type: application/json');
        $result = \Mycheck::curl($url, 'POST', json_encode($parameters), $header, "no");
        if($result['response'] != ''){
            $doc = json_decode($result['response']);
            if($doc->status == "TXN"){
                return \Redirect::away($doc->message);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    public function matmInitiate(Request $post)
    {
        if($this->companyPermission('matm_service')){
            return response()->json(['status' => 'ERR', 'message'=>'Permission Not Allowed'], 400);
        }

        if (!\Mycheck::can(['matm_service'])) {
            abort(401);
        }

        $user = \Auth::user();
        $post['user_id'] = $user->id;
        $post['api_id'] = $this->matmapi->id;

        if(env('APP_ENV') == "local"){
            $post['agentcode'] = "RS00789";
        }else{
            $aepsdata = Aepsdata::where('user_id', $post->user_id)->first();
            if(!$aepsdata){
                return response()->json(["status"=>"ERR", 'message'=>"Aeps registration pending"]);
            }
            $post['agentcode'] = $aepsdata->agentcode;
        }
        $sscode = Carbon::now()->timestamp*1000;

        if($post->service == "003"){
            $txnid = "MAW".$sscode.rand(11,99);
        }else{
            $txnid = "MAB".$sscode.rand(11,99);
            $post['amount'] = 0;
        }

        if($post->service == "11"){
            $insert = [
                "mobile"  => $post->mobile,
                "aadhar"  => $user->mobile,
                "txnid"   => $txnid,
                "amount"  => $post->amount,
                "user_id" => $post->user_id,
                "balance" => $user->aepsbalance,
                'type'    => "none",
                'api_id'  => $post->api_id,
                'credited_by' => $post->user_id,
                'status'  => 'initiated',
                'rtype'   => 'main',
                'transtype' => 'transaction'
            ];
            $report = Aepsreport::create($insert);
        }

        $parameters['token'] = $this->matmapi->username;
        $parameters['mobile'] = $post->mobile;
        $parameters['agentcode'] = $post->agentcode;
        $parameters['amount'] = $post->amount;
        $parameters['service'] = $post->service;
        $parameters['txnid'] = $txnid;
        $query = http_build_query($parameters);
        $url = $this->matmapi->url."/v1/transaction?".$query;
        $header = array('Content-Type: application/json');
        $result = \Mycheck::curl($url, 'POST', json_encode($parameters), $header, "no");
        if($result['response'] != ''){
            $doc = json_decode($result['response']);
            if($doc->status == "TXN"){
                return \Redirect::away($doc->message);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    public function yesAepsUpdate(Request $post)
    {
        if($post['HEADER']['ST'] == "WITHDRAWAL"){
            switch (strtolower($post['DATA']['TXNDETAILS'][0]['txnStatus'])) {
                case 'success':
                    $update['status'] = 'success';
                    $update['type'] = 'credit';
                    $update['aadhar'] = isset($post['DATA']['TXNDETAILS'][0]['AadharNumber'])?$post['DATA']['TXNDETAILS'][0]['AadharNumber']:'';
                    $update['refno'] = $post['DATA']['TXNDETAILS'][0]['RRN'];
                    $update['bank'] = isset($post['DATA']['TXNDETAILS'][0]['BANK_NAME']) ? $post['DATA']['TXNDETAILS'][0]['BANK_NAME'] : '';
                    $update['remark'] = isset($post['DATA']['TXNDETAILS'][0]['RESP_MSG']) ? $post['DATA']['TXNDETAILS'][0]['RESP_MSG']:'';
                    break;

                case 'pending':
                    $update['status'] = 'pending';
                    $update['aadhar'] = isset($post['DATA']['TXNDETAILS'][0]['AadharNumber'])?$post['DATA']['TXNDETAILS'][0]['AadharNumber']:'';
                    $update['refno'] = isset($post['DATA']['TXNDETAILS'][0]['RRN'])?$post['DATA']['TXNDETAILS'][0]['RRN']:'';
                    $update['bank'] = isset($post['DATA']['TXNDETAILS'][0]['BANK_NAME']) ? $post['DATA']['TXNDETAILS'][0]['BANK_NAME'] : '';
                    $update['remark'] = isset($post['DATA']['TXNDETAILS'][0]['RESP_MSG']) ? $post['DATA']['TXNDETAILS'][0]['RESP_MSG']:'';
                    break;
                
                case 'failed':
                    $update['status'] = 'failed';
                    $update['aadhar'] = isset($post['DATA']['TXNDETAILS'][0]['AadharNumber'])?$post['DATA']['TXNDETAILS'][0]['AadharNumber']:'';
                    $update['remark'] = isset($post['DATA']['TXNDETAILS'][0]['RESP_MSG']) ? $post['DATA']['TXNDETAILS'][0]['RESP_MSG']:'';
                    $update['refno'] = isset($post['DATA']['TXNDETAILS'][0]['RRN']) ? $post['DATA']['TXNDETAILS'][0]['RRN']:'';
                    $update['bank'] = isset($post['DATA']['TXNDETAILS'][0]['BANK_NAME'])?$post['DATA']['TXNDETAILS'][0]['BANK_NAME']:'';
                    break;

                default:
                    $output['RESP_CODE'] = 302;
                    $output['RESPONSE'] = "Failed";
                    $output['RESP_MSG'] = "Status Not Found";
                    return response()->json($output);
                    break;
            }
        }elseif($post['HEADER']['ST'] == "BALANCEINFO"){
            $output['RESP_CODE'] = 300;
            $output['RESPONSE'] = "Success";
            $output['RESP_MSG'] = "Status Updated Successfully";
            return response()->json($output);
        }else{
            $output['RESP_CODE'] = 302;
            $output['RESPONSE'] = "Failed";
            $output['RESP_MSG'] = "Transaction Failed";
            return response()->json($output);
        }

        $report = Aepsreport::where('txnid', $post['DATA']['TXNDETAILS'][0]['ORDER_ID'])->first();

        if($report && $report->status == "initiated"){
            $action = Aepsreport::where('id', $report->id)->update($update);
            
            if($update['status'] == "success"){
                User::where('id', $report->user_id)->increment('aepsbalance', $report->amount);

                if(isset($report->api->code) && $report->api->code == "matm"){
                    $product = "matm";
                }else{
                    $product = "aeps";
                }

                if($report->amount > 500){
                    if($report->amount>=501 && $report->amount<=1000){
                        $slab = "ptxn1";
                    }elseif($report->amount>1000 && $report->amount<=2000){
                        $slab = "ptxn2";
                    }elseif($report->amount>2000 && $report->amount<=3000){
                        $slab = "ptxn3";
                    }elseif($report->amount>3000 && $report->amount<=3500){
                        $slab = "ptxn4";
                    }elseif($report->amount>3500 && $report->amount<=5000){
                        $slab = "ptxn5";
                    }elseif($report->amount>5000 && $report->amount<=10000){
                        $slab = "ptxn6";
                    }
                    
                    $user   = User::where('id', $report->user_id)->first();

                    $profit = \Mycheck::getCommission($report->amount, $user->scheme_id, $product, $slab);
                    $tds    = $this->getTds($profit);
                    $inserts= [
                        'number'    => $report->aadhar,
                        'mobile'    => $report->mobile,
                        'provider'  => 'aeps',
                        'amount'    => 0,
                        'profit'    => $profit-$tds,
                        'tds'       => $tds,
                        'txn_id'    => $report->id,
                        'api_id'    => $report->api_id,
                        'description'       => "Aeps Commission",
                        'previous_balance'  => $user->balance,
                        'credited_by'       => $user->id,
                        'user_id'           => $user->id,
                        'transaction_type'  => "c",
                        'status'            => "success",
                        'report_type'       => "commission",
                        'wallet'            =>  "balance"
                    ];

                    User::where('id', $user->id)->increment('balance', $profit-$tds);
                    $aepscom = Report::create($inserts);
                    Aepsreport::where('id', $report->id)->update(['charge' => $profit-$tds]);
                    \Mycheck::commission($aepscom, $slab, $product);
                }
            }

            $output['RESP_CODE'] = 300;
            $output['RESPONSE'] = "Success";
            $output['RESP_MSG'] = "Status Updated Successfully";
            return response()->json($output);
        }else{
            $output['RESP_CODE'] = 302;
            $output['RESPONSE'] = "Failedq";
            $output['RESP_MSG'] = "Transaction Not Found";
            return response()->json($output);
        }
    }
}
