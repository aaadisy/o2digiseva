@extends('layouts.app')
@section('title', "Uti Id Statement")
@section('pagetitle',  "Uti Id Statement")

@php
    $table = "yes";
    $export = "utiid";
    $status['type'] = "Id";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Uti Id Statement</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Details</th>
                            <th>Uti Id Details</th>
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

@if (Myhelper::can('Utiid_statement_edit'))
<div id="editModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Edit Report</h6>
            </div>
            <form id="editUtiidForm" action="{{route('statementUpdate')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="utiid">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-control select" required>
                                <option value="">Select Type</option>
                                <option value="pending">Pending</option>
                                <option value="success">Success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Vle Id</label>
                            <input type="text" name="vleid" class="form-control" placeholder="Enter Vle id" required="">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Vle Password</label>
                            <input type="text" name="vlepassword" class="form-control" placeholder="Enter Vle Password" required="">
                        </div>
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
@endif
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/utiidstatement/{{$id}}";
        var onDraw = function() {
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class='text-inverse pull-left m-l-10 text-capitalize'><b>`+full.type +`</b> </span>
                            <span class='text-inverse pull-right m-l-10'><b>`+full.id +`</b> </span>
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
                    return `Vle Id - `+full.vleid+`<br>Vle Name - <a href="javascript:void(0)" onclick="viewUtiid(`+full.id+`)">`+full.name+`</a>`;
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
                    @if (Myhelper::can('utiid_status'))
                        menu += `<li class="dropdown-header">Status</li>
                                <li><a href="javascript:void(0)" onclick="status(`+full.id+`, 'utiid')"><i class="icon-info22"></i>Check Status</a></li>`;
                        @endif

                    @if (Myhelper::can('Utiid_statement_edit'))
                    menu += `<li class="dropdown-header">Setting</li>
                            <li><a href="javascript:void(0)" onclick="editUtiid(`+full.id+`,'`+full.vleid+`','`+full.vlepassword+`', '`+full.status+`')"><i class="icon-pencil5"></i> Edit</a></li>`;
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

        $( "#editUtiidForm" ).validate({
            rules: {
                status: {
                    required: true,
                },
                vleid: {
                    required: true,
                },
                vlepassword: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Please select status",
                },
                vleid: {
                    required: "Please enter vle id",
                },
                vlepassword: {
                    required: "Please enter vle password",
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
                var form = $('#editUtiidForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            if(id == "new"){
                                form[0].reset();
                            }
                            form.find('button[type="submit"]').button('reset');
                            notify("Task Successfully Completed", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
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

    	$("#editModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('form')[0].reset();
        });
    });

    function viewUtiid(id){
        $.ajax({
            url: `{{url('statement/fetch')}}/utiidstatement/`+id+`/view`,
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

    function editUtiid(id, vleid, vlepassword, status){
        $('#editModal').find('[name="id"]').val(id);
        $('#editModal').find('[name="status"]').val(status).trigger('change');
        $('#editModal').find('[name="vleid"]').val(vleid);
        $('#editModal').find('[name="vlepassword"]').val(vlepassword);
    	$('#editModal').modal('show');
    }
</script>
@endpush