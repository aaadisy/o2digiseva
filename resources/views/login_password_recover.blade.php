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
                                text: 'We are checking your login credential',
                                onOpen: () => {
                                    swal.showLoading()
                                },
                                allowOutsideClick: () => !swal.isLoading()
                            });
                        },
                        success:function(data){
                            swal.close();
                            if(data.status == "Login"){
                                swal({
                                    type: 'success',
                                    title : 'Success',
                                    text: 'Successfully logged in.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    onClose: () => {
                                        window.location.reload();
                                    },
                                });
                            }
                            else if(data.status == "New Password Sent Successfully"){
                                swal({
                                    type: 'success',
                                    title : 'Success',
                                    text: 'New Password Sent Successfully.',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    onClose: () => {
                                        window.location.reload();
                                    },
                                });
                            }
                            else if(data.status == "TXN"){
                                swal({
                                    type: 'success',
                                    title : 'Success',
                                    text: 'Password Reset Successfully',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    onClose: () => {
                                        window.location.reload();
                                    },
                                });
                            }else if(data.status == "otpsent" || data.status == "preotp"){
                                $('#forgetotp').append(`<div class="wrap-input100 validate-input m-b-20" data-validate="Type Otp">
                                    <input class="form-control valid" type="password" name="otp" placeholder="Otp" required>
                                    <span class="focus-input100"></span>
                                </div><div class="wrap-input100 validate-input m-b-20" data-validate="Type Password">
                                    <input class="form-control valid" type="password" name="password" placeholder="Password" required>
                                    <span class="focus-input100"></span>
                                </div><div class="wrap-input100 validate-input m-b-20" data-validate="Type Confirm Password">
                                    <input class="form-control valid" type="password" name="cpassword" placeholder="Confirm Password" required>
                                    <span class="focus-input100"></span>
                                </div>`);
                                $('#req').val('otp');
                                if(data.status == "preotp"){
                                    $('b.successText').text('Please use previous otp sent on your mobile.');
                                    setTimeout(function(){
                                        $('b.successText').text('');
                                    }, 5000);
                                }
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
                    <form  action="{{route('authReset')}}" method="POST" class="login-form">
                        <div class="panel panel-body login-form">
                            <div class="text-center">
                                <div class="border-slate-300 text-slate-300">
                                    @if($mydata['company']->logo)
                                        <img src="{{asset('public/logos')}}/{{$mydata['company']->logo}}" style="width: 180px">
                                    @endif
                                </div>
                                <h5 class="content-group">Reset Password of your account <small class="display-block">Enter your credentials below</small></h5>
                            </div>
                            {{ csrf_field() }}
                            <p style="color:red"><b class="errorText"></b></p>
                            <p style="color:teal"><b class="successText"></b></p>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" class="form-control" placeholder="Username" name="mobile" placeholder="User name" pattern="[0-9]*" maxlength="11" minlength="10" required>
                                <input type="hidden" name="role" value="{{ Request()->segment(1) }}" >
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>
                            <div class="form-group has-feedback has-feedback-left" id="forgetotp">
                                
                            </div>
                            <input type="hidden" name="type" value="request" id="req">
                            

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Get New Password <i class="icon-circle-right2 position-right"></i></button>
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
