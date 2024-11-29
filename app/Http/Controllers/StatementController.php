<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Utireport;
use Carbon\Carbon;
use App\Model\Rechargereport;
use App\Model\Billpayreport;
use App\Model\Moneyreport;
use App\User;
use App\Model\AccountStatement;
use App\Model\Aepsreport;
use App\Model\Report;
use App\Model\Fundreport;
use Db;

class StatementController extends Controller
{
    
    public function exportapilog() {
    // Correct the whereDate condition
    $apilogs = \App\Model\Apilog::whereDate('created_at', date('Y-m-d'))
                                ->whereIn('txnid', ['CW3way','MATM3way','AP3way'])->get();

    // Define column names
    $column_names = array(
        'merchantTransactionId',
        'fingpayTransactionId',
        'transactionRrn',
        'responseCode',
        'referenceId',
        'transactionDate',
        'serviceType',
        'merchantId'
    );

    // Download file
    header("Content-Disposition: attachment; filename=\"output.csv\"");
    header("Content-Type: text/csv");

    // Output the column names as the first row
    echo implode(",", $column_names) . "\r\n";

    // Output each row of data
    foreach ($apilogs as $apilog) {
        $data = json_decode($apilog->response);
        $data = json_decode($data);
        
        
        $data = $data->data;
      

        foreach ($data as $row) {
            // Prepare data for CSV output
            $csv_row = array();
            foreach ($column_names as $column) {
                $csv_row[] = isset($row->$column) ? '"' . str_replace('"', '""', $row->$column) . '"' : '';
            }
            echo implode(",", $csv_row) . "\r\n";
        }
    }
    // Terminate the script after CSV generation
    exit();
}

    public function index($type, $id=0)
    {
        if($id != 0){
            $user = User::where('id', $id)->first();
            if (!$user) {
                abort('404');
            }
        }

        switch ($type) {
            case 'account':
                if($id == 0){
                    $permission = "account_statement";
                }else{
                    $permission = "member_account_statement_view";
                }
                break;
            
            case 'utiid':
                if($id == 0){
                    $permission = "utiid_statement";
                }else{
                    $permission = "member_utiid_statement_view";
                }
                break;

            case 'utipancard':
                if($id == 0){
                    $permission = "utipancard_statement";
                }else{
                    $permission = "member_utipancard_statement_view";
                }
                break;
            
            case 'billpay':
                if($id == 0){
                    $permission = "billpayment_statement";
                }else{
                    $permission = "member_billpayment_statement_view";
                }
                break;
            case 'insurancebillpay':
                if($id == 0){
                    $permission = "billpayment_statement";
                }else{
                    $permission = "member_billpayment_statement_view";
                }
                break;
                
            case 'cashdepositbillpay':
                if($id == 0){
                    $permission = "billpayment_statement";
                }else{
                    $permission = "member_billpayment_statement_view";
                }
                break;
                
            case 'wallettransferbillpay':
                if($id == 0){
                    $permission = "billpayment_statement";
                }else{
                    $permission = "member_billpayment_statement_view";
                }
                break;

            case 'recharge':
                if($id == 0){
                    $permission = "recharge_statement";
                }else{
                    $permission = "member_recharge_statement_view";
                }
                break;
            case 'commission':
                if($id == 0){
                    $permission = "recharge_statement";
                }else{
                    $permission = "member_recharge_statement_view";
                }
                break;

             case 'aepscommission':
                if($id == 0){
                    $permission = "recharge_statement";
                }else{
                    $permission = "member_recharge_statement_view";
                }
                break;


            case 'money':
                if($id == 0){
                    $permission = "money_statement";
                }else{
                    $permission = "member_money_statement_view";
                }
                break;
                
            case 'apilog':
                if($id == 0){
                    $permission = "aeps_statement";
                }else{
                    $permission = "member_aeps_statement_view";
                }
                break;

            case 'aeps':
                if($id == 0){
                    $permission = "aeps_statement";
                }else{
                    $permission = "member_aeps_statement_view";
                }
                break;

            case 'ministatement':
                if($id == 0){
                    $permission = "aeps_statement";
                }else{
                    $permission = "member_aeps_statement_view";
                }
                break;

            case 'matm':
                if($id == 0){
                    $permission = "aeps_statement";
                }else{
                    $permission = "member_aeps_statement_view";
                }
                break;

            case 'aadharpay':
                if($id == 0){
                    $permission = "aeps_statement";
                }else{
                    $permission = "member_aeps_statement_view";
                }
                break;

            case 'aepsid':
                if($id == 0){
                    $permission = "aepsid_statement";
                }else{
                    $permission = "member_aepsid_statement";
                }
                break;

            case 'awallet':
                if($id == 0){
                    $permission = "awallet_statement";
                }else{
                    $permission = "member_awallet_statement_view";
                }
                break;

            case 'icicikyc':
                if($id == 0){
                    $permission = "aepsid_statement";
                }else{
                    $permission = "member_aepsid_statement";
                }
                break;
            
            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }

        if($id != 0){
            $agentfilter = "hide";
        }else{
            $agentfilter = "";
        }

        return view('statement.'.$type)->with(['id' => $id, 'agentfilter' => $agentfilter]);
    }
    
    public function agentFilter($post)
	{
		if (\Myhelper::hasRole('admin') || in_array($post->agent, session('parentData'))) {
			return \Myhelper::getParents($post->agent);
		}else{
			return [];
		}
	}
    
    public function fetchData(Request $request, $type, $id=0)
    {
    	$parentData = session('parentData');
        switch ($type) {
            case 'complaints':
                $request['table']= '\App\Model\Complaint';
                $request['searchdata'] = ['id', 'product', 'subject', 'description', 'solution', 'transaction_id', 'status', 'user_id', 'resolve_id'];
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                $request['parentData'] = 'all';
            break;
            
            case 'permissions':
                $request['table']= '\App\Model\Permission';
                $request['searchdata'] = ['name', 'display_name'];
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                $request['parentData'] = 'all';
            break;

            case 'roles':
                $request['table']= '\App\Model\Role';
                $request['searchdata'] = ['name', 'display_name'];
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                $request['parentData'] = 'all';
            break;

            case 'api':
                $request['table']= '\App\Model\Api';
                $request['searchdata'] = ['name', 'api_name', 'code'];
                $request['select'] = 'all';
                $request['order'] = ['id','ASC'];
                $request['parentData'] = 'all';
                $request['whereIn'] = 'user_id';
            break;

            case 'bankaccount':
                $request['table']= '\App\Model\Netbank';
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                if($id != 0){
                    $request['parentData'] = [$id];
                }else{
                    $request['parentData'] = [\Auth::id()];
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'operator':
                $request['table']= '\App\Model\Rprovider';
                $request['searchdata'] = ['provider_name'];
                $request['select'] = 'all';
                $request['order'] = ['id','ASC'];
                $request['parentData'] = 'all';
            break;

            case 'scheme':
                $request['table']= '\App\Model\Scheme';
                $request['searchdata'] = ['name'];
                $request['select'] = 'all';
                $request['order'] = ['id','ASC'];
                if($id != 0){
                    $request['parentData'] = [$id];
                }else{
                    $request['parentData'] = [\Auth::id()];
                }

                $request['whereIn'] = 'user_id';
            break;

            case 'company':
                $request['table']= '\App\Model\Company';
                $request['searchdata'] = ['id','name', 'website','code','address'];
                $request['select'] = 'all';
                $request['order'] = ['id','DESC'];
                $request['parentData'] = 'all';
            break;

            case 'whitelable':
            case 'md':
            case 'distributor':
            case 'retailer':
            case 'fundaction':
                $request['table']= '\App\User';
                $request['searchdata'] = ['id','name', 'mobile','email', 'parent_id'];
                $request['select'] = 'all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }

                $request['whereIn'] = 'id';
            break;

            case 'loadcash':
                $request['table']= '\App\Model\Loadcash';
                $request['searchdata'] = ['bankref', 'amount'];
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                $request['parentData'] = [\Auth::id()];
                $request['whereIn'] = 'user_id';
            break;

            case 'requestview':
            case 'fundrequest':
                $request['table']= '\App\Model\Loadcash';
                $request['searchdata'] = ['bankref', 'amount'];
                $request['select'] = 'all';
                $request['order'] = ['id','desc'];
                $request['parentData'] = [\Auth::id()];
                $request['whereIn'] = 'credited_by';
            break;

            case 'fund':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] = ['id', 'number', 'mobile', 'ref_no','txnid','description'];
                $request['select']='all';
                $request['order'] = ['id','DESC'];
                $request['parentData'] = [\Auth::id()];
                $request['whereIn'] = 'user_id';
            break;

            case 'utipanstatement':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] =['number','mobile','id'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'nsdlpanstatement':
                $request['table']= '\App\Model\Nsdlpan';
                $request['searchdata'] =['id'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'rechargestatement':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] =['number','mobile','id','txn_id', 'pay_id', 'ref_no', 'amount'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'billpaystatement':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] =['number','mobile','id','txn_id', 'pay_id', 'ref_no', 'amount'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'moneystatement':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] =['number','mobile','id','txn_id', 'pay_id', 'ref_no', 'amount'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'utiidstatement':
                $request['table']= '\App\Model\Pancard';
                $request['searchdata'] =['vle_id','mobile','id', 'vle_name', 'email'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'aepskycstatement':
                $request['table']= '\App\Model\Aepsdata';
                $request['searchdata'] =['name','email','mobile','agentcode'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'ministatement':
            case 'aepsstatement':
            case 'matmstatement':
                $request['table']= '\App\Model\Aepsreport';
                $request['searchdata'] =['mobile','aadhar','txnid','refno', 'payid'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'wallet':
                $request['table']= '\App\Model\Report';
                $request['searchdata'] =['number','mobile','id', 'amount', 'txn_id', 'pay_id', 'ref_no', 'beneficiari_id', 'user_id', 'credited_by'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                $request['parentData'] = [\Auth::id()];
                $request['whereIn'] = 'user_id';
            break;

            case 'aepsfundrequest':
            case 'aepsfundrequestall':
                $request['table']= '\App\Model\Aepsfundrequest';
                $request['searchdata'] =['amount','remark'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            case 'fingaepskycstatement':
                $request['table']= '\App\Model\Fingagent';
                $request['searchdata'] =['merchantPhoneNumber','userPan','merchantAadhar','merchantName'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if(isset($request->agent) && !empty($request->agent)){
                         $request['parentData'] = [$request->agent];
                    }
                    else{
                        if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                    }
                    
                    
                }
                
                
                
                $request['whereIn'] = 'user_id';
            break;

            case 'cyrusskycstatement':
                $request['table']= '\App\Model\Fingagent';
                $request['searchdata'] =['merchantPhoneNumber','userPan','merchantAadhar'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;
            case 'complain':
                $request['table']= '\App\Model\Complain';
                $request['searchdata'] =['amount','remark'];
                $request['select'] ='all';
                $request['order'] = ['id','DESC'];
                if (\Myhelper::hasRole(['retailer', 'apiuser'])){
                    $request['parentData'] = [\Auth::id()];
                }elseif(\Myhelper::hasRole(['md', 'distributor', 'whitelable'])){
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = $parentData;
                    }
                    
                }else{
                    if($id != 0){
                        $request['parentData'] = [$id];
                    }else{
                        $request['parentData'] = 'all';
                    }
                }
                $request['whereIn'] = 'user_id';
            break;

            default:
                # code...
                break;
        }
        $request->where=0;
		
        try {
            //return $request;
            $totalData = $this->getData($request, 'count');
            //dd($totalData); exit;
        } catch (\Exception $e) {
            $totalData = 0;
        }
        //dd([$request->from_date, $request->searchtext, $request->to_date, $request->option1, $request->option2, $request->option3, $request->status, $request->mode]); exit;
        if ((isset($request->from_date) && !empty($request->from_date))   ||
            (isset($request->searchtext) && !empty($request->searchtext)) ||
            (isset($request->to_date) && !empty($request->to_date))       ||
            (isset($request->option1) && !empty($request->option1))       ||
            (isset($request->option2) && !empty($request->option2))       ||
            (isset($request->option3) && !empty($request->option3))       ||
            (isset($request->status) && !empty($request->status))         ||
            (isset($request->mode) && !empty($request->mode))
         ) 
        {
            $request->where = 1;
        }

        try {
            $totalFiltered = $this->getData($request, 'count');
        } catch (\Exception $e) {
            $totalFiltered = 0;
        }

        try {
            $data = $this->getData($request, 'data');
            //dd($data); exit;
        } catch (\Exception $e) {
            $data = [];
        }
        
        $json_data = array(
            "draw"            => intval( $request['draw'] ),
            "recordsTotal"    => intval( $totalData ),
            "recordsFiltered" => intval( $totalFiltered ),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function getData($request, $returntype)
    { 
        $table = $request->table;
        $data = $table::query();
        $data->orderBy($request->order[0], $request->order[1]);

        if($request->parentData != 'all'){
            $data->whereIn($request->whereIn, $request->parentData);
        }

        switch ($request->type) {
            case 'whitelable':
                $data->whereHas('role', function ($q){
                    $q->where('role_slug', 'whitelable');
                });
                $data->with(['role']);
            break;

            case 'md':
                $data->whereHas('role', function ($q){
                    $q->where('role_slug', 'md');
                });
                $data->with(['role']);
            break;

            case 'distributor':
                $data->whereHas('role', function ($q){
                    $q->where('role_slug', 'distributor');
                });
                $data->with(['role']);
            break;

            case 'retailer':
                $data->whereHas('role', function ($q){
                    $q->where('role_slug', 'retailer');
                });
                $data->with(['role']);
            break;

            case 'fundaction':
                $data->whereHas('role', function ($q){
                    $q->whereNotIn('role_slug', ['admin']);
                });
                $data->with(['role']);
                break;

            case 'requestview':
                $data->where('status', 'pending');
                break;

            case 'utipanstatement':
                $data->whereHas('api', function ($q){
                    $q->where('code', 'utipancard');
                })->where('report_type', 'main');
            break;

            case 'rechargestatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['erecharge', 'mrobotics']);
                })->where('report_type', 'main');
            break;

            case 'billpaystatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['billpay']);
                })->where('report_type', 'main');
            break;

            case 'moneystatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['dmt']);
                })->where('report_type', 'main');
            break;

            case 'aepsfundrequest':
                $data->where('status', 'pending');
            break;

            case 'aepsstatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['aeps', 'iciciaeps']);
                })->where('rtype', 'main')->whereIn('transtype', ['transaction', 'fund'])->where('product', 'aeps');
            break;
            case 'ministatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['aeps', 'iciciaeps']);
                })->where('rtype', 'main')->where('transtype', 'transaction')->where('product', 'aeps')->where('aepstype', 'MS');
            break;
            case 'complain':
                /*$data->whereHas('api', function ($q){
                    $q->whereIn('code', ['aeps', 'iciciaeps']);
                })->where('rtype', 'main')->where('transtype', 'transaction');*/
            break;

            case 'matmstatement':
                $data->whereHas('api', function ($q){
                    $q->whereIn('code', ['matm']);
                })->where('rtype', 'main')->where('product', 'matm');
            break;
        }

        if ($request->where) {
            if((isset($request->from_date) && !empty($request->from_date)) 
                && (isset($request->to_date) && !empty($request->to_date))){
                if($request->from_date == $request->to_date){
                    $data->whereDate('created_at','=', Carbon::createFromFormat('d-M-Y', $request->from_date)->format('Y-m-d'));
                }else{
                    $data->whereBetween('created_at', [Carbon::createFromFormat('d-M-Y', $request->from_date)->format('Y-m-d'), Carbon::createFromFormat('d-M-Y', $request->to_date)->addDay(1)->format('Y-m-d')]);
                }
            }elseif (isset($request->from_date) && !empty($request->from_date)) {
                $data->whereDate('created_at','=', Carbon::createFromFormat('d-M-Y', $request->from_date)->format('Y-m-d'));
            }

            if(isset($request->option1) && !empty($request->option1)){
                switch ($request->type) {
                    case 'fundrequest':
                        $data->where('netbank_id', $request->option1);
                    break;

                    case 'rechargestatement':
                        $data->where('provider', $request->option1);
                    break;

                    case 'wallet':
                        $data->where('api_id', $request->option1);
                    break;

                    case 'whitelable':
                    case 'md':
                    case 'distributor':
                    case 'retailer':
                    case 'apiuser':
                    case 'kycuser':
                        $data->where('kyc', $request->option1);
                    break;
                }
            }

            if(isset($request->option2) && !empty($request->option2)){
                switch ($request->type) {
                    case 'fundrequest':
                        $data->where('pmethod_id', $request->option2);
                    break;

                    case 'wallet':
                        $data->where('transaction_type', $request->option2);
                    break;
                }
            }

            if(isset($request->option3) && !empty($request->option3)){
                switch ($request->type) {
                    case 'wallet':
                        $data->where('report_type', $request->option3);
                    break;
                }
            }

            if(isset($request->status) && !empty($request->status)){
                switch ($request->type) {
                    case 'whitelable':
                    case 'md':
                    case 'distributor':
                    case 'retailer':
                    case 'apiuser':
                    case 'kycuser':
                        $data->where('status_id', $request->status);
                    break;

                    case 'nsdlpanstatement':
                        if($request->status == "hardcopypending"){
                            $data->where('hardcopy', '0')->where('status', "success");
                        }elseif ($request->status == "complete") {
                            $data->where('hardcopy', '1')->where('status', "success");
                        }else{
                            $data->where('status', $request->status);
                        }
                    break;

                    default:
                        $data->where('status', $request->status);
                    break;
                }
            }

            if(isset($request->mode) && !empty($request->mode)){
                $data->where('provider', $request->mode);
            }

            if(!empty($request->searchtext)){
                $data->where( function($q) use($request){
                    foreach ($request->searchdata as $value) {
                        $q->orWhere($value, 'like',$request->searchtext.'%');
                        $q->orWhere($value,'like','%'.$request->searchtext.'%');
                        $q->orWhere($value, 'like','%'.$request->searchtext);
                    }
                });
            } 
        }

        if($returntype == "count"){
            return $data->count();
        }else{
            if($request['length'] != -1){
                $data->skip($request['start'])->take($request['length']);
            }

            if($request->select == "all"){
                
                return $data->get();
            }else{
                return $data->select($request->select)->get();
            }
        }
    }
    
    public function export(Request $post, $type)
    {
        // dd($type); 
        // dd($post->all()); exit;
        ini_set('max_execution_time', 0);
        $parentData = session('parentData');
        
        if($type == 'apilogs'){
            
             $query = \App\Model\Apilog::whereIn('txnid', ['CW3way','MATM3way','AP3way']);
                                
                                 if((isset($post->fromdate) && !empty($post->fromdate)) 
            && (isset($post->todate) && !empty($post->todate))){
                
            if($post->fromdate == $post->todate){
                $query->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
            }else{
                $query->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
            }
        }elseif (isset($post->fromdate) && !empty($post->fromdate)) {
            if(!in_array($type, ['whitelable', 'md', 'distributor', 'retailer'])){
                $query->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
            }
        }else{
            if(!in_array($type, ['whitelable', 'md', 'distributor', 'retailer'])){
                $query->whereDate('created_at','=', date('Y-m-d'));
            }
        }
    
    $apilogs = $query->get();
    // Define column names
    $column_names = array(
        'merchantTransactionId',
        'fingpayTransactionId',
        'transactionRrn',
        'responseCode',
        'referenceId',
        'transactionDate',
        'serviceType',
        'merchantId'
    );

    // Download file
    $name = $type.'report'.date('d_M_Y');
       header("Content-Disposition: attachment; filename=\"$name.csv\"");
    header("Content-Type: text/csv");

    // Output the column names as the first row
    echo implode(",", $column_names) . "\r\n";

    // Output each row of data
    foreach ($apilogs as $apilog) {
        $data = json_decode($apilog->response);
        $data = json_decode($data);
        
        
        $data = $data->data;
      

        foreach ($data as $row) {
            // Prepare data for CSV output
            $csv_row = array();
            foreach ($column_names as $column) {
                $csv_row[] = isset($row->$column) ? '"' . str_replace('"', '""', $row->$column) . '"' : '';
            }
            echo implode(",", $csv_row) . "\r\n";
        }
    }
    // Terminate the script after CSV generation
    exit();
        }

        switch ($type) {
            case 'recharge':
            case 'billpay':
                $table = "reports.";
                $query = \DB::table('reports')->where($table.'rtype', 'main')->where($table.'product', $type);

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'reports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'reports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'reports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'reports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'reports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                break;

            case 'pancard':
                $table = "reports.";
                $query = \DB::table('reports')->where($table.'rtype', 'main')->where($table.'product', "utipancard");

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'reports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'reports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'reports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'reports.api_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                break;
                
            case 'wtbillpaystatement':
                $table = "reports.";
                $query = \DB::table('reports')->where($table.'rtype', 'main')->where($table.'product', "wt");

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'reports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'reports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'reports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'reports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'reports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                break;

            case 'money':
                $table = "reports.";
                $query = \DB::table('reports')->where($table.'rtype', 'main')->where($table.'product', "dmt");

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'reports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'reports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'reports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'reports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'reports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                break;

            case 'aeps':
                $table = "aepsreports.";
               //$query = \DB::table('aepsreports')->where($table.'aepstype', 'CW')->where($table.'rtype', 'main');
                $query = \DB::table('aepsreports')->where($table.'rtype', 'main');
                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'aepsreports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                $query->where($table.'aepstype', 'CW');
                $query->where($table.'product', 'aeps');
                
                break;

            case 'cashdepositbillpay':
                $table = "aepsreports.";
               //$query = \DB::table('aepsreports')->where($table.'aepstype', 'CW')->where($table.'rtype', 'main');
                $query = \DB::table('aepsreports')->where($table.'rtype', 'main');
                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'aepsreports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                $query->where($table.'aepstype', 'CD');
                $query->where($table.'rtype', 'main');
                break;

            case 'mini':
                $table = "aepsreports.";
               //$query = \DB::table('aepsreports')->where($table.'aepstype', 'CW')->where($table.'rtype', 'main');
                $query = \DB::table('aepsreports')->where($table.'rtype', 'main');
                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'aepsreports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                $query->where($table.'aepstype', 'MS');
                $query->where($table.'product', 'aeps');
                $query->where($table.'transtype', 'transaction');
                break;

            case 'matm':
                $table = "aepsreports.";
               // $query = \DB::table('aepsreports')->where($table.'aepstype', 'CW')->where($table.'rtype', 'main');
                $query = \DB::table('aepsreports')->where($table.'rtype', 'main')->where($table.'product', 'matm');
                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'aepsreports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                $query->where($table.'aepstype', 'CW');
                $query->where($table.'product', 'matm');
                break;

            case 'aadharpay':
                $table = "aepsreports.";
               // $query = \DB::table('aepsreports')->where($table.'aepstype', 'CW')->where($table.'rtype', 'main');
                $query = \DB::table('aepsreports')->where($table.'rtype', 'main');

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                $query->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid');
                $query->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid');
                $query->leftJoin('users as whitelable', 'whitelable.id', '=', 'aepsreports.wid');
                $query->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id');
                $query->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
                break;

            case 'whitelable':
            case 'md':
            case 'distributor':
            case 'retailer':
                $table = "users.";
                $query = \DB::table('users');
                $query->leftJoin('companies', 'companies.id', '=', 'users.company_id');
                $query->leftJoin('roles', 'roles.id', '=', 'users.role_id');
                $query->leftJoin('users as parents', 'parents.id', '=', 'users.parent_id');
                $query->where('roles.slug', '=', $type);
            break;

            case 'fundrequest':
                $table = "fundreports.";
                $query = \DB::table('fundreports');

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'fundreports.user_id');
                $query->leftJoin('users as sender', 'sender.id', '=', 'fundreports.credited_by');
                $query->leftJoin('fundbanks', 'fundbanks.id', '=', 'fundreports.fundbank_id');

                $query->where($table.'credited_by', \Auth::id());
                break;

            case 'fund':
                $table = "reports.";
                $apis_id = array('2');
                $query = \DB::table('reports')->whereIn($table.'api_id', $apis_id);

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');
                $query->leftJoin('users as sender', 'sender.id', '=', 'reports.credit_by');

                $query->where($table.'user_id', \Auth::id());
                break;

            case 'aepsfundrequestview':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->redirect()->back();
                }
                $table = "aepsfundrequests.";
                $query = \DB::table('aepsfundrequests')->where($table.'status', 'pending');

                $query->leftJoin('users', 'users.id', '=', 'aepsfundrequests.user_id');
                break;

            case 'aepsfundrequest':
            case 'aepsfundrequestviewall':
                $table = "aepsfundrequests.";
                $query = \DB::table('aepsfundrequests');

                $query->leftJoin('users', 'users.id', '=', 'aepsfundrequests.user_id');
                if($type == "aepsfundrequest"){
                    $query->where($table.'user_id', \Auth::id());
                }else{
                    if(\Myhelper::hasNotRole('admin')){
                        return response()->redirect()->back();
                    }
                }
                break;

            case 'aepsagentstatement':
                $query = \DB::table('mahaagents');
                $table = "mahaagents.";

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
            break;

            case 'utiid':
                $query = \DB::table('utiids');
                $table = "utiids.";
                $query->leftJoin('users', 'users.id', '=', 'utiids.user_id');
                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->whereIn($table.'user_id', $parentData);
                }
            break;

            case 'wallet':
                $table = "reports.";
                $query = \DB::table('reports');

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'reports.user_id');

                if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                    $query->where($table.'user_id', $post->agent);
                }else{
                    $query->where($table.'user_id', \Auth::id());
                }
                break;

            case 'awallet':
                $table = "aepsreports.";
                $query = \DB::table('aepsreports');

                $query->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id');
                
                if(\Myhelper::hasNotRole('admin')){
                    if(isset($post->agent) && $post->agent != '' && $post->agent != 0){
                        $query->where($table.'user_id', $post->agent);
                    }else{
                        $query->where($table.'user_id', \Auth::id());
                    }
                }
                
                

                break;
            
            default:
                # code...
                break;
        }

        if((isset($post->fromdate) && !empty($post->fromdate)) 
            && (isset($post->todate) && !empty($post->todate))){
            //dd([Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d H:m:i'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d H:m:i')]); exit;
            if($post->fromdate == $post->todate){
                $query->whereDate($table.'created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
            }else{
                $query->whereBetween($table.'created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
            }
        }elseif (isset($post->fromdate) && !empty($post->fromdate)) {
            if(!in_array($type, ['whitelable', 'md', 'distributor', 'retailer'])){
                $query->whereDate($table.'created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
            }
        }else{
            if(!in_array($type, ['whitelable', 'md', 'distributor', 'retailer'])){
                $query->whereDate($table.'created_at','=', date('Y-m-d'));
            }
        }

        if(isset($post->status) && $post->status != '' && $post->status != 'undefined'){
            switch ($post->type) {
                default:
                    $query->where($table.'status', $post->status);
                break;
            }
        }
        switch ($type) {
            case 'recharge':
            case 'billpay':
                
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.number', 'reports.amount', 'reports.charge', 'reports.profit', 'reports.refno', 'reports.txnid', 'reports.status', 'reports.wid', 'reports.wprofit', 'reports.mdid', 'reports.mdprofit', 'reports.disid', 'reports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'reports.user_id']);
                break;

            case 'pancard':
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.number', 'reports.amount', 'reports.charge', 'reports.profit', 'reports.refno', 'reports.txnid', 'reports.option1', 'reports.status', 'reports.wid', 'reports.wprofit', 'reports.mdid', 'reports.mdprofit', 'reports.disid', 'reports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'reports.user_id']);
                break;
                
            case 'wtbillpaystatement':
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.amount', 'reports.refno', 'reports.product', 'reports.status', 'retailer.name as username', 'retailer.mobile as usermobile', 'reports.user_id', 'reports.credit_by', 'reports.remark']);
                break;
                

            case 'money':
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.number', 'reports.amount', 'reports.charge', 'reports.profit', 'reports.refno', 'reports.txnid', 'reports.option1', 'reports.option2', 'reports.option3', 'reports.option4', 'reports.mobile', 'reports.option1', 'reports.status', 'reports.wid', 'reports.wprofit', 'reports.mdid', 'reports.mdprofit', 'reports.disid', 'reports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'reports.user_id']);
                break;

            case 'aeps':
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.mobile', 'aepsreports.aadhar', 'aepsreports.amount', 'aepsreports.refno', 'aepsreports.charge', 'aepsreports.status', 'aepsreports.wid', 'aepsreports.wprofit', 'aepsreports.mdid', 'aepsreports.mdprofit', 'aepsreports.disid', 'aepsreports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'aepsreports.user_id']);
                // DB::enableQueryLog();
                // $dataa = $query->get();
                // dd(DB::getQueryLog()); exit;
                break;

            case 'mini':
                //DB::enableQueryLog();

                // and then you can get query log

                //$dataa = $query->get();
                //dd(DB::getQueryLog()); exit;
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.mobile', 'aepsreports.aadhar', 'aepsreports.amount', 'aepsreports.refno', 'aepsreports.charge', 'aepsreports.status', 'aepsreports.wid', 'aepsreports.wprofit', 'aepsreports.mdid', 'aepsreports.mdprofit', 'aepsreports.disid', 'aepsreports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'aepsreports.user_id']);
                break;
            case 'cashdepositbillpay': 
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.mobile', 'aepsreports.aadhar', 'aepsreports.amount', 'aepsreports.refno', 'aepsreports.charge', 'aepsreports.status', 'aepsreports.wid', 'aepsreports.wprofit', 'aepsreports.mdid', 'aepsreports.mdprofit', 'aepsreports.disid', 'aepsreports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'aepsreports.user_id']);
                break;

            case 'matm':
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.mobile', 'aepsreports.aadhar', 'aepsreports.amount', 'aepsreports.refno', 'aepsreports.charge', 'aepsreports.status', 'aepsreports.wid', 'aepsreports.wprofit', 'aepsreports.mdid', 'aepsreports.mdprofit', 'aepsreports.disid', 'aepsreports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'aepsreports.user_id']);
                break;

            case 'aadharpay':
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.mobile', 'aepsreports.aadhar', 'aepsreports.amount', 'aepsreports.refno', 'aepsreports.charge', 'aepsreports.status', 'aepsreports.wid', 'aepsreports.wprofit', 'aepsreports.mdid', 'aepsreports.mdprofit', 'aepsreports.disid', 'aepsreports.disprofit', 'retailer.name as username', 'retailer.mobile as usermobile', 'whitelable.name as wname', 'whitelable.mobile as wmobile', 'md.name as mdname', 'md.mobile as mdmobile', 'distributor.name as disname', 'distributor.mobile as dismobile', 'apis.name as apiname', 'providers.name as providername', 'aepsreports.user_id']);
                break;

            case 'whitelable':
            case 'md':
            case 'distributor':
            case 'retailer':
                $datas = $query->get(['users.id','users.created_at','users.name','users.email','users.mobile','users.role_id','users.company_id','users.mainwallet','users.aepsbalance','users.status','users.address','users.state','users.city','users.pincode','users.shopname','users.gstin','users.pancard','users.aadharcard','users.bank','users.ifsc','users.account','companies.companyname as companyname','roles.name as rolename','parents.name as parentname','parents.mobile as parentmobile']);
            break;

            case 'fundrequest':
                $datas = $query->get(['fundreports.id', 'fundreports.created_at', 'fundreports.paymode', 'fundreports.amount', 'fundreports.amount', 'fundreports.ref_no', 'fundreports.paydate', 'fundreports.status', 'retailer.name as username', 'retailer.mobile as usermobile', 'sender.name as sendername', 'sender.mobile as sendermobile', 'fundbanks.name as fundbank']);
                break;

            case 'fund':
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.amount', 'reports.refno', 'reports.product', 'reports.status', 'retailer.name as username', 'retailer.mobile as usermobile', 'sender.name as sendername', 'sender.mobile as sendermobile', 'reports.user_id', 'reports.credit_by', 'reports.remark']);
                break;

            case 'aepsfundrequestview':
            case 'aepsfundrequest':
            case 'aepsfundrequestviewall':
                $datas = $query->get(['aepsfundrequests.id', 'aepsfundrequests.created_at', 'aepsfundrequests.account', 'aepsfundrequests.bank', 'aepsfundrequests.ifsc', 'aepsfundrequests.amount', 'aepsfundrequests.type', 'aepsfundrequests.status', 'aepsfundrequests.remark', 'users.name as username', 'users.mobile as usermobile']);
                break;

            case 'aepsagentstatement':
                $datas = $query->get(['mahaagents.id', 'mahaagents.created_at', 'mahaagents.bc_id', 'mahaagents.bc_f_name', 'mahaagents.bc_l_name', 'mahaagents.bc_l_name', 'mahaagents.emailid', 'mahaagents.phone1', 'mahaagents.phone2']);
                break;

            case 'utiid':
                $datas = $query->get(['utiids.id', 'utiids.created_at', 'utiids.vleid', 'utiids.status', 'utiids.name', 'utiids.location', 'utiids.contact_person', 'utiids.pincode', 'utiids.state', 'utiids.state', 'utiids.email', 'utiids.mobile', 'utiids.remark', 'utiids.user_id', 'users.name as username', 'users.mobile as usermobile']);
                break;

            case 'wallet':
                $datas = $query->get(['reports.id', 'reports.created_at', 'reports.number', 'reports.amount', 'reports.charge', 'reports.profit', 'reports.status', 'retailer.name as username', 'retailer.mobile as usermobile', 'reports.user_id', 'reports.product', 'reports.rtype', 'reports.trans_type', 'reports.balance']);
                break;

            case 'awallet':
                $datas = $query->get(['aepsreports.id', 'aepsreports.created_at', 'aepsreports.payid', 'aepsreports.remark', 'aepsreports.aadhar', 'aepsreports.mobile', 'aepsreports.refno', 'retailer.name as username', 'retailer.mobile as usermobile', 'aepsreports.user_id', 'aepsreports.transtype', 'aepsreports.rtype', 'aepsreports.status', 'aepsreports.balance', 'aepsreports.amount', 'aepsreports.charge', 'aepsreports.type']);
                break;
            
            default:
                # code...
                break;
        }
        //dd($datas); exit;
        //dd($query->toSql()); exit;
        // dd($type); exit;
        $excelData = array();
        switch ($type) {
            case 'recharge':
            case 'billpay':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Number',' Amount', 'Charge', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }
                //count($datas); exit;
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['number'] = $record->number;
                    $data['amount'] = $record->amount;
                    $data['charge'] = $record->charge;
                    $data['profit'] = $record->profit;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['userdetails'] = $record->username." (".$record->usermobile.")";

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        $data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        $data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        $data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                }
                break;

            case 'pancard':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Vle Id', 'No of Token', ' Amount', 'Charge', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }

                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['number'] = $record->number;
                    $data['option1'] = $record->option1;
                    $data['amount'] = $record->amount;
                    $data['charge'] = $record->charge;
                    $data['profit'] = $record->profit;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['userdetails'] = $record->username." (".$record->usermobile.")";

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        $data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        $data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        $data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                }
                break;

            case 'money':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Remitter Name', 'Remitter Mobile', 'Beneficiary Name', 'Beneficiary Account', 'Beneficiary Bank', 'Beneficiary Ifsc',' Amount', 'Charge', 'Profit', 'Order Id', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }

                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->option1;
                    $data['rmobile'] = $record->mobile;
                    $data['name'] = $record->option2;
                    $data['number'] = $record->number;
                    $data['bank'] = $record->option3;
                    $data['ifsc'] = $record->option4;
                    $data['amount'] = $record->amount;
                    $data['charge'] = $record->charge;
                    $data['profit'] = $record->profit;
                    $data['txnid'] = $record->txnid;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['userdetails'] = $record->username." (".$record->usermobile.")";

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        $data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        $data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        $data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                }
                break;

            case 'aeps':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Aadhar No', 'Bc Mobile',' Amount', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }

                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->aadhar;
                    $data['rmobile'] = $record->mobile;
                    $data['amount'] = $record->amount;
                    $data['profit'] = $record->charge;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    
                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        //$data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disdetails'] = $record->disname;
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        //$data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mddetails'] = $record->mdname;
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        //$data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wdetails'] = $record->wname;
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                } 
                break;

            case 'mini':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Bc Id', 'Bc Mobile',' Amount', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }
                //dd($datas); exit;
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->aadhar;
                    $data['rmobile'] = $record->mobile;
                    $data['amount'] = $record->amount;
                    $data['profit'] = $record->charge;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['userdetails'] = $record->username." (".$record->usermobile.")";

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        $data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        $data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        $data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                } 
                //dd($excelData); exit;
                break;

            case 'cashdepositbillpay':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Bc Id', 'Bc Mobile',' Amount', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }
                //dd($datas); exit;
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->aadhar;
                    $data['rmobile'] = $record->mobile;
                    $data['amount'] = $record->amount;
                    $data['profit'] = $record->charge;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        //$data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disdetails'] = $record->disname;
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        //$data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mddetails'] = $record->mdname;
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        //$data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wdetails'] = $record->wname;
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                } 
                //dd($excelData); exit;
                break;

            case 'matm':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Bc Id', 'Bc Mobile',' Amount', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }

                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->aadhar;
                    $data['rmobile'] = $record->mobile;
                    $data['amount'] = $record->amount;
                    $data['profit'] = $record->charge;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        //$data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disdetails'] = $record->disname;
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        //$data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mddetails'] = $record->mdname;
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        //$data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wdetails'] = $record->wname;
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                }
                break;

            case 'aadharpay':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Transaction Id', 'Date','Api Name', 'Provider', 'Bc Id', 'Bc Mobile',' Amount', 'Profit', 'Ref No', 'Status', "Member Details"];

                if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Distributor Details", "Distributor Profit"]);
                }

                if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Md Details", "Md Profit"]);
                }

                if(\Myhelper::hasRole(['whitelable', 'admin'])){
                    $titles = array_merge($titles, ["Whitelable Details", "Whitelable Profit"]);
                }

                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['apitype'] = $record->apiname;
                    $data['provider'] = $record->providername;
                    $data['rname'] = $record->aadhar;
                    $data['rmobile'] = $record->mobile;
                    $data['amount'] = $record->amount;
                    $data['profit'] = $record->charge;
                    $data['refno'] = $record->refno;
                    $data['status'] = $record->status;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;

                    if(\Myhelper::hasRole(['distributor', 'md', 'whitelable', 'admin'])){
                        //$data['disdetails'] = $record->disname." (".$record->dismobile.")";
                        $data['disdetails'] = $record->disname;
                        $data['disprofit'] = $record->disprofit;
                    }

                    if(\Myhelper::hasRole(['md', 'whitelable', 'admin'])){
                        //$data['mddetails'] = $record->mdname." (".$record->mdmobile.")";
                        $data['mddetails'] = $record->mdname;
                        $data['mdprofit'] = $record->mdprofit;
                    }

                    if(\Myhelper::hasRole(['whitelable', 'admin'])){
                        //$data['wdetails'] = $record->wname." (".$record->wmobile.")";
                        $data['wdetails'] = $record->wname;
                        $data['wprofit'] = $record->wprofit;
                    }
                    array_push($excelData, $data);
                }
                break;

            case 'whitelable':
            case 'md':
            case 'distributor':
            case 'retailer':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Id', 'Date' ,'Name', 'Email', 'Mobile', 'Role', 'Main Balance', 'Aeps Balance', 'Parent', 'Company', 'Status' ,'address', 'City', 'State','Pincode','Shopname', 'Gst Tin','Pancard','Aadhar Card', 'Account', 'Bank','Ifsc'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['name'] = $record->name;
                    $data['email'] = $record->email;
                    $data['mobile'] = $record->mobile;
                    $data['role'] = $record->rolename;
                    $data['mainwallet'] = $record->mainwallet;
                    $data['aepsbalance'] = $record->aepsbalance;
                    //$data['parents'] = $record->parentname ." (".$record->parentmobile.")";
                    $data['parents'] = $record->parentname;
                    $data['company'] = $record->companyname;
                    $data['status'] = $record->status;
                    $data['address'] = $record->address;
                    $data['city'] = $record->city;
                    $data['state'] = $record->state;
                    $data['pincode'] = $record->pincode;
                    $data['shopname'] = $record->shopname;
                    $data['gstin'] = $record->gstin;
                    $data['pancard'] = $record->pancard;
                    $data['aadharcard'] = $record->aadharcard;
                    $data['account'] = $record->account;
                    $data['bank'] = $record->bank;
                    $data['ifsc'] = $record->ifsc;
                    array_push($excelData, $data);
                }
            break;

            case 'fundrequest':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Id', 'Date' ,'Paymode', 'Amount', 'Ref No', 'Payment Bank', 'Pay Date', 'Status', 'Requested Via', 'Approved By'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['paymode'] = $record->paymode;
                    $data['amount']   = $record->amount;
                    $data['ref_no']  = $record->ref_no;
                    $data['fundbank'] = $record->fundbank;
                    $data['paydate']  = $record->paydate;
                    $data['status'] = $record->status;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    //$data['senderdetails'] = $record->sendername." (".$record->sendermobile.")";
                    $data['senderdetails'] = $record->sendername;
                    array_push($excelData, $data);
                }
            break;

            case 'fund':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Order Id', 'Date', 'Payment Type', 'Amount', 'Ref No', 'Status', 'Remarks', 'Requested Via', 'Approved By'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['type'] = $record->product;
                    $data['amount'] = $record->amount;
                    $data['bankref'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['remark'] = $record->remark;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    //$data['senderdetails'] = $record->sendername." (".$record->sendermobile.")";
                    $data['senderdetails'] = $record->sendername;
                    array_push($excelData, $data);
                }
                break;
                
            case 'wtbillpaystatement':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Order Id', 'Date', 'Payment Type', 'Amount', 'Ref No', 'Status', 'Remarks', 'User Details'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['type'] = $record->product;
                    $data['amount'] = $record->amount;
                    $data['bankref'] = $record->refno;
                    $data['status'] = $record->status;
                    $data['remark'] = $record->remark;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    //$data['senderdetails'] = $record->sendername." (".$record->sendermobile.")";
                    array_push($excelData, $data);
                }
                break;

            case 'aepsfundrequestview':
            case 'aepsfundrequest':
            case 'aepsfundrequestviewall':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Order Id', 'Date', 'Payment Mode' ,'Account', 'Bank', 'Ifsc', 'Amount', 'Status', 'Remarks', 'Requested Via'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['type'] = $record->type;
                    $data['account'] = $record->account;
                    $data['bank'] = $record->bank;
                    $data['ifsc'] = $record->ifsc;
                    $data['amount'] = $record->amount;
                    $data['status'] = $record->status;
                    $data['remark'] = $record->remark;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    array_push($excelData, $data);
                }
                break;

            case 'aepsagentstatement':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Id', 'Date','BCID' ,'Name', 'Email', 'Phone1', 'Phone2'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['bc_id'] = $record->bc_id;
                    $data['name'] = $record->bc_f_name. " ". $record->bc_l_name." ".$record->bc_l_name;
                    $data['email'] = $record->emailid;
                    $data['phone1'] = $record->phone1;
                    $data['phone2'] = $record->phone2;
                    array_push($excelData, $data);
                }
            break;

            case 'utiid':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Id', 'Date','Vle id','Name', 'Email', 'Mobile', 'Location', 'Contact Person', 'Pincode', 'State', 'Status', 'Remark', 'User Details'];
                foreach ($datas as $record) {
                    $data['id'] = $record->id;
                    $data['created_at'] = $record->created_at;
                    $data['vleid'] = $record->vleid;
                    $data['name'] = $record->name;
                    $data['email'] = $record->email;
                    $data['mobile'] = $record->mobile;
                    $data['location'] = $record->location;
                    $data['contact_person'] = $record->contact_person;
                    $data['pincode'] = $record->pincode;
                    $data['state'] = $record->state;
                    $data['status'] = $record->status;
                    $data['remark'] = $record->remark;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    array_push($excelData, $data);
                }
            break;

            case 'wallet':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Date', 'Transaction Id', 'User Details','Product', 'Number', 'ST Type', 'Status', 'Opening Balance', 'Credit', 'Debit'];
                foreach ($datas as $record) {
                    $data['created_at'] = $record->created_at;
                    $data['id'] = $record->id;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    $data['product'] = $record->product;
                    $data['number'] = $record->number;
                    $data['rtype'] = $record->rtype;
                    $data['status'] = $record->status;
                    $data['balance'] = " ".round($record->balance, 2);
                    if($record->trans_type == "credit"){
                        $data['credit'] = $record->amount + $record->charge - $record->profit;
                        $data['debit']  = '';
                    }elseif($record->trans_type == "debit"){
                        $data['credit'] = '';
                        $data['debit']  = $record->amount + $record->charge - $record->profit;
                    }else{
                        $data['credit'] = '';
                        $data['debit']  = '';
                    }
                    array_push($excelData, $data);
                }
            break;

            case 'awallet':
                $name = $type.'report'.date('d_M_Y');
                $titles = ['Date', 'User Details', 'Transaction Details', 'Transaction Type', 'Status', 'Opening Balance', 'Credit', 'Debit'];
                foreach ($datas as $record) {
                    $data['created_at'] = $record->created_at;
                    //$data['userdetails'] = $record->username." (".$record->usermobile.")";
                    $data['userdetails'] = $record->username;
                    if($record->transtype == "fund" ){
                        $data['product'] = $record->payid."/".$record->remark;
                    }else{
                        $data['product'] = $record->aadhar."/".$record->mobile."/".$record->refno;
                    }
                    $data['number'] = $record->transtype;
                    $data['status'] = $record->status;
                    $data['balance'] = " ".round($record->balance, 2);
                    if($record->type == "credit"){
                        $data['credit'] = $record->amount + $record->charge;
                        $data['debit']  = '';
                    }elseif($record->type == "debit"){
                        $data['credit'] = '';
                        $data['debit']  = $record->amount - $record->charge;
                    }else{
                        $data['credit'] = '';
                        $data['debit']  = '';
                    }
                    array_push($excelData, $data);
                }
            break;
        }
        

        try {
            //dd($excelData); exit;
            
    // Download file
   header("Content-Disposition: attachment; filename=\"$name.csv\"");
header("Content-Type: text/csv");

// Output the titles as the first row
echo implode(",", array_values($titles)) . "\r\n";

// Output each row of data
foreach($excelData as $excelDat) {
    echo implode(",", array_values($excelDat)) . "\r\n";
}
       
      exit;
            ini_set('max_execution_time', '0');
            return \Excel::create($name, function ($excel) use ($titles, $excelData) {
                $excel->sheet('Sheet1', function ($sheet) use ($titles, $excelData) {
                    $sheet->fromArray($excelData, null, 'A1', false, false)->prependRow($titles);
                });
            })->download('xls');

        } catch (\Exception $e) {

            return $e->getMessage();
        }
        
    }
    
    public function aeps_invoice(Request $request)
    {
        $data = [];
    
        if ($request->has('from_date')) {
            $fromDate = Carbon::createFromFormat('Y-m-d', $request->from_date)->format('Y-m-d');
            $toDate = Carbon::createFromFormat('Y-m-d', $request->to_date)->addDay(1)->format('Y-m-d');
            $table = "aepsreports.";
    
            $mainQuery = \DB::table('aepsreports')
                ->whereBetween($table.'created_at', [$fromDate, $toDate])
                ->where($table.'rtype', 'main')
                ->where($table.'user_id', \Auth::id())
                ->where($table.'aepstype', 'CW')
                ->where($table.'product', 'aeps')
                ->where($table.'status', 'success');
    
            $mainQuery->leftJoin('users as retailer', 'retailer.id', '=', 'aepsreports.user_id')
                ->leftJoin('users as distributor', 'distributor.id', '=', 'aepsreports.disid')
                ->leftJoin('users as md', 'md.id', '=', 'aepsreports.mdid')
                ->leftJoin('users as whitelabel', 'whitelabel.id', '=', 'aepsreports.wid')
                ->leftJoin('apis', 'apis.id', '=', 'aepsreports.api_id')
                ->leftJoin('providers', 'providers.id', '=', 'aepsreports.provider_id');
    
            $data['amount'] = $mainQuery->sum($table.'amount');
            $data['gst'] = $mainQuery->sum($table.'gst');
            $data['tds'] = $mainQuery->sum($table.'tds');
            $data['commission'] = $mainQuery->sum($table.'charge');
    
            $data['datashow'] = 'view';
            $data['from_date'] = $request->from_date;
            $data['to_date'] = $request->to_date;
        } else {
            $data['datashow'] = 'no';
        }
    
        return view('statement.aeps_invoice')->with(['data' => $data]);
    }
    

    
    
    public function wallet_invoice(Request $post)
    {
        
        if($post->has('from_date'))
        {
          $data['amount'] = Report::whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->from_date)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->to_date)->addDay(1)->format('Y-m-d')])->where('rtype','main')->where('status','success')->where('user_id',\Auth::id())->sum('amount');
          $data['gst'] = Report::whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->from_date)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->to_date)->addDay(1)->format('Y-m-d')])->where('rtype','main')->where('status','success')->where('user_id',\Auth::id())->sum('charge');
          $data['tds'] = Report::whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->from_date)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->to_date)->addDay(1)->format('Y-m-d')])->where('rtype','main')->where('status','success')->where('user_id',\Auth::id())->sum('tds');
          $data['commission'] = Report::whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->from_date)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->to_date)->addDay(1)->format('Y-m-d')])->where('status','success')->where('user_id',\Auth::id())->where('rtype','main')->sum('profit');
          $data['datashow'] = 'view';
          $data['from_date'] = $post->from_date;
          $data['to_date'] = $post->to_date;
        }
        else
        {
            $data['datashow'] = 'no';
        }
        
        return view('statement.wallet_invoice')->with(['data' => $data]);
    }
}
