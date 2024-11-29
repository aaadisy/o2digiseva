@extends('layouts.app')
@section('title', 'Not Loggedin Member')
@section('pagetitle','Not Loggedin Member')



@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Not Loggedin Member Report Download</h4>

                   
                </div>

                <form action="{{ route('notloggedin') }}" method="post">
                @csrf
                <label for="days">Number of Days:</label>
                <input type="number" name="days" min="1" value="1">
                <button type="submit">Generate Report</button>
            </form>

              
            </div>
        </div>
    </div>
</div>


@endsection



@push('script')


@endpush