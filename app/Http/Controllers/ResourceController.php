<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Scheme;
use App\Model\Company;
use App\Model\Provider;
use App\Model\Commission;
use App\Model\Companydata;
use App\User;

class ResourceController extends Controller
{
    public function index($type)
    {
        switch ($type) {
            case 'scheme':
                $permission = "scheme_manager";
                $data['mobileOperator'] = Provider::where('type', 'mobile')->where('status', "1")->get();
                $data['dthOperator'] = Provider::where('type', 'dth')->where('status', "1")->get();
                $data['ebillOperator'] = Provider::where('type', 'electricity')->where('status', "1")->get();
                $data['insuranceOperator'] = Provider::where('type', 'insurance')->where('status', "1")->get();
                $data['cashdepositOperator'] = Provider::where('type', 'cashdeposit')->where('status', "1")->get();
                $data['pancardOperator'] = Provider::where('type', 'pancard')->where('status', "1")->get();
                $data['dmtOperator'] = Provider::where('type', 'dmt')->where('status', "1")->get();
                $data['aepsOperator'] = Provider::where('type', 'aeps')->where('status', "1")->get();
                $data['matmOperator'] = Provider::where('type', 'matm')->where('status', "1")->get();
                $data['msOperator'] = Provider::where('type', 'ms')->where('status', "1")->get();
                $data['aadharpayOperator'] = Provider::where('type', 'aadharpay')->where('status', "1")->get();
                break;

            case 'company':
                $permission = "company_manager";
                break;

            case 'companyprofile':
                $permission = "change_company_profile";
                $data['company'] = Company::where('id', \Auth::user()->company_id)->first();
                $data['companydata'] = Companydata::where('company_id', \Auth::user()->company_id)->first();
                break;
            
            case 'commission':
                $permission = "view_commission";
                $product = ['mobile','dth','electricity','dmt','pancard','aeps','ms'];
                foreach ($product as $key) {
                    $data['commission'][$key] = Commission::where('scheme_id', \Auth::user()->scheme_id)->whereHas('provider', function ($q) use($key){
                        $q->where('type' , $key);
                    })->get();
                }
                break;
            
            default:
                # code...
                break;
        }

        if ($type != "scheme" && !\Myhelper::can($permission)) {
            abort(403);
        }elseif($type == "scheme"){
            if(\Myhelper::hasRole('retailer')){
                abort(403);
            }
        }
        $data['type'] = $type;

        return view("resource.".$type)->with($data);
    }

    public function update(Request $post)
    {
        switch ($post->actiontype) {
            case 'scheme':
            case 'commission':
                $permission = "scheme_manager";
                break;
            
            case 'company':
                $permission = ["company_manager", "change_company_profile"];
                break;

            case 'companydata':
                $permission = "change_company_profile";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        if(in_array($post->actiontype, ['scheme', 'commission']) && \Myhelper::hasRole('retailer')){
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'scheme':
                $rules = array(
                    'name'    => 'sometimes|required' 
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $post['user_id'] = \Auth::id();
                $action = Scheme::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'company':
                $rules = array(
                    'companyname'    => 'sometimes|required'
                );

                if($post->file('logos')){
                    $rules['logos'] = 'sometimes|required|mimes:jpg,JPG,jpeg,png|max:500';
                }
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                if($post->id != 'new'){
                    $company = Company::find($post->id);
                }
                
                if($post->hasFile('logos')){
                    try {
                        unlink(public_path('logos/').$company->logo);
                    } catch (\Exception $e) {
                    }
                    $filename ='logo'.$post->id.".".$post->file('logos')->guessExtension();
                    $post->file('logos')->move(public_path('logos/'), $filename);
                    $post['logo'] = $filename;
                }

                $action = Company::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'companydata':
                $rules = array(
                    'company_id'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Companydata::updateOrCreate(['company_id'=> $post->company_id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            case 'commission':
                $rules = array(
                    'scheme_id'    => 'sometimes|required|numeric' 
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                foreach ($post->slab as $key => $value) {
                    $update[$value] = Commission::updateOrCreate([
                        'scheme_id' => $post->scheme_id,
                        'slab'      => $post->slab[$key]
                    ],[
                        'scheme_id' => $post->scheme_id,
                        'slab'      => $post->slab[$key],
                        'type'      => $post->type[$key],
                        'admin'      => $post->admin[$key],
                        'whitelable'=> $post->whitelable[$key],
                        'md'        => $post->md[$key],
                        'distributor'  => $post->distributor[$key],
                        'retailer'     => $post->retailer[$key],
                    ]);
                }
                return response()->json(['status'=>$update], 200);
                break;
            
            default:
                # code...
                break;
        }
    }

    public function getCommission(Request $post , $type)
    {
        return Commission::where('scheme_id', $post->scheme_id)->get(['slab', 'type','admin', 'whitelable', 'md', 'distributor', 'retailer'])->toJson();
    }
}
