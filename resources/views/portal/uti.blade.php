@extends('layouts.app')
@section('title', "Uti Id Request")
@section('pagetitle',  "Uti Id Request")

@php
    $table = "yes";

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
                    <h4 class="panel-title">Uti Id Request</h4>
                    <div class="heading-elements">
                        <a href="#" data-toggle="modal", data-target="#addModal"><button type="button" class="btn btn-sm bg-slate btn-raised heading-btn legitRipple">
                            <i class="icon-plus2"></i> Add New
                        </button></a>
                    </div>
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

<div id="addModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">New  Id Request</h4>
            </div>
            <form id="transferForm" method="post" action="{{ route('portalstore') }}">
                <input type="hidden" name="id" name="new">
                <div class="modal-body">
                    <div class="row">
                        {!! csrf_field() !!}
                        <div class="form-group col-md-6">
                            <label>Retailer</label>
                            <select class="form-control select" name="user_id" required="">
                                <option value="">Select Retailer</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->name}} ({{$user->mobile}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Vle Id</label>
                            <input type="text" class="form-control" name="vleid" placeholder="Enter Value" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Vle Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Comtact Person</label>
                            <input type="text" class="form-control" name="contact_person" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Mobile</label>
                            <input type="text" class="form-control" name="mobile" pattern="[0-9]*" maxlength="10" minlength="10" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>pancard</label>
                            <input type="text" class="form-control" name="pancard" required="" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Location</label>
                            <input type="text" class="form-control" name="location" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>State</label>
                            <input type="text" class="form-control" name="state" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Pincode</label>
                            <input type="text" class="form-control" name="pincode" placeholder="Enter Value">
                        </div>
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

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/portal{{$type}}/0";
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

        $( "#transferForm" ).validate({
            rules: {
                vleid: {
                    required: true,
                },
                name: {
                    required: true,
                },
                contact_person: {
                    required: true,
                },
                email: {
                    required: true,
                },
                mobile: {
                    required: true,
                },
                pancard: {
                    required: true,
                },
                location: {
                    required: true,
                },
                state: {
                    required: true,
                },
                pincode: {
                    required: true,
                }
            },
            messages: {
                name: {
                    required: "Please enter name",
                },
                vleid: {
                    required: "Please enter vleid",
                },
                contact_person: {
                    required: "Please enter contact_person",
                },
                email: {
                    required: "Please enter email",
                },
                pancard: {
                    required: "Please enter pancard",
                },
                location: {
                    required: "Please enter location",
                },
                state: {
                    required: "Please enter state",
                },
                pincode: {
                    required: "Please enter pincode",
                },
                mobile: {
                    required: "Please enter mobile",
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
                var form = $('#transferForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form[0].reset();
                            form.find('button[type="submit"]').button('reset');
                            notify("Uti id request successfully submitted", 'success');
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
</script>
@endpush