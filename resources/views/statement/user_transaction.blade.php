@extends('layouts.app')
@section('title', "User Transaction")
@section('pagetitle',  "User Transaction")

@php
    $table = "yes";
    
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">User Transaction | {{ date('d-m-Y') }}</h4>
                </div>
                <table id="example" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th>User Details</th>
                            <th>Total Transaction</th>
                            <th>Total Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                
                            </td>
                             <td>
                                {{$user->name}} <b>[ {{$user->id}} ] => {{$user->role->name}}</b>
                                </br>{{$user->mobile}}
                            </td>
                             <td>
                                {{ \Myhelper::total_transaction($user->id)}} 
                            </td>
                            <td>
                                {{ \Myhelper::total_profit($user->id)}} 
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script>
    $(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ],
        "pagingType": "full_numbers"
    } );
} );
</script>

@endpush