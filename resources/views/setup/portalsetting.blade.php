@extends('layouts.app')
@section('title', 'Portal Settings')
@section('pagetitle',  'Portal Settings')

@section('content')
<div class="content">
    <div class="row">
    <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="morphossl">
                <input type="hidden" name="name" value="morphossl">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Morpho SSL</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Morpho SSL</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($morphossl->value) && $morphossl->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($morphossl->value) && $morphossl->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="matm_service">
                <input type="hidden" name="name" value="MATM Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">MATM Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>MATM Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($matm_service->value) && $matm_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($matm_service->value) && $matm_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="aeps_service">
                <input type="hidden" name="name" value="AEPS Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">AEPS Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>AEPS Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($aeps_service->value) && $aeps_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($aeps_service->value) && $aeps_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="payout_service">
                <input type="hidden" name="name" value="PAY OUT Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">PAY OUT Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>PAY OUT Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($payout_service->value) && $payout_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($payout_service->value) && $payout_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="recharge_service">
                <input type="hidden" name="name" value="RECHARGE Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">RECHARGE Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>RECHARGE Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($recharge_service->value) && $recharge_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($recharge_service->value) && $recharge_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="bbps_service">
                <input type="hidden" name="name" value="BBPS Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">BBPS Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>BBPS Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($bbps_service->value) && $bbps_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($bbps_service->value) && $bbps_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="pancard_service">
                <input type="hidden" name="name" value="PAN CARD Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">PAN CARD Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>PAN CARD Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($pancard_service->value) && $pancard_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($pancard_service->value) && $pancard_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="moneytransfer_service">
                <input type="hidden" name="name" value="MONEY TRANSFER Service">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">MONEY TRANSFER Service</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>MONEY TRANSFER Service</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="on" {{(isset($moneytransfer_service->value) && $moneytransfer_service->value == "on") ? "selected=''" : ''}}>On</option>
                                <option value="off" {{(isset($moneytransfer_service->value) && $moneytransfer_service->value == "off") ? "selected=''" : ''}}>Off</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="settlementtype">
                <input type="hidden" name="name" value="Wallet Settlement Type">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Wallet Settlement Type</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Settlement Type</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="auto" {{(isset($settlementtype->value) && $settlementtype->value == "auto") ? "selected=''" : ''}}>Auto</option>
                                <option value="mannual" {{(isset($settlementtype->value) && $settlementtype->value == "mannual") ? "selected=''" : ''}}>Mannual</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="low_balance_amount">
                <input type="hidden" name="name" value="Low Balance Amount">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Low Balance Amount</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Charge</label>
                            <input type="number" name="value" value="{{$low_balance_amount->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="cd_surcahrge_type">
                <input type="hidden" name="name" value="Cash Deposit Surcharge Type">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Cash Deposit Surcharge Type</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Surcharge Type</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="fixed" {{(isset($cd_surcahrge_type->value) && $cd_surcahrge_type->value == "fixed") ? "selected=''" : ''}}>Fixed</option>
                                <option value="percentage" {{(isset($cd_surcahrge_type->value) && $cd_surcahrge_type->value == "percentage") ? "selected=''" : ''}}>Percentage</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="cd_surcahrge_value">
                <input type="hidden" name="name" value="Cash Deposit Surcharge Value">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Cash Deposit Surcharge Value</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Surcharge Value</label>
                            <input type="number" name="value" value="{{$cd_surcahrge_value->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="wt_surcahrge_type">
                <input type="hidden" name="name" value="Wallet Transfer Surcharge Type">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Wallet Transfer Surcharge Type</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Surcharge Type</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="fixed" {{(isset($wt_surcahrge_type->value) && $wt_surcahrge_type->value == "fixed") ? "selected=''" : ''}}>Fixed</option>
                                <option value="percentage" {{(isset($wt_surcahrge_type->value) && $wt_surcahrge_type->value == "percentage") ? "selected=''" : ''}}>Percentage</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="wt_surcahrge_value">
                <input type="hidden" name="name" value="Wallet Transfer Surcharge Value">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Wallet Transfer Surcharge Value</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Surcharge Value</label>
                            <input type="number" name="value" value="{{$wt_surcahrge_value->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="banksettlementtype">
                <input type="hidden" name="name" value="Wallet Settlement Type">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Bank Settlement Type</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Settlement Type</label>
                            <select name="value" required="" class="form-control select">
                                <option value="">Select Type</option>
                                <option value="auto" {{(isset($banksettlementtype->value) && $banksettlementtype->value == "auto") ? "selected=''" : ''}}>Auto</option>
                                <option value="mannual" {{(isset($banksettlementtype->value) && $banksettlementtype->value == "mannual") ? "selected=''" : ''}}>Mannual</option>
                            </select>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="settlementcharge">
                <input type="hidden" name="name" value="Bank Settlement Charge">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Bank Settlement Charge</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Charge</label>
                            <input type="number" name="value" value="{{$settlementcharge->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
                <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="settlementcharge1k">
                <input type="hidden" name="name" value="Bank Settlement Charge">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Bank Settlement Charge (<= 1000)</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Charge</label>
                            <input type="number" name="value" value="{{$settlementcharge1k->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="settlementcharge25k">
                <input type="hidden" name="name" value="Bank Settlement Charge">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Bank Settlement Charge ( 1001 To 25000)</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Charge</label>
                            <input type="number" name="value" value="{{$settlementcharge25k->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="settlementcharge2l">
                <input type="hidden" name="name" value="Bank Settlement Charge">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Bank Settlement Charge (25001 To 2Lac)</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Charge</label>
                            <input type="number" name="value" value="{{$settlementcharge2l->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="otplogin">
        <input type="hidden" name="name" value="Login required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Login Google Authenticator Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Login Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($otplogin->value) && $otplogin->value == "yes") ? "selected=''" : ''}}>With Google Authenticator</option>
                        <option value="no" {{(isset($otplogin->value) && $otplogin->value == "no") ? "selected=''" : ''}}>Without Google Authenticator</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="wotplogin">
        <input type="hidden" name="name" value="Login required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Login WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Login Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($wotplogin->value) && $wotplogin->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($wotplogin->value) && $wotplogin->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="walletpinupdateotp">
        <input type="hidden" name="name" value="Login required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Wallet Pin Update WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Wallet Pin Update Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($walletpinupdateotp->value) && $walletpinupdateotp->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($walletpinupdateotp->value) && $walletpinupdateotp->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>
<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="cashdeposite">
        <input type="hidden" name="name" value="Cash Deposite required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Cash Deposite Update WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Cash Deposite Update Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($cashdeposite->value) && $cashdeposite->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($cashdeposite->value) && $cashdeposite->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>
<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="wt">
        <input type="hidden" name="name" value="Wallet Transfer required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Wallet Transfer Update WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Wallet Transfer Update Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($wt->value) && $wt->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($wt->value) && $wt->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="passwordupdateotp">
        <input type="hidden" name="name" value="Login required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Password Update WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Pwd Update Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($passwordupdateotp->value) && $passwordupdateotp->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($passwordupdateotp->value) && $passwordupdateotp->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="manualpayoutotp">
        <input type="hidden" name="name" value="Login required otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Manual Payout WhatsApp OTP Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Manual Payout OTP Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($manualpayoutotp->value) && $manualpayoutotp->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($manualpayoutotp->value) && $manualpayoutotp->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

<div class="col-sm-4">
    <form class="actionForm" action="{{route('setupupdate')}}" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="actiontype" value="portalsetting">
        <input type="hidden" name="code" value="walletpinupdateotp">
        <input type="hidden" name="name" value="Wallet Pin Change otp">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h5 class="panel-title">Wallet Pin Change otp Required</h5>
            </div>
            <div class="panel-body p-b-0">
                <div class="form-group">
                    <label>Wallet Pin Change OTP Type</label>
                    <select name="value" required="" class="form-control select">
                        <option value="">Select Type</option>
                        <option value="yes" {{(isset($walletpinupdateotp->value) && $walletpinupdateotp->value == "yes") ? "selected=''" : ''}}>With WhatsApp OTP</option>
                        <option value="no" {{(isset($walletpinupdateotp->value) && $walletpinupdateotp->value == "no") ? "selected=''" : ''}}>Without Whatsapp OTP</option>
                    </select>
                </div>
            </div>
            <div class="panel-footer">
                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
            </div>
        </div>
    </form>
</div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="otpsendmailid">
                <input type="hidden" name="name" value="Sending mail id for otp">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Sending mail id for otp</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Mail Id</label>
                            <input type="text" name="value" value="{{$otpsendmailid->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="otpsendmailname">
                <input type="hidden" name="name" value="Sending mailer name id for otp">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Sending mailer name id for otp</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Mailer Name</label>
                            <input type="text" name="value" value="{{$otpsendmailname->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="transactioncode">
                <input type="hidden" name="name" value="Transaction Id Code">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Transaction Id Code</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" name="value" value="{{$transactioncode->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="mainlocked">
                <input type="hidden" name="name" value="Main Wallet Locked Amount">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Main Wallet Locked Amount</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Locked Amount</label>
                            <input type="text" name="value" value="{{$mainlocked->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="aepslocked">
                <input type="hidden" name="name" value="Aeps Wallet Locked Amount">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Aeps Wallet Locked Amount</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Locked Amount</label>
                            <input type="text" name="value" value="{{$aepslocked->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="master_2fa_cost">
                <input type="hidden" name="name" value="Master 2FA Cost">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Master 2FA Cost</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>2FA Amount</label>
                            <input type="text" name="value" value="{{$master_2fa_cost->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="batch">
                <input type="hidden" name="name" value="Settlement Batch">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">Settlement Batch</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>Time</label>
                            <textarea name="value"class="form-control" required="" placeholder="Enter value" rows="3">{{$batch->value ?? ''}}</textarea>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-sm-4">
            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="actiontype" value="portalsetting">
                <input type="hidden" name="code" value="app_setting">
                <input type="hidden" name="name" value="App Setting">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h5 class="panel-title">App Setting</h5>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="form-group">
                            <label>App Setting</label>
                            <textarea name="value"class="form-control" required="" placeholder="Enter value" rows="3">{{$appsetting->value ?? ''}}</textarea>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </div>
            </form>
        </div>


    </div>
</div>
@endsection

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        $('.actionForm').submit(function(event) {
            var form = $(this);
            var id = form.find('[name="id"]').val();
            form.ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    form.find('button[type="submit"]').button('loading');
                },
                success:function(data){
                    if(data.status == "success"){
                        if(id == "new"){
                            form[0].reset();
                            $('[name="api_id"]').select2().val(null).trigger('change');
                        }
                        form.find('button[type="submit"]').button('reset');
                        notify("Task Successfully Completed", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        notify(data.status, 'warning');
                    }
                },
                error: function(errors) {
                    showError(errors, form);
                }
            });
            return false;
        });

    	$("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });

        $('')
    });
</script>
@endpush