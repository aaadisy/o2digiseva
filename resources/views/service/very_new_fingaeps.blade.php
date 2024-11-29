@php
    $name = explode(" ", Auth::user()->name);
@endphp

@extends('layouts.app')
@section('title', "Aeps Service")
@section('pagetitle', "Aeps Service")

@section('bodyClass', "has-detached-left")

@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    @if(!$agent || $agent->status != "approved")
        <div class="row">
            <div class="col-sm-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Aeps Service Registration</h4>
                    </div>
                    <div class="panel-body">
                        <form action="{{route('ifaepstransaction') ?? ''}}" method="post" id="kycForm" target="_blank"> 
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Name </label>
                                    <input type="text" class="form-control" autocomplete="off" name="merchantName" placeholder="Enter Your Name" value="{{isset($name[0]) ? $name[0] : ''}}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Shopname</label>
                                    <input type="text" class="form-control" autocomplete="off" name="merchantShopname" value="{{Auth::user()->shopname}}" placeholder="Enter Your Shopname" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Email </label>
                                    <input type="email" class="form-control" autocomplete="off" name="merchantEmail" placeholder="Enter Your Email" value="{{Auth::user()->email}}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Mobile</label>
                                    <input type="text" pattern="[0-9]*" maxlength="10" minlength="10" class="form-control" name="merchantPhoneNumber" autocomplete="off" placeholder="Enter Your Mobile" value="{{Auth::user()->mobile}}" required>
                                </div>
                            </div>
                            @if(isset($error) && $error != "nul")
                                <p class="text-danger">{{$error}}</p>
                            @endif
                            <div class="form-group text-center">
                                <button type="submit" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Submitting"><b><i class=" icon-paperplane"></i></b> Submit</button>
                            </div>
                        </form>
                    </div> 
                </div>
            </div>
        </div>
    @else
        <div class="sidebar-detached"style="margin: 0px;">
            <div class="sidebar sidebar-default sidebar-separate" style="width: 320px;background-color: #fff;">
                <div class="sidebar-content">
                    <div class="content-group"style="margin-bottom: 0px!important;">
                        <div class="panel-body border-radius-top text-center" style="">
                            <div class="content-group-sm">
                                <h4 class=" no-margin-bottom">
                                    ICICI AEPS
                                </h4>
                            </div>

                            <a href="#" class="display-inline-block content-group-sm">
                                <img src="{{asset('')}}/assets/icicilogo.png" class="img-responsive" alt="">
                            </a>
                        </div>

                        <div class="no-border-top no-border-radius-top">
                            <ul class="navigation" style="padding-bottom: 0px;">
                                <li class="navigation-header">Banking Services</li>
                                <li class="active"><a href="#profile" data-toggle="tab" onclick="AEPSTAB('BE')" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Balance Enquiry</a></li>
                                <li><a href="#profile" data-toggle="tab" onclick="AEPSTAB('MS')" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Mini Statement</a></li>
                                <li><a href="#profile" data-toggle="tab" onclick="AEPSTAB('CW')" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Cash Withdrawal</a></li>
                                <li><a href="#profile" data-toggle="tab" onclick="AEPSTAB('M')" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Aadhar Pay</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--<img src="{{asset('assets/cash.jpeg')}}" class="img-responsive" style="width: 321px;">-->
            </div>
        </div>
        <div class="container-detached" style="margin-left: -321px;">
            <div class="content-detached" style="margin-left: 321px;">
                <div class="tab-content">
                    <div class="tab-pane fade in active">
                        <form action="{{route('ifaepstransaction')}}" method="post" id="transactionForm">
                            {{ csrf_field() }}
                            <input type="hidden" name="transactionType" id="transactionType" value="BE">
                            <input type="hidden" name="type" value="transaction">
                            <input type="hidden" name="aeps" value="">
                            <input type="hidden" name="agree" value="true">
                            <input type="hidden" name="biodata" value="">

                            <div class="panel panel-default no-margin">
                                <div class="panel-heading">
                                    <h4 class="panel-title mytitle">Balance Enquiry</h4>
                                </div>

                                <div class="panel-body" style="min-height: 180px;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Mobile Number :</label>
                                                <input type="text"  class="form-control" name="mobileNumber" id="mobileNumber" maxlength="10"  autocomplete="off" placeholder="Enter mobile number" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Aadhar Number :</label>
                                                <input type="text" class="form-control" name="adhaarNumber" id="adhaarNumber" maxlength="12" minlength="12" autocomplete="off" pattern="[0-9]*"  placeholder="Enter aadhar number" required="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bank :</label>
                                                <select name="bankid" id="nationalBankIdentificationNumber" class="form-control select" required="">
                                                    <option value="">Select Bank</option>         
                                                    @foreach ($aepsbanks as $bank)
                                                        <option value="{{$bank->iinno}}">{{$bank->bankName}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="label label-primary" onclick="bank('607094')" style="cursor: pointer;">SBI Bank</span>
                                                <span class="label label-primary" onclick="bank('508534')" style="cursor: pointer;">ICICI Bank</span>
                                                <span class="label label-primary" onclick="bank('607152')" style="cursor: pointer;">HDFC Bank</span>
                                                <span class="label label-primary" onclick="bank('607027')" style="cursor: pointer;">PNB Bank</span>
                                                <span class="label label-primary" onclick="bank('606985')" style="cursor: pointer;">BOB Bank</span>
                                                <span class="label label-primary" onclick="bank('607161')" style="cursor: pointer;">Union Bank</span>
                                                <span class="label label-primary" onclick="bank('607264')" style="cursor: pointer;">Central Bank</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group transactionAmount">
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel-footer text-center">
                                    @if($agent->status == "approved")
                                        <button type="submit" class="btn bg-slate-800 btn-lg btn-raised legitRipple" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Proceeding...">Scan & Submit</button>
                                    @else
                                        <h4 class="text-danger">User Document Under Screening, Wait For Approval</h4>
                                    @endif 
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div id="receipt" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
  
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Receipt</h4>
            </div>
            <div class="modal-body p-0">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="clearfix">
                            <div class="pull-left">
                                <h4>
                                    @if (Auth::user()->company->logo)
                                        <img src="{{asset('')}}public/logos/{{Auth::user()->company->logo}}" class=" img-responsive" alt="" style="width: 220px;height: 40px;">
                                    @else
                                        {{Auth::user()->company->companyname}}
                                    @endif
                                </h4>
                            </div>
                            <div class="pull-right">
                                <h4><span class="receptTitle"></span> Invoice</h4>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left m-t-10">
                                    <address class="m-b-10">
                                        <strong>{{Auth::user()->name}}</strong><br>
                                        {{Auth::user()->company->companyname}}<br>
                                        Phone : {{Auth::user()->mobile}}
                                    </address>
                                </div>
                                <div class="pull-right m-t-10">
                                    <address class="m-b-10">
                                        <strong>Date: </strong> <span class="created_at"></span><br>
                                        <strong>Order ID: </strong> <span class="id"></span><br>
                                        <strong>Status: </strong> <span class="status"></span><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <h4 class="title"></h4>
                                    <table class="table m-t-10 table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bank</th>
                                                <th>Aadhar Number</th>
                                                <th>Ref No.</th>
                                                <th class="cash">Amount</th>
                                                <th>Account Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="bank"></td>
                                                <td class="aadhar"></td>
                                                <td class="rrn"></td>
                                                <td class="amount cash"></td>
                                                <td class="balance"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="border-radius: 0px;">
                            <div class="col-md-6 col-md-offset-6">
                                <h4 class="text-right cash">Withdrawal Amount : <span class="amount"></span></h4>
                            </div>
                        </div>
                        <hr>
                        <div class="hidden-print">
                            <div class="pull-right">
                                <a href="javascript:void(0)"  id="print" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ministatement" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
  
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Mini Statement</h4>
            </div>
            <div class="modal-body p-0">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="clearfix">
                            
                            <div class="pull-right">
                                <h4><span class="receptTitle"></span> Invoice</h4>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left m-t-10">
                                    <address class="m-b-10">
                                        <strong>{{Auth::user()->name}}</strong><br>
                                        {{Auth::user()->company->companyname}}<br>
                                        Phone : {{Auth::user()->mobile}}
                                    </address>
                                </div>
                                <div class="pull-right m-t-10">
                                    <address class="m-b-10">
                                        <strong>Bank : </strong> <span class="bank"></span><br>
                                        <strong>Acc. Bal. : </strong> <span class="balance"></span><br>
                                        <strong>Bank Rrn: </strong> <span class="rrn"></span><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <h4 class="title"></h4>
                                    <table class="table m-t-10 table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Narrartion</th>
                                                <th>Credit (Rs)</th>
                                                <th>Debit (Rs)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="statementData">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="hidden-print">
                            <div class="pull-right">
                                <a href="javascript:void(0)"  id="statementprint" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    <link href="{{asset('')}}assets/css/jquery-confirm.min.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        .error{
            color: red;
        }
        .has-detached-left .content-detached{
            margin-left: 261px;
        }
        .mycontent{
            background: #fff;
            margin: 20px;
            padding: 0px;
        }

        .label{
            margin: 2px;
        }
    </style>
@endpush
@push('script')
<script src="{{ asset('/assets/js/core/jquery.cookie.js') }}"></script>
<script src="{{ asset('/assets/js/core/aadhaar_capture.js') }}"></script>
<script type="text/javascript" src="{{asset('')}}assets/js/core/jquery-confirm.min.js"></script>
<script type="text/javascript" src="{{asset('')}}assets/js/core/notify.min.js"></script>

<script type="text/javascript">
    var gif = '{{url('')}}/assets/images/capute.gif',loading = '{{url('')}}/assets/images/loading.gif';

    var STOCK = {};
    STOCK.RD_SERVICES = [];
    STOCK.DEVICE_LIST = [];
    STOCK.PAST_TXNS = [];
    STOCK.BANKLIST = [];

    var FLAG = {};
    FLAG.RD_SERVICE_SCAN_DONE = false;

    $(document).ready(function () {
        FETCH_RD_SERVICE_LIST();
        $('.mydatepic').datepicker({
            'autoclose':true,
            'clearBtn':true,
            'todayHighlight':true,
            'format':'dd-mm-yyyy',
        });
      
      $( "#kycForm" ).validate({
            rules: {
                merchantShopname: {
                    required: true
                },
            },
            messages: {
                merchantShopname: {
                    required: "Please enter shopname"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop( "name" ) === "bankId" ) {
                    error.insertAfter( element.closest( ".form-group" ).find("span.select2"));
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function (element) {
                var form = $("#kycForm" );
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.statuscode == "TXN"){
                            window.open(data.url, '_blank');
                        }else{
                            swal({
                                title:'Failed', 
                                text : data.message, 
                                type : 'error'
                            });
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });
      
      

        $( "#transactionForm" ).validate({
            rules: {
                mobileNumber: {
                    required: true,
                    minlength: 10,
                    number : true,
                    maxlength: 11
                },
                adhaarNumber: {
                    required: true,
                    number: true,
                    minlength: 12,
                    maxlength: 12
                },
                bankName1: 'required',
                device: 'required'
            },
            messages: {
                mobileNumber: {
                    required: "Please enter mobile number",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 10 digit",
                    maxlength: "Your mobile number must be 10 digit"
                },
                adhaarNumber: {
                    required: "Please enter aadhar number",
                    number: "Aadhar number should be numeric",
                    minlength: "Your aadhar number must be 12 digit",
                    maxlength: "Your aadhar number must be 12 digit"
                },
                transactionAmount: {
                    required: "Please enter amount",
                    number: "Transaction amount should be numeric",
                    min : "Minimum transaction amount should be 10"
                },
                bankName1 : "Please select bank",
                device : "Please select device"
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop( "name" ) === "bankId" ) {
                    error.insertAfter( element.closest( ".form-group" ).find("span.select2"));
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function (element) {
                var form = $("#transactionForm" );
                var scan = form.find('[name="biodata"]').val();
                if(scan != ''){
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button[type="submit"]').button('loading');
                        },
                        success:function(data){
                            console.log(data);
                            form.find('button[type="submit"]').button('reset');
                            form.find('[name="biodata"]').val('');
                            if(data.status == "Success" || data.status == "Pending"){
                                form[0].reset();
                                form.find('select').select2().val(null).trigger('change');
                                getbalance();
                                form.find('button[type="submit"]').button('reset');
                                if(data.status == "Success" || data.status == "Pending"){
                                    if(data.transactionType != "MS"){
                                        form[0].reset();
                                        swal({
                                            title: data.title,
                                            text:  data.message + ", Remaining Balance - "+data.balance,
                                            type: 'success',
                                            showCancelButton: true,
                                            confirmButtonColor: '#3085d6',
                                            cancelButtonColor: '#456b8c',
                                            confirmButtonText: 'Print Invoice',
                                            cancelButtonText: 'Close',
                                            allowOutsideClick : false,
                                            allowEscapeKey : false,
                                            allowEnterKey : false
                                        }).then((result) => {
                                            if(result.value){
                                                if(data.transactionType == "CW"){
                                                    $(".cash").show();
                                                }else{
                                                    $('.cash').hide();
                                                }
                                                $('#receipt').find('.created_at').text(data.created_at);
                                                $('#receipt').find('.amount').text(data.amount);
                                                $('#receipt').find('.rrn').text(data.rrn);
                                                $('#receipt').find('.aadhar').text(data.aadhar);
                                                $('#receipt').find('.id').text(data.id);
                                                $('#receipt').find('.status').text(data.status);
                                                $('#receipt').find('.bank').text(data.bank);
                                                $('#receipt').find('.balance').text(data.balance);
                                                $('#receipt').find('.title').text(data.title);
                                                $('#receipt').modal();
                                            }
                                        });
                                    }else{
                                        $('#ministatement').find('.rrn').text(data.rrn);
                                        $('#ministatement').find('.bank').text(data.bank);
                                        $('#ministatement').find('.balance').text(data.balance);
                                        $('#ministatement').find('.title').text(data.title);
                                        var trdata = '';
                                        $.each(data.data, function(index, val) {
                                            if(val.txnType == "C"){
                                                trdata += `<tr>
                                                        <td>`+val.date+`</td>
                                                        <td>`+val.narration+`</td>
                                                        <td>`+val.amount+`</td>
                                                        <td></td>
                                                    </tr>`;
                                            }else{
                                                trdata += `<tr>
                                                        <td>`+val.date+`</td>
                                                        <td>`+val.narration+`</td>
                                                        <td></td>
                                                        <td>`+val.amount+`</td>
                                                    </tr>`;
                                            }
                                        });
                                        $('#ministatement').find('.statementData').html(trdata);
                                        $('#ministatement').modal();
                                    }
                                }else{
                                    swal('Failed', data.message, 'error');
                                }
                            }else{
                                form.find('[name="biodata"]').val('');
                                swal({
                                    title:'Failed', 
                                    text : data.message, 
                                    type : 'error'
                                });
                            }
                        },
                        error: function(errors) {
                            form.find('[name="biodata"]').val('');
                            showError(errors, form);
                        }
                    });
                }else{
                    //scandata();
                    aadhaar_capture();
                }
            }
        });
    });
    
    function FETCH_RD_SERVICE_LIST(forced = false) {
        var rd_service_cookie = $.cookie('RDSLS');
        if(rd_service_cookie) {
            FLAG.RD_SERVICE_SCAN_DONE = true;
            STOCK.RD_SERVICES = JSON.parse(rd_service_cookie);
        }
        
        if(forced || !rd_service_cookie || (rd_service_cookie && STOCK.RD_SERVICES.length == 0) ) {
            STOCK.RD_SERVICES = [];
            FLAG.RD_SERVICE_SCAN_DONE = false;
            scan_all_rd_services();
        }
    }

    function scandata() {
        var device = $( "#transactionForm" ).find('[name="device"]:checked').val();
        rdservice(device, "11100");
    }
            
    function rdservice(device, port)
    {
        if (location.protocol !== "https:") {
            var primaryUrl = "https://127.0.0.1:"+port;
        }else{
            var primaryUrl = "http://127.0.0.1:"+port;
        }

        $.ajax({
            type: "RDSERVICE",
            async: true,
            crossDomain: true,
            url: primaryUrl,
            processData: false,
            beforeSend: function(){
            },
            success: function (data) {
                var $doc = $.parseXML(data);
                var CmbData1 =  $($doc).find('RDService').attr('status');
                var CmbData2 =  $($doc).find('RDService').attr('info');
                
                if(!CmbData1){
                    var CmbData1 =  $(data).find('RDService').attr('status');
                    var CmbData2 =  $(data).find('RDService').attr('info');
                }
                
                if(CmbData1 == "READY"){
                    capture(device, port);
                }else if(port == "11100"){
                    rdservice(device, "11101");
                }else if(port == "11101"){
                    rdservice(device, "11102");
                }else if(port == "11102"){
                    rdservice(device, "11103");
                }else if(port == "11103"){
                    rdservice(device, "11104");
                }else if(port == "11104"){
                    rdservice(device, "11105");
                }else{
                    notify("Device : "+CmbData1, 'warning');
                }
            },
            error: function (jqXHR, ajaxOptions, thrownError) {
                $('#transactionForm').unblock();
                if(port == "11100"){
                    rdservice(device, "11101");
                }else if(port == "11101"){
                    rdservice(device, "11102");
                }else if(port == "11102"){
                    rdservice(device, "11103");
                }else if(port == "11103"){
                    rdservice(device, "11104");
                }else if(port == "11104"){
                    rdservice(device, "11105");
                }else{
                    notify("Oops! Device not working correctly, please try again", 'warning');
                }
            },
        });
    }

    function capture(device, port){
        var primaryUrl = "http://127.0.0.1:"+port;
        
        if(device == "MANTRA_PROTOBUF"){
            var url = primaryUrl+"/rd/capture";
        }else{
            var url = primaryUrl+"/capture";
        }

        if(device == "MANTRA_PROTOBUF"){
            var XML='<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="0" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
        }else{
            var XML='<PidOptions ver=\"1.0\">'+'<Opts fCount=\"1\" fType=\"0\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>'; 
        }
        
        $.ajax({
            type: "CAPTURE",
            async: true,
            crossDomain: true,
            url: url,
            data:XML,
            contentType: "text/xml; charset=utf-8",
            processData: false,
            beforeSend: function(){
            },
            success: function (data) {
                if(device == "MANTRA_PROTOBUF"){
                    var $doc = $.parseXML(data);
                    var errorInfo =  $($doc).find('Resp').attr('errInfo');
        
                    if(errorInfo == 'Success'){
                        notify("Fingerprint Captured Successfully", "success");
                        
                        $('[name="biodata"]').val(data);
                        $('#transactionForm').submit();
                    }else{
                        notify("Oops! Device not working correctly, please try again", "warning");
                    }
                }else{
                    var errorInfo =  $(data).find('Resp').attr('errInfo');
                    var errorCode =  $(data).find('Resp').attr('errCode');
                    var mydata =  $(data).find('PidData').html();
                    if(errorCode == '0'){
                        notify("Fingerprint Captured Successfully", "success");
                        $('[name="biodata"]').val("<PidData>"+mydata+"</PidData>");
                        $('#transactionForm').submit();
                    }else{
                        notify("Oops! Device not working correctly, please try again", "warning");
                    }
                }
            },
            error: function (jqXHR, ajaxOptions, thrownError) {
                notify("Oops! Device not working correctly, please try again", "warning");
            },
        });
    }
    
    function AEPSTAB(type){
        if(type == "CW" || type == "M"){
            $('.transactionAmount').html(`
                <label>Amount :</label>
                <input type="text" class="form-control" name="transactionAmount" pattern="[0-9]*" id="amount" autocomplete="off" placeholder="Enter Amount">
                <span class="label label-primary" onclick="amount('100')" style="cursor: pointer;">100</span>
                <span class="label label-primary" onclick="amount('500')" style="cursor: pointer;">500</span>
                <span class="label label-primary" onclick="amount('1000')" style="cursor: pointer;">1000</span>
                <span class="label label-primary" onclick="amount('1500')" style="cursor: pointer;">1500</span>
                <span class="label label-primary" onclick="amount('2000')" style="cursor: pointer;">2000</span>
                <span class="label label-primary" onclick="amount('2500')" style="cursor: pointer;">2500</span>
                <span class="label label-primary" onclick="amount('3000')" style="cursor: pointer;">3000</span>
                <span class="label label-primary" onclick="amount('10000')" style="cursor: pointer;">10000</span>
            `);   
        }else{
            $('.transactionAmount').html('');
        }
        
        $("#transactionForm" ).find('[name="transactionType"]').val(type);
    }
    
    function bank(iinno){
        $('[name="bankid"]').val(iinno).trigger('change');
    }

    function amount(amount){
        $('[name="transactionAmount"]').val(amount);
    }
</script>
@endpush