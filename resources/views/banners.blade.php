@extends('layouts.app')
@section('title', 'Banners')
@section('pagetitle', 'Banners')
@section('content')

    <div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Banners</h4>
                    <div class="heading-elements">
                        <button type="button" class="btn btn-sm bg-slate btn-raised heading-btn legitRipple" onclick="addSetup()">
                            <i class="icon-plus2"></i> Add New
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <table class="table table-bordered table-striped table-hover" id="datatable">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Banner</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        
                        <tbody>
                             @foreach($banners as $banner)
                            <tr>
                                <td>
                                    
                                </td>
                                <td>
                                  <img style="max-width: 150px;" src="{{ url('/public/images') }}/{{ $banner->banner }}"  />
                                </td>
                                <td>
                                    {{ $banner->type }}
                                </td>
                                <td>
                                    <a href="{{route('deletebanner',$banner->id)}}">Delete</a>
                                    
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        </table>
        </div>
            </div>
            
            
        </div>
        
        
    </div>
</div>

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Banner</h6>
            </div>
            <form  enctype="multipart/form-data" id="setupManager" action="{{route('savebanner')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Banner</label>
                            <input type="file" name="input_img" class="form-control" placeholder="Enter value" required="">
                        </div>

                        <div class="form-group col-md-6">
                            <label>Type</label>
                            <select name="type" class="form-control" required="">
                                <option>Select Banner Type</option>
                                <option value="website">Website</option>
                                <option value="recharge">Recharge</option>
                                <option value="dth">Dth</option>
                                <option value="billpayment">Bill Payment</option>
                                <option value="mobileapp">Mobile App</option>
                            </select>
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection       
@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        
    });
    
    
        function addSetup(){
    	$('#setupModal').find('.msg').text("Add");
    	$('#setupModal').find('input[name="id"]').val("new");
    	$('#setupModal').modal('show');
	}
        
    
</script>
@endpush