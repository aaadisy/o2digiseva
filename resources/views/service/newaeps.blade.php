@extends('layouts.app')
@section('title', "Yes Bank Aeps")

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-8">
            <h4 class="page-title">Aeps</h4>
            <p class="page-title-alt text-inverse"> <b> Welcome to Aeps Services!</b></p>
        </div>
    </div>
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
                    <div class="p-20 text-center">
                        <a class="btn btn-md btn-primary waves-effect waves-light pull-left" data-toggle="modal" data-target="#aepsFundRequest">Fund Request</a>
                        <a class="btn btn-md btn-inverse waves-effect waves-light pull-right" data-toggle="modal" data-target="#aepsBankChange">Change Bank Details</a>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Aeps Transaction <span class="customer_name m-l-15 text-capitalize"></span></h3>
                </div>
                <div class="panel-body">
                    <form action="{{route('yesaepstransaction')}}" method="post" id="transactionForm" target="_blank">
                        {{ csrf_field() }}
                        <input type="hidden" name="type" value="transaction">
                        <input type="hidden" name="agree" value="true">
                        <input type="hidden" name="biodata">
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
                        </div>

                        <div class="row">
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

@if (Mycheck::hasNotRole('admin'))
    <div id="aepsBankChange" class="modal fade" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Bank Details Update</h4>
                </div>
                <form id="aepsBankChangeForm" method="post" action="{{ route('memberupdate') }}" enctype="multipart/form-data">
                <input type="hidden" name="id" value="{{Auth::id()}}">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="type" value="bank">
                        <div class="form-group col-md-6">
                            <label>Bank</label>
                            <input type="text" class="form-control" name="bank" value="{{Auth::user()->bank}}" placeholder="Enter Value" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Account</label>
                            <input type="text" class="form-control" name="account" value="{{Auth::user()->account}}" placeholder="Enter Value" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Ifsc</label>
                            <input type="text" class="form-control" name="ifsc" value="{{Auth::user()->ifsc}}" placeholder="Enter Value" required="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-inverse waves-effect waves-light" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update</button>
                    <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
        </div>
    </div><!-- /.modal -->
@endif
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