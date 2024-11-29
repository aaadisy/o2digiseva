<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Utiid;
use Carbon\Carbon;
use App\Model\Report;
use App\Model\Aepsreport;
use App\User;
use App\Model\Provider;
use App\Model\Api;

class CommonController extends Controller
{
	protected $api;

    public function __construct()
    {
        $this->api = Api::where('code', 'recharge1')->first();
    }

    public function fetchData(Request $request, $type, $id=0, $returntype="all")
	{
	    date_default_timezone_set('Asia/Kolkata');

		$request['return'] = 'all';
		$request['returntype'] = $returntype;
		$parentData = session('parentData');
		switch ($type) {
			case 'permissions':
				$request['table']= '\App\Model\Permission';
				$request['searchdata'] = ['name', 'slug'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'roles':
				$request['table']= '\App\Model\Role';
				$request['searchdata'] = ['name', 'slug'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'whitelable':
			case 'md':
			case 'distributor':
			case 'retailer':
			case 'apiuser':
			case 'other':
			case 'tr' :
			case 'kycpending':
			case 'kycsubmitted':
			case 'kycrejected':
				$request['table']= '\App\User';
				$request['searchdata'] = ['id','name', 'mobile','email'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if (\Myhelper::hasRole(['retailer', 'apiuser'])){
					$request['parentData'] = [\Auth::id()];
				}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
					$request['parentData'] = $parentData;
				}else{
					$request['parentData'] = 'all';
				}
				$request['whereIn'] = 'parent_id';
			break;

			case 'fundrequest':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount','ref_no', 'remark','paymode', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'fundrequestview':
			case 'fundrequestviewall':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount','ref_no', 'remark','paymode', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'credited_by';
				break;

			case 'fundstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['amount','number', 'mobile','credit_by', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'aepsfundrequest':
				$request['table']= '\App\Model\Aepsfundrequest';
				$request['searchdata'] = ['amount','type', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;

			case 'aepsfundrequestview':
			case 'aepspayoutrequestview':
			case 'aepsfundrequestviewall':
				$request['table']= '\App\Model\Aepsfundrequest';
				$request['searchdata'] = ['amount','type', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if(\Myhelper::hasNotRole(['admin'])){
					$request['parentData'] = [\Auth::id()];
				}else{
					$request['parentData'] = 'all';
				}
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupbank':
				$request['table']= '\App\Model\Fundbank';
				$request['searchdata'] = ['name','account', 'ifsc','branch'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupapi':
				$request['table']= '\App\Model\Api';
				$request['searchdata'] = ['name','account', 'ifsc','branch'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
				
			case 'setupwhatsapptemplate':
				$request['table']= '\App\Model\Whatsapptemplate';
				$request['searchdata'] = ['name','content'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
			//	$request['whereIn'] = 'user_id';
				break;
				
			case 'setupoperator':
				$request['table']= '\App\Model\Provider';
				$request['searchdata'] = ['name','recharge1', 'recharge2','type'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupcomplaintsub':
				$request['table']= '\App\Model\Complaintsubject';
				$request['searchdata'] = ['name'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'resourcescheme':
				$request['table']= '\App\Model\Scheme';
				$request['searchdata'] = ['name', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;

			case 'resourcecompany':
				$request['table']= '\App\Model\Company';
				$request['searchdata'] = ['companyname'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			case 'accountstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['txnid','statement_type', 'user_id', 'credited_by'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if($id == 0){
					$request['parentData'] = [\Auth::id()];
				}else{
					if(in_array($id, $parentData)){
						$request['parentData'] = [$id];
					}else{
						$request['parentData'] = [\Auth::id()];
					}
				}
				$request['whereIn'] = 'user_id';
				
				break;

			case 'awalletstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['mobile','aadhar', 'txnid', 'refno', 'payid', 'amount'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if($id == 0){
					$request['parentData'] = [\Auth::id()];
				}else{
					if(in_array($id, $parentData)){
						$request['parentData'] = [$id];
					}else{
						$request['parentData'] = [\Auth::id()];
					}
				}
				if(\Myhelper::hasNotRole(['admin'])){
					$request['parentData'] = [\Auth::id()];
				}else{
					$request['parentData'] = 'all';
				}
				$request['whereIn'] = 'user_id';
				break;
			
			case 'utiidstatement':
				$request['table']= '\App\Model\Utiid';
				$request['searchdata'] = ['name','vleid', 'user_id', 'location', 'contact_person', 'pincode', 'email'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'portaluti':
				$request['table']= '\App\Model\Utiid';
				$request['searchdata'] = ['name','vleid', 'user_id', 'location', 'contact_person', 'pincode', 'email'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					$request['parentData'] = [\Auth::id()];
					$request['whereIn'] = 'sender_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
			
			case 'utipancardstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['vleid', 'tokens', 'amount', 'remark'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'rechargestatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'aepsreportstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'billpaystatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
				
				case 'insurancebillpaystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
				
			case 'cashdepositbillpaystatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
				
			case 'wtbillpaystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['number', 'txnid', 'payid', 'remark', 'description', 'refno','option1', 'option2', 'mobile'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'moneystatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['mobile', 'number', 'option1', 'option2', 'option3', 'option4', 'refno', 'payid', 'amount', 'id', 'txnid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
			
			case 'aepsstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
			
			case 'ministatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
				
			case 'apilogstatement':
				$request['table']= '\App\Model\Apilog';
				$request['searchdata'] = ['txnid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
			
			case 'matmstatement':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
			
			case 'aadharpay':
				$request['table']= '\App\Model\Aepsreport';
				$request['searchdata'] = ['aadhar', 'mobile', 'txnid', 'payid', 'mytxnid', 'terminalid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'complaints':
				$request['table']= '\App\Model\Complaint';
				$request['searchdata'] = ['type', 'solution', 'description', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
            
            case 'disputes':
				$request['table']= '\App\Model\Dispute';
				$request['searchdata'] = ['comment', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'aepscomplaints':
				$request['table']= '\App\Model\Aeps_complaint';
				$request['searchdata'] = ['type', 'solution', 'description', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
            
            case 'aepsdisputes':
				$request['table']= '\App\Model\Aeps_dispute';
				$request['searchdata'] = ['comment', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;
				
			case 'apitoken':
				$request['table']= '\App\Model\Apitoken';
				$request['searchdata'] = ['ip'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if (\Myhelper::hasRole('admin')){
					$request['parentData'] = 'all';
				}else{
					$request['parentData'] = [\Auth::id()];
				}
				$request['whereIn'] = 'user_id';
				break;

			case 'aepsagentstatement':
				$request['table']= '\App\Model\Mahaagent';
				$request['searchdata'] = ['bc_f_name','bc_m_name', 'bc_id', 'phone1', 'phone2', 'emailid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
							$request['parentData'] = $parentData;
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, $parentData)){
							$request['parentData'] = \Myhelper::getParents($id);
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			default:
				# code...
				break;
        }
        
		$request['where']=0;
		$request['type']= $type;
        
		try {
			$totalData = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalData = 0;
		}

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
			$totalFiltered = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalFiltered = 0;
		}
		
		try {
			$data = $this->getData($request, 'data');
		} catch (\Exception $e) {
			$data = [];
		}
		
		if ($request->return == "all" || $returntype =="all") {
			$json_data = array(
				"draw"            => intval( $request['draw'] ),
				"recordsTotal"    => intval( $totalData ),
				"recordsFiltered" => intval( $totalFiltered ),
				"data"            => $data
			);
			echo json_encode($json_data);
		}else{
			return response()->json($data);
		}
	}

	public function getData($request, $returntype)
	{ 
		$table = $request->table;
		$data = $table::query();
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

		if( $request->type != "roles" &&
			$request->type != "permissions" &&
			$request->type != "fundrequestview" &&
			$request->type != "fundrequest" &&
			$request->type != "setupbank" &&
			$request->type != "setupapi" &&
			$request->type != "setupoperator" &&
			$request->type != "resourcescheme" &&
			$request->type != "resourcecompany" &&
			!in_array($request->type , ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'other', 'tr'])&&
			$request->where != 1
        ){
            if(!empty($request->fromdate)){
                $data->whereDate('created_at', $request->fromdate);
            }
	    }

        switch ($request->type) {
			case 'whitelable':
			case 'md':
			case 'distributor':
			case 'retailer':
			case 'apiuser':
				$data->whereHas('role', function ($q) use($request){
					$q->where('slug', $request->type);
				})->where('kyc', 'verified');
			break;

			case 'other':
				$data->whereHas('role', function ($q) use($request){
					$q->whereNotIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'admin']);
				});
			break;

			case 'tr':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser']);
				})->where('kyc', 'verified');
			break;

			case 'kycpending':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser']);
				})->whereIn('kyc', [$request->status]);
			break;

			case 'kycsubmitted':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser']);
				})->whereIn('kyc', ['submitted']);
			break;
				
			case 'kycrejected':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser']);
				})->whereIn('kyc', ['rejected']);
			break;

			case 'fundrequest':
				$data->where('type', 'request');
				break;

			case 'fundrequestview':
				$data->where('status', 'pending')->where('type', 'request');
				break;
			
			case 'fundrequestviewall':
				//$data->where('type', 'request');
				break;

			case 'aepsfundrequestview':
				$data->where('status', 'pending')->where('pay_type', 'manual');
				break;

			case 'aepspayoutrequestview':
				$data->where('status', 'pending')->where('pay_type', 'payout');
				break;

			case 'rechargestatement':
				$data->where('product', 'recharge')->where('rtype', 'main');
				break;

			case 'aepsreportstatement':
				$data->where('product', 'recharge')->where('rtype', 'main');
				break;
			
			case 'billpaystatement':
				$data->where('aepstype', 'billpay')->where('rtype', 'main');
				break;
				
			case 'gassbillpaystatement':
				$data->where('product', 'gass')->where('rtype', 'main');
				break;
				
			case 'waterbillpaystatement':
				$data->where('product', 'water')->where('rtype', 'main');
				break;
				
			case 'insurancebillpaystatement':
				$data->where('product', 'insurance')->where('rtype', 'main');
				break;
				
			case 'cashdepositbillpaystatement':
				$data->where('aepstype', 'CD')->where('rtype', 'main');
				break;
				
			case 'wtbillpaystatement':
				$data->where('product', 'wt')->where('rtype', 'main');
				break;
				
			case 'gamingtopupbillpaystatement':
				$data->where('product', 'gamingtopup')->where('rtype', 'main');
				break;

			case 'aepsstatement':
				$data->where('rtype', 'main')->where('product', 'aeps')->where('transtype', 'transaction')->where('aepstype', '!=', 'M')->where('aepstype', '!=', 'MS')->where('aepstype', '!=', 'CD');
				break;

			case 'ministatement':
				$data->where('rtype', 'main')->where('product', 'aeps')->where('transtype', 'transaction')->where('aepstype', '=', 'MS');
				break;
            
            case 'apilogstatement':
		//		$data->whereIn('txnid', ['CW3way','MATM3way','AP3way']);
			$data->whereIn('txnid', ['CW3way','MATM3way','AP3way']);

				break;
				
			case 'matmstatement':
				$data->where('rtype', 'main')->where('product', 'matm')->where('transtype', 'transaction')->where('aepstype', '!=', 'M')->where('aepstype', '!=', 'CD');
				break;

			case 'aadharpay':
				$data->where('rtype', 'main')->where('aepstype', 'M')->where('transtype', 'transaction');
				break;
			
			case 'utipancardstatement':
				$data->where('product', 'utipancard')->where('rtype', 'main');
				break;
			
			case 'fundstatement':
				$data->whereHas('provider', function ($q){
					$q->where('recharge1', 'fund');
				});
				break;

			case 'awalletstatement':
				$data->where('aepstype', '!=','BE')->where('aepstype', '!=', 'MS');

				break;

			case 'moneystatement':
				$data->where('product', 'dmt')->where('rtype', 'main');;
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
	        }else{
	        	$data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', date('Y-m-d'))->format('Y-m-d'));
	        }

	        if(isset($request->product) && !empty($request->product)){
	            switch ($request->type) {
					case 'billpaystatement':
					    break;
					case 'gassbillpaystatement':
					    break;
					case 'waterbillpaystatement':
					    break;
					case 'insurancebillpaystatement':
					    break;
					case 'cashdepositbillpaystatement':
					    break;
					case 'wtbillpaystatement':
					    break;
					case 'gamingtopupbillpaystatement':
					    break;
					 
					case 'rechargestatement':
	            		$data->where('provider_id', $request->product);
					break;

					case 'setupoperator':
	            		$data->where('type', $request->product);
					break;

					case 'complaints':
	            		$data->where('product', $request->product);
					break;
					case 'aepscomplaints':
	            		$data->where('product', $request->product);
					break;

					case 'fundstatement':
					case 'aepsfundrequestview':
					case 'aepsfundrequestviewall':
	            		$data->where('type', $request->product);
					break;
				}
			}
			
	        if(isset($request->status) && $request->status != '' && $request->status != null){
	        	switch ($request->type) {	
					case 'kycpending':
					case 'kycsubmitted':
					case 'kycrejected':
						$data->where('kyc', $request->status);
					break;

					default:
	            		$data->where('status', $request->status);
					break;
				}
			}
			
			if(isset($request->agent) && !empty($request->agent)){
	        	switch ($request->type) {					
					case 'whitelable':
					case 'md':
					case 'distributor':
					case 'retailer':
					case 'apiuser':
					case 'other':
					case 'tr' :
					case 'kycpending':
					case 'kycsubmitted':
					case 'kycrejected':
						$data->whereIn('id', $this->agentFilter($request));
					break;

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
		
		if ($request->return == "all" || $request->returntype == "all") {
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
		}else{
			if($request->select == "all"){
				return $data->first();
			}else{
				return $data->select($request->select)->first();
			}
		}
	}

	public function agentFilter($post)
	{
		if (\Myhelper::hasRole('admin') || in_array($post->agent, session('parentData'))) {
			return \Myhelper::getParents($post->agent);
		}else{
			return [];
		}
	}

	public function update(Request $post)
    {
        switch ($post->actiontype) {
            case 'utiid':
                $permission = "Utiid_statement_edit";
				break;
				
			case 'utipancard':
                $permission = "utipancard_statement_edit";
				break;
				
			case 'recharge':
                $permission = "recharge_statement_edit";
				break;
				
			case 'billpay':
                $permission = "billpay_statement_edit";
				break;
			
			case 'money':
                $permission = "money_statement_edit";
                break;

            case 'aeps':
                $permission = "aeps_statement_edit";
                break;
            case 'billpay':
                $permission = "aeps_statement_edit";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'utiid':
                $rules = array(
					'id'    => 'required',
                    'status'    => 'required',
                    'vleid'    => 'required|unique:utiids,vleid'.($post->id != "new" ? ",".$post->id : ''),
                    'vlepassword'    => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $action = Utiid::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype', 'actiontype']));
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
				
			case 'utipancard':
                $rules = array(
					'id'    => 'required',
                    'status'    => 'required',
                    'number'    => 'required',
                    'remark'    => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}
				
				$report = Report::where('id', $post->id)->first();
				if(!$report || !in_array($report->status , ['pending', 'success'])){
					return response()->json(['status' => "Utipancard Editing Not Allowed"], 400);
				}

                $action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
                if ($action) {
					if($post->status == "reversed"){
						\Myhelper::transactionRefund($post->id, "utipancard");
					}

					if($report->user->role->slug == "apiuser" && $report->status == "pending" && $post->status != "pending"){
						\Myhelper::callback($report, 'utipancard');
					}
					
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
				
			case 'recharge':
                $rules = array(
					'id'    => 'required',
                    'status'    => 'required',
                    'txnid'    => 'required',
					'refno'    => 'required',
                    'payid'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}

				$report = Report::where('id', $post->id)->first();
				if(!$report || !in_array($report->status , ['pending', 'success'])){
					return response()->json(['status' => "Recharge Editing Not Allowed"], 400);
				}

                $action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
                if ($action) {
					if($post->status == "reversed"){
						\Myhelper::transactionRefund($post->id, "recharge");
					}

					if($report->user->role->slug == "apiuser" && $report->status != "reversed" && $post->status != "pending"){
						\Myhelper::callback($report, 'recharge');
					}

                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
				
			case 'billpay':
                $rules = array(
					'id'    => 'required',
                    'status'    => 'required',
                    'txnid'    => 'required',
					'refno'    => 'required'
                );
                $post['product'] = "cashdeposit";
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}

				$report = Report::where('id', $post->id)->first();
				$aepsreport = Aepsreport::where('txnid', $post->txnid)->count();
				if($aepsreport > 0)
				{
					$aepsreport = Aepsreport::where('txnid', $post->txnid)->first();
					if(!$aepsreport || !in_array($aepsreport->status , ['pending', 'success'])){
						return response()->json(['status' => "Cash Deposit Editing Not Allowed"], 400);
					}

	                $action = Aepsreport::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
	                if ($action) {
	                	if($post->status == "failed")
	                	{
	                		$post['status'] = 'reversed';
	                	}
	                	
						if($post->status == "reversed"){
							\Myhelper::aepstransactionRefund($post->id, "billpay");
						}

				
                    if($action){
			$aps = Aepsreport::where('id', $post->id)->first();
                        $user = User::where('id', $aps->user_id)->first();
                         $msg =  urlencode("Hi DIGISEVA  cash deposit amount of Rs- ".$aps->amount." for Merchant Name ".$user->name." (".$user->mobile.") is ".$post->status." ");
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message='.$msg.'',
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
                        $res = json_decode($response);

                    


                    
                    
                        //$send = \Myhelper::sms(\Myhelper::adminphone(), $msg);

  			$msg =  urlencode("Hi Dear ".$user->name." cash deposit amount of Rs- ".$aps->amount." is ".$post->status." ");
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.$user->mobile.'&message='.$msg.'',
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
                        $res = json_decode($response);

                    


                    
                    
                        //$send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                        //dd($send); exit;
                        $update['status'] = 'pending';
                        $update['description'] = 'Mainwallet Transfer Submitted Successfully';

                    }
	                    return response()->json(['status' => "success"], 200);
	                }else{
	                    return response()->json(['status' => "Task Failed, please try again"], 200);
	                }
				}
				else
				{
					$post['product'] = "wt";
                
					if(!$report || !in_array($report->status , ['pending', 'success'])){
					return response()->json(['status' => "Recharge Editing Not Allowed"], 400);
					}
					
					$action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
	                if($post->status == "failed")
					{
						$post['status'] = 'reversed';
					}
					if ($action) {
						if($post->status == "reversed"){
							\Myhelper::transactionRefund($post->id, "billpay");
						}
	                    return response()->json(['status' => "success"], 200);
	                }else{
	                    return response()->json(['status' => "Task Failed, please try again"], 200);
	                }
				}

				
				break;
				
			case 'money':
                $rules = array(
					'id'    => 'required',
                    'status'=> 'required',
                    'txnid' => 'required',
					'refno' => 'required',
                    'payid' => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}

				$report = Report::where('id', $post->id)->first();
				if(!$report || !in_array($report->status , ['pending', 'success'])){
					return response()->json(['status' => "Money Transfer Editing Not Allowed"], 400);
				}

                $action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
                if ($action) {
					if($post->status == "reversed"){
						\Myhelper::transactionRefund($post->id, "dmt");
					}
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;

			case 'aeps':
                $rules = array(
					'id'    => 'required',
                    'status'=> 'required',
                    'txnid' => 'required',
					'refno' => 'required',
                    'payid' => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}

				$report = Aepsreport::where('id', $post->id)->first();
				if(!$report || !in_array($report->status , ['pending', 'success', 'initiated'])){
					return response()->json(['status' => "Aeps Not Allowed"], 400);
				}

                $action = Aepsreport::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
        }
	}
	
	public function status(Request $post)
    {
		if (!\Myhelper::can($post->type."_status")) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
		}
		
		switch ($post->type) {
			case 'recharge':
			case 'billpayment':
			case 'utipancard':
			case 'money':
				$report = Report::where('id', $post->id)->first();
				break;

			case 'aeps':
				$report = Aepsreport::where('id', $post->id)->first();
				break;

			case 'utiid':
				$report = Utiid::where('id', $post->id)->first();
				break;

			default:
				return response()->json(['status' => "Status Not Allowed"], 400);
				break;
		}

		if(!$report || !in_array($report->status , ['pending', 'success'])){
			return response()->json(['status' => "Recharge Status Not Allowed"], 400);
		}

		switch ($post->type) {
			case 'recharge':
				$url = $this->api->url."recharge/status";
			
				switch ($report->api->code) {
					case 'recharge1':
						//$url = $report->api->url.'/status?token='.$report->api->username.'&apitxnid='.$report->txnid;
						 $url = 'https://swipecare.co.in/api/rechargestatus?token='.$report->api->username.'&transid='.$report->refno;
						 $method = "GET";
						 $parameter = "";
				         $header = [];
						break;
						
					
					
					default:
						return response()->json(['status' => "Recharge Status Not Allowed"], 400);
						break;
				}
				
				
				
				break;

			case 'billpayment':
				$url = $report->api->url.'/status?token='.$report->api->username.'&apitxnid='.$report->txnid;
				$method = "GET";
				$parameter = "";
				$header = [];
				break;

			case 'utipancard':
				$url = $report->api->url.'/request/status?token='.$report->api->username.'&txnid='.$report->txnid;
				$method = "GET";
				$parameter = "";
				$header = [];
				break;
			
			case 'utiid':
				$url = $report->api->url.'/status?token='.$report->api->username.'&vleid='.$report->vleid;
				$method = "GET";
				$parameter = "";
				$header = [];
				break;

			case 'money':
				$url = $report->api->url."/transaction";
				$method = "POST";
				$parameter = json_encode(array(
					'token' => $report->api->username,
					'type'  => "status",
					'txnid'	=> $report->txnid
				));

				$header = array(
					"Accept: application/json",
					"Cache-Control: no-cache",
					"Content-Type: application/json"
				);
				break;

			case 'aeps':
				$url = $report->api->url.'/status?token='.$report->api->username.'&txnid='.$report->mytxnid;
				$method = "GET";
				$parameter = "";
				$header = [];
				break;
			
			default:
				# code...
				break;
		}

		$result = \Myhelper::curl($url, $method, $parameter, $header);
		//dd([$url, $result]);
		if($result['response'] != ''){
			switch ($post->type) {
				case 'recharge':
					switch ($report->api->code) {
						case 'recharge1':
						   
						$doc = json_decode($result['response']);
					
						if($doc->txnid && $doc->status =="success"){
							$update['refno'] = $doc->txnid;
							$update['status'] = "success";
						}elseif($doc->txnid && $doc->status =="failed"){
							$update['status'] = "failed";
							$update['refno'] = $doc->txnid;
							$update['profit'] = 0;
							$debit = User::where('id', $report->user_id)->increment('mainwallet', $report->amount - $report->profit);
						}else{
							$update['status'] = "pending";
							$update['refno'] = $doc->txnid;
						}
						break;
						
						
					
					
					}
					$product = "recharge";
					break;

				case 'billpayment':
					$doc = json_decode($result['response']);
					if(isset($doc->statuscode)){
						if(($doc->statuscode == "TXN" && $doc->data->status =="success") || ($doc->statuscode == "TXN" && $doc->data->status =="pending")){
							$update['refno'] = $doc->data->ref_no;
							$update['status'] = "success";
						}elseif($doc->statuscode == "TXN" && $doc->data->status =="reversed"){
							$update['status'] = "reversed";
						}else{
							$update['status'] = "Unknown";
						}
					}else{
						$update['status'] = "Unknown";
					}
					$product = "billpay";
					break;

				case 'utipancard':
					$doc = json_decode($result['response']);
					//dd($doc);
					if(isset($doc->statuscode) && $doc->statuscode == "TXN" && $doc->trans_status == "success"){
						$update['status'] = "success";
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TXN" && $doc->trans_status == "reversed"){
						$update['status'] = "reversed";
						$update['refno']  = $doc->refno;
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TUP" && $doc->trans_status == "pending"){
						$update['status'] = "pending";
					}else{
						$update['status'] = "Unknown";
					}
					$product = "utipancard";
					break;

				case 'money':
					$doc = json_decode($result['response']);
					dd($doc);
					if(isset($doc->statuscode) && $doc->statuscode == "TXN" && $doc->trans_status == "success"){
						$update['refno'] = $doc->refno;
						$update['status'] = "success";
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TXN" && $doc->trans_status == "reversed"){
						$update['refno'] = $doc->refno;
						$update['status'] = "reversed";
					}else{
						$update['status'] = isset($doc->trans_status) ? $doc->trans_status : 'unknown';
					}

					$product = "dmt";
					break;

				case 'utiid':
					$doc = json_decode($result['response']);
					//dd($doc);
					if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
						$update['status'] = "success";
						$update['remark'] = $doc->message;
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TXF"){
						$update['status'] = "reversed";
						$update['remark'] = $doc->message;
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TUP"){
						$update['status'] = "pending";
						$update['remark'] = $doc->message;
					}else{
						$update['status'] = "Unknown";
					}
					$product = "utiid";
					break;

				case 'aeps':
					$doc = json_decode($result['response']);
					if(isset($doc->statuscode)){
						if($doc->statuscode == "TXN" && $doc->trans_status =="success"){
							$update['refno'] = $doc->refno;
							$update['status'] = "complete";
						}elseif($doc->statuscode == "TXN" && $doc->trans_status =="complete"){
							$update['refno'] = $doc->refno;
							$update['status'] = "complete";
						}elseif($doc->statuscode == "TXN" && $doc->trans_status =="failed"){
							$update['status'] = "failed";
							$update['refno'] = $doc->refno;
						}elseif($doc->statuscode == "TXN" && $doc->trans_status =="pending"){
							$update['status'] = "pending";
							$update['refno'] = $doc->refno;
						}else{
							$update['status'] = "Unknown";
						}
					}else{
						$update['status'] = "Unknown";
					}
					$product = "aeps";
					break;
			}

			if ($update['status'] != "Unknown") {
				switch ($post->type) {
					case 'recharge':
					case 'billpayment':
					case 'utipancard':
					case 'money':
						$reportupdate = Report::where('id', $post->id)->update($update);
						if ($reportupdate && $update['status'] == "reversed") {
							\Myhelper::transactionRefund($post->id, $product);
						}
						break;

					case 'aeps':
						$reportupdate = Aepsreport::where('id', $post->id)->update($update);

						if($report->status == "pending" && $update['status'] == "complete"){
						    $user = User::where('id', $report->user_id)->first();
						    $insert = [
                                "mobile" => $report->mobile,
                                "aadhar" => $report->aadhar,
                                "api_id" => $report->api_id,
                                "txnid"  => $report->txnid,
                                "refno"  => "Txnid - ".$report->id. " Cleared",
                                "amount" => $report->amount,
                                "bank"   => $report->bank,
                                "user_id"=> $report->user_id,
                                "balance" => $user->aepsbalance,
                                'aepstype'=> $report->aepstype,
                                'status'  => 'success',
                                'authcode'=> $report->authcode,
                                'payid'=> $report->payid,
                                'mytxnid'=> $report->mytxnid,
                                'terminalid'=> $report->terminalid,
                                'TxnMedium'=> $report->TxnMedium,
                                'credited_by' => $report->credited_by,
                                'type' => 'credit'
                            ];
                            if($report->amount >= 100 && $report->amount <= 3000){
		                        $provider = Provider::where('recharge1', 'aeps1')->first();
		                    }elseif($report->amount>3000 && $report->amount<=10000){
		                        $provider = Provider::where('recharge1', 'aeps2')->first();
		                    }
                    
                            $post['provider_id'] = $provider->id;
                            $post['service'] = $provider->type;
                
                            if($report->aepstype == "CW"){
                                if($report->amount > 500){
                                    $usercommission = \Myhelper::getCommission($report->amount, $user->scheme_id, $post->provider_id, $user->role->slug);
                                }else{
                                    $usercommission = 0;
                                }
                            }else{
                                $usercommission = 0;
                            }
                            
                            $insert['charge'] = $usercommission;
                            $action = User::where('id', $report->user_id)->increment('aepsbalance', $report->amount+$usercommission);
                            if($action){
                                $aeps = Aepsreport::create($insert);
                                if($report->amount > 500){
                                    \Myhelper::commission($aeps);
                                }
                            }
						}
						break;

					case 'utiid':
						$reportupdate = Utiid::where('id', $post->id)->update($update);
						break;
				}
			}
			return response()->json($update, 200);
		}else{
			return response()->json(['status' => "Recharge Status Not Fetched , Try Again."], 400);
		}
	}
}
