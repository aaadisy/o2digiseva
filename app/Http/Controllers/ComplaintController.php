<?php

namespace App\Http\Controllers;

use App\Model\Complaint;
use Illuminate\Http\Request;
use App\Model\Aeps_complaint;

class ComplaintController extends Controller
{
    public function index()
    {

        return view('complaint');
    }
    public function aeps()
    {
        
        return view('aepscomplaint');
    }

    public function store(Request $post)
    {
        $rules = array(
            'query'    => 'sometimes|required',
            'subject'    => 'sometimes|required'
        );
        
        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        if($post->id == "new"){
            $post['user_id'] = \Auth::id();
        }else{
            $post['resolve_id'] = \Auth::id();
        }
        if($post->product == 'aeps')
        {
            $action = Aeps_complaint::updateOrCreate(['id'=> $post->id], $post->all());
        }
        else
        {
            $action = Complaint::updateOrCreate(['id'=> $post->id], $post->all());
        }
        $action = Complaint::updateOrCreate(['id'=> $post->id], $post->all());
        if ($action) {
            return response()->json(['status' => "success"], 200);
        }else{
            return response()->json(['status' => "Task Failed, please try again"], 200);
        }
    }
}
