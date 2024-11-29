@extends('layouts.app')
@section('title', 'Dashboard')
@section('pagetitle', 'Dashboard')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">AePS Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$aeps['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$aeps['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$aeps['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-indigo-400 border-top border-top-indigo-300">
                                <li class="active">
                                    <a href="#aeps-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#aeps-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#aeps-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            
                           
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="aeps-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$aeps['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$aeps['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$aeps['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">AEPS Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('aeps','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('aeps','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('aeps','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                             
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Bill Payment Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$billpayment['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$billpayment['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$billpayment['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-primary-400 border-top border-top-primary-300">
                                <li class="active">
                                    <a href="#billpay-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#billpay-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#billpay-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="billpay-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$billpayment['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$billpayment['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$billpayment['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Electricity Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('billpay','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('billpay','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('billpay','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-teal-800">
                                <h5 class="panel-title">Money Transfer Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$money['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$money['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$money['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-teal-400 border-top border-top-teal-300">
                                <li class="active">
                                    <a href="#dmt-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#dmt-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#dmt-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->
                            

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="dmt-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$money['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$money['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$money['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Money Transfer Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('dmt','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('dmt','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('dmt','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-danger-800">
                                <h5 class="panel-title">Recharge Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$recharge['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$recharge['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$recharge['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-danger-400 border-top border-top-danger-300">
                                <li class="active">
                                    <a href="#recharge-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#recharge-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#recharge-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="recharge-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$recharge['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$recharge['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$recharge['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Recharge Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('recharge','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('recharge','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('rechage','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-slate-800">
                                <h5 class="panel-title">Uti Pancard Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$utipancard['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$utipancard['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$utipancard['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-slate-400 border-top border-top-slate-300">
                                <li class="active">
                                    <a href="#utipan-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#utipan-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#utipan-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="utipan-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$utipancard['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$utipancard['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$utipancard['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">UTI PAN Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('utipancard','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('utipancard','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('utipancard','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    
                    
                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-slate-800">
                                <h5 class="panel-title">Insurance Bill Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$insurance['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$insurance['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$insurance['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-slate-400 border-top border-top-slate-300">
                                <li class="active">
                                    <a href="#ins-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#ins-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#ins-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="ins-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$insurance['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$insurance['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$insurance['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            <div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Insurance Commission</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('insurance','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('insurance','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('insurance','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-6">
                        <div class="panel">
                            <div class="panel-heading text-center bg-slate-800">
                                <h5 class="panel-title">Cash Deposit Sales Statistics</h5>
                            </div>

                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$cashdeposit['today']}}</h5>
                                            <span>Today Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$cashdeposit['month']}}</h5>
                                            <span>Month Sale</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$cashdeposit['lastmonth']}}</h5>
                                            <span>Last Month Sale</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            <ul class="nav nav-lg nav-tabs nav-justified no-margin no-border-radius bg-slate-400 border-top border-top-slate-300">
                                <li class="active">
                                    <a href="#cash-tue" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        Today
                                    </a>
                                </li>

                                <li class="">
                                    <a href="#cash-mon" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="false">
                                        This Month
                                    </a>
                                </li>

                                <li>
                                    <a href="#cash-fri" class="text-size-small text-uppercase legitRipple" data-toggle="tab" aria-expanded="true">
                                        Last Month
                                    </a>
                                </li>
                            </ul>
                            <!-- /tabs -->

                            <!-- Tabs content -->
                            <div class="tab-content">
                                <div class="tab-pane fade has-padding active in" id="cash-tue">
                                    <ul class="media-list">
                                        <li class="media">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-check position-left text-success" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        
                                                        <h5 class="no-margin">
                                                            {{$cashdeposit['success']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Success</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-question3 position-left text-warning" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$cashdeposit['pending']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Pending</span>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="media-left">
                                                        <i class="icon-x position-left text-danger" style="font-size: 25px;padding: 10px 0px;"></i>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="no-margin">
                                                            {{$cashdeposit['failed']}}
                                                        </h5>
                                                        <span class="display-block text-muted" style="font-size:12px">Failed</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /tabs content -->
                            <!--<div class="panel-heading text-center bg-primary-800">
                                <h5 class="panel-title">Cash Deposit Commission</h5>
                            </div>

                            
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('cashdeposit','today') }}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('cashdeposit','month') }}</h5>
                                            <span>Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{ \Myhelper::total_commision('cashdeposit','lastmonth') }}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                        
                    </div>
                    
                    <div class="col-md-6">
                 @if (in_array(Auth::user()->role->slug, ['admin']))
                <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading text-center bg-success-800">
                                <h5 class="panel-title">Admin Commission Statistics</h5>
                            </div>
                            @php
                            
                            $today = \App\Model\Report::whereDate('created_at', date('Y-m-d'))->sum('adminprofit');
                            
                            $thismonth = \App\Model\Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('adminprofit');
                            
                            $lastmonth = \App\Model\Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->sum('adminprofit');
                            
                            @endphp
                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$today}}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$thismonth}}</h5>
                                            <span>This Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$lastmonth}}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            
                            <!-- /tabs content -->
                        </div>
                    </div>

                   
                </div>
            </div>
        </div>
        
                @elseif (in_array(Auth::user()->role->slug, ['whitelable']))
                <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading text-center bg-success-800">
                                <h5 class="panel-title"> Commission Statistics</h5>
                            </div>
                            @php
                            
                            $today = \App\Model\Report::whereDate('created_at', date('Y-m-d'))->sum('wprofit');
                            
                            $thismonth = \App\Model\Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('wprofit');
                            
                            $lastmonth = \App\Model\Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->sum('wprofit');
                            
                            @endphp
                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$today}}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$thismonth}}</h5>
                                            <span>This Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$lastmonth}}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            
                            <!-- /tabs content -->
                        </div>
                    </div>

                   
                </div>
            </div>
        </div>
                @elseif (in_array(Auth::user()->role->slug, ['md']))
                <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading text-center bg-success-800">
                                <h5 class="panel-title"> Commission Statistics</h5>
                            </div>
                            @php
                            
                            $today = \App\Model\Report::whereDate('created_at', date('Y-m-d'))->sum('mdprofit');
                            
                            $thismonth = \App\Model\Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('mdprofit');
                            
                            $lastmonth = \App\Model\Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->sum('mdprofit');
                            
                            @endphp
                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$today}}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$thismonth}}</h5>
                                            <span>This Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$lastmonth}}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            
                            <!-- /tabs content -->
                        </div>
                    </div>

                   
                </div>
            </div>
        </div>
                @elseif (in_array(Auth::user()->role->slug, ['distributor']))
               
                
                <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading text-center bg-success-800">
                                <h5 class="panel-title"> Commission Statistics</h5>
                            </div>
                            @php
                            
                            $today = \App\Model\Report::whereDate('created_at', date('Y-m-d'))->sum('profit');
                            
                            $thismonth = \App\Model\Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('profit');
                            
                            $lastmonth = \App\Model\Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->sum('profit');
                            
                            @endphp
                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$today}}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$thismonth}}</h5>
                                            <span>This Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$lastmonth}}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            
                            <!-- /tabs content -->
                        </div>
                    </div>

                   
                </div>
            </div>
        </div>
                @else
                <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-heading text-center bg-success-800">
                                <h5 class="panel-title"> Commission Statistics</h5>
                            </div>
                            @php
                            
                            $today = \App\Model\Report::whereDate('created_at', date('Y-m-d'))->sum('disprofit');
                            
                            $thismonth = \App\Model\Report::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('disprofit');
                            
                            $lastmonth = \App\Model\Report::whereMonth('created_at', date('m', strtotime("-1 months")))->whereYear('created_at', date('Y'))->sum('disprofit');
                            
                            @endphp
                            <!-- Numbers -->
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$today}}</h5>
                                            <span>Today</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$thismonth}}</h5>
                                            <span>This Month</span>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <h5 class="text-semibold no-margin"><i class="fa fa-inr position-left text-slate-600"></i>{{$lastmonth}}</h5>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /numbers -->

                            <!-- Tabs -->
                            
                            <!-- /tabs content -->
                        </div>
                    </div>

                   
                </div>
            </div>
        </div>
                @endif
            </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="content-group">
                    <div class="panel-body bg-teal-300 border-radius-top text-center" style="padding: 10px">
                        <div class="content-group-sm">
                            <img src="{{asset('assets/helpdesk.png')}}" class="img-responsive mb-10" style="margin: auto; width: 200px">
                        </div>

                        <a href="#" class="display-inline-block content-group-sm mb-5">
                            <img src="{{asset('assets/support.png')}}" class="img-circle img-responsive" alt="" style="width: 80px; height: 80px;">
                        </a>
                        <span class="display-block"><b>Timing - 10 AM to 7 PM</b></span>
                    </div>

                    <div class="panel panel-body no-border-top no-border-radius-top text-center" style="padding: 10px">
                        <div class="form-group mt-5 mb-5">
                            <h5 class="text-semibold"><i class="fa fa-phone"></i> Call Us</h5>
                            <span>{{$mydata['supportnumber']}}</span>
                        </div>

                        <div class="form-group  mb-5">
                            <h5 class="text-semibold"><i class="fa fa-envelope"></i>  Email Us:</h5>
                            <span>{{$mydata['supportemail']}}</span>
                        </div>
                    </div>
                </div>

                @if (in_array(Auth::user()->role->slug, ['whitelable', 'md', 'distributor', 'admin']))
                    <div class="panel">
                        <div class="panel-heading bg-teal-700">
                            <h6 class="panel-title">User Panel<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                            <div class="heading-elements">
                                <ul class="icons-list">
                                    <li><a data-action="collapse"></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="panel-body p-0">
                            <table class="table table-bordered" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member Type</th>
                                        <th>Count</th>
                                        @if(Myhelper::hasRole(['admin', 'whitelable', 'md', 'distributor']))
                                        <th>Stock</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    @if (in_array(Auth::user()->role->slug, ['admin']))
                                        <tr>
                                            <td><i class="icon-users2 icon-2x display-inline-block text-warning"></i></td>
                                            <td>Channel partner</td>
                                            <td>{{$whitelable}}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                    @if (in_array(Auth::user()->role->slug, ['admin', 'whitelable']))
                                        <tr>
                                            <td><i class="icon-users2 icon-2x display-inline-block text-slate"></i></td>
                                            <td>Master Distributor</td>
                                            <td>{{$md}}</td>
                                            <td>{{Auth::user()->mstock}}</td>
                                        </tr>
                                    @endif
                                    @if (in_array(Auth::user()->role->slug, ['admin', 'whitelable', 'md']))
                                        <tr>
                                            <td><i class="icon-users2 icon-2x display-inline-block text-teal"></i></td>
                                            <td>Distributor</td>
                                            <td>{{$distributor}}</td>
                                            <td>{{Auth::user()->dstock}}</td>
                                        </tr>
                                    @endif
                                    @if (in_array(Auth::user()->role->slug, ['admin', 'whitelable', 'md', 'distributor']))
                                        <tr>
                                            <td><i class="icon-users2 icon-2x display-inline-block text-primary"></i></td>
                                            <td>Retailer</td>
                                            <td>{{$retailer}}</td>
                                            <td>{{Auth::user()->rstock}}</td>
                                        </tr>
                                    @endif
                                    @if (in_array(Auth::user()->role->slug, ['admin']))
                                        <tr>
                                            <td><i class="icon-users2 icon-2x display-inline-block text-info"></i></td>
                                            <td>Other User</td>
                                            <td>{{$other}}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="panel">
                    <div class="panel-heading bg-primary-800">
                        <h6 class="panel-title">Balances<a class="heading-elements-toggle"><i class="icon-more"></i></a></h6>
                        <div class="heading-elements">
                            <ul class="icons-list">
                                <li><a data-action="collapse"></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body p-0">
                        <table class="table table-bordered" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Wallet Type</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fa fa-inr icon-2x display-inline-block text-primary" style="font-size: 24px"></i></td>
                                    <td>Utility Balance</td>
                                    <td class="mainwallet"></td>
                                </tr>

                                <tr>
                                    <td><i class="fa fa-inr icon-2x display-inline-block text-danger" style="font-size: 24px"></i></td>
                                    <td>AePS Balance</td>
                                    <td class="aepsbalance"></td>
                                </tr>
                                
                                <tr>
                                    <td><i class="fa fa-inr icon-2x display-inline-block text-danger" style="font-size: 24px"></i></td>
                                    <td>AePS Locked Amount</td>
                                    <td class="aepsbalancelocked"></td>
                                </tr>
                                
                                <tr>
                                    <td><i class="fa fa-inr icon-2x display-inline-block text-danger" style="font-size: 24px"></i></td>
                                    <td>Main Wallet Locked Amount</td>
                                    <td class="mainbalancelocked"></td>
                                </tr>
                                
                                @if (in_array(Auth::user()->role->slug, ['admin']))
                                    <tr>
                                        <td><i class="fa fa-inr icon-2x display-inline-block text-teal" style="font-size: 24px"></i></td>
                                        <td>Downline Balance</td>
                                        <td class="downlinebalance"></td>
                                    </tr>
                                @endif
                                @if (in_array(Auth::user()->role->slug, ['admin']))
                                    <tr>
                                        <td><i class="fa fa-inr icon-2x display-inline-block text-slate" style="font-size: 24px"></i></td>
                                        <td>Secure Api Balance</td>
                                        <td class="apibalance"></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (Myhelper::hasNotRole('admin'))
        @if (Auth::user()->kyc != "verified")
            <div id="kycModal" class="modal fade" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h6 class="modal-title">Complete your profile with kyc</h6>
                        </div>
                        @if (Auth::user()->kyc == "rejected")
                            <div class="alert bg-danger alert-styled-left">
                                <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
                                <span class="text-semibold">Kyc Rejected!</span> {{ Auth::user()->remark }}</a>.
                            </div>
                            @endif
                          
                        @if (Auth::user()->kyc != "pending")  
                       <center>
                           <b>KYC Status: {{ ucwords(Auth::user()->kyc) }}</b>
                           @if(Auth::user()->remark != NULL)
                           </br>
                           
                           <span class="text-semibold"></span><b>KYC Remark: </b> {{ Auth::user()->remark }}
                           @endif
                       </center> 
                        @endif
                        <form id="kycForm" action="{{route('profileUpdate')}}" method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="{{Auth::id()}}">
                                <input type="hidden" name="type" value="kycdata">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control" rows="2" required="" placeholder="Enter Value">{{ Auth::user()->address}}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>State</label>
                                        <select name="state" class="form-control select" required="">
                                            <option value="">Select State</option>
                                            @foreach ($state as $state)
                                                <option value="{{$state->state}}" {{ (Auth::user()->state == $state->state)? 'selected=""': '' }}>{{$state->state}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" required="" placeholder="Enter Value" value="{{Auth::user()->city}}">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Pincode</label>
                                        <input type="number" name="pincode" value="{{ Auth::user()->pincode}}" class="form-control" value="" required="" maxlength="6" minlength="6" placeholder="Enter Value">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Shop Name</label>
                                        <input type="text" name="shopname" value="{{ Auth::user()->shopname}}"  class="form-control" value="" required="" placeholder="Enter Value">
                                    </div>
        
                                    <div class="form-group col-md-4">
                                        <label>Pancard Number</label>
                                        <input type="text" name="pancard" value="{{ Auth::user()->pancard}}"  class="form-control" value="" required="" placeholder="Enter Value">
                                    </div>
        
                                    <div class="form-group col-md-4">
                                        <label>Adhaarcard Number</label>
                                        <input type="text" name="aadharcard" value="{{ Auth::user()->aadharcard}}"  class="form-control" value="" required="" placeholder="Enter Value" maxlength="12" minlength="12">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Pancard Pic</label>
                                        @if(Auth::user()->pancardpic != NULL)
                                        <img style="max-width:175px;" src="{{ ('public/kyc/').Auth::user()->pancardpic }} " >
                                        @endif
                                        <input type="file" name="pancardpics" class="form-control" value="" placeholder="Enter Value" required="">
                                    </div>
        
                                    <div class="form-group col-md-6">
                                        <label>Adhaarcard Pic</label>
                                        @if(Auth::user()->aadharcardpic != NULL)
                                        <img  style="max-width:175px;" src="{{ ('public/kyc/').Auth::user()->aadharcardpic }} " >
                                        @endif
                                        <input type="file" name="aadharcardpics" class="form-control" value="" placeholder="Enter Value" required="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Video Upload</label>

                                        <input type="file" name="videos" class="form-control" value="" placeholder="Enter Value" required="">
                                    </div>
        
                                    <div class="form-group col-md-6">
                                                                                @if(Auth::user()->video != NULL)
                                       
                                        <video width="100%" controls>
                              <source src="{{ ('public/kyc/').Auth::user()->video }} " type="video/mp4">
                              Your browser does not support HTML video.
                            </video>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Complete Profile</button>
                            </div>
                        </form>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        @endif

        @if (Auth::user()->resetpwd == "default")
            <div id="pwdModal" class="modal fade" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h6 class="modal-title">Change Password </h6>
                        </div>
                        <form id="passwordForm" action="{{route('profileUpdate')}}" method="post">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="{{Auth::id()}}">
                                <input type="hidden" name="actiontype" value="password">
                                {{ csrf_field() }}
                                @if (Myhelper::can('password_reset'))
                                    <div class="row">
                                        <div class="form-group col-md-6  ">
                                            <label>Old Password</label>
                                            <input type="password" name="oldpassword" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                        <div class="form-group col-md-6  ">
                                            <label>New Password</label>
                                            <input type="password" name="password" id="password" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6  ">
                                            <label>Confirmed Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Change Password</button>
                            </div>
                        </form>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        @endif
    @endif

    <div id="noticeModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Necessary Notice ( आवश्यक सूचना )</h4>
                </div>
                <div class="modal-body">
                     {!! nl2br($mydata['notice']) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
@endsection

@push('script')
<script type="text/javascript" src="{{asset('')}}assets/js/plugins/forms/selects/select2.min.js"></script>
<script>
    $(document).ready(function(){
        $('select').select2();

        @if (Myhelper::hasNotRole('admin'))
            @if (Auth::user()->kyc != "verified")
                $('#kycModal').modal();
            @endif
        @endif

        @if (Myhelper::hasNotRole('admin') && Auth::user()->resetpwd == "default")
            $('#pwdModal').modal();
        @endif

        @if ($mydata['notice'] != null && $mydata['notice'] != '')
            $('#noticeModal').modal();
        @endif

        $( "#kycForm" ).validate({
            rules: {
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                pincode: {
                    required: true,
                    minlength: 6,
                    number : true,
                    maxlength: 6
                },
                address: {
                    required: true,
                },
                aadharcard: {
                    required: true,
                    minlength: 12,
                    number : true,
                    maxlength: 12
                },
                pancard: {
                    required: true,
                },
                shopname: {
                    required: true,
                },
                pancardpics: {
                    required: true,
                },
                aadharcardpics: {
                    required: true,
                }
            },
            messages: {
                state: {
                    required: "Please select state",
                },
                city: {
                    required: "Please enter city",
                },
                pincode: {
                    required: "Please enter pincode",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 6 digit",
                    maxlength: "Your mobile number must be 6 digit"
                },
                address: {
                    required: "Please enter address",
                },
                aadharcard: {
                    required: "Please enter aadharcard",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 12 digit",
                    maxlength: "Your mobile number must be 12 digit"
                },
                pancard: {
                    required: "Please enter pancard",
                },
                shopname: {
                    required: "Please enter shop name",
                },
                pancardpics: {
                    required: "Please upload pancard pic",
                },
                aadharcardpics: {
                    required: "Please upload aadharcard pic",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $( "#kycForm" );
                form.find('span.text-danger').remove();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form[0].reset();
                            $('select').val('');
                            $('select').trigger('change');
                            notify("Profile Successfully Updated, wait for kyc approval" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#passwordForm" ).validate({
            rules: {
                @if (!Myhelper::can('member_password_reset'))
                oldpassword: {
                    required: true,
                    minlength: 6,
                },
                password_confirmation: {
                    required: true,
                    minlength: 8,
                    equalTo : "#password"
                },
                @endif
                password: {
                    required: true,
                    minlength: 8
                }
            },
            messages: {
                @if (!Myhelper::can('member_password_reset'))
                oldpassword: {
                    required: "Please enter old password",
                    minlength: "Your password lenght should be atleast 6 character",
                },
                password_confirmation: {
                    required: "Please enter confirmed password",
                    minlength: "Your password lenght should be atleast 8 character",
                    equalTo : "New password and confirmed password should be equal"
                },
                @endif
                password: {
                    required: "Please enter new password",
                    minlength: "Your password lenght should be atleast 8 character"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#passwordForm');
                form.find('span.text-danger').remove();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form[0].reset();
                            form.closest('.modal').modal('hide');
                            notify("Password Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.modal-body'));
                    }
                });
            }
        });
    });
</script>
@endpush
