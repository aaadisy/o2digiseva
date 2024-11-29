@extends('layouts.app')
@section('title', 'Video Tutorials')
@section('pagetitle',  'Video Tutorials')
@php
    $table = "yes";
    $agentfilter = "hide";
    $status['type'] = "Template";
    $status['data'] = [
        "active" => "Active",
        "inactive" => "Inactive"
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
					<h4 class="panel-title">Video Tutorials</h4>
    				 @if (Myhelper::can('video_tutorials'))
					<div class="heading-elements">
                        <button type="button" class="btn btn-sm bg-slate btn-raised heading-btn legitRipple" onclick="addSetup()">
                            <i class="icon-plus2"></i> Add New
                        </button>
                    </div>
                    @endif
					
				</div>
                <table class="table table-bordered table-striped table-hover" id="datatable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Video</th>
                             @if (Myhelper::can('video_tutorials'))
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($videos as $vid)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$vid->name}}</td>
                            <td>
                            <video width="400" controls>
                              <source src="{{route('front')}}/public/uploads/{{$vid->video}}" type="video/mp4">
                              Your browser does not support HTML video.
                            </video>
                            </td>
                            
                             @if (Myhelper::can('video_tutorials'))
                                <td><a href="{{ route('videodelete',$vid->id) }}" class="btn btn-danger">Delete</a></td>
                            @endif
                        </tr>
                        @endforeach
                        
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Video Tutorial</h6>
            </div>
            <form id="setupManager" enctype='multipart/form-data'  action="{{route('videoadd')}}" method="post">
                <div class="modal-body">
                    
                   
                    <hr>
                    <div class="row">
                        <input type="hidden" name="id">
                        {{ csrf_field() }}
                        <div class="form-group col-md-8">
                            <label>Name</label>
                            <textarea name="name" class="form-control">
                                
                            </textarea>
                            
                        </div>
                        <div class="form-group col-md-4">
                            <label>Video</label>
                            <input type="file" name="video" class="form-control" required="">
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


    function addSetup(){
    	$('#setupModal').find('.msg').text("Add");
    	$('#setupModal').find('input[name="id"]').val("new");
    	$('#setupModal').modal('show');
	}

	function editSetup(id,content, name, status){
		$('#setupModal').find('.msg').text("Edit");
    	$('#setupModal').find('input[name="id"]').val(id);
        $('#setupModal').find('input[name="name"]').val(name);
        $('#setupModal').find('textarea[name="content"]').val(content);
        $('#setupModal').find('[name="status"]').select2().val(status).trigger('change');
    	$('#setupModal').modal('show');
	}
</script>
@endpush