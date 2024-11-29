@extends('layouts.app')
@section('title', "Money Transfer")
@section('pagetitle', "Money Transfer")
@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Money Transfer</h4>
                </div>
                <form id="serachForm" action="{{route('dmt1pay')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="verification">
                    <input type="hidden" id="rname">
                    <input type="hidden" id="rlimit">
                    <div class="panel-body">
                        <div class="form-group no-margin-bottom">
                            <label>Mobile Number</label>
                            <input type="number" step="any" name="mobile" class="form-control" placeholder="Enter Mobile Number" required="">
                        </div>
                    </div>
                    <div class="panel-footer text-center">
                        <button type="submit" class="btn bg-slate btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i></b> Search</button>
                    </div>
                </form>
            </div>

            <div class="panel userdetails" style="display:none">
                <div class="panel-body">
                    <h5 class="content-group no-margin">
                        <span class="label label-flat label-rounded label-icon border-grey text-grey mr-10">
                            <i class="icon-user"></i>
                        </span>
                        <a href="javascript:void(0)" class="text-default name"></a>
                    </h5>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <h6 class="text-semibold no-margin-top mobile"></h6>
                            <ul class="list list-unstyled">
                                <li>Used Limit : <i class="fa fa-inr"></i> <span class="usedlimit"></span></li>
                            </ul>
                        </div>

                        <div class="col-sm-6">
                            <h6 class="text-semibold text-right no-margin-top">Total Limit : <i class="fa fa-inr"></i> <span class="totallimit"></span></h6>
                            <ul class="list list-unstyled text-right">
                                <li>Remain Limit: <i class="fa fa-inr"></i> <span class="text-semibold remainlimit"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="no-margin">

                <div class="panel-footer text-center alpha-grey">
                    <a href="#" data-toggle="modal" data-target="#beneficiaryModal" class="btn bg-slate legitRipple">
                        <i class="icon-plus22 position-left"></i>
                        New Beneficiary
                    </a>
                </div>
            </div> 
        </div>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Beneficiary List</h4>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-bordered table-bordered transaction" cellspacing="0" width="100%">
                            <thead>
                                <th width="150px">Name</th>
                                <th width="250px">Account Details</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="beneficiaryModal" class="modal fade" role="dialog" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h4 class="modal-title pull-left">Beneficiary Details Please</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{route('dmt1pay')}}" method="post" id="beneficiaryForm">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <input type="hidden" name="rid">
                    <input type="hidden" name="type" value="addbeneficiary">
                    <input type="hidden" name="mobile">
                    <input type="hidden" name="name">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bank Name : </label>
                                <select id="bank" name="benebank" class="form-control select">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{$bank->id}}" ifsc="{{$bank->ifsc}}">{{$bank->bankname}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">IFSC Code:</label>
                                <input type="text" class="form-control" name="beneifsc" placeholder="Bank ifsc code" required="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Bank Account No.:</label>
                                <input type="text" class="form-control" id="account" name="beneaccount" placeholder="Enter account no." required="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Beneficiary Mobile:</label>
                                <input type="text" class="form-control" name="benemobile" placeholder="Enter name" required="">
                                <p></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Beneficiary Name:</label>
                                <input type="text" class="form-control" name="benename" placeholder="Enter name" required="">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">DOB:</label>
                                <input type="date" class="form-control" name="dob" placeholder="Enter dob" required="">
                                <p></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Pincode:</label>
                                <input type="text" class="form-control" name="pincode" placeholder="Enter pincode" required="">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Address:</label>
                                <input type="text" class="form-control" name="address" placeholder="Enter address" required="">
                                <p></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--<button class="btn btn-primary legitRipple" type="button" id="getBenename">Get Name</button>-->
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="otpModal" class="modal fade" role="dialog" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h4 class="modal-title pull-left">Otp Verification</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{route('dmt1pay')}}" method="post" id="otpForm">
                {{ csrf_field() }}
                <div class="modal-body">
                    <input type="hidden" name="type" value="beneverify">
                    <input type="hidden" name="mobile">
                    <input type="hidden" name="beneaccount">
                    <input type="hidden" name="benemobile">
                    <div class="form-group">
                        <label>OTP</label>
                        <input type="text" class="form-control" name="otp" placeholder="enter otp" required>
                        <a href="javascript:void(0)" class="pull-right resendOtp" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Sending" type="resendOtpVerification"><i class='fa fa-paper-plane'></i> Resend Otp</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="transferModal" class="modal fade" role="dialog" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h4 class="modal-title">Transfer Money</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{route('dmt1pay')}}" method="post" id="transferForm">
                {{ csrf_field() }}
                <input type="hidden" name="type" value="transfer">
                <input type="hidden" name="mobile">
                <input type="hidden" name="name">
                <input type="hidden" name="benename">
                <input type="hidden" name="beneaccount">
                <input type="hidden" name="benebank">
                <input type="hidden" name="beneifsc">
                <input type="hidden" name="benemobile">
                <input type="hidden" name="beneid">
                <div class="modal-body">
                    <div class="panel border-left-lg border-left-success invoice-grid timeline-content">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h6 class="text-semibold no-margin-top ">Name - <span class="benename"></span></h6>
                                    <ul class="list list-unstyled">
                                        <li>Bank - <span class="benebank"></span></li>
                                    </ul>
                                </div>

                                <div class="col-sm-6">
                                    <h6 class="text-semibold text-right no-margin-top">Acc - <span class="beneaccount"></span></h6>
                                    <ul class="list list-unstyled text-right">
                                        <li>Ifsc - <span class="beneifsc"></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" class="form-control" placeholder="Enter amount to be transfer" name="amount" step="any" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div>
    
    </div>
</div>

<div id="receipt" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Receipt</h4>
            </div>
            <div class="modal-body">
                <ul class="list-group transactionData p-0">
                </ul>
                <div id="receptTable">
                    <div class="clearfix">
                        <div class="pull-left">
                            <h4>Invoice</h4>
                        </div>
                    </div>
                    <hr class="m-t-10 m-b-10">
                    <div class="row">
                        <div class="col-md-12">
                            
                            <div class="pull-left m-t-10">
                                <address class="m-b-10">
                                    Agent : <strong class="username">{{Auth::user()->name}}</strong><br>
                                    Shop Name : <span class="company">{{Auth::user()->shopname}}</span><br>
                                    Phone : <span class="mobile">{{Auth::user()->mobile}}</span>
                                    </address>
                            </div>
                            <div class="pull-right m-t-10">
                                <address class="m-b-10">
                                    <strong>Date : </strong> <span class="date">{{date('d M y - h:i A')}}</span><br>
                                    <strong>Name : </strong> <span class="benename"></span><br>
                                    <strong>Account : </strong> <span class="beneaccount"></span><br>
                                    <strong>Bank : </strong> <span class="benebank"></span>
                                </address>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <h4>Transaction Details :</h4>
                                <table class="table m-t-10">
                                    <thead>
                                        <tr>
                                            <th>Order Id</th>
                                            <th>Amount</th>
                                            <th>UTR No.</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="id"></td>
                                            <td class="amount"></td>
                                            <td class="refno"></td>
                                            <td class="status"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="border-radius: 0px;">
                        <div class="col-md-6 col-md-offset-6">
                            <h5 class="text-right">Transfer Amount : <span class="samount"></span></h5>
                        </div>
                    </div>
                    <p>* As per RBI guideline, maximum charges allowed is 2%.</p>
                    <hr>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                <button class="btn bg-slate btn-raised legitRipple" type="button" id="print"><i class="fa fa-print"></i></button>
            </div>
        </div>
    </div>
</div>

<div id="registrationModal" class="modal fade" role="dialog" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h4 class="modal-title pull-left">Member Registration</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{route('dmt1pay')}}" method="post" id="registrationForm">
                {{ csrf_field() }}
                <div class="modal-body">
                    <input type="hidden" name="type" value="registration">
                    <input type="hidden" name="mobile">
                    <input type="hidden" name="stateresp">
                    <div  class="row">
                        <div class="form-group col-md-6">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="fname" required="" placeholder="Enter last name">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="lname" required="" placeholder="Enter first name">
                        </div>
                    </div>
                    <div  class="row">
                        <div class="form-group col-md-6">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="dob" required="" placeholder="Enter Date of Birth">
                        </div>

                        <div class="form-group col-md-6">
                            <label>GST State</label>
                            <select class="form-control" name="gst_state" required="">
                                <option value="0">Select State</option>
                                <option value="7">Utter Pradesh</option>
                            </select>
                            <!--<input type="text" class="form-control" name="lname" required="" placeholder="Enter first name">-->
                        </div>
                    </div>
                    <div  class="row">
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" required="" placeholder="Enter Pincode">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Otp</label>
                            <input type="text" class="form-control" name="otp" required="" placeholder="Enter Pincode">
                            <a href="javascript:void(0)" class="pull-right resendOtp" data-loading-text="<i class='fa fa-spinner fa-spin'></i> Sending" type="resendOtpVerification"><i class='fa fa-paper-plane'></i> Resend Otp</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('/assets/js/core/jQuery.print.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("[name='mobile']").keyup(function(){
            $( "#serachForm" ).submit();
        });

        $('#print').click(function(){
            $('#receptTable').print();
        });

        $('#bank').on('change', function (e) {
            $('input[name="beneifsc"]').val($(this).find('option:selected').attr('ifsc'));
        }); 

        $('a.resendOtp').click(function(){
            var mobile = $(this).closest('form').find('input[name="mobile"]').val();
            var button = $(this);
            var form  = $(this).closest('form');
            $.ajax({
                url: "{{route('dmt1pay')}}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data: {'mobile':mobile, 'type':"otp"},
                beforeSend:function(){
                    swal({
                        title: 'Wait!',
                        text: 'We are processing your request.',
                        allowOutsideClick: () => !swal.isLoading(),
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });
                },
                success: function(data){
                    swal.close();
                    if(result.statuscode == "TXN"){
                        notify(data.message, 'success', "inline",form);
                    }else{
                        notify(data.message, 'danger', "inline",form);
                    }
                },
                error: function(error){
                    swal.close();
                    notify("Something went wrong", 'danger', "inline",form);
                }
            });
        });

        $( "#serachForm" ).validate({
            rules: {
                mobile: {
                    required: true,
                    number : true,
                    minlength:10,
                    maxlength:10
                },
            },
            messages: {
                mobile: {
                    required: "Please enter mobile number",
                    number: "Mobile number should be numeric",
                    minlenght: "Mobile number length should be 10 digit",
                    maxlenght: "Mobile number length should be 10 digit",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#serachForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.statuscode == "TXN"){
                            setVerifyData(data);
                            setBeneData(data);
                        }else if(data.statuscode == "RNF"){
                            var mobile = form.find('[name="mobile"]').val();
                            $('#registrationModal').find('[name="mobile"]').val(mobile);
                            $('#registrationModal').find('[name="stateresp"]').val(data.data.stateresp);

                            $('#registrationModal').modal();
                        }else if(data.statuscode == "TXNOTP"){
                            var type = form.find('[name="type"]').val();
                            if(type == "registration" || type == "verification"){
                                $('#otpModal').find('[name="type"]').val("registrationValidate");
                            }
                            var mobile = form.find('[name="mobile"]').val();
                            $('#otpModal').find('[name="transid"]').val(data.transid);
                            $('#otpModal').find('[name="mobile"]').val(mobile);
                            $('#otpModal').modal();
                        }else{
                            notify(data.status, 'danger', "inline",form);
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#beneficiaryForm" ).validate({
            rules: {
                ifsc: {
                    required: true,
                },
                account: {
                    required: true,
                },
                account_confirmation: {
                    required: true,
                    equalTo : '#account'
                },
                name: {
                    required: true,
                }
            },
            messages: {
                ifsc: {
                    required: "Bank ifsc code is required",
                },
                account: {
                    required: "Beneficiary bank account number is required",
                },
                account_confirmation: {
                    required: "Account number confirmation is required",
                    equalTo : 'Account confirmation is same as account number'
                },
                name: {
                    required: "Beneficiary account name is required",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#beneficiaryForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.statuscode == "TXN"){
                            form[0].reset();
                            form.find('select').select2().val(null).trigger('change');
                            form.closest('.modal').modal('hide');
                            notify('Beneficiary Successfully Added.', 'success');
                            $( "#serachForm" ).submit();
                        }else{
                            notify(data.status, 'danger', "inline", form);
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#otpForm" ).validate({
            rules: {
                otp: {
                    required: true,
                    number : true,
                },
            },
            messages: {
                otp: {
                    required: "Please enter otp number",
                    number: "Otp number should be numeric",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#otpForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.statuscode == "TXN"){
                            var type = form.find('[name="type"]').val();
                            form[0].reset();
                            $('#otpModal').find('[name="mobile"]').val("");
                            $('#otpModal').find('[name="beneaccount"]').val("");
                            $('#otpModal').find('[name="benemobile"]').val("");
                            $('#otpModal').modal('hide');
                            if(type == "registrationValidate"){
                                notify('Member successfully registered.', 'success');
                            }else{
                                notify('Beneficiary Successfully verified.', 'success');
                            }
                            $( "#serachForm" ).submit();
                        }else{
                            notify(data.status, 'danger', "inline",form);
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#transferForm" ).validate({
            rules: {
                amount: {
                    required: true,
                    number : true,
                    min:100
                }
            },
            messages: {
                amount: {
                    required: "Please enter amount",
                    number: "Amount should be numeric",
                    min : "Amount value should be greater than 100"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#transferForm');
                var amount = form.find('[name="amount"]').val();
                var benename = form.find('[name="benename"]').val();
                var beneaccount = form.find('[name="beneaccount"]').val();
                var benebank = form.find('[name="benebank"]').val();
                var bankname = form.find('.benebank').text();
                var beneifsc = form.find('[name="beneifsc"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        form[0].reset();
                        getbalance();
                        form.closest('.modal').modal('hide');
                        var samount = 0;
                        var out ="";
                        var tbody = '';
                        if(data.data.status == "true" || data.data.response_code == "1"){
                            console.log('data.data');
                            console.log(data.data);
                                samount += parseFloat(data.data.amount);
                                out += '<li class="list-group-item alert alert-success no-margin mb-10"><strong>Rs.  '+data.data.amount+'</strong><span class="pull-right">'+data.data.status+'</span></li>';
                                let sta = '';
                                if(data.data.status == true){
                                    sta = 'Paid';
                                }else{
                                    sta = 'Pending';
                                }
                                tbody += `
                                    <tr>
                                        <td>`+data.data.ackno+`</td>
                                        <td>`+data.data.amount+`</td>
                                        <td>`+data.data.utr+`</td>
                                        <td>`+sta+`</td>
                                    </tr>        
                                `;
                            }else{
                                out += '<li class="list-group-item alert alert-danger no-margin mb-10"><strong>Rs.  '+data.amount+'</strong><span class="pull-right">'+data.data.message+'</span></li>';
                            }
                        /*$.each(data.data , function(index, val){
                            
                        });*/
                        $('.transactionData').html(out);
                        if(samount != 0){
                            $('#receptTable').fadeIn('400');                            
                            $('.benename').text(benename);
                            $('.beneaccount').text(beneaccount);
                            $('.benebank').text(bankname);
                            $('#receptTable').find('tbody').html(tbody);
                            $('.samount').text(parseFloat(samount));
                        }else{
                            $('#receptTable').fadeOut('400');
                        }
                        $('#receipt').modal();
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#registrationForm" ).validate({
            rules: {
                name: {
                    required: true,
                },
                surname: {
                    required: true,
                },
                pincode: {
                    required: true,
                    number : true,
                    minlength:6,
                    maxlength:6
                },
            },
            messages: {
                name: {
                    required: "Please enter firstname",
                },
                surname: {
                    required: "Please enter surname",
                },
                pincode: {
                    required: "Please enter pincode",
                    number: "Pincode should be numeric",
                    minlenght: "Pincode length should be 6 digit",
                    maxlenght: "Pincode length should be 6 digit",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('#registrationForm');
                var type = form.find('input[name="type"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.statuscode == "TXN"){
                            form.closest('.modal').modal('hide');
                            $( "#serachForm" ).submit();
                        }else{
                            notify(data.message, 'danger', "inline",form);
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $('#getBenename').click(function(){
            var mobile = $(this).closest('form').find("[name='mobile']").val();
            var name = $(this).closest('form').find("[name='name']").val();
            var benebank = $(this).closest('form').find("[name='benebank']").val();
            var beneaccount = $(this).closest('form').find("[name='beneaccount']").val();
            var beneifsc = $(this).closest('form').find("[name='beneifsc']").val();
            var benename = $(this).closest('form').find("[name='benename']").val();
            var benemobile = $(this).closest('form').find("[name='benemobile']").val();

            if(mobile != '' || name != '' || benebank != '' || beneaccount != '' || beneifsc != '' || benename != '' || benemobile != ''){
                getName(mobile, name, benebank, beneaccount, beneifsc, benename, benemobile, 'add');
            }
        });
    });

    function setVerifyData(data) {
        $('.name').text(data.data.fname+' '+data.data.lname);
        $('.mobile').text(data.data.mobile);
        $('.totallimit').text( parseInt(data.data.bank3_limit));
        $('.usedlimit').text( parseInt(0) );
        $('.remainlimit').text( parseInt(data.data.bank3_limit) - parseInt(0));
        $('[name="mobile"]').val(data.data.mobile);
        $('[name="name"]').val(data.data.fname);
        $('#rname').val(data.data.fname);
        $('#rlimit').val(parseInt(data.data.bank3_limit) - parseInt(0));
        $('.userdetails').fadeIn('400');
    }

    function setBeneData(data) {
        console.log('setting bene data');
        console.log(data);
        if(data.data.benedata.length > 0){
            out = ``;
            $.each(data.data.benedata , function(index, beneficiary) {
                out += `<tr>
                        <td>`+beneficiary.name+`</td>
                        <td>`+beneficiary.accno+` <br> (`+beneficiary.ifsc+`)<br> ( `+beneficiary.bankname+` )</td>
                        <td>`;
                out +=`<button class="btn bg-slate btn-xs legitRipple" onclick="sendMoney('`+data.data.mobile+`','`+data.data.fname+`','`+beneficiary.bankid+`', '`+beneficiary.bene_id+`', '`+beneficiary.accno+`', '`+beneficiary.ifsc+`', '`+beneficiary.name+`', '`+beneficiary.bankname+`')"><i class="fa fa-paper-plane"></i> Send</button>`;
                
                /*if(beneficiary.verified == "1"){
                    out +=`<button class="btn btn-info btn-xs legitRipple" onclick="otpVerify('`+data.data.mobile+`', '`+beneficiary.accno+`'')"><i class="fa fa-check"></i> Verify</button>`;
                }*/
                out +=`</td>
                    </tr>`;
            });
            $('.transaction').find('tbody').html(out);
        }else{
            $('.transaction').find('tbody').html('');
        }
    }

    function getName(mobile, name, benebank, beneaccount, beneifsc, benename, benemobile,type) {
        swal({
            title: 'Are you sure ?',
            text: "You want verify account details, it will charge.",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: "Yes Verify",
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !swal.isLoading(),
            preConfirm: () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: "{{route('dmt1pay')}}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        data: {
                            'type':"accountverification",
                            'mobile':mobile,
                            "beneaccount":beneaccount,
                            "beneifsc":beneifsc,
                            "name":name,
                            "benebank":benebank,
                            "benename":benename,
                            "benemobile":benemobile
                        },
                        success: function(data){
                            swal.close();
                            if(data.statuscode == "IWB"){
                                notify(data.status , 'warning');
                            }else if (data.statuscode == "TXN") {
                                if(type == "add"){
                                    $( "#beneficiaryForm" ).find('input[name="benename"]').val(data.data.benename);
                                    $( "#beneficiaryForm" ).find('input[name="benename"]').blur();
                                    notify("Success! Account details found", 'success', "inline", $( "#beneficiaryForm" ));
                                }else{
                                    swal(
                                        'Account Verified',
                                        "Account Name is - "+ data.data.benename,
                                        'success'
                                    );
                                }
                            }else {
                                if(type == "add"){
                                    notify(data.status, 'danger', "inline", $( "#beneficiaryForm" ));
                                }else{
                                    swal('Oops!', data.status,'error');
                                }
                            }
                        },
                        error: function(errors){
                            swal.close();
                            showError(errors, 'withoutform');
                        }
                    });
                });
            },
        });
    }

    function sendMoney(mobile, name, benebank, beneid, beneaccount, beneifsc, benename, benemobile, bankname) {
        console.log('beneid');
        console.log(beneid);
        $('#transferForm').find('input[name="mobile"]').val(mobile);
        $('#transferForm').find('input[name="name"]').val(name);
        $('#transferForm').find('input[name="benebank"]').val(benebank);
        $('#transferForm').find('input[name="beneaccount"]').val(beneaccount);
        $('#transferForm').find('input[name="beneifsc"]').val(beneifsc);
        $('#transferForm').find('input[name="benename"]').val(benename);
        $('#transferForm').find('input[name="benemobile"]').val(benemobile);
        $('#transferForm').find('input[name="beneid"]').val(beneid);

        $('#transferForm').find('.benename').text(benename);
        $('#transferForm').find('.beneaccount').text(beneaccount);
        $('#transferForm').find('.beneifsc').text(beneifsc);
        $('#transferForm').find('.benebank').text(bankname);
        $('#transferModal').modal();
    }

    function otpVerify(mobile, beneaccount, benemobile){
        $.ajax({
                url: "{{route('dmt1pay')}}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data: {'mobile':mobile, 'type':"otp"},
                beforeSend:function(){
                    swal({
                        title: 'Wait!',
                        text: 'We are processing your request.',
                        allowOutsideClick: () => !swal.isLoading(),
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });
                },
                success: function(result){
                    swal.close();
                    if(result.statuscode == "TXN"){
                        $('#otpModal').find("[name='mobile']").val(mobile);
                        $('#otpModal').find("[name='beneaccount']").val(beneaccount);
                        $('#otpModal').find("[name='benemobile']").val(benemobile);
                        $('#otpModal').modal();
                    }else{
                        notify(data.message, 'danger', "inline",form);
                    }
                },
                error: function(error){
                    swal.close();
                    notify("Something went wrong", 'danger', "inline",form);
                }
            });
    }
</script>
@endpush