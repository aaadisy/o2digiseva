@extends('layouts.app')
@section('title', 'Dashboard')
@section('pagetitle', 'Dashboard')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                    
			<div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Aeps</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('ifaeps' , ['type' => 'aeps'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		                <div class="panel">
		                    <div class="panel-heading text-center bg-indigo-800">
		                        <h5 class="panel-title">Cash Deposit / Express Payout</h5>
		                    </div>
		                    <div class="container-fluid panel-body">
		                        <div class="row text-center">
		                       	    <div class="col-md-4">
		                                <div class="content-group no-margin">
		                                    <a href="{{route('bill' , ['type' => 'cashdeposit'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
		                                    <span></span>
		                                </div>
		                            </div>
		                        </div>
		                    </div>
		                </div>
                        </div>
                        <div class="col-md-4">
		        
                        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Aeps Fund Request</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('fund', ['type' => 'aeps'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		        
                        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Commission Transfer</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('bill' , ['type' => 'wt'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Aeps Statement</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('statement', ['type' => 'aeps'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Micro ATM Statement</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('statement', ['type' => 'matm'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Aadharpay Statement</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('statement', ['type' => 'aadharpay'])}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-4">
		        <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Aeps Invoice</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	    <div class="col-md-4">
                                        <div class="content-group no-margin">
                                            <a href="{{route('aeps_invoice')}}"><h5 class="text-semibold no-margin">Click Here</h5></a>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        
                        <div class="col-md-4">
		                <div class="panel">
                            <div class="panel-heading text-center bg-indigo-800">
                                <h5 class="panel-title">Support</h5>
                            </div>
                            <div class="container-fluid panel-body">
                                <div class="row text-center">
                               	   Mobile: +91-9883722390 / +91-7501648140 / +91-9679427505
                                </div>
                            </div>
                        </div>

                       
                        </div>
                        <div class="amount-container">
                                  @php
    $parentData = session('parentData', \Myhelper::getParents(\Auth::id()));
$currentDate = now()->format('Y-m-d');

$totalAmountsData = DB::table('aepsreports')
    ->whereIn('user_id', $parentData)
    ->where('status', 'success')
    ->where('transtype', 'transaction')
    ->whereDate('created_at', $currentDate)
    ->groupBy('product', 'aepstype') // Group by both 'product' and 'aepstype'
    ->select(
        'product', 
        'aepstype',
        DB::raw('SUM(amount) as total_amount'), 
        DB::raw('COUNT(*) as count')
    )
    ->get();


      @endphp
    @foreach ($totalAmountsData as $index => $totalAmountData)
    @php
    if($totalAmountData->product == 'aeps'){
    $totalAmountData->product = $totalAmountData->aepstype;
    }
    else{
     $totalAmountData->product = $totalAmountData->product;
    }
    
    if($totalAmountData->product == 'M'){
    $totalAmountData->product = 'AadharPay';
    }
    if($totalAmountData->product == 'MS'){
    $totalAmountData->product = 'Mini Statement';
    } 
    if($totalAmountData->product == 'CW'){
    $totalAmountData->product = 'AEPS Cash Withdrawal';
    }
    if($totalAmountData->product == 'matm'){
    $totalAmountData->product = 'Matm Cash Withdrawal';
    }
    @endphp
    <div class="col-md-4">
        <div class="panel">
            <div class="panel-heading text-center bg-indigo-800">
                <h5 class="panel-title">Today's {{ ucfirst($totalAmountData->product) }}</h5>
            </div>
            <div class="panel-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <div class="text-center bg-indigo-800">
                            <strong class="panel-title">Success Amount</strong>
                        </div>
                        <div class="counter" id="counter{{ $index }}" style="font-size: 24px;" title="Total Success Amount">
                            {{ number_format($totalAmountData->total_amount) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center bg-indigo-800">
                            <strong class="panel-title">Success Count</strong>
                        </div>
                        <span class="count" id="count{{ $index }}" title="Total Success Count">
                            {{ $totalAmountData->count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

                        </div>
                   
                </div>
            </div>
        </div>
       
        <div class="row">





        
   
</div>



            <!-- The fetched aepstypes will be displayed here -->
        </div>

        </div>
      
        <div class="row">
      

  

    <!-- Include Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
 
<script>
   function fetchAndUpdateData() {
    $.get('/fetch-total-amount', function(response) {
        if (response && response.totalAmounts && response.totalCounts) {
            updateCounters(response.totalAmounts, response.totalCounts);
        } else {
            console.error('Invalid response data');
        }
    }).fail(function(error) {
        console.error('Error fetching data:', error);
    });
}

function updateCounters(totalAmounts, totalCounts) {
    totalAmounts.forEach((totalAmount, index) => {
        const counterElement = document.getElementById('counter' + index);
        if (counterElement) {
            counterElement.innerText = formatNumber(totalAmount);
        }
    });

    totalCounts.forEach((totalCount, index) => {
        const counterElement = document.getElementById('count' + index);
        if (counterElement) {
            counterElement.innerText = formatNumber(totalCount);
        }
    });
}

function formatNumber(number) {
    // Format the number as needed, e.g., add commas, decimals, etc.
    return number.toLocaleString();
}


    function animateCounter(element, initialValue, targetValue, duration) {
        const startTime = Date.now();
        const updateInterval = 100; // Update every 100ms
        const steps = duration / updateInterval;
        const stepValue = (targetValue - initialValue) / steps;

        const update = () => {
            const currentTime = Date.now();
            const elapsedTime = currentTime - startTime;
            const currentValue = Math.min(initialValue + stepValue * (elapsedTime / duration), targetValue);
            element.innerText = numberWithCommas(Math.round(currentValue));

            if (currentTime - startTime < duration) {
                requestAnimationFrame(update);
            }
        };

        update();
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Initial fetch and update
    fetchAndUpdateData();

    // Periodically fetch and update data every 5 seconds
    setInterval(fetchAndUpdateData, 5000);
</script>






        </div>
        
               
    

    <div id="noticeModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Necessary Notice ( आवश्यक सूचना )</h4>
                </div>
                <div class="modal-body">
                     {!! nl2br($mydata['notice']) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
@endsection

@push('script')
<script type="text/javascript" src="{{asset('')}}assets/js/plugins/forms/selects/select2.min.js"></script>
<script>
    $(document).ready(function(){
        $('select').select2();

        @if (Myhelper::hasNotRole('admin'))
            @if (Auth::user()->kyc != "verified")
                $('#kycModal').modal();
            @endif
        @endif

        @if (Myhelper::hasNotRole('admin') && Auth::user()->resetpwd == "default")
            $('#pwdModal').modal();
        @endif

        @if ($mydata['notice'] != null && $mydata['notice'] != '')
            $('#noticeModal').modal();
        @endif

        $( "#kycForm" ).validate({
            rules: {
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
                },
                pancard: {
                    required: true,
                },
                shopname: {
                    required: true,
                },
                pancardpics: {
                    required: true,
                },
                aadharcardpics: {
                    required: true,
                }
            },
            messages: {
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
                },
                pancard: {
                    required: "Please enter pancard",
                },
                shopname: {
                    required: "Please enter shop name",
                },
                pancardpics: {
                    required: "Please upload pancard pic",
                },
                aadharcardpics: {
                    required: "Please upload aadharcard pic",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $( "#kycForm" );
                form.find('span.text-danger').remove();
                form.ajaxSubmit({
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
                            notify("Profile Successfully Updated, wait for kyc approval" , 'success');
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

        $( "#passwordForm" ).validate({
            rules: {
                @if (!Myhelper::can('member_password_reset'))
                oldpassword: {
                    required: true,
                    minlength: 6,
                },
                password_confirmation: {
                    required: true,
                    minlength: 8,
                    equalTo : "#password"
                },
                @endif
                password: {
                    required: true,
                    minlength: 8
                }
            },
            messages: {
                @if (!Myhelper::can('member_password_reset'))
                oldpassword: {
                    required: "Please enter old password",
                    minlength: "Your password lenght should be atleast 6 character",
                },
                password_confirmation: {
                    required: "Please enter confirmed password",
                    minlength: "Your password lenght should be atleast 8 character",
                    equalTo : "New password and confirmed password should be equal"
                },
                @endif
                password: {
                    required: "Please enter new password",
                    minlength: "Your password lenght should be atleast 8 character"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#passwordForm');
                form.find('span.text-danger').remove();
                form.ajaxSubmit({
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
                            form.closest('.modal').modal('hide');
                            notify("Password Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.modal-body'));
                    }
                });
            }
        });
    });
</script>
@endpush
