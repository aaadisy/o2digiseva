@extends('layouts.app')
@section('title', "Complaints")
@section('pagetitle',  "Complaints")

@php
    $table = "yes";

    $product['data'] = array(
        'recharge' => 'Recharge',
        'billpay' => 'Billpay',
        'dmt' => 'Dmt',
        'aeps' => 'Aeps',
        'utipancard' => 'Utipancard'
    );
    $product['type'] = "Service";

    $status['type'] = "Report";
    $status['data'] = [
        "resolved" => "Resolved",
        "pending" => "Pending",
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Complaints</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <th>Transaction Details</th>
                            <th>Subject</th>
                            <th>Query Details</th>
                            <th>Solution Details</th>
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

<div id="complaintEditModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Edit Report</h6>
            </div>
            <form id="complaintEditForm" action="{{route('complaintstore')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select">
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Solution</label>
                        <textarea name="solution" cols="30" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating">Update</button>
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
        var url = "{{url('statement/fetch')}}/aepscomplaints/0";
        var onDraw = function() {
        };
        var options = [
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.user.name+` ( `+full.user.id+` )<br>`+full.user.mobile+` <br>`+full.user.role.name;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return ``+full.transaction_id+`&nbsp;<a href="javascript:void(0)" class="label label-info" style="font-size:15px" onclick="viewData('`+full.transaction_id+`')"> `+full.product+ ` ( `+full.transaction_id+` )`+`</a>`+full.report_details.txnid+``;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.complaintsubject.subject;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.description;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.resolver){
                        return 'Resolved By - '+full.resolver.name + '('+full.resolver.id+')<br>'+full.solution;
                    }else{
                        return full.solution;
                    }
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "resolved"){
                        var out = `<span class="label label-success">Resolved</span>`;
                    }else{
                        var out = `<span class="label label-warning">Pending</span>`;
                    }

                    var menu = ``;
                    @if (Myhelper::can('complaint_edit'))
                    menu += `<li class="dropdown-header">Setting</li>
                            <li><a href="javascript:void(0)" onclick="editComplaint(`+full.id+`, '`+full.status+`', '`+full.solution+`')"><i class="icon-pencil5"></i> Edit</a></li>`;
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

        $( "#complaintEditForm").validate({
            rules: {
                status: {
                    required: true,
                },
                solution: {
                    required: true,
                }
            },
            messages: {
                status: {
                    required: "Please select status",
                },
                solution: {
                    required: "Please enter your solution",
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
                var form = $('#complaintEditForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status){
                            form[0].reset();
                            form.find('select').val(null).trigger('change');
                            form.closest('.modal').modal('hide');
                            $('#datatable').dataTable().api().ajax.reload();
                            notify("Complaint successfully updated", 'success');
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
    });

    function viewData(id){
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
            notify('Oops', errors.status+'! '+errors.statusText, 'warning');
        });
    }

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
            notify('Oops', errors.status+'! '+errors.statusText, 'warning');
        });
    }

    function editComplaint(id, status, solution){
        $('#complaintEditModal').find('[name="id"]').val(id);
        $('#complaintEditModal').find('[name="solution"]').val(solution);
        $('#complaintEditModal').find('[name="status"]').val(status).trigger('change');
        $('#complaintEditModal').modal('show');
    }
</script>
@endpush