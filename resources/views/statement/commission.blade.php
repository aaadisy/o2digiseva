@extends('layouts.app')
@section('title', "Commission Statement")
@section('pagetitle',  "Commission Statement")

@php
    $table = "yes";
    $export = "recharge";

    $billers = App\Model\Provider::whereIn('type', ['mobile', 'dth'])->get(['id', 'name']);
    foreach ($billers as $item){
        $product['data'][$item->id] = $item->name;
    }
    $product['type'] = "Operator";
    
    $apidata = DB::table('apis')->where('type','recharge')->get();
    $status['type'] = "Report";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "reversed" => "Reversed",
        "refunded" => "Refunded",
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Commission Statement</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User Details</th>
                            <th>Transaction Details</th>
                            <th>Refrence Details</th>
                            <th>Amount/Commission</th>
                            <th>Admin Profit</th>
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

<div id="utiidModal" class="modal fade right" role="dialog" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Uti Id Details</h4>
            </div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            <th>Vle Id</th>
                            <td class="vleid"></td>
                        </tr>
                        <tr>
                            <th>Vle Password</th>
                            <td class="vlepassword"></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td class="name"></td>
                        </tr>
                        <tr>
                            <th>Localtion</th>
                            <td class="location"></td>
                        </tr>
                        <tr>
                            <th>Contact Person</th>
                            <td class="contact_person"></td>
                        </tr>
                        <tr>
                            <th>State</th>
                            <td class="state"></td>
                        </tr>
                        <tr>
                            <th>Pincode</th>
                            <td class="pincode"></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td class="email"></td>
                        </tr>
                        <tr>
                            <th>Mobile</th>
                            <td class="mobile"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/rechargestatement/{{$id}}";
        var onDraw = function() {
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class=''>`+full.apiname +`</span><br>
                            <span class='text-inverse m-l-10'><b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "username"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Number - `+full.number+`<br>Operator - `+full.providername+`<br>Txnid - `+full.txnid;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `GST  - `+`<i class="fa fa-inr"></i> `+full.gst+`<br>TDS - `+`<i class="fa fa-inr"></i> `+full.tds;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                     return `<i class="fa fa-inr"></i> `+full.amount+` / `+`<i class="fa fa-inr"></i> `+full.profit;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    return `<i class="fa fa-inr"></i> `+full.adminprofit;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success"){
                        var out = `<span class="label label-success">Success</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else if(full.status == "reversed" || full.status == "refunded"){
                        var out = `<span class="label bg-slate">`+full.status+`</span>`;
                    }else{
                        var out = `<span class="label label-danger">`+full.status+`</span>`;
                    }

                    var menu = ``;
                    if(full.status == "success" || full.status == "pending"){
                        @if (Myhelper::can('recharge_status'))
                        menu += `<li class="dropdown-header">Status</li>
                                <li><a href="javascript:void(0)" onclick="status(`+full.id+`, 'recharge')"><i class="icon-info22"></i>Check Status</a></li>`;
                        @endif

                        @if (Myhelper::can('recharge_statement_edit'))
                        menu += `<li class="dropdown-header">Setting</li>
                                <li><a href="javascript:void(0)" onclick="editReport(`+full.id+`,'`+full.refno+`','`+full.txnid+`','`+full.payid+`','`+full.remark+`', '`+full.status+`', 'recharge')"><i class="icon-pencil5"></i> Edit</a></li>`;
                        @endif
                    }

                    if(full.status == "success" || full.status == "pending" || full.status == "reversed"){
                        @if (Myhelper::can('complaint'))
                        menu += `<li class="dropdown-header">Complaint</li>
                                <li><a href="javascript:void(0)" onclick="complaint(`+full.id+`, 'dmt')"><i class="icon-cogs"></i> Complaint</a></li>`;
                        @endif
                    }
                    
                    if(full.status == "success" || full.status == "pending"){
                        @if (Myhelper::can('complaint'))
                        menu += `<li class="dropdown-header">Raise Dispute</li>
                                <li><a href="javascript:void(0)" onclick="dispute(`+full.id+`)"><i class="icon-cogs"></i> Raise Dispute</a></li>`;
                        @endif
                    }
                    

                    out +=  ``;

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);
    });

    function viewUtiid(id){
        $.ajax({
            url: `{{url('statement/fetch')}}/utiidstatement/`+id,
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType:'json',
            data:{'scheme_id':id}
        })
        .done(function(data) {
            $.each(data, function(index, values) {
                $("."+index).text(values);
            });
            $('#utiidModal').modal();
        })
        .fail(function(errors) {
            $.notify('Oops', errors.status+'! '+errors.statusText, 'warning');
        });
    }
</script>
@endpush