@extends('layouts.app')
@section('title', "Aeps Wallet Statement")
@section('pagetitle',  "Aeps Wallet Statement")

@php
    $table = "yes";
    $export = "awallet";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Aeps Wallet Statement</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th width="150px">Refrence Details</th>
                            <th>Transaction Details</th>
                            <th width="100px">TXN Type</th>
                            <th width="100px">ST Type</th>
                            <th>Status</th>
                            <th width="130px">Opening Bal.</th>
                            <th width="130px">Amount</th>
                            <th width="130px">Closing Balance</th>
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
        var url = "{{url('statement/fetch')}}/awalletstatement/{{$id}}";
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
            { "data" : "username"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.transtype == "fund" ){
                        return `<b>`+full.payid+`</b><br> `+full.remark;
                    }else{
                        if(full.status == "success"){
                            return full.aadhar+` / `+full.mobile+' / '+full.refno;
                        }else{
                            return full.aadhar+` / `+full.mobile+' / '+full.mytxnid;
                        }
                    }
                }
            },
            { "data" : "transtype"},
            { "data" : "rtype"},
            { "data" : "status"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.transtype == "fund"){
                        if(full.type == "debit"){
                            return `<i class="fa fa-inr"></i> `+(full.balance + (full.amount + full.charge)).toFixed(2);
                        }else{
                            return `<i class="fa fa-inr"></i> `+(full.balance).toFixed(2);
                        }
                    }else{
                        return `<i class="fa fa-inr"></i> `+full.balance.toFixed(2);
                    }
                    
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                     if(full.aepstype == "M"){
                        return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.amount - full.charge).toFixed(2);;
                    }
                    else if(full.type == "credit"){
                        return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge).toFixed(2);;
                    }
                    
                    else if(full.type == "debit"){
                        return `<i class="text-danger icon-dash"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge).toFixed(2);;
                    }else{
                        return `<i class="fa fa-inr"></i> `+ (full.amount - full.charge).toFixed(2);;
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){

                    if(full.transtype == "fund"){
                        if(full.type == "credit"){
                            return `<i class="fa fa-inr"></i> `+(full.balance + (full.amount + full.charge)).toFixed(2);
                        }else{
                            return `<i class="fa fa-inr"></i> `+(full.balance).toFixed(2);
                        }
                    }

                    if(full.aepstype == "M"){
                        return ` <i class="fa fa-inr"></i> `+ (full.amount - full.charge).toFixed(2);;
                    }
                    else if(full.type == "credit"){
                        return ` <i class="fa fa-inr"></i> `+ (full.balance + (full.amount + full.charge)).toFixed(2);;
                    }
                    
                    else if(full.type == "debit"){
                        return ` <i class="fa fa-inr"></i> `+ (full.balance - (full.amount + full.charge)).toFixed(2);;
                    }else{
                        return `<i class="fa fa-inr"></i> `+ (full.amount - full.charge).toFixed(2);;
                    }
                }
            }
        ];

        datatableSetup(url, options, onDraw , '#datatable', {columnDefs: [{
                    orderable: false,
                    width: '80px',
                    targets: [0],
                    pageLength: 50

                }]});
    });
</script>
@endpush