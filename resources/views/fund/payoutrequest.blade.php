@extends('layouts.app')
@section('title', "Payout Pending Request")
@section('pagetitle',  "Payout Pending Request")

@php
    $table = "yes";
    $export = "aepsfundrequestview";
    $status['type'] = "Fund";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
        "approved" => "Approved",
        "rejected" => "Rejected",
    ];

    $product['type'] = "Transaction";
    $product['data'] = [
        "wallet" => "Move To Wallet",
        "bank" => "Move To Bank"
    ];

@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Payout Pending Request</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th width="160px">#</th>
                            <th>User Details</th>
                            <th>Bank Details</th>
                            <th>Reference Details</th>
                            <th>Amount</th>
                            <th>Remark</th>
                            <th width="100px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if (Myhelper::hasRole('admin'))
<div id="transferModal" class="modal fade" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                 <h4 class="modal-title">Fund Request From <span class="payeename text-capitalize"></span> </h4>
            </div>

            <form id="transferForm" method="post" action="{{ route('fundtransaction') }}">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id">
                    <input type="hidden" name="type" value="aepstransfer">
                    <div class="form-group">
                        <label>Action Type</label>
                        <select class="form-control select" name="status" required="">
                            <option value="">Select Action Type</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ref No</label>
                        <input text="text" name="refno" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control" rows="3" placeholder="Enter Value"></textarea>
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
@endif

@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/aepspayoutrequestview/0";
        var onDraw = function() {};
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    var out = '';
                    if(full.api){
                        out +=  `<span class='myspan'>`+full.api.api_name +`</span><br>`;
                    }
                    out += `<span class='text-inverse'>`+full.id +`</span><br><span style='font-size:12px'>`+full.created_at+`</span>`;
                    return out;
                }
            },
            { "data" : "account",
                render:function(data, type, full, meta){
                    return full.user.name+`<br>`+full.user.mobile;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.type == "wallet"){
                        return "Wallet"
                    }else{
                        if(full.account != '' && full.account != null){
                            return full.account +" ( "+full.bank+" )<br>"+full.ifsc;
                        }else{
                            return full.user.account +" ( "+full.user.bank+" )<br>"+full.user.ifsc;
                        }
                    }
                }
            },
            { "data" : "account",
                render:function(data, type, full, meta){
                    return "RRN - "+full.payoutref+"<br>Payout Id - "+full.payoutid;
                }
            },
            { "data" : "description",
                render:function(data, type, full, meta){
                    return `<span class='text-inverse'><i class="fa fa-rupee"></i> `+full.amount;
                }
            },
            { "data" : "remark"},
            { 
                "data": "action",
                render:function(data, type, full, meta){
                    if(full.status == "approved"){
                        var btn = '<span class="label label-success text-uppercase"><b>'+full.status+'</b></span>';
                    }else if(full.status== 'pending'){
                        var btn = '<span class="label label-warning text-uppercase"><b>'+full.status+'</b></span>';
                    }else{
                        var btn = '<span class="label label-danger text-uppercase"><b>'+full.status+'</b></span>';
                    }
                    @if(Myhelper::hasRole('admin'))
                        btn += `<br><button class="btn bg-slate btn-xs waves-effect mt-10" onclick="transfer('`+full.id+`', '`+full.user.name+`')"><i class="fa fa-pencil"></i> Edit</button>`;
                    @endif
                    return btn;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $('form#transferForm').submit(function() {
            var form = $(this);
            $(this).ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    form.find('button:submit').button('loading');
                },
                success:function(data){
                    if(data.status == "success"){
                        form.find('button:submit').button('reset');
                        form[0].reset();
                        notify('Fund request successfully updated', 'success');
                        $('#transferModal').modal('hide');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        notify('Something went wrong', 'danger');
                    }
                },
                error: function(errors) {
                    form.find('button:submit').button('reset');
                    notify(errors.statusText, 'Oops!', 'error');
                }
            });
            return false;
        });

        $("#transferModal").on('hidden.bs.modal', function () {
            $('#transferModal').find('form')[0].reset();
            $('#transferForm').find('input[name="id"]').val('');
            $('#transferModal').find('.payeename').text('');
        });
    });

function transfer(id, name) {
    $('#transferModal').find('.payeename').text(name);
    $('#transferForm').find('input[name="id"]').val(id);
    $('#transferModal').modal();
}
</script>
@endpush