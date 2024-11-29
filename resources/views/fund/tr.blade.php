@extends('layouts.app')
@section('title', "Fund Transfer or Return")
@section('pagetitle',  "Fund Transfer & Return")

@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Member List</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Parent Details</th>
                            <th>Company Profile</th>
                            <th>Wallet Details</th>
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

<div id="transferModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Fund Transfer / Return</h6>
            </div>
            <form id="transferForm" action="{{route('fundtransaction')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="user_id">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Fund Action</label>
                            <select name="type" class="form-control select" id="select" required>
                                <option value="">Select Action</option>
                                @if (Myhelper::can('fund_transfer'))
                                <option value="transfer">Transfer</option>
                                @endif
                                @if (Myhelper::can('fund_return'))
                                <option value="return">Return</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Amount</label>
                            <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Remark</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="Enter Remark"></textarea>
                        </div>
                    </div>
                     <div class="row formdata">
                        
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
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/tr/0";
        var onDraw = function() {
            $('input#membarStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "block";
                if($(this).prop('checked')){
                    status = "active";
                }
    
                $.ajax({
                    url: '{{ route('profileUpdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data:{'id':id, 'status':status}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Member Updated", 'success');
                    }else{
                        notify("Something went wrong, Try again." ,'warning');
                    }
                })
                .fail(function(errors) {
                    if(status == "active"){
                        ele.prop('checked', false);
                    }else{
                        ele.prop('checked', true);
                    }
                    showError(errors, "withoutform");
                });
            });
        };
        var options = [
            { "data" : "name",
                'className' : "notClick",
                render:function(data, type, full, meta){
                    var check = "";
                    if(full.kyc == "pending"){
                        check += `<label class="label label-warning">Kyc Pending</label>`;
                    }else{
                        check += `<label class="label label-success">Kyc Success</label>`;
                    }
                    return `<div>`+check+`<span class='text-inverse pull-right m-l-10'><b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div>
                        <span style='font-size:13px'>`+full.updated_at+`</span>`;
                }
            },
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<span class="name">`+full.name+`</span>` +`<br>`+full.mobile+`<br>`+full.role.name;
                }
            },
            { "data" : "parents"},
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<span class="name">`+full.company.companyname+`</span>` +`<br>`+full.company.website;
                }
            },
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `Main - `+full.mainwallet +" /-<br>Locked - "+full.lockedamount+" /-";
                }
            },
            { "data" : "action",
                render:function(data, type, full, meta){
                    return `<button class="btn bg-slate btn-raised legitRipple btn-xs" onclick="transfer('`+full.id+`')">Transfer / Return</button>`;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $( "#transferForm").validate({
            rules: {
                type: {
                    required: true
                },
                amount: {
                    required: true,
                    min : 1
                }
            },
            messages: {
                type: {
                    required: "Please select transfer action",
                },
                amount: {
                    required: "Please enter amount",
                    min : "Amount value should be greater than 0"
                },
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
                var type = $('#transferForm').find('[name="type"]').val();
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
                            getbalance();
                            
                            $('div.formdata').remove();
                            form.closest('.modal').modal('hide');
                            notify("Fund "+type+" Successfull", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }
                        else if(data.status == "otpsent"){
                                $('div.formdata').append(`<div class="wrap-input100 validate-input m-b-20" data-validate="Type Otp">
                                
                                    <div class="form-group has-feedback has-feedback-left">
                                <input class="form-control" type="password" name="otp" placeholder="Otp" required>
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                            </div>
                                    
                                </div>`);

                                
                            }
                            else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });
    });

    function transfer(id){
        $('#transferForm').find('[name="user_id"]').val(id);
        $('#transferModal').modal();
    }
</script>
@endpush