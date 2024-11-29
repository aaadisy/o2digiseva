@extends('layouts.app')
 @if($type == 'wt') 
 @section('title', 'Wallet Transfer')
@section('pagetitle', 'Wallet Transfer')
 @else 
 @section('title', ucfirst($type).' Bill Payment')
@section('pagetitle', ucfirst($type).' Bill Payment')
@endif

@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        @if($type == 'wt') Wallet Transfer / Express Payout @else {{ucfirst($type)}} Bill Payment @endif
                    </h4>
                </div>
                <form id="billpayForm" action="{{route('billpay')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="payment">
                    <div class="panel-body">
                        @if($mydata['billnotice'] != null && $mydata['billnotice'] != '')
                            <div class="alert bg-info alert-styled-left no-margin mb-20" style="font-size:20px">
                                <span class="text-semibold">Note !</span> {{$mydata['billnotice']}}.
                            </div>
                        @endif
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Select Bank @else {{ucfirst($type)}} Operator @endif </label>
                                <input type="hidden" name="billtype" value="{{ $type }}" />
                                <select name="provider_id" class="form-control select" required="">
                                    <option value="">@if($type == 'wt') Select Bank @else Select Operator @endif</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{$provider->id}}">{{$provider->bankname}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Bank Account Number @elseif($type == 'insurance') Policy Number @elseif($type == 'electricity') Consumer ID @else  Account Number @endif
                                 </label>
                                <input type="text" name="number" class="form-control" placeholder="@if($type == 'wt') Bank Account Number @elseif($type == 'insurance') Policy Number @elseif($type == 'electricity') Consumer ID @else  Account Number @endif" required="">
                            </div>
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Bank IFS Code @else  Biller Mobile Number @endif</label>
                                <input type="text" name="mobile" class="form-control" placeholder="Enter @if($type == 'wt') Bank IFS Code @else  Biller Mobile Number @endif number" required="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Bank Account Holder Name @else  Name @endif </label>
                                <input type="text" name="biller" class="form-control" placeholder="@if($type == 'wt') Bank Account Holder Name @else  Name @endif" required="" @if($type == 'wt')  @elseif ($type == 'gamingtopup')  @else readonly="" @endif>
                            </div>
                            
                            
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Date @else Bill Date @endif</label>
                                <input  @if($type == 'wt') type="date"  @elseif ($type == 'gamingtopup') type="date" @else type="text" @endif name="duedate" class="form-control" placeholder="Enter @if($type == 'wt') Date @else Bill Date @endif" required=""  @if($type == 'wt')  @elseif ($type == 'gamingtopup')  @else readonly="" @endif>
                            </div>
                            <div class="form-group col-md-4">
                                <label>@if($type == 'wt') Amount @else Bill Amount @endif</label>
                                <input type="text"   name="amount" class="form-control" placeholder="@if($type == 'wt') Amount @else Bill Amount @endif" @if($type == 'wt')  @elseif ($type == 'gamingtopup')  @else readonly="" @endif >
                            </div>
                            <div class="form-group col-md-4">
                                <label>Wallet Pin</label>
                                <input type="text" name="walletpin" class="form-control" placeholder="Enter Pin" required="">
                            </div>
                            <div class="form-group col-md-4" id="otpreq">
                                
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer text-center">
                        @if($type == 'wt')
                        
                         @elseif ($type == 'gamingtopup')  
                         
                        @else 
                        <button type="button" onclick="getBill(this)" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Fetching"><b><i class=" icon-refresh"></i></b> Fetch Bill</button>
                        
                        @endif
                        <button type="submit" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Paying"><b><i class=" icon-paperplane"></i></b> Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
          <h4 class="panel-title">Recent  @if($type == 'wt') Wallet Transfer @else {{ucfirst($type)}} Bill Payment @endif</h4>
        </div>
        <div class="panel-body">
        </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User Details</th>
                            <th>Transaction Details</th>
                            <th>Refrence Details</th>
                            <th>Amount/Commission</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
                            <h4>{{Auth::user()->company->companyname}}</h4>
                      </div>
                      <div class="pull-right">
                         @if(Request()->segment(2) == 'wt')
                          <h4>Wallet Transfer Invoice</h4>
                          @else
                           <h4>Bill Payment Invoice</h4>
                          @endif
                      </div>
                  </div>
                  <hr class="m-t-10 m-b-10">
                  <div class="row">
                      <div class="col-md-12">
                          
                          <div class="pull-left m-t-10">
                              <address class="m-b-10">
                                <strong>{{Auth::user()->name}}</strong><br>
                                {{Auth::user()->company->companyname}}<br>
                                Phone : {{Auth::user()->mobile}}
                                </address>
                          </div>
                          <div class="pull-right m-t-10">
                              <address class="m-b-10">
                                <strong>Date: </strong> <span class="created_at"></span><br>
                                <strong>Order ID: </strong> <span class="order_id"></span><br>
                                </address>
                          </div>
                      </div>
                  </div>
                  <div class="row">
                      <div class="col-md-12">
                          <div class="table-responsive">
                            @if(Request()->segment(2) == 'wt')
                                     
                              <h4>Wallet Transfer Invoice:</h4>
                          
                          @else
                          
                            <h4>Bill Details :</h4>
                          
                          @endif
                              <table class="table m-t-10">
                                  <thead>
                                      <tr>
                                        
                                        @if(Request()->segment(2) == 'wt')
                                       
                                          <th>Bank Name</th>
                                          <th>Account Number</th>
                                      
                                      @else
                                      
                                        <th>{{ucfirst($type)}} Board</th>
                                          <th>Consumer Number</th>
                                      
                                      @endif
                                          <th>Amount</th>
                                          <th>Ref No.</th>
                                      </tr></thead>
                                  <tbody>
                                      <tr>
                                          <td class="provider"></td>
                                           @if(Request()->segment(2) == 'wt')
                                     
                            <td id="aacountnumner">
                          
                          @else
                          <td id="aacountnumner" class="number numbertest ">
                          @endif
                                          </td>
                                          <td class="amount"></td>
                                          <td class="refno"></td>
                                      </tr>
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>
                  <div class="row" style="border-radius: 0px;">
                      <div class="col-md-6 col-md-offset-6">
                          <h4 class="text-right">Amount : <span class="amount"></span></h4>
                      </div>
                  </div>
                  <hr>
                  <div class="hidden-print">
                      <div class="pull-right">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                          <a href="javascript:void(0)"  id="print" class="btn btn-inverse waves-effect waves-light"><i class="fa fa-print"></i></a>
                      </div>
                  </div>
              </div>
          </div>
          </div>
      </div>
    </div>
  </div>
  
  
  
  @php
  if(Request()->segment(2) == 'electricity')
  {
    $ajaxurl = 'billpaystatement';
  }
  else
  {
    $ajaxurl = Request()->segment(2).'billpaystatement';
  }
  @endphp
@endsection

@push('script')
    <script src="{{ asset('/assets/js/core/jQuery.print.js') }}"></script>
  <script type="text/javascript">
    $(document).ready(function () {
        
        var url = "{{url('statement/fetch')}}/{{ $ajaxurl }}/0";

        var onDraw = function() {};

        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class='text-inverse m-l-10'><b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.username+` ( `+full.user_id+` )<br>`+full.mobile+` ( `+full.username+` )`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.aepstype == 'CD')
                    {
                    return `Account Number - `+full.aadhar+`<br>Bank - `+full.bank;
                }
                else
                {
                     return `Number - `+full.number+`<br>Operator - `+full.providername;
                }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Ref No.  - `+full.refno+`<br>Txnid - `+full.txnid;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.aepstype == 'CD')
                    {
                        return `Amount - <i class="fa fa-inr"></i> `+full.amount+`<br>Surcharge - <i class="fa fa-inr"></i> `+full.charge;
                    }
                    else
                    {
                        return `Amount - <i class="fa fa-inr"></i> `+full.amount+`<br>Profit - <i class="fa fa-inr"></i> `+full.profit;
                    }
                    
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success"){
                        var out = `<span class="label label-success">Success</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else if(full.status == "reversed" || full.status == "refunded"){
                        var out = `<span class="label bg-slate">Reversed/Refunded</span>`;
                    }
                    
                    else{
                        var out = `<span class="label label-danger">Failed</span>`;
                    }

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $('#print').click(function(){
            $('#receipt').find('.modal-body').print();
        });

        $( "#billpayForm" ).validate({
            rules: {
                provider_id: {
                    required: true
                },
                number: {
                    required: true
                },
                mobile: {
                    required: true
                },
                walletpin: {
                    required: true,
                    number : true,
                    minlength: 4
                },
                amount: {
                    required: true,
                    number : true,
                    min: 10
                },
                biller: {
                    required: true
                },
                duedate: {
                    required: true,
                },
            },
            messages: {
                provider_id: {
                    required: "Please select recharge operator",
                    number: "Operator id should be numeric",
                },
                number: {
                    required: "Please enter recharge number",
                    min: "{{ucfirst($type)}} number length should be 10 digit",
                },
                mobile: {
                    required: "Please enter biller mobile number",
                    number: "Biller mobile number should be numeric",
                    min: "Biller mobile number length should be 10 digit",
                },
                walletpin: {
                    required: "Please enter Wallet Pin",
                    number: "Wallet Pin should be numeric",
                    min: "Wallet Pin length should be 4 Digits",
                },
                amount: {
                    required: "Please enter recharge amount",
                    number: "Amount should be numeric",
                    min: "Min recharge amount value rs 10",
                },
                biller: {
                    required: "Please enter biller name",
                },
                duedate: {
                    required: "Please enter biller duedate",
                }
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
                var form = $('#billpayForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.status == "success" || data.status == "pending"){
                            form[0].reset();
                            form.find('select').select2().val(null).trigger('change');
                            getbalance();
                            form.find('button[type="submit"]').button('reset');
                            notify("Billpayment Successfully Submitted", 'success');

                            $('#receipt').find('.created_at').text(data.data.created_at);
                            $('#receipt').find('.amount').text(data.data.amount);
                            $('#receipt').find('.refno').text(data.data.txnid);
                            $('#receipt').find('.order_id').text(data.data.id);
                            if(data.data.aepstype == 'CD')
                            {
                                $('#receipt').find('.provider').text(data.data.bank);

                                $('#receipt').find('#aacountnumner').text(data.data.aadhar);
                            }
                            else
                            {
                                
                            $('#receipt').find('.provider').text(data.data.providername);

                                $('#receipt').find('.numbertest').text(data.data.number);
                            }
                            $('#receipt').modal();
                            $('#datatable').dataTable().api().ajax.reload();
                        }else if(data.status == "TXNOTP"){
                            $('#otpreq').append(`<label>OTP</label>
                                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required="">`);
                        }else{
                            notify("Recharge "+data.status+ "! "+data.description, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });
    });

    function getBill(ele){
        var operator = $('#billpayForm').find('[name="provider_id"]').val();
        var number = $('#billpayForm').find('[name="number"]').val();
        var mobile = $('#billpayForm').find('[name="mobile"]').val();
        var billtype = $('#billpayForm').find('[name="billtype"]').val();
        var walletpin = $('#billpayForm').find('[name="walletpin"]').val();

        if(operator != "" && number != "" && mobile != ""){
            $.ajax({
                url: "{{route('billpay')}}",
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend:function(){
                    swal({
                        title: 'Wait!',
                        text: 'We are fetching bill details',
                        onOpen: () => {
                            swal.showLoading()
                        },
                        allowOutsideClick: () => !swal.isLoading()
                    });
                },
                data: {"type" : "getbilldetails", "provider_id" : operator, "number" : number, "mobile" : mobile, "billtype" : billtype,"walletpin" :walletpin}
            })
            .done(function(data) {
                swal.close();
                console.log('data');
                console.log(data);
                if(data.statuscode == "TXN"){
                    $('#billpayForm').find('[name="biller"]').val(data.data.CustomerName);
                    $('#billpayForm').find('[name="duedate"]').val(data.data.Duedate);
                    $('#billpayForm').find('[name="amount"]').val(data.data.Billamount);
                }else{
                    notify(data.status , 'warning');
                }
            })
            .fail(function(errors) {
                swal.close();
                showError(errors, $('#billpayForm'));
            });
        }
    }
</script>
@endpush