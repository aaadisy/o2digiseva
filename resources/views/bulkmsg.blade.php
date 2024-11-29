@extends('layouts.app')
@section('title', 'Bulk Msgs')
@section('pagetitle', 'Bulk Msgs')
@section('content')

    <div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Bulk Msgs</h4>
                </div>
                
                <div class="row">
                    <form class="actionForm" action="{{route('sendbulkmsg')}}" method="post">
                {{ csrf_field() }}
            <div class="col-md-12">
                <div class="panel-body p-b-0">
                    <div class="form-group">
                            <label>Member Type</label>
                            <select name="member_type" required="" class="form-control select">
                                <option value="">Select Member Type</option>
                                @foreach($member_type as $mem)
                                <option value="{{ $mem->id}}">{{ $mem->name }}</option>
                                @endforeach
                                
                            </select>
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                
                <div class="panel-body p-b-0">
                    <div class="form-group">
                            <label>Enter Msg</label>
                            <textarea name="msg"class="form-control" required="" placeholder="Enter Msg" rows="6"></textarea>
                    </div>
                </div>
                
            </div>

            <div class="col-md-12">
                <div class="panel-body p-b-0">
                    <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="sent_img" id="sent_img">
                    </div>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="panel-body p-b-0">
                    <div class="form-group">
                            <button class="btn bg-slate btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Sending...">Send</button>
                    </div>
                </div>
            </div>
            </form>
        </div>
            </div>
            
            
        </div>
        
        
    </div>
</div>
@endsection       
@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        $('.actionForm').submit(function(event) {
            var form = $(this);
            var id = form.find('[name="id"]').val();
            form.ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    form.find('button[type="submit"]').button('loading');
                },
                success:function(data){
                    if(data.status == "success"){
                        if(id == "new"){
                            form[0].reset();
                            $('[name="api_id"]').select2().val(null).trigger('change');
                        }
                        form.find('button[type="submit"]').button('reset');
                        notify("Task Successfully Completed", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        notify(data.status, 'warning');
                    }
                },
                error: function(errors) {
                    showError(errors, form);
                }
            });
            return false;
        });

    	$("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });

        $('')
    });
</script>
@endpush