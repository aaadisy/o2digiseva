@extends('layouts.app')
@section('title', "Uti Pancard")
@section('pagetitle', "Uti Pancard")
@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title pull-left">Uti Pancard</h4>
                    <a class="btn bg-slate legitRipple pull-right" href="http://www.psaonline.utiitsl.com/psaonline/" target="_blank">Login UTI Portal</a>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body p-0">
                    <table class="table table-bordered">
                        <tr><td>1 Token</td><td>1 PAN Application</td></tr>
                        <tr><td>Username</td><td>{{($vledata) ? $vledata->vleid : ''}}</td></tr>
                        <tr><td>Password</td><td>{{($vledata) ? $vledata->vlepassword : ''}}</td></tr>
                    </table>
                </div>
                <form id="pancardForm" action="{{route('pancardpay')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="actiontype" value="purchase">
                    <div class="panel-body">
                        <div class="form-group">
                            <label>No Of Tokens</label>
                            <input type="number" class="form-control" name="tokens" placeholder="Enter No. of tokens" required="">
                        </div>
                        <div class="form-group">
                            <label>Total Price in Rs</label>
                            <input type="number" class="form-control" id = "price" value = "" readonly>
                        </div>
                        <div class="form-group">
                            <label>Vle Id</label>
                            <input type="text" class="form-control" name="vleid" value="{{($vledata) ? $vledata->vleid : ''}}" required="">
                        </div>
                    </div>
                    <div class="panel-footer text-center">
                        @if ($vledata && $vledata->status == "success")
                            <button type="submit" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Paying"><b><i class=" icon-paperplane"></i></b> Pay Now</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title pull-left">Recent Uti Pancard Token</h4>
                    @if (!$vledata)
                        <a class="btn bg-slate legitRipple pull-right" href="javascript:void(0)" data-toggle="modal" data-target="#addModal">Request For Vle-id</a>
                    @elseif ($vledata && $vledata->status != "success")
                        <button disabled="disabled" class="btn bg-danger pull-right">Utiid Request is {{$vledata->status}}, {{$vledata->remark}}</button>
                    @endif
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User Details</th>
                            <th>Transaction Details</th>
                            <th>Amount/Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Footer -->
<div class="footer text-muted">
    <div class="row">
        <div class="col-md-6">
            <h4><strong>Important T&amp;Cs:</strong></h4>
            <ul>
                <li>The fee for processing PAN application is ₹107 inclusive of GST.</li>
                <li>PAN card application can be processed using eKYC or physical documents.</li>
            </ul>
        </div>
        <div class="col-md-6 text-right">
            <div>Powered by</div>
            <img src="{{asset('')}}/assets/images/uti.png" style="position: relative;">
        </div>
    </div>
</div>
<!-- /footer -->

<div id="addModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">New Id Request</h4>
            </div>
            <form id="transferForm" method="post" action="{{ route('pancardpay') }}">
                <input type="hidden" name="actiontype" value="vleid">
                <div class="modal-body">
                    <div class="row">
                        {!! csrf_field() !!}
                        <div class="form-group col-md-6">
                            <label>Vle Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Value" value="{{Auth::user()->name}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Comtact Person</label>
                            <input type="text" class="form-control" name="contact_person" placeholder="Enter Value" value="{{Auth::user()->name}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter Value" value="{{Auth::user()->email}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mobile</label>
                            <input type="text" class="form-control" name="mobile" pattern="[0-9]*" maxlength="10" minlength="10" placeholder="Enter Value" value="{{Auth::user()->mobile}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Pancard</label>
                            <input type="text" class="form-control" name="pancard" required="" placeholder="Enter Value" value="{{Auth::user()->pancard}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Address</label>
                            <input type="text" class="form-control" name="location" placeholder="Enter Value" value="{{Auth::user()->city}}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>State</label>
                            <select placeholder="Select State" class="form-control select" name="state" required="">
                                <option value="">--Select State--</option>
                                <option value="1">ANDAMAN AND NICOBAR ISLANDS</option>
                                <option value="2">ANDHRA PRADESH</option>
                                <option value="3">ARUNACHAL PRADESH</option>
                                <option value="4">ASSAM</option>
                                <option value="5">BIHAR</option>
                                <option value="6">CHANDIGARH</option>
                                <option value="33">CHHATTISGARH</option>
                                <option value="7">DADRA AND NAGAR HAVELI</option>
                                <option value="8">DAMAN AND DIU</option>
                                <option value="9">DELHI</option>
                                <option value="10">GOA</option>
                                <option value="11">GUJARAT</option>
                                <option value="12">HARYANA</option>
                                <option value="13">HIMACHAL PRADESH</option>
                                <option value="14">JAMMU AND KASHMIR</option>
                                <option value="35">JHARKHAND</option>
                                <option value="15">KARNATAKA</option>
                                <option value="16">KERALA</option>
                                <option value="17">LAKSHADWEEP</option>
                                <option value="18">MADHYA PRADESH</option>
                                <option value="19">MAHARASHTRA</option>
                                <option value="20">MANIPUR</option>
                                <option value="21">MEGHALAYA</option>
                                <option value="22">MIZORAM</option>
                                <option value="23">NAGALAND</option>
                                <option value="24">ODISHA</option>
                                <option value="99">OTHER</option>
                                <option value="25">PONDICHERRY</option>
                                <option value="26">PUNJAB</option>
                                <option value="27">RAJASTHAN</option>
                                <option value="28">SIKKIM</option>
                                <option value="29">TAMILNADU</option>
                                <option value="36">TELANGANA</option>
                                <option value="30">TRIPURA</option>
                                <option value="31">UTTAR PRADESH</option>
                                <option value="34">UTTARAKHAND</option>
                                <option value="32">WEST BENGAL</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" placeholder="Enter Value" value="{{Auth::user()->pincode}}">
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
</div><!-- /.modal -->
@endsection

@push('script')
    <script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/utipancardstatement/0";
        var onDraw = function() {
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class='text-inverse m-l-10'><b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.user.name+` ( `+full.user.id+` )<br>`+full.user.mobile+` ( `+full.user.role.name+` )`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Vle Id - `+full.number+`<br>Tokens - `+full.option1;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Amount - <i class="fa fa-inr"></i> `+full.amount+`<br>Profit - <i class="fa fa-inr"></i> `+full.profit;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success"){
                        var out = `<span class="label label-success">Success</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else{
                        var out = `<span class="label label-danger">Failed</span>`;
                    }

                    var menu = ``;
                    @if (Myhelper::can('Utipancard_statement_edit'))
                    menu += `<li class="dropdown-header">Setting</li>
                            <li><a href="javascript:void(0)" onclick="editReport(`+full.id+`,'`+full.refno+`','`+full.txnid+`','`+full.payid+`','`+full.remark+`', '`+full.status+`', 'utipancard')"><i class="icon-pencil5"></i> Edit</a></li>`;
                    @endif

                    out +=  `<ul class="icons-list">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-right">
                                        `+menu+`
                                    </ul>
                                </li>
                            </ul>`;

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $('[name="tokens"]').keyup(function(){
             $("#price").val($(this).val() * 107);

        });

        $( "#pancardForm" ).validate({
            rules: {
                tokens: {
                    required: true,
                    number : true,
                    min : 1
                },
                vleid: {
                    required: true
                }
            },
            messages: {
                tokens: {
                    required: "Please enter token number",
                    number: "Token should be numeric",
                    min: "Minimum one token is required",
                },
                vleid: {
                    required: "Please enter vle id",
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
                var form = $('#pancardForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.status == "success"){
                            getbalance();
                            form[0].reset();
                            notify("Pancard Token Request Successfully Submitted", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify("Pancard "+data.status+ "! "+data.description, 'warning', 'inline', form);
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
                vleid: {
                    required: true,
                },
                name: {
                    required: true,
                },
                contact_person: {
                    required: true,
                },
                email: {
                    required: true,
                },
                mobile: {
                    required: true,
                },
                pancard: {
                    required: true,
                },
                location: {
                    required: true,
                },
                state: {
                    required: true,
                },
                pincode: {
                    required: true,
                }
            },
            messages: {
                name: {
                    required: "Please enter name",
                },
                vleid: {
                    required: "Please enter vleid",
                },
                contact_person: {
                    required: "Please enter contact_person",
                },
                email: {
                    required: "Please enter email",
                },
                pancard: {
                    required: "Please enter pancard",
                },
                location: {
                    required: "Please enter location",
                },
                state: {
                    required: "Please enter state",
                },
                pincode: {
                    required: "Please enter pincode",
                },
                mobile: {
                    required: "Please enter mobile",
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
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            swal({
                                type: "success",
                                title: "Success",
                                text: "Uti id request submitted successfull",
                                onClose: () => {
                                    window.location.reload();
                                }
                            });
                        }else{
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });
    });
    @if (Myhelper::can('uti_vle_creation'))
        function vlerequest(){
            $.ajax({
                url: "{{ route('pancardpay') }}",
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data : {"actiontype" : 'vleid'},
                beforeSend:function(){
                    swal({
                        title: 'Wait!',
                        text: 'We are feching details.',
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });
                }
            })
            .success(function(data) {
                if(data.status == "success"){
                    swal({
                        type: "success",
                        title: "Success",
                        text: "Uti id request submitted successfull",
                        onClose: () => {
                            window.location.reload();
                        }
                    });
                }else{
                    swal.close();
                    notify(data.status, 'warning');
                }
            })
            .error(function(errors) {
                swal.close();
                showError(errors, $('#pancardForm'));
            });
        }
    @endif
</script>
@endpush