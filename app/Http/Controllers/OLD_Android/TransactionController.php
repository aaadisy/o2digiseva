<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function transaction(Request $request)
    {
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
