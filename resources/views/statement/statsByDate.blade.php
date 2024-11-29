@extends('layouts.app')
@section('title', "Stats By Date")
@section('pagetitle',  "Stats By Date")



@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Stats By Date</h4>
                </div>
                
             <div class="col-sm-12">
                <form class="form-control" method="post" action="{{ route('statsByDate') }}">
    {{ csrf_field() }}
    
    <div class="form-group col-md-3 m-b-10">
        <input type="datetime-local" name="from_date" class="form-control" placeholder="From Date" value="{{ isset($data['from_date']) ? $data['from_date'] : '' }}">
    </div>
    
    <div class="form-group col-md-3 m-b-10">
        <input type="datetime-local" name="to_date" class="form-control" placeholder="To Date" value="{{ isset($data['to_date']) ? $data['to_date'] : '' }}">
    </div>
    
    <div class="form-group col-md-3 m-b-10">
        <select name="service" class="form-control">
            <option value="matm" {{ isset($data['service']) && $data['service'] == 'matm' ? 'selected' : '' }}>MATM</option>
            <option value="cw" {{ isset($data['service']) && $data['service'] == 'cw' ? 'selected' : '' }}>Cash Withdrawal</option>
            <option value="m" {{ isset($data['service']) && $data['service'] == 'm' ? 'selected' : '' }}>Aadhar Pay</option>
            <option value="cd" {{ isset($data['service']) && $data['service'] == 'cd' ? 'selected' : '' }}>Cash Deposit</option>
            <option value="mfund" {{ isset($data['service']) && $data['service'] == 'mfund' ? 'selected' : '' }}>Manual Fund Request</option>
            <option value="wtb" {{ isset($data['service']) && $data['service'] == 'wtb' ? 'selected' : '' }}>Wallet / Profit Move To Bank</option>
            
        </select>
    </div>
    
    <div class="form-group col-md-3 m-b-10">
        <button type="submit" class="btn bg-slate btn-xs btn-labeled legitRipple btn-lg">
            <b><i class="icon-search4"></i></b> Search
        </button>
    </div>
</form>

        
            </div>



               <!-- ... -->

@if($data['datashow'] == 'view')
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Status</th>
            <th>Count</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['data'] as $alldata)
        <tr>
            <td>{{ $alldata->status }}</td>
            <td>{{ $alldata->count }}</td>
            <td>{{ $alldata->total_amount }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!-- ... -->

 </div>
        </div>
    </div>
</div>
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
       
        
    });

</script>
@endpush