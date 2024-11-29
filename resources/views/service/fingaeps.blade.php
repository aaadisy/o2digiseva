@extends('layouts.app')
@section('title', "DigiSeva Aeps")

@section('content')
<div class="content">
    <div class="row" style="background: #fff;">
        <center>

       
        <div class="col-sm-4">
        <img src="{{asset('assets/digis.png')}}" class="img-responsive" style="width: 260px;height: 130px;">
        </div>
        <div class="col-md-4">
           
               
                <img src="{{asset('assets/aeps_thumb.png')}}" class="img-responsive" style="width: 75%;">
           
        </div>
        <div class="col-sm-4">
            <h3 class="btn bg-warning">DigiSeva Support</h3></br>
            <a href="tel:917001491919">+91 7001491919</a></br>
            <a href="tel:919679427505">+91 9679427505</a>
        </div>
        </center>
    </div>
    @php
    $currentDate = now()->toDateString();

    @endphp

    @if($agent && $agent->status == 'approved')
    <div class="row">
     <!---   <div class="col-md-4">
            <div class="widget-profile-one">
                <div class="text-white card-box m-b-0 b-0 bg-primary p-lg text-center">
                    <div class="m-b-30">
                        <h4 class="text-white m-b-5">
                            {{ucfirst(Auth::user()->name)}}
                        </h4>
                        <small>{{ucfirst(Auth::user()->role->slug)}} of Company</small>
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
                                )
                            </th>
                            <th><i class="fa fa-inr"></i> @if ($fundrequest)
                                {{$fundrequest->amount}}
                                @endif
                            </th>
                        </tr>
                        <tr>
                            <th class="text-center" colspan="2">Settlement Bank Details</th>
                        </tr>
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
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>  -->

        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">AADHAAR ENABLED PAYMENT SYSTEM (AEPS) <span class="customer_name m-l-15 text-capitalize"></span></h3>
                    <div class="heading-elements">
                    <a href="{{route('statement', ['type' => 'aeps'])}}"><button class="btn bg-black btn-xs legitRipple btn-lg" style="font-size: 18px;">AEPS STATEMENT</button></a>
                    <a href="{{route('statement', ['type' => 'awallet'])}}"><button class="btn bg-black btn-xs legitRipple btn-lg" style="font-size: 18px;">AEPS WALLET</button></a>
                    <a href="{{route('bill', ['type' => 'wt'])}}"><button class="btn bg-black btn-xs legitRipple btn-lg" style="font-size: 18px;">COMMISSION TRANSFER</button></a>
                        
                </div>
                </div>
                <div class="panel-body">
                    <form action="{{route('ifaepstransaction')}}" method="post" id="transactionForm">
                        {{ csrf_field() }}
                        <input type="hidden" name="type" value="transaction">
                        <input type="hidden" name="agree" value="true">
                        <input type="hidden" name="biodata">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Transaction Type :</label>
                                <div class="row">

                                   

                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MS" id="MS" name="transactionType" checked="">
                                            <label for="MS" style="padding: 0 25px">MINI STATEMENT</label>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="BE" id="Balance" name="transactionType">
                                            <label for="Balance" style="padding: 0 25px">BALANCE INFO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="CW" id="Withdrawal" name="transactionType" >
                                               
                                                <label  id="withdrawalLabel" for="Withdrawal" style="padding: 0 25px; cursor: pointer;" >
                                                    WITHDRAWAL
                                                </label>
                                        </div>
                                    </div>

<div class="col-md-2">
    <div class="md-radio m-b-0">
        <input autocomplete="off" type="radio" value="M" id="M" name="transactionType" 
               @if(Request::segment(3) === 'AP') checked @endif>
        <label id="aadharPayLabel" for="M" style="padding: 0 25px; cursor: pointer;">
            AADHAR PAY
        </label>
    </div>
</div>

@if(is_null($agent->ap_auth) || strtotime($agent->ap_auth) !== strtotime(date('Y-m-d')))

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Listen for changes on the checkbox with ID 'M'
        document.getElementById('M').addEventListener('change', function() {
            // Redirect to the specified route if the checkbox is checked
            if (this.checked) {
                window.location.href = "{{ route('ifaeps', ['type' => 'ap2fa']) }}";
            }
        });
    });
</script>
@endif







                                    <div class="col-md-2">
                                        
                                    <a href="{{route('fund', ['type' => 'aeps'])}}">
                                        <div class="md-radio m-b-0">
                                            <label for="MTB" style="padding: 0 25px">MOVE TO BANK</label>
                                        </div>
                                        </a>
                                    </div>
                                    <div class="col-md-2">
                                        
                                    <a href="{{route('bill', ['type' => 'cashdeposit'])}}">
                                        <div class="md-radio m-b-0">
                                            <label for="MTB" style="padding: 0 25px">CASH DEPOSIT</label>
                                        </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Device Type :</label>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MORPHO_PROTOBUF" id="MORPHO_PROTOBUF" name="device" checked="">
                                            <label for="MORPHO_PROTOBUF" style="padding: 0 25px">MORPHO</label>
                                        </div>
                                    </div>
                                    @if(\Myhelper::service_active('morphossl'))
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MORPHO_PROTOBUF_SSL" id="MORPHO_PROTOBUF_SSL" name="device">
                                            <label for="MORPHO_PROTOBUF_SSL" style="padding: 0 25px">MORPHO SSL</label>
                                        </div>
                                        </div>
                                        @endif
                                        
                                        
                                    
                                    <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio" value="MANTRA_PROTOBUF" id="MANTRA_PROTOBUF" name="device">
                                            <label for="MANTRA_PROTOBUF" style="padding: 0 25px">MANTRA</label>
                                        </div>
                                    </div>
                                     <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio"  value="MORPHO_PROTOBUF_L1WS" id="MORPHO_PROTOBUF_L1WS" name="device">
                                            <label for="MORPHO_PROTOBUF_L1WS" style="padding: 0 25px">MORPHO L1</label>
                                        </div>
                                    </div>
                                     <div class="col-md-2">
                                        <div class="md-radio m-b-0">
                                            <input autocomplete="off" type="radio"  value="MORPHO_PROTOBUF_L1" id="MORPHO_PROTOBUF_L1" name="device">
                                            <label for="MORPHO_PROTOBUF_L1" style="padding: 0 25px">MORPHO L1 SSL</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Mobile Number :</label>
                                <input type="number" class="form-control" name="mobileNumber" id="typeyourid" autocomplete="off" placeholder="Enter mobile number" pattern="\d{10}" maxlength="10" value="9999999999">
                            </div>

                            <div class="form-group col-md-6">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Aadhar Number :</label>
                                <input type="text" id="aadharcardnum" class="form-control" name="adhaarNumber" autocomplete="off" placeholder="Enter aadhar number">
                            </div>
                        </div>

                        <div class="row" id="transactionBankData">
                            <div class="form-group col-md-6 CASH">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Bank :</label>
                                <select name="nationalBankIdentificationNumber" class="form-control select" required="">
                                    <option value="">Select Bank</option>
                                    @foreach ($aepsbanks as $item)
                                    <option value="{{$item->iinno}}">{{$item->bankName}}</option>
                                    @endforeach
                                </select>
                            </br>
                            <div class="bank-blocks">
                            <button class="bank-btn" data-bank="607105">INDIAN</button>
                            <button class="bank-btn" data-bank="607027">PNB</button>
                            <button class="bank-btn" data-bank="607094">SBI</button>
                            <button class="bank-btn" data-bank="607066">UCO</button>
                            <button class="bank-btn" data-bank="990320">AIRTEL</button>
                            <button class="bank-btn" data-bank="508505">BOI</button>
                            <button class="bank-btn" data-bank="606985">BOB</button>
                            <button class="bank-btn" data-bank="607396">CANARA</button>
                            <button class="bank-btn" data-bank="608001">FINO</button>
                            <button class="bank-btn" data-bank="607063">BGVB</button>
                            <button class="bank-btn" data-bank="607152">HDFC</button>
                            <button class="bank-btn" data-bank="607095">IDBI</button>
                            <button class="bank-btn" data-bank="608117">IDFC</button>
                            <button class="bank-btn" data-bank="508534">ICICI</button>
                            <button class="bank-btn" data-bank="607126">IOB</button>
                            <button class="bank-btn" data-bank="608314">IPPB</button>
                            <button class="bank-btn" data-bank="607768">NSDL</button>
                            <button class="bank-btn" data-bank="608032">PAYTM</button>
                            <button class="bank-btn" data-bank="607161">UNION</button>

                            </div>
                            </div>
                            <div class="form-group col-md-6 PAY" style="display: none">
                                <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Bank :</label>
                                <select name="nationalBankIdentificationNumber" class="form-control select" required="">
                                    <option value="">Select Banks</option>
                                    @foreach ($aepsbanks as $item)
                                    <option value="{{$item->iinno}}">{{$item->bankName}}</option>
                                    @endforeach
                                </select>
                            </br>
                            <div class="bank-blocks">
                            <button class="bank-btn" data-bank="607105">INDIAN</button>
                            <button class="bank-btn" data-bank="607027">PNB</button>
                            <button class="bank-btn" data-bank="607094">SBI</button>
                            <button class="bank-btn" data-bank="607066">UCO</button>
                            <button class="bank-btn" data-bank="990320">AIRTEL</button>
                            <button class="bank-btn" data-bank="508505">BOI</button>
                            <button class="bank-btn" data-bank="606985">BOB</button>
                            <button class="bank-btn" data-bank="607396">CANARA</button>
                            <button class="bank-btn" data-bank="608001">FINO</button>
                            <button class="bank-btn" data-bank="607063">BGVB</button>
                            <button class="bank-btn" data-bank="607152">HDFC</button>
                            <button class="bank-btn" data-bank="607095">IDBI</button>
                            <button class="bank-btn" data-bank="608117">IDFC</button>
                            <button class="bank-btn" data-bank="508534">ICICI</button>
                            <button class="bank-btn" data-bank="607126">IOB</button>
                            <button class="bank-btn" data-bank="608314">IPPB</button>
                            <button class="bank-btn" data-bank="607768">NSDL</button>
                            <button class="bank-btn" data-bank="608032">PAYTM</button>
                            <button class="bank-btn" data-bank="607161">UNION</button>

                            </div>
                            </div>
                            
                            
                            <div class="row" id="transactionData">
                            </div>
                        </div>

                        <div class="form-group text-center col-md-12 m-b-0">
                            <button type="button" class="btn btn-warning btn-lg waves-effect waves-light" id="scan">Scan</button>
                            <button id="proceed" type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @elseif($agent && $agent->status == 'pending')
    <div class="col-md-12"  id="re">
            <div class="panel panel-primary text-center">
                <div class="panel-body">
                    <h3 class="text-danger text-center">Your Kyc Approval is {{$agent->status ?? 'Pending'}} , Remark -{{$agent->remark ?? ''}} </h3>
                    @if (isset($aepsdata->status) && $aepsdata->status == "rejected")
                    <a href="{{url('aeps/kyc/action')}}?id={{$agent->id ?? ''}}&type=re" class="btn btn-primary" style="margin:auto">Resubmit Kyc</a>
                    @endif
                </div>
            </div>
    </div>
    @elseif($agent && $agent->status == 'rejected')
    <div class="col-md-12"  id="re">
            <div class="panel panel-primary text-center">
                <div class="panel-body">
                    <h3 class="text-danger text-center">Your Kyc Approval is {{$agent->status ?? 'Pending'}} , Remark -{{$agent->remark ?? ''}} </h3>
                    
                </div>
            </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-md-offset-2">
            <form action="{{route('ifaepstransaction') ?? ''}}" method="post" id="fingkycForm" enctype="multipart/form-data" novalidate="">
                <input type="hidden" name="transactionType" value="useronboardresubmit">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Personal Details</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Full Name</label>
                                <input type="text" class="form-control" value="{{ $agent->merchantName ?? ''}}" autocomplete="off" name="merchantName" placeholder="Enter Your Name" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Mobile </label>
                                <input type="text" class="form-control" autocomplete="off" value="{{ $agent->merchantPhoneNumber ?? ''}}" name="merchantPhoneNumber" placeholder="Enter Your Mobile Number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Bank Name</label>
                                <input type="text" class="form-control" value="{{ $agent->companyBankName ?? ''}}" name="bank" autocomplete="off" placeholder="Enter Your bank name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Branch Name</label>
                                <input type="text" class="form-control" value="{{ $agent->bankBranchName ?? ''}}" name="branch" autocomplete="off" placeholder="Enter Your branch name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>IFSC</label>
                                <input type="text" class="form-control" value="{{ $agent->bankIfscCode ?? ''}}" name="ifsc" autocomplete="off" placeholder="Enter Your ifsc code" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Account Number</label>
                                <input type="number" class="form-control" value="{{ $agent->companyBankAccountNumber ?? ''}}" name="account" autocomplete="off" placeholder="Enter Your account Number" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Account Name</label>
                                <input type="text" class="form-control" value="{{ $agent->bankAccountName ?? ''}}" name="account_name" autocomplete="off" placeholder="Enter Your account Name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Pancard Number</label>
                                <input type="text" class="form-control" value="{{ $agent->userPan ?? ''}}" name="userPan" autocomplete="off" placeholder="Enter Your Pancard Address" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Aadhar Number</label>
                                <input type="text" class="form-control" value="{{ $agent->merchantAadhar ?? ''}}" name="merchantAadhar" autocomplete="off" placeholder="Enter Your Aadhar Number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Address</label>
                                    <textarea class="form-control" name="merchantAddress" placeholder="Enter Your Address" required>{{ $agent->merchantAddress ?? ''}}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Address 2</label>
                                    <input type="text" class="form-control" name="merchantAddress2" value="{{ $agent->merchantAddress2 ?? ''}}" autocomplete="off" placeholder="Enter Your Address 2" required="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>City</label>
                                    <input type="text" class="form-control" name="merchantCityName" value="{{ $agent->merchantCityName ?? ''}}" autocomplete="off" placeholder="Enter Your City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>District</label>
                                    <input type="text" class="form-control" name="merchantDistrictName" value="{{ $agent->merchantDistrictName ?? ''}}" autocomplete="off" placeholder="Enter Your City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>state </label>
                                    <select name="merchantState" class="form-control select" required="">
                                        <option value="">Select State</option>
                                        @php
                                        $finostate = $state;
                                        @endphp

                                        @foreach ($state as $state)
                                        @if (isset($agent->merchantState))
                                        @if (strtoupper($agent->merchantState) == $state->state)
                                        <option value="{{$state->stateId}}" selected="">{{$state->state}}</option>
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Pincode </label>
                                    <input type="text" class="form-control" name="merchantPinCode" value="{{ $agent->merchantPinCode ?? ''}}" autocomplete="off" placeholder="Enter Your Pincode" required="">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                        <button type="button" id="copyAddressButton" class="btn btn-primary">Copy Personal Address to Shop Address</button>


                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Shop Address</label>
                                    <input type="text" class="form-control" name="shopAddress" value="{{ $agent->shopAddress ?? ''}}" autocomplete="off" placeholder="Enter Shop Address" required="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Shop City</label>
                                    <input type="text" class="form-control" name="shopCity" value="{{ $agent->shopCity ?? ''}}" autocomplete="off" placeholder="Enter Shop City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Shop District</label>
                                    <input type="text" class="form-control" name="shopDistrict" value="{{ $agent->shopDistrict ?? ''}}" autocomplete="off" placeholder="Enter Shop City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Shop state </label>
                                    <select name="shopState" class="form-control" required="">
                                        <option value="">Select Shop State</option>
                                        @foreach ($finostate as $state)
                                        @if (isset($agent->shopState))
                                        @if (strtoupper($agent->shopState) == $state->state)
                                        <option value="{{$state->stateId}}" selected="">{{$state->state}}</option>
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Shop Pincode </label>
                                    <input type="text" class="form-control" name="shopPincode" value="{{ $agent->shopPincode ?? ''}}" autocomplete="off" placeholder="Enter Shop Pincode" required="">
                                </div>
                            </div>
                        </div>


                        <div class="row">
                      <!--      <div class="form-group col-md-6">
                                <label>Pancard Pic</label>
                                <input type="file" name="pancardPics" class="form-control" value="" placeholder="Enter Value" >
                            </div>

                            <div class="form-group col-md-6">
                                <label>Aadharcard Pic</label>
                                <input type="file" name="aadharPics" class="form-control" value="" placeholder="Enter Value" >
                            </div>

                            <div class="form-group col-md-6">
                                <label>Masked Aadharcard Pic</label>
                                <input type="file" name="maskedAadharImages" class="form-control" value="" placeholder="Enter Value" >
                            </div>

                            <div class="form-group col-md-6">
                                <label>BackgroundImage Of Shop</label>
                                <input type="file" name="backgroundImageOfShops" class="form-control" value="" placeholder="Enter Value" >
                            </div>  -->
                            <div class="form-group col-md-6">
                                <label>Shop / Company Type</label>
                               <select name="mccCode" id="mccCode" class="form-control">
    <option value="">Select MCC Code</option>
    <option value="4215" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4215' ? 'selected' : '' }}>
        Courier services — air and ground and freight forwarders
    </option>
    <option value="4722" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4722' ? 'selected' : '' }}>
        Travel agencies and tour operators
    </option>
    <option value="4789" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4789' ? 'selected' : '' }}>
        Transportation services
    </option>
    <option value="4812" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4812' ? 'selected' : '' }}>
        Telecommunication equipment and telephone sales
    </option>
    <option value="4814" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4814' ? 'selected' : '' }}>
        Telecommunication services, including local and long distance calls
    </option>
    <option value="4816" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4816' ? 'selected' : '' }}>
        Computer network/information services
    </option>
    <option value="4900" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '4900' ? 'selected' : '' }}>
        Utilities — electric, gas, water and sanitary
    </option>
    <option value="5099" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5099' ? 'selected' : '' }}>
        Durable goods
    </option>
    <option value="5111" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5111' ? 'selected' : '' }}>
        Stationery, office supplies and printing and writing paper
    </option>
    <option value="5137" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5137' ? 'selected' : '' }}>
        Men’s, women’s and children’s uniforms and commercial clothing
    </option>
    <option value="5192" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5192' ? 'selected' : '' }}>
        Books, periodicals and newspapers
    </option>
    <option value="5193" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5193' ? 'selected' : '' }}>
        Florists’ supplies, nursery stock and flowers
    </option>
    <option value="5199" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5199' ? 'selected' : '' }}>
        Non-durable goods
    </option>
    <option value="5311" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5311' ? 'selected' : '' }}>
        Department Stores
    </option>
    <option value="5331" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5331' ? 'selected' : '' }}>
        Variety Stores
    </option>
    <option value="5411" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5411' ? 'selected' : '' }}>
        Groceries and supermarkets
    </option>
    <option value="5451" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5451' ? 'selected' : '' }}>
        Dairies
    </option>
    <option value="5462" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5462' ? 'selected' : '' }}>
        Bakeries
    </option>
    <option value="5533" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5533' ? 'selected' : '' }}>
        Automotive parts and accessories outlets
    </option>
    <option value="5651" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5651' ? 'selected' : '' }}>
        Family clothing shops
    </option>
    <option value="5655" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5655' ? 'selected' : '' }}>
        Sports and riding apparels
    </option>
    <option value="5661" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5661' ? 'selected' : '' }}>
        Shoe shops
    </option>
    <option value="5722" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5722' ? 'selected' : '' }}>
        Household appliance shops
    </option>
    <option value="5732" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5732' ? 'selected' : '' }}>
        Electronics Shops
    </option>
    <option value="5734" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5734' ? 'selected' : '' }}>
        Computer software outlets
    </option>
    <option value="5814" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5814' ? 'selected' : '' }}>
        Fast food restaurants
    </option>
    <option value="5942" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5942' ? 'selected' : '' }}>
        Bookshops
    </option>
    <option value="5943" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5943' ? 'selected' : '' }}>
        Stationery, office and school supply shops
    </option>
    <option value="5947" {{ isset($agent->mccCode) && strtoupper($agent->mccCode) == '5947' ? 'selected' : '' }}>
        Gift, card, novelty and souvenir shops
    </option>
</select>


                            </div>

                        </div>

                        <div class="panel-footer text-center">
                            <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12" {{(!$agent) ? 'style=display:none' : ''}} id="re">
            <div class="panel panel-primary text-center">
                <div class="panel-body">
                    <h3 class="text-danger text-center">Your Kyc Approval is {{$agent->status ?? 'Pending'}} , Remark -{{$agent->remark ?? ''}} </h3>
                    @if (isset($aepsdata->status) && $aepsdata->status == "rejected")
                    <a href="{{url('aeps/kyc/action')}}?id={{$agent->id ?? ''}}&type=re" class="btn btn-primary" style="margin:auto">Resubmit Kyc</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-sm-8 col-md-offset-2">
            <form action="{{route('ifaepstransaction') ?? ''}}" method="post" id="fingkycForm" enctype="multipart/form-data" novalidate="">
                <input type="hidden" name="transactionType" value="useronboard">
                {{ csrf_field() }}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Personal Details</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Full Name</label>
                                <input type="text" class="form-control" value="{{Auth::user()->name ?? ''}}" autocomplete="off" name="merchantName" placeholder="Enter Your Name" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Mobile </label>
                                <input type="text" class="form-control" autocomplete="off" value="{{Auth::user()->mobile ?? ''}}" name="merchantPhoneNumber" placeholder="Enter Your Mobile Number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Bank Name</label>
                                <input type="text" class="form-control" value="{{Auth::user()->bank ?? ''}}" name="bank" autocomplete="off" placeholder="Enter Your bank name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Branch Name</label>
                                <input type="text" class="form-control" value="{{Auth::user()->branch ?? ''}}" name="branch" autocomplete="off" placeholder="Enter Your branch name" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>IFSC</label>
                                <input type="text" class="form-control" value="{{Auth::user()->ifsc ?? ''}}" name="ifsc" autocomplete="off" placeholder="Enter Your ifsc code" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Account Number</label>
                                <input type="number" class="form-control" value="{{Auth::user()->account ?? ''}}" name="account" autocomplete="off" placeholder="Enter Your account Number" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Account Name</label>
                                <input type="text" class="form-control" value="{{Auth::user()->account_name ?? ''}}" name="account_name" autocomplete="off" placeholder="Enter Your account Name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Pancard Number</label>
                                <input type="text" class="form-control" value="{{Auth::user()->pancard ?? ''}}" name="userPan" autocomplete="off" placeholder="Enter Your Pancard Address" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Aadhar Number</label>
                                <input type="text" class="form-control" value="{{Auth::user()->aadharcard ?? ''}}" name="merchantAadhar" autocomplete="off" placeholder="Enter Your Aadhar Number" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Address</label>
                                    <textarea class="form-control" name="merchantAddress" placeholder="Enter Your Address" required>{{Auth::user()->address ?? ''}}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Address 2</label>
                                    <input type="text" class="form-control" name="merchantAddress2" value="{{Auth::user()->merchantAddress2 ?? ''}}" autocomplete="off" placeholder="Enter Your Address 2" required="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>City</label>
                                    <input type="text" class="form-control" name="merchantCityName" value="{{Auth::user()->city ?? ''}}" autocomplete="off" placeholder="Enter Your City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>District</label>
                                    <input type="text" class="form-control" name="merchantDistrictName" value="{{Auth::user()->merchantDistrictName ?? ''}}" autocomplete="off" placeholder="Enter Your City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>state </label>
                                    <select name="merchantState" class="form-control select" required="">
                                        <option value="">Select State</option>
                                        @php
                                        $finostate = $state;
                                        @endphp

                                        @foreach ($state as $state)
                                        @if (isset($user->state))
                                        @if (strtoupper($user->state) == $state->state)
                                        <option value="{{$state->stateId}}" selected="">{{$state->state}}</option>
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Pincode </label>
                                    <input type="text" class="form-control" name="merchantPinCode" value="{{Auth::user()->pincode ?? ''}}" autocomplete="off" placeholder="Enter Your Pincode" required="">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                        <button type="button" id="copyAddressButton" class="btn btn-primary">Copy Personal Address to Shop Address</button>


                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Shop Address</label>
                                    <input type="text" class="form-control" name="shopAddress" value="{{Auth::user()->shopAddress ?? ''}}" autocomplete="off" placeholder="Enter Shop Address" required="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Shop City</label>
                                    <input type="text" class="form-control" name="shopCity" value="{{Auth::user()->shopCity ?? ''}}" autocomplete="off" placeholder="Enter Shop City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Shop District</label>
                                    <input type="text" class="form-control" name="shopDistrict" value="{{Auth::user()->shopDistrict ?? ''}}" autocomplete="off" placeholder="Enter Shop City" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Shop state </label>
                                    <select name="shopState" class="form-control" required="">
                                        <option value="">Select Shop State</option>
                                        @foreach ($finostate as $state)
                                        @if (isset(Auth::user()->shopState))
                                        @if (strtoupper(Auth::user()->shopState) == $state->state)
                                        <option value="{{$state->stateId}}" selected="">{{$state->state}}</option>
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @else
                                        <option value="{{$state->stateId}}">{{$state->state}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Shop Pincode </label>
                                    <input type="text" class="form-control" name="shopPincode" value="{{Auth::user()->shopPincode ?? ''}}" autocomplete="off" placeholder="Enter Shop Pincode" required="">
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Pancard Pic</label>
                                <input type="file" name="pancardPics" class="form-control" value="" placeholder="Enter Value" required="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Aadharcard Pic</label>
                                <input type="file" name="aadharPics" class="form-control" value="" placeholder="Enter Value" required="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Masked Aadharcard Pic</label>
                                <input type="file" name="maskedAadharImages" class="form-control" value="" placeholder="Enter Value" required="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>BackgroundImage Of Shop</label>
                                <input type="file" name="backgroundImageOfShops" class="form-control" value="" placeholder="Enter Value" required="">
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Shop / Company Type</label>
                               <select name="mccCode" id="mccCode" class="form-control">
    <option value="">Select MCC Code</option>
    <option value="4215" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4215' ? 'selected' : '' }}>
        Courier services — air and ground and freight forwarders
    </option>
    <option value="4722" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4722' ? 'selected' : '' }}>
        Travel agencies and tour operators
    </option>
    <option value="4789" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4789' ? 'selected' : '' }}>
        Transportation services
    </option>
    <option value="4812" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4812' ? 'selected' : '' }}>
        Telecommunication equipment and telephone sales
    </option>
    <option value="4814" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4814' ? 'selected' : '' }}>
        Telecommunication services, including local and long distance calls
    </option>
    <option value="4816" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4816' ? 'selected' : '' }}>
        Computer network/information services
    </option>
    <option value="4900" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '4900' ? 'selected' : '' }}>
        Utilities — electric, gas, water and sanitary
    </option>
    <option value="5099" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5099' ? 'selected' : '' }}>
        Durable goods
    </option>
    <option value="5111" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5111' ? 'selected' : '' }}>
        Stationery, office supplies and printing and writing paper
    </option>
    <option value="5137" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5137' ? 'selected' : '' }}>
        Men’s, women’s and children’s uniforms and commercial clothing
    </option>
    <option value="5192" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5192' ? 'selected' : '' }}>
        Books, periodicals and newspapers
    </option>
    <option value="5193" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5193' ? 'selected' : '' }}>
        Florists’ supplies, nursery stock and flowers
    </option>
    <option value="5199" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5199' ? 'selected' : '' }}>
        Non-durable goods
    </option>
    <option value="5311" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5311' ? 'selected' : '' }}>
        Department Stores
    </option>
    <option value="5331" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5331' ? 'selected' : '' }}>
        Variety Stores
    </option>
    <option value="5411" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5411' ? 'selected' : '' }}>
        Groceries and supermarkets
    </option>
    <option value="5451" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5451' ? 'selected' : '' }}>
        Dairies
    </option>
    <option value="5462" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5462' ? 'selected' : '' }}>
        Bakeries
    </option>
    <option value="5533" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5533' ? 'selected' : '' }}>
        Automotive parts and accessories outlets
    </option>
    <option value="5651" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5651' ? 'selected' : '' }}>
        Family clothing shops
    </option>
    <option value="5655" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5655' ? 'selected' : '' }}>
        Sports and riding apparels
    </option>
    <option value="5661" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5661' ? 'selected' : '' }}>
        Shoe shops
    </option>
    <option value="5722" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5722' ? 'selected' : '' }}>
        Household appliance shops
    </option>
    <option value="5732" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5732' ? 'selected' : '' }}>
        Electronics Shops
    </option>
    <option value="5734" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5734' ? 'selected' : '' }}>
        Computer software outlets
    </option>
    <option value="5814" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5814' ? 'selected' : '' }}>
        Fast food restaurants
    </option>
    <option value="5942" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5942' ? 'selected' : '' }}>
        Bookshops
    </option>
    <option value="5943" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5943' ? 'selected' : '' }}>
        Stationery, office and school supply shops
    </option>
    <option value="5947" {{ isset(Auth::user()->mccCode) && strtoupper(Auth::user()->mccCode) == '5947' ? 'selected' : '' }}>
        Gift, card, novelty and souvenir shops
    </option>
</select>


                            </div>

                        </div>

                        <div class="panel-footer text-center">
                            <button type="submit" class="btn btn-inverse btn-lg waves-effect waves-light" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Processing...">Proceed</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-12" {{(!$agent) ? 'style=display:none' : ''}} id="re">
            <div class="panel panel-primary text-center">
                <div class="panel-body">
                    <h3 class="text-danger text-center">Your Kyc Approval is {{$agent->status ?? 'Pending'}} , Remark -{{$agent->remark ?? ''}} </h3>
                    @if (isset($aepsdata->status) && $aepsdata->status == "rejected")
                    <a href="{{url('aeps/kyc/action')}}?id={{$agent->id ?? ''}}&type=re" class="btn btn-primary" style="margin:auto">Resubmit Kyc</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div id="receipt" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Receipt</h4>
            </div>
            <div class="modal-body p-0">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="clearfix">
                            <div class="pull-left">

                                @if ($company->logo)
                                <div class="clearfix">
                                    <div class="pull-left">
                                        <img src="{{asset('')}}public/logos/{{$company->logo}}" class=" img-responsive" alt="" style="width: 220px;height: 133px;">
                                    </div>
                                    <div class="pull-right">
                                        <img src="{{asset('')}}public/axis.png" class="img-responsive" style="height: 120px; width: 300px;">
                                    </div>
                                </div>
                                @else
                                <h4>{{$company->name}}</h4>
                                @endif

                            </div>
                            <div class="pull-right">
                                <h4><span class="receptTitle"></span> Invoice</h4>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left m-t-10">
                                    <address class="m-b-10">
                                        <strong>{{Auth::user()->name}}</strong><br>
                                        {{Auth::user()->company->name}}<br>
                                        Phone : {{Auth::user()->mobile}}
                                    </address>
                                </div>
                                <div class="pull-right m-t-10">
                                    <address class="m-b-10">
                                        <strong>Date: </strong> <span class="created_at"></span><br>
                                        <strong>Order ID: </strong> <span class="id"></span><br>
                                        <strong>Status: </strong> <span class="status"></span><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <h4 class="title"></h4>
                                    <table class="table m-t-10 table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bank</th>
                                                <th>Aadhar Number</th>
                                                <th>Ref No.</th>
                                                <th class="cash">Amount</th>
                                                <th>Account Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="bank"></td>
                                                <td class="aadhar"></td>
                                                <td class="rrn"></td>
                                                <td class="amount cash"></td>
                                                <td class="balance"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="border-radius: 0px;">
                            <div class="col-md-6 col-md-offset-6">
                                <h4 class="text-right cash">Withdrawal Amount : <span class="amount"></span></h4>
                            </div>
                        </div>
                        <hr>
                        <div class="hidden-print">
                            <div class="pull-right">
                                <a href="javascript:void(0)" id="print" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal" id="closeButton">Close</button>

<script>
    $(document).ready(function() {
        $('#closeButton').click(function() {
            // Reload the page when the "Close" button is clicked
            window.location.href = "{{ route('ifaeps', ['type' => 'aeps']) }}";
        });
    });
</script>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ministatement" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Mini Statement</h4>
            </div>
            <div class="modal-body p-0">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="clearfix">
                            <div class="pull-left">

                                @if ($company->logo)

                                <div class="clearfix">
                                    <div class="pull-left">
                                        <img src="{{asset('')}}public/logos/{{$company->logo}}" class=" img-responsive" alt="" style="width: 220px;height: 133px;">
                                    </div>
                                    <div class="pull-right">
                                        <img src="{{asset('')}}public/axis.png" class="img-responsive" style="height: 120px; width: 300px;">
                                    </div>
                                </div>

                                @else
                                <h4>{{$company->name}}</h4>

                                @endif

                            </div>
                            <div class="pull-right">
                                <h4><span class="receptTitle"></span> Invoice</h4>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left m-t-10">
                                    <address class="m-b-10">
                                        <strong>{{Auth::user()->name}}</strong><br>
                                        {{Auth::user()->company->name}}<br>
                                        Phone : {{Auth::user()->mobile}}
                                    </address>
                                </div>
                                <div class="pull-right m-t-10">
                                    <address class="m-b-10">
                                        <strong>Bank : </strong> <span class="bank"></span><br>
                                        <strong>Acc. Bal. : </strong> <span class="balance"></span><br>
                                        <strong>Bank Rrn: </strong> <span class="rrn"></span><br>
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <h4 class="title"></h4>
                                    <table class="table m-t-10 table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Narrartion</th>
                                                <th>Credit (Rs)</th>
                                                <th>Debit (Rs)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="statementData">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="hidden-print">
                            <div class="pull-right">
                                <a href="javascript:void(0)" id="statementprint" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
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
<script>
    $(document).on('keypress', '#typeyourid', function(e) {
        if ($(e.target).prop('value').length >= 10) {
            if (e.keyCode != 32) {
                return false
            }
        }
    })

    $(document).on('keypress', '#aadharcardnum', function(e) {
        if ($(e.target).prop('value').length >= 12) {
            if (e.keyCode != 32) {
                return false
            }
        }
    })
</script>
<script type="text/javascript" src="{{asset('')}}/assets/js/jquery.validate.min.js"></script>
<script src="{{ asset('/assets/js/jQuery.print.js') }}"></script>

<script type="text/javascript">
    var ROOT = "http://digiseva.me/",
        SYSTEM;

    $(document).ready(function() {
        $.fn.extend({
            myalert: function(value, type, time = 5000) {
                var tag = $(this);
                tag.find('.myalert').remove();
                tag.append('<p id="" class="myalert text-' + type + '">' + value + '</p>');
                tag.find('input').focus();
                tag.find('select').focus();
                setTimeout(function() {
                    tag.find('.myalert').remove();
                }, time);
                tag.find('input').change(function() {
                    if (tag.find('input').val() != '') {
                        tag.find('.myalert').remove();
                    }
                });
                tag.find('select').change(function() {
                    if (tag.find('select').val() != '') {
                        tag.find('.myalert').remove();
                    }
                });
            },

            mynotify: function(value, type, time = 5000) {
                var tag = $(this);
                tag.find('.mynotify').remove();
                tag.prepend(`<div class="mynotify alert alert-` + type + ` alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            ` + value + `
                        </div>`);
                setTimeout(function() {
                    tag.find('.mynotify').remove();
                }, time);
            }
        });

        SYSTEM = {

            COMPLAINT: function(id, type) {
                $('#complaintEditForm').find("[name='product']").val(type);
                $('#complaintEditForm').find("[name='transaction_id']").val(id);
                $('#complaintModal').modal();
            },


            DEFAULT: function() {
                SYSTEM.GETBALANCE();
                SYSTEM.EVENTSOURCE();
                SYSTEM.PLUGININIT();
                SYSTEM.DATAFILTER();
                SYSTEM.COMPLAINTBEFORESUBMIT();

                $(".modal").on('hidden.bs.modal', function() {
                    if ($(this).find('[type="hidden"]').length) {
                        $(this).find('[name="id"]').val(null);
                    }
                    if ($(this).find('form').length) {
                        $(this).find('form')[0].reset();
                    }

                    if ($(this).find('.select').length) {
                        $(this).find('.select').val(null).trigger('change');
                    }

                    $(this).find('form').find('p.error').remove();
                });

                $('#moreDataFilter').click(function() {
                    $('#moreFilter').slideToggle('fast', 'swing');
                    if ($('#moreDataFilter').find('.fa-arrow-down')) {
                        $('#moreDataFilter').find('i').removeClass('fa-arrow-down').addClass('fa-arrow-up');
                    } else if ($('#moreDataFilter').find('.fa-arrow-up')) {
                        $('#moreDataFilter').find('i').removeClass('fa-arrow-up').addClass('fa-arrow-down');
                    }
                });

                $('#formReset').click(function() {
                    $('form#dataFilter')[0].reset();
                    $('input[name="from_date"]').datepicker().datepicker("setDate", new Date());
                    $('form#dataFilter').find('select').select2().val(null).trigger('change');
                    $('#datatable').dataTable().api().ajax.reload();
                });
            },

            EVENTSOURCE: function() {
                if (typeof(EventSource) !== "undefined") {
                    var source = new EventSource(ROOT + "/home/mydata");
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
                        if (data.session == 0) {
                            window.location.href = "https://connect.spindiapay.com/auth/logout";
                        }
                    };
                }
            },

            NOTIFY: function(type, title, message) {
                swal({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1110000,
                    type: type,
                    title: title,
                    text: message
                });
            },

            VALIDATE: function(type, value) {
                switch (type) {
                    case 'empty':
                        if (value.val() == '') {
                            value.closest('.form-group').myalert('Enter Value', 'danger');
                            return false;
                        } else {
                            return true;
                        }
                        break;

                    case 'numeric':
                        if (value.val().match(/[^0-9]/g)) {
                            value.closest('.form-group').myalert('Value should be numeric', 'danger');
                            return false;
                        } else {
                            return true;
                        }
                        break;
                }
            },

            PLUGININIT: function() {
                $('.select').select2();
                $('.date').datepicker({
                    'autoclose': true,
                    'clearBtn': true,
                    'todayHighlight': true,
                    'format': 'dd-M-yyyy'
                });
            },

            GETBALANCE: function() {
                $.ajax({
                    url: ROOT + "/home/balance",
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(result) {
                        $('span.mbalance').text(result.balance);
                        $('span.aepsbalance').text(result.aepsbalance);
                    }
                });
            },
            COMPLAINTBEFORESUBMIT: function() {
                $('#complaintEditForm').submit(function() {
                    var user_id = $("[name='user_id']").val();
                    var subject = $("[name='subject']").val();
                    var description = $("[name='description']").val();
                    var product = $("[name='product']").val();
                    var transaction_id = $("[name='transaction_id']").val();

                    if (subject == "") {
                        $("[name='subject']").closest('.form-group').myalert('Please enter subject', 'danger');
                    } else if (description == "") {
                        $("[name='description']").closest('.form-group').myalert('Please enter description', 'danger');
                    } else {
                        SYSTEM.FORMSUBMIT($('#complaintEditForm'), function(data) {
                            if (!data.statusText) {
                                if (data.status == "success") {
                                    $('#complaintModal').modal('hide');
                                    $('#complaintEditForm')[0].reset();
                                    DT.draw();
                                    SYSTEM.GETBALANCE();
                                    SYSTEM.NOTIFY('success', 'Success', 'Complaint registered successfully!')
                                } else {
                                    SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                                }
                            } else {
                                SYSTEM.SHOWERROR(data, $('#complaintEditForm'));
                            }
                        });
                    }

                    return false;
                });
            },

            FORMSUBMIT: function(form, callback) {
                form.ajaxSubmit({
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSubmit: function() {
                        form.find('button[type="submit"]').button('loading');
                    },
                    complete: function() {
                        form.find('button[type="submit"]').button('reset');
                    },
                    success: function(data) {
                        callback(data);
                    },
                    error: function(errors) {
                        callback(errors);
                    }
                });
            },

            AJAX: function(url, data, callback) {
                $.ajax({
                    url: url,
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    data: data,
                    beforeSend: function() {
                        swal({
                            title: 'Wait!',
                            text: 'Please wait, we are working on your request',
                            onOpen: () => {
                                swal.showLoading()
                            }
                        });
                    },
                    success: function(data) {
                        swal.close();
                        callback(data);
                    },
                    error: function(errors) {
                        swal.close();
                        callback(errors);
                    }
                });
            },

            SHOWERROR: function(errors, form, type = "inline") {
                if (type == "inline") {
                    if (errors.statusText) {
                        if (errors.status == 422) {
                            form.find('p.error').remove();
                            $.each(errors.responseJSON, function(index, value) {
                                form.find('[name="' + index + '"]').closest('div.form-group').myalert(value, 'danger');
                            });
                        } else if (errors.status == 400) {
                            form.mynotify(errors.responseJSON.message, 'danger');
                        } else {
                            form.mynotify(errors.statusText, 'danger');
                        }
                    } else {
                        form.mynotify(errors.message, 'danger');
                    }
                } else {
                    if (errors.statusText) {
                        if (errors.status == 400) {
                            SYSTEM.NOTIFY('error', 'Oops', errors.responseJSON.message);
                        } else {
                            SYSTEM.NOTIFY('error', 'Oops', errors.statusText);
                        }
                    } else {
                        SYSTEM.NOTIFY('error', 'Oops', errors.message);
                    }
                }
            },

            DATAFILTER: function() {
                $('#dataFilter').submit(function() {
                    DT.draw();
                    return false;
                });

                $('#formReset').click(function() {
                    $('#dataFilter')[0].reset();
                    DT.draw();
                });
            }
        }
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        SYSTEM.DEFAULT();
    });
</script>


<script type="text/javascript">
    var USERSYSTEM, STOCK = {},
        DT = !1,
        AEPSURL = "{{route('ifaepstransaction')}}";

        $(document).ready(function() {

            function handleTransactionTypeChange() {
        if ($('[name="transactionType"]:checked').val() != "M") {
            $(".CASH").show();
            $(".CASH").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
            $(".PAY").hide();
            $(".PAY").find("select").removeAttr('name').removeAttr('required');
        } else {
            $(".PAY").show();
            $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
            $(".CASH").hide();
            $(".CASH").find("select").removeAttr('name').removeAttr('required');
        }
    }
            // Add click event handler to amount buttons
            $('#transactionData').on('click', '.amount-btn', function() {
                var amount = $(this).data('amount');
                $('#transactionAmount').val(amount);
            });

            $('#transactionBankData').on('click', '.bank-btn', function() {
                var bank = $(this).data('bank');
                $('[name="nationalBankIdentificationNumber"]').val(bank).change(); // Set the value and trigger change event
   
            });

        });

    $(document).ready(function() {


        if ($('[name="transactionType"]:checked').val() == "CW" || $('[name="transactionType"]:checked').val() == "M") {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                        var out = `<div class="form-group col-md-6">
                                            <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Amount :</label>
                                            <input type="number" class="form-control"  id="transactionAmount" name="transactionAmount" autocomplete="off" placeholder="Enter Amount">
                                            <div class="amount-blocks">
                                                <button class="amount-btn" data-amount="500">500</button>
                                                <button class="amount-btn" data-amount="1000">1000</button>
                                                <button class="amount-btn" data-amount="2000">2000</button>
                                                <button class="amount-btn" data-amount="3000">3000</button>
                                                <button class="amount-btn" data-amount="4000">4000</button>
                                                <button class="amount-btn" data-amount="5000">5000</button>
                                                <button class="amount-btn" data-amount="6000">6000</button>
                                                <button class="amount-btn" data-amount="7000">7000</button>
                                                <button class="amount-btn" data-amount="8000">8000</button>
                                                <button class="amount-btn" data-amount="9000">9000</button>
                                                <button class="amount-btn" data-amount="10000">10000</button>
                                            </div>
                                        </div>`;

                        $('#transactionData').append(out);
                    } else {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                    }
        USERSYSTEM = {
            DEFAULT: function() {
                USERSYSTEM.TRANSACTION();
                USERSYSTEM.ONBOARD();

                $(".PAY").show();
                $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                $(".CASH").hide();
                $(".CASH").find("select").removeAttr('name').removeAttr('required');

                $('[name="transactionType"]').on('change', function() {
                    if ($('[name="transactionType"]:checked').val() == "CW" || $('[name="transactionType"]:checked').val() == "M") {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                        var out = `<div class="form-group col-md-6">
                                            <label style="color:#fff !important;background-color:#3991ab;padding:5px;">Amount :</label>
                                            <input type="number" class="form-control"  id="transactionAmount" name="transactionAmount" autocomplete="off" placeholder="Enter Amount">
                                            <div class="amount-blocks">
                                                <button class="amount-btn" data-amount="500">500</button>
                                                <button class="amount-btn" data-amount="1000">1000</button>
                                                <button class="amount-btn" data-amount="2000">2000</button>
                                                <button class="amount-btn" data-amount="3000">3000</button>
                                                <button class="amount-btn" data-amount="4000">4000</button>
                                                <button class="amount-btn" data-amount="5000">5000</button>
                                                <button class="amount-btn" data-amount="6000">6000</button>
                                                <button class="amount-btn" data-amount="7000">7000</button>
                                                <button class="amount-btn" data-amount="8000">8000</button>
                                                <button class="amount-btn" data-amount="9000">9000</button>
                                                <button class="amount-btn" data-amount="10000">10000</button>
                                            </div>
                                        </div>`;

                        $('#transactionData').append(out);
                    } else {
                        $('[name="transactionAmount"]').closest(".form-group").remove();
                    }
                });

                

                $('[name="transactionType"]').on('change', function() {
                    if ($('[name="transactionType"]:checked').val() != "M") {
                        $(".CASH").show();
                        $(".CASH").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                        $(".PAY").hide();
                        $(".PAY").find("select").removeAttr('name').removeAttr('required');
                    } else {
                        $(".PAY").show();
                        $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                        $(".CASH").hide();
                        $(".CASH").find("select").removeAttr('name').removeAttr('required');
                    }
                });

                $('#print').click(function() {
                    $('#receipt').find('.modal-body').print();
                   // window.location.href = "{{ route('ifaeps', ['type' => 'aeps']) }}";
                });

                $('#statementprint').click(function() {
                    $('#ministatement').find('.modal-body').print();
                });

                $('#scan').click(function() {
                    var device = $('#transactionForm').find('[name="device"]:checked').val();
                    
                     if(device == 'MORPHO_PROTOBUF_L1'){
                        Capture();
                    }
                    else if(device == 'MORPHO_PROTOBUF_L1WS'){
                        CaptureWS();
                    }else{
                        USERSYSTEM.RDSERVICE(device, "11100");
                    }
                    
                });
            },

            ONBOARD: function() {
                $("#fingkycForm").validate({
                    rules: {
                        merchantName: {
                            required: true
                        },
                        merchantAddress: {
                            required: true
                        },
                        merchantState: {
                            required: true
                        },
                        merchantPhoneNumber: {
                            required: true,
                            number: true,
                            minlength: 10,
                            maxlength: 10
                        },
                        userPan: {
                            required: true
                        },
                        merchantPinCode: {
                            required: true,
                            number: true,
                            minlength: 6,
                            maxlength: 6
                        }
                    },
                    messages: {
                        merchantName: {
                            required: "Please enter value",
                        },
                        merchantAddress: {
                            required: "Please enter value",
                        },
                        merchantState: {
                            required: "Please enter value",
                        },
                        merchantPhoneNumber: {
                            required: "Please enter value",
                            nnumber: "Aadhar number should be numeric",
                            minlength: "Your aadhar number must be 10 digit",
                            maxlength: "Your aadhar number must be 10 digit"
                        },
                        userPan: {
                            required: "Please enter value",
                        },
                        merchantPinCode: {
                            required: "Please enter value",
                            nnumber: "Aadhar number should be numeric",
                            minlength: "Your aadhar number must be 6 digit",
                            maxlength: "Your aadhar number must be 6 digit"
                        }
                    },
                    errorElement: "p",
                    errorPlacement: function(error, element) {
                        if (element.prop("name") === "bank") {
                            error.insertAfter(element.closest(".form-group").find(".select2"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        var form = $(form);
                        SYSTEM.FORMSUBMIT(form, function(data) {
                            if (!data.statusText) {
                                if (data.status == "TXN") {
                                    swal({
                                        type: "success",
                                        title: "Success",
                                        text: "User onboard successfully",

                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });

                                } else if (data.status == "pending") {
                                    swal({
                                        type: "warning",
                                        title: "Pending",
                                        text: "User onboard pending",
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                } else {
                                    if(data.message == 'Please do 2fa before initiating transaction'){
                                        swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            window.location.reload();
                                        }
                                    });
                                    }
                                    else{
                                        swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    }
                                    // swal('Failed', data.message, 'error');
                                }
                            } else {
                                SYSTEM.SHOWERROR(data, form);
                            }
                        });
                    }
                });
            },

            TRANSACTION: function() {
                $("#transactionForm").validate({
                    rules: {
                        mobileNumber: {
                            required: true,
                            minlength: 10,
                            number: true,
                            maxlength: 11
                        },
                        adhaarNumber: {
                            required: true,
                            number: true,
                            minlength: 12,
                            maxlength: 12
                        },
                        nationalBankIdentificationNumber: 'required',
                        transactionAmount: {
                            required: true,
                            number: true,
                            min: 10
                        },
                    },
                    messages: {
                        mobileNumber: {
                            required: "Please enter mobile number",
                            number: "Mobile number should be numeric",
                            minlength: "Your mobile number must be 10 digit",
                            maxlength: "Your mobile number must be 10 digit"
                        },
                        adhaarNumber: {
                            required: "Please enter aadhar number",
                            number: "Aadhar number should be numeric",
                            minlength: "Your aadhar number must be 12 digit",
                            maxlength: "Your aadhar number must be 12 digit"
                        },
                        transactionAmount: {
                            required: "Please enter amount",
                            number: "Transaction amount should be numeric",
                            min: "Minimum transaction amount should be 10"
                        },
                        nationalBankIdentificationNumber: "Please select bank"
                    },
                    errorElement: "p",
                    errorPlacement: function(error, element) {
                        if (element.prop("name") === "nationalBankIdentificationNumber") {
                            error.insertAfter(element.closest(".form-group").find("span.select2"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(element) {
                        var form = $(element);
                        var scan = form.find('[name="biodata"]').val();
                        if (scan != '') {
                            SYSTEM.FORMSUBMIT($('#transactionForm'), function(data) {

                                if (!data.statusText) {
                                    form.find('[name="biodata"]').val(null);
                                    if (data.status == "success" || data.status == "pending") {
                                        $('#MS').prop('checked', true).trigger('click');
                                        if ($('[name="transactionType"]:checked').val() != "M") {
                                            $(".CASH").show();
                                            $(".CASH").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                                            $(".PAY").hide();
                                            $(".PAY").find("select").removeAttr('name').removeAttr('required');
                                        } else {
                                            $(".PAY").show();
                                            $(".PAY").find("select").attr('name', 'nationalBankIdentificationNumber').attr('required', '');
                                            $(".CASH").hide();
                                            $(".CASH").find("select").removeAttr('name').removeAttr('required');
                                        }

                                        if (data.transactionType == "AUO") {
                                            form[0].reset();
                                            swal({
                                            type: "success",
                                            title: "Success",
                                            text: data.message,
                                            showCancelButton: true,
                                            onClose: () => {
                                                window.location.reload();
                                            }
                                        });
                                        }

                                        if (data.transactionType != "MS") {
                                            form[0].reset();
                                            swal({
                                                title: data.title,
                                                text: data.message + ", Remaining Balance - " + data.balance,
                                                type: 'success',
                                                showCancelButton: true,
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#456b8c',
                                                confirmButtonText: 'Print Invoice',
                                                cancelButtonText: 'Close',
                                                allowOutsideClick: false,
                                                allowEscapeKey: false,
                                                allowEnterKey: false,
                                                timer: 1110000
                                            }).then((result) => {
                                                if (result.value) {
                                                    $('#MS').prop('checked', true);
                                                    if (data.transactionType == "CW" || data.transactionType == "M") {
                                                        $(".cash").show();
                                                    } else {
                                                        $('.cash').hide();
                                                    }

                                                    // $('#receipt').find('.created_at').text(data.created_at);
                                                    $('#receipt').find('.amount').text(data.amount);
                                                    $('#receipt').find('.rrn').text(data.rrn);
                                                    $('#receipt').find('.aadhar').text(data.aadhar);
                                                    $('#receipt').find('.id').text(data.id);
                                                    $('#receipt').find('.status').text(data.status);
                                                    $('#receipt').find('.bank').text(data.bank);
                                                    $('#receipt').find('.balance').text(data.balance);
                                                    $('#receipt').find('.title').text(data.title);
                                                    $('#receipt').modal();
                                                }
                                            });
                                        } else {
                                            $('#ministatement').find('.rrn').text(data.rrn);
                                            $('#ministatement').find('.bank').text(data.bank);
                                            $('#ministatement').find('.balance').text(data.balance);
                                            $('#ministatement').find('.title').text(data.title);
                                            var trdata = '';
                                            $.each(data.data, function(index, val) {
                                                if (val.txnType == "Cr") {
                                                    trdata += `<tr>
                                                                <td>` + val.date + `</td>
                                                                <td>` + val.narration + `</td>
                                                                <td>` + val.amount + `</td>
                                                                <td></td>
                                                            </tr>`;
                                                } else {
                                                    trdata += `<tr>
                                                                <td>` + val.date + `</td>
                                                                <td>` + val.narration + `</td>
                                                                <td></td>
                                                                <td>` + val.amount + `</td>
                                                            </tr>`;
                                                }
                                            });
                                            $('#ministatement').find('.statementData').html(trdata);
                                            $('#ministatement').modal();
                                        }
                                    } else {

                                        if(data.transactionType == "CW"){
                                                    swal({
                                                        type: "error",
                                                        title: "Failed",
                                                        text: data.message,
                                                        timer: 1110000,
                                                        onClose: () => {
                                                         //   window.location.href = "{{ route('ifaeps', ['type' => 'cw2fa/CW']) }}";
                                                        }
                                                    });
                                                }
                                                if(data.transactionType == "M"){
                                                    swal({
                                                        type: "error",
                                                        title: "Failed",
                                                        text: data.message,
                                                        timer: 1110000,
                                                        onClose: () => {
                                                            
                                                        //    window.location.href = "{{ route('ifaeps', ['type' => 'ap2fa/AP']) }}";
                                                        }
                                                    });
                                                }
                                        if(data.message == 'Please do 2fa before initiating transaction'){
                                        swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            window.location.reload();
                                        }
                                    });
                                    }

                                    let swalConfig = {
    type: "error",
    title: "Failed",
    text: data.message,
    timer: 1110000,
    onClose: () => {
        //window.location.reload();
    }
};

if (data.transactionType == "CW") {
    swalConfig.onClose = () => {
   //     window.location.href = "{{ route('ifaeps', ['type' => 'cw2fa/CW']) }}";
    };
} else if (data.transactionType == "M") {
    swalConfig.onClose = () => {
    //    window.location.href = "{{ route('ifaeps', ['type' => 'ap2fa/AP']) }}";
    };
}
console.log('check new function');
console.log(data.transactionType);
swal(swalConfig);

                                   
                                        return false;
                                        //  swal('Failed', data.message, 'error');
                                    }
                                } else {
                                    //  SYSTEM.SHOWERROR(data, $('#transactionForm'));
                                    form.find('[name="biodata"]').val(null);
                                    // swal('Failed', data.message, 'error');
                                    if(data.message == 'Please do 2fa before initiating transaction'){
                                        swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            window.location.reload();
                                        }
                                    });
                                    }
                                    else{
                                        swal({
                                        type: "error",
                                        title: "Failed",
                                        text: data.message,
                                        timer: 1110000,
                                        onClose: () => {
                                            //window.location.reload();
                                        }
                                    });
                                    }

                                    console.log(data);
                                }
                            });
                        } else {
                            form.find('[name="biodata"]').val(null);
                            form.mynotify("Please scan your finger", 'danger');
                        }
                    }
                });
            },

            RDSERVICE: function(device, port) {
                var primaryUrl = "http://127.0.0.1:" + port;

                if(device == "MORPHO_PROTOBUF_SSL")
                    {
                        primaryUrl = "https://localhost:" + port;
                    }

                $.ajax({
                    type: "RDSERVICE",
                    async: true,
                    crossDomain: true,
                    url: primaryUrl,
                    processData: false,
                    beforeSend: function() {
                        swal({
                            title: 'Scanning',
                            text: 'Please wait, device getting initiated',
                            onOpen: () => {
                                swal.showLoading()
                            },
                            allowOutsideClick: () => !swal.isLoading()
                        });
                    },
                    success: function(data) {
                        swal.close();
                        var $doc = $.parseXML(data);
                        var CmbData1 = $($doc).find('RDService').attr('status');
                        var CmbData2 = $($doc).find('RDService').attr('info');

                        if (!CmbData1) {
                            var CmbData1 = $(data).find('RDService').attr('status');
                            var CmbData2 = $(data).find('RDService').attr('info');
                        }

                        if (CmbData1 == "READY") {
                            USERSYSTEM.CAPTURE(device, port);
                        } else if (CmbData1 == "NOTREADY" && CmbData2 == "Mantra Authentication Vendor Device Manager") {
                            USERSYSTEM.RDSERVICE(device, "11101");
                        } else {
                            notify("Device : " + CmbData1, 'danger');
                            SYSTEM.NOTIFY('error', 'Oops', "Device : " + CmbData1);
                        }
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        swal.close();
                        if (port == "11100") {
                            USERSYSTEM.RDSERVICE(device, "11101");
                        } else {
                            SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                        }
                    },
                });
            },

            CAPTURE: function(device, port) {
                var primaryUrl = "http://127.0.0.1:" + port;
                if (device == "MANTRA_PROTOBUF") {
                    var url = primaryUrl + "/rd/capture";
                } else {
                    var url = primaryUrl + "/capture";

                }

                if(device == "MORPHO_PROTOBUF_SSL")
                    {
                        primaryUrl = "https://localhost:" + port;
                        url = primaryUrl + "/capture";
                    }

                if (device == "MANTRA_PROTOBUF") {

                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    } else {
                        var XML = '<?php echo '<?xml version="1.0"?>'; ?> <PidOptions ver="1.0"> <Opts fCount="1" fType="2" iCount="0" pCount="0" format="0" pidVer="2.0" timeout="20000" posh="UNKNOWN" env="P" wadh=""/> <CustOpts><Param name="mantrakey" value="" /></CustOpts> </PidOptions>';
                    }
                } else {
                    if ($('[name="transactionType"]:checked').val() == "M") {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
                    } else {
                        var XML = '<PidOptions ver=\"1.0\">' + '<Opts fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>' + '</PidOptions>';
                    }
                }

                $.ajax({
                    type: "CAPTURE",
                    async: true,
                    crossDomain: true,
                    url: url,
                    data: XML,
                    contentType: "text/xml; charset=utf-8",
                    processData: false,
                    beforeSend: function() {
                        swal({
                            text: 'Please put any of your finger on device',
                            imageUrl: '{{asset("")}}assets/images/capute.gif',
                            showConfirmButton: false,
                            allowOutsideClick: () => false
                        });
                    },
                    success: function(data) {
                        swal.close();
                        if (device == "MANTRA_PROTOBUF") {
                            var $doc = $.parseXML(data);
                            var errorInfo = $($doc).find('Resp').attr('errInfo');


                            if (errorInfo == 'Success.' || errorInfo == 'Success') {
                                SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
                                $('[name="biodata"]').val(data);
                                console.log('click on btn 2');
                                $('#proceed').trigger('click');
                            } else {
                                SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                            }
                        } else {
                            var errorInfo = $(data).find('Resp').attr('errInfo');
                            var errorCode = $(data).find('Resp').attr('errCode');
                            var mydata = $(data).find('PidData').html();
                            if (errorCode == '0') {
                                SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
                                $('[name="biodata"]').val("<PidData>" + mydata + "</PidData>");
                                console.log('click on btn 2');
                                $('#proceed').trigger('click');
                            } else {
                                SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                            }
                        }
                    },
                    error: function(jqXHR, ajaxOptions, thrownError) {
                        swal.close();
                        SYSTEM.NOTIFY('error', 'Oops', 'Device not working correctly, please try again');
                    },
                });
            }
        }

        USERSYSTEM.DEFAULT();
    });
</script>
<script>
var count=0;





function Capture()
{

  var url = "https://localhost:11100/capture";

   var PIDOPTS='<PidOptions ver=\"1.0\">'+'<Opts env="P" fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
   
 
   /*
   format=\"0\"     --> XML
   format=\"1\"     --> Protobuf
   */
 var xhr;
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");

			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
			{
				//IE browser
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				//other browser
				xhr = new XMLHttpRequest();
			}
        
        xhr.open('CAPTURE', url, true);
		xhr.setRequestHeader("Content-Type","text/xml");
		xhr.setRequestHeader("Accept","text/xml");

        xhr.onreadystatechange = function () {
		//if(xhr.readyState == 1 && count == 0){
		//	fakeCall();
		//}
if (xhr.readyState == 4){
            var status = xhr.status;
            //parser = new DOMParser();
            if (status == 200) {
            var test1=xhr.responseText;
            var test2=test1.search("errCode");
			var test6=getPosition(test1, '"', 4);
			var test4=test2+9;
			var test5=test1.slice(test4, test6);
			if (test5>0)
			{
			//alert("XXX Capture Unsuccessful XXX");
            alert(xhr.responseText);
			//document.getElementById('text').value = xhr.responseText;
			}
			else
			{
			SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
			 $('[name="biodata"]').val(xhr.response);
                                console.log('click on btn 2');
                                $('#proceed').trigger('click');
			//document.getElementById('text').value = "Captured Successfully";
			}


            } else 
            {
                
	            console.log(xhr.response);

            }
			}

        };

        xhr.send(PIDOPTS);
	
}



function getPosition(string, subString, index) {
  return string.split(subString, index).join(subString).length;
}

function CaptureWS()
{

  var url = "http://127.0.0.1:11101/capture";

   var PIDOPTS='<PidOptions ver=\"1.0\">'+'<Opts env="P" fCount=\"1\" fType=\"2\" iCount=\"\" iType=\"\" pCount=\"\" pType=\"\" format=\"0\" pidVer=\"2.0\" timeout=\"10000\" otp=\"\" wadh=\"\" posh=\"\"/>'+'</PidOptions>';
   
 
   /*
   format=\"0\"     --> XML
   format=\"1\"     --> Protobuf
   */
 var xhr;
			var ua = window.navigator.userAgent;
			var msie = ua.indexOf("MSIE ");

			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number
			{
				//IE browser
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} else {
				//other browser
				xhr = new XMLHttpRequest();
			}
        
        xhr.open('CAPTURE', url, true);
		xhr.setRequestHeader("Content-Type","text/xml");
		xhr.setRequestHeader("Accept","text/xml");

        xhr.onreadystatechange = function () {
		//if(xhr.readyState == 1 && count == 0){
		//	fakeCall();
		//}
if (xhr.readyState == 4){
            var status = xhr.status;
            //parser = new DOMParser();
            if (status == 200) {
            var test1=xhr.responseText;
            var test2=test1.search("errCode");
			var test6=getPositionWS(test1, '"', 4);
			var test4=test2+9;
			var test5=test1.slice(test4, test6);
			if (test5>0)
			{
			//alert("XXX Capture Unsuccessful XXX");
            alert(xhr.responseText);
			//document.getElementById('text').value = xhr.responseText;
			}
			else
			{
			SYSTEM.NOTIFY('success', 'Scanned', 'Fingerprint Captured Successfully');
			 $('[name="biodata"]').val(xhr.response);
                                console.log('click on btn without SSL ');
                                $('#proceed').trigger('click');
			//document.getElementById('text').value = "Captured Successfully";
			}


            } else 
            {
                
	            console.log(xhr.response);

            }
			}

        };

        xhr.send(PIDOPTS);
	
}

function getPositionWS(string, subString, index) {
  return string.split(subString, index).join(subString).length;
}


</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    const copyButton = document.getElementById("copyAddressButton");

    copyButton.addEventListener("click", function() {
        
        console.log('hiii');
        // Copy values for input and textarea fields
        const personalAddressFields = [
            "merchantAddress",
            "merchantCityName",
            "merchantDistrictName",
            "merchantPinCode"
        ];

        const shopAddressFields = [
            "shopAddress",
            "shopCity",
            "shopDistrict",
            "shopPincode"
        ];

        personalAddressFields.forEach(function(fieldName, index) {
            const personalField = document.querySelector(`[name="${fieldName}"]`);
            const shopField = document.querySelector(`[name="${shopAddressFields[index]}"]`);
            shopField.value = personalField.value;
        });

        // Copy selected option from state dropdowns
        const personalStateDropdown = document.querySelector('[name="merchantState"]');
        const shopStateDropdown = document.querySelector('[name="shopState"]');
        
        const selectedOptionValue = personalStateDropdown.options[personalStateDropdown.selectedIndex].value;
        console.log(selectedOptionValue);
        
        for (const option of shopStateDropdown.options) {
            if (option.value === selectedOptionValue) {
                option.selected = true;
                break;
            }
        }
    });
});
</script>









@endpush