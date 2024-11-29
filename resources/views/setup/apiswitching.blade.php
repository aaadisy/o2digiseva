@extends('layouts.app')
@section('title', 'Api Switching')
@section('pagetitle',  'Api Switching')
@php
    $table = "yes";
    $agentfilter = "hide";
    $status['type'] = "Api";
    $status['data'] = [
        "1" => "Active",
        "0" => "De-active"
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
					<h4 class="panel-title">Api Switching</h4>
					
				</div>
				<div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-responsive" id="datatable">
                    <thead>
                        <tr>
                            <th>API Name</th>
                            
                            @foreach($providers as $provider)
                            <th>Operator</th>
                            @endforeach
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apis as $api)
                        <tr>
                            @php
                            $listapiid = $api->id;
                            
                            @endphp
                            <td>{{$loop->iteration}} | {{ $api->product }}</td>
                            @foreach($providers as $provider)
                            <td>
                            {{ $provider->name }}
                            <div>
                            <input name="provider_id" class="thisapi" id="provider_id_{{ $provider->id }}" value="{{ $provider->id }}" type="hidden" />
                            <input class="thisapi"  name="provider_id_api" id="provider_id_api_{{ $api->id }}" value="{{ $listapiid }}" type="hidden" />
                            
                            @php
                            $apiid = $api->id;
                                $selected = \Myhelper::get_selected_api_switch($provider->id, $listapiid);
                            @endphp
                            <select class="form control switchapi thisapi" name="default_api">
                                <option  value="">Select Switch API</option>
                                @foreach($apis as $api)
                                @php
                                
                                $api_id_new = $api->id;
                                @endphp
                                <option @php
                                
                                if($selected == $api_id_new) 
                                {
                                echo 'selected';
                                }
                                
                                @endphp value="{{ $api->id }}">{{ $api->product }}</option>
                                 @endforeach
                                
                            </select>
                            </div>
                            </td>
                            @endforeach
                            
                            <td>
                            Default
                            <input name="provider_id" class="thisapi" id="provider_id_0" value="0" type="hidden" />
                            <input name="provider_id_api" class="thisapi"  value="{{ $listapiid }}" type="hidden" />
                            @php
                            $apiid = $api->id;
                                $selected = \Myhelper::get_selected_api_switch('0', $listapiid);
                            @endphp
                            <select class="form control switchapidefault thisapi" name="switch_api">
                                <option value="">Select Switch API</option>
                                 @foreach($apis as $api)
                                @php
                                
                                $api_id_new = $api->id;
                                @endphp
                                <option @php
                                
                                if($selected == $api_id_new) 
                                {
                                echo 'selected';
                                }
                                
                                @endphp value="{{ $api->id }}">{{ $api->product }}</option>
                                 @endforeach
                                
                            </select>
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


@endsection

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        $('.switchapi').change(function () {
            var id = $(this).val();
            var api_id = $(this).parent().parent().find("input[name=default_api]").val();
            var provider_id_api = $(this).parent().parent().find("input[name=provider_id_api]").val();
           var provider_id =  $(this).parent().parent().find("input[name=provider_id]").val();


            $.ajax({
                 url: '{{ route('saveapiswitching') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    
                    dataType:'json',
                data: {'id': id,'api_id':api_id,'provider_id_api':provider_id_api,'provider_id':provider_id}

            }).done(function (data) {
                    if (data.status == "success") {
                        notify("Api Switch Successfully Completed", 'success');
                    }else{
                        notify(data.status, 'warning');
                    }
            });
        });
        
    });
    
    $(document).ready(function () {
        $('.switchapidefault').change(function () {
            var id = $(this).val();
            var api_id = $(this).parent().parent().find("input[name=default_api]").val();
            var provider_id_api = $(this).parent().parent().find("input[name=provider_id_api]").val();
           var provider_id =  0;


            $.ajax({
                 url: '{{ route('saveapiswitching') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    
                    dataType:'json',
                data: {'id': id,'api_id':api_id,'provider_id_api':provider_id_api,'provider_id':provider_id}

            }).done(function (data) {
                    if (data.status == "success") {
                        notify("Api Switch Successfully Completed", 'success');
                    }else{
                        notify(data.status, 'warning');
                    }
            });
        });
        
    });

   
</script>
@endpush