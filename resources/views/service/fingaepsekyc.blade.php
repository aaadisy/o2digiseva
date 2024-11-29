@extends('layouts.app')
@section('title', "Icici-Bank Aeps")

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-7">
            <h4 class="page-title">Icici-Aeps</h4>
            <p class="page-title-alt text-inverse"> <b> Welcome to Icici Bank Aeps EKYC Services!</b></p>
        </div>
        <div class="col-md-5">
            <h4>
                <span class="pull-left" style="padding: 12px;">Powered By -</span>
                <img src="{{asset('assets/icici_logo.jpeg')}}" class="img-responsive" width="300px">
            </h4>
        </div>
    </div>
    @php
    $currentDate = now()->toDateString();

    @endphp

    @if($agent && $agent->status == 'approved' && $agent->status == '1')
   
    @elseif($agent && $agent->status != 'approved')
    <div class="col-md-12" id="re">
        <div class="panel panel-primary text-center">
            <div class="panel-body">
                <h3 class="text-danger text-center">Your Kyc Approval is {{$agent->status ?? 'Pending'}} , Remark -{{$agent->remark ?? ''}} </h3>
                @if (isset($aepsdata->status) && $aepsdata->status == "rejected")
                <a href="{{url('aeps/kyc/action')}}?id={{$agent->id ?? ''}}&type=re" class="btn btn-primary" style="margin:auto">Resubmit Kyc</a>
                @endif
            </div>
        </div>
    </div>

    @else
    <div class="row">
        <div class="col-sm-8 col-md-offset-2">
            <form action="{{route('ifaepstransaction') ?? ''}}" method="post" id="fingkycForm" enctype="multipart/form-data" novalidate="">
                <input type="hidden" name="transactionType" value="ekycsendotp">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">EYC Send OTP</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">

                            <input type="hidden" name="id" value="{{ $agent->id ?? ''}}" required />
                            <input type="hidden" name="biodata">
                            <input type="hidden" name="primaryKeyId" value="" class="primaryKeyId" />
                            <input type="hidden" name="encodeFPTxnId" value="" class="encodeFPTxnId" />

                            <div class="form-group col-md-12 firstdiv">
                                <label>Mobile </label>
                                <input readonly type="text" class="form-control" autocomplete="off" value="{{ $agent->merchantPhoneNumber ?? ''}}" name="merchantPhoneNumber" placeholder="Enter Your Mobile Number" required>
                            </div>
                        </div>
                        <div class="row seconddiv" id="biometric">
                        <div class="form-group col-md-12">
                                <label>Device Type :</label>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MORPHO_PROTOBUF" id="MORPHO_PROTOBUF" name="device" checked="">
                                            <label for="MORPHO_PROTOBUF" style="padding: 0 25px">MORPHO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MORPHO_PROTOBUF_SSL" id="MORPHO_PROTOBUF_SSL" name="device">
                                            <label for="MORPHO_PROTOBUF_SSL" style="padding: 0 25px">MORPHO SSL</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MANTRA_PROTOBUF" id="MANTRA_PROTOBUF" name="device">
                                            <label for="MANTRA_PROTOBUF" style="padding: 0 25px">MANTRA</label>
                                        </div>
                                    </div>
                                    
                                     <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio"  value="MORPHO_PROTOBUF_L1WS" id="MORPHO_PROTOBUF_L1WS" name="device">
                                            <label for="MORPHO_PROTOBUF_L1WS" style="padding: 0 25px">MORPHO L1</label>
                                        </div>
                                    </div>
                                     <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio"  value="MORPHO_PROTOBUF_L1" id="MORPHO_PROTOBUF_L1" name="device">
                                            <label for="MORPHO_PROTOBUF_L1" style="padding: 0 25px">MORPHO L1 SSL</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="row firstdiv">
                            <div class="form-group col-md-6">
                                <label>Pancard Number</label>
                                <input readonly type="text" class="form-control" value="{{ $agent->userPan ?? ''}}" name="userPan" autocomplete="off" placeholder="Enter Your Pancard Address" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Aadhar Number</label>
                                <input readonly type="text" class="form-control" value="{{ $agent->merchantAadhar ?? ''}}" name="merchantAadhar" autocomplete="off" placeholder="Enter Your Aadhar Number" required>
                            </div>
                        </div>

                        <div class="row firstdiv" id="otpdiv">
                            <div class="form-group col-md-12">
                                <label>OTP </label>
                                <input  type="number" class="form-control" autocomplete="off" value="" name="otp" placeholder="Enter OTP">
                            </div>
                        </div>


                        <div class="panel-footer text-center">

                            <button type="button" class="btn btn-warning btn-lg waves-effect waves-light seconddiv" id="scan">Scan</button>
                            <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>


    </div>
    @endif
</div>


@endsection

@push('style')
<style type="text/css">
    .md-radio:before {
        content: "";
    }
</style>
@endpush

@push('script')
<script>
    $(document).on('keypress', '#typeyourid', function(e) {
        if ($(e.target).prop('value').length >= 10) {
            if (e.keyCode != 32) {
                return false
            }
        }
    })

    $(document).on('keypress', '#aadharcardnum', function(e) {
        if ($(e.target).prop('value').length >= 12) {
            if (e.keyCode != 32) {
                return false
            }
        }
    })
</script>
<script type="text/javascript" src="{{asset('')}}/assets/js/jquery.validate.min.js"></script>
<script src="{{ asset('/assets/js/jQuery.print.js') }}"></script>

<script type="text/javascript">
    var ROOT = "http://digiseva.me/",
        SYSTEM;

    $(document).ready(function() {
        $.fn.extend({
            myalert: function(value, type, time = 5000) {
                var tag = $(this);
                tag.find('.myalert').remove();
                tag.append('<p id="" class="myalert text-' + type + '">' + value + '</p>');
                tag.find('input').focus();
                tag.find('select').focus();
                setTimeout(function() {
                    tag.find('.myalert').remove();
                }, time);
                tag.find('input').change(function() {
                    if (tag.find('input').val() != '') {
                        tag.find('.myalert').remove();
                    }
                });
                tag.find('select').change(function() {
                    if (tag.find('select').val() != '') {
                        tag.find('.myalert').remove();
                    }
                });
            },

            mynotify: function(value, type, time = 5000) {
                var tag = $(this);
                tag.find('.mynotify').remove();
                tag.prepend(`<div class="mynotify alert alert-` + type + ` alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            ` + value + `
                        </div>`);
                setTimeout(function() {
                    tag.find('.mynotify').remove();
                }, time);
            }
        });

        SYSTEM = {

            COMPLAINT: function(id, type) {
                $('#complaintEditForm').find("[name='product']").val(type);
                $('#complaintEditForm').find("[name='transaction_id']").val(id);
                $('#complaintModal').modal();
            },


            DEFAULT: function() {
                SYSTEM.GETBALANCE();
                SYSTEM.EVENTSOURCE();
                SYSTEM.PLUGININIT();
                SYSTEM.DATAFILTER();
                SYSTEM.COMPLAINTBEFORESUBMIT();

                $(".modal").on('hidden.bs.modal', function() {
                    if ($(this).find('[type="hidden"]').length) {
                        $(this).find('[name="id"]').val(null);
                    }
                    if ($(this).find('form').length) {
                        $(this).find('form')[0].reset();
                    }

                    if ($(this).find('.select').length) {
                        $(this).find('.select').val(null).trigger('change');
                    }

                    $(this).find('form').find('p.error').remove();
                });

                $('#moreDataFilter').click(function() {
                    $('#moreFilter').slideToggle('fast', 'swing');
                    if ($('#moreDataFilter').find('.fa-arrow-down')) {
                        $('#moreDataFilter').find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
                    } else if ($('#moreDataFilter').find('.fa-arrow-up')) {
                        $('#moreDataFilter').find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');
                    }
                });

                $('#formReset').click(function() {
                    $('form#dataFilter')[0].reset();
                    $('input[name="from_date"]').datepicker().datepicker("setDate", new Date());
                    $('form#dataFilter').find('select').select2().val(null).trigger('change');
                    $('#datatable').dataTable().api().ajax.reload();
                });
            },

            EVENTSOURCE: function() {
                if (typeof(EventSource) !== "undefined") {
                    var source = new EventSource(ROOT + "/home/mydata");
                    source.onmessage = function(event) {
                        var data = jQuery.parseJSON(event.data);
                        $('.fundCount').text(data.fund);
                        $('.aepsFundCount').text(data.aepsfund);
                        $('.totalfundCount').text(parseInt(data.fund) + parseInt(data.aepsfund));

                        $('.uti').text(data.uti);
                        $('.utiid').text(data.utiid);
                        $('.totaltransCount').text(parseInt(data.uti) + parseInt(data.utiid));

                        $('.worthbal').text(data.worthbal);
                        $('.rechbal').text(data.rechbal);
                        if (data.session == 0) {
                            window.location.href = "https://connect.spindiapay.com/auth/logout";
                        }
                    };
                }
            },

            NOTIFY: function(type, title, message) {
                swal({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1110000,
                    type: type,
                    title: title,
                    text: message
                });
            },

            VALIDATE: function(type, value) {
                switch (type) {
                    case 'empty':
                        if (value.val() == '') {
                            value.closest('.form-group').myalert('Enter Value', 'danger');
                            return false;
                        } else {
                            return true;
                        }
                        break;

                    case 'numeric':
                        if (value.val().match(/[^0-9]/g)) {
                            value.closest('.form-group').myalert('Value should be numeric', 'danger');
                            return false;
                        } else {
                            return true;
                        }
                        break;
                }
            },

            PLUGININIT: function() {
                $('.select').select2();
                $('.date').datepicker({
                    'autoclose': true,
                    'clearBtn': true,
                    'todayHighlight': true,
                    'format': 'dd-M-yyyy'
                });
            },

            GETBALANCE: function() {
                $.ajax({
                    url: ROOT + "/home/balance",
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(result) {
                        $('span.mbalance').text(result.balance);
                        $('span.aepsbalance').text(result.aepsbalance);
                    }
                });
            },
            COMPLAINTBEFORESUBMIT: function() {
                $('#complaintEditForm').submit(function() {
                    var user_id = $("[name='user_id']").val();
                    var subject = $("[name='subject']").val();
                    var description = $("[name='description']").val();
                    var product = $("[name='product']").val();
                    var transaction_id = $("[name='transaction_id']").val();

                    if (subject == "") {
                        $("[name='subject']").closest('.form-group').myalert('Please enter subject', 'danger');
                    } else if (description == "") {
                        $("[name='description']").closest('.form-group').myalert('Please enter description', 'danger');
                    } else {
                        SYSTEM.FORMSUBMIT($('#complaintEditForm'), function(data) {
                            if (!data.statusText) {
                                if (data.status == "success") {
                                    $('#complaintModal').modal('hide');
                                    $('#complaintEditForm')[0].reset();
                                    DT.draw();
                                    SYSTEM.GETBALANCE();
                                    SYSTEM.NOTIFY('success', 'Success', 'Complaint registered successfully!')
                                } else {
                                    SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                                }
                            } else {
                                SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                            }
                        });
                    }

                    return false;
                });
            },

            FORMSUBMIT: function(form, callback) {
                form.ajaxSubmit({
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSubmit: function() {
                        form.find('button[type="submit"]').button('loading');
                    },
                    complete: function() {
                        form.find('button[type="submit"]').button('reset');
                    },
                    success: function(data) {
                        callback(data);
                    },
                    error: function(errors) {
                        callback(errors);
                    }
                });
            },

            AJAX: function(url, data, callback) {
                $.ajax({
                    url: url,
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    data: data,
                    beforeSend: function() {
                        swal({
                            title: 'Wait!',
                            text: 'Please wait, we are working on your request',
                            onOpen: () => {
                                swal.showLoading()
                            }
                        });
                    },
                    success: function(data) {
                        swal.close();
                        callback(data);
                    },
                    error: function(errors) {
                        swal.close();
                        callback(errors);
                    }
                });
            },

            SHOWERROR: function(errors, form, type = "inline") {
                if (type == "inline") {
                    if (errors.statusText) {
                        if (errors.status == 422) {
                            form.find('p.error').remove();
                            $.each(errors.responseJSON, function(index, value) {
                                form.find('[name="' + index + '"]').closest('div.form-group').myalert(value, 'danger');
                            });
                        } else if (errors.status == 400) {
                            form.mynotify(errors.responseJSON.message, 'danger');
                        } else {
                            form.mynotify(errors.statusText, 'danger');
                        }
                    } else {
                        form.mynotify(errors.message, 'danger');
                    }
                } else {
                    if (errors.statusText) {
                        if (errors.status == 400) {
                            SYSTEM.NOTIFY('error', 'Oops', errors.responseJSON.message);
                        } else {
                            SYSTEM.NOTIFY('error', 'Oops', errors.statusText);
                        }
                    } else {
                        SYSTEM.NOTIFY('error', 'Oops', errors.message);
                    }
                }
            },

            DATAFILTER: function() {
                $('#dataFilter').submit(function() {
                    DT.draw();
                    return false;
                });

                $('#formReset').click(function() {
                    $('#dataFilter')[0].reset();
                    DT.draw();
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        SYSTEM.DEFAULT();
    });
</script>


<script type="text/javascript">
    var USERSYSTEM, STOCK = {},
        DT = !1,
        AEPSURL = "{{route('ifaepstransaction')}}";

    $(document).ready(function() {

        $('#otpdiv').hide();
        $('.seconddiv').hide();
        USERSYSTEM = {
            DEFAULT: function() {
                USERSYSTEM.TRANSACTION();
                USERSYSTEM.ONBOARD();

                $(".PAY").show();
                $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                $(".CASH").hide();
                $(".CASH").find("select").removeAttr('name').removeAttr('required');

                $('[name="transactionType"]').on('change', function() {
                    if ($('[name="transactionType"]:checked').val() == "CW" || $('[name="transactionType"]:checked').val() == "M") {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                        var out = `<div class="form-group col-md-6">
                                            <label>Amount :</label>
                                            <input type="number" class="form-control" name="transactionAmount" autocomplete="off" placeholder="Enter Amount">
                                        </div>`;

                        $('#transactionData').append(out);
                    } else {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                    }
                });

                $('[name="transactionType"]').on('change', function() {
                    if ($('[name="transactionType"]:checked').val() != "M") {
                        $(".CASH").show();
                        $(".CASH").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                        $(".PAY").hide();
                        $(".PAY").find("select").removeAttr('name').removeAttr('required');
                    } else {
                        $(".PAY").show();
                        $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                        $(".CASH").hide();
                        $(".CASH").find("select").removeAttr('name').removeAttr('required');
                    }
                });

                $('#print').click(function() {
                    $('#receipt').find('.modal-body').print();
                });

                $('#statementprint').click(function() {
                    $('#ministatement').find('.modal-body').print();
                });

                $('#scan').click(function() {
                    var device = $('#fingkycForm').find('[name="device"]:checked').val();
                    if(device == 'MORPHO_PROTOBUF_L1'){
                        Capture();
                    }
                    else if(device == 'MORPHO_PROTOBUF_L1WS'){
                        CaptureWS();
                    }else{
                        USERSYSTEM.RDSERVICE(device, "11100");
                    }
                });
            },

            ONBOARD: function() {
                $("#fingkycForm").validate({
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
                    errorPlacement: function(error, element) {
                        if (element.prop("name") === "bank") {
                            error.insertAfter(element.closest(".form-group").find(".select2"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        var form = $(form);
                        SYSTEM.FORMSUBMIT(form, function(data) {
                            
                            if (!data.statusText) {
                                if (data.status == "success") {
                                    console.log('hi');
                                    if (data.txntype == 'ekycsendotp') {
                                        var inputElement = $('input[name="transactionType"]');
                                        // Set the value of the input element
                                        inputElement.val('ekycvalidateotp');
                                        $('input[name="primaryKeyId"]').val(data.data.primaryKeyId);
                                        $('input[name="encodeFPTxnId"]').val(data.data.encodeFPTxnId);
                                        $('#otpdiv').show();
                                        swal({
                                            type: "success",
                                            title: "Success",
                                            text: "OTP Sent successfully Please fill OTP and Proceed",

                                            onClose: () => {
                                                //window.location.reload();
                                            }
                                        });
                                    }

                                    if (data.txntype == 'ekycvalidateotp') {
                                        var inputElement = $('input[name="transactionType"]');
                                        // Set the value of the input element
                                        inputElement.val('biometric');
                                        $('input[name="primaryKeyId"]').val(data.data.primaryKeyId);
                                        $('input[name="encodeFPTxnId"]').val(data.data.encodeFPTxnId);
                                        $('.firstdiv').hide();
                                        $('.seconddiv').show();
                                        swal({
                                            type: "success",
                                            title: "Success",
                                            text: "OTP Verified successfully Please Validate Biometric Now",

                                            onClose: () => {
                                                //window.location.reload();
                                            }
                                        });
                                    }

                                    if (data.txntype == 'biometric') {
                                        swal({
                                            type: "success",
                                            title: "Success",
                                            text: "EKYC Successfull",

                                            onClose: () => {
                                                window.location.reload();
                                            }
                                        });
                                    }




                                } else if (data.status == "pending") {
                                    swal({
                                        type: "warning",
                                        title: "Pending",
                                        text: "User onboard pending",
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                } else {
                                    console.log(data);
                                    swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    // swal('Failed', data.message, 'error');
                                }
                            } else {
                                SYSTEM.SHOWERROR(data, form);
                            }
                        });
                    }
                });
            },

            TRANSACTION: function() {
                $("#transactionForm").validate({
                    rules: {
                        mobileNumber: {
                            required: true,
                            minlength: 10,
                            number: true,
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
                            min: "Minimum transaction amount should be 10"
                        },
                        nationalBankIdentificationNumber: "Please select bank"
                    },
                    errorElement: "p",
                    errorPlacement: function(error, element) {
                        if (element.prop("name") === "nationalBankIdentificationNumber") {
                            error.insertAfter(element.closest(".form-group").find("span.select2"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(element) {
                        var form = $(element);
                        var scan = form.find('[name="biodata"]').val();
                        if (scan != '') {
                            SYSTEM.FORMSUBMIT($('#transactionForm'), function(data) {

                                if (!data.statusText) {
                                    form.find('[name="biodata"]').val(null);
                                    if (data.status == "success" || data.status == "pending") {
                                        if (data.transactionType == "AUO") {
                                            form[0].reset();
                                            swal({
                                                type: "success",
                                                title: "Success",
                                                text: data.message,
                                                showCancelButton: true,
                                                onClose: () => {
                                                    window.location.reload();
                                                }
                                            });
                                        }

                                        if (data.transactionType != "MS") {
                                            form[0].reset();
                                            swal({
                                                title: data.title,
                                                text: data.message + ", Remaining Balance - " + data.balance,
                                                type: 'success',
                                                showCancelButton: true,
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#456b8c',
                                                confirmButtonText: 'Print Invoice',
                                                cancelButtonText: 'Close',
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                allowEnterKey: false,
                                                timer: 1110000
                                            }).then((result) => {
                                                if (result.value) {

                                                    if (data.transactionType == "CW" || data.transactionType == "M") {
                                                        $(".cash").show();
                                                    } else {
                                                        $('.cash').hide();
                                                    }

                                                    // $('#receipt').find('.created_at').text(data.created_at);
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
                                        } else {
                                            $('#ministatement').find('.rrn').text(data.rrn);
                                            $('#ministatement').find('.bank').text(data.bank);
                                            $('#ministatement').find('.balance').text(data.balance);
                                            $('#ministatement').find('.title').text(data.title);
                                            var trdata = '';
                                            $.each(data.data, function(index, val) {
                                                if (val.txnType == "Cr") {
                                                    trdata += `<tr>
                                                                <td>` + val.date + `</td>
                                                                <td>` + val.narration + `</td>
                                                                <td>` + val.amount + `</td>
                                                                <td></td>
                                                            </tr>`;
                                                } else {
                                                    trdata += `<tr>
                                                                <td>` + val.date + `</td>
                                                                <td>` + val.narration + `</td>
                                                                <td></td>
                                                                <td>` + val.amount + `</td>
                                                            </tr>`;
                                                }
                                            });
                                            $('#ministatement').find('.statementData').html(trdata);
                                            $('#ministatement').modal();
                                        }
                                    } else {
                                        swal({
                                            type: "error",
                                            title: "Failed",
                                            text: data.message,
                                            showCancelButton: true,
                                            timer: 1110000,
                                            onClose: () => {
                                                //window.location.reload();
                                            }
                                        });
                                        return false;
                                        //  swal('Failed', data.message, 'error');
                                    }
                                } else {
                                    //  SYSTEM.SHOWERROR(data, $('#transactionForm'));
                                    form.find('[name="biodata"]').val(null);
                                    // swal('Failed', data.message, 'error');

                                    swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });

                                    console.log(data);
                                }
                            });
                        } else {
                            form.find('[name="biodata"]').val(null);
                            form.mynotify("Please scan your finger", 'danger');
                        }
                    }
                });
            },

            RDSERVICE: function(device, port) {
                var primaryUrl = "http://127.0.0.1:" + port;
                if(device == "MORPHO_PROTOBUF_SSL")
                {
                    primaryUrl = "https://localhost:" + port;
                }

                $.ajax({
                    type: "RDSERVICE",
                    async: true,
                    crossDomain: true,
                    url: primaryUrl,
                    processData: false,
                    beforeSend: function() {
                        swal({
                            title: 'Scanning',
                            text: 'Please wait, device getting initiated',
                            onOpen: () => {
                                swal.showLoading()
                            },
                            allowOutsideClick: () => !swal.isLoading()
                        });
                    },
                    success: function(data) {
                        swal.close();
                        var $doc = $.parseXML(data);
                        var CmbData1 = $($doc).find('RDService').attr('status');
                        var CmbData2 = $($doc).find('RDService').attr('info');

                        if (!CmbData1) {
                            var CmbData1 = $(data).find('RDService').attr('status');
                            var CmbData2 = $(data).find('RDService').attr('info');
                        }

                        if (CmbData1 == "READY") {
                            USERSYSTEM.CAPTURE(device, port);
                        } else if (CmbData1 == "NOTREADY" && CmbData2 == "Mantra Authentication Vendor Device Manager") {
                            USERSYSTEM.RDSERVICE(device, "11101");
                        } else {
                            notify("Device : " + CmbData1, 'danger');
                            SYSTEM.NOTIFY('error', 'Oops', "Device : " + CmbData1);
                        }
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        swal.close();
                        if (port == "11100") {
                            USERSYSTEM.RDSERVICE(device, "11101");
                        } else {
                            SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                        }
                    },
                });
            },

            CAPTURE: function(device, port) {
                var primaryUrl = "http://127.0.0.1:" + port;
                if (device == "MANTRA_PROTOBUF") {
                    var url = primaryUrl + "/rd/capture";
                } else {
                    var url = primaryUrl + "/capture";

                }

                if(device == "MORPHO_PROTOBUF_SSL")
                {
                    primaryUrl = "https://localhost:" + port;
                     url = primaryUrl + "/capture";
                }

                if (device == "MANTRA_PROTOBUF") {

                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    } else {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh="E0jzJ/P8UopUHAieZn8CKqS4WPMi5ZSYXgfnlfkWjrc="/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    }
                } else {
                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
                    } else {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"E0jzJ/P8UopUHAieZn8CKqS4WPMi5ZSYXgfnlfkWjrc=\" posh=\"\"/>' + '</PidOptions>';
                    }
                }

                $.ajax({
                    type: "CAPTURE",
                    async: true,
                    crossDomain: true,
                    url: url,
                    data: XML,
                    contentType: "text/xml; charset=utf-8",
                    processData: false,
                    beforeSend: function() {
                        swal({
                            text: 'Please put any of your finger on device',
                            imageUrl: '{{asset("")}}assets/images/capute.gif',
                            showConfirmButton: false,
                            allowOutsideClick: () => false
                        });
                    },
                    success: function(data) {
                        swal.close();
                        if (device == "MANTRA_PROTOBUF") {
                            var $doc = $.parseXML(data);
                            var errorInfo = $($doc).find('Resp').attr('errInfo');


                            if (errorInfo == 'Success.' || errorInfo == 'Success') {
                                SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
                                $('[name="biodata"]').val(data);
                            } else {
                                SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                            }
                        } else {
                            var errorInfo = $(data).find('Resp').attr('errInfo');
                            var errorCode = $(data).find('Resp').attr('errCode');
                            var mydata = $(data).find('PidData').html();
                            if (errorCode == '0') {
                                SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
                                $('[name="biodata"]').val("<PidData>" + mydata + "</PidData>");
                            } else {
                                SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                            }
                        }
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        swal.close();
                        SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                    },
                });
            }
        }

        USERSYSTEM.DEFAULT();
    });
</script>

<script>
var count=0;





function Capture()
{

  var url = "https://localhost:11100/capture";

   var PIDOPTS='<PidOptions ver=\"1.0\">'+'<Opts env="P" fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
   
 
   /*
   format=\"0\"     --> XML
   format=\"1\"     --> Protobuf
   */
 var xhr;
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");

			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
			{
				//IE browser
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				//other browser
				xhr = new XMLHttpRequest();
			}
        
        xhr.open('CAPTURE', url, true);
		xhr.setRequestHeader("Content-Type","text/xml");
		xhr.setRequestHeader("Accept","text/xml");

        xhr.onreadystatechange = function () {
		//if(xhr.readyState == 1 && count == 0){
		//	fakeCall();
		//}
if (xhr.readyState == 4){
            var status = xhr.status;
            //parser = new DOMParser();
            if (status == 200) {
            var test1=xhr.responseText;
            var test2=test1.search("errCode");
			var test6=getPosition(test1, '"', 4);
			var test4=test2+9;
			var test5=test1.slice(test4, test6);
			if (test5>0)
			{
			//alert("XXX Capture Unsuccessful XXX");
            alert(xhr.responseText);
			//document.getElementById('text').value = xhr.responseText;
			}
			else
			{
			SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
			 $('[name="biodata"]').val(xhr.response);
                                console.log('click on btn 2');
                                $('#proceed').trigger('click');
			//document.getElementById('text').value = "Captured Successfully";
			}


            } else 
            {
                
	            console.log(xhr.response);

            }
			}

        };

        xhr.send(PIDOPTS);
	
}



function getPosition(string, subString, index) {
  return string.split(subString, index).join(subString).length;
}

function CaptureWS()
{

  var url = "http://127.0.0.1:11101/capture";

   var PIDOPTS='<PidOptions ver=\"1.0\">'+'<Opts env="P" fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
   
 
   /*
   format=\"0\"     --> XML
   format=\"1\"     --> Protobuf
   */
 var xhr;
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");

			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
			{
				//IE browser
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				//other browser
				xhr = new XMLHttpRequest();
			}
        
        xhr.open('CAPTURE', url, true);
		xhr.setRequestHeader("Content-Type","text/xml");
		xhr.setRequestHeader("Accept","text/xml");

        xhr.onreadystatechange = function () {
		//if(xhr.readyState == 1 && count == 0){
		//	fakeCall();
		//}
if (xhr.readyState == 4){
            var status = xhr.status;
            //parser = new DOMParser();
            if (status == 200) {
            var test1=xhr.responseText;
            var test2=test1.search("errCode");
			var test6=getPositionWS(test1, '"', 4);
			var test4=test2+9;
			var test5=test1.slice(test4, test6);
			if (test5>0)
			{
			//alert("XXX Capture Unsuccessful XXX");
            alert(xhr.responseText);
			//document.getElementById('text').value = xhr.responseText;
			}
			else
			{
			SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
			 $('[name="biodata"]').val(xhr.response);
                                console.log('click on btn without SSL ');
                                $('#proceed').trigger('click');
			//document.getElementById('text').value = "Captured Successfully";
			}


            } else 
            {
                
	            console.log(xhr.response);

            }
			}

        };

        xhr.send(PIDOPTS);
	
}

function getPositionWS(string, subString, index) {
  return string.split(subString, index).join(subString).length;
}


</script>
@endpush