<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Android\UserController;
use App\Http\Controllers\Android\FingaepsController;
use App\Http\Controllers\Android\TransactionController;
use App\Http\Controllers\Android\FundController;
use App\Http\Controllers\Android\RechargeController;
use App\Http\Controllers\Android\BillpayController;
use App\Http\Controllers\Android\DmtController;
use App\Http\Controllers\Api\AepsController;
use App\Http\Controllers\Api\CallbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('getbal', [UserController::class, 'getbalance']);
Route::any('getip', [UserController::class, 'getip']);
Route::any('getstate', [UserController::class, 'getState']);
Route::any('get-circle', [UserController::class, 'getCircle']);
Route::any('get-plan-operator', [UserController::class, 'getPlanOperator']);
Route::any('get-dth-plan-operator', [UserController::class, 'getDthPlanOperator']);
Route::any('get-dth-ci-plan-operator', [UserController::class, 'getDthCiOperator']);

Route::any('simple-plan', [UserController::class, 'simplePlan']);
Route::any('dth-customer-info', [UserController::class, 'dthCustomerInfo']);
Route::any('dth-plan', [UserController::class, 'dthPlan']);
Route::any('dth-plan-channel', [UserController::class, 'dthPlanChannel']);
Route::any('dth-roffer', [UserController::class, 'dthRoffer']);
Route::any('dth-roffer-operator', [UserController::class, 'dthRofferOperator']);

Route::any('getrole', [UserController::class, 'getrole']);
//Route::any('signup', 'Android\UserController@signupstore');

/*Aeps Api*/
Route::any('aeps/agent/ekyc', [AepsController::class, 'kyc']);
Route::any('aeps/agent/ekyc/status', [AepsController::class, 'kycstatus']);
Route::any('aeps/banklist', [AepsController::class, 'banklist']);
Route::any('aeps/transaction/{type}', [AepsController::class, 'transaction']);

/*Callback Api's*/

Route::group(['prefix' => 'callback/update'], function () {
    Route::any('dmt', [CallbackController::class, 'dmt']);
    Route::any('payout', [CallbackController::class, 'payout']);
});

/*Android App Apis*/
Route::any('android/auth', [UserController::class, 'login']);
Route::any('android/auth/reset/request', [UserController::class, 'passwordResetRequest']);
Route::any('android/auth/change/password', [UserController::class, 'changepassword']);

Route::any('android/changeProfile', [UserController::class, 'changeProfile']);
Route::any('android/auth/reset', [UserController::class, 'passwordReset']);
Route::any('android/getbalance', [UserController::class, 'getbalance']);
Route::any('android/appbanner', [UserController::class, 'appbanner']);
Route::any('android/rechargebanner', [UserController::class, 'rechargebanner']);
Route::any('android/kycupdate', [UserController::class, 'kycupdate']);
Route::any('android/logout', [UserController::class, 'logout']);
Route::any('android/userstatas', [UserController::class, 'userstatas']);
Route::any('android/mycertificate', [UserController::class, 'mycertificate']);
Route::any('android/support', [UserController::class, 'support']);
Route::any('android/offlinecashdeposit', [UserController::class, 'offlinecashdeposit']);
Route::any('android/wallettransfer', [UserController::class, 'wallettransfer']);
//Route::any('android/aeps/initiate', 'Android\UserController@aepsInitiate');

//matm
//matmstatusupdate

Route::any('android/matm/initiate', [FingaepsController::class, 'matminitiate']);
Route::any('android/matm/status', [FingaepsController::class, 'matmstatus']);
Route::any('android/matm/update', [FingaepsController::class, 'matmstatusupdate']);

//aeps
Route::any('android/aeps/initiate', [FingaepsController::class, 'initiate']);
Route::any('android/aeps/BE', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/MS', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/CW', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/M', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/M', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/AUO/AEPS', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/AUO/AP', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/check2fa', [FingaepsController::class, 'check2fa']);
Route::any('android/aeps/useronboard', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/useronboardresubmit', [FingaepsController::class, 'aepstransaction']);

Route::any('android/aeps/useronboardingstatus', [FingaepsController::class, 'useronboardingstatus']);
Route::any('android/aeps/useronboardingdetails', [FingaepsController::class, 'useronboardingdetails']);

Route::any('android/aeps/ekycsendotp', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/ekycvalidateotp', [FingaepsController::class, 'aepstransaction']);
Route::any('android/aeps/biometric', [FingaepsController::class, 'aepstransaction']);

Route::any('android/aeps/banklist', [UserController::class, 'aepsBank']);
Route::any('android/user/walletpinupdate', [UserController::class, 'walletpinupdate']);
Route::any('android/user/verifypin', [UserController::class, 'verifypin']);

Route::any('android/member/create', [UserController::class, 'membercreate']);
//new


Route::any('android/permissions', [UserController::class, 'permissions']);
Route::any('android/defaultpermissions', [UserController::class, 'defaultpermissions']);
Route::any('android/userPermission', [UserController::class, 'userPermission']);
Route::any('android/user/checkpermission', [UserController::class, 'checkpermission']);
Route::any('android/api/news', [UserController::class, 'news']);
Route::any('android/api/notifications', [UserController::class, 'notifications']);
Route::any('android/api/mycommision', [UserController::class, 'mycommision']);

Route::any('android/api/wallethistory', [UserController::class, 'wallethistory']);


//newend

Route::any('android/transaction', [TransactionController::class, 'transaction']);
Route::any('android/fundrequest', [FundController::class, 'transaction']);
Route::any('android/onlinefundrequest', [FundController::class, 'onlinefundtransaction']);
Route::any('android/fundrequest/request', [FundController::class, 'apitransaction']);
Route::any('android/fundrequest/bank', [FundController::class, 'apitransaction']);
Route::any('android/fundrequest/wallet', [FundController::class, 'apitransaction']);
Route::any('android/memberlist', [TransactionController::class, 'memberlist']);
Route::any('android/givetopup', [UserController::class, 'givetopup']);
Route::any('android/stocktransfer', [UserController::class, 'stocktransfer']);



Route::any('android/transactions/accountstatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/fundstatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/offlinecashdeposit', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/aepsstatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/aadharpaystatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/matmstatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/aepsfundrequest', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/awalletstatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/rechargestatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/billpaystatement', [TransactionController::class, 'transactionapi']);
Route::any('android/transactions/fundrequest', [TransactionController::class, 'transactionapi']);


/*Recharge Android Api*/

Route::any('android/recharge/roffer', [RechargeController::class, 'roffer']);
Route::any('android/recharge/rofferdth', [RechargeController::class, 'rofferdth']);

Route::any('android/recharge/planOffers', [RechargeController::class, 'offer']);

Route::any('android/recharge/providers', [RechargeController::class, 'providersList']);
Route::any('android/recharge/pay', [RechargeController::class, 'transaction']);
Route::any('android/recharge/status', [RechargeController::class, 'status']);

Route::any('android/recharge/recentrecharge', [RechargeController::class, 'recentrecharge']);
Route::any('android/recharge/rechargereport', [RechargeController::class, 'rechargereport']);

/*Bill Android Api*/

Route::any('android/billpay/providers', [BillpayController::class, 'providersList']);
Route::any('android/billpay/transaction', [BillpayController::class, 'transaction']);
Route::any('android/billpay/status', [BillpayController::class, 'status']);

//new
Route::any('android/billpay/recentbillpay', [RechargeController::class, 'recentbillpay']);
Route::any('android/billpay/billpayreport', [RechargeController::class, 'billpayreport']);
Route::any('android/insurancebillpay/recentinsurancebillpay', [RechargeController::class, 'recentinsurancebillpay']);
Route::any('android/insurancebillpay/insurancebillpayreport', [RechargeController::class, 'insurancebillpayreport']);

//mew

/*Dmt Android Api*/
Route::any('android/dmt/transaction', [DmtController::class, 'transaction']);

//new today

Route::any('android/transactions/wtbillpaystatement', [TransactionController::class, 'transactionapi']);

Route::any('android/api/rasiedispute', [UserController::class, 'rasiedispute']);
Route::any('android/transactions/disputes', [TransactionController::class, 'transactionapi']);

Route::any('android/transactions/complaints', [TransactionController::class, 'transactionapi']);
Route::any('android/appsetting', [UserController::class, 'getAppSetting']);