<?php

namespace App\Http\Middleware;

use Closure;

class ApiCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($post, Closure $next)
    {
        // if(!\Request::is('api/getip') && !\Request::is('api/getbal/*') && !\Request::is('api/getrole') && !\Request::is('api/signup') && !\Request::is('api/callback/*') && !\Request::is('api/android/*') && !\Request::is('api/merchant/callback/*')){
        //     if(!$post->has('token')){
        //         return response()->json(['statuscode'=>'ERR','status'=>'ERR','message'=> 'Invalid api token']);
        //     }

        //     $user = \App\Model\Apitoken::where('ip', $post->ip())->where('domain', $_SERVER['HTTP_HOST'])->where('token', $post->token)->first();
        //     if(!$user){
        //         return response()->json(['statuscode'=>'ERR','status'=>'ERR','message'=> 'Invalid Domain or Ip Address or Api Token']);
        //     }

        //     $myuser = \App\User::where('id', $user->user_id)->first(['company_id', 'id']);

        //     if(!$myuser->company->status){
        //         return response()->json(['statuscode'=>'ERR', 'status'=>'ERR','message'=> 'Service Down For Sometime']);
        //     }
        // }

        if (\Request::is('api/android/*')) {

            if ($post->has('apptoken')) {
                $myuser = \App\User::where('apptoken', $post->apptoken)->first(['company_id', 'id']);

                if (!$myuser) {
                    return response()->json(['statuscode' => 'ERR', 'status' => 'ERR', 'message' => 'User details not match']);
                }

                if (!$myuser->company->status) {
                    return response()->json(['statuscode' => 'ERR', 'status' => 'ERR', 'message' => 'Service Down For Sometime']);
                }

                $isactive = date('Y-m-d H:i:s');
                \App\User::where('id', $myuser->id)
                    ->update(['isactive' => $isactive]);
            }
        }

        return $next($post);
    }
}
