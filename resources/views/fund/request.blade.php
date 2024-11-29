@extends('layouts.app')
@section('title', "Wallet Load Request")
@section('pagetitle',  "Wallet Load Request")

@php
    $table = "yes";

    $status['type'] = "Fund";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
        "approved" => "Approved",
        "rejected" => "Rejected",
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        @if ($banks)
            @foreach ($banks as $bank)
            <a href="javascript:void(0)" onclick="fundRequest({{$bank->id}})">
                <div class="col-sm-4">
                    <div class="panel border-left-lg border-left-success invoice-grid timeline-content">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h6 class="text-semibold no-margin-top">{{$bank->name}}</h6>
                                    <ul class="list list-unstyled">
                                        <li>IFSC : <span class="text-semibold">{{$bank->ifsc}}</span></li>
                                    </ul>
                                </div>

                                <div class="col-sm-6">
                                    <h6 class="text-semibold text-right no-margin-top">{{$bank->account}}</h6>
                                    <ul class="list list-unstyled text-right">
                                        <li>Branch : <span class="text-semibold">{{$bank->branch}}</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer panel-footer-condensed">
                            <div class="heading-elements">
                                <span class="heading-text no-margin-left">
                                    <i class="fa fa-long-arrow-right mr-10"></i><span class="text-semibold">Click here to make request </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        @endif
        
        @if(\Myhelper::company_details()->paytm_status == 'active')
         <div class="row">
                <a href="javascript:void(0)" onclick="onlinefundRequest()">
                <div class="col-sm-4">
                    <div class="panel border-left-lg border-left-success invoice-grid timeline-content">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    
                                    @if(\Myhelper::company_details()->paytm_status == 'active')
                                    <h6 class="text-semibold no-margin-top">PAYTM</h6>
                                    @endif
                                    </br>
                                    
                                </div>

                                
                            </div>
                        </div>
                        <div class="panel-footer panel-footer-condensed">
                            <div class="heading-elements">
                                <span class="heading-text no-margin-left">
                                    <i class="fa fa-long-arrow-right mr-10"></i><span class="text-semibold">Click here to make payment Online </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                </a>
                </div>
                @endif
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Wallet Load Request</h4>
                    <div class="heading-elements">
                        <button type="button" data-toggle="modal" data-target="#fundRequestModal" class="btn bg-slate btn-xs btn-labeled legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-plus2"></i></b> New Request</button>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Deposit Bank Details</th>
                            <th>Refrence Details</th>
                            <th>Amount</th>
                            <th>Remark</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="fundRequestModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Wallet Fund Request</h6>
            </div>
            <form id="fundRequestForm" action="{{route('fundtransaction')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="user_id">
                    <input type="hidden" name="type" value="request">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Deposit Bank</label>
                            <select name="fundbank_id" class="form-control select" id="select" required>
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}} ( {{$bank->account}} )</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Amount</label>
                            <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Payment Mode</label>
                            <select name="paymode" class="form-control select" id="select" required>
                                <option value="">Select Paymode</option>
                                @foreach ($paymodes as $paymode)
                                <option value="{{$paymode->name}}">{{$paymode->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Pay Date</label>
                            <input type="text" name="paydate" class="form-control mydate" placeholder="Select date">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ref No.</label>
                            <input type="text" name="ref_no" class="form-control" placeholder="Enter Refrence Number" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Pay Slip (Optional)</label>
                            <input type="file" name="payslips" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Remark</label>
                            <textarea name="remark" class="form-control" rows="2" placeholder="Enter Remark"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="onlinefundRequestModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Wallet Fund Online</h6>
            </div>
            <form id="onlinefundRequestForm" action="{{route('onlinefundtransaction')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="user_id">
                    <input type="hidden" name="type" value="request">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Payment Gateway</label>
                            <select name="fundbank_id" class="form-control select" id="select" required>
                                <option value="">Select Gateway</option>
                                @if(\Myhelper::company_details()->paytm_status == 'active')
                                <option value="paytm">Paytm</option>
                                @endif
                               
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Amount</label>
                            <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                        </div>
                        
                    </div>
                    
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
       
        var url = "{{url('statement/fetch')}}/fundrequest/0";
        var onDraw = function() {};
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<span class='text-inverse m-l-10'><b>`+full.id +`</b> </span><br>
                        <span style='font-size:13px'>`+full.updated_at+`</span>`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Name - `+full.fundbank.name+`<br>Account No. - `+full.fundbank.account+`<br>Branch - `+full.fundbank.branch;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    var slip = '';
                    if(full.payslip){
                        var slip = `<a target="_blank" href="{{asset('public')}}/deposit_slip/`+full.payslip+`">Pay Slip</a>`
                    }
                    return `Ref No. - `+full.ref_no+`<br>Paydate - `+full.paydate+`<br>Paymode - `+full.paymode+` ( `+slip+` )`;
                }
            },
            { "data" : "amount"},
            { "data" : "remark"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    var out = '';
                    if(full.status == "approved"){
                        out += `<label class="label label-success">Approved</label>`;
                    }else if(full.status == "pending"){
                        out += `<label class="label label-warning">Pending</label>`;
                    }else if(full.status == "rejected"){
                        out += `<label class="label label-danger">Rejected</label>`;
                    }

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $( "#fundRequestForm").validate({
            rules: {
                fundbank_id: {
                    required: true
                },
                amount: {
                    required: true
                },
                paymode: {
                    required: true
                },
                ref_no: {
                    required: true
                },
            },
            messages: {
                fundbank_id: {
                    required: "Please select deposit bank",
                },
                amount: {
                    required: "Please enter request amount",
                },
                paymode: {
                    required: "Please select payment mode",
                },
                ref_no: {
                    required: "Please enter transaction refrence number",
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
                var form = $('#fundRequestForm');
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
                            form.closest('.modal').modal('hide');
                            notify("Fund Request submitted Successfull", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
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
        
        $( "#onlinefundRequestForm").validate({
            rules: {
                fundbank_id: {
                    required: true
                },
                amount: {
                    required: true
                }
            },
            messages: {
                fundbank_id: {
                    required: "Please select Payment Gateway",
                },
                amount: {
                    required: "Please enter request amount",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            }
        });
    });

    function fundRequest(id = "none"){
        if(id != "none"){
            $('#fundRequestForm').find('[name="fundbank_id"]').select2().val(id).trigger('change');
        }
        $('#fundRequestModal').modal();
    }
    function onlinefundRequest(id = "none"){
        if(id != "none"){
            $('#onlinefundRequestForm').find('[name="fundbank_id"]').select2().val(id).trigger('change');
        }
        $('#onlinefundRequestModal').modal();
    }
</script>
@endpush