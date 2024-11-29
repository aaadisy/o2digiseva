<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Model\Aepsreport;
use App\Model\UserPermission;
use App\Model\Apilog;
use App\Model\Fingagent;
use App\Model\Scheme;
use App\Model\Commission;
use App\User;
use App\Model\Report;
use App\Model\Utiid;
use App\Model\Provider;
use App\Model\PortalSetting;


use DB;
use URL;
use App\Model\Whatsapptemplate;

class Permission
{
    /**
     * @param String $permissions
     * 
     * @return boolean
     */
     
     public static function handleMaster2faCost_bkp($type,$userid)
{
    $post['user_id'] = $userid;
    $data['agent'] = Fingagent::where('user_id', $userid)->first();
    
    if(PortalSetting::where('code', 'master_2fa_cost')->first()->value == '0'){
         return true;
    }

    if ($type == 'AEPS') {
        if (($data['agent']->aeps_auth === NULL || strtotime($data['agent']->aeps_auth) !== strtotime(date('Y-m-d')))) {
            $post['provider_id'] = 111;
            $post['txnid'] = 'MCW2FA' . date('ymdhis');
            $user = User::where('id', $post['user_id'])->first();
            $post['amount'] = PortalSetting::where('code', 'master_2fa_cost')->first()->value;
            $action = User::where('id', $post['user_id'])->decrement('mainwallet', $post['amount']);

            if ($action) {
                $post['trans_type'] = "debit";

                $insert = [
                    'number' => $user->mobile,
                    'mobile' => $user->mobile,
                    'provider_id' => $post['provider_id'],
                    'api_id' => 1,
                    'amount' => $post['amount'],
                    'charge' => '0.00',
                    'profit' => '0.00',
                    'gst' => '0.00',
                    'tds' => '0.00',
                    'apitxnid' => NULL,
                    'txnid' => $post['txnid'],
                    'payid' => NULL,
                    'refno' => NULL,
                    'description' => NULL,
                    'remark' => 'Master CW2FA',
                    'option1' => '',
                    'option2' => '',
                    'option3' => '',
                    'option4' => NULL,
                    'status' => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $userid,
                    'rtype' => 'main',
                    'via' => 'portal',
                    'adminprofit' => '0.00',
                    'balance' => $user->mainwallet,
                    'trans_type' => $post['trans_type'],
                    'product' => "fund return"
                ];
                $action = Report::create($insert);
                return $action ? true : false;
            } else {
                return false;
            }
        }
    } else {
        if (($data['agent']->ap_auth === NULL || strtotime($data['agent']->ap_auth) !== strtotime(date('Y-m-d')))) {
            $post['provider_id'] = 112;
            $post['txnid'] = 'AP2FA' . date('ymdhis');
            $user = User::where('id', $post['user_id'])->first();
            $post['amount'] = PortalSetting::where('code', 'master_2fa_cost')->first()->value;
            $action = User::where('id', $post['user_id'])->decrement('mainwallet', $post['amount']);

            if ($action) {
                $post['trans_type'] = "debit";

                $insert = [
                    'number' => $user->mobile,
                    'mobile' => $user->mobile,
                    'provider_id' => $post['provider_id'],
                    'api_id' => 1,
                    'amount' => $post['amount'],
                    'charge' => '0.00',
                    'profit' => '0.00',
                    'gst' => '0.00',
                    'tds' => '0.00',
                    'apitxnid' => NULL,
                    'txnid' => $post['txnid'],
                    'payid' => NULL,
                    'refno' => NULL,
                    'description' => NULL,
                    'remark' => 'AP 2FA',
                    'option1' => '',
                    'option2' => '',
                    'option3' => '',
                    'option4' => NULL,
                    'status' => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $userid,
                    'rtype' => 'main',
                    'via' => 'portal',
                    'adminprofit' => '0.00',
                    'balance' => $user->mainwallet,
                    'trans_type' => $post['trans_type'],
                    'product' => "fund return"
                ];
                $action = Report::create($insert);
                return $action ? true : false;
            } else {
                return false;
            }
        
    }
    }
    return true;
}

public static function handleMaster2faCost($type,$userid)
{
    $post['user_id'] = $userid;
    $data['agent'] = Fingagent::where('user_id', $userid)->first();
    
    if(PortalSetting::where('code', 'master_2fa_cost')->first()->value == '0'){
         return true;
    }

    if ($type == 'AEPS') {
        
        
            $post['provider_id'] = 111;
            $post['txnid'] = 'MCW2FA' . date('ymdhis');
            $user = User::where('id', $post['user_id'])->first();
            $post['amount'] = PortalSetting::where('code', 'master_2fa_cost')->first()->value;
            $action = User::where('id', $post['user_id'])->decrement('mainwallet', $post['amount']);

            if ($action) {
                $post['trans_type'] = "debit";

                $insert = [
                    'number' => $user->mobile,
                    'mobile' => $user->mobile,
                    'provider_id' => $post['provider_id'],
                    'api_id' => 1,
                    'amount' => $post['amount'],
                    'charge' => '0.00',
                    'profit' => '0.00',
                    'gst' => '0.00',
                    'tds' => '0.00',
                    'apitxnid' => NULL,
                    'txnid' => $post['txnid'],
                    'payid' => NULL,
                    'refno' => NULL,
                    'description' => NULL,
                    'remark' => 'Master CW2FA',
                    'option1' => '',
                    'option2' => '',
                    'option3' => '',
                    'option4' => NULL,
                    'status' => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $userid,
                    'rtype' => 'main',
                    'via' => 'portal',
                    'adminprofit' => '0.00',
                    'balance' => $user->mainwallet,
                    'trans_type' => $post['trans_type'],
                    'product' => "fund return"
                ];
                $action = Report::create($insert);
                return $action ? true : false;
            } else {
                return false;
            }
        
    } else {
            $post['provider_id'] = 112;
            $post['txnid'] = 'AP2FA' . date('ymdhis');
            $user = User::where('id', $post['user_id'])->first();
            $post['amount'] = PortalSetting::where('code', 'master_2fa_cost')->first()->value;
            $action = User::where('id', $post['user_id'])->decrement('mainwallet', $post['amount']);

            if ($action) {
                $post['trans_type'] = "debit";

                $insert = [
                    'number' => $user->mobile,
                    'mobile' => $user->mobile,
                    'provider_id' => $post['provider_id'],
                    'api_id' => 1,
                    'amount' => $post['amount'],
                    'charge' => '0.00',
                    'profit' => '0.00',
                    'gst' => '0.00',
                    'tds' => '0.00',
                    'apitxnid' => NULL,
                    'txnid' => $post['txnid'],
                    'payid' => NULL,
                    'refno' => NULL,
                    'description' => NULL,
                    'remark' => 'AP 2FA',
                    'option1' => '',
                    'option2' => '',
                    'option3' => '',
                    'option4' => NULL,
                    'status' => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $userid,
                    'rtype' => 'main',
                    'via' => 'portal',
                    'adminprofit' => '0.00',
                    'balance' => $user->mainwallet,
                    'trans_type' => $post['trans_type'],
                    'product' => "fund return"
                ];
                $action = Report::create($insert);
                return $action ? true : false;
            } else {
                return false;
            }
        
    }
    return true;
}


    

    public static function total_commision($product, $date)
    {
        if (\Auth::check()) {

            if (\Auth::user()->role->slug == 'admin') {
                if ($date == 'today') {
                    return  $today = Report::whereDate('created_at', date('Y-m-d'))->where('product', $product)->where('status', 'success')->sum('adminprofit');
                } elseif ($date == 'month') {
                    return $thismonth = Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('adminprofit');
                } elseif ($date == 'lastmonth') {
                    return $lastmonth = Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('adminprofit');
                }
            } elseif (\Auth::user()->role->slug == 'whitelable') {
                if ($date == 'today') {
                    return  $today = Report::whereDate('created_at', date('Y-m-d'))->where('product', $product)->where('status', 'success')->sum('wprofit');
                } elseif ($date == 'month') {
                    return $thismonth = Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('wprofit');
                } elseif ($date == 'lastmonth') {
                    return $lastmonth = Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('wprofit');
                }
            } elseif (\Auth::user()->role->slug == 'md') {
                if ($date == 'today') {
                    return  $today = Report::whereDate('created_at', date('Y-m-d'))->where('product', $product)->where('status', 'success')->sum('mdprofit');
                } elseif ($date == 'month') {
                    return $thismonth = Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('mdprofit');
                } elseif ($date == 'lastmonth') {
                    return $lastmonth = Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('mdprofit');
                }
            } elseif (\Auth::user()->role->slug == 'distributor') {
                if ($date == 'today') {
                    return  $today = Report::whereDate('created_at', date('Y-m-d'))->where('product', $product)->where('status', 'success')->sum('disprofit');
                } elseif ($date == 'month') {
                    return $thismonth = Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('disprofit');
                } elseif ($date == 'lastmonth') {
                    return $lastmonth = Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('disprofit');
                }
            } elseif (\Auth::user()->role->slug == 'retailer') {
                if ($date == 'today') {
                    return  $today = Report::whereDate('created_at', date('Y-m-d'))->where('product', $product)->where('status', 'success')->sum('profit');
                } elseif ($date == 'month') {
                    return $thismonth = Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('profit');
                } elseif ($date == 'lastmonth') {
                    return $lastmonth = Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->where('product', $product)->where('status', 'success')->sum('profit');
                }
            }
        } else {
            return 0;
        }
    }
    public static function hasRole_id()
    {
        if (\Auth::check()) {
            return  \Auth::user()->role->id;
        } else {
            return false;
        }
    }



    public static function handleFingAeps2()
    {
        if (!\Myhelper::hasRole(['admin']) && auth()->user()->id == 2291) {
            echo 'hi';
            $currentDate = now()->toDateString();
            $agent = \App\Model\Fingagent::where('user_id', auth()->user()->id)->first();

            // Check if the user does not have any agent associated
            if (!$agent) {
                $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
                $data['agent'] = \App\Model\Fingagent::where('user_id', auth()->user()->id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->orderBy('bankName', 'ASC')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                $data['fundrequest'] = \App\Model\Aepsfundrequest::where('user_id', auth()->user()->id)->where('status', 'pending')->first();

                return view('service.fingaeps')->with($data);
            }

            // Check if the agent status is not 'success' or AEPS and AP authentication arrays are missing today's date
            if ($agent->status !== 'success' || !in_array($currentDate, $agent->aeps_auth) || !in_array($currentDate, $agent->ap_auth)) {
                $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
                $data['agent'] = \App\Model\Fingagent::where('user_id', auth()->user()->id)->first();
                $data['aepsbanks'] = \DB::table('fingaepsbanks')->orderBy('bankName', 'ASC')->get();
                $data['aadharbanks'] = \DB::table('fingaadharpaybanks')->get();
                $data['state'] = \DB::table('fingstate')->get();
                $data['fundrequest'] = \App\Model\Aepsfundrequest::where('user_id', auth()->user()->id)->where('status', 'pending')->first();

                return view('service.fingaeps')->with($data);
            }
        }
    }

    public static function handleFingAeps()
    {
        if (!\Myhelper::hasNotRole(['admin']) && auth()->user()->id == 314) {
            $currentDate = now()->toDateString();
            $agent = \App\Model\Fingagent::where('user_id', auth()->user()->id)->first();

            // Check if the user does not have any agent associated
            if (!$agent) {
                return false;
            }

            // Check if the agent status is not 'success' or AEPS and AP authentication arrays are missing today's date
            if ($agent->status !== 'success' || $agent->aeps_auth == NULL || $agent->ap_auth == NULL || !in_array($currentDate, $agent->aeps_auth) || !in_array($currentDate, $agent->ap_auth)) {
                return false;
            }
        }

        // If the conditions are met or if the user is an admin, return true
        return true;
    }



    public static function urlToBase64($imageName)
    {
        $imageUrl = "https://digiseva.me/storage/app/" . $imageName;

        // Read the image file and convert to Base64
        $imageData = file_get_contents($imageUrl);
        if ($imageData !== false) {
            $base64Image = base64_encode($imageData);
            return $base64Image;
        } else {
            return null; // Return null or an error message if unable to read the image file.
        }
    }

    public static function website_popup()
    {
        $popup = DB::table('companydatas')->first()->websitepopup;
        if ($popup != NULL || $popup != '</BR>') {
            return $popup;
        } else {
            return false;
        }
    }
    public static function get_banners()
    {
        return DB::table('banners')->get();
    }

    public static function get_states()
    {
        return DB::table('fingstate')->get();
    }

    public static function company_id()
    {
        if (\Auth::check()) {
            return  \Auth::user()->company_id;
        } else {
            return false;
        }
    }

    public static function company_id_api($user)
    {
        if ($user) {
            return  $user->company_id;
        } else {
            return false;
        }
    }

    public static function total_transaction($id)
    {
        $wallet = DB::table('reports')->whereIn('product', ['recharge', 'billpay', 'dmt', 'aeps', 'utipancard'])->where('user_id', $id)->whereDate('created_at', date("Y-m-d"))->where('status', 'success')->where('trans_type', 'debit')->sum('amount');
        $aeps = DB::table('aepsreports')->where('user_id', $id)->whereDate('created_at', date("Y-m-d"))->where('status', 'success')->where('type', 'debit')->sum('amount');

        return $wallet + $aeps;
    }

    public static function total_profit($id)
    {
        $wallet = DB::table('reports')->whereIn('product', ['recharge', 'billpay', 'dmt', 'aeps', 'utipancard'])->whereDate('created_at', date("Y-m-d"))->where('user_id', $id)->where('status', 'success')->where('trans_type', 'debit')->sum('profit');
        $aeps = DB::table('aepsreports')->whereDate('created_at', date("Y-m-d"))->where('user_id', $id)->where('status', 'success')->where('type', 'debit')->sum('charge');
        return $wallet + $aeps;
    }

    public static function company_details()
    {
        if (\Auth::check()) {

            $companyid = \Myhelper::company_id();
            return  $compdata = \App\Model\Company::where('id', $companyid)->first();
        } else {
            return false;
        }
    }
    public static function company_details_api()
    {
        return  $compdata = \App\Model\Company::where('id', 1)->first();
    }

    public static function can($permission, $id = "none")
    {
        if ($id == "none") {
            $id = \Auth::id();
        }
        $user = User::where('id', $id)->first();

        if (is_array($permission)) {
            $mypermissions = \DB::table('permissions')->whereIn('slug', $permission)->get(['id'])->toArray();
            if ($mypermissions) {
                foreach ($mypermissions as $value) {
                    $mypermissionss[] = $value->id;
                }
            } else {
                $mypermissionss = [];
            }
            $output = UserPermission::where('user_id', $id)->whereIn('permission_id', $mypermissionss)->count();
        } else {
            $mypermission = \DB::table('permissions')->where('slug', $permission)->first(['id']);
            if ($mypermission) {
                $output = UserPermission::where('user_id', $id)->where('permission_id', $mypermission->id)->count();
            } else {
                $output = 0;
            }
        }

        if ($output > 0 || $user->role->slug == "admin") {
            return true;
        } else {
            return false;
        }
    }

    public static function get_whatsapp_content($id)
    {

        $content = Whatsapptemplate::where('id', $id)->first();
        return $content->content;
    }
    public static function is_template_active($id)
    {

        $content = Whatsapptemplate::where('id', $id)->first();
        if ($content->status == 'active') {
            return true;
        } else {
            return false;
        }
    }

    public static function service_active($id)
    {

        $content = PortalSetting::where('code', $id)->first();
        if ($content->value == 'on') {
            return true;
        } else {
            return false;
        }
    }

    public static function cd_surcahrge_type()
    {

        $content = PortalSetting::where('code', 'cd_surcahrge_type')->first();
        return $content->value;
    }

    public static function cd_surcahrge_value()
    {

        $content = PortalSetting::where('code', 'cd_surcahrge_value')->first();
        return $content->value;
    }

    public static function wt_surcahrge_type()
    {

        $content = PortalSetting::where('code', 'wt_surcahrge_type')->first();
        return $content->value;
    }

    public static function wt_surcahrge_value()
    {

        $content = PortalSetting::where('code', 'wt_surcahrge_value')->first();
        return $content->value;
    }

    public static function adminphone()
    {

        $content = User::where('role_id', 1)->first();
        return $content->mobile;
    }

    public static function hasRole($roles)
    {
        if (\Auth::check()) {
            if (is_array($roles)) {
                if (in_array(\Auth::user()->role->slug, $roles)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (\Auth::user()->role->slug == $roles) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function hasNotRole($roles)
    {
        if (\Auth::check()) {
            if (is_array($roles)) {
                if (!in_array(\Auth::user()->role->slug, $roles)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if (\Auth::user()->role->slug != $roles) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function apiLog($url, $modal, $txnid, $header, $request, $response)
    {
        try {
            $apiresponse = Apilog::create([
                "url" => $url,
                "modal" => $modal,
                "txnid" => $txnid,
                "header" => $header,
                "request" => $request,
                "response" => $response
            ]);
        } catch (\Exception $e) {
            $apiresponse = "error";
        }
        return $apiresponse;
    }

    public static function mail($view, $data, $mailto, $name, $mailvia, $namevia, $subject)
    {
        \Mail::send($view, $data, function ($message) use ($mailto, $name, $mailvia, $namevia, $subject) {
            $message->to($mailto, $name)->subject($subject);
            $message->from($mailvia, $namevia);
        });

        if (\Mail::failures()) {
            return "fail";
        }
        return "success";
    }

    public static function get_selected_api_switch($operator, $api)
    {
        $count =  DB::table('api_switch')->where('provider_id', $operator)->where('provider_id_api', $api)->count();
        if ($count > 0) {
            return $data =  DB::table('api_switch')->where('provider_id', $operator)->where('provider_id_api', $api)->first()->api_id;
        } else {
            return '0';
        }
    }

    public static function save_notification($content)
    {
        return true;
        if (\Auth::check()) {
            $userid = \Auth::id();
            DB::table('notifications')->insert(
                ['user_id' => $userid, 'content' => $content]
            );
            return true;
        } else {
            return false;
        }
    }
    public static function filter_parameters($msg, $mobile = "", $password = "", $otp = "")
    {
        $token = array(
            'URL'  =>  URL::to('/'),
            'MOBILE' => $mobile,
            'PASSWORD' => $password,
            'OTP' => $otp
        );
        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        $emailContent = strtr($msg, $varMap);

        return urlencode($emailContent);
    }
    public static function sms($mobile, $content)
    {
        $smsdata = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if (isset($smsdata->senderid)) {
            $url = "http://securesms.co.in/vendorsms/pushsms.aspx?user=" . $smsdata->smsuser . "&password=" . $smsdata->smspwd . "&msisdn=" . $mobile . "&sid=" . $smsdata->senderid . "&msg=" . $content . "&fl=0&gwid=2";
            $url = "http://whatsappbot.co.in/api/send.php?token=54548&no=91" . $mobile . "&text=" . $content . "";
            $url = "https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91" . $mobile . "&message=" . $content . "";
            //return $url; 
            // $curl = curl_init();

            //         curl_setopt_array($curl, array(
            //           CURLOPT_URL => $url,
            //           CURLOPT_RETURNTRANSFER => true,
            //           CURLOPT_ENCODING => '',
            //           CURLOPT_MAXREDIRS => 10,
            //           CURLOPT_TIMEOUT => 0,
            //           CURLOPT_FOLLOWLOCATION => true,
            //           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //           CURLOPT_CUSTOMREQUEST => 'GET',
            //         ));

            //         $response = curl_exec($curl);

            //         curl_close($curl);
            //         //echo $response; exit;
            //         //dd($response); exit;
            //         $result['response'] = $response;

            //return "success";

            $result = \Myhelper::curl($url, "GET", "", [], "no", "", "");
            //return [$url, $result];
            if ($result['response'] != '') {
                $response = json_decode($result['response']);
                if ($response->status == true) {
                    return "success";
                }
            }
        }
        return "fail";
    }
    public static function smsImg($mobile, $content, $img)
    {
        $smsdata = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if (isset($smsdata->senderid)) {
            $url = "http://securesms.co.in/vendorsms/pushsms.aspx?user=" . $smsdata->smsuser . "&password=" . $smsdata->smspwd . "&msisdn=" . $mobile . "&sid=" . $smsdata->senderid . "&msg=" . $content . "&fl=0&gwid=2";
            $url = "http://whatsappbot.co.in/api/send.php?token=54548&no=91" . $mobile . "&text=" . $content . "";
            //$url = "https://whatsbot.tech/api/send_sms?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91".$mobile."&message=".$content."";
            $url = "https://whatsbot.tech/api/send_img?api_token=a6fc4456-bf81-4255-9e5d-e44ebfb361b1&mobile=91" . $mobile . "&img_url=https://digiseva.me/public/whatsappimg/" . $img . "&img_caption=" . $content . "";
            //https://whatsbot.tech/api/send_img?api_token=12345678-1234-1234-1234-123456789123&mobile=919876543210&img_url=https://i.ibb.co/PZjKyTS/mobilesmsapi-mrobo.jpg

            //return $url; 
            // $curl = curl_init();

            //         curl_setopt_array($curl, array(
            //           CURLOPT_URL => $url,
            //           CURLOPT_RETURNTRANSFER => true,
            //           CURLOPT_ENCODING => '',
            //           CURLOPT_MAXREDIRS => 10,
            //           CURLOPT_TIMEOUT => 0,
            //           CURLOPT_FOLLOWLOCATION => true,
            //           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //           CURLOPT_CUSTOMREQUEST => 'GET',
            //         ));

            //         $response = curl_exec($curl);

            //         curl_close($curl);
            //         //echo $response; exit;
            //         //dd($response); exit;
            //         $result['response'] = $response;

            //return "success";

            $result = \Myhelper::curl($url, "GET", "", [], "no", "", "");
            //return [$url, $result];
            if ($result['response'] != '') {
                $response = json_decode($result['response']);
                if ($response->status == true) {
                    return "success";
                }
            }
        }
        return "fail";
    }

    public static function commission($report)
    {
        if (in_array($report->apicode, ['aeps', 'kaeps'])) {
            $insert = [
                'number'  => $report->aadhar,
                'mobile'  => $report->mobile,
                'provider_id' => $report->provider_id,
                'api_id'  => $report->api_id,
                'txnid'   => $report->id,
                'payid'   => $report->payid,
                'refno'   => $report->refno,
                'status'  => 'success',
                'rtype'   => 'commission',
                'trans_type' => "credit",
                'via'     => "portal",
                'product' => "aeps"
            ];

            $provider = $report->provider_id;
            $precommission = $report->charge;
        } else {
            $myreport = Report::where('id', $report->id)->first(['profit']);
            $insert = [
                'number' => $report->number,
                'mobile' => $report->mobile,
                'provider_id' => $report->provider_id,
                'api_id' => $report->api_id,
                'txnid'  => $report->id,
                'payid'  => $report->payid,
                'refno'  => $report->refno,
                'status' => 'success',
                'rtype'  => 'commission',
                'via'    => $report->via,
                'trans_type' => "credit",
                'product' => $report->product
            ];
            if ($report->product == "dmt") {
                $precommission = $report->charge - $myreport->profit;
            } else {
                $precommission = $report->profit;
            }
            $provider = $report->provider_id;
        }

        $parent = User::where('id', $report->user->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        if ($parent->role->slug == "distributor") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'distributor');

            if (in_array($report->product, ['recharge', 'billpay', 'aeps'])) {
                $insert['amount'] = $parentcommission - $precommission;
            } elseif ($report->product == "utipancard") {
                $insert['amount'] = $report->option1 * $parentcommission - $precommission;
            } elseif ($report->product == "dmt") {
                $insert['amount'] = $precommission - $parentcommission;
            }

            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
            Report::create($insert);
            if (in_array($report->apicode, ['aeps', 'kaeps'])) {
                Aepsreport::where('id', $report->id)->update(['disid' => $parent->id, "disprofit" => $insert['amount']]);
            } else {
                Report::where('id', $report->id)->update(['disid' => $parent->id, "disprofit" => $insert['amount']]);
            }

            if (in_array($report->product, ['recharge', 'billpay', 'dmt', 'aeps'])) {
                $precommission = $parentcommission;
            } elseif ($report->product == "utipancard") {
                $precommission = $report->option1 * $parentcommission;
            }

            $parent = User::where('id', $parent->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        }

        if ($parent->role->slug == "md") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'md');

            if (in_array($report->product, ['recharge', 'billpay', 'aeps'])) {
                $insert['amount'] = $parentcommission - $precommission;
            } elseif ($report->product == "utipancard") {
                $insert['amount'] = $report->option1 * $parentcommission - $precommission;
            } elseif ($report->product == "dmt") {
                $insert['amount'] = $precommission - $parentcommission;
            }

            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
            Report::create($insert);
            if (in_array($report->apicode, ['aeps', 'kaeps'])) {
                Aepsreport::where('id', $report->id)->update(['mdid' => $parent->id, "mdprofit" => $insert['amount']]);
            } else {
                Report::where('id', $report->id)->update(['mdid' => $parent->id, "mdprofit" => $insert['amount']]);
            }

            if (in_array($report->product, ['recharge', 'billpay', 'dmt', 'aeps'])) {
                $precommission = $parentcommission;
            } elseif ($report->product == "utipancard") {
                $precommission = $report->option1 * $parentcommission;
            }
            $parent = User::where('id', $parent->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        }

        if ($parent->role->slug == "whitelable") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;

            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'whitelable');
            if (in_array($report->product, ['recharge', 'billpay', 'aeps'])) {

                $insert['amount'] = $parentcommission - $precommission;
            } elseif ($report->product == "utipancard") {
                $insert['amount'] = $report->option1 * $parentcommission - $precommission;
            } elseif ($report->product == "dmt") {
                $insert['amount'] = $precommission - $parentcommission;
            }

            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
            Report::create($insert);
            if (in_array($report->apicode, ['aeps', 'kaeps'])) {
                Aepsreport::where('id', $report->id)->update(['wid' => $parent->id, "wprofit" => $insert['amount']]);
            } else {
                Report::where('id', $report->id)->update(['wid' => $parent->id, "wprofit" => $insert['amount']]);
            }
        }
    }

    public static function aepscommission($report)
    {

        $insert = [
            'number'  => $report->aadhar,
            'mobile'  => $report->mobile,
            'provider_id' => $report->provider_id,
            'api_id'  => $report->api_id,
            'txnid'   => $report->txnid,
            'payid'   => $report->payid,
            'refno'   => $report->refno,
            'status'  => 'success',
            'rtype'   => 'commission',
            'trans_type' => "credit",
            'via'     => "portal",
            'product' => "aeps"
        ];

        $provider = $report->provider_id;
        if ($report->aepstype == 'MS') {
            $precommission = $report->amount;
        } else {
            $precommission = $report->charge;
        }




        $parent = User::where('id', $report->user->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        if ($parent->role->slug == "distributor") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'distributor');

            $insert['amount'] = $parentcommission - $precommission;
            $insert['amount'] = $parentcommission;

            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
            Aepsreport::where('id', $report->id)->update(['disid' => $parent->id, "disprofit" => $insert['amount']]);
            Report::create($insert);
            $precommission = $parentcommission;

            $parent = User::where('id', $parent->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        }

        if ($parent->role->slug == "md") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'md');
            $insert['amount'] = $parentcommission - $precommission;
            $insert['amount'] = $parentcommission;


            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);

            Aepsreport::where('id', $report->id)->update(['mdid' => $parent->id, "mdprofit" => $insert['amount']]);
            Report::create($insert);

            $precommission = $parentcommission;
            $parent = User::where('id', $parent->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);
        }

        if ($parent->role->slug == "whitelable") {
            $insert['balance'] = $parent->mainwallet;
            $insert['user_id'] = $parent->id;
            $insert['credit_by'] = $report->user_id;

            $parentcommission = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'whitelable');
            $insert['amount'] = $parentcommission - $precommission;
            $insert['amount'] = $parentcommission;

            User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
            Aepsreport::where('id', $report->id)->update(['wid' => $parent->id, "wprofit" => $insert['amount']]);
            Report::create($insert);
            $precommission = $parentcommission;
        }

        if ($parent->role->slug == "admin") {

            $adminid = User::where('id', 1)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);

            $insert['balance'] = $adminid->mainwallet;
            $insert['user_id'] = $adminid->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $adminid->scheme_id, $provider, 'admin');
            $lastcom = $precommission;
            $insert['amount'] = $parentcommission - $lastcom;

            $insert['amount'] = $parentcommission;

            User::where('id', $adminid->id)->increment('mainwallet', $insert['amount']);
            Aepsreport::where('id', $report->id)->update(["adminprofit" => $insert['amount']]);
            Report::create($insert);
            $precommission = $parentcommission;
        } else {
            $adminid = User::where('id', 1)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);

            $insert['balance'] = $adminid->mainwallet;
            $insert['user_id'] = $adminid->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission = \Myhelper::getCommission($report->amount, $adminid->scheme_id, $provider, 'admin');
            $lastcom = Report::where('txnid', $report->txnid)->where('rtype', 'commission')->where('credit_by', $report->user_id)->first()->amount;
            $insert['amount'] = $parentcommission - $lastcom;

            $insert['amount'] = $parentcommission;

            User::where('id', $adminid->id)->increment('mainwallet', $insert['amount']);
            Aepsreport::where('id', $report->id)->update(["adminprofit" => $insert['amount']]);
            if ($report->refno == 'Mini Statement') {
                $insert['payid'] = $report->txnid;
                $insert['profit'] = $report->charge;
                $insert['tds'] = $report->tds;
            }
            Report::create($insert);
            $precommission = $parentcommission;
        }
    }




    public static function getCommission($amount, $scheme, $slab, $role = "none")
    {
        $myscheme = Scheme::where('id', $scheme)->first(['status']);
        if ($myscheme && $myscheme->status == "1") {
            $comdata = Commission::where('scheme_id', $scheme)->where('slab', $slab)->first();
            if ($comdata) {
                switch ($role) {
                    case 'admin':
                        if ($comdata->type == "percent") {
                            $commission = $amount * $comdata->admin / 100;
                        } else {
                            $commission = $comdata->admin;
                        }
                        break;

                    case 'whitelable':
                        if ($comdata->type == "percent") {
                            $commission = $amount * $comdata->whitelable / 100;
                        } else {
                            $commission = $comdata->whitelable;
                        }
                        break;

                    case 'md':
                        if ($comdata->type == "percent") {
                            $commission = $amount * $comdata->md / 100;
                        } else {
                            $commission = $comdata->md;
                        }
                        break;

                    case 'distributor':
                        if ($comdata->type == "percent") {
                            $commission = $amount * $comdata->distributor / 100;
                        } else {
                            $commission = $comdata->distributor;
                        }
                        break;

                    case 'retailer':
                        if ($comdata->type == "percent") {
                            $commission = $amount * $comdata->retailer / 100;
                        } else {
                            $commission = $comdata->retailer;
                        }
                        break;

                    default:
                        $commission = 0;
                        break;
                }
                if ($commission == null) {
                    $commission = 0;
                }
            } else {
                $commission = 0;
            }
        } else {
            $commission = 0;
        }
        return $commission;
    }

    public static function curl($url, $method = 'GET', $parameters, $header, $log = "no", $modal = "none", $txnid = "none")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if ($parameters != "") {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        if (sizeof($header) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($log != "no") {
            Apilog::create([
                "url" => $url,
                "modal" => $modal,
                "txnid" => $txnid,
                "header" => $header,
                "request" => $parameters,
                "response" => $err . "/" . $response
            ]);
        }

        return ["response" => $response, "error" => $err, 'code' => $code];
    }

    public static function getParents($id)
    {
        $data = [];
        $user = User::where('id', $id)->first(['id', 'role_id']);
        if ($user) {
            $data[] = $id;
            switch ($user->role->slug) {
                case 'admin':
                    $whitelabels = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'whitelable');
                    })->get(['id']);

                    if (sizeOf($whitelabels) > 0) {
                        foreach ($whitelabels as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $mds = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'md');
                    })->get(['id']);

                    if (sizeOf($mds) > 0) {
                        foreach ($mds as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $distributors = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'distributor');
                    })->get(['id']);

                    if (sizeOf($distributors) > 0) {
                        foreach ($distributors as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->whereIn('slug', ['retailer', 'apiuser']);
                    })->get(['id']);

                    if (sizeOf($retailers) > 0) {
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;

                case 'whitelable':
                    $mds = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'md');
                    })->get(['id']);

                    if (sizeOf($mds) > 0) {
                        foreach ($mds as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $distributors = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'distributor');
                    })->get(['id']);

                    if (sizeOf($distributors) > 0) {
                        foreach ($distributors as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'retailer');
                    })->get(['id']);

                    if (sizeOf($retailers) > 0) {
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;

                case 'md':
                    $distributors = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'distributor');
                    })->get(['id']);

                    if (sizeOf($distributors) > 0) {
                        foreach ($distributors as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'retailer');
                    })->get(['id']);

                    if (sizeOf($retailers) > 0) {
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;

                case 'distributor':
                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function ($q) {
                        $q->where('slug', 'retailer');
                    })->get(['id']);

                    if (sizeOf($retailers) > 0) {
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;
            }
        }
        return $data;
    }

    public static function transactionRefund($id, $product = "none")
    {
        $report = Report::where('id', $id)->first();
        $count = Report::where('user_id', $report->user_id)->whereIn('status', ['refunded', 'reversed'])->where('txnid', $report->id)->count();
        if ($count == 0) {
            $user = User::where('id', $report->user_id)->first(['id', 'mainwallet']);
            if ($report->trans_type == "debit") {
                User::where('id', $report->user_id)->increment('mainwallet', $report->amount + $report->charge - $report->profit);
            } elseif ($report->trans_type == "credit") {
                User::where('id', $report->user_id)->decrement('mainwallet', $report->amount + $report->charge - $report->profit);
            } else {
                return false;
            }
            $insert = [
                'number' => $report->number,
                'mobile' => $report->mobile,
                'provider_id' => $report->provider_id,
                'api_id' => $report->api_id,
                'apitxnid' => $report->apitxnid,
                'txnid' => $report->id,
                'payid' => $report->payid,
                'refno' => $report->refno,
                'description' => "Transaction Reversed, amount refunded",
                'remark' => $report->remark,
                'option1' => $report->option1,
                'option2' => $report->option2,
                'option3' => $report->option3,
                'option4' => $report->option3,
                'status' => 'refunded',
                'rtype' => $report->rtype,
                'via' => $report->via,
                'trans_type' => ($report->trans_type == "credit") ? "debit" : "credit",
                'product' => $report->product,
                'amount' => $report->amount,
                'profit' => $report->profit,
                'charge' => $report->charge,
                'gst' => $report->gst,
                'tds' => $report->tds,
                'balance' => $user->mainwallet,
                'user_id' => $report->user_id,
                'credit_by' => $report->credit_by,
                'adminprofit' => $report->adminprofit
            ];
            Report::create($insert);

            $commissionReports = Report::where('rtype', 'commission')->where('txnid', $report->id)->get();
            foreach ($commissionReports as $report) {
                $user = User::where('id', $report->user_id)->first(['id', 'mainwallet']);

                if ($report->trans_type == "debit") {
                    User::where('id', $report->user_id)->increment('mainwallet', $report->amount - $report->profit);
                } else {
                    User::where('id', $report->user_id)->decrement('mainwallet', $report->amount - $report->profit);
                }

                $insert = [
                    'number' => $report->number,
                    'mobile' => $report->mobile,
                    'provider_id' => $report->provider_id,
                    'api_id' => $report->api_id,
                    'apitxnid' => $report->apitxnid,
                    'txnid' => $report->id,
                    'payid' => $report->payid,
                    'refno' => $report->refno,
                    'description' => "Transaction Reversed, amount refunded",
                    'remark' => $report->remark,
                    'option1' => $report->option1,
                    'option2' => $report->option2,
                    'option3' => $report->option3,
                    'option4' => $report->option3,
                    'status' => 'refunded',
                    'rtype' => $report->rtype,
                    'via' => $report->via,
                    'trans_type' => ($report->trans_type == "credit") ? "debit" : "credit",
                    'product' => $report->product,
                    'amount' => $report->amount,
                    'profit' => $report->profit,
                    'charge' => $report->charge,
                    'gst' => $report->gst,
                    'tds' => $report->tds,
                    'balance' => $user->mainwallet,
                    'user_id' => $report->user_id,
                    'credit_by' => $report->credit_by,
                    'adminprofit' => $report->adminprofit
                ];
                Report::create($insert);
            }
        }
    }

    public static function aepstransactionRefund($id, $product = "none")
    {
        $report = Aepsreport::where('id', $id)->first();
        $count = Aepsreport::where('user_id', $report->user_id)->whereIn('status', ['refunded', 'reversed'])->where('txnid', $report->id)->count();

        if ($count == 0) {
            $user = User::where('id', $report->user_id)->first();
            if ($report->type == "debit") {
                User::where('id', $report->user_id)->increment('aepsbalance', $report->amount + $report->charge - $report->profit);
            } elseif ($report->type == "credit") {
                User::where('id', $report->user_id)->decrement('aepsbalance', $report->amount + $report->charge - $report->profit);
            } else {
                return false;
            }


            $user = User::where('id', $report->user_id)->first();
            $insert = [

                'test' => $report->test,
                'user_idew' => $report->user_idew,


                'provider_id' => $report->provider_id,
                'charge' => $report->charge,
                'aadhar' => $report->aadhar,
                'mobile' => $report->mobile,
                'product' => 'cashdeposit',
                'txnid' => $report->txnid,
                'amount' => $report->amount,
                'user_id'    => $report->user_id,
                "balance" => $user->aepsbalance,
                'type'    => "credit",
                'api_id' => 6,
                'credited_by'  => $user->id,
                'status' => 'refunded',
                'rtype'      => 'main',
                'trans_type' => 'transaction',
                'bank'    => $report->bank,
                'aepstype' => $report->aepstype,
                'withdrawType' => 'CD'
            ];
            Aepsreport::create($insert);
        }
    }

    public static function getTds($amount)
    {
        return $amount * 5 / 100;
    }

    public static function callback($report, $product)
    {
        switch ($product) {
            case 'utipancard':
            case 'recharge':
                $report = Report::where('id', $report->id)->first();
                $apitxnid = $report->apitxnid;
                $refno = $report->refno;
                break;

            case 'utiid':
                $report = Utiid::where('id', $report->id)->first();
                $apitxnid = $report->vleid;
                $refno = $report->remark;
                break;
        }

        if ($report->status == "success") {
            $status = "success";
        } elseif ($report->status == "reversed") {
            $status = "failed";
        } else {
            $status = "unknown";
        }


        if ($status != "unknown") {
            $url = $report->user->callbackurl . "?txnid=" . $apitxnid . "&status=" . $report->status . "&refno=" . $refno;
            $result = \Myhelper::curl($url, "GET", "", [], "no", "", "");
            Callbackresponse::create([
                'url' => $url,
                'response' => ($result['response'] != '') ? $result['response'] : $result['error'],
                'status' => $result['code'],
                'product' => $product,
                'user_id' => $report->user_id,
                'transaction_id' => $report->id
            ]);
        }
    }

    public static function FormValidator($rules, $post)
    {
        $validator = \Validator::make($post->all(), array_reverse($rules));
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(array(
                'status' => 'ERR',
                'message' => $error
            ));
        } else {
            return "no";
        }
    }

    public static function allmemberwallet()
    {
        $query = User::where('kyc', 'verified')->where('status', 'active')->where('id', '!=', '1')->sum('mainwallet');
        $query = User::where('id', '!=', '1')->sum('mainwallet');
        return $query;
    }

    public static function allmemberaepswallet()
    {
        $query = User::where('kyc', 'verified')->where('status', 'active')->where('id', '!=', '1')->sum('mainwallet');
        $query = User::where('id', '!=', '1')->sum('aepsbalance');
        return $query;
    }
}
