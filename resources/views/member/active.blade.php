@extends('layouts.app')
@section('title', 'Active Member List')
@section('pagetitle','Active Member List')

@php
    $table = "yes";
    
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Active Member List</h4>
                    <button id="exportCsvBtn" class="btn btn-primary">Export CSV</button>


                   
                </div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Mobile</th>
                            <th>Wallet Balance</th>
                            <th>AEPS Balance</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


@endsection



@push('script')
<script type="text/javascript" src="{{asset('')}}assets/js/plugins/forms/selects/select2.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        activemembers();
         setInterval(activemembers,5000);

         function exportToCsv(filename, rows) {
    const csvContent = "data:text/csv;charset=utf-8," + rows.map(row => row.join(',')).join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link); // Required for Firefox
    link.click();
}


        function activemembers(){
            $.ajax({
                url: "{{ route('activememberlist') }}",
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                success: function(result){
                    $('table > tbody').empty();
                    $.each(result, function(key, val) {
                      $('table > tbody').append('<tr><td>' + val.id + '</td> +<td>' + val.name + '</td><td>' + val.role.name + '</td><td>' + val.mobile + '</td><td> ₹' + val.mainwallet + '</td><td> ₹' + val.aepsbalance + '</td></tr>')
                    });
            }
        
        
    });
    
    }

    $('#exportCsvBtn').click(function () {
        const csvRows = [];
        const tableHeaders = [];
        
        // Get table headers
        $('#datatable thead th').each(function() {
            tableHeaders.push($(this).text());
        });
        csvRows.push(tableHeaders);

        // Get table data
        $('#datatable tbody tr').each(function () {
            const rowData = [];
            $(this).find('td').each(function () {
                rowData.push($(this).text().trim());
            });
            csvRows.push(rowData);
        });

        // Trigger CSV export
        const filename = "active member.csv";
        exportToCsv(filename, csvRows);
    });
        
    });

    
</script>

@endpush