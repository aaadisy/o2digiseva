@extends('layouts.app')
@section('title', 'Icici Bank User Onboard')
@php

    $status['type'] = "KYC";
    $status['data'] = [
        "approved" => "Approved",
        "pending" => "Pending",
        "rejected" => "Rejected",
    ];
@endphp

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title pull-left">Icici Bank User Onboard</h3>
                    <button class="pull-right btn btn-warning btn-sm" id="reportExport" export="icicikyc"><i class="fa fa-file-excel-o"></i> Export</button>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body p-0 table-responsive">
                    <table id="datatable" class="table table-hover table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th width="80px">#</th>
                                <th>User Details</th>
                                <th>Kyc Details</th>
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

    <div id="aepsModal" class="modal fade right" role="dialog" data-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Aeps Details</h4>
                </div>
                <div class="modal-body p-0">
                    <span class="id" style="display: none"></span>
                    <table class="table table-bordered table-striped ">
                        <tbody>
                            <tr>
                                <th>Merchant Login Id</th>
                                <td class="merchantLoginId"></td>
                                <th>Merchant Login Pin</th>
                                <td class="merchantLoginPin"></td>
                            </tr>
                            <tr>
                                <th>Merchant Name</th>
                                <td class="merchantName"></td>
                                <th>Merchant Mobile</th>
                                <td class="merchantPhoneNumber"></td>
                            </tr>

                            <tr>
                                <th>Merchant Address</th>
                                <td class="merchantAddress"></td>
                                <th>Merchant Address 2</th>
                                <td class="merchantAddress2"></td>
                                <th>Merchant District</th>
                                <td class="merchantDistrictName"></td>
                            </tr>
                            <tr>
                                <th>Merchant City</th>
                                <td class="merchantCityName"></td>
                                <th>Merchant State</th>
                                <td class="merchant_state_name"></td>
                                <th>Merchant Pincode</th>
                                <td class="merchantPinCode"></td>
                            </tr>
                            <tr>
                                <th>Merchant Aadhar</th>
                                <td class="merchantAadhar"></td>
                                <th>Merchant Pancard</th>
                                <td class="userPan"></td>
                            </tr>
                            <tr>
                                <th>Bank Name</th>
                                <td class="companyBankName"></td>
                                <th>Bank Branch</th>
                                <td class="bankBranchName"></td>
                                <th>Bank Account Name</th>
                                <td class="bankAccountName"></td>
                            </tr>
                            <tr>
                                <th>Bank Account Number</th>
                                <td class="companyBankAccountNumber"></td>
                                <th>Bank IFSC </th>
                                <td class="bankIfscCode"></td>
                                <th>Comapny Type </th>
                                <td class="merchant_company_type"></td>
                                
                                
                            </tr>
                            
                            <tr>
                                <th>Shop Address</th>
                                <td class="shopAddress"></td>
                                <th>Shop District</th>
                                <td class="shopDistrict"></td>
                            </tr>
                            <tr>
                                <th>Shop City</th>
                                <td class="shopCity"></td>
                                <th>Shop State</th>
                                <td class="shop_state_name"></td>
                                <th>Shop Pincode</th>
                                <td class="shopPincode"></td>
                            </tr>
                            <tr>
                                <th>Adhaar Pic</th>
                                <td><a href="" download class="aadharPic" target="_blank">Download</a></td>
                                <th>Pancard Pic</th>
                                <td><a href="" download class="pancardPic" target="_blank">Download</a></td>
                            </tr>
                            <tr>
                                <th>Background Image Of Shop</th>
                                <td><a href="" download class="backgroundImageOfShop" target="_blank">Download</a></td>
                                <th>Masked Aadhar Pic</th>
                                <td><a href="" download class="maskedAadharImage" target="_blank">Download</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    @if(Myhelper::hasRole('admin'))
                    <button type="button" id="kycsubmit"  class="btn btn-inverse btn-raised waves-light waves-effect" >Submit To Fingpay</button>
                    @endif
                    <button type="button" class="btn btn-warning btn-raised waves-light waves-effect" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if (Myhelper::can('aepskyc_report_edit'))
        <div id="reportEditModal" class="modal fade" role="dialog" data-backdrop="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Update Report</h4>
                    </div>
                    <form id="reportEditForm" method="post" action="{{ route('reportUpdate') }}">
                        <div class="modal-body">
                            <input type="hidden" name="id">
                            <input type="hidden" name="type" value="icicikyc">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Action</label>
                                    <select name="status" class="form-control select" required="">
                                        <option value="">Select Action</option>
                                        <option value="approved">Approved</option>
                                        <option value="pending">Pending</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>AEPS KYC 2FA</label>
                                    <input name="aeps_auth" type="text" class="form-control" value=""  />
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Aadhar PAY 2FA</label>
                                    <input name="ap_auth" type="text" class="form-control" value=""  />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-inverse waves-effect waves-light  waves-effect" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Processing">Submit</button>
                            <button type="button" class="btn btn-warning waves-effect waves-light  waves-effect" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
        
        
@push('script')
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.jqueryui.min.css"/>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.jqueryui.min.js"></script>

    <script type="text/javascript">
        var ROOT = "https://digiseva.me/" , SYSTEM;
        var USERSYSTEM, STOCK={}, DT=!1, STATUSURL='https://digiseva.me/statement/status_lat';


        SYSTEM = {

                    COMPLAINT: function (id, type){
                        $('#complaintEditForm').find("[name='product']").val(type);
                        $('#complaintEditForm').find("[name='transaction_id']").val(id);
                        $('#complaintModal').modal();
                    },


                    DEFAULT: function () {
                        SYSTEM.GETBALANCE();
                        SYSTEM.EVENTSOURCE();
                        SYSTEM.PLUGININIT();
                        SYSTEM.DATAFILTER();
                        SYSTEM.COMPLAINTBEFORESUBMIT();

                        $(".modal").on('hidden.bs.modal', function () {
                            if($(this).find('[type="hidden"]').length){
                                $(this).find('[name="id"]').val(null);
                            }
                            if($(this).find('form').length){
                                $(this).find('form')[0].reset();
                            }

                            if($(this).find('.select').length){
                                $(this).find('.select').val(null).trigger('change');
                            }

                            $(this).find('form').find('p.error').remove();
                        });

                        $('#moreDataFilter').click(function(){
                            $('#moreFilter').slideToggle('fast', 'swing');
                            if($('#moreDataFilter').find('.fa-arrow-down')){
                                $('#moreDataFilter').find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
                            }else if($('#moreDataFilter').find('.fa-arrow-up')){
                                $('#moreDataFilter').find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');
                            }
                        });

                        $('#formReset').click(function () {
                            $('form#dataFilter')[0].reset();
                            $('input[name="from_date"]').datepicker().datepicker("setDate", new Date());
                            $('form#dataFilter').find('select').select2().val(null).trigger('change');
                            $('#datatable').dataTable().api().ajax.reload();
                        });
                    },

                    EVENTSOURCE: function(){
                        if(typeof(EventSource) !== "undefined") {
                            var source = new EventSource(ROOT+"/mydata");
                            source.onmessage = function(event) {
                                var data = jQuery.parseJSON(event.data);
                                $('.fundCount').text(data.fund);
                                $('.aepsFundCount').text(data.aepsfund);
                                $('.totalfundCount').text(parseInt(data.fund) + parseInt(data.aepsfund));

                                $('.uti').text(data.uti);
                                $('.utiid').text(data.utiid);
                                $('.totaltransCount').text(parseInt(data.uti) + parseInt(data.utiid));

                                $('.worthbal').text(data.worthbal);
                                $('.rechbal').text(data.rechbal);
                                if(data.session == 0){
                                    window.location.href = "https://digiseva.me/auth/logout";
                                }
                            };
                        }
                    },

                    NOTIFY: function(type, title, message){
                        swal({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 10000,
                            type: type,
                            title: title,
                            text : message
                        });
                    },

                    VALIDATE: function(type, value){
                        switch(type){
                            case 'empty':
                                if(value.val() == '' ){
                                    value.closest('.form-group').myalert('Enter Value','danger');
                                    return false;
                                }else{
                                    return true;
                                }
                                break;

                            case 'numeric':
                                if(value.val().match(/[^0-9]/g)){
                                    value.closest('.form-group').myalert('Value should be numeric','danger');
                                    return false;
                                }else{
                                    return true;
                                }
                                break;
                        }
                    },

                    PLUGININIT: function(){
                        $('.select').select2();
                        $('.date').datepicker({
                            'autoclose':true,
                            'clearBtn':true,
                            'todayHighlight':true,
                            'format':'dd-M-yyyy'
                        });
                    },

                    GETBALANCE: function(){
                        $.ajax({
                            url: ROOT+"/home/balance",
                            type: "GET",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            success: function(result){
                                $('span.mbalance').text(result.balance);
                                $('span.aepsbalance').text(result.aepsbalance);
                            }
                        });
                    },
                    COMPLAINTBEFORESUBMIT: function (){
                        $('#complaintEditForm').submit(function(){
                            var user_id = $("[name='user_id']").val();
                            var subject = $("[name='subject']").val();
                            var description = $("[name='description']").val();
                            var product = $("[name='product']").val();
                            var transaction_id = $("[name='transaction_id']").val();

                            if(subject == "")
                            {
                                $("[name='subject']").closest('.form-group').myalert('Please enter subject','danger');
                            }
                            else if(description == "")
                            {
                                $("[name='description']").closest('.form-group').myalert('Please enter description','danger');
                            }
                            else
                            {
                                SYSTEM.FORMSUBMIT($('#complaintEditForm'), function(data){
                                    if(!data.statusText){
                                        if(data.status == "success"){
                                            $('#complaintModal').modal('hide');
                                            $('#complaintEditForm')[0].reset();
                                            DT.draw();
                                            SYSTEM.GETBALANCE();
                                            SYSTEM.NOTIFY('success', 'Success', 'Complaint registered successfully!')
                                        }else{
                                            SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                                        }
                                    }else{
                                        SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                                    }
                                });
                            }

                            return false;
                        });
                    },

                    FORMSUBMIT: function(form, callback){
                        form.ajaxSubmit({
                            dataType:'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            beforeSubmit:function(){
                                form.find('button[type="submit"]').button('loading');
                            },
                            complete: function(){
                                form.find('button[type="submit"]').button('reset');
                            },
                            success:function(data){
                                callback(data);
                            },
                            error: function(errors) {
                                callback(errors);
                            }
                        });
                    },

                    AJAX: function(url, data, callback){
                        $.ajax({
                            url: url,
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            data: data,
                            beforeSend:function(){
                                swal({
                                    title: 'Wait!',
                                    text: 'Please wait, we are working on your request',
                                    onOpen: () => {
                                        swal.showLoading()
                                    }
                                });
                            },
                            success:function(data){
                                swal.close();
                                callback(data);
                            },
                            error: function(errors) {
                                swal.close();
                                callback(errors);
                            }
                        });
                    },

                    SHOWERROR: function(errors, form, type="inline"){
                        if(type == "inline"){
                            if(errors.statusText){
                                if(errors.status == 422){
                                    form.find('p.error').remove();
                                    $.each(errors.responseJSON, function (index, value) {
                                        form.find('[name="'+index+'"]').closest('div.form-group').myalert(value, 'danger');
                                    });
                                }else if(errors.status == 400){
                                    form.mynotify(errors.responseJSON.message, 'danger');
                                }else{
                                    form.mynotify(errors.statusText, 'danger');
                                }
                            }else{
                                form.mynotify(errors.message, 'danger');
                            }
                        }else{
                            if(errors.statusText){
                                if(errors.status == 400){
                                    SYSTEM.NOTIFY('error', 'Oops', errors.responseJSON.message);
                                }else{
                                    SYSTEM.NOTIFY('error', 'Oops', errors.statusText);
                                }
                            }else{
                                SYSTEM.NOTIFY('error', 'Oops', errors.message);
                            }
                        }
                    },

                    DATAFILTER: function(){
                       $('#searchForm').submit(function(e) {
    e.preventDefault();

    // Set the button to a loading state and ensure it's enabled
    const searchButton = $(this).find('button[type="submit"]');
    searchButton.html("<b><i class='fa fa-spin fa-spinner'></i></b> Searching").prop('disabled', false).removeClass('disabled');

    // Draw the DataTable with new filters
    DT.draw();

    // Listen for the draw event to reset the button after the table is refreshed
    DT.on('draw', function() {
        // Reset the button text and ensure it's fully enabled
        searchButton.html("<b><i class='icon-search4'></i></b> Search").prop('disabled', false).removeClass('disabled');
    });

    console.log('clicked');
});


                        $('#formReset').click(function(){
                            $('#dataFilter')[0].reset();
                            DT.draw();
                        });
                    }
                };



        $(document).ready(function () {
            SYSTEM.DEFAULT();
            USERSYSTEM = {
                DEFAULT: function () {
                    USERSYSTEM.DT_SETTING();
                    USERSYSTEM.BEFORESUBMIT();

                    $('#kycsubmit').click(function(event) {
                        var id = $('span.id').text();
                        USERSYSTEM.KYCSUBMIT(id);
                    });
                },

                BEFORESUBMIT: function(){
                    $('#reportEditForm').submit(function(){
                        var agentcode = $(this).find("[name='agentcode']").val();
                        var status = $(this).find("[name='status']").val();

                        if(agentcode == "")
                        {
                            $("[name='agentcode']").closest('.form-group').myalert('Enter agentcode','danger');
                        }
                        else if(status == "")
                        {
                            $("[name='status']").closest('.form-group').myalert('Select Status','danger');
                        }
                        else
                        {
                            USERSYSTEM.SUBMIT();
                        }
                        return false;
                    });
                },

                SUBMIT: function(){
                    SYSTEM.FORMSUBMIT($('#reportEditForm'), function(data){
                        if(!data.statusText){
                            if(data.status == "success"){
                                $('#reportEditModal').modal('hide');
                                DT.draw();
                                SYSTEM.NOTIFY('success', 'Success', 'Statement successfully updated')
                            }else{
                                SYSTEM.SHOWERROR(data, $('#reportEditForm'));
                            }
                        }else{
                            SYSTEM.SHOWERROR(data, $('#reportEditForm'));
                        }
                    });
                },

                KYCSUBMIT: function (id) {
                    var inputdata = {"id" : id, "transactionType" : "useronboardsubmit"};

                    SYSTEM.AJAX("https://digiseva.me/ifaeps/initiate", inputdata, function(data){
                        DT.draw();
                        if(!data.statusText){
                            if(data.status == "success"){
                                swal({
                                    type: 'info',
                                    title: data.data.reportstatus,
                                    text : data.data.remark
                                });
                            }
                            else if(data.status == "ERR"){
                                swal({
                                    type: 'danger',
                                    title: data.status,
                                    text : data.message
                                });
                            }else{
                                SYSTEM.SHOWERROR(data, $('#addForm'), 'popup');
                            }
                        }else{
                            SYSTEM.SHOWERROR(data, $('#addForm'), 'popup');
                        }
                    });
                },

                DT_SETTING: function(){
                    DT = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        dom: '<"datatable-scroll-wrap"rt><"datatable-footer"<"col-md-6"i><"col-md-6"p>>',
                        ajax: {
                            url: ROOT+"/statement/fetch_lat/fingaepskycstatement/0",
                            type: "POST",
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: function(d) {
    const searchtext = $('#searchForm').find("[name='searchtext']").val();
    const from_date = $('#searchForm').find("[name='from_date']").val();
    const to_date = $('#searchForm').find("[name='to_date']").val();
    const agent = $('#searchForm').find("[name='agent']").val();
    const status = $('#searchForm').find("[name='status']").val();

    if (searchtext) d.searchtext = searchtext;
    if (from_date && to_date) {
        d.from_date = from_date;
        d.to_date = to_date;
    }
    if (agent) d.agent = agent;
    if (status) d.status = status;
},

                            dataSrc: function(json) {
                                return json.data;
                            }
                        },
                        columns: [
                            { "data" : "id",
                                render:function(data, type, full, meta){
                                    var out = '';
                                    out += `<span class='text-inverse'>`+full.id +`</span><br><span style='font-size:12px'>`+full.created_at+`</span>`;
                                    return out;
                                }
                            },
                            { "data" : "user_name"},
                            { "data" : "vle",
                                render:function(data, type, full, meta){
                                    return `Agentcode - <a href="javascript:void(0)"><span class="label label-success" id="viewStatement">`+full.merchantLoginId+`</span></a><br>Name - `+full.merchantName+`<br>Mobile - `+full.merchantPhoneNumber;
                                }
                            },
                            { "data": "action",
                                render:function(data, type, full, meta){
                                    var btnclass='', btnText='', menu='';
                                    if(full.status == 'approved'){
                                        btnclass = 'btn-success';
                                        btnText = 'Success';
                                    }else if(full.status == 'pending'){
                                        btnclass = 'btn-warning';
                                        btnText = 'Pending';
                                    }else if(full.status == 'Kyc Submitted'){
                                        btnclass = 'btn-primary';
                                        btnText = 'Kyc Submitted';
                                    }else{
                                        btnclass = 'btn-danger';
                                        btnText = 'Rejected';
                                    }

                                                                            menu +=`<li><a href="javascript:void(0)" id="editStatement"><i class="fa fa-pencil"></i> Edit Report</a></li>`;
                                    
                                    return `<div class="btn-group">
                                        <button type="button" class="btn `+btnclass+` dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">`+btnText+` <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">`+menu+`</ul>
                                    </div>`;
                                }
                            }
                        ],
                        responsive: true
                    });

                    USERSYSTEM.MANAGE_DT();
                },

                MANAGE_DT: function(){
                    $('#datatable').on('click', '#viewStatement', function () {
                        var data = DT.row($(this).parent().parent().parent()).data();
                        $.each(data, function(index, values) {
                         
                            if(index == "aadharPic" || index == "pancardPic" || index == "backgroundImageOfShop" || index == "maskedAadharImage"){
                                $("."+index).attr('href', "https://digiseva.me/storage/app/"+values);
                            }else{
                                $("."+index).text(values);
                            }
                           
                        });
                        $('#aepsModal').modal();
                    });

                    $('#datatable').on('click', '#editStatement', function () {
                        var data = DT.row($(this).parent().parent().parent().parent().parent()).data();
                        $('#reportEditForm').find("[name='id']").val(data.id);
                        $('#reportEditForm').find("[name='agentcode']").val(data.agentcode);
                        $('#reportEditForm').find("[name='aeps_auth']").val(data.aeps_auth);
                        $('#reportEditForm').find("[name='ap_auth']").val(data.ap_auth);
                        $('#reportEditForm').find("[name='status']").val(data.status).trigger('change');
                        $('#reportEditForm').find("[name='remark']").val(data.remark);
                        $('#reportEditModal').modal();
                    });
                }
            }

            USERSYSTEM.DEFAULT();
        });
    </script>
@endpush