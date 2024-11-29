@extends('layouts.app')
@section('title', ucfirst($type).' Recharge')
@section('pagetitle', ucfirst($type).' Recharge')
@php
    $table = "yes";
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{{ucfirst($type)}} Recharge</h4>
                </div>
                <form id="rechargeForm" action="{{route('rechargepay')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="type" value="{{$type}}">
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Mobile Operator</label>
                            <select id="provider_id" name="provider_id" class="form-control select" required>
                                <option value="">Select Operator</option>
                                @foreach ($providers as $provider)
                                    <option value="{{$provider->id}}">{{$provider->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ucfirst($type)}} Number</label>
                            <input id="number" type="text" name="number" class="form-control" placeholder="Enter {{$type}} number" required="">
                        </div>
                        <div class="form-group">
                            <label>Recharge Amount</label>
                            <input type="text" id="amount" name="amount" class="form-control" placeholder="Enter {{$type}} amount" required="">
                        </div>
                        <div class="form-group">
                            <label>Wallet Pin</label>
                            <input type="text" name="walletpin" class="form-control" placeholder="Enter Pin" required="">
                        </div>
                        <div class="panel-footer text-center">
                        <button type="button" class="btn btn-primary userinfo" data-toggle="modal" data-target="#exampleModalLong">
                          Check Plans
                        </button>
                        </div>
                    </div>
                    <div class="panel-footer text-center">
                        <button type="submit" class="btn bg-teal-400 btn-labeled btn-rounded legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Paying"><b><i class=" icon-paperplane"></i></b> Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
					<h4 class="panel-title">Recent {{ucfirst($type)}} Recharge</h4>
				</div>
				<div class="panel-body">
				</div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Recharge Details</th>
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
<div class="modal fade" id="empModal" role="dialog">
    <div class="modal-dialog">
 
     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">ROffer</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
 
      </div>
      <div class="modal-footer">
       <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
     </div>
    </div>
</div>
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
 
<script>
    $('.btnsave').on('click', function() {
  $('#amount').text( $('this').data('amount') );
});
</script>
<script>
    $(document).ready(function(){

 $('.userinfo').click(function(){
   
   
   var provider_id = $('#provider_id').find(":selected").val();
   var number = $('#number').val();
   var url = "{{url('roffer')}}";
   var token = $('meta[name="csrf-token"]').attr('content');
   // AJAX request
   $.ajax({
    url: url,
    type: 'post',
    data: {provider_id: provider_id,number:number, _token: token},
    success: function(response){ 
      // Add response in Modal body
      var data = JSON.parse(response);
        var htm = '<ul>';
        console.log(data);
        console.log(data.records);
        var res = data.records;
        for(var o = 0; o < res.length; o++){
          htm += '<li style="cursor: pointer;" onclick="setAmount('+res[o].rs+')"><p>Amount: '+res[o].rs+'</p><p>'+res[o].desc+'</p></li>';  

          htm +='----------------------------------------------------------------------------------------------------------------------------';
        }
        htm += '</ul>';
      $('.modal-body').html(htm);

      // Display Modal
      $('#empModal').modal('show'); 
    }
  });
 });
});

    function setAmount(amt){
        $('#amount').val(amt);
        $('#empModal').modal('hide'); 
    }
</script>

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/rechargestatement/0";

        var onDraw = function() {};

        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class=''>`+full.apiname +`</span><br>
                            <span class='text-inverse m-l-10'><b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Number - `+full.number+`<br>Operator - `+full.providername;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `Amount - <i class="fa fa-inr"></i> `+full.amount+`<br>Profit - <i class="fa fa-inr"></i> `+full.profit;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success"){
                        var out = `<span class="label label-success">Success</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else if(full.status == "reversed"){
                        var out = `<span class="label bg-slate">Reversed</span>`;
                    }else{
                        var out = `<span class="label label-danger">Failed</span>`;
                    }
                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $( "#rechargeForm" ).validate({
            rules: {
                provider_id: {
                    required: true,
                    number : true,
                },
                number: {
                    required: true,
                    number : true,
                    minlength: 8
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
            },
            messages: {
                provider_id: {
                    required: "Please select {{$type}} operator",
                    number: "Operator id should be numeric",
                },
                number: {
                    required: "Please enter {{$type}} number",
                    number: "Mobile number should be numeric",
                    min: "Mobile number length should be atleast 8",
                },
                walletpin: {
                    required: "Please enter Wallet Pin",
                    number: "Wallet Pin should be numeric",
                    min: "Wallet Pin length should be 4 Digits",
                },
                amount: {
                    required: "Please enter {{$type}} amount",
                    number: "Amount should be numeric",
                    min: "Min {{$type}} amount value rs 10",
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
                var form = $('#rechargeForm');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        form.find('button[type="submit"]').button('reset');
                        if(data.status == "success" || data.status == "pending"){
                            getbalance();
                            form[0].reset();
                            form.find('select').select2().val(null).trigger('change')
                            form.find('button[type="submit"]').button('reset');
                            notify("Recharge Successfully Submitted", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
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
</script>
@endpush