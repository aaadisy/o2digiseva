<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Model\Role;
use App\Model\Circle;
use App\Model\Scheme;
use App\Model\Company;
use App\Model\Provider;
use App\Model\Utiid;
use App\Model\Permission;
use App\User;
use App\Model\Commission;
use Illuminate\Support\Carbon;
use Illuminate\Http\Response;

class MemberController extends Controller
{
    public function index($type , $action="view")
    {
        if($action != 'view' && $action != 'create'){
            abort(404);
        }

        $data['role'] = Role::where('slug', $type)->first();
        $data['roles'] = [];
        if(!$data['role'] && !in_array($type, ['other', 'kycpending', 'kycsubmitted', 'kycrejected'])){
            abort(404);
        }
        
        if($action == "view" && !\Myhelper::can('kyc_manager')){
            abort(401);
        }elseif($action == "create" && !\Myhelper::can('create_'.$type) && !in_array($type, ['kycpending', 'kycsubmitted', 'kycrejected'])){
            abort(401);
        }

        if($action == "create" && !$data['role']){
            $roles = Role::whereIn('slug', ["whitelable", "md", 'distributor', 'retailer', 'apiuser'])->get();

            foreach ($roles as $role) {
                if(\Myhelper::can('create_'.$type)){
                    $data['roles'][] = $role;
                }
            }

            $roless = Role::whereNotIn('slug', ['admin', "whitelable", "md", 'distributor', 'retailer', 'apiuser'])->get();

            foreach ($roless as $role) {
                if(\Myhelper::can('create_other')){
                    $data['roles'][] = $role;
                }
            }
        }
        
        if ($action == "create" && (!$data['role'] && sizeOf($data['roles']) == 0)){
            abort(404);
        }
        
        $data['type'] = $type;
        $data['state'] = Circle::all();
        $data['scheme'] = Scheme::where('user_id', \Auth::id())->get();

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

        if($action == "view"){
            return view('member.index')->with($data);
        }else{
            return view('member.create')->with($data);
        }
    }
    
     public function active()
    {
        
        $data['title'] = 'Active Members';
        return view('member.active')->with($data);
    }



    public function notloggedin(Request $post)
    {
        if ($post->has('days')) {
            $days = $post->input('days', 1);
            $limitDate = Carbon::now()->subDays($days);
        
            $notLoggedInUsers = User::where('isactive', '<', $limitDate)
                ->where('status','active')
                ->orderBy('id', 'desc') // Modify the ordering as needed
                ->get();
        
            $csvFileName = 'not_logged_in_users.csv';
        
            // Generate CSV content
            $csvData = "Name,Mobile,Main Wallet,AEPS Wallet,Last Login Date\n";
            foreach ($notLoggedInUsers as $user) {
                $csvData .= "{$user->name},{$user->mobile},{$user->mainwallet},{$user->aepsbalance},{$user->isactive}\n";
            }
        
            // Prepare the response
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$csvFileName}",
            ];
        
            return new Response($csvData, 200, $headers);
        }
        
        $data['title'] = 'Not Loggedin Members';
        return view('member.notloggedin')->with($data);
    }
    
    
    public function activememberlist()
    {
            
            
            $cuurenttime =  date('Y-m-d H:i:s', strtotime('-60 seconds'));
      
            $data = User::where('isactive','>=', $cuurenttime)->get();
            
          return response()->json($data);
    }

    public function create(\App\Http\Requests\Member $post)
    {
        unset($post->mainwallet);
        unset($post->aepsbalance);
        $role = Role::where('id', $post->role_id)->first();

        if(!in_array($role->slug, ['admin', "whitelable", "md", 'distributor', 'retailer', 'apiuser','other'])){
            if(!\Myhelper::can('create_other')){
                return response()->json(['status' => "Permission not allowed"],200);
            }
        }
        
        if(!\Myhelper::can('create_'.$role->slug)){
            return response()->json(['status' => "Permission not allowed"],200);
        }

        if(\Myhelper::hasNotRole('admin')){
            $parent = User::where('id', \Auth::id())->first(['id', 'rstock', 'dstock', 'mstock']);
            if($role->slug == "md"){
                if($parent->mstock < 1){
                    return response()->json(['status'=>'Low id stock'], 200);
                }
            }

            if($role->slug == "distributor"){
                if($parent->dstock < 1){
                    return response()->json(['status'=>'Low id stock'], 200);
                }
            }

            if($role->slug == "retailer"){
                if($parent->rstock < 1){
                    return response()->json(['status'=>'Low id stock'], 200);
                }
            }
        }

        $post['id'] = "new";
        $post['parent_id'] = \Auth::id();
        if($role->id == 10)
        {
            $post['kyc'] = "verified";
        }
        else
        {
            $post['kyc'] = "pending";
        }
        
        $post['password'] = bcrypt($post->mobile);

        /*if($role->slug == "whitelable"){
            $company = Company::create($post->all());
            $post['company_id'] = $company->id;
        }else{
            $post['company_id'] = \Auth::user()->company_id;
        }*/
        $post['company_id'] = '1';
        if($post->hasFile('aadharcardpics')){
            $filename ='addhar'.\Auth::id().date('ymdhis').".".$post->file('aadharcardpics')->guessExtension();
            $post->file('aadharcardpics')->move(public_path('kyc/'), $filename);
            $post['aadharcardpic'] = $filename;
        }

        if($post->hasFile('pancardpics')){
            $filename ='pan'.\Auth::id().date('ymdhis').".".$post->file('pancardpics')->guessExtension();
            $post->file('pancardpics')->move(public_path('kyc/'), $filename);
            $post['pancardpic'] = $filename;
        }

        if (!$post->has('scheme_id')) {
            $scheme = \DB::table('default_permissions')->where('type', 'scheme')->where('role_id', $post->role_id)->first();
            if($scheme){
                $post['scheme_id'] = $scheme->permission_id;
            }
        }

        $response = User::updateOrCreate(['id'=> $post->id], $post->all());
    	if($response){
            $responses = session('parentData');
            array_push($responses, $response->id);
            session(['parentData' => $responses]);
            
            $permissions = \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $post->role_id)->get();
            if(sizeof($permissions) > 0){
                foreach ($permissions as $permission) {
                    $insert = array('user_id'=> $response->id , 'permission_id'=> $permission->permission_id);
                    $inserts[] = $insert;
                }
                \DB::table('user_permissions')->insert($inserts);
            }
            
            if(\App\User::where('mobile', $response->mobile)->count() > 0){
                $newuser = \App\User::where('mobile', $response->mobile)->first();
                
                    
                    $otp = rand(11111111, 99999999);
                    $walletpin = rand(0000, 9999);
                    User::where('mobile', $response->mobile)->update(['password' => bcrypt($otp), 'passwordold' => $otp, 'walletpin' => $walletpin]);
                    
                    
                    if(\Myhelper::is_template_active(1))
                    {
                            $msg = \Myhelper::get_whatsapp_content(1);
                            $msg =    \Myhelper::filter_parameters($msg,$response->mobile,$otp,$walletpin);
                            $send = \Myhelper::sms($newuser->mobile, $msg);
                    }
                    
                    $msg = "Hello Admin New Member is registered on Portal with mobile number ".$newuser->mobile."";
                    $send = \Myhelper::sms(\Myhelper::adminphone(), $msg);
                    
                    
                    
            }
            
            
                   

            if(\Myhelper::hasNotRole(['admin'])){
                if($role->slug == "md"){
                    User::where('id', \Auth::user()->id)->decrement('mstock', 1);
                }

                if($role->slug == "distributor"){
                    User::where('id', \Auth::user()->id)->decrement('dstock', 1);
                }
    
                if($role->slug == "retailer"){
                    User::where('id', \Auth::user()->id)->decrement('rstock', 1);
                }
            }
    		return response()->json(['status'=>'success'], 200);
    	}else{
    		return response()->json(['status'=>'fail'], 400);
    	}
    }

    public function utiidcreation($user)
    {
        $provider = Provider::where('recharge1', 'utipancard')->first();

        if($provider && $provider->status != 0 && $provider->api && $provider->api->status != 0){
            $parameter['token'] = $provider->api->username;
            $parameter['vle_id'] = $user->mobile;
            $parameter['vle_name'] = $user->name;
            $parameter['location'] = $user->city;
            $parameter['contact_person'] = $user->name;
            $parameter['pincode'] = $user->pincode;
            $parameter['state'] = $user->state;
            $parameter['email'] = $user->email;
            $parameter['mobile'] = $user->mobile;
            $url = $provider->api->url."/create";
            $result = \Myhelper::curl($url, "POST", json_encode($parameter), ["Content-Type: application/json", "Accept: application/json"], "no");

            if(!$result['error'] || $result['response'] != ''){
                $doc = json_decode($result['response']);
                if($doc->statuscode == "TXN"){
                    $parameter['user_id'] = $user->email;
                    $parameter['type'] = "new";
                    Utiid::create($post->all());
                }
            }
        }
    }

    public function getCommission(Request $post)
    {
        $product = ['mobile', 'dth', 'electricity', 'pancard', 'dmt', 'aeps','insurance','cashdeposit','ms'];
        foreach ($product as $key) {
            $data['commission'][$key] = Commission::where('scheme_id', $post->scheme_id)->whereHas('provider', function ($q) use($key){
                $q->where('type' , $key);
            })->get();
        }
        return response()->json(view('member.commission')->with($data)->render());
    }

    public function getScheme(Request $post)
    {
        $user = User::where('id', $post->id)->first(['id', 'role_id']);
        $scheme = Scheme::where('user_id', \Auth::id())->orWhere('type', $user->role->slug)->orWhere('id', $post->scheme_id)->get();
        return response()->json(['data' => $scheme]);
    }
    
    public function user_transaction()
    {
         $data['title'] = 'user_transaction';
         $data['users'] = User::where('status', 'active')->get();
        return view('statement.user_transaction')->with($data);
    }
}
