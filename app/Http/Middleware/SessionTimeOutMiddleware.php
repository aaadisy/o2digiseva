<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Session\Store;
use Session;

class SessionTimeOutMiddleware
{
  /**
  * Instance of Session Store
  * @var session
  */
  protected $session;
  /**
   * Time for user to remain active, set to 300 secs( 5 minutes )
   * @var timeout
  */
  protected $timeout = 600;
  public function __construct(Store $session){
   $this->session        = $session;
   $this->redirectUrl    = route("mylogin");
   $this->sessionLabel   = 'warning';
 }
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
  */
 public function handle($request, Closure $next)
 {
   if(!($this->session->has('lastActivityTime')))
   {
     $this->session->put('lastActivityTime', time());
   }
       else if(time() -$this->session->get('lastActivityTime') > $this->getTimeOut())
   {
     $this->session->forget('lastActivityTime');
     Auth::logout();
     return redirect($this->getRedirectUrl())->with([ $this->getSessionLabel() => 'You have been inactive for '. $this->timeout/60 .' minutes ago.']);
   }
   
   
   $this->session->put('lastActivityTime',time());
   return $next($request);
 }
 /**
 * Get timeout from laravel default's session lifetime, if it's not set/empty, set timeout to 15 minutes
 * @return int
 */
 private function getTimeOut()
 {
     
   return  $this->timeout;
 } 
 /**
 * Get redirect url from env file
 * @return string
 */
 private function getRedirectUrl()
 {
   return  (env('SESSION_TIMEOUT_REDIRECTURL')) ?: $this->redirectUrl;
 } 
 /**
   * Get Session label from env file
   * @return string
 */
 private function getSessionLabel()
 {
   return  (env('SESSION_LABEL')) ?: $this->sessionLabel;
 }
 
}