@extends('layouts.app')
@section('title', "Delete Data")
@section('pagetitle', "Delete Data")

@php
$table = "no";
$export = "aeps";


@endphp

@section('content')
<style>
    .progress {
        width: 100%;
        background-color: #f1f1f1;
    }

    .bar {
        width: 0;
        height: 30px;
        background-color: #4CAF50;
        text-align: center;
        line-height: 30px;
        color: white;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Delete Data</h4>
                </div>
                <div class="panel-body p-tb-10">
                    <div class="col-sm-12">
                        <form class="searchForm" id="deleteForm">
                            @csrf
                            <div class="form-group col-md-2 m-b-10">
                                <label>From</label>
                                <input class="form-control" type="date" id="date_from" name="date_from" required>
                            </div>

                            <div class="form-group col-md-2 m-b-10">
                                <label>To</label>
                                <input class="form-control" type="date" id="date_to" name="date_to" required>
                            </div>
                            <div class="form-group col-md-2 m-b-10">
                                <label>Table</label>
                                <select class="form-control" id="table_name" name="table_name" required>
                                    <option value="apilogs">Apilogs</option>
                                    <option value="microlog">Microlog</option>
                                    <option value="aepsreports">Aeps Reports</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2 m-b-10">
                                <button type="button" id="deleteBtn">Delete Data</button>
                            </div>
                        </form>

                        <div class="progress-container" style="display: none;">
                            <div class="progress-bar" id="progressBar">0%</div>
                        </div>

                        <div id="message" style="display: none;"></div>
                    </div>

                </div>



            </div>
        </div>
    </div>
</div>


@endsection

@push('style')

@endpush

@push('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Define the deleteData function globally
    function deleteData() {
        var formData = new FormData($('#deleteForm')[0]);
        var progressBar = $('#progressBar');
        var messageDiv = $('#message');

        // Display the progress bar and reset the progress
        progressBar.width('0%');
        progressBar.html('0%');
        progressBar.parent().css('display', 'block');

        // Disable the delete button during the process
        $('#deleteBtn').prop('disabled', true);

        // Make the Ajax request using jQuery
        $.ajax({
            url: '{{ route("deleteData") }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var progress = Math.round((evt.loaded / evt.total) * 100);
                        progressBar.width(progress + "%");
                        progressBar.html(progress + "%");
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                // Show the success message and hide the progress bar
                messageDiv.html(response.message);
                messageDiv.css('display', 'block');
                progressBar.parent().css('display', 'none');

                // Enable the delete button after the process
                $('#deleteBtn').prop('disabled', false);
            },
            error: function() {
                // Show the error message and hide the progress bar
                messageDiv.html('An error occurred while deleting data.');
                messageDiv.css('display', 'block');
                progressBar.parent().css('display', 'none');

                // Enable the delete button after the process
                $('#deleteBtn').prop('disabled', false);
            }
        });
    }

    // Attach the click event handler to the delete button
    $(document).ready(function() {
        $('#deleteBtn').click(function() {
            deleteData(); // Call the deleteData function
        });
    });
</script>
@endpush