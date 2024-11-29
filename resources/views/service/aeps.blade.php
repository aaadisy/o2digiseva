@extends('layouts.app')
@section('title', "Yes Bank Aeps")

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="widget-profile-one">
                <div class="text-white card-box m-b-0 b-0 bg-primary p-lg text-center">
                    <div class="m-b-30">
                        <h4 class="text-white m-b-5">
                            {{ucfirst(Auth::user()->name)}}
                        </h4>
                        <small>{{ucfirst(Auth::user()->role->role_slug)}} of Company</small>
                    </div>
                </div>
                <div class="card-box p-0">
                    <table class="table table-bordered" style="width:100%">
                        <tr>
                            <th>AEPS Balance</th>
                            <th><i class="fa fa-inr"></i> {{Auth::user()->aepsbalance}}</th>
                        </tr>
                        <tr>
                            <th>Recent Fund Request (
                                @if ($fundrequest)
                                    {{$fundrequest->status}}
                                @endif
                            )</th>
                            <th><i class="fa fa-inr"></i> @if ($fundrequest)
                                {{$fundrequest->amount}}
                            @endif</th>
                        </tr>
                        <tr><th class="text-center" colspan="2">Settlement Bank Details</th></tr>
                        <tr>
                            <th>Account No.</th>
                            <th>{{Auth::user()->account}}</th>
                        </tr>
                        <tr>
                            <th>Bank</th>
                            <th>{{Auth::user()->bank}}</th>
                        </tr>
                        <tr>
                            <th>Ifsc</th>
                            <th>{{Auth::user()->ifsc}}</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Aeps Transaction <span class="customer_name m-l-15 text-capitalize"></span></h3>
                </div>
                <div class="panel-body">
                    <form action="{{route('aepstransaction')}}" method="post" arget="_blank">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Transaction Type :</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="11" id="Withdrawal" name="service" checked="">
                                            <label for="Withdrawal">Withdrawal</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="10" id="Balance" name="service">
                                            <label for="Balance">Balance Info</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Mobile Number :</label>
                                <input type="number" class="form-control" name="mobile"  autocomplete="off" placeholder="Enter mobile number">
                            </div>
                        </div>
                        <div class="row" id="transactionData">
                            <div class="form-group col-md-6">
                                <label>Amount :</label>
                                <input type="number" class="form-control" name="amount" autocomplete="off" placeholder="Enter Amount">
                            </div>
                        </div>

                        <div class="form-group text-center col-md-12">
                            <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light">Proceed</button>
                        </div>
                    </form>
                </div>  
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
    <style type="text/css">
        .md-radio:before {
            content: "";
        }
    </style>
@endpush

@push('script')
    <script type="text/javascript" src="{{asset('')}}/assets/js/jquery.validate.min.js"></script>
    <script src="{{ asset('/assets/js/jQuery.print.js') }}"></script>
    <script type="text/javascript">
        var USERSYSTEM, STOCK={}, DT=!1, AEPSURL="{{route('aepstransaction')}}";

        $(document).ready(function () {
            USERSYSTEM = {
                DEFAULT: function () {
                    USERSYSTEM.TRANSACTION();

                    $('[name="service"]').on('change', function () {
                        if($('[name="service"]:checked').val() == "11"){
                            var out = `<div class="form-group col-md-6">
                                            <label>Amount :</label>
                                            <input type="number" class="form-control" name="amount" autocomplete="off" placeholder="Enter Amount">
                                        </div>`;

                            $('#transactionData').append(out);
                        }else{
                            $('[name="amount"]').closest(".form-group").remove();
                        }
                    });
                },

                TRANSACTION: function(){
                    $( "#transactionForm" ).validate({
                        rules: {
                            mobile: {
                                required: true,
                                minlength: 10,
                                number : true,
                                maxlength: 11
                            },
                            amount: {
                                required: true,
                                number : true,
                                max:10000,
                                min:101
                            },
                        },
                        messages: {
                            mobile: {
                                required: "Please enter mobile number",
                                number: "Mobile number should be numeric",
                                minlength: "Your mobile number must be 10 digit",
                                maxlength: "Your mobile number must be 10 digit"
                            },
                            amount: {
                                required: "Please enter amount",
                                number: "Amount should be numeric",
                                max: "Amount shouldn't be graterthan 10000",
                                min: "Amount should be graterthan 100",
                            }
                        },
                        errorElement: "p",
                        errorPlacement: function ( error, element ) {
                            if ( element.prop( "name" ) === "bank" ) {
                                error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                            } else {
                                error.insertAfter( element );
                            }
                        },
                        submitHandler: function () {
                            return true;
                        }
                    });
                }
            }

            USERSYSTEM.DEFAULT();
        });
    </script>
@endpush