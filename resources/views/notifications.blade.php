@extends('layouts.app')
@section('title', 'Notifications')
@section('pagetitle', 'Notifications')
@section('content')

    <div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Notifications</h4>
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
                            <th>Image</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        
                        <tbody>
                             @foreach($notifications as $notification)
                            <tr>
                                <td>
                                    
                                </td>
                                <td>
                                  @if($notification->image != "")
                                    <img style="max-width: 150px;" src="{{ $notification->image }}"  />
                                  @else
                                    NA
                                  @endif
                                </td>
                                <td>
                                    {{ $notification->title }}
                                </td>
                                <td>
                                    {{ $notification->content }}
                                </td>
                                <td>
                                    <a href="{{route('deletenotification',$notification->id)}}">Delete</a>
                                    
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
            <form  enctype="multipart/form-data" id="setupManager" action="{{route('savenotification')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        {{ csrf_field() }}
                        <div class="form-group col-md-12">
                            <label>Users</label>
                            <select name="member_type" class="form-control select" required="">
                                <option>Select User</option>
                                @if(isset($member_type) && !empty($member_type))
                                    @foreach($member_type as $mem)
                                        <option value="{{ $mem->id}}">{{ $mem->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group col-md-12">
                            <label>Banner</label>
                            <input type="file" name="input_img" class="form-control" placeholder="Enter value">
                        </div>

                        <div class="form-group col-md-12">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required="required" placeholder="Enter Title" />
                        </div>

                        <div class="form-group col-md-12">
                            <label>Content</label>
                            <textarea name="content" class="form-control" required="required" placeholder="Enter Content"></textarea>
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
    <script src="//cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" async></script>
    <script type="text/javascript">
    $(document).ready(function () {
        $('#datatable').DataTable();   
    });
    
    
        function addSetup(){
        $('#setupModal').find('.msg').text("Add");
        $('#setupModal').find('input[name="id"]').val("new");
        $('#setupModal').modal('show');
    }
        
    
</script>
@endpush