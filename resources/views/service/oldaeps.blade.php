@php
    $name = explode(" ", Auth::user()->name);
@endphp

@extends('layouts.app')
@section('title', "Aeps Service")
@section('pagetitle', "Aeps Service")
@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        @if($is_agent == 'yes')
            <div class="col-md-4">
                <div class="widget-profile-one">
                    <div class="text-white card-box m-b-0 b-0 bg-primary p-lg text-center">
                        <div class="m-b-30">
                            <h4 class="text-white m-b-5">
                                {{ucfirst(Auth::user()->name)}}
                            </h4>
                            <small>{{ucfirst(Auth::user()->role->role_slug)}} of Company</small>
                        </div>
                    </div>
                    <div class="card-box p-0">
                        <table class="table table-bordered" style="width:100%">
                            <tr>
                                <th>AEPS Balance</th>
                                <th><i class="fa fa-inr"></i> {{Auth::user()->aepsbalance}}</th>
                            </tr>
                            <tr>
                                <th>Recent Fund Request (
                                    @if ($fundrequest)
                                        {{$fundrequest->status}}
                                    @endif
                                )</th>
                                <th><i class="fa fa-inr"></i> @if ($fundrequest)
                                    {{$fundrequest->amount}}
                                @endif</th>
                            </tr>
                            <tr><th class="text-center" colspan="2">Settlement Bank Details</th></tr>
                            <tr>
                                <th>Account No.</th>
                                <th>{{Auth::user()->account}}</th>
                            </tr>
                            <tr>
                                <th>Bank</th>
                                <th>{{Auth::user()->bank}}</th>
                            </tr>
                            <tr>
                                <th>Ifsc</th>
                                <th>{{Auth::user()->ifsc}}</th>
                            </tr>
                        </table>
                        <div class="p-20 text-center">
                            <a class="btn btn-md btn-primary waves-effect waves-light pull-left" data-toggle="modal" data-target="#aepsFundRequest">Fund Request</a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Aeps Transaction <span class="customer_name m-l-15 text-capitalize"></span></h3>
                    </div>
                    <div class="panel-body">
                        <form action="{{route('aepsinitiate')}}" method="post" id="transactionForm">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="transaction">
                            <input type="hidden" name="agree" value="true">
                            <input type="hidden" name="biodata">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Transaction Type :</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="BE" id="Balance" name="transactionType" checked="">
                                                <label for="Balance" style="padding: 0 25px">Balance Info</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="MS" id="MS" name="transactionType">
                                                <label for="MS" style="padding: 0 25px">Mini Statement</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="CW" id="Withdrawal" name="transactionType">
                                                <label for="Withdrawal" style="padding: 0 25px">Withdrawal</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="M" id="M" name="transactionType">
                                                <label for="M" style="padding: 0 25px">Aadhar Pay</label>
                                            </div>
                                        </div>

                                        <!--<div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="CD" id="Deposit" name="transactionType">
                                                <label for="Deposit" style="padding: 0 25px">Deposit</label>
                                            </div>
                                        </div>-->
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Device Type :</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="MORPHO_PROTOBUF" id="MORPHO_PROTOBUF" name="device" checked="">
                                                <label for="MORPHO_PROTOBUF" style="padding: 0 25px">MORPHO</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="md-radio m-b-0">
                                                <input autocomplete="off" type="radio" value="MANTRA_PROTOBUF" id="MANTRA_PROTOBUF" name="device">
                                                <label for="MANTRA_PROTOBUF" style="padding: 0 25px">MANTRA</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Mobile Number :</label>
                                    <input type="number" class="form-control" name="mobileNumber"  autocomplete="off" placeholder="Enter mobile number">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Aadhar Number :</label>
                                    <input type="text" class="form-control" name="adhaarNumber" autocomplete="off" placeholder="Enter aadhar number">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 CASH">
                                    <label>Bank :</label>
                                    <select name="nationalBankIdentificationNumber" class="form-control select" required="">
                                        <option value="">Select Bank</option>
                                        @foreach ($aepsbanks as $item)
                                        <option value="{{$item->BankIIN}}">{{$item->BankName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 PAY" style="display: none">
                                    <label>Bank :</label>
                                    <select name="nationalBankIdentificationNumber" class="form-control select">
                                        <option value="">Select Banks</option>
                                        @foreach ($aepsbanks as $item)
                                        <option value="{{$item->BankIIN}}">{{$item->BankName}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row" id="transactionData">
                                </div>
                            </div>

                            <div class="form-group text-center col-md-12 m-b-0">
                                <button type="button" class="btn btn-warning btn-lg waves-effect waves-light" id="scan">Scan</button>
                                <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                            </div>
                        </form>
                    </div>  
                </div>
            </div>
        @else
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Aeps Service Registration</h4>
                    </div>
                    <div class="panel-body">
                        <form action="{{route('aepskyc')}}" method="post" id="transactionFormKyc"> 
                            {{ csrf_field() }}


                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Email </label>
                                    <input type="email" class="form-control" autocomplete="off" name="emailid" placeholder="Enter Your Email" value="{{Auth::user()->email}}" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Mobile</label>
                                    <input type="text" pattern="[0-9]*" maxlength="10" minlength="10" class="form-control" name="phone1" autocomplete="off" placeholder="Enter Your Mobile" value="{{Auth::user()->mobile}}" required>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Submitting"><b><i class=" icon-paperplane"></i></b> Submit</button>
                            </div>
                        </form>
                    </div> 
                </div>
            </div>
        @endif
        
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    $(document).ready(function () {

        $('.mydatepic').datepicker({
            'autoclose':true,
            'clearBtn':true,
            'todayHighlight':true,
            'format':'dd-mm-yyyy',
        });
        //$('form#transactionForm').submit();
        $('form#transactionForm').submit(function() {
            var form= $(this);
            var type = form.find('[name="type"]');
            $(this).ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    swal({
                        title: 'Wait!',
                        text: 'We are working on request.',
                        onOpen: () => {
                            swal.showLoading()
                        },
                        allowOutsideClick: () => !swal.isLoading()
                    });
                },
                success:function(data){
                    swal.close();
                    console.log(type);
                    switch(data.status){
                        case 'TXN':
                            swal({
                                title:'Suceess', 
                                text : data.message, 
                                type : 'success',
                                onClose: () => {
                                    window.location.reload();
                                }
                            });
                            break;
                        
                        default:
                            //$.notify(data.message, 'danger');
                            swal({
                                title:'Oops', 
                                text : data.message, 
                                type : 'error',
                                onClose: () => {
                                    window.location.reload();
                                }
                            });
                            break;
                    }
                },
                error: function(errors) {
                    swal.close();
                    if(errors.status == '400'){
                        $.notify(errors.responseJSON.message, 'danger');
                    }else{
                        swal(
                          'Oops!',
                          'Something went wrong, try again later.',
                          'error'
                        );
                    }
                }
            });
            return false;
        });
    });

    function getDistrict(ele){
        $.ajax({
            url:  "{{route('dmt1pay')}}",
            type: "POST",
            dataType:'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend:function(){
                swal({
                    title: 'Wait!',
                    text: 'We are fetching district.',
                    allowOutsideClick: () => !swal.isLoading(),
                    onOpen: () => {
                        swal.showLoading()
                    }
                });
            },
            data: {'type':"getdistrict", 'stateid':$(ele).val()},
            success: function(data){
                swal.close();
                var out = `<option value="">Select District</option>`;
                $.each(data.message, function(index, value){
                    out += `<option value="`+value.districtid+`">`+value.districtname+`</option>`;
                });

                $('[name="bc_district"]').html(out);
            }
        });
    }
</script>

@endpush

@push('style')
    <style type="text/css">
        .md-radio:before {
            content: "";
        }
    </style>
@endpush

@push('script')
    <script type="text/javascript" src="{{asset('')}}/assets/js/jquery.validate.min.js"></script>
    <script src="{{ asset('/assets/js/jQuery.print.js') }}"></script>
    <script src="https://connect.spindiapay.com/assets/plugins/notifyjs/js/notify.js"></script>
    <script src="https://connect.spindiapay.com/assets/plugins/notifications/notify-metro.js"></script>
        
    <script type="text/javascript">
        var USERSYSTEM, STOCK={}, DT=!1, AEPSURL="{{route('aeps')}}";
        var SYSTEM = $("html");

        $(document).ready(function () {
            USERSYSTEM = {
                DEFAULT: function () {
                    USERSYSTEM.TRANSACTION();
                    USERSYSTEM.ONBOARD();
                    $(window).load(function() {
                        $('[name="transactionType"]').trigger('change');
                    });
                    

                    $('[name="transactionType"]').on('change', function () {
                        if($('[name="transactionType"]:checked').val() == "CW" || $('[name="transactionType"]:checked').val() == "CD" || $('[name="transactionType"]:checked').val() == "M"){
                            $('[name="transactionAmount"]').closest(".form-group").remove();
                            var out = `<div class="form-group col-md-6">
                                            <label>Amount :</label>
                                            <input type="number" class="form-control" name="transactionAmount" autocomplete="off" placeholder="Enter Amount">
                                        </div>`;

                            $('#transactionData').append(out);
                        }else{
                            $('[name="transactionAmount"]').closest(".form-group").remove();
                        }

                        if($('[name="transactionType"]:checked').val() != "M"){
                            $(".CASH").show();
                            $(".CASH").find("select").attr('name' , 'nationalBankIdentificationNumber').attr('required', '');
                            $(".PAY").hide();
                            $(".PAY").find("select").removeAttr('name').removeAttr('required');
                        }else{
                            $(".PAY").show();
                            $(".PAY").find("select").attr('name' , 'nationalBankIdentificationNumber').attr('required', '');
                            $(".CASH").hide();
                            $(".CASH").find("select").removeAttr('name').removeAttr('required');
                        }
                    });

                    $('#print').click(function(){
                        $('#receipt').find('.modal-body').print();
                    });

                    $('#statementprint').click(function(){
                        $('#ministatement').find('.modal-body').print();
                    });

                    $('#scan').click(function(){
                        var device = $('#transactionForm').find('[name="device"]:checked').val();
                        USERSYSTEM.RDSERVICE(device, "11100");
                    });
                },

                ONBOARD: function(){
                    $( "#fingkycForm" ).validate({
                        rules: {
                            merchantName: {
                                required: true
                            },
                            merchantAddress: {
                                required: true
                            },
                            merchantState: {
                                required: true
                            },
                            merchantPhoneNumber: {
                                required: true,
                                number: true,
                                minlength: 10,
                                maxlength: 10
                            },
                            userPan: {
                                required: true
                            },
                            merchantPinCode: {
                                required: true,
                                number: true,
                                minlength: 6,
                                maxlength: 6
                            }
                        },
                        messages: {
                            merchantName: {
                                required: "Please enter value",
                            },
                            merchantAddress: {
                                required: "Please enter value",
                            },
                            merchantState: {
                                required: "Please enter value",
                            },
                            merchantPhoneNumber: {
                                required: "Please enter value",
                                nnumber: "Aadhar number should be numeric",
                                minlength: "Your aadhar number must be 10 digit",
                                maxlength: "Your aadhar number must be 10 digit"
                            },
                            userPan: {
                                required: "Please enter value",
                            },
                            merchantPinCode: {
                                required: "Please enter value",
                                nnumber: "Aadhar number should be numeric",
                                minlength: "Your aadhar number must be 6 digit",
                                maxlength: "Your aadhar number must be 6 digit"
                            }
                        },
                        errorElement: "p",
                        errorPlacement: function ( error, element ) {
                            if ( element.prop( "name" ) === "bank" ) {
                                error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                            } else {
                                error.insertAfter( element );
                            }
                        },
                        submitHandler: function (form) {
                            var form = $(form);
                            SYSTEM.FORMSUBMIT(form, function(data){
                                if(!data.statusText){
                                    if(data.status == "success"){
                                        swal({
                                            type : "success",
                                            title: "Success",
                                            text : "User onboard successfully",
                                            onClose: () => {
                                                //window.location.reload();
                                            }
                                        });

                                    }else if(data.status == "pending"){
                                        swal({
                                            type : "warning",
                                            title: "Pending",
                                            text : "User onboard pending",
                                            onClose: () => {
                                                //window.location.reload();
                                            }
                                        });
                                    }else{
                                        swal('Failed', data.message, 'error');
                                    }
                                }else{
                                    SYSTEM.SHOWERROR(data, form);
                                }
                            });
                        }
                    });
                },

                TRANSACTION: function(){
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
                            nationalBankIdentificationNumber: 'required',
                            transactionAmount: {
                                required: true,
                                number: true,
                                min: 10
                            },
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
                            nationalBankIdentificationNumber : "Please select bank"
                        },
                        errorElement: "p",
                        errorPlacement: function ( error, element ) {
                            if ( element.prop( "name" ) === "nationalBankIdentificationNumber" ) {
                                error.insertAfter( element.closest( ".form-group" ).find("span.select2"));
                            } else {
                                error.insertAfter( element );
                            }
                        },
                        submitHandler: function (element) {
                            var form = $(element);
                            var scan = form.find('[name="biodata"]').val();
                            if(scan != ''){
                                SYSTEM.FORMSUBMIT($('#transactionForm'), function(data){

                                    if(!data.statusText){
                                        form.find('[name="biodata"]').val(null);
                                        if(data.status == "success"|| data.status == "pending"){
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

                                                        if(data.transactionType == "CW" || data.transactionType == "CD"){
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
                                                    if(val.txnType == "Cr"){
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
                                        SYSTEM.SHOWERROR(data, $('#transactionForm'));
                                    }
                                });
                            }else{
                                form.notify("Please scan your finger", 'danger');
                            }
                        }
                    });
                },

                RDSERVICE: function(device, port)
                {
                    var primaryUrl = "http://127.0.0.1:"+port;

                    $.ajax({
                        type: "RDSERVICE",
                        async: true,
                        crossDomain: true,
                        url: primaryUrl,
                        processData: false,
                        beforeSend: function(){
                            swal({
                                title: 'Scanning',
                                text: 'Please wait, device getting initiated',
                                onOpen: () => {
                                    swal.showLoading()
                                },
                                allowOutsideClick: () => !swal.isLoading()
                            });
                        },
                        success: function (data) {
                            swal.close();
                            var $doc = $.parseXML(data);
                            var CmbData1 =  $($doc).find('RDService').attr('status');
                            var CmbData2 =  $($doc).find('RDService').attr('info');
                            console.log('fingre data'); 
                            console.log(data); 
                            if(!CmbData1){
                                var CmbData1 =  $(data).find('RDService').attr('status');
                                var CmbData2 =  $(data).find('RDService').attr('info');
                            }
                            
                            if(CmbData1 == "READY"){
                                USERSYSTEM.CAPTURE(device, port);
                            }else if(CmbData1 == "NOTREADY" && CmbData2 == "Mantra Authentication Vendor Device Manager"){
                                USERSYSTEM.RDSERVICE(device, "11101");
                            }else{
                                //$.notify("Device : "+CmbData1, 'danger');
                                swal({
                                    title:'Success', 
                                    text : "Device : "+CmbData1, 
                                    type : 'success',
                                    onClose: () => {
                                        //window.location.reload();
                                    }
                                });
                                swal({
                                    title:'Oops', 
                                    text : "Device : "+CmbData1, 
                                    type : 'error',
                                    onClose: () => {
                                        //window.location.reload();
                                    }
                                });
                                //$.notify('error', 'Oops', "Device : "+CmbData1);
                            }
                        },
                        error: function (jqXHR, ajaxOptions, thrownError) {
                            swal.close();
                            if(port == "11100"){
                                USERSYSTEM.RDSERVICE(device, "11101");
                            }else{
                                swal({
                                    title:'Oops', 
                                    text : "Device not working correctly, please try again", 
                                    type : 'error',
                                    onClose: () => {
                                        //window.location.reload();
                                    }
                                });
                                //$.notify('error', 'Oops', 'Device not working correctly, please try again');
                            }
                        },
                    });
                },

                CAPTURE: function(device, port){
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
                            swal({
                                text: 'Please put any of your finger on device',
                                imageUrl:  '{{asset('')}}assets/images/capute.gif',
                                showConfirmButton: false,
                                allowOutsideClick: () => false
                            });
                        },
                        success: function (data) {
                            swal.close();
                            if(device == "MANTRA_PROTOBUF"){
                                var $doc = $.parseXML(data);
                                var errorInfo =  $($doc).find('Resp').attr('errInfo');
                    
                                if(errorInfo == 'Success'){
                                    swal({
                                        title:'Scanned', 
                                        text : "Fingerprint Captured Successfully", 
                                        type : 'success',
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    //$.notify('success', 'Scanned', 'Fingerprint Captured Successfully');
                                    $('[name="biodata"]').val(data);
                                }else{
                                    $.notify('error', 'Oops', 'Device not working correctly, please try again');
                                }
                            }else{
                                var errorInfo =  $(data).find('Resp').attr('errInfo');
                                var errorCode =  $(data).find('Resp').attr('errCode');
                                var mydata =  $(data).find('PidData').html();
                                if(errorCode == '0'){
                                    swal({
                                        title:'Scanned', 
                                        text : "Fingerprint Captured Successfully", 
                                        type : 'success',
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    //$.notify('success', 'Scanned', 'Fingerprint Captured Successfully');
                                    $('[name="biodata"]').val("<PidData>"+mydata+"</PidData>");
                                }else{
                                    swal({
                                        title:'Oops', 
                                        text : "Device not working correctly, please try again", 
                                        type : 'error',
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    //$.notify('error', 'Oops', 'Device not working correctly, please try again');
                                }
                            }
                        },
                        error: function (jqXHR, ajaxOptions, thrownError) {
                            swal.close();
                            swal({
                                title:'Oops', 
                                text : "Device not working correctly, please try again", 
                                type : 'error',
                                onClose: () => {
                                    //window.location.reload();
                                }
                            });
                            //$.notify('error', 'Oops', 'Device not working correctly, please try again');
                        },
                    });
                }
            }

            USERSYSTEM.DEFAULT();
        });
    </script>
@endpush