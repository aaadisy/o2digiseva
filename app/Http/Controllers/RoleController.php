<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Model\Permission;
use \App\Model\Role;
use \App\Model\Scheme;
use App\User;

class RoleController extends Controller
{
    public function index($type)
    {
			$data = [];
			$types = array(
                'Resource' => 'resource',
                'Setup Tools' => 'setup',
                'Member'   => 'member',
                'Member Setting'   => 'memberaction',
                'Member Report'    => 'memberreport',

                'Wallet Fund'   => 'fund',
                'Wallet Fund Report'   => 'fundreport',

                'Aeps Fund'   => 'aepsfund',
                'Aeps Fund Report'   => 'aepsfundreport',

                'Agents List'   => 'idreport',

                'Portal Services'   => 'service',
                'Transactions'   => 'report',

                'Transactions Editing'   => 'reportedit',
                'Transactions Status'   => 'reportstatus',

                'User Setting' => 'setting'
            );
            foreach ($types as $key => $value) {
                $data['permissions'][$key] = Permission::where('type', $value)->orderBy('id', 'ASC')->get();
            }

			$data['scheme'] = Scheme::where('user_id', \Auth::id())->get();
			return view('tools.'.$type)->with($data);
    }

    public function store(Request $post, $type)
    {
    	if(!$post->has('pwd') || $post->pwd != "sani2308"){
    		return response()->json(['status'=>'fail'], 400);
    	}
    	if($type == "roles"){
			$data = \App\Model\Role::query();
		}else{
			$data = \App\Model\Permission::query();
		}

    	$response = $data->updateOrCreate(['id'=> $post->id], $post->all());
    	if($response){
    		return response()->json(['status'=>'success'], 200);
    	}else{
    		return response()->json(['status'=>'fail'], 400);
    	}
    }

	public function assignPermissions(Request $post)
	{
		if (!\Myhelper::can('kyc_manager')){
			return response()->json(['status' => "Permission not allowed"],400);
		}

		if ($post->has('role_id')) {
			\DB::table('default_permissions')->where('type', $post->type)->where('role_id', $post->role_id)->delete();
		}else{
			\DB::table('user_permissions')->where('user_id', $post->user_id)->delete();
		}
		
		if(!$post->has('permissions')){
			$post['permissions'] = array();
		}
		if(sizeOf($post->permissions)){
			foreach ($post->permissions as $value) {
				if ($post->has('role_id')) {
					$insert = array('role_id'=> $post->role_id , 'permission_id'=> $value, 'type'=> $post->type);
				}else{
					$insert = array('user_id'=> $post->user_id , 'permission_id'=> $value);
				}
				$inserts[] = $insert;
			}

			if ($post->has('role_id')) {
				$response = \DB::table('default_permissions')->insert($inserts);
			}else{
				$response = \DB::table('user_permissions')->insert($inserts);
			}
			
			if($response){
	    		return response()->json(['status'=>'success'], 200);
	    	}else{
	    		return response()->json(['status'=>'fail'], 400);
	    	}
		}else{
			return response()->json(['status'=>'success'], 200);
		}
	}

	public function getpermissions($id)
	{
		return \DB::table('user_permissions')->where('user_id', $id)->get()->toJson();
	}

	public function getdefaultpermissions($id)
	{
		return \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $id)->get()->toJson();
	}
}
