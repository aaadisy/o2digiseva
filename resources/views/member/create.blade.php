@extends('layouts.app')
@section('title', 'Create '.$type)
@section('pagetitle', 'Create '.$type)
@section('content')
<div class="content">
    <form class="memberForm" action="{{ route('memberstore') }}" method="post">
        {{ csrf_field() }}
        <div class="row">
            @if (!$role)
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Member Type Information</h3>
                        </div>
                        <div class="panel-body p-b-0">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Mamber Type</label>
                                    <select name="role_id" class="form-control select" required="">
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <input type="hidden" name="role_id" value="{{$role->id}}">
            @endif
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Personal Information</h3>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="" required="" placeholder="Enter Value">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Mobile</label>
                                <input type="number" name="mobile" required="" class="form-control" placeholder="Enter Value">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="" required="" placeholder="Enter Value">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Address</label>
                                <textarea name="address" class="form-control" rows="2" required="" placeholder="Enter Value"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>State</label>
                                <select name="state" class="form-control select" required="">
                                    <option value="">Select State</option>
                                    @foreach ($state as $state)
                                        <option value="{{$state->state}}">{{$state->state}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="" required="" placeholder="Enter Value">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Pincode</label>
                                <input type="number" name="pincode" class="form-control" value="" required="" maxlength="6" minlength="6" placeholder="Enter Value">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Buisness Information</h3>
                    </div>
                    <div class="panel-body p-b-0">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Shop Name</label>
                                <input type="text" name="shopname" class="form-control" value="" required="" placeholder="Enter Value">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Pancard Number</label>
                                <input type="text" name="pancard" class="form-control" value="" required="" placeholder="Enter Value">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Adhaarcard Number</label>
                                <input type="text" name="aadharcard" class="form-control" value="" required="" placeholder="Enter Value" maxlength="12" minlength="12">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           <!-- @if ($role->slug == "whitelable")
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Whitelable Information</h3>
                        </div>
                        <div class="panel-body p-b-0">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Company Name</label>
                                    <input type="text" name="companyname" class="form-control" value="" required="" placeholder="Enter Value">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Domain</label>
                                    <input type="url" name="website" class="form-control" value="" required="" placeholder="Enter Value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif-->

            <div class="col-md-4 col-md-offset-4">
                <button class="btn bg-slate btn-raised legitRipple btn-lg btn-block" type="submit" data-loading-text="Please Wait...">Submit</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        $( ".memberForm" ).validate({
            rules: {
                name: {
                    required: true,
                },
                mobile: {
                    required: true,
                    minlength: 10,
                    number : true,
                    maxlength: 10
                },
                email: {
                    required: true,
                    email : true
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                pincode: {
                    required: true,
                    minlength: 6,
                    number : true,
                    maxlength: 6
                },
                address: {
                    required: true,
                },
                aadharcard: {
                    required: true,
                    minlength: 12,
                    number : true,
                    maxlength: 12
                }
                @if ($role->slug == "whitelable")
                ,
                companyname: {
                    required: true,
                }
                ,
                website: {
                    required: true,
                    url : true
                }
                @endif
            },
            messages: {
                name: {
                    required: "Please enter name",
                },
                mobile: {
                    required: "Please enter mobile",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 10 digit",
                    maxlength: "Your mobile number must be 10 digit"
                },
                email: {
                    required: "Please enter email",
                    email: "Please enter valid email address",
                },
                state: {
                    required: "Please select state",
                },
                city: {
                    required: "Please enter city",
                },
                pincode: {
                    required: "Please enter pincode",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 6 digit",
                    maxlength: "Your mobile number must be 6 digit"
                },
                address: {
                    required: "Please enter address",
                },
                aadharcard: {
                    required: "Please enter aadharcard",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 12 digit",
                    maxlength: "Your mobile number must be 12 digit"
                }
                @if ($role->slug == "whitelable")
                ,
                companyname: {
                    required: "Please enter company name",
                }
                ,
                website: {
                    required: "Please enter company website",
                    url : "Please enter valid company url"
                }
                @endif
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
                var form = $('form.memberForm');
                form.find('span.text-danger').remove();
                $('form.memberForm').ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form[0].reset();
                            $('select').val('');
                            $('select').trigger('change');
                            notify("Member Successfully Created" , 'success');
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
</script>
@endpush
