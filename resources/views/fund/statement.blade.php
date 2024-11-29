@extends('layouts.app')
@section('title', "Fund Statement")
@section('pagetitle',  "Fund Statement")

@php
    $table = "yes";
    $export = "fund";
    $status['type'] = "Fund";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
        "approved" => "Approved",
        "rejected" => "Rejected",
    ];

    $product['type'] = "Fund Type";
    $product['data'] = [
        "transfer" => "Transfer",
        "return" => "Return",
        "request" => "Request"
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Fund Statement</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Details</th>
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
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {

        var url = "{{url('statement/fetch')}}/fundstatement/0";
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
                    var uid = "{{Auth::id()}}";
                    if(full.credited_by == uid){
                        return full.user.name+` ( `+full.user.id+` )<br>`+full.user.mobile+`<br>`+full.user.role.name;
                    }else{
                        return full.sender.name+` ( `+full.sender.id+` )<br>`+full.sender.mobile+`<br>`+full.sender.role.name;
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.product == "fund request"){
                        return `Name - `+full.fundbank.name+`<br>Account No. - `+full.fundbank.account+`<br>Ref - `+full.refno+`(`+full.product+`)`;
                    }else{
                        return full.refno+`<br>`+full.product;
                    }
                }
            },
            { "data" : "amount"},
            { "data" : "remark"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    var out = '';
                    if(full.status == "approved" ||full.status == "success"){
                        out += `<label class="label label-success">`+full.status+`</label>`;
                    }else if(full.status == "pending"){
                        out += `<label class="label label-warning">Pending</label>`;
                    }else{
                        out += `<label class="label label-danger">`+full.status+`</label>`;
                    }

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);
    });
</script>
@endpush