<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login To - {{$mydata['company']->companyname}}</title>

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/colors.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        .error {
            color:red
        }
    </style>
    <!-- /global stylesheets -->

    <!-- Core JS files -->
    <script type="text/javascript" src="{{asset('')}}assets/js/plugins/loaders/pace.min.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/core/libraries/jquery.min.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/core/libraries/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/plugins/loaders/blockui.min.js"></script>
    <!-- /core JS files -->


    <!-- Theme JS files -->
    <script type="text/javascript" src="{{asset('')}}assets/js/core/app.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.validate.min.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.form.min.js"></script>
    <script type="text/javascript" src="{{asset('')}}assets/js/core/sweetalert2.min.js"></script>
     <script>
        addEventListener("load", function () {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
            window.scrollTo(0, 1);
        }

        $( document ).ready(function() {
            $('form').on('click', '#otpResend', function(){
                var mobile = $( ".login-form" ).find('[name="mobile"]').val();
                if(mobile != ''){
                    $.ajax({
                        url: `{{route('authCheck')}}`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType:'json',
                        data:{'otp':'resend' , "mobile" : mobile},
                        beforeSend: function(){
                            swal({
                                title: 'Wait!',
                                text: 'Please wait, we are working on your request',
                                onOpen: () => {
                                    swal.showLoading()
                                },
                                allowOutsideClick: () => !swal.isLoading()
                            });
                        }
                    })
                    .done(function(data) {
                        swal.close();
                        $('b.successText').text('Otp sent on your mobile number');
                        setTimeout(function(){
                            $('b.successText').text('');
                        }, 5000);
                    })
                    .fail(function(errors) {
                        swal.close();
                        notify('Oops', errors.status+'! '+errors.statusText, 'warning');
                    });
                }else{
                    $('b.errorText').text('Enter mobile number.');
                    setTimeout(function(){
                        $('b.errorText').text('');
                    }, 5000);
                }
            });

            $( ".login-form" ).validate({
                rules: {
                    mobile: {
                        required: true,
                        minlength: 10,
                        number : true,
                        maxlength: 11
                    },
                    password: {
                        required: true,
                    },
                    otp: {
                        required: true,
                    }
                },
                messages: {
                    mobile: {
                        required: "Please enter mobile number",
                        number: "Mobile number should be numeric",
                        minlength: "Your mobile number must be 10 digit",
                        maxlength: "Your mobile number must be 10 digit"
                    },
                    password: {
                        required: "Please enter password",
                    },
                    otp: {
                        required: "Please enter otp",
                    }
                },
                errorElement: "p",
                errorPlacement: function ( error, element ) {
                    if ( element.prop("tagName").toLowerCase() === "select" ) {
                        error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                    } else {
                        error.insertAfter( element );
                        $
                    }
                },
                submitHandler: function () {
                    var form = $('.login-form');
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            swal({
                                title: 'Wait!',
                                text: 'We are submiting your Signup request',
                                onOpen: () => {
                                    swal.showLoading()
                                },
                                allowOutsideClick: () => !swal.isLoading()
                            });
                        },
                        success:function(data){
                            swal.close();
                            if(data.status == "success"){
                                swal({
                                    type: 'success',
                                    title : 'Success',
                                    text: 'Successfully Registered Please check Your Whatsapp For Login Details.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    onClose: () => {
                                        window.location.href = 'signin';


                                    },
                                });
                            }
                            else if(data.status == "fail"){
                                swal({
                                    type: 'failed',
                                    title : 'Failed',
                                    text: 'Registration unsucessfull please try again',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    onClose: () => {
                                        window.location.reload();
                                    },
                                });
                            }
                        },
                        error: function(errors) {
                            swal.close();
                            if(errors.status == '400'){
                                $('b.errorText').text(errors.responseJSON.status);
                                setTimeout(function(){
                                    $('b.errorText').text('');
                                }, 5000);
                            }else{
                                $('b.errorText').text('Something went wrong, try again later.');
                                setTimeout(function(){
                                    $('b.errorText').text('');
                                }, 5000);
                            }
                        }
                    });
                }
            });
        });
    </script>
    <!-- /theme JS files -->

</head>

<body class="login-container">

    <!-- Main navbar -->
    <div class="navbar navbar-inverse">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{url('')}}" style="font-weight: bold; font-size: 20px">
                {{$mydata['company']->companyname}}
            </a>
        </div>

        <div class="navbar-collapse collapse" id="navbar-mobile">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="#">
                        <i class="icon-phone"></i> <span class="position-right"> {{$mydata['supportnumber']}}</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <i class="icon-envelop3"></i> <span class="position-right"> {{$mydata['supportemail']}}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- /main navbar -->


    <!-- Page container -->
    <div class="page-container">

        <!-- Page content -->
        <div class="page-content">

            <!-- Main content -->
            <div class="content-wrapper">

                <!-- Content area -->
                <div class="content">

                    <!-- Simple login form -->
                    <form  action="{{route('signupstore')}}" method="POST" class="login-form">
                        <div class="panel panel-body login-form">
                            <div class="text-center">
                                <div class="border-slate-300 text-slate-300">
                                    @if($mydata['company']->logo)
                                        <img src="{{asset('public/logos')}}/{{$mydata['company']->logo}}" style="width: 180px">
                                    @endif
                                </div>
                                <h5 class="content-group">Sign Up / Register<small class="display-block">Enter your Details below</small></h5>
                            </div>
                            {{ csrf_field() }}
                            <p style="color:red"><b class="errorText"></b></p>
                            <p style="color:teal"><b class="successText"></b></p>
                            <h3 class="panel-title">Personal Information</h3>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" class="form-control" placeholder="Full Name" name="name"  required>
                                
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" class="form-control" placeholder="Mobile Number" name="mobile"  pattern="[0-9]*" maxlength="11" minlength="10" required>
                               
                                <div class="form-control-feedback">
                                    <i class="icon-phone text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="email" class="form-control" placeholder="Email Address" name="email"  required>
                                
                                <div class="form-control-feedback">
                                    <i class="icon-envelope text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                
                                <textarea name="address" class="form-control" rows="2" required="" placeholder="Enter Complete Address" aria-required="true"></textarea>
                                
                                <!--<div class="form-control-feedback">
                                    <i class="icon-location text-muted"></i>
                                </div>-->
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <select name="state" class="form-control select" required="">
                                    <option value="">Select State</option>
                                    @foreach (\Myhelper::get_states() as $state)
                                        <option value="{{$state->state}}">{{$state->state}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" name="city" class="form-control" value="" required="" placeholder="Enter City">
                                
                                <div class="form-control-feedback">
                                    <i class="icon-address-book text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="number" name="pincode" class="form-control" value="" required="" maxlength="6" minlength="6" placeholder="Enter Pincode">
                                
                                <div class="form-control-feedback">
                                    <i class="icon-address-book text-muted"></i>
                                </div>
                            </div>
                             <h3 class="panel-title">Buisness Information</h3>
                             <div class="form-group has-feedback has-feedback-left">
                                  <input type="text" name="shopname" class="form-control" value="" required="" placeholder="Enter Shop Name">
                                
                                <div class="form-control-feedback">
                                    <i class="icon-address-book text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" name="pancard" class="form-control" value="" required="" placeholder="Enter Pancard Number">
                                
                                <div class="form-control-feedback">
                                    <i class="icon-address-book text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" name="aadharcard" class="form-control" value="" required="" placeholder="Enter Aadhar Card Number" maxlength="12" minlength="12">
                                
                                <div class="form-control-feedback">
                                    <i class="icon-address-book text-muted"></i>
                                </div>
                            </div>
                            
                            <div class="form-group has-feedback has-feedback-left">
                                <select name="role_id" class="form-control select" required="">
                                    <option value="">Want to Join as</option>
                                    
                                        <option value="4">Retailer</option>
                                        <option value="3">Distributor</option>
                                        <option value="3">Master Distributor</option>
                                        <option value="9">Channel Partner</option>
                                    
                                </select>
                            </div>
                           

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Register Now <i class="icon-circle-right2 position-right"></i></button>
                            </div>
                            
                            <div class="text-center">
                                <a href="/signin">Sign In</a>
                            </div>
                            
                        </div>
                    </form>
                    <!-- /simple login form -->


                    <!-- Footer -->
                    <div class="footer text-muted text-center">
                        &copy; {{date('Y')}}. Portal by <a href="http://{{$mydata['company']->website}}" target="_blank">{{$mydata['company']->companyname}}</a>
                    </div>
                    <!-- /footer -->

                </div>
                <!-- /content area -->

            </div>
            <!-- /main content -->

        </div>
        <!-- /page content -->

    </div>
    <!-- /page container -->

</body>
</html>
