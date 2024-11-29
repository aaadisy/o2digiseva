<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Report;
use App\Model\Utiid;
use App\Model\Aepsreport;
use App\Model\Aepsfundrequest;
use App\Model\Api;
use App\User;

class CallbackController extends Controller
{
    public function recharge(Request $post, $api)
    {

        switch ($api) {
            case 'secure':
                $report = Report::where('txnid', $post->RequestID)->first();
                if(isset($post->Status) && strtolower($post->Status) == "success"){
                    $update['status'] = "success";
                    $update['refno']  = isset($post->TransID) ? $post->TransID : 'pending';
                }elseif(isset($post->Status) && strtolower($post->Status) == "fail"){
                    $update['status'] = "reversed";
                    $update['refno']  = isset($post->TransID) ? $post->TransID : 'failed';
                }else{
                    $update['status'] = "pending";
                }
                break;
            
            default:
                return response('');
                break;
        }

        if(isset($update['status']) && $update['status'] != "pending" && isset($report) && $report){
            if($report && (in_array($report->status, ['success', 'pending']))){
                $updates = Report::where('id', $report->id)->update($update);
                if($updates){
                    if($update['status'] == "reversed"){
                        try {
                            \Myhelper::transactionRefund($report->id);
                        } catch (\Exception $e) {}
                    }
                }
            }
        }
    }

    public function payout(Request $post)
    {
        \DB::table('paytmlogs')->insert(['response' => json_encode($post->all()), 'txnid' => $post->result['orderId']]);
        $report = Aepsfundrequest::where('payoutid', $post->result['orderId'])->first();
        //dd($report);
        if($report){
            if(strtolower($post->status) == "success"){
                Aepsreport::where('txnid', $post->result['orderId'])->update([
                    'status' => 'success',
                    'refno' => $post->result['rrn']
                ]);

                Aepsfundrequest::where('payoutid', $post->result['orderId'])->update([
                    'payoutref' => $post->result['rrn'],
                    'status'    => 'approved'
                ]);
            }elseif (strtolower($post->status) == "failure") {
                Aepsreport::where('txnid', $post->result['orderId'])->update([
                    'status' => 'reversed',
                    'refno' => $post->result['rrn']
                ]);

                Aepsfundrequest::where('id', $report->id)->update(['status' => "rejected", 'payoutref' => $post->result['rrn']]);
                $aepsreport = Aepsreport::where('txnid', $post->result['orderId'])->first();
                $aepsreports['api_id'] = $aepsreport->api_id;
                $aepsreports['payid']  = $aepsreport->payid;
                $aepsreports['mobile'] = $aepsreport->mobile;
                $aepsreports['refno']  = $aepsreport->refno;
                $aepsreports['aadhar'] = $aepsreport->aadhar;
                $aepsreports['amount'] = $aepsreport->amount;
                $aepsreports['charge'] = $aepsreport->charge;
                $aepsreports['bank']   = $aepsreport->bank;
                $aepsreports['txnid']  = $aepsreport->id;
                $aepsreports['user_id']= $aepsreport->user_id;
                $aepsreports['credited_by'] = $aepsreport->credited_by;
                $aepsreports['balance']     = $aepsreport->user->aepsbalance;
                $aepsreports['type']        = "credit";
                $aepsreports['transtype']   = 'fund';
                $aepsreports['status'] = 'refunded';
                $aepsreports['remark'] = "Bank Settlement Refunded";

                User::where('id', $aepsreports['user_id'])->increment('aepsbalance', $aepsreports['amount'] + $aepsreports['charge']);
                Aepsreport::create($aepsreports);
            }
        }
    }
    
    public function callback(Request $post, $api)
    {
        switch ($api) {
            case 'payout':
                $fundreport = Aepsfundrequest::where('payoutid', $post->txnid)->first();
                if($fundreport && in_array($fundreport->status , ['pending', 'approved'])){
                    if(strtolower($post->status) == "success"){
                        $update['status'] = "approved";
                        $update['payoutref'] = $post->refno;
                    }elseif (strtolower($post->status) == "reversed") {
                        $update['status'] = "rejected";
                        $update['payoutref'] = $post->refno;
                    }else{
                        $update['status'] = "pending";
                    }
                    
                    if($update['status'] != "pending"){
                        $action = Aepsfundrequest::where('id', $fundreport->id)->update($update);
                        if ($action) {
                            if($update['status'] == "rejected"){
                                $report = Aepsreport::where('txnid', $fundreport->payoutid)->update(['status' => "reversed"]);
                                $report = Aepsreport::where('txnid', $fundreport->payoutid)->first();
                                $aepsreports['api_id'] = $report->api_id;
                                $aepsreports['payid']  = $report->payid;
                                $aepsreports['mobile'] = $report->mobile;
                                $aepsreports['refno']  = $report->refno;
                                $aepsreports['aadhar'] = $report->aadhar;
                                $aepsreports['amount'] = $report->amount;
                                $aepsreports['charge'] = $report->charge;
                                $aepsreports['bank']   = $report->bank;
                                $aepsreports['txnid']  = $report->id;
                                $aepsreports['user_id']= $report->user_id;
                                $aepsreports['credited_by'] = $report->credited_by;
                                $aepsreports['balance']     = $report->user->aepsbalance;
                                $aepsreports['type']        = "credit";
                                $aepsreports['transtype']   = 'fund';
                                $aepsreports['status'] = 'refunded';
                                $aepsreports['remark'] = "Bank Settlement";
                                Aepsreport::create($aepsreports);
                                User::where('id', $aepsreports['user_id'])->increment('aepsbalance',$aepsreports['amount']+$aepsreports['charge']);
                            }
                        }
                    }
                }
                break;
            
            default:
                return response('');
                break;
        }
    }
}
