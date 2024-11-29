@extends('layouts.app')
@section('title', "Aeps Agents List")
@section('pagetitle',  "Aeps Agent List")

@php
    $table = "yes";
    $export= "aepsagentstatement";
    $status['type'] = "Id";
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
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Aeps Agent List</h4>
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Details</th>
                            <th>BC Details</th>
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

<div id="viewFullDataModal" class="modal fade right" role="dialog" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Agent Details</h4>
            </div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-striped ">
                    <tbody>
                        <tr>
                            <th>Bc Id</th>
                            <td class="bc_id"></td>
                        </tr>
                        <tr>
                            <th>Bc Name</th>
                            <td><span class="bc_f_name"></span> <span class="bc_l_name"></span></td>
                        </tr>
                        <tr>
                            <th>Bc Mailid</th>
                            <td class="emailid"></td>
                        </tr>
                        <tr>
                            <th>Phone 1</th>
                            <td class="phone1"></td>
                        </tr>
                        <tr>
                            <th>Phone 2</th>
                            <td class="phone2"></td>
                        </tr>
                        <tr>
                            <th>Shopname</th>
                            <td class="shopname"></td>
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
        var url = "{{url('statement/fetch')}}/aepsagentstatement/{{$id}}";
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
                    return `Bc Id - `+full.bc_id+`<br>BC Name - <a href="javascript:void(0)" onclick="viewFullData(`+full.id+`)">`+full.bc_f_name+`</a>`;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success" || full.status == "approved"){
                        var out = `<span class="label label-success">Approved</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else{
                        var out = `<span class="label label-danger">Rejected</span>`;
                    }

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);
    });

    function viewFullData(id){
        $.ajax({
            url: `{{url('statement/fetch')}}/aepsagentstatement/`+id+`/view`,
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
            $('#viewFullDataModal').modal();
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