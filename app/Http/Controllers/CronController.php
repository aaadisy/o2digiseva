<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Report;
use App\User;

use App\Model\Provider;

class CronController extends Controller
{
    public function sessionClear()
  	{
	    $session = \DB::table('sessions')->where('last_activity' , '<', time()-900)->delete();
  	}

  	public function otpClear()
  	{
  		User::where('otpverify', '!=', 'yes')->update(['otpverify' => "yes", 'otpresend' => 0]);
  	}
}
