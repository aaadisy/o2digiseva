<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Aepsfundrequest;
use App\Model\Fundbank;
use App\Model\Paymode;
use App\Model\Report;
use App\Model\Aepsreport;
use App\Model\Fundreport;
use App\Model\PortalSetting;
use App\Model\Provider;
use App\Model\Api;
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

    public function transaction(Request $request)
    {
        switch ($request->type) {
            case 'bank':
                $banksettlementtype = $this->banksettlementtype();

                if($banksettlementtype == "down"){
                    return response()->json(['statuscode' => "ERR", 'message' => "Aeps Settlement Down For Sometime"],400);
                }

                $user = User::where('id', $request->user_id)->first();

                if(!\Myhelper::can('aeps_fund_request', $user->id)){
                    return response()->json(['statuscode' => "ERR", 'message' => "Permission not allowed"],400);
                }

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

                    $request['account'] = $user->account;
                    $request['bank']    = $user->bank;
                    $request['ifsc']    = $user->ifsc;
                }

                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }

                if($user->account == '' && $user->bank == '' && $user->ifsc == ''){
                    User::where('id',$user->id)->update(['account' => $request->account, 'bank' => $request->bank, 'ifsc'=>$request->ifsc]);
                }

                $settlerequest = Aepsfundrequest::where('user_id', $user->id)->where('status', 'pending')->count();
                if($settlerequest > 0){
                    return response()->json(['statuscode' => "ERR", 'message' => "One request is already submitted"], 400);
                }

                if($request->amount <= 1000){
                    $request['charge'] = $this->settlementcharge1k();
                }elseif($request->amount > 1000 && $request->amount <= 25000){
                    $request['charge'] = $this->settlementcharge25k();
                }else{
                    $request['charge'] = $this->settlementcharge2l();
                }

                if($user->aepsbalance - $this->aepslocked() < $request->amount + $request->charge){
                    return response()->json(['statuscode' => "ERR", 'message' => "Low aeps balance to make this request."], 400);
                }

                if($banksettlementtype == "auto"){

                    $previousrecharge = Aepsfundrequest::where('account', $request->account)->where('amount', $request->amount)->where('user_id', $request->user_id)->whereBetween('created_at', [Carbon::now()->subSeconds(30)->format('Y-m-d H:i:s'), Carbon::now()->addSeconds(30)->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge){
                        return response()->json(['statuscode'=>"ERR", 'message' => "Transaction Allowed After 1 Min."]);
                    } 

                    $api = Api::where('code', 'psettlement')->first();

                    do {
                        $request['payoutid'] = $this->transcode().rand(111111111111, 999999999999);
                    } while (Aepsfundrequest::where("payoutid", "=", $request->payoutid)->first() instanceof Aepsfundrequest);

                    $request['status']   = "pending";
                    $request['pay_type'] = "payout";
                    $request['payoutid'] = $request->payoutid;
                    $request['payoutref']= $request->payoutid;
                    $request['create_time']= Carbon::now()->toDateTimeString();
                    try {
                        $aepsrequest = Aepsfundrequest::create($request->all());
                    } catch (\Exception $e) {
                        return response()->json(['statuscode' => "ERR", 'message' => "Duplicate Transaction Not Allowed, Please Check Transaction History"]);
                    }
                    
                    $aepsreports['api_id'] = $api->id;
                    $aepsreports['payid']  = $aepsrequest->id;
                    $aepsreports['mobile'] = $user->mobile;
                    $aepsreports['refno']  = "success";
                    $aepsreports['aadhar'] = $request->account;
                    $aepsreports['amount'] = $request->amount;
                    $aepsreports['charge'] = $request->charge;
                    $aepsreports['bank']   = $request->bank."(".$request->ifsc.")";
                    $aepsreports['txnid']  = $request->payoutid;
                    $aepsreports['user_id']= $user->id;
                    $aepsreports['credited_by'] = $this->admin->id;
                    $aepsreports['balance']     = $user->aepsbalance;
                    $aepsreports['type']        = "debit";
                    $aepsreports['transtype']   = 'fund';
                    $aepsreports['status'] = 'success';
                    $aepsreports['remark'] = "Bank Settlement";

                    User::where('id', $aepsreports['user_id'])->decrement('aepsbalance',$aepsreports['amount']+$aepsreports['charge']);
                    $myaepsreport = Aepsreport::create($aepsreports);
                    $url = $api->url;

                    $parameter = [
                        "apitxnid" => $request->payoutid,
                        "amount"   => $request->amount, 
                        "account"  => $request->account,
                        "name"     => $user->name,
                        "bank"     => $request->bank,
                        "ifsc"     => $request->ifsc,
                        "ip"     => $request->ip(),
                        "token"    => $api->username,
                        'callback' => url('api/callback/update/payout')
                    ];
                    $header = array("Content-Type: application/json");

                    if(env('APP_ENV') != "local"){
                        $result = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, 'yes', '\App\Model\Aepsfundrequest', $request->payoutid);
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
                        return response()->json(['statuscode' => "TXN", "message" => "Aeps fund request submitted successfully", "txnid" => $aepsrequest->id],200);
                    }elseif(isset($response->status) && in_array($response->status, ['ERR', 'TXF'])){
                        User::where('id', $aepsreports['user_id'])->increment('aepsbalance', $aepsreports['amount']+$aepsreports['charge']);
                        Aepsreport::where('id', $myaepsreport->id)->update(['status' => "failed", "refno" => isset($response->rrn) ? $response->rrn : $response->message]);

                        Aepsfundrequest::where('id', $aepsrequest->id)->update(['status' => "rejected"]);
                        return response()->json(['statuscode' => "TXF", "message" => $response->message], 400);
                    }else{
                        Aepsfundrequest::where('id', $aepsrequest->id)->update(['status' => "pending"]);
                        return response()->json(['statuscode' => "TUP", "message" => "Transaction Under Pending"]);
                    }
                }else{
                    $request['pay_type'] = "manual";
                    $aepsrequest = Aepsfundrequest::create($request->all());
                }

                if($aepsrequest){
                    return response()->json(['statuscode' => "TXN", "message" => "Aeps fund request submitted successfully", "txnid" => $aepsrequest->id],200);
                }else{
                    return response()->json(['statuscode'=>"ERR", 'message' => "Something went wrong."]);
                }
                break;

            case 'wallet':
                $settlementtype = $this->settlementtype();

                if($settlementtype == "down"){
                    return response()->json(['statuscode'=>"ERR", 'message' => "Aeps Settlement Down For Sometime"],400);
                }

                $rules = array(
                    'amount'    => 'required|numeric|min:1',
                );
        
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $user = User::where('id', $request->user_id)->first();
                
                if(!\Myhelper::can('aeps_fund_request', $user->id)){
                    return response()->json(['statuscode'=>"ERR", 'message' => "Permission not allowed"],400);
                }
                
                $myrequest = Aepsfundrequest::where('user_id', $user->id)->where('status', 'pending')->count();
                if($myrequest > 0){
                    return response()->json(['statuscode'=>"ERR", 'message' => "One request is already submitted"], 400);
                }

                if($user->aepsbalance - $this->aepslocked() < $request->amount){
                    return response()->json(['statuscode'=>"ERR", 'message' => "Low aeps balance to make this request"], 400);
                }

                if($settlementtype == "auto"){
                    $previousrecharge = Aepsfundrequest::where('type', $request->type)->where('amount', $request->amount)->where('user_id', $request->user_id)->whereBetween('created_at', [Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'), Carbon::now()->format('Y-m-d H:i:s')])->count();
                    if($previousrecharge > 0){
                        return response()->json(['statuscode'=>"ERR", 'message' => "Transaction Allowed After 5 Min."]);
                    }

                    $request['status'] = "approved";
                    $load = Aepsfundrequest::create($request->all());
                    $payee = User::where('id', $user->id)->first();
                    User::where('id', $payee->id)->decrement('aepsbalance', $request->amount);
                    $inserts = [
                        "mobile"  => $payee->mobile,
                        "amount"  => $request->amount,
                        "bank"    => $payee->bank,
                        'txnid'   => date('ymdhis'),
                        'refno'   => $request->refno,
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

                    if($request->type == "wallet"){
                        $provide = Provider::where('recharge1', 'aepsfund')->first();
                        User::where('id', $payee->id)->increment('mainwallet', $request->amount);
                        $insert = [
                            'number' => $payee->account,
                            'mobile' => $payee->mobile,
                            'provider_id' => $provide->id,
                            'api_id' => $this->fundapi->id,
                            'amount' => $request->amount,
                            'charge' => '0.00',
                            'profit' => '0.00',
                            'gst' => '0.00',
                            'tds' => '0.00',
                            'txnid' => $load->id,
                            'payid' => $load->id,
                            'refno' => $request->refno,
                            'description' =>  "Aeps Fund Recieved",
                            'remark' => $request->remark,
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
                    $load = Aepsfundrequest::create($request->all());
                }

                if($load){
                    return response()->json(['statuscode' => "TXN", "message" => "Aeps fund request submitted successfully", "txnid" => $load->id],200);
                }else{
                    return response()->json(['statuscode' => "ERR", 'message' => "Transaction Failed"]);
                }
                break;

            case 'request':
                if(!\Myhelper::can('fund_request', $request->user_id)){
                    return response()->json(['statuscode' => "ERR", "message" => "Permission not allowed"]);
                }

                $rules = array(
                    'fundbank_id'    => 'required|numeric',
                    'paymode'    => 'required',
                    'amount'    => 'required|numeric|min:100',
                    'ref_no'    => 'required|unique:fundreports,ref_no',
                    'paydate'    => 'required',
                    'apptoken'    => 'required'
                );
        
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }
                $user = User::where('id', $request->user_id)->first();

                $request['user_id'] = $user->id;
                $request['credited_by'] = $user->parent_id;
                if(!\Myhelper::can('setup_bank', $user->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', $user->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $request['credited_by'] = $admin->id;
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $request['credited_by'] = $admin->id;
                    }
                }

                $request['status'] = "pending";
                $action = Fundreport::create($request->all());
                if($action){
                    return response()->json(['statuscode' => "TXN", "message" => "Fund request send successfully", "txnid" => $action->id]);
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Something went wrong, please try again."]);
                }
                break;

            case 'getfundbank':
                $rules = array(
                    'apptoken' => 'required',
                    'user_id'  => 'required|numeric'
                );
        
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }
                $user = User::where('id', $request->user_id)->first();
                $data['banks'] = Fundbank::where('user_id', $user->parent_id)->where('status', '1')->get();
                if(!\Myhelper::can('setup_bank', $user->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', $user->company_id)->first(['id']);

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
                return response()->json(['statuscode' => "TXN", "message" => "Get successfully", "data" => $data]);
                break;

            default :
                return response()->json(['statuscode' => "ERR", 'message' => "Bad Parameter Request"]);
            break;
        }
    }
}
