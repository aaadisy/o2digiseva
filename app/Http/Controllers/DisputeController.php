<?php

namespace App\Http\Controllers;

use App\Model\Dispute;
use App\Model\Aeps_dispute;
use Illuminate\Http\Request;
use App\User;

use App\Model\Report;

class DisputeController extends Controller
{
    public function index()
    {
        return view('dispute');
    }
    public function aeps()
    {
        return view('aepsdispute');
    }

    public function store(Request $post)
    {
        
        $rules = array(
        );
        
        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        if($post->id == "new"){
            $post['user_id'] = \Auth::id();
        }else{
            $post['resolve_id'] = \Auth::id();
            
            if($post->status == 'approved')
            {
                $dispute = Dispute::where('id', $post->id)->first();
                $user = User::where('id', $dispute->user_id)->first();
                $report = Report::where('id', $dispute->report_id)->first();
                $profit = \Myhelper::getCommission($report->amount, $user->scheme_id, $report->provider_id, $user->role->slug);
                
                $debit = User::where('id', $dispute->user_id)->increment('mainwallet', $report->amount - $profit);
            }
        }
        if($post->product == 'aeps')
        {
            $action = Aeps_dispute::updateOrCreate(['id'=> $post->id], $post->all());
        }
        else
        {
            $action = Dispute::updateOrCreate(['id'=> $post->id], $post->all());
        }
        
        if ($action) {
            return response()->json(['status' => "success"], 200);
        }else{
            return response()->json(['status' => "Task Failed, please try again"], 200);
        }
    }
}
