@extends('layouts.app')
@section('title', 'Operator List')
@section('pagetitle',  'Operator List')
@php
    $table = "yes";
    $agentfilter = "hide";
    $product['type'] = "Operator Type";
    $product['data'] = [
        "mobile" => "Mobile",
        "dth" => "Dth",
        "electricity" => "Electricity",
        "pancard" => "Pancard",
        "dmt" => "Dmt",
        "fund" => "Fund"
    ];

    $status['type'] = "Operator";
    $status['data'] = [
        "1" => "Active",
        "0" => "De-active"
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
					<h4 class="panel-title">Operator List</h4>
					<div class="heading-elements">
                        <button type="button" class="btn btn-sm bg-slate btn-raised heading-btn legitRipple" onclick="addSetup()">
                            <i class="icon-plus2"></i> Add New
                        </button>
                    </div>
				</div>
				<div class="panel-body">
				</div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Operator Api</th>
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

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Operator</h6>
            </div>
            <form id="setupManager" action="{{route('setupupdate')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="operator">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter value" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Recharge1</label>
                            <input type="text" name="recharge1" class="form-control" placeholder="Enter value" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Recharge2</label>
                            <input type="text" name="recharge2" class="form-control" placeholder="Enter value" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Recharge3</label>
                            <input type="text" name="recharge3" class="form-control" placeholder="Enter value" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Minimum Amount</label>
                            <input type="text" name="min_amount" class="form-control" placeholder="Enter value" >
                        </div>
                        <div class="form-group col-md-6">
                            <label>Max Amount</label>
                            <input type="text" name="max_amount" class="form-control" placeholder="Enter value" >
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Operator Type</label>
                            <select name="type" class="form-control select" required>
                                <option value="">Select Operator Type</option>
                                <option value="mobile">Mobile</option>
                                <option value="dth">DTH</option>
                                <option value="electricity">Electricity Bill</option>
                                <option value="insurance">Insurance</option>
                                <option value="cashdeposit">Cash Deposit</option>
                                <option value="pancard">Pancard</option>
                                <option value="dmt">Dmt</option>
                                <option value="fund">Fund</option>
                                <option value="aeps">AePS</option>
                                <option value="aadharpay">Aadhar Pay</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Api</label>
                            <select name="api_id" class="form-control select" required>
                                <option value="">Select Api</option>
                                @foreach ($apis as $api)
                                <option value="{{$api->id}}">{{$api->product}}</option>
                                @endforeach
                            </select>
                        </div>
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

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/setup{{$type}}/0";

        var onDraw = function() {
            $('select').select2();
            $('input#operatorStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "0";
                if($(this).prop('checked')){
                    status = "1";
                }
                
                $.ajax({
                    url: '{{ route('setupupdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data: {'id':id, 'status':status, "actiontype":"operator"}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Operator Updated", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        if(status == "1"){
                            ele.prop('checked', false);
                        }else{
                            ele.prop('checked', true);
                        }
                        notify("Something went wrong, Try again." ,'warning');
                    }
                })
                .fail(function(errors) {
                    if(status == "1"){
                        ele.prop('checked', false);
                    }else{
                        ele.prop('checked', true);
                    }
                    showError(errors, "withoutform");
                });
            });
        };

        var options = [
            { "data" : "id"},
            { "data" : "name"},
            { "data" : "type"},
            { "data" : "name",
                render:function(data, type, full, meta){
                    var check = "";
                    if(full.status == "1"){
                        check = "checked='checked'";
                    }

                    return `<label class="switch">
                                <input type="checkbox" id="operatorStatus" `+check+` value="`+full.id+`" actionType="`+type+`">
                                <span class="slider round"></span>
                            </label>`;
                }
            },
            { "data" : "name",
                render:function(data, type, full, meta){
                    var out = "";
                    out += `<select class="form-control select" required="" onchange="apiUpdate(this, `+full.id+`)">`;
                    @foreach ($apis as $api)
                    var apiid = "{{$api->id}}";
                    out += `<option value="{{$api->id}}"`;
                    if(apiid == full.api_id){
                        out += `selected="selected"`;
                    }
                    out += `>{{$api->product}}</option>`;
                    @endforeach
                    out += `</select>`;
                    return out;
                }
            },
            { "data" : "action",
                render:function(data, type, full, meta){
                    return `<button type="button" class="btn bg-slate btn-raised legitRipple btn-xs" onclick="editSetup(`+full.id+`, \``+full.name+`\`, \``+full.recharge1+`\`, \``+full.recharge2+`\`, \``+full.recharge3+`\`, \``+full.type+`\`, \``+full.api_id+`\`, \``+full.min_amount+`\`, \``+full.max_amount+`\`)"> Edit</button>`;
                }
            },
        ];
        datatableSetup(url, options, onDraw);

        $( "#setupManager" ).validate({
            rules: {
                name: {
                    required: true,
                },
                recharge1: {
                    required: true,
                },
                recharge2: {
                    required: true,
                },
                type: {
                    required: true,
                },
                api_id: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Please enter operator name",
                },
                recharge1: {
                    required: "Please enter value",
                },
                recharge2: {
                    required: "Please enter value",
                },
                type: {
                    required: "Please select operator type",
                },
                api_id: {
                    required: "Please select api",
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
                                $('[name="api_id"]').select2().val(null).trigger('change');
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

    	$("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });
    
    });

    function addSetup(){
    	$('#setupModal').find('.msg').text("Add");
    	$('#setupModal').find('input[name="id"]').val("new");
    	$('#setupModal').modal('show');
	}

	function editSetup(id, name, recharge1, recharge2, recharge3, type, api_id,min_amount,max_amount){
		$('#setupModal').find('.msg').text("Edit");
    	$('#setupModal').find('input[name="id"]').val(id);
    	$('#setupModal').find('input[name="name"]').val(name);
        $('#setupModal').find('input[name="recharge1"]').val(recharge1);
        $('#setupModal').find('input[name="recharge2"]').val(recharge2);
        $('#setupModal').find('input[name="recharge3"]').val(recharge3);
        $('#setupModal').find('input[name="min_amount"]').val(min_amount);
        $('#setupModal').find('input[name="max_amount"]').val(max_amount);
        $('#setupModal').find('[name="type"]').select2().val(type).trigger('change');
        $('#setupModal').find('[name="api_id"]').select2().val(api_id).trigger('change');
    	$('#setupModal').modal('show');
    }
    
    function apiUpdate(ele, id){
        var api_id = $(ele).val();
        if(api_id != ""){
            $.ajax({
                url: '{{ route('setupupdate') }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data: {'id':id, 'api_id':api_id, "actiontype":"operator"}
            })
            .done(function(data) {
                if(data.status == "success"){
                    notify("Operator Updated", 'success');
                }else{
                    notify("Something went wrong, Try again." ,'warning');
                }
                $('#datatable').dataTable().api().ajax.reload();
            })
            .fail(function(errors) {
                showError(errors, "withoutform");
            });
        }
    }
</script>
@endpush