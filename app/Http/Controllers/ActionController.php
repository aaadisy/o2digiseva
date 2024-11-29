<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Pancard;
use App\Model\Api;
use App\Model\Report;
use App\Model\Setting;
use App\Model\Rprovider;
use App\Model\Fingagent;
use App\Model\Aepsdata;
use App\Model\Aepsreport;
use App\Model\Aepstransaction;
use App\Model\Aepsfundrequest;
use App\Model\Nsdlpan;
use App\User;
use Carbon\Carbon;

class ActionController extends Controller
{
    public function reportUpdate(Request $post)
    {
        switch ($post->type) {            
            case 'utiid':
                $permission = "utiid_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                ];
                break;

            case 'utipan':
                $permission = "utipan_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'ref_no' => 'required|max:255',
                ];
                break;

            case 'nsdlpancard':
                $permission = "nsdlpan_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'recharge':
                $permission = "recharge_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'ref_no' => 'required|max:255',
                ];
                break;

            case 'billpay':
                $permission = "billpay_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'ref_no' => 'required|max:255',
                ];
                break;

            case 'money':
                $permission = "money_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'ref_no' => 'required|max:255',
                ];
                break;

            case 'aepskyc':
                $permission = "aepskyc_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'agentcode' => 'required|max:255',
                ];
                break;

            case 'icicikyc':
                    $permission = "aepskyc_report_edit";
                    $rules = [
                        'id'     => 'required|max:255',
                        'status' => 'required|max:255',
                        'type' => 'required',
                    ];
                    break;

            case 'aeps':
                $permission = "aeps_report_edit";
                $rules = [
                    'id'     => 'required|max:255',
                    'status' => 'required|max:255',
                    'refno' => 'required|max:255',
                    'txnid' => 'required|max:255',
                    'payid' => 'required|max:255',
                ];
                break;

            default:
                return response()->json(['status'=>'ERR', 'message' => "Bad Parameter Request"], 400);
                break;
        }

     //   if(!\Mycheck::can($permission)){
      //      return response()->json(['status'=>'ERR', 'message' => "Permission Not Allowed"], 400);
      //  }

        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        switch ($post->type) {
            case 'utiid':
                $report = Pancard::find($post->id);
                break;

            case 'aepskyc':
                $report = Aepsdata::find($post->id);
                break;

            case 'aeps':
                $report = Aepsreport::find($post->id);
                break;

            case 'nsdlpancard':
                $report = Nsdlpan::find($post->id);
                break;

            case 'icicikyc':
                    $report = Fingagent::find($post->id);
                    break;
            
            default:
                $report = Report::find($post->id);
                break;
        }

        if(!$report){
            return response()->json(['status'=>'ERR', 'message' => "Statement Not Found"], 400);
        }

        if(!in_array($report->status, ['accept', 'pending', 'success', 'approved', 'Kyc Submitted', 'initiated'])){
            return response()->json(['status'=>'ERR', 'message' => "Report Editing Not Allowed"], 400);
        }
        
        $refund = "no";
        switch ($post->type) {            
            case 'utiid':
                $update = Pancard::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                break;

            case 'aepskyc':
                $update = Aepsdata::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                break;

            case 'utipan':
                $update = Report::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                if(in_array($report->status, ['accept', 'pending', 'success']) && $post->status == "reversed"){
                    $refund = "yes";
                }
                break;

            case 'recharge':
            case 'billpay':
            case 'money':
                $update = Report::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                if(in_array($report->status, ['accept', 'pending', 'success']) && $post->status == "reversed"){
                    $refund = "yes";
                }
                break;

            case 'aeps':
                $update = Aepsreport::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                break;

            case 'icicikyc':
                    $update = Fingagent::updateOrCreate(
                        ['id'=> $post->id],
                        $post->all()
                    );
                    break;

            case 'nsdlpancard':
                if($post->hardcopy){
                    $post['hardcopydate'] = date('d-m-Y h:i A');
                }else{
                    $post['hardcopydate'] = Null;
                }
                if($post->hasFile('receipts')){
                    try {
                        unlink(public_path('nsdlpanforms/').$report->receipt);
                    } catch (\Exception $e) {
                    }
                    $filename ='nsdlpanforms'.\Auth::id().date('ymdhis').".".$post->file('receipts')->guessExtension();
                    $post->file('receipts')->move(public_path('nsdlpanforms/'), $filename);
                    $post['receipt'] = $filename;
                }
                $update = Nsdlpan::updateOrCreate(
                    ['id'=> $post->id],
                    $post->all()
                );
                if(in_array($report->status, ['accept', 'pending', 'success']) && $post->status == "rejected"){
                    $refund = "yes";
                }
                break;
        }

        if($refund == "yes"){
            if ($post->type != "nsdlpancard") {
                \Mycheck::transactionRefund($report->id);
            }else{
                \Mycheck::transactionRefund($report->txnid);
            }
        }

        if($update){
            return response()->json(['status'=>'success', 'message' => "Updated Successfully"], 200);
        }else{
            return response()->json(['status'=>'fail', 'message' => "Something went wrong"], 400);
        }
    }

    public function reportStatus(Request $post)
    {
        switch ($post->type) {            
            case 'recharge':
                $permission = "recharge_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'billpay':
                $permission = "billpay_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'money':
                $permission = "money_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'utipan':
                $permission = "utipan_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'aepssettlement':
                $permission = "aepssettlement_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            case 'aeps':
                $permission = "aeps_report_status";
                $rules = [
                    'id'     => 'required|max:255',
                ];
                break;

            default:
                return response()->json(['status'=>'ERR', 'message' => "Bad Parameter Request"], 400);
                break;
        }

        if(!\Mycheck::can($permission)){
            return response()->json(['status'=>'ERR', 'message' => "Permission Not Allowed"], 400);
        }

        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        switch ($post->type) {
            case 'aepssettlement' :
                $report = Aepsfundrequest::find($post->id);
                break;

            case 'aeps':
                $report = Aepsreport::find($post->id);
                break;

            default:
                $report = Report::find($post->id);
                break;
        }

        if(!$report){
            return response()->json(['status'=>'ERR', 'message' => "Statement Not Found"], 400);
        }

        switch ($post->type) {
            case 'aepssettlement' :
                if(!in_array($report->status, ['pending'])){
                    return response()->json(['status'=>'ERR', 'message' => "Status Not Allowed"], 400);
                }
                break;

            case 'aeps' :
                if(!in_array($report->status, ['initiated'])){
                    return response()->json(['status'=>'ERR', 'message' => "Status Not Allowed"], 400);
                }
                break;

            default:
                if(!in_array($report->status, ['accept', 'pending', 'success', 'approved'])){
                    return response()->json(['status'=>'ERR', 'message' => "Status Not Allowed"], 400);
                }
                break;
        }

        $api = Api::where('id', $report->api_id)->first();

        switch ($post->type) {            
            case 'recharge':
                switch ($api->code) {
                    case 'erecharge':
                        $url    = $api->url."/status";
                        $parameter['token']  = $api->username;
                        $parameter['txnid']  = $report->txn_id;
                        $method = "POST";
                        break;
                }
                $header = array("Content-Type: application/json");
                break;

            case 'billpay':
                $url    = $api->url."/status";
                $parameter['token']  = $api->username;
                $parameter['txnid']  = $report->txn_id;
                $method = "POST";
                $header = array("Content-Type: application/json");
                break;

            case 'aepssettlement':
                $settlementapi = Api::where('code', 'settlement')->first(['url', 'username', 'status']);
                $parameter['token']    =  $settlementapi->username;
                $parameter['apitxnid'] =  $report->txnid;
                $header = array("Content-Type: application/json");
                $method = "POST";
                $query = http_build_query($parameter);
                $url = $settlementapi->url."status";
                break;

            case 'aeps':
                $url    = "https://partners.easypaisa.in/api/aeps/status";
                $parameter['token']  = $report->api->username;
                $parameter['txnid']  = $report->txnid;
                $method = "POST";
                $header = array("Content-Type: application/json");
                break;
        }

        $result = \Mycheck::curl($url, $method, json_encode($parameter), $header, 'no');
        //dd([$url,json_encode($parameter), $result]);
        if ($result['response'] != '') {
            switch ($post->type) {
                case 'recharge':
                    switch ($api->code) {
                        case 'erecharge':
                            $data =json_decode($result['response']);
                            if(isset($data->status) && $data->status == "TXN"){
                                $status = "success";
                                $refno  = $data->refno;
                                $data->message = "Operator refrence - ".$data->refno;
                            }elseif(isset($data->status) && $data->status == "TUP"){
                                $status = "pending";
                                $refno  = $data->refno;
                                $data->message = "Operator refrence - ".$data->refno;
                            }elseif(isset($data->status) && $data->status == "TXR"){
                                $status = "reversed";
                            }elseif(isset($data->status) && $data->status == "TNF" && $report->status == "accept"){
                                $status = "reversed";
                            }else{
                                $status = "unknown";
                            }

                            if(isset($data->refno) && !isset($data->message)){
                                $data->message = $data->refno;
                            }
                            break;
                    }
                    break;

                case 'billpay':
                    $data =json_decode($result['response']);
                    if(isset($data->status) && $data->status == "TXN"){
                        $status = "success";
                        $refno  = $data->refno;
                        $data->message = "Operator refrence - ".$data->refno;
                    }elseif(isset($data->status) && $data->status == "TUP"){
                        $status = "pending";
                        $refno  = $data->refno;
                        $data->message = "Operator refrence - ".$data->refno;
                    }elseif(isset($data->status) && $data->status == "TXR"){
                        $status = "reversed";
                        $refno  = $data->refno;
                        $data->message = $data->refno;
                    }elseif(isset($data->status) && $data->status == "TNF" && $report->status == "accept"){
                        $status = "reversed";
                    }else{
                        $status = "unknown";
                    }

                    if(isset($data->refno) && !isset($data->message)){
                        $data->message = $data->refno;
                    }
                    break;

                case 'aepssettlement':
                    $data = json_decode($result['response']);
                    if(isset($data->statuscode) && $data->statuscode == "TXN" && $data->status == "approved"){
                        $status = "approved";
                        $refno  = $data->message;
                    }elseif(isset($data->statuscode) && $data->statuscode == "TXN" && $data->status == "rejected"){
                        $status = "rejected";
                        $refno = $data->message;
                    }elseif(isset($data->statuscode) && $data->statuscode == "TXN" && $data->status == "pending"){
                        $status = "pending";
                        $refno = "Pending";
                    }else{
                        $status = "unknown";
                    }
                    break;

                case 'aeps':
                    $data = json_decode($result['response']);
                    if(isset($data->statuscode) && $data->statuscode == "TXN" && $data->status == "success"){
                        $status = "complete";
                        $refno  = $data->refno;
                    }elseif(isset($data->statuscode) && $data->statuscode == "TXN" && $data->status == "failed"){
                        $status = "failed";
                        $refno = $data->refno;
                    }else{
                        $status = "unknown";
                    }
                    break;
            }

            $remark = isset($data->message) ? $data->message : 'Contact service provider';

            if($status != 'unknown' && $status != 'pending'){
                switch ($post->type) {            
                    case 'recharge':
                        Report::where('id', $post->id)->update(['status'=> $status ,'ref_no'=> isset($refno) ? $refno : $remark ]);
                        if($report->status == "accept" && in_array($status, ['success', 'pending'])){
                            $provider = Rprovider::where('name', $report->provider)->first();
                            if($provider){
                                \Mycheck::commission($report, $provider->id, 'recharge');
                            }
                        }
                        break;

                    case 'billpay':
                        Report::where('id', $post->id)->update(['status'=> $status ,'ref_no'=> isset($refno) ? $refno : $remark ]);
                        if($report->status == "accept" && in_array($status, ['success', 'pending'])){
                            $provider = Rprovider::where('name', $report->provider)->first();
                            if($provider){
                                \Mycheck::commission($report, $provider->id, 'recharge');
                            }
                        }
                        break;

                    case 'aepssettlement':
                        $load = Aepsfundrequest::where('id', $post->id)->update(['status'=>$status, 'remark'=> isset($refno) ? $refno : $remark]);
                        if($status == "approved" && $report->status == "pending"){
                            $charge  = Setting::where('name', 'settlementcharge')->first();
                            if($charge){
                                $settlecharge = $charge->value;
                            }else{
                                $settlecharge = 0;
                            }

                            if($report->type == "wallet"){
                                $inserts['payid']   = "Wallet Transfer Request";
                                $inserts['aadhar']  = $report->user->mobile;
                                $settlecharge = 0;
                            }else{
                                $inserts['payid'] = $report->bank." ( ".$report->ifsc.")";
                                $inserts['aadhar']= $report->account;
                                
                            }

                            User::where('id', $report->user_id)->decrement('aepsbalance', $report->amount + $settlecharge);
                            $inserts['charge'] = $settlecharge;
                            $inserts['refno']  = $report->user->name;
                            $inserts['api_id'] = Api::where('code', 'aeps')->first(['id'])->id;
                            $inserts['mobile'] = $report->user->mobile;
                            $inserts['amount'] = $report->amount;
                            $inserts['bank']   = $report->bank;
                            $inserts['txnid']  = $report->id;
                            $inserts['user_id']= $report->user->id;
                            $inserts['credited_by']= $report->user->id;
                            $inserts['balance']    = $report->user->aepsbalance;
                            $inserts['type']       = "debit";
                            $inserts['transtype']  = 'fund';
                            $inserts['status'] = 'success';
                            $inserts['remark'] = isset($refno) ? $refno : $remark;

                            Aepsreport::create($inserts);

                            if($report->type == "wallet"){
                                User::where('id', $report->user_id)->increment('balance', $report->amount);
                                $insert['provider'] = "aepsfund";
                                $insert['txn_id'] = $report->id;
                                $insert['amount'] = $report->amount;
                                $insert['number'] = $report->user->mobile;
                                $insert['user_id']= $report->user_id;
                                $insert['ref_no'] = $report->user->name;
                                $insert['status'] = "success";
                                $insert['previous_balance'] = $report->user->balance;
                                $insert['wallet'] = "main";
                                $insert['description'] = "Aeps Fund Recieved";
                                $insert['report_type'] = "main";
                                $insert['transaction_type'] = "c";
                                $insert['credited_by'] = \Auth::user()->id;
                                Report::create($insert);
                            }
                        }
                        break;

                    case 'aeps':
                        $remark = $refno;
                        Aepsreport::where('id', $post->id)->update(['status'=> $status ,'refno'=> $refno ]);
                        if(in_array($report->status, ["initiated", 'pending']) && in_array($status, ['complete'])){
                            User::where('id', $report->user_id)->increment('aepsbalance', $report->amount);
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
                                $profit = \Mycheck::getCommission($report->amount, $user->scheme_id,'aeps', $slab);
                                $tds    = $this->getTds($profit);
                                
                                $insert = [
                                    "mobile"  => $report->mobile,
                                    "aadhar"  => $user->mobile,
                                    "txnid"   => $report->id,
                                    "refno"   => "Txn ".$report->id." Cleared",
                                    "amount"  => $report->amount,
                                    "charge"  => $profit,
                                    "user_id" => $report->user_id,
                                    "balance" => $user->aepsbalance,
                                    'type'    => "none",
                                    'api_id'  => $report->api_id,
                                    'credited_by' => $report->user_id,
                                    'status'    => 'success',
                                    'rtype'     => 'main',
                                    'transtype' => 'transaction'
                                ];
                                $report = Aepsreport::create($insert);

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
                                \Mycheck::commission($aepscom, $slab, 'aeps');
                            }
                        }
                        break;
                }

                if($status == "reversed"){
                    \Mycheck::transactionRefund($report->id);
                }
            }

            return response()->json(['status'=>'success', 'message' => "Transaction Successfully", "data" => [
                "reportstatus" => ucfirst($status),
                "remark" => $remark
            ]], 200);
        }

        return response()->json(['status'=>'fail', 'message' => $result['error']], 400);
    }
}
