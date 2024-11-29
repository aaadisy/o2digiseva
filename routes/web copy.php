<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\FingaepsController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\BillpayController;
use App\Http\Controllers\PancardController;
use App\Http\Controllers\DmtController;
use App\Http\Controllers\WtController;
use App\Http\Controllers\AepsController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\ResourceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any('performThreeWayReconciliation-cw', [UserController::class, 'performThreeWayReconciliation'])->name('performThreeWayReconciliation');
Route::any('performThreeWayReconciliation-matm', [UserController::class, 'performThreeWayReconciliationMATM'])->name('performThreeWayReconciliationMATM');
Route::any('performThreeWayReconciliation-ap', [UserController::class, 'performThreeWayReconciliationAP'])->name('performThreeWayReconciliationAP');
Route::any('performmatmstatusCheckCallback', [UserController::class, 'matmstatusCheckCallback'])->name('performmatmstatusCheckCallback');

Route::get('/', function () {
    return view('index');
})->middleware('guest')->name('front');
Route::get('/signin', function () {
    return view('welcome');
})->middleware('guest')->name('mylogin');
Route::get('/login_password_recover', function () {
    return view('login_password_recover');
})->middleware('guest')->name('login_password_recover');


Route::get('/delete-account', function () {
    return view('deleteAccount');
})->middleware('guest')->name('deleteAccount');

Route::any('sendOTP', [UserController::class, 'sendOTP'])->name('sendOTP');
Route::any('deleteAccount', [UserController::class, 'deleteAccount'])->name('deleteAccount');



Route::any('websitecallback', [UserController::class, 'websitecallback'])->middleware('guest')->name('websitecallback');
Route::any('paysprint-callback', [UserController::class, 'paysprintCallback'])->middleware('guest')->name('paysprintCallback');
Route::any('fing-callback', [UserController::class, 'fingCallBack'])->middleware('guest')->name('fingCallBack');

Route::any('validate-payment', [FundController::class, 'validate_payment'])->name('validate_payment');

Route::any('aeps', [UserController::class, 'paysprintCallback'])->middleware('guest')->name('aeps');

Route::any('roffer', [RechargeController::class, 'roffer']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('check', [UserController::class, 'login'])->name('authCheck');
    Route::get('logout', [UserController::class, 'logout'])->name('logout');
    Route::post('reset', [UserController::class, 'passwordReset'])->name('authReset');
});

Route::get('/download_certificate', function () {
    return view('download_certificate');
})->name('download_certificate');
Route::get('/updateChartData', [HomeController::class, 'fetchUpdatedData'])->name('updateChartData');
Route::get('/fetch-total-amount', [HomeController::class, 'fetchTotalAmount']);
Route::get('/dashboard', [HomeController::class, 'index'])->name('home');
Route::post('/delete-data', [HomeController::class, 'deleteData'])->name('deleteData');
Route::get('/delete-data-view', [HomeController::class, 'deletedataview'])->name('deletedataview');

Route::get('/statistics', [HomeController::class, 'statistics'])->name('statistics');

Route::any('/statsByDate', [HomeController::class, 'statsByDate'])->name('statsByDate');
Route::post('wallet/balance', [HomeController::class, 'getbalance'])->name('getbalance');
Route::get('isactive', [HomeController::class, 'isactive'])->name('isactive');
Route::post('currentlocation', [HomeController::class, 'currentlocation'])->name('currentlocation');
Route::get('setpermissions', [HomeController::class, 'setpermissions']);
Route::get('setscheme', [HomeController::class, 'setscheme']);
Route::get('getmyip', [HomeController::class, 'getmysendip']);
Route::get('balance', [HomeController::class, 'getbalance'])->name('getbalance');
Route::get('mydata', [HomeController::class, 'mydata']);
Route::any('digipay', [FundController::class, 'digipay'])->name('digipay');
Route::any('aeps_invoice', [StatementController::class, 'aeps_invoice'])->name('aeps_invoice');
Route::any('wallet_invoice', [StatementController::class, 'wallet_invoice'])->name('wallet_invoice');

Route::any('exportapilog', [StatementController::class, 'exportapilog'])->name('exportapilog');

Route::group(['prefix' => 'tools', 'middleware' => ['auth', 'company', 'checkrole:admin']], function () {
    Route::get('{type}', [RoleController::class, 'index'])->name('tools');
    Route::post('{type}/store', [RoleController::class, 'store'])->name('toolsstore');
    Route::post('setpermissions', [RoleController::class, 'assignPermissions'])->name('toolssetpermission');
    Route::any('get/permission/{id}', [RoleController::class, 'getpermissions'])->name('permissions');
    Route::any('getdefault/permission/{id}', [RoleController::class, 'getdefaultpermissions'])->name('defaultpermissions');
});

Route::group(['prefix' => 'statement', 'middleware' => ['auth', 'company']], function () {
    Route::get("export/{type}", [StatementController::class, 'export'])->name('export');
    Route::get('{type}/{id?}', [StatementController::class, 'index'])->name('statement');
    Route::post('fetch/{type}/{id?}/{returntype?}', [CommonController::class, 'fetchData']);
    Route::post('update', [CommonController::class, 'update'])->name('statementUpdate');
    Route::post('status', [CommonController::class, 'status'])->name('statementStatus');

    Route::post('fetch_lat/{type}/{id}', [StatementController::class, 'fetchData']);
    Route::post('update_lat', [ActionController::class, 'reportUpdate'])->name('reportUpdate');
    Route::post('status_lat', [ActionController::class, 'reportStatus'])->name('reportStatus');
});

Route::group(['prefix' => 'ifaeps', 'middleware' => ['checkpermission:aeps_service', 'auth', 'company']], function () {
    
    Route::get('{type}/{optional?}', [FingaepsController::class, 'index'])->name('ifaeps');

    Route::post('initiate', [FingaepsController::class, 'initiate'])->name('ifaepstransaction');
    Route::post('matm/initiate', [FingaepsController::class, 'matmInitiate'])->middleware(['ServiceStatus:matm'])->name('matmtransaction');
});

Route::group(['prefix' => 'member', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}/{action?}', [MemberController::class, 'index'])->name('member');
    Route::post('store', [MemberController::class, 'create'])->name('memberstore');
    Route::post('commission/update', [MemberController::class, 'commissionUpdate'])->name('commissionUpdate');
    Route::post('getcommission', [MemberController::class, 'getCommission'])->name('getMemberCommission');
    
    Route::post('getScheme', [MemberController::class, 'getScheme'])->name('getScheme');
Route::any('active/now/members', [MemberController::class, 'active'])->name('active');
Route::any('active/now/notloggedin', [MemberController::class, 'notloggedin'])->name('notloggedin');
Route::any('active/now/activememberlist', [MemberController::class, 'activememberlist'])->name('activememberlist');
Route::any('user/transaction/total', [MemberController::class, 'user_transaction'])->name('user_transaction');
});

Route::group(['prefix' => 'portal', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [PortalController::class, 'index'])->name('portal');
    Route::post('store', [PortalController::class, 'create'])->name('portalstore');
});

Route::group(['prefix' => 'fund', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}/{action?}', [FundController::class, 'index'])->name('fund');
    Route::post('transaction', [FundController::class, 'transaction'])->name('fundtransaction');
    Route::post('onlinefundtransaction', [FundController::class, 'onlinefundtransaction'])->name('onlinefundtransaction');
});

Route::group(['prefix' => 'profile', 'middleware' => ['auth']], function () {
    Route::get('/view/{id?}', [SettingController::class, 'index'])->name('profile');
    Route::post('update', [SettingController::class, 'profileUpdate'])->name('profileUpdate');
});

Route::group(['prefix' => 'setup', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [SetupController::class, 'index'])->name('setup');
    Route::post('update', [SetupController::class, 'update'])->name('setupupdate');
    Route::post('template/whatsapptemplateupdate', [SetupController::class, 'whatsapptemplateupdate'])->name('whatsapptemplateupdate');
    Route::any('template/whatsapptemplate', [SetupController::class, 'whatsapptemplate'])->name('whatsapptemplate');
    Route::any('video/tutorial', [SetupController::class, 'video'])->name('video');
    Route::post('video/tutorial', [SetupController::class, 'videoadd'])->name('videoadd');
    Route::any('video/delete/{id?}', [SetupController::class, 'videodelete'])->name('videodelete');
    Route::any('api/apiswitching', [SetupController::class, 'apiswitching'])->name('apiswitching');
    Route::any('api/saveapiswitching', [SetupController::class, 'saveapiswitching'])->name('saveapiswitching');
});
Route::any('bulkmsg', [SetupController::class, 'bulkmsg'])->name('bulkmsg');
Route::any('sendbulkmsg', [SetupController::class, 'sendbulkmsg'])->name('sendbulkmsg');
Route::any('unreadnotifications', [UserController::class, 'unreadnotifications'])->name('unreadnotifications');
Route::any('banner', [SetupController::class, 'banners'])->name('banners');
Route::any('notification', [SetupController::class, 'notification'])->name('notification');

Route::any('savenotification', [SetupController::class, 'savenotification'])->name('savenotification');
Route::any('savebanner', [SetupController::class, 'savebanner'])->name('savebanner');

Route::any('deletenotification/{var1}', [SetupController::class, 'deletenotification'])->name('deletenotification');
Route::any('deletebanner/{var1}', [SetupController::class, 'deletebanner'])->name('deletebanner');
Route::group(['prefix' => 'resources', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [ResourceController::class, 'index'])->name('resource');
    Route::post('update', [ResourceController::class, 'update'])->name('resourceupdate');
    Route::post('get/{type}/commission', [ResourceController::class, 'getCommission']);
});

Route::group(['prefix' => 'recharge', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [RechargeController::class, 'index'])->name('recharge');
    Route::post('payment', [RechargeController::class, 'payment'])->name('rechargepay');
});

Route::group(['prefix' => 'billpay', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [BillpayController::class, 'index'])->name('bill');
    Route::post('payment', [BillpayController::class, 'payment'])->name('billpay');
});

Route::group(['prefix' => 'pancard', 'middleware' => ['auth', 'company']], function () {
    Route::get('{type}', [PancardController::class, 'index'])->name('pancard');
    Route::post('payment', [PancardController::class, 'payment'])->name('pancardpay');
});

Route::group(['prefix' => 'dmt', 'middleware' => ['auth', 'company']], function () {
    Route::get('/', [DmtController::class, 'index'])->name('dmt1');
    Route::post('transaction', [DmtController::class, 'payment'])->name('dmt1pay');
});

Route::group(['prefix' => 'wt', 'middleware' => ['auth', 'company']], function () {
    Route::get('/', [WtController::class, 'index'])->name('Wt');
    Route::post('transaction', [WtController::class, 'payment'])->name('wtpay');
});

Route::group(['prefix' => 'aeps', 'middleware' => ['auth', 'company']], function () {
    Route::any('initiate', [AepsController::class, 'index'])->name('aeps');
    Route::any('aepstransaction', [AepsController::class, 'index'])->name('aeps');
    Route::post('aepstransaction', [AepsController::class, 'initiate'])->name('aepsinitiate');

    Route::any('registration', [AepsController::class, 'registration'])->name('aepskyc');
});

Route::group(['prefix' => 'checkaeps'], function () {
    Route::any('icici/initiate', [AepsController::class, 'iciciaepslog']);
    Route::any('icici/update', [AepsController::class, 'iciciaepslogupdate']);
});

Route::group(['prefix' => 'complaint', 'middleware' => ['auth', 'company']], function () {
    Route::get('/', [ComplaintController::class, 'index'])->name('complaint');
    Route::get('/aeps', [ComplaintController::class, 'aeps'])->name('aepscomplaint');
    Route::post('store', [ComplaintController::class, 'store'])->name('complaintstore');
});

Route::group(['prefix' => 'dispute', 'middleware' => ['auth', 'company']], function () {
    Route::get('/', [DisputeController::class, 'index'])->name('dispute');
    Route::get('/aeps', [DisputeController::class, 'aeps'])->name('aepsdispute');
    Route::post('store', [DisputeController::class, 'store'])->name('disputestore');
});
Route::get('artisancmd/{cmd}', function ($cmd) {
    Artisan::call($cmd);
});