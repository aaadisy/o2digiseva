<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\User;

class TransactionController extends Controller
{
    public function transaction(Request $request)
    {
    	exit;
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
            'type' 	   => 'required'
        );

        $validate = \Myhelper::FormValidator($rules, $request);
        if($validate != "no"){
        	return $validate;
        }

        if(!$request->has('start')){
        	$request['start'] = 0;
        }

    	switch ($request->type) {
    		case 'aepsstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'offlinecashdeposit':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['aadhar as account_no', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'wallettransfer':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['aadhar as account_no', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
				

			case 'aadharpaystatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'aepsfundrequest':
				$request['table']= '\App\Model\Aepsfundrequest';
				$request['searchdata'] = ['amount','type', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'awalletstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['mobile','aadhar', 'txnid', 'refno', 'payid', 'amount'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'rechargestatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'billpaystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'utipancardstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'dmtstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'fundrequest':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
    	}

    	$request['where']=0;
        
		try {
			$totalData = $this->getData($request, 'totalcount');
		} catch (\Exception $e) {
			$totalData = 0;
		}
		$totalpage = floor($totalData/20);

		if ((isset($request->searchtext) && !empty($request->searchtext)) ||
           	(isset($request->todate) && !empty($request->todate))       ||
           	(isset($request->product) && !empty($request->product))       ||
           	(isset($request->status) && $request->status != '')		  ||
           	(isset($request->agent) && !empty($request->agent))
         ) 
	    {
	        $request['where'] = 1;
	    }
		
		try {
			$data = $this->getData($request, 'data');
		} catch (\Exception $e) {
			$data = [];
		}
		
		return response()->json(['statuscode' => "TXN", 'pages' => $totalpage,"data" => $data]);
    }

     public function transactionapi(Request $request)
    {
    	$rules = array(
            'apptoken' => 'required',
            'user_id'  => 'required|numeric',
        );
    	 $request['type'] = Request()->segment(4);
		 $id=0;
        $validate = \Myhelper::FormValidator($rules, $request);
        if($validate != "no"){
        	return $validate;
        }

        if(!$request->has('start')){
        	$request['start'] = 0;
        }

    	switch ($request->type) {
    		case 'aepsstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
			case 'offlinecashdeposit':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				
				// if($id == 0){

				// 	//dd(\Auth::user()); exit;

				// 	if (\Myhelper::hasRole(['retailer', 'apiuser'])){
				// 		$request['parentData'] = [$request->user_id];
				// 	}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
				// 		$request['parentData'] = $parentData;
				// 	}else{
				// 		$request['parentData'] = 'all';
				// 	}
				// }else{
				// 	if(in_array($id, $parentData)){
				// 		$request['parentData'] = \Myhelper::getParents($id);
				// 	}else{
				// 		$request['parentData'] = [$request->user_id];
				// 	}
				// }
				$request['whereIn'] = 'user_id';
				
				
				// if ($id == 0 || $returntype == "all") {
				// 	if($id == 0){
				// 		if (\Myhelper::hasRole(['retailer', 'apiuser'])){
				// 			$request['parentData'] = [$request->user_id];
				// 		}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
				// 			$request['parentData'] = $parentData;
				// 		}else{
				// 			$request['parentData'] = 'all';
				// 		}
				// 	}else{
				// 		if(in_array($id, $parentData)){
				// 			$request['parentData'] = \Myhelper::getParents($id);
				// 		}else{
				// 			$request['parentData'] = [$request->user_id];
				// 		}
				// 	}
				// 	$request['whereIn'] = 'user_id';
				// }else{
				// 	$request['parentData'] = [$id];
				// 	$request['whereIn'] = 'id';
				// 	$request['return'] = 'single';
				// }

				//dd($request); exit;


				// $request['table']= '\App\Model\Report';
				// $request['searchdata'] = ['number', 'mobile', 'txnid', 'payid', 'amount', 'status'];
				// $request['select'] = 'all';
				// $request['order'] = ['id','DESC'];
				// $request['parentData'] = [$request->user_id];
				// $request['whereIn'] = 'user_id';
				break;
			case 'wallettransfer':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				
				break;
			case 'aadharpaystatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
			case 'matmstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'fundstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['amount','number', 'mobile','credit_by', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'accountstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['txnid','statement_type', 'user_id', 'credited_by'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				
				break;

			case 'complaints':
				$request['table']= '\App\Model\Complaint';
				$request['searchdata'] = ['type', 'solution', 'description', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'disputes':
				$request['table']= '\App\Model\Dispute';
				$request['searchdata'] = ['comment', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;


			case 'aepsfundrequest':
				$request['table']= '\App\Model\Aepsfundrequest';
				$request['searchdata'] = ['amount','type', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'awalletstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['mobile','aadhar', 'txnid', 'refno', 'payid', 'amount'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'rechargestatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'billpaystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'utipancardstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'dmtstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;

			case 'fundrequest':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
				
			case 'wtbillpaystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'user_id';
				break;
    	}

    	$request['where']=0;
        
		try {
			$totalData = $this->getData($request, 'totalcount');
		} catch (\Exception $e) {
			$totalData = 0;
		}
		$totalpage = floor($totalData/20);

		if ((isset($request->searchtext) && !empty($request->searchtext)) ||
           	(isset($request->todate) && !empty($request->todate))       ||
           	(isset($request->product) && !empty($request->product))       ||
           	(isset($request->status) && $request->status != '')		  ||
           	(isset($request->agent) && !empty($request->agent))
         ) 
	    {
	        $request['where'] = 1;
	    }
		
		try {
			$data = $this->getData($request, 'data');
		} catch (\Exception $e) {
			$data = [];
		}
		
		return response()->json(['statuscode' => "TXN", 'pages' => $totalpage,"data" => $data]);
    }

     public function memberlist(Request $request)
    {
    	$rules = array(
                'apptoken' => 'required',
                'user_id'  =>'required|numeric',
            );

            $validate = \Myhelper::FormValidator($rules, $request);
            if($validate != "no"){
                return $validate;
            }

            $user = User::where('id',$request->user_id)->where('apptoken',$request->apptoken)->first();
            if($user){
                
                $request['table']= '\App\User';
				$request['searchdata'] = ['id','name', 'mobile','email'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$request->user_id];
				$request['whereIn'] = 'parent_id';


                $request['where']=0;
        
				try {
					$totalData = $this->getData($request, 'totalcount');
				} catch (\Exception $e) {
					$totalData = 0;
				}
				$totalpage = floor($totalData/20);

				if ((isset($request->searchtext) && !empty($request->searchtext)) ||
		           	(isset($request->todate) && !empty($request->todate))       ||
		           	(isset($request->product) && !empty($request->product))       ||
		           	(isset($request->status) && $request->status != '')		  ||
		           	(isset($request->agent) && !empty($request->agent))
		         ) 
			    {
			        $request['where'] = 1;
			    }
				
				try {
					$data = $this->getData($request, 'data');
				} catch (\Exception $e) {
					$data = [];
				}
				
				return response()->json(['statuscode' => "TXN", 'pages' => $totalpage,"data" => $data]);

                $output['status'] = "TXN";

                $output['message'] = "message Submitted SuccessFully";

            }else{
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);

    }


    public function getData($request, $returntype)
	{ 
		$table = $request->table;
		$data  = $table::query();
		$data->orderBy($request->order[0], $request->order[1]);

		if($request->parentData != 'all'){
			if(!is_array($request->whereIn)){
				$data->whereIn($request->whereIn, $request->parentData);
			}else{
				$data->where(function ($query) use($request){
					$query->where($request->whereIn[0] , $request->parentData)
					->orWhere($request->whereIn[1] , $request->parentData);
				});
			}
		}

        switch ($request->type) {
			case 'aepsstatement':
				$data->where('rtype', 'main')->where('aepstype', 'CW');
				break;
			case 'offlinecashdeposit':
				//$data->where('rtype', 'main')->where('product', 'cashdeposit');
				$data->where('aepstype', 'CD')->where('rtype', 'main');
				break;
			case 'wallettransfer':
				//$data->where('rtype', 'main')->where('product', 'cashdeposit');
				$data->where('aepstype', 'CD')->where('rtype', 'main');
				break;
			case 'aadharpaystatement':
				$data->where('rtype', 'main')->where('aepstype', 'M');
				break;
			case 'matmstatement':
				$data->where('rtype', 'main')->where('product', 'matm');
				break;

			case 'awalletstatement':
				$data->where('rtype', 'main')->whereNotIn('aepstype', ['BE']);
				break;

			case 'rechargestatement':
				$data->where('product', 'recharge');
				break;

			case 'billpaystatement':
				$data->where('product', 'billpay');
				break;

			case 'utipancardstatement':
				$data->where('product', 'utipancard');
				break;

			case 'dmtstatement':
				$data->where('product', 'dmt');
				break;
				
			case 'wtbillpaystatement':
				$data->where('product', 'wt')->where('rtype', 'main');
				break;
        }

		if ($request->where) {
	        if((isset($request->fromdate) && !empty($request->fromdate)) 
	        	&& (isset($request->todate) && !empty($request->todate))){
	            if($request->fromdate == $request->todate){
	                $data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
	            }else{
	                $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
	            }
	        }

	        if(isset($request->product) && !empty($request->product)){
	            switch ($request->type) {
					
				}
			}
			
	        if(isset($request->status) && $request->status != '' && $request->status != null){
	        	switch ($request->type) {	
					default:
	            		$data->where('status', $request->status);
					break;
				}
			}
			
			if(isset($request->agent) && !empty($request->agent)){
	        	switch ($request->type) {					
					default:
						$data->whereIn('user_id', $this->agentFilter($request));
					break;
				}
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
		
		if($request->has('start')){
			$data->skip(($request->start - 1) * 20)->take(20);
		}

		if($returntype == "data"){
			if($request->select == "all"){
				return $data->get();
			}else{
				return $data->select($request->select)->get();
			}
		}else{
			return $data->count();
		}
	}
}
