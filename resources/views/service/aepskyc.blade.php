@extends('layouts.app')
@section('title',"Aeps Registration")

@section('content')
    <div class="row">
        <div class="col-sm-6">
            <h4 class="page-title">Aeps Registration</h4>
            <p class="text-muted page-title-alt">Welcome to {{Auth::user()->company->name ?? ''}} !</p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12" {{($aepsdata) ? 'style=display:none' : ''}} id="new">
            <form action="{{route('kyctransaction') ?? ''}}" method="post" id="transactionForm" enctype="multipart/form-data" novalidate="">
                <input type="hidden" name="type" value="new">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Personal Details</h4>
                    </div>
                    <div class="panel-body">
                        {{ csrf_field()  ?? ''}}
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Name</label>
                                <input type="text" class="form-control" value="{{Auth::user()->name ?? ''}}" autocomplete="off" name="name" placeholder="Enter Your Name" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Email</label>
                                <input type="text" class="form-control" value="{{Auth::user()->email ?? ''}}" name="email" autocomplete="off" placeholder="Enter Your Email Address" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Mobile </label>
                                <input type="text" class="form-control" autocomplete="off" value="{{Auth::user()->mobile ?? ''}}" name="mobile" placeholder="Enter Your Mobile Number" required>
                            </div>
                        </div>

                        <div class="row">
                             <div class="form-group col-md-4">
                                <label>Pancard Number</label>
                                <input type="text" class="form-control" value="{{Auth::user()->pancard ?? ''}}" name="pancard" autocomplete="off" placeholder="Enter Your Pancard Address" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Aadhar Number </label>
                                <input type="text" class="form-control" autocomplete="off" value="{{Auth::user()->aadhar ?? ''}}" name="aadhar" placeholder="Enter Your Mobile Number" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label>state </label>
                                <input type="text" class="form-control" name="state" value="{{Auth::user()->state ?? ''}}" autocomplete="off" placeholder="Enter Your Pancard" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Address</label>
                                <textarea class="form-control" name="address" placeholder="Enter Your Address" required>{{Auth::user()->address ?? ''}}</textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>City</label>
                                <input type="text" class="form-control" value="{{Auth::user()->city ?? ''}}" autocomplete="off" name="city" placeholder="Enter Your City">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Pincode </label>
                                <input type="text" class="form-control" name="pincode" value="{{Auth::user()->pincode ?? ''}}" autocomplete="off" placeholder="Enter Your Pincode">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Shop Name</label>
                                <input type="text" class="form-control" name="shopname" value="{{Auth::user()->shopname ?? ''}}" placeholder="Enter Shop Name" required>
                            </div>
                        </div>
                    </div>  
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Personal Details</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Aadhar Copy</label>
                                <input type="file" class="form-control" name="aadharpics">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Pancard</label>
                                <input type="file" class="form-control" name="pancardpics">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Self Declaration</label>
                                <input type="file" class="form-control" name="selfdeclares">
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light">Proceed</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12" {{(!$aepsdata) ? 'style=display:none' : ''}} id="re">
            <div class="panel panel-primary text-center">
                <div class="panel-body">
                    <h3 class="text-danger text-center">Your Kyc Approval is {{$aepsdata->status ?? 'Pending'}} , Remark -{{$aepsdata->remark ?? ''}}  </h3>
                    @if (isset($aepsdata->status) && $aepsdata->status == "rejected")
                        <a href="{{url('aeps/kyc/action')}}?id={{$aepsdata->id ?? ''}}&type=re" class="btn btn-primary" style="margin:auto">Resubmit Kyc</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script type="text/javascript">
        var USERSYSTEM, STOCK={}, DT=!1;

        $(document).ready(function () {
            USERSYSTEM = {
                DEFAULT: function () {
                    USERSYSTEM.BEFORE_SUBMIT();
                },

                BEFORE_SUBMIT: function(){
                    $('#transactionForm').submit(function(){
                        var pass = true;

                        $(this).find('.form-control').each(function(x, v, ){
                            pass = SYSTEM.VALIDATE('empty', $(this));
                            if(!pass){
                                return false;
                            }
                        });
                        console.log(pass);
                        if(pass)
                        {
                            USERSYSTEM.ADD();
                        }

                        return false;
                    });
                },

                ADD: function(){
                    SYSTEM.FORMSUBMIT($('#transactionForm'), function(data){
                        if(!data.statusText){
                            if(data.status == "success"){
                                $('#transactionForm')[0].reset();
                                swal({
                                    title: 'Success',
                                    text:  'Aeps Registration sussessfully Complete, Wait for Approval',
                                    type:  'success',
                                    confirmButtonText: 'Ok'
                                }).then((result) => {
                                  $('#new').fadeOut(400);
                                  $('#re').fadeIn(400);
                                })
                            }else{
                                SYSTEM.SHOWERROR(data, $('#transactionForm'), 'inline');
                            }
                        }else{
                            SYSTEM.SHOWERROR(data, $('#transactionForm'), 'inline');
                        }
                    });
                }
            }

            USERSYSTEM.DEFAULT();
        });
    </script>
@endpush

