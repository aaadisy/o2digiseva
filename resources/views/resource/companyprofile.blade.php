@extends('layouts.app')
@section('title', "Company Profile")
@section('pagetitle', "Company Profile")
@section('bodyClass', "has-detached-left")

@section('content')
<div class="content">
    <div class="sidebar-detached">
        <div class="sidebar sidebar-default sidebar-separate">
            <div class="sidebar-content">
                <div class="content-group">
                    <div class="panel-body bg-indigo-400 border-radius-top text-center" style="background-image: url(http://demo.interface.club/limitless/assets/images/bg.png); background-size: contain;">
                        <div class="content-group-sm">
                            <h6 class="text-semibold no-margin-bottom">
                                {{ucfirst($company->companyname)}}
                            </h6>
                        </div>

                        <a href="#" class="display-inline-block content-group-sm">
                            @if (Auth::user()->company->logo)
                                <a class="navbar-brand no-padding" href="{{route('home')}}">
                                    <img src="{{asset('')}}public/logos/{{Auth::user()->company->logo}}" class=" img-responsive" alt="" style="width: 260px;height: 56px;">
                                </a>
                            @endif
                        </a>
                    </div>

                    <div class="panel no-border-top no-border-radius-top">
                        <ul class="navigation">
                            <li class="navigation-header">Navigation</li>
                            <li class="active"><a href="#profile" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Company Details</a></li>
                            <li class=""><a href="#logo" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Company Logo</a></li>
                            <li class=""><a href="#news" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Company News</a></li>
                            <li class=""><a href="#notice" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Company Notice</a></li>
                            <li class=""><a href="#websitepopup" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Website Popup</a></li>
                            <li class=""><a href="#support" data-toggle="tab" class="legitRipple" aria-expanded="false"><i class="icon-chevron-right"></i> Company Support Details</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-detached">
        <div class="content-detached">
            <div class="tab-content">

                <div class="tab-pane fade in active" id="profile">
                    <form id="profileForm" action="{{route('resourceupdate')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$company->id}}">
                        <input type="hidden" name="actiontype" value="company">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Company Information</h3>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Company Name</label>
                                        <input type="text" name="companyname" class="form-control" value="{{$company->companyname}}" required="" placeholder="Enter Value">
                                    </div>
                                    <div class="form-group  col-md-4">
                                        <label>Company Website</label>
                                        <input type="text" name="website" class="form-control" value="{{$company->website}}" required="" placeholder="Enter Value">
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="logo">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">Company Logo</h4>
                        </div>
                        <div class="panel-body p-5">
                            <form class="dropzone" id="logoupload" action="{{route('resourceupdate')}}" method="post" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <input type="hidden" name="actiontype" value="company">
                                <input type="hidden" name="id" value="{{$company->id}}">
                            </form>
                            <p>Note : Prefered image size is 260px * 56px</p>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="news">
                    <form id="newsForm" action="{{route('resourceupdate')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$companydata->id ?? 'new'}}">
                        <input type="hidden" name="company_id" value="{{$company->id}}">
                        <input type="hidden" name="actiontype" value="companydata">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Company News</h3>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>News</label>
                                        <textarea name="news" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->news ?? ""}}</textarea>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Bill Notice</label>
                                        <textarea name="billnotice" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->billnotice ?? ""}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Channel Parnter News</label>
                                        <textarea name="wnews" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->wnews ?? ""}}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Master Distributer News</label>
                                        <textarea name="mdnews" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->mdnews ?? ""}}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Distributer News</label>
                                        <textarea name="dnews" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->dnews ?? ""}}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Retailer News</label>
                                        <textarea name="rnews" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->rnews ?? ""}}</textarea>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="panel-footer">
                                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="notice">
                    <form id="noticeForm" action="{{route('resourceupdate')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$companydata->id ?? 'new'}}">
                        <input type="hidden" name="company_id" value="{{$company->id}}">
                        <input type="hidden" name="actiontype" value="companydata">
                        <input type="hidden" name="notice">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Company Notice</h3>
                            </div>
                            <div class="panel-body no-padding">
                                <div class="form-group summernote">
                                    {!! nl2br($companydata->notice ?? '') !!}
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="tab-pane fade" id="websitepopup">
                    <form id="websitepopupForm" action="{{route('resourceupdate')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$companydata->id ?? 'new'}}">
                        <input type="hidden" name="company_id" value="{{$company->id}}">
                        <input type="hidden" name="actiontype" value="companydata">
                        <input type="hidden" name="websitepopup">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Website Popup</h3>
                            </div>
                            <div class="panel-body no-padding">
                                <div class="form-group summernote">
                                    {!! nl2br($companydata->websitepopup ?? '') !!}
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="support">
                    <form id="supportForm" action="{{route('resourceupdate')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="company_id" value="{{$company->id}}">
                        <input type="hidden" name="actiontype" value="companydata">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Company Support Information</h3>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Contact Number</label>
                                        <textarea name="number" class="form-control" cols="30" rows="3" placeholder="Enter Value" required="">{{$companydata->number ?? ""}}</textarea>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Contact Email</label>
                                        <textarea name="email" class="form-control" cols="30" rows="3" placeholder="Enter Value" required="">{{$companydata->email ?? ""}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
    <style>
        .dropzone {
            min-height: 127px;
        }
        .dropzone .dz-default.dz-message:before{
            font-size: 50px;
            top: 60px;
        }
        .dropzone .dz-default.dz-message span{
            font-size: 18px;
            margin-top: 100px;
        }
    </style>
@endpush

@push('script')

<script type="text/javascript" src="{{asset('')}}assets/js/plugins/editors/summernote/summernote.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $( "#profileForm" ).validate({
            rules: {
                companyname: {
                    required: true,
                }
            },
            messages: {
                companyname: {
                    required: "Please enter name",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#profileForm');
                form.find('span.text-danger').remove();
                $('form#profileForm').ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            notify("Company Profile Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#newsForm" ).validate({
            rules: {
                company_id: {
                    required: true,
                }
            },
            messages: {
                company_id: {
                    required: "Please enter id",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#newsForm');
                form.find('span.text-danger').remove();
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
                            notify("Company News Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#supportForm" ).validate({
            rules: {
                number: {
                    required: true,
                },
                email: {
                    required: true,
                }
            },
            messages: {
                number: {
                    required: "Number value is required",
                },
                email: {
                    required: "Email value is required",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#supportForm');
                form.find('span.text-danger').remove();
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
                            notify("Company Support Details Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#noticeForm" ).validate({
            rules: {
                news: {
                    required: true,
                }
            },
            messages: {
                news: {
                    required: "Please enter name",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#noticeForm');
                $('input[name="notice"]').val($('.note-editable').html());
                form.find('span.text-danger').remove();
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
                            notify("Company Notice Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });
        
        $( "#websitepopupForm" ).validate({
            rules: {
                news: {
                    required: true,
                }
            },
            messages: {
                news: {
                    required: "Please enter name",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#websitepopupForm');
                
                $('input[name="websitepopup"]').val($('form#websitepopupForm').parent().find('.note-editable').html());
                form.find('span.text-danger').remove();
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
                            notify("Website Popup Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });
        
        

        $('.summernote').summernote({
            height: 350,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: false                 // set focus to editable area after initializing summernote
        });
        
        $('.summernote').each(function(i, obj) { 
i++;
$(obj).summernote({
  onChange: function() {
      $("#stage_"+i).val($("#description_"+i).code());
      //alert(i);
  }
});
});

        Dropzone.options.logoupload = {
            paramName: "logos", // The name that will be used to transfer the file
            maxFilesize: .5, // MB
            complete: function(file) {
                this.removeFile(file);
            },
            success : function(file, data){
                console.log(file);
                if(data.status == "success"){
                    notify("Company Logo Successfully Uploaded", 'success');
                }else{
                    notify("Something went wrong, please try again.", 'warning');
                }
            }
        };
    });
</script>
@endpush
