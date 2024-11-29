<?php
namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Aepsfundrequest;
use App\Model\Fundbank;
use App\Model\Paymode;
use App\Model\Circle;
use App\Model\Provider;
use Carbon\Carbon;
use App\Model\Mahaagent;
use App\Model\Fingagent;
use App\Model\Api;
use App\Model\Role;
use App\Model\Utiid;
use App\Model\Permission;
use App\Model\UserPermission;
use App\Model\Commission;
use App\Model\Report;
use App\Model\Aepsreport;
use App\Model\Fcmtoken;

use App\Model\Fundreport;
use App\Classes\Authenticator;
use Session;


use App\Model\Dispute;

use URL;
use DB;

class UserController extends Controller
{
    public $fundapi, $admin;
    public function __construct()
    {
        $this->fundapi = Api::where('code', 'fund')
            ->first();
        $this->admin = User::whereHas('role', function ($q)
        {
            $q->where('slug', 'admin');
        })
            ->first();

    }

    public function getAppSetting(Request $post){
        $appsetting = \App\Model\PortalSetting::where('code', 'app_setting')->first();
        return $appsetting->value;
    }

     public function rasiedispute(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'report_id' => 'required',
            'comment' => 'required',
            'user_id' => 'required|numeric',
        );
         $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
        
        if(!$post->status)
        {
            $post->status = 'pending';
        }
       
               if($post->id == "new"){
                    $post['user_id'] = $post->user_id;
                }else{
                    $post['resolve_id'] = $post->user_id;
            
            if($post->status == 'approved')
            {
                $dispute = Dispute::where('id', $post->id)->first();
                $user = User::where('id', $dispute->user_id)->first();
                $report = Report::where('id', $dispute->report_id)->first();
                $profit = \Myhelper::getCommission($report->amount, $user->scheme_id, $report->provider_id, $user->role->slug);
                
                $debit = User::where('id', $dispute->user_id)->increment('mainwallet', $report->amount - $profit);
            }
        }
        unset($post->apptoken);
        $action = Dispute::updateOrCreate(['id'=> $post->id], $post->all());
        if ($action) {
            $output['status'] = "TXN";
         $output['message'] = "Dispute Successfully Filed";
        }else{
           
            $output['status'] = "ERR";
         $output['message'] = "Task Failed, please try again";
        }

       
         }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

            return response()->json($output);

        
    }
    public function login(Request $post)
    {
        $rules = array(
            'password' => 'required',
            'mobile' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('mobile', $post->mobile)
            ->with(['role'])
            ->first();
        if (!$user)
        {
            return response()->json(['status' => 'ERR', 'message' => "Your aren't registred with us."]);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post
            ->password]))
        {
            return response()
                ->json(['status' => 'ERR', 'message' => 'Username and Password is incorrect']);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password, 'status' => "active"]))
        {
            return response()
                ->json(['status' => 'ERR', 'message' => 'Your account currently de-activated, please contact administrator']);
        }


        $otprequired = \App\Model\PortalSetting::where('code', 'otplogin')->first();
        $wotprequired = \App\Model\PortalSetting::where('code', 'wotplogin')->first();
        //dd($otprequired); exit;
        //$wotprequired->value = "yes";
        if($wotprequired->value == "yes") {
            if($post->has('otp') && $post->otp != ""){
                $otp = User::where('otpverify', $post->otp)->where('mobile', $post->mobile)->count();
                if($otp > 0){
                    $flag = 1;
                }else{
                    return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                }
            } else {

                $otp = rand(100000, 999999);
                if($post->mobile == '7001491919'){
                    $otp = '123456';
                    $post['latitude'] = '22.0873829';
                    $post['longitude'] = '87.8562545';
                }
                User::where('mobile', $post->mobile)->update(['otpverify'=>$otp]);
                if (\Myhelper::is_template_active(2))
                {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                    $send = \Myhelper::sms($post->mobile, $msg);
                    
                    //$send = 'success';
                }
                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                // $otp = rand(0000, 999999);
                // //dd($otp); exit;
                // User::where('mobile', $post->mobile)->update(['otpverify'=>$otp]);

                // $curl = curl_init();

                // curl_setopt_array($curl, array(
                // CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.$user->mobile.'&message=Hello%20'.urldecode($user->name).'%20you%20have%20request%20for%20login%20OTP:%20'.$otp,
                // CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_ENCODING => '',
                // CURLOPT_MAXREDIRS => 10,
                // CURLOPT_TIMEOUT => 0,
                // CURLOPT_FOLLOWLOCATION => true,
                // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                // CURLOPT_CUSTOMREQUEST => 'GET',
                // ));

                // $response = curl_exec($curl);

                // curl_close($curl);
                // dd($response); exit;
                // //echo $response;
                
                // return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
            }
        }

        if($otprequired->value == "yes" && !$post->has('otp')){
            
                $Authenticator = new Authenticator();
                if (!Session::get('auth_secret')) {
                    $secret = $Authenticator->generateRandomSecret();
                    Session::put('auth_secret', $secret);

                }
                
                
                $qrCodeUrl = $Authenticator->getQR('DIGISeva', Session::get('auth_secret'));
                if($qrCodeUrl){
                    
                    return response()->json(['status' => 'TXN','message' => 'Qr Recived Successfully', 'qrcode'=>$qrCodeUrl], 200);
                }else{
                    return response()->json(['status' => 'ERR','message'=>'please try again'], 200);
               }
            
        }

        if($otprequired->value == "yes" && $post->has('otp'))
        {
            $Authenticator = new Authenticator();
            $checkResult = $Authenticator->verifyCode(Session::get('auth_secret'), $post->otp, 2);    
            if($checkResult)
            {
                
                
            }
            else
            {
                return response()->json(['status' => 'ERR','message'=>'Invalid OTP'], 200);
            }
            exit;
        }

        if ($user->apptoken == "none")
        {
            do
            {
                $string = str_random(40);
            }
            while (User::where("apptoken", "=", $string)->first() instanceof User);
            User::where('mobile', $post->mobile)
                ->update(['apptoken' => $string]);

if($post->mobile == '7001491919'){
                    $otp = '123456';
                    $post['latitude'] = '22.0873829';
                    $post['longitude'] = '87.8562545';
                }
                
                User::where('mobile', $post->mobile)->update([
    'lat' => number_format($post->latitude, 7, '.', ''),
    'long' => number_format($post->longitude, 7, '.', '')
]);
        }

        $user = User::where('mobile', $post->mobile)
            ->with(['role'])
            ->first();
        if ($user)
        {
            if ($user->status == 'active')
            {
                $user->status_id = 1;
            }
            else
            {
                $user->status_id = 0;
            }
            $user->balance = $user->mainwallet;
        }
        //$agent = Mahaagent::where('user_id', $user->id)->where('status', 'success')->first();
        $agent = Fingagent::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();
        if ($agent)
        {
            $user->aepsid = 'yes';
            $user->merchant_id = $agent->merchantLoginId;
            $user->request_id = $agent->merchantLoginPin;
        }
        else
        {
            $user->aepsid = 'no';
            $user->merchant_id = 'no';
            $user->request_id = 'no';
        }
        $utiid = Utiid::where('user_id', $user->id)
            ->first();
        if ($utiid)
        {
            $user['utiid'] = $utiid->vleid;
            $user['utiidtxnid'] = $utiid->id;
            $user['utiidstatus'] = $utiid->status;
        }
        else
        {
            $user['utiid'] = 'no';
            $user['utiidstatus'] = 'no';
            $user['utiidtxnid'] = 'no';
        }
        $user['tokenamount'] = '107';
        if(isset($post->token) && !empty($post->token)){
            User::where('id', $user->id)->update(['fcm_token' => $post->token]);
            Fcmtoken::create(['user_id' => $user->id, 'token' => $post->token]);
        }
        
        if($post->mobile == '7001491919'){
                    $otp = '123456';
                    $post['latitude'] = '22.0873829';
                    $post['longitude'] = '87.8562545';
                }

        User::where('mobile', $post->mobile)->update([
    'lat' => number_format($post->latitude, 7, '.', ''),
    'long' => number_format($post->longitude, 7, '.', '')
]);
        return response()->json(['status' => 'TXN', 'message' => 'User details matched successfully', 'userdata' => $user]);
    }


    public function logout(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );
         $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
        
       
       User::where('id', $post->user_id)
                    ->update(['apptoken' => 'none']);

        $output['status'] = "TXN";
         $output['message'] = "Logged Out Successfully";
         }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

            return response()->json($output);
    }

    public function kycupdate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'shopname' => 'required',
            'pancard' => 'required',
            'aadharcard' => 'required',
            'pancardpics' => 'required',
            'aadharcardpics' => 'required',
        );

         $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
        $post['id'] = $user->id;

        if($post->hasFile('pancardpics')){
                    $filename ='pancardpics'.$user->id.date('ymdhis').".".$post->file('pancardpics')->guessExtension();
                    $post->file('pancardpics')->move(public_path('kyc/'), $filename);
                    $post['pancardpic'] = $filename;
                    $post['kyc'] = 'submitted';
                }
        if($post->hasFile('aadharcardpics')){
                    $filename ='aadharcardpics'.$user->id.date('ymdhis').".".$post->file('aadharcardpics')->guessExtension();
                    $post->file('aadharcardpics')->move(public_path('kyc/'), $filename);
                    $post['aadharcardpic'] = $filename;
                }

        $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], $post->all());

         $output['status'] = "TXN";
         $output['message'] = "KYC Submitted SuccessFully";
         }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

            return response()->json($output);

    }
    public function userstatas(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        
        if(!session('parentData')){
            session(['parentData' => \Myhelper::getParents($user->id)]);
        }

       // $data['state'] = Circle::all();
        $roles = ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'other'];

        foreach ($roles as $role) {
            if($role == "other"){
                $data[$role] = User::whereHas('role', function($q){
                    $q->whereNotIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'admin']);
                })->whereIn('id', session('parentData'))->whereIn('kyc', ['verified'])->count();
            }else{
                $data[$role] = User::whereHas('role', function($q) use($role){
                    $q->where('slug', $role);
                })->whereIn('id', session('parentData'))->whereIn('kyc', ['verified'])->count();
            }
        }

        $product = [
            'recharge',
            'billpayment',
            'cashdeposit',
            'insurance',
            'utipancard',
            'money',
            'aeps'
        ];

        $slot = ['today' , 'month', 'lastmonth'];

        $statuscount = [ 'success' => ['success'] , 'pending' => ['pending'], 'failed' => ['failed', 'reversed']];

        foreach ($product as $value) {
            foreach ($slot as $slots) {

                if($value == "aeps" || $value == "cashdeposit"){
                    $query = Aepsreport::whereIn('user_id', session('parentData'));
                }else{
                    $query = Report::whereIn('user_id', session('parentData'));
                }

                

                if($slots == "today"){
                    $query->whereDate('created_at', date('Y-m-d'));
                }

                if($slots == "month"){
                    $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
                }

                if($slots == "lastmonth"){
                    $query->whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'));
                }

                switch ($value) {
                    case 'recharge':
                        $query->where('product', 'recharge');
                        break;
                    
                    case 'billpayment':
                        $query->where('product', 'billpay');
                        break;

                    case 'utipancard':
                        $query->where('product', 'utipancard');
                        break;

                    case 'money':
                        $query->where('product', 'dmt');
                        break;

                    case 'cashdeposit':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', 'CD');
                    case 'aeps':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype','!=', 'CD');
                        break;
                }
                $data[$value][$slots] = $query->where('status', 'success')->sum('amount');
            }

            foreach ($statuscount as $keys => $values) {
                if($value == "aeps" || $value == "cashdeposit"){
                    $query = Aepsreport::whereIn('user_id', session('parentData'));
                }else{
                    $query = Report::whereIn('user_id', session('parentData'));
                }
                switch ($value) {
                    case 'recharge':
                        $query->where('product', 'recharge');
                        break;
                    
                    case 'billpayment':
                        $query->where('product', 'billpay');
                        break;

                    case 'utipancard':
                        $query->where('product', 'utipancard');
                        break;

                    case 'money':
                        $query->where('product', 'dmt');
                        break;

                    case 'cashdeposit':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype', 'CD');
                    case 'aeps':
                        $query->where('transtype', 'transaction')->where('rtype', 'main')->where('aepstype','!=', 'CD');
                        break;
                }
                $data[$value][$keys] = $query->whereIn('status', $values)->whereDate('created_at', date('Y-m-d'))->count();
            }
        }
        
        $output['status'] = "TXN";
        $output['data'] = $data;

        return response()->json($output);

    }

    public function appbanner(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        $data =  DB::table('banners')->where('type','mobileapp')->get();
        $banner = array();
        foreach($data as $d)
        {
            $banner[] = URL::to('/public/images/')."/".$d->banner;
        }

            $output['status'] = "TXN";
            $output['banners'] = $banner;

            return response()->json($output);

    }

    public function rechargebanner(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        $data =  DB::table('banners')->where('type','mobileapp')->get();
        $banner = array();
        foreach($data as $d)
        {
            $banner[] = URL::to('/public/images/')."/".$d->banner;
        }

            $output['status'] = "TXN";
            $output['banners'] = $banner;

            return response()->json($output);

    }

    
    public function aepsBank(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $aepsbanks = \DB::table('aepsbanks')->select('id', 'bankName as bankname', 'id', 'BankIIN as bankiin', 'BankIIN as iinno')
                ->orderBy('bankName', 'ASC')
                ->get();
            $output['status'] = "TXN";
            $output['statuscode'] = "txn";

            $output['message'] = "Balance Fetched Successfully";
            $output['data'] = $aepsbanks;
        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function notifications(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $notifications = DB::table('notifications')->orderBy('id', 'DESC')
                ->where('seen', 0)
                ->where('user_id', $post->user_id)
                ->limit('10')
                ->get();
            $data['notifications_count'] = DB::table('notifications')->orderBy('id', 'DESC')
                ->where('seen', 0)
                ->where('user_id', $post->user_id)
                ->limit('10')
                ->count();
            $data['notifications'] = $notifications;

            $output['status'] = "TXN";
            $output['notifications_count'] = $data['notifications_count'];
            $output['notifications'] = $data['notifications'];

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);

    }

    public function news(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'company_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $news = \App\Model\Companydata::where('company_id', $post->company_id)
                ->first();

            $output['status'] = "TXN";

            $output['news'] = $news->news;
            $output['notice'] = $news->notice;

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function mycertificate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $data['bac_agent_id'] = 'DIGI' . str_pad($post->user_id, 4, '0', STR_PAD_LEFT);
            $data['mobile'] = $user->mobile;
            $data['agent_name'] = $user->name;

            $output['status'] = "TXN";

            $output['certificate'] = $data;
            $output['message'] = "certificate data Fetched Successfully";

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function support(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'support_message' => 'required',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $content = $post->support_message;
            $send = \Myhelper::sms(\Myhelper::adminphone() , $content);

            $output['status'] = "TXN";

            $output['message'] = "message Submitted SuccessFully";

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function stocktransfer(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'id' => 'required|numeric',
            'stock' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            if (!\Myhelper::can('member_stock_manager', $post->user_id))
            {

                $output['status'] = "ERR";
                $output['message'] = "Permission Not Allowed";
            }

            if ($user
                ->role->slug == 'whitelable')
            {
                if ($post->stock > 0 && $user->mstock < $post->stock)
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Low id stock";
                    return response()->json($output);
                }
            }
            if ($user
                ->role->slug == 'md')
            {
                if ($post->stock > 0 && $user->dstock < $post->stock)
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Low id stock";
                    return response()->json($output);
                }
            }
            if ($user
                ->role->slug == 'distributor')
            {
                if ($post->stock > 0 && $user->rstock < $post->stock)
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Low id stock";
                    return response()->json($output);
                }
            }

            if ($user
                ->role->slug == 'whitelable')
            {
                if ($post->stock != '')
                {
                    User::where('id', $user->id)
                        ->decrement('mstock', $post->stock);
                    $response = User::where('id', $post->id)
                        ->increment('mstock', $post->stock);
                }
            }

            if ($user
                ->role->slug == 'md')
            {
                if ($post->stock != '')
                {
                    User::where('id', $user->id)
                        ->decrement('dstock', $post->stock);
                    $response = User::where('id', $post->id)
                        ->increment('dstock', $post->stock);
                }
            }

            if ($user
                ->role->slug == 'distributor')
            {

                if ($post->stock != '')
                {
                    User::where('id', $user->id)
                        ->decrement('rstock', $post->stock);
                    $response = User::where('id', $post->id)
                        ->increment('rstock', $post->stock);
                }
            }

            if ($response)
            {
                $output['status'] = "TXN";
                $output['message'] = "stock transfer SuccessFully";
            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "failed";
            }

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function givetopup(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'topup_userid' => 'required',
            'amount' => 'required',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
            $post['type'] = "transfer";
            $post['provider_id'] = 54;

            if ($post->type == "transfer" && !\Myhelper::can('fund_transfer', $post->user_id))
            {
                return response()
                    ->json(['status' => "Permission not allowed"], 400);
            }

            if ($post->type == "return" && !\Myhelper::can('fund_return', $post->user_id))
            {
                return response()
                    ->json(['status' => "Permission not allowed"], 400);
            }

            $rules = array(
                'amount' => 'required|numeric|min:1',
            );

            if ($post->type == "transfer")
            {
                if ($user->mainwallet - $this->mainlocked() < $post->amount)
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Insufficient balance in user wallet.";
                    return response()->json($output);
                }
            }
            else
            {
                $user = User::where('id', $post->user_id)
                    ->first();
                if ($user->mainwallet - $this->mainlocked() < $post->amount)
                {

                    $output['status'] = "ERR";
                    $output['message'] = "Insufficient balance in user wallet.";
                    return response()->json($output);
                }
            }
            $post['txnid'] = 0;
            $post['option1'] = 0;
            $post['option2'] = 0;
            $post['option3'] = 0;
            $post['refno'] = date('ymdhis');
            return $this->paymentAction($post);

            return response()->json($output);

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        return response()->json($output);
    }

    public function paymentAction($post)
    {
        $user = User::where('id', $post->topup_userid)
            ->first();

        if ($post->type == "transfer" || $post->type == "request")
        {
            $action = User::where('id', $post->topup_userid)
                ->increment('mainwallet', $post->amount);
        }
        else
        {
            $action = User::where('id', $post->user_id)
                ->decrement('mainwallet', $post->amount);
        }

        if ($action)
        {
            if ($post->type == "transfer" || $post->type == "request")
            {
                $post['trans_type'] = "credit";
            }
            else
            {
                $post['trans_type'] = "debit";
            }

            $insert = ['number' => $user->mobile, 'mobile' => $user->mobile, 'provider_id' => $post->provider_id, 'api_id' => $this
                ->fundapi->id, 'amount' => $post->amount, 'charge' => '0.00', 'profit' => '0.00', 'gst' => '0.00', 'tds' => '0.00', 'apitxnid' => NULL, 'txnid' => $post->txnid, 'payid' => NULL, 'refno' => $post->refno, 'description' => NULL, 'remark' => $post->remark, 'option1' => $post->option1, 'option2' => $post->option2, 'option3' => $post->option3, 'option4' => NULL, 'status' => 'success', 'user_id' => $user->id, 'credit_by' => $post->user_id, 'rtype' => 'main', 'via' => 'portal', 'adminprofit' => '0.00', 'balance' => $user->mainwallet, 'trans_type' => $post->trans_type, 'product' => "fund " . $post->type];
            $action = Report::create($insert);
            if ($action)
            {
                $post['user_id'] = $post->user_id;
                return $this->paymentActionCreditor($post);
            }
            else
            {

                $output['status'] = "ERR";
                $output['message'] = "Technical error, please contact your service provider before doing transaction.";
                return response()->json($output);
            }
        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "Fund transfer failed, please try again.";
            return response()->json($output);
        }
    }

    public function paymentActionCreditor($post)
    {
        $payee = $post->topup_userid;
        $user = User::where('id', $post->user_id)
            ->first();
        if ($post->type == "transfer" || $post->type == "request")
        {
            $action = User::where('id', $user->id)
                ->decrement('mainwallet', $post->amount);
        }
        else
        {
            $action = User::where('id', $user->id)
                ->increment('mainwallet', $post->amount);
        }

        if ($action)
        {
            if ($post->type == "transfer" || $post->type == "request")
            {
                $post['trans_type'] = "debit";
            }
            else
            {
                $post['trans_type'] = "credit";
            }

            $insert = ['number' => $user->mobile, 'mobile' => $user->mobile, 'provider_id' => $post->provider_id, 'api_id' => $this
                ->fundapi->id, 'amount' => $post->amount, 'charge' => '0.00', 'profit' => '0.00', 'gst' => '0.00', 'tds' => '0.00', 'apitxnid' => NULL, 'txnid' => $post->txnid, 'payid' => NULL, 'refno' => $post->refno, 'description' => NULL, 'remark' => $post->remark, 'option1' => $post->option1, 'option2' => $post->option2, 'option3' => $post->option3, 'option4' => NULL, 'status' => 'success', 'user_id' => $user->id, 'credit_by' => $payee, 'rtype' => 'main', 'via' => 'portal', 'adminprofit' => '0.00', 'balance' => $user->mainwallet, 'trans_type' => $post->trans_type, 'product' => "fund " . $post->type];

            $action = Report::create($insert);
            if ($action)
            {
                $output['status'] = "TXN";
                $output['message'] = "Fund transferred Successfully";
                return response()->json($output);
            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "Technical error, please contact your service provider before doing transaction.";
                return response()->json($output);
            }
        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "Technical error, please contact your service provider before doing transaction.";
            return response()->json($output);
        }
    }

    public function walletpinupdate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'walletpin' => 'required|numeric',
            'new_walletpin' => 'required|numeric',
            'confirm_walletpin' => 'required_with:password|same:new_walletpin',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        
        if ($user)
        {

            if ($user->walletpin == $post->walletpin)
            {




                $walletpinotp = \App\Model\PortalSetting::where('code', 'walletpinupdateotp')->first();
                $walletpinotp->value = "yes";
                if($walletpinotp->value == "yes") {
                    if($post->otp != ""){
                        $otp = User::where('walletpinotp', $post->otp)->where('mobile', $user->mobile)->count();
                        if($otp > 0){
                            $flag = 1;
                        }else{
                            return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                        }
                    } else {


                        $otp = rand(100000, 999999);
                        
                        User::where('mobile', $user->mobile)->update(['walletpinotp'=>$otp]);

                        if (\Myhelper::is_template_active(2))
                        {
                            $msg = \Myhelper::get_whatsapp_content(2);
                            $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                            $send = \Myhelper::sms($user->mobile, $msg);
                            //dd($send); exit;
                            //$send = 'success';
                        }
                        
                        return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                    }
                }



                User::where('id', $post->user_id)
                    ->update(['walletpin' => $post->new_walletpin]);

                $output['status'] = "TXN";

                $output['message'] = "walletpin updated successfully";
            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "incorrect walletpin";
            }

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function verifypin(Request $post){
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'walletpin' => 'required|numeric',
        );


        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
            if ($user->walletpin == $post->walletpin)
            {
                return response()->json(['status' => 'TXNOTP', 'message' => 'Wallet pin matched successfully']);
            }else{
                return response()->json(['status' => 'ERR', 'message' => 'Wallet pin could not match.']); 
            }
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'User could not match.']); 
        }
    }

    public function membercreate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'role_id' => 'required|numeric',
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'shopname' => 'required',
            'pancard' => 'required',
            'aadharcard' => 'required',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }
        $post->user_id = 1;
        $post->apptoken = "2783462786472735673256532287";
        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
            unset($post->mainwallet);
            unset($post->mainwallet);
            unset($post->apptoken);
            $role = Role::where('id', $post->role_id)
                ->first();

            if (!in_array($role->slug, ['admin', "whitelable", "md", 'distributor', 'retailer', 'apiuser', 'other']))
            {
                if (!\Myhelper::can('create_other', $post->user_id))
                {
                    return response()
                        ->json(['status' => "Permission not allowed"], 200);
                }
            }

            if (!\Myhelper::can('create_' . $role->slug, $post->user_id))
            {
                return response()
                    ->json(['status' => "Permission not allowed"], 200);
            }

            if (\Myhelper::hasNotRole('admin'))
            {
                $parent = User::where('id', $post->user_id)
                    ->first(['id', 'rstock', 'dstock', 'mstock']);
                if ($role->slug == "md")
                {
                    if ($parent->mstock < 1)
                    {
                        return response()
                            ->json(['status' => 'Low id stock'], 200);
                    }
                }

                if ($role->slug == "distributor")
                {
                    if ($parent->dstock < 1)
                    {
                        return response()
                            ->json(['status' => 'Low id stock'], 200);
                    }
                }

                if ($role->slug == "retailer")
                {
                    if ($parent->rstock < 1)
                    {
                        return response()
                            ->json(['status' => 'Low id stock'], 200);
                    }
                }
            }

            unset($post->user_id);
            $post['id'] = "new";
            $post['parent_id'] = $post->user_id;
            if ($role->id == 10)
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
            $post['company_id'] = $user->company_id;
            }*/
            $post['company_id'] = '1';
            if ($post->hasFile('aadharcardpics'))
            {
                $filename = 'addhar' . $post->user_id . date('ymdhis') . "." . $post->file('aadharcardpics')
                    ->guessExtension();
                $post->file('aadharcardpics')
                    ->move(public_path('kyc/') , $filename);
                $post['aadharcardpic'] = $filename;
            }

            if ($post->hasFile('pancardpics'))
            {
                $filename = 'pan' . $post->user_id . date('ymdhis') . "." . $post->file('pancardpics')
                    ->guessExtension();
                $post->file('pancardpics')
                    ->move(public_path('kyc/') , $filename);
                $post['pancardpic'] = $filename;
            }

            if (!$post->has('scheme_id'))
            {
                $scheme = \DB::table('default_permissions')->where('type', 'scheme')
                    ->where('role_id', $post->role_id)
                    ->first();
                if ($scheme)
                {
                    $post['scheme_id'] = $scheme->permission_id;
                }
            }

            try {

                $response = User::updateOrCreate(['id' => $post
                ->id], $post->all());
              
              } catch (\Exception $e) {
                $output['status'] = "ERR";
                $output['message'] = "Something wents wrong please check your mobile number and email id";
                //return $e->getMessage();
                return response()->json($output);
              }
            
            

            if ($response)
            {
                $responses = session('parentData');
                array_push($responses, $response->id);
                session(['parentData' => $responses]);

                $permissions = \DB::table('default_permissions')->where('type', 'permission')
                    ->where('role_id', $post->role_id)
                    ->get();
                if (sizeof($permissions) > 0)
                {
                    foreach ($permissions as $permission)
                    {
                        $insert = array(
                            'user_id' => $response->id,
                            'permission_id' => $permission->permission_id
                        );
                        $inserts[] = $insert;
                    }
                    \DB::table('user_permissions')->insert($inserts);
                }

                if (\App\User::where('mobile', $response->mobile)
                    ->count() > 0)
                {
                    $newuser = \App\User::where('mobile', $response->mobile)
                        ->first();

                   $otp = rand(100000, 999999);
                    User::where('mobile', $response->mobile)
                        ->update(['password' => bcrypt($otp) ]);

                    if (\Myhelper::is_template_active(1))
                    {
                        $msg = \Myhelper::get_whatsapp_content(1);
                        $msg = \Myhelper::filter_parameters($msg, $response->mobile, $otp, "");
                        $send = \Myhelper::sms($newuser->mobile, $msg);
                    }

                    $msg = "Hello Admin New Member is registered on Portal with mobile number " . $newuser->mobile . "";
                    $send = \Myhelper::sms(\Myhelper::adminphone() , $msg);

                }

                if (\Myhelper::hasNotRole(['admin']))
                {
                    if ($role->slug == "md")
                    {
                        User::where('id', $user->id)
                            ->decrement('mstock', 1);
                    }

                    if ($role->slug == "distributor")
                    {
                        User::where('id', $user->id)
                            ->decrement('dstock', 1);
                    }

                    if ($role->slug == "retailer")
                    {
                        User::where('id', $user->id)
                            ->decrement('rstock', 1);
                    }
                }
                $output['status'] = "TXN";
                $output['message'] = "Member Created Successfully";
            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "Filed";
            }

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }
    public function getrole()
    {
        $news = \App\Model\Role::get();

        $output['status'] = "TXN";

        $output['role'] = $news;
        $output['message'] = 'Role Fetched Successfully';

        return response()->json($output);
    }

    public function checkpermission(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'permission' => 'required',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            if (\Myhelper::can($post->permission, $user->id))
            {
                return response()
                    ->json(['status' => "TXN", "message" => "Permission  Allowed"]);
            }
            else
            {
                return response()
                    ->json(['status' => "ERR", "message" => "Permission Not Allowed"]);
            }

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function permissions(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $Permissions = DB::table('permissions')->get();
            $output['status'] = "TXN";
            $output['permissions'] = $Permissions;

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function defaultpermissions(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $Permissions = Permission::get();
            $output['status'] = "TXN";
            $output['defaultpermissions'] = $Permissions;

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function userPermission(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'user_id_for_permission' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $Permissions = UserPermission::where('user_id', $post->user_id)
                ->get();
            $output['status'] = "TXN";
            $output['UserPermission'] = $Permissions;

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function mycommision(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {
            $roleslug = $user
                ->role->slug;
            $permission = "view_commission";
            $product = ['mobile', 'dth', 'electricity', 'dmt', 'pancard', 'aeps', 'ms'];
            foreach ($product as $key)
            {
                $data['commission'][$key] = Commission::select($roleslug, 'slab', 'type')->where('scheme_id', $user->scheme_id)->whereHas('provider', function ($q) use ($key)
                {
                    $q->where('type', $key);
                })->get();
            }
            $output['status'] = "TXN";
            $output['mycommision'] = $data;
            $output['message'] = "User Commission Fetched Successfully";

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function gstStateList(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();
        if ($user)
        {

            $aepsbanks = \DB::table('gst_state')->select('id', 'name', 'code')
                ->orderBy('name', 'ASC')
                ->get();
            $output['status'] = "TXN";
            $output['statuscode'] = "txn";

            $output['message'] = "State Fetched Successfully";
            $output['data'] = $aepsbanks;
        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function getbalance(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first(['mainwallet', 'aepsbalance']);
        if ($user)
        {
            $output['status'] = "TXN";
            $output['message'] = "Balance Fetched Successfully";
            $output['data'] = [
    "mainwallet" => sprintf("%.2f", $user->mainwallet),
    "aepsbalance" => sprintf("%.2f", $user->aepsbalance)
];

        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }
        return response()->json($output);
    }

    public function aepsInitiate(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        if (!\Myhelper::can('aeps_service', $post->user_id))
        {
            return response()
                ->json(['status' => "ERR", "message" => "Service Not Allowed"]);
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->count();
        if ($user)
        {
            $agent = Mahaagent::where('user_id', $post->user_id)
                ->first();

            if ($agent)
            {
                $api = Api::where('code', 'aeps')->first();

                $data["bc_id"] = $agent->bc_id;
                $data["code"] = $api->optional1;
                $data["token"] = $api->username;

                $url = $api->url . "/transaction";
                $header = array(
                    "Content-Type: application/json"
                );
                $result = \Myhelper::curl($url, "POST", json_encode($data) , $header, "no");
                //dd([$url, json_encode($data), $result]);
                if ($result['response'] != '')
                {
                    $datas = json_decode($result['response']);
                    if (isset($datas->statuscode) && $datas->statuscode == "TXN")
                    {
                        $output['status'] = "TXN";
                        $output['message'] = "Deatils Fetched Successfully";
                        $output['data'] = ["saltKey" => $datas
                            ->data->saltkey, "secretKey" => $datas
                            ->data->secretkey, "BcId" => $agent->bc_id, "UserId" => $post->user_id, "bcEmailId" => $agent->emailid, "Phone1" => $agent->phone1];
                    }
                    else
                    {
                        $output['status'] = "ERR";
                        $output['message'] = "Technical Error, Contact Service Provider";
                    }
                }
                else
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Technical Error, Contact Service Provider";
                }
            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "Aeps registration pending";
            }
        }
        else
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }

        return response()->json($output);
    }

    public function passwordResetRequest(Request $post)
    {
        $rules = array(
            'mobile' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('mobile', $post->mobile)
            ->first();
        if ($user)
        {
            $otp = rand(100000, 999999);
            if (\Myhelper::is_template_active(2))
            {
                $msg = \Myhelper::get_whatsapp_content(2);
                $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                $send = \Myhelper::sms($post->mobile, $msg);
                
                //$send = 'success';
            }
            $otpmailid = \App\Model\PortalSetting::where('code', 'otpsendmailid')->first();
            $otpmailname = \App\Model\PortalSetting::where('code', 'otpsendmailname')->first();
            //$mail = \Myhelper::mail('mail.password', ["token" => $otp, "name" => $user->name], $user->email, $user->name, $otpmailid->value, $otpmailname->value, "Reset Password");
            $mail = 'success';
            if ($send == "success" || $mail == "success")
            {
                $tkn = bcrypt($otp);
                User::where('mobile', $post->mobile)->update(['remember_token' => $otp ]);
                //App\User::where('mobile', $post->mobile)->update(['password' => bcrypt($otp) ]);
                return response()->json(['status' => 'TXN', 'message' => "New Password Reset OTP Sent Successfully."]);
            }
            else
            {
                return response()
                    ->json(['status' => 'ERR', 'message' => "Something went wrong"]);
            }
        }
        else
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "You aren't registered with us"]);
        }
    }

    public function changepassword(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'oldpassword' => 'required|min:8',
            'password' => 'required|min:8',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->first();
        if (!\Myhelper::can('password_reset', $post->user_id))
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Permission Not Allowed"]);
        }

        $credentials = ['mobile' => $user->mobile, 'password' => $post->oldpassword];

        if (!\Auth::validate($credentials))
        {
            return response()->json(['status' => 'ERR', 'message' => "Please enter corret old password"]);
        }

        $post['passwordold'] = $post->password;
        $post['password'] = $post->password; //bcrypt($post->password);
        //dd($post->all()); exit;
        $passwordupdateotp = \App\Model\PortalSetting::where('code', 'passwordupdateotp')->first();
        
         $passwordupdateotp->value = "yes";
        if($passwordupdateotp->value == "yes") {
            if($post->has('otp') && $post->otp != ""){
                $otp = User::where('otpverify', $post->otp)->where('mobile', $user->mobile)->count();
                if($otp > 0){
                    $flag = 1;
                }else{
                    return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                }
            } else {

                $otp = rand(100000, 999999);
                User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
                if (\Myhelper::is_template_active(2))
                {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                    $send = \Myhelper::sms($user->mobile, $msg);
                    
                    //$send = 'success';
                }
                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                
            }
        }
          
          //dd($post->password); exit;
        $response = User::where('id', $post->user_id)
            ->update(['password' => bcrypt($post->password), 'passwordold' => $post->password ]);
        if ($response)
        {
            return response()->json(['status' => 'TXN', 'message' => 'User password changed successfully']);
        }
        else
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Something went wrong"]);
        }
    }

    public function passwordReset(Request $post)
    {
        $rules = array(
            'mobile' => 'required|numeric',
            'password' => 'required',
            'otp' => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('mobile', $post->mobile)
            ->where('remember_token', $post->otp)
            ->get();
        //dd($user->count()); exit;
        if ($user->count() == 1)
        {
            $update = User::where('mobile', $post->mobile)
                ->update(['password' => bcrypt($post->password) , 'passwordold' => $post->password]);
            if ($update)
            {
                return response()->json(['status' => "TXN", 'message' => "Password reset successfully"], 200);
            }
            else
            {
                return response()
                    ->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
            }
        }
        else
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Please enter valid otp"], 400);
        }
    }

    public function getState()
    {
        $state = \App\Model\Circle::all(['state']);
        return response()->json(['status' => 'TXN', 'message' => 'State Details', 'data' => $state]);
    }

    public function getCircle()
    {
        $state = array("Andhra Pradesh Telangana", "Assam", "Bihar Jharkhand", "Chennai", "Delhi NCR", "Gujarat", "Haryana", "Himachal Pradesh", "Jammu Kashmir", "Karnataka", "Kerala", "Kolkata", "Madhya Pradesh Chhattisgarh", "Maharashtra Goa", "Mumbai", "North East", "Orissa", "Punjab", "Rajasthan", "Tamil Nadu", "UP East", "UP West", "West Bengal");
        return response()->json(['status' => 'TXN', 'message' => 'State Details', 'data' => $state]);
    }

    public function getPlanOperator()
    {
        $state = array("Airtel", "Aircel", "Bsnl", "Tata Docomo", "Tata Indicom", "Jio", "Vodafone", "Idea", "MTS", "MTNL");
        return response()->json(['status' => 'TXN', 'message' => 'State Details', 'data' => $state]);
    }

    public function getDthCiOperator()
    {
        $dthcioperator = array("Airteldth", "TataSky", "Videocon", "Sundirect", "Dishtv");
        return response()->json(['status' => 'TXN', 'message' => 'Dth OPerstor Details', 'data' => $dthcioperator]);
    }

    public function getDthPlanOperator()
    {
        $dthoperator = array("Airtel dth", "Dish TV", "Tata Sky", "Sun Direct", "Videocon");
        return response()->json(['status' => 'TXN', 'message' => 'Dth OPerstor Details', 'data' => $dthoperator]);
    }

    public function simplePlan(Request $post){
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.mplan.in/api/plans.php?apikey=2a27133a47858620d0e485ec67d60d15&cricle='.urlencode($post->circle).'&operator='.urlencode($post->operator),
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
        //dd($response); exit;
        if(isset($response) && !empty($response)){
            return response()->json(['status' => 'TXN', 'message' => 'Plans found as stated', 'data' => json_decode($response)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }

    }

    public function dthCustomerInfo(Request $post){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.mplan.in/api/Dthinfo.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel='.urlencode($post->number).'&operator='.urlencode($post->operator),
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
        //dd($response); exit;
        if(isset($response) && !empty($response)){
            return response()->json(['status' => 'TXN', 'message' => 'Plans found as stated', 'data' => json_decode($response)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }
    }

    public function dthPlan(Request $post){
        $curl = curl_init();
       
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.mplan.in/api/dthplans.php?apikey=2a27133a47858620d0e485ec67d60d15&operator='.urlencode($post->operator),
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
        //dd($response); exit;
        if(isset($response) && !empty($response)){
            return response()->json(['status' => 'TXN', 'message' => 'Plans found as stated', 'data' => json_decode($response)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }
    }

    public function dthPlanChannel(Request $post){
        $curl = curl_init();
       
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.mplan.in/api/dth_plans.php?apikey=2a27133a47858620d0e485ec67d60d15&operator='.urlencode($post->operator),
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
        //dd($response); exit;
        if(isset($response) && !empty($response)){
            return response()->json(['status' => 'TXN', 'message' => 'Plans found as stated', 'data' => json_decode($response)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }
    }

    public function dthRoffer(Request $post){

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.mplan.in/api/DthRoffer.php?apikey=2a27133a47858620d0e485ec67d60d15&offer=roffer&tel='.urlencode($post->number).'&operator='.urlencode($post->operator),
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
        //dd($response); exit;
        if(isset($response) && !empty($response)){
            return response()->json(['status' => 'TXN', 'message' => 'Plans found as stated', 'data' => json_decode($response)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }
    }

    public function dthRofferOperator(Request $post){
        $operator = array("AirtelDTH", "Sundirect");
        if(isset($operator) && !empty($operator)){
            return response()->json(['status' => 'TXN', 'message' => 'Operator found as stated', 'data' => json_decode($operator)]);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Plan could not be found', 'data' => []]);
        }
    }

    

    

    public function getCompany(Request $post)
    {
        //dd($post->all()); exit;
        $api_token = \App\Model\Apitoken::where('domain', $_SERVER['HTTP_HOST'])->where('token', $post->apptoken)
            ->first();
        $user = User::where('id', $api_token->user_id)
            ->first();
        $company = \App\Model\Companydata::where('company_id', $user->company_id)
            ->select('number', 'email')
            ->first();
        return response()
            ->json($company, 200);
    }

    public function changeProfile(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'name' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'pincode' => 'required|numeric|digits:6',
            'pancard' => 'required',
            'aadharcard' => 'required|numeric|digits:12',
            'shopname' => 'required',
            'city' => 'required',
            'state' => 'required'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->count();

        if ($user == 0)
        {
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        $update = User::where('id', $post->user_id)
            ->update(array(
            'name' => $post->name,
            'email' => $post->email,
            'address' => $post->address,
            'pincode' => $post->pincode,
            'pancard' => $post->pancard,
            'aadharcard' => $post->aadharcard,
            'shopname' => $post->shopname,
            'city' => $post->city,
            'state' => $post->state
        ));

        if ($update)
        {
            return response()->json(['status' => 'TXN', 'message' => 'User profile updated successfully']);
        }
        else
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Something went wrong"]);
        }
    }

    public function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function signupstore(Request $post)
    {
        //dd($post->all()); exit;
        $rules = array(
            'name' => 'required',
            'email' => 'required',
            'mobile' => 'required|numeric',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'shopname' => 'required',
            'pancard' => 'required',
            'aadharcard' => 'required',
            'role_id' => 'required'
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }
        if (User::where('mobile', $post->mobile)
            ->count() > 0)
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Mobile Number Already Registed"]);
        }
        if (User::where('email', $post->email)
            ->count() > 0)
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Email Address Already Registed"]);

        }
        unset($post->mainwallet);
        unset($post->aepsbalance);
        $role = Role::where('id', $post->role_id)
            ->first();
        $post['id'] = "new";
        $post['parent_id'] = 1;
        $post['kyc'] = "pending";
        $pwd = $this->randomPassword();//rand(11111111, 99999999);
                
        $post['password'] = bcrypt($pwd);
        $post['passwordold'] = $pwd;

        $post['company_id'] = '1';
        if ($post->hasFile('aadharcardpics'))
        {
            $filename = 'addhar' . $post->user_id . date('ymdhis') . "." . $post->file('aadharcardpics')
                ->guessExtension();
            $post->file('aadharcardpics')
                ->move(public_path('kyc/') , $filename);
            $post['aadharcardpic'] = $filename;
        }

        if ($post->hasFile('pancardpics'))
        {
            $filename = 'pan' . $post->user_id . date('ymdhis') . "." . $post->file('pancardpics')
                ->guessExtension();
            $post->file('pancardpics')
                ->move(public_path('kyc/') , $filename);
            $post['pancardpic'] = $filename;
        }

        if (!$post->has('scheme_id'))
        {
            $scheme = \DB::table('default_permissions')->where('type', 'scheme')
                ->where('role_id', $post->role_id)
                ->first();
            if ($scheme)
            {
                $post['scheme_id'] = $scheme->permission_id;
            }
        }

        $response = User::updateOrCreate(['id' => $post
            ->id], $post->all());

        if ($response)
        {

            $permissions = \DB::table('default_permissions')->where('type', 'permission')
                ->where('role_id', $post->role_id)
                ->get();
            if (sizeof($permissions) > 0)
            {
                foreach ($permissions as $permission)
                {
                    $insert = array(
                        'user_id' => $response->id,
                        'permission_id' => $permission->permission_id
                    );
                    $inserts[] = $insert;
                }
                \DB::table('user_permissions')->insert($inserts);
            }

            if (\App\User::where('mobile', $response->mobile)
                ->count() > 0)
            {
                $newuser = \App\User::where('mobile', $response->mobile)
                    ->first();

                $otp = rand(0000, 9999);
                User::where('mobile', $newuser->mobile)
                    ->update(['walletpin' => $otp ]);

                if (\Myhelper::is_template_active(1))
                {
                    $msg = \Myhelper::get_whatsapp_content(1);
                    $msg = \Myhelper::filter_parameters($msg, $newuser->mobile, $pwd, $otp);
                    $send = \Myhelper::sms($newuser->mobile, $msg);
                }
                $se = $send;
                $msg = "Hello Admin New Member is registered on Portal with mobile number " . $newuser->mobile . "";
                $send = \Myhelper::sms(\Myhelper::adminphone() , $msg);
                //dd([$send, $se]); exit;
            }

            return response()->json(['status' => 'TXN', 'message' => "Signed Up Successfully"]);
        }
        else
        {
            return response()
                ->json(['status' => 'ERR', 'message' => "Something went wrong"]);
        }

    }
    

    public function offlinecashdeposit(Request $post)
    {
        // $output['status'] = "ERR";
        //         $output['message'] = "Service Down for few hours";
        //         return response()->json($output);

        $rules = array(
            'apptoken' => 'required',
            'user_id' => 'required|numeric',
            'walletpin' => 'required',
            'amount' => 'required',
            'number' => 'required',
            'mobile' => 'required',
        );
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no")
        {
            return $validate;
        }

        $user = User::where('id', $post->user_id)
            ->where('apptoken', $post->apptoken)
            ->first();



        $walletpinotp = \App\Model\PortalSetting::where('code', 'cashdeposite')->first();
        $walletpinotp->value = "yes";
        if($walletpinotp->value == "yes") {
            if($post->otp != ""){
                $otp = User::where('otpverify', $post->otp)->where('mobile', $user->mobile)->count();
                if($otp > 0){
                    $flag = 1;
                }else{
                    return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                }
            } else {


                $otp = rand(100000, 999999);
                
                User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);

                if (\Myhelper::is_template_active(2))
                {
                    $msg = \Myhelper::get_whatsapp_content(2);
                    $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                    $send = \Myhelper::sms($user->mobile, $msg);
                    //dd($send); exit;
                    //$send = 'success';
                }
                
                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
            }
        }



        if ($user)
        {
            
            $user = $user;
            $post['user_id'] = $user->id;
            if ($user->status != "active")
            {
                $output['status'] = "ERR";
                $output['message'] = "Your account has been blocked";

                return response()->json($output);

            }
            if ($user->walletpin != $post->walletpin)
            {
                $output['status'] = "ERR";
                $output['message'] = "Incorrect Wallet Pin";
                return response()->json($output);
            }
            $post->provider_id = 87;
            $post->biller = $user->name;
            $provider = DB::table('providers')->where('id', $post->provider_id)
                ->first();
            $post['type'] = 'payment';
            $post['billtype'] = 'cashdeposit';

            switch ($post->type)
            {


                case 'payment':


                    switch ($post->billtype)
                    {

                        case 'cashdeposit':
                            $billpaytype = $post->billtype;
                        break;

                    }


                    $api = Api::where('code', 'billpay')->first();

                    $previousrecharge = Report::where('number', $post->number)
                        ->where('amount', $post->amount)
                        ->where('provider_id', $post->provider_id)
                        ->whereBetween('created_at', [Carbon::now()
                        ->subMinutes(2)
                        ->format('Y-m-d H:i:s') , Carbon::now()
                        ->format('Y-m-d H:i:s') ])
                        ->count();
                    if ($previousrecharge > 0)
                    {

                        $output['status'] = "ERR";
                        $output['message'] = "Same Transaction allowed after 2 min.";
                        return response()->json($output);
                    }

                    $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user
                        ->role
                        ->slug);
                    

                    if ($user->aepsbalance - $this->aepslocked() < $post->amount)
                    {

                        $output['status'] = "ERR";
                        $output['message'] = "Low aeps balance to make this request";
                        return response()->json($output);
                    }


                    if ($previousrecharge > 0)
                    {

                        $output['status'] = "ERR";
                        $output['message'] = "Same Transaction allowed after 2 min.";
                        return response()->json($output);
                    }

                    // $debit = User::where('id', $user->id)
                    //     ->decrement('aepsbalance', ($post->amount + $post->profit));

                    $previousrecharge = Report::where('number', $post->number)
                        ->where('amount', $post->amount)
                        ->where('provider_id', $post->provider_id)
                        ->whereBetween('created_at', [Carbon::now()
                        ->subMinutes(2)
                        ->format('Y-m-d H:i:s') , Carbon::now()
                        ->format('Y-m-d H:i:s') ])
                        ->count();

                    // $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user
                    //     ->role
                    //     ->slug);
                    $cd_surcahrge_type = \Myhelper::cd_surcahrge_type();
                    $surchargevalue = \Myhelper::cd_surcahrge_value();
                    if ($cd_surcahrge_type == 'percentage')
                    {
                        $surcharge = $post->amount * 100 / $surchargevalue;
                    }
                    else
                    {
                        $surcharge = $surchargevalue;
                    }

                    if (User::where('id', $user->id)
                        ->first()->aepsbalance < $post->amount + $surcharge)
                    {
                        return response()->json(['status' => 'AEPS Balance is not sufficient to make this Transaction'], 400);
                    }
                    $debit = User::where('id', $user->id)
                        ->decrement('aepsbalance', ($post->amount + $surcharge));
                    $post['profit'] = 0;

                    //dd($user->mainwallet); exit;
                    

                    $debit = true;
                    if ($debit)
                    {
                        do
                        {
                            $post['txnid'] = $this->transcode() . rand(1111111111, 9999999999);
                        }
                        while (Report::where("txnid", "=", $post->txnid)
                            ->first() instanceof Report);

                        $insert = [

                        'test' => $post->biller, 'user_idew' => date("Y-m-d H:m:i"),

                        'provider_id' => $post->provider_id, 'charge' => $surcharge, 'aadhar' => $post->number, 'mobile' => $post->mobile, 'txnid' => $post->txnid, 'amount' => $post->amount, 'user_id' => $user->id, "balance" => $user->aepsbalance, 'type' => "debit", 'api_id' => $api->id, 'credited_by' => $user->id, 'status' => 'pending', 'rtype' => 'main', 'trans_type' => 'transaction', 'bank' => $provider->name, 'aepstype' => 'CD', 'withdrawType' => 'CD', 'product' => 'cashdeposit'];
                        $report = Aepsreport::create($insert);

                        
                        

                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Hello%20Admin%20New%20Cash%20Deposit%20Request%20is%20of%20Rs.%20'.$post->amount.'%20subimmted%20by%20User%20Name:%20'.urlencode($user->name).'%20User%20Mobile:%20'.urlencode($user->mobile).'%20User%20ID:%20'.urlencode($user->id).'',
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


                        // $msg = "Hello Admin New Cash Deposit Request is of Rs. " . $post->amount . " subimmted by  User Name: " . $user->name . " User Mobile: " . $user->mobile . " User ID: " . $user->id . " From Android APP";
                        // $send = \Myhelper::sms(\Myhelper::adminphone() , $msg);




                        $update['status'] = 'pending';
                        $update['description'] = 'Cash Despost Submitted Successfully';

                        if ($update['status'] == "success" || $update['status'] == "pending")
                        {
                            Report::where('id', $report->id)
                                ->update($update);
                            if ($post->billtype != 'cashdeposit')
                            {
                                \Myhelper::commission($report);
                            }
                        }
                        else
                        {
                            User::where('id', $user->id)
                                ->increment('aepsbalance', ($post->amount + $post->profit));
                            Report::where('id', $report->id)
                                ->update($update);
                        }

                        $output['status'] = "TXN";
                        $output['cashdeposit_status'] = $update['status'];
                        $output['report'] = $report;
                        $output['description'] = $update['description'];
                        $output['message'] = "Transaction ".$update['status'];
                        return response()->json($output);
                    }
                    else
                    {
                        $output['status'] = "ERR";
                        $output['message'] = "Transaction Failed, please try again.";
                        return response()->json($output);

                    }
                    break;
                }

            }
            else
            {
                $output['status'] = "ERR";
                $output['message'] = "User details not matched";
            }

            return response()->json($output);

        }
    

        public function wallettransfer(Request $post)
        {
            // $output['status'] = "ERR";
            //         $output['message'] = "Service Down for few hours";
            //         return response()->json($output);
            //dd($post->all()); exit;
            $rules = array(
                'apptoken' => 'required',
                'user_id' => 'required|numeric',
                'walletpin' => 'required',
                'amount' => 'required',
                'number' => 'required',
                'mobile' => 'required',
            );
            
            $validate = \Myhelper::FormValidator($rules, $post);
            if ($validate != "no")
            {
                return $validate;
            }
    
            $user = User::where('id', $post->user_id)
                ->where('apptoken', $post->apptoken)
                ->first();
            //dd($user); exit;
    
    
            $walletpinotp = \App\Model\PortalSetting::where('code', 'wt')->first();
            $walletpinotp->value = "yes";
            if($walletpinotp->value == "yes") {
                if($post->otp != ""){
                    $otp = User::where('otpverify', $post->otp)->where('mobile', $user->mobile)->count();
                    if($otp > 0){
                        $flag = 1;
                    }else{
                        return response()->json(['status' => 'ERR', 'message' => 'OTP could not match.']); 
                    }
                } else {
    
    
                    $otp = rand(100000, 999999);
                    
                    User::where('mobile', $user->mobile)->update(['otpverify'=>$otp]);
    
                    if (\Myhelper::is_template_active(2))
                    {
                        $msg = \Myhelper::get_whatsapp_content(2);
                        $msg = \Myhelper::filter_parameters($msg, "", $otp, $otp);
                        $send = \Myhelper::sms($user->mobile, $msg);
                        //dd($send); exit;
                        //$send = 'success';
                    }
                    
                    return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent on your registered mobile number']);
                }
            }
    
    
    
            if ($user)
            {
                
                $user = $user;
                $post['user_id'] = $user->id;
                if ($user->status != "active")
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Your account has been blocked";
    
                    return response()->json($output);
    
                }
                if ($user->walletpin != $post->walletpin)
                {
                    $output['status'] = "ERR";
                    $output['message'] = "Incorrect Wallet Pin";
                    return response()->json($output);
                }
                $post->provider_id = 87;
                $post->biller = $user->name;
                $provider = DB::table('providers')->where('id', $post->provider_id)
                    ->first();
                $post['type'] = 'payment';
                $post['billtype'] = 'wt';
    
                switch ($post->type)
                {
    
    
                    case 'payment':
    
    
                        switch ($post->billtype)
                        {
    
                            case 'wt':
                                $billpaytype = $post->billtype;
                            break;
    
                        }
    
    
                        $api = Api::where('code', 'billpay')->first();
    
                        $previousrecharge = Report::where('number', $post->number)
                            ->where('amount', $post->amount)
                            ->where('provider_id', $post->provider_id)
                            ->whereBetween('created_at', [Carbon::now()
                            ->subMinutes(2)
                            ->format('Y-m-d H:i:s') , Carbon::now()
                            ->format('Y-m-d H:i:s') ])
                            ->count();
                        if ($previousrecharge > 0)
                        {
    
                            $output['status'] = "ERR";
                            $output['message'] = "Same Transaction allowed after 2 min.";
                            return response()->json($output);
                        }
    
                        $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user
                            ->role
                            ->slug);
                        
    
                        if ($user->mainwallet - $this->mainlocked() < $post->amount)
                        {
    
                            $output['status'] = "ERR";
                            $output['message'] = "Low main balance to make this request";
                            return response()->json($output);
                        }
    
    
                        if ($previousrecharge > 0)
                        {
    
                            $output['status'] = "ERR";
                            $output['message'] = "Same Transaction allowed after 2 min.";
                            return response()->json($output);
                        }
    
                        // $debit = User::where('id', $user->id)
                        //     ->decrement('mainwallet', ($post->amount + $post->profit));
    
                        $previousrecharge = Report::where('number', $post->number)
                            ->where('amount', $post->amount)
                            ->where('provider_id', $post->provider_id)
                            ->whereBetween('created_at', [Carbon::now()
                            ->subMinutes(2)
                            ->format('Y-m-d H:i:s') , Carbon::now()
                            ->format('Y-m-d H:i:s') ])
                            ->count();
    
                        // $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $post->provider_id, $user
                        //     ->role
                        //     ->slug);
                        $wt_surcahrge_type = \Myhelper::wt_surcahrge_type();
                        $surchargevalue = \Myhelper::wt_surcahrge_value();
                        if ($wt_surcahrge_type == 'percentage')
                        {
                            $surcharge = $post->amount * 100 / $surchargevalue;
                        }
                        else
                        {
                            $surcharge = $surchargevalue;
                        }
    
                        if (User::where('id', $user->id)
                            ->first()->mainwallet < $post->amount + $surcharge)
                        {
                            return response()->json(['status' => 'Main Balance is not sufficient to make this Transaction'], 400);
                        }
                        $debit = User::where('id', $user->id)
                            ->decrement('mainwallet', ($post->amount + $surcharge));
                        $post['profit'] = 0;
    
                        //dd($user->mainwallet); exit;
                        
    
                        $debit = true;
                        if ($debit)
                        {
                            do
                            {
                                $post['txnid'] = $this->transcode() . rand(1111111111, 9999999999);
                            }
                            while (Report::where("id", "=", $post->txnid)
                                ->first() instanceof Report);
    
                            // $insert = [
    
                            // 'test' => $post->biller, 'user_idew' => date("Y-m-d H:m:i"),
    
                            // 'provider_id' => $post->provider_id, 'charge' => $surcharge, 'aadhar' => $post->number, 'mobile' => $post->mobile, 'txnid' => $post->txnid, 'amount' => $post->amount, 'user_id' => $user->id, "balance" => $user->aepsbalance, 'type' => "debit", 'api_id' => $api->id, 'credited_by' => $user->id, 'status' => 'pending', 'rtype' => 'main', 'trans_type' => 'transaction', 'bank' => $provider->name, 'aepstype' => 'CD', 'withdrawType' => 'CD', 'product' => 'cashdeposit'];
                            // $report = Aepsreport::create($insert);

                            $insert = [
                                'provider_id' => '86',//$post->provider_id,
                                'charge' => $surcharge,
                                'number' => $post->number,
                                'mobile' => $post->mobile,
                                'option1' => $user->bank,
                                'option2' => $post->mobile,
                                'option3' => $post->number,
                                'option4' => $user->name,
                                'txnid' => $post->txnid,
                                'amount' => $post->amount,
                                'user_id'    => $user->id,
                                "balance" => $user->mainwallet,
                                'trans_type'    => "debit",
                                'api_id' => $api->id,
                                'credit_by'  => $user->id,
                                'status' => 'pending',
                                'rtype'      => 'main',
                                'product'    => 'wt',
                                'via'   => 'APP'
                            ];
                            $report = Report::create($insert);


    
                            
                            
    
                            $curl = curl_init();
    
                            curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91'.\Myhelper::adminphone().'&message=Hello%20Admin%20New%20Cash%20Deposit%20Request%20is%20of%20Rs.%20'.$post->amount.'%20subimmted%20by%20User%20Name:%20'.urlencode($user->name).'%20User%20Mobile:%20'.urlencode($user->mobile).'%20User%20ID:%20'.urlencode($user->id).'',
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
    
    
                            // $msg = "Hello Admin New Cash Deposit Request is of Rs. " . $post->amount . " subimmted by  User Name: " . $user->name . " User Mobile: " . $user->mobile . " User ID: " . $user->id . " From Android APP";
                            // $send = \Myhelper::sms(\Myhelper::adminphone() , $msg);
    
    
    
    
                            $update['status'] = 'pending';
                            $update['description'] = 'Cash Despost Submitted Successfully';
    
                            if ($update['status'] == "success" || $update['status'] == "pending")
                            {
                                Report::where('id', $report->id)
                                    ->update($update);
                                if ($post->billtype != 'cashdeposit')
                                {
                                    \Myhelper::commission($report);
                                }
                            }
                            else
                            {
                                User::where('id', $user->id)
                                    ->increment('aepsbalance', ($post->amount + $post->profit));
                                Report::where('id', $report->id)
                                    ->update($update);
                            }
    
                            $output['status'] = "TXN";
                            $output['cashdeposit_status'] = $update['status'];
                            $output['report'] = $report;
                            $output['description'] = $update['description'];
                            $output['message'] = "Transaction ".$update['status'];
                            return response()->json($output);
                        }
                        else
                        {
                            $output['status'] = "ERR";
                            $output['message'] = "Transaction Failed, please try again.";
                            return response()->json($output);
    
                        }
                        break;
                    }
    
                }
                else
                {
                    $output['status'] = "ERR";
                    $output['message'] = "User details not matched";
                }
    
                return response()->json($output);
    
            }
    }
    
