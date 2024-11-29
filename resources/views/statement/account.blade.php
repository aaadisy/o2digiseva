@extends('layouts.app')
@section('title', "Account Statement")
@section('pagetitle',  "Account Statement")

@php
    $table = "yes";
    $export = "wallet";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Account Statement</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th width="150px">Refrence Details</th>
                            <th>Product</th>
                            <th>Provider</th>
                            <th>Txnid</th>
                            <th>Number</th>
                            <th width="100px">ST Type</th>
                            <th>Status</th>
                            <th width="130px">Opening Bal.</th>
                            <th width="130px">Amount</th>
                            <th width="130px">Closing Bal.</th>
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
        var url = "{{url('statement/fetch')}}/accountstatement/{{$id}}";
        var onDraw = function() {
            $('[data-popup="tooltip"]').tooltip();
            $('[data-popup="popover"]').popover({
                template: '<div class="popover border-teal-400"><div class="arrow"></div><h3 class="popover-title bg-teal-400"></h3><div class="popover-content"></div></div>'
            });
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    var out = "";
                    out += `</a><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                    return out;
                }
            },
            { "data" : "full.username",
                render:function(data, type, full, meta){
                    var uid = "{{Auth::id()}}";
                    if(full.credited_by == uid){
                        var name = full.username;
                    }else{
                        var name = full.sendername;
                    }
                    return name;
                }
            },
            { "data" : "product"},
            { "data" : "providername"},
            { "data" : "txnid"},
            { "data" : "number"},
            { "data" : "rtype",
                render:function(data, type, full, meta){
                    if(full.rtype == "commission"){
                        if(full.trans_type == "credit" && full.product == "aeps"){
                            return full.rtype+` on (<i class="fa fa-inr"></i> `+ (full.amount).toFixed(2)+`)`;
                        }else if(full.trans_type == "credit" && full.product == "matm"){
                            return full.rtype+` on (<i class="fa fa-inr"></i> `+ (full.amount).toFixed(2)+`)`;
                        }
                    }else{
                        return full.rtype;
                    }
                }
            },
            { "data" : "status"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `<i class="fa fa-inr"></i> `+full.balance;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.trans_type == "credit"){
                        if(full.product == "aeps"){
                            return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.charge).toFixed(2);
                        }else if(full.product == "matm"){
                            return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.charge).toFixed(2);
                        }{
                            return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit).toFixed(2);
                        }
                    }else if(full.trans_type == "debit"){
                        return `<i class="text-danger icon-dash"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit).toFixed(2);
                    }else{
                        return `<i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit).toFixed(2);
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.status == "pending" || full.status == "success" || full.status == "reversed" || full.status == "refunded"){
                        if(full.trans_type == "credit"){
                            if(full.product == "aeps"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance + (full.charge - full.profit)).toFixed(2);
                            }else if(full.product == "matm"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance + (full.charge - full.profit)).toFixed(2);
                            }{
                                return `<i class="fa fa-inr"></i> `+ (full.balance + (full.amount + full.charge - full.profit)).toFixed(2);
                            }
                        }else if(full.trans_type == "debit"){
                            return `<i class="fa fa-inr"></i> `+ (full.balance - (full.amount + full.charge - full.profit)).toFixed(2);
                        }else{
                            return `<i class="fa fa-inr"></i> `+ (full.balance - (full.amount + full.charge - full.profit)).toFixed(2); 
                        }
                    }else{
                        return `<i class="fa fa-inr"></i> `+full.balance;
                    }
                }
            },
        ];

        datatableSetup(url, options, onDraw , '#datatable', {columnDefs: [{
                    orderable: false,
                    width: '80px',
                    targets: [0]
                }]});
    });
</script>
@endpush