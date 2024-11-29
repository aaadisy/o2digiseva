@extends('layouts.app')
@section('title', "Api Setting")
@section('pagetitle', "Api Setting")

@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Api Tokens</h4>
                    <div class="heading-elements">
                        <button type="button" class="btn btn-sm bg-slate btn-raised heading-btn legitRipple" onclick="addSetup()">
                            <i class="icon-plus2"></i> Add New
                        </button>
                    </div>
                </div>
                <table class="table table-striped table-hover mt-20" id="datatable">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Token</th>
                            <th>Domain</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Call Back</h4>
                </div>
                <form id="callbackForm" action="{{route('profileUpdate')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{Auth::user()->id}}">
                    <div class="panel-body" style="padding:16px">
                        <div class="form-group">
                            <textarea name="callbackurl" class="form-control" cols="30" rows="3" required placeholder="Enter Callback Url">{{Auth::user()->callbackurl ?? ""}}</textarea>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Bank</h6>
            </div>
            <form id="setupManager" action="{{route('apitokenstore')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>IP</label>
                        <input type="text" name="ip" class="form-control" placeholder="Enter your server ip" required="">
                    </div>
                    <div class="form-group">
                        <label>Domain</label>
                        <input type="text" name="domain" class="form-control" placeholder="Enter your domain" required="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Add Token</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@push('script')

<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/apitoken/0";

        var onDraw = function() {};

        var options = [
            { "data" : "ip"},
            { "data" : "token"},
            { "data" : "domain"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    return `<button type="button" class="btn bg-danger btn-raised legitRipple btn-xs" onclick="deleteToken(`+full.id+`)"> <i class="fa fa-trash"></i></button>`;
                }
            },
        ];
        datatableSetup(url, options, onDraw);

        $( "#setupManager" ).validate({
            rules: {
                ip: {
                    required: true,
                },
                domain: {
                    required: true,
                }
            },
            messages: {
                ip: {
                    required: "Please enter ip",
                },
                domain: {
                    required: "Please enter domain",
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
                var form = $('#setupManager');
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
                                form.closest('.modal').modal('hide');
                            }
                            form.find('button[type="submit"]').button('reset');
                            notify("Token Successfully Generated", 'success');
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

        $( "#callbackForm" ).validate({
            rules: {
                callback: {
                    required: true,
                }
            },
            messages: {
                callback: {
                    required: "Please enter callback url",
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
                var form = $('#callbackForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form.find('button[type="submit"]').button('reset');
                            notify("Callback Successfully Updated", 'success');
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
    });

    function addSetup(){
    	$('#setupModal').find('.msg').text("Add");
    	$('#setupModal').find('input[name="id"]').val("new");
    	$('#setupModal').modal('show');
    }
    
    function deleteToken(id){
        swal({
            title: 'Are you sure ?',
            text: "You want to delete token",
            type: 'warning',
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: 'Yes delete it!',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !swal.isLoading(),
            preConfirm: () => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: "{{ route('tokenDelete') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        data: {'id':id},
                        success: function(result){
                            resolve(result);
                        },
                        error: function(error){
                            resolve(error);
                        }
                    });
                });
            },
        }).then((result) => {
            if(result.value.status == "1"){
                notify("Token Successfully Deleted", 'success');
                $('#datatable').dataTable().api().ajax.reload();
            }else{
                notify('Something went wrong, try again', 'Oops', 'error');
            }
        });
    }
</script>
@endpush
