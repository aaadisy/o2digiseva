@extends('layouts.app')
@section('title', "2fA Icici-Bank Aeps")

@section('content')
<div class="container">
    <div class="row panel panel-default panel-heading">
       
        <img src="{{asset('assets/2fabanner.png')}}" class="img-responsive" style="width:100%;height:324px;">
        
        
    </div>
    <div class="row">
       

        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                <h3 class="panel-title">
    @if(Request::segment(2) == 'cw2fa')
        2FA Authentication For Cash Withdrawal<span class="customer_name m-l-15 text-capitalize"></span>
    @elseif(Request::segment(2) == 'ap2fa')
        2FA Authentication For Aadhar Pay<span class="customer_name m-l-15 text-capitalize"></span>
    @else
        <!-- Your default content if none of the conditions are met -->
        Default Text
    @endif
</h3>
   </div>
                <div class="panel-body">
                    <form action="{{route('ifaepstransaction')}}" method="post" id="transactionForm">
                        {{ csrf_field() }}
                        <input type="hidden" name="type" value="transaction">
                        <input type="hidden" name="agree" value="true">
                        <input type="hidden" name="biodata">
                        <div class="row" >
                            <div class="form-group col-md-12" style="display:none;">
                                <label>Authentication Type :</label>
                                <div class="row">
                                <input autocomplete="off" type="hidden" value="AUO" id="AUO" name="transactionType">
                                <input autocomplete="off" type="hidden" value="508505" id="nationalBankIdentificationNumber" name="nationalBankIdentificationNumber">

                                
                                   @if(request()->segment(2) != 'ap2fa')        
                                    <div class="col-md-6">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="AEPS" id="AEPS" name="auth_type"  checked="">
                                            <label for="AEPS" style="padding: 0 25px">Authentication For AEPS</label>
                                        </div>
                                    </div>
                                    @endif
                                    @if(request()->segment(2) == 'ap2fa')       
                                    <div class="col-md-6">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="AP" id="AP"  @if(request()->segment(2) == 'ap2fa')  checked="" @endif name="auth_type">
                                            <label for="AP" style="padding: 0 25px">Authentication For Aadhar Pay</label>
                                        </div>
                                    </div>
                                    @endif

                                   
                                </div>
                            </div>
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
 <div class="row" style="display:none;">
                            <div class="form-group col-md-6">
                                <label>Mobile Number :</label>
                                <input type="number" class="form-control" name="mobileNumber" id="typeyourid" autocomplete="off" placeholder="Enter mobile number" pattern="\d{10}" maxlength="10" value="{{ \Auth::user()->mobile }}">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Aadhar Number :</label>
                                <input type="text" id="aadharcardnum" class="form-control" name="adhaarNumber" autocomplete="off" placeholder="Enter aadhar number" value="{{ \Auth::user()->aadharcard }}">
                            </div>
                        </div>
                            </div>
                        </div>

                       

                       

                        <div class="form-group text-center col-md-12 m-b-0">
                            <button type="button" class="btn btn-warning btn-lg waves-effect waves-light" id="scan">Scan</button>
                            <button id="proceed" type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
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
            <div class="modal-body p-0">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="clearfix">
                            <div class="pull-left">

                                @if ($company->logo)
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <img src="{{asset('')}}public/logos/{{$company->logo}}" class=" img-responsive" alt="" style="width: 220px;height: 133px;">
                                    </div>
                                    <div class="pull-right">
                                        <img src="{{asset('')}}public/axis.png" class="img-responsive" style="height: 120px; width: 300px;">
                                    </div>
                                </div>
                                @else
                                <h4>{{$company->name}}</h4>
                                @endif

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
                                        {{Auth::user()->company->name}}<br>
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
                                <a href="javascript:void(0)" id="print" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
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
                            <div class="pull-left">

                                @if ($company->logo)

                                <div class="clearfix">
                                    <div class="pull-left">
                                        <img src="{{asset('')}}public/logos/{{$company->logo}}" class=" img-responsive" alt="" style="width: 220px;height: 133px;">
                                    </div>
                                    <div class="pull-right">
                                        <img src="{{asset('')}}public/axis.png" class="img-responsive" style="height: 120px; width: 300px;">
                                    </div>
                                </div>

                                @else
                                <h4>{{$company->name}}</h4>

                                @endif

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
                                        {{Auth::user()->company->name}}<br>
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
                                <a href="javascript:void(0)" id="statementprint" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
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

        
        USERSYSTEM = {
            DEFAULT: function() {
                USERSYSTEM.TRANSACTION();

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
                    var device = $('#transactionForm').find('[name="device"]:checked').val();
                    
                    if(device == 'MORPHO_PROTOBUF_L1'){
                        Capture();
                    }
                    else if(device == 'MORPHO_PROTOBUF_L1WS'){
                        CaptureWS();
                    }
                    else{
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
                                if (data.status == "TXN") {
                                    if ("{{ Request::segment(2) }}" === "cw2fa") {
                                                // Redirect to a specific route for 'CW'
                                                window.location.href = "{{ route('ifaeps', ['type' => 'aeps/CW']) }}";
                                            }
                                            else if ("{{ Request::segment(2) }}" === "ap2fa") {
                                                // Redirect to a specific route for 'CW'
                                                window.location.href = "{{ route('ifaeps', ['type' => 'aeps/AP']) }}";
                                            }
                                            else {
                                               
                                                window.location.reload();
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
                                    swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        onClose: () => {
                                            window.location.reload();
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
                        
                    },
                    messages: {
                        
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
                                        console.log(data);
                                        if (data.transactionType == "AUO") {
                                            form[0].reset();
                                            if ("{{ Request::segment(2) }}" === "cw2fa") {
                                                // Redirect to a specific route for 'CW'
                                                window.location.href = "{{ route('ifaeps', ['type' => 'aeps/CW']) }}";
                                            }
                                            
                                            else if ("{{ Request::segment(2) }}" === "ap2fa") {
                                                // Redirect to a specific route for 'CW'
                                                window.location.href = "{{ route('ifaeps', ['type' => 'aeps/AP']) }}";
                                            } else {
                                                // Reload the current window if the condition is not met
                                                window.location.reload();
                                            }
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
                                     swal('Failed', data.message, 'error');

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
                     primaryUrl = "https://127.0.0.1:" + port;
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
                }
                 else {
                    var url = primaryUrl + "/capture";

                }
                if(device == "MORPHO_PROTOBUF_SSL")
                {
                     primaryUrl = "https://127.0.0.1:" + port;
                     primaryUrl = "https://localhost:" + port;
                     url = primaryUrl + "/capture";
                }

                if (device == "MANTRA_PROTOBUF") {

                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    } else {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    }
                } else {
                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
                    } else {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
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
                                console.log('click on btn');
                                $('#proceed').trigger('click');
                                
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
                                console.log('click on btn 2');
                                $('#proceed').trigger('click');

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