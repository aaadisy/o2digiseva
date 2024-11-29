<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Delete Account - {{$mydata['company']->companyname}}</title>

    <!-- Global stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/bootstrap.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/core.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/components.css" rel="stylesheet" type="text/css">
    <link href="{{asset('')}}assets/css/colors.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        .error {
            color: red
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
        $(document).ready(function () {

    // Initial state - hide OTP section
    $('#otpSection').hide();

    // Handle the mobile number form submission
    $("#mobileForm").validate({
        rules: {
            mobile: {
                required: true,
                minlength: 10,
                maxlength: 11,
                number: true
            }
        },
        messages: {
            mobile: {
                required: "Please enter your mobile number",
                minlength: "Mobile number must be at least 10 digits",
                maxlength: "Mobile number can't exceed 11 digits",
                number: "Please enter a valid mobile number"
            }
        },
        submitHandler: function (form) {
            var mobile = $('[name="mobile"]').val();
            $.ajax({
                url: `{{route('sendOTP')}}`, // The route to send OTP, NOT deleteAccount
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                data: { "mobile": mobile },
                beforeSend: function () {
                    swal({
                        title: 'Wait!',
                        text: 'Sending OTP...',
                        onOpen: () => { swal.showLoading() },
                        allowOutsideClick: () => !swal.isLoading()
                    });
                },
                success: function (response) {
                    swal.close();
                    
                    
                    if (response.status == 'OTP_SENT') {
                        $('b.successText').text('OTP sent to your mobile number.');
                        $('#mobileForm').hide(); 
                        $('#otpSection').show(); // Show the OTP section after OTP is sent
                        
                        
                        $('#submitMobile').prop('disabled', true); // Disable mobile submit button
                    } else {
                        $('b.errorText').text('Failed to send OTP. Please try again.');
                    }
                },
                error: function (errors) {
                    swal.close();
                    $('b.errorText').text('Something went wrong. Please try again later.');
                }
            });
        }
    });

    // Handle OTP and account deletion submission
    $("#otpForm").validate({
        rules: {
            otp: {
                required: true,
                minlength: 4
            }
        },
        messages: {
            otp: {
                required: "Please enter the OTP",
                minlength: "OTP must be at least 4 digits"
            }
        },
        submitHandler: function (form) {
            var otp = $('[name="otp"]').val();
            var mobile = $('[name="mobile"]').val();
            $.ajax({
                url: `{{route('deleteAccount')}}`, // The route to delete the account after OTP verification
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                data: { "mobile": mobile, "otp": otp },
                beforeSend: function () {
                    swal({
                        title: 'Wait!',
                        text: 'Verifying OTP and deleting account...',
                        onOpen: () => { swal.showLoading() },
                        allowOutsideClick: () => !swal.isLoading()
                    });
                },
                success: function (response) {
                    swal.close();
                    if (response.status == 'Deleted') {
                        swal({
                            type: 'success',
                            title: 'Success',
                            text: 'Your account has been deleted.',
                            showConfirmButton: false,
                            timer: 2000,
                            onClose: () => {
                                window.location.href = "{{url('/')}}"; // Redirect to homepage
                            },
                        });
                    } else if (response.status == 'InvalidOTP') {
                        $('b.errorText').text('Invalid OTP. Please try again.');
                    } else {
                        $('b.errorText').text('Failed to delete account. Please try again.');
                    }
                },
                error: function (errors) {
                    swal.close();
                    $('b.errorText').text('Something went wrong. Please try again later.');
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

                    <!-- Mobile Number Form -->
                    <form id="mobileForm">
                        <div class="panel panel-body login-form">
                            <div class="text-center">
                                <div class="border-slate-300 text-slate-300">
                                    @if($mydata['company']->logo)
                                    <img src="{{asset('public/logos')}}/{{$mydata['company']->logo}}" style="width: 180px">
                                    @endif
                                </div>
                                <h5 class="content-group">Delete Your Account <small class="display-block">Enter your mobile number to receive an OTP</small></h5>
                            </div>
                            {{ csrf_field() }}
                            <p style="color:red"><b class="errorText"></b></p>
                            <p style="color:teal"><b class="successText"></b></p>
                        
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" class="form-control" placeholder="Mobile Number" name="mobile" pattern="[0-9]*" maxlength="11" minlength="10" required>
                                <div class="form-control-feedback">
                                    <i class="icon-user text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" id="submitMobile" class="btn btn-primary btn-block">Submit <i class="icon-circle-right2 position-right"></i></button>
                            </div>
                        </div>
                    </form>
                    <!-- /Mobile Number Form -->

                    <!-- OTP Section -->
                    <form id="otpForm">
                        <div id="otpSection" class="panel panel-body login-form">
                            
                                <div class="text-center">
                                <div class="border-slate-300 text-slate-300">
                                    @if($mydata['company']->logo)
                                    <img src="{{asset('public/logos')}}/{{$mydata['company']->logo}}" style="width: 180px">
                                    @endif
                                </div>
                                <h5 class="content-group">Delete Your Account </h5>
                            </div>
                            {{ csrf_field() }}
                            <p style="color:red"><b class="errorText"></b></p>
                            <p style="color:teal"><b class="successText"></b></p>
                            <div class="form-group has-feedback has-feedback-left">
                                <input type="text" class="form-control" placeholder="Enter OTP" name="otp" minlength="4" required>
                                <div class="form-control-feedback">
                                    <i class="icon-lock2 text-muted"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-danger btn-block">Delete Account <i class="icon-circle-right2 position-right"></i></button>
                            </div>
                        </div>
                    </form>
                    <!-- /OTP Section -->

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
