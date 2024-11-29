<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Model\Mahaagent;
use App\Model\Mahastate;
use App\Model\Aepsreport;
use App\Model\Report;
use App\Model\Commission;
use App\Model\Provider;
use App\Model\Api;

class AepsController extends Controller
{
    protected $api;
    public function __construct()
    {
        $this->api = Api::where('code', 'aeps')->first();
    }

    public function index(Request $post)
    {
       
        if (\Myhelper::hasRole('admin') || !\Myhelper::can('aeps_service')) {
            abort(403);
        }

        if(!$this->api || $this->api->status == 0){
            abort(405);
        }

        $agent = Mahaagent::where('user_id', \Auth::id())->first();
        $data['mahastate'] = Mahastate::get();
        if(!$agent){
            return view('service.aeps')->with($data);
        }

        $data["bc_id"] = $agent->bc_id;
        $data["phone1"] = $agent->phone1;
        $data["token"] = $this->api->username;

        $url = $this->api->url."initiate";
       
        $header = array("Content-Type: application/json");
        $result = \Myhelper::curl($url, "POST", json_encode($data), $header, "no");
            if($result['response'] != ''){
            $datas = json_decode($result['response']);
            if(isset($datas->statuscode) && $datas->statuscode == "TXN"){
                return \Redirect::away($datas->data);
            }else{
                return redirect(url('dashboard'));
            }
        }else{
            return redirect(url('dashboard'));
        }
    }

    public function registration(Request $post)
    {
        $data["bc_f_name"] = $post->bc_f_name;
        $data["bc_m_name"] = "";
        $data["bc_l_name"] = $post->bc_l_name;
        $data["emailid"] = $post->emailid;
        $data["phone1"] = $post->phone1;
        $data["phone2"] = $post->phone2;
        $data["bc_dob"] = $post->bc_dob;
        $data["bc_state"] = $post->bc_state;
        $data["bc_district"] = $post->bc_district;
        $data["bc_address"] = $post->bc_address;
        $data["bc_block"] = $post->bc_block;
        $data["bc_city"] = $post->bc_city;
        $data["bc_landmark"] = $post->bc_landmark;
        $data["bc_mohhalla"] = $post->bc_mohhalla;
        $data["bc_loc"] = $post->bc_loc;
        $data["bc_pincode"] = $post->bc_pincode;
        $data["bc_pan"] = $post->bc_pan;
        $data["shopname"] = $post->shopname;
        $data["shopType"] = $post->shopType;
        $data["qualification"] = $post->qualification;
        $data["population"] = $post->population;
        $data["locationType"] = $post->locationType;
        $data["token"] = $this->api->username;

        $url = $this->api->url."registration";
        $header = array("Content-Type: application/json");
        $result = \Myhelper::curl($url, "POST", json_encode($data), $header, "yes", "Kyc", \Auth::id());
        if($result['response'] != ''){
            $datas = json_decode($result['response']);
            if(isset($datas->statuscode) && $datas->statuscode == "TXN"){
                $data['bc_id'] = $datas->message;
                $data['user_id'] = \Auth::id();
                $user = Mahaagent::create($data);
                return response()->json(['statuscode'=>'TXN', 'status'=>'Transaction Successfull', 'message'=> "Kyc Submitted"]);
            }else{
                return response()->json(['statuscode'=>'TXF', 'status'=>'Transaction Failed', 'message'=> $datas->message]);
            }
        }else{
            return response()->json(['statuscode'=>'TXF', 'status'=>'Transaction Failed', 'message'=> "Something went wrong"]);
        }
    }

    public function iciciaepslog(Request $post)
    {
        if(!$this->api || $this->api->status == 0){
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }

        $agent = Mahaagent::where('bc_id', $post->BcId)->first();
        $user = User::where('id', $agent->user_id)->first();

        if(!$agent){
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }

        $insert = [
            "mobile" => $post->EndCustMobile,
            "aadhar" => $post->BcId,
            "txnid"  => $post->TransactionId,
            "amount" => $post->Amount,
            "bank"   => $post->BankIIN,
            "user_id"=> $user->id,
            "balance" => $user->aepsbalance,
            'aepstype'=> $post->Txntype,
            'status'  => 'pending',
            'authcode'=> $post->Timestamp,
            'payid'=> $post->TerminalId,
            'TxnMedium'=> $post->TxnMedium,
            'credited_by' => $user->id,
            'api_id' => $this->api->id,
            'type' => 'credit',
            "mytxnid" => $post->mytxnid,
            "terminalid" => $post->terminalid,
            'balance' => $user->aepsbalance
        ];

        $transaction = Aepsreport::create($insert);
        if($transaction){
            $output['TRANSACTION_ID'] = $post->mytxnid;
            $output['VENDOR_ID'] = $post->terminalid;
            $output['STATUS'] = "SUCCESS";
            $output['MESSAGE'] = "Success";
            return response()->json($output);
        }else{
            $output['TRANSACTION_ID'] = date('Ymdhis');
            $output['VENDOR_ID'] = $agent->user_id.date('Ymdhis');
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Service Down";
            return response()->json($output);
        }
    }

    public function iciciaepslogupdate(Request $post)
    {
        $report = Aepsreport::where('mytxnid', $post->TransactionId)->where('terminalid', $post->VenderId)->where('aadhar', $post->BcCode)->first();
        if(!$report){
            $output['STATUS'] = "FAILED";
            $output['MESSAGE'] = "Report Not Found";
            return response()->json($output);
        }

        $user = User::where('id', $report->user_id)->first();

        if(isset($post->Status) && strtolower($post->Status) == "success" && $report->status == "pending"){
            $post['provider_id'] = 0;
            if($report->aepstype == "CW"){
                if($report->amount >= 100 && $report->amount <= 3000){
                    $provider = Provider::where('recharge1', 'aeps1')->first();
                    $post['provider_id'] = $provider->id;
                }elseif($report->amount>3000 && $report->amount<=10000){
                    $provider = Provider::where('recharge1', 'aeps2')->first();
                    $post['provider_id'] = $provider->id;
                }

                if($report->amount > 500){
                    $usercommission = \Myhelper::getCommission($report->amount, $user->scheme_id, $provider->id, $user->role->slug);
                }else{
                    $usercommission = 0;
                }
                
                User::where('id', $report->user_id)->increment('aepsbalance', $report->amount + $usercommission);
            }else{
                $usercommission = 0;
            }

            Aepsreport::where('id', $report->id)->update([
                'status' => "success",
                "refno"  => $post->rrn,
                "balance" => $user->aepsbalance,
                'charge' => $usercommission,
                'provider_id' => $post->provider_id
            ]);
            
            try {
                if($report->amount > 500){
                    $myreport = Aepsreport::where('id', $report->id)->first();
                    \Myhelper::commission($myreport);
                }
            } catch (\Exception $th) {}
                
        }else{
            Aepsreport::where('id', $report->id)->update([
                'status' => "failed",
            ]);
        }
        
        $output['STATUS'] = "SUCCESS";
        $output['MESSAGE'] = "SUCCESS";
        return response()->json($output);
    }
}
