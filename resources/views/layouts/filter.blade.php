<form class="row" id="dataFilter">
        <div class="panel-body" id="moreFilter" style="display: none">
        @if(isset($option1))
        <div class="form-group col-md-3 m-b-0">
            <select class="select" name="option1">
                <option value="">Sort By {{$option1['title']}}</option>
                @if(isset($option1['values']))
                    @foreach($option1['values'] as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                @endif
            </select>
        </div>
        @endif

        @if(isset($option2))
        <div class="form-group col-md-3 m-b-0">
            <select class="select" name="option2">
                <option value="">Sort By {{$option2['title']}}</option>
                @if(isset($option2['values']))
                    @foreach($option2['values'] as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                @endif
            </select>
        </div>
        @endif

        @if(isset($option3))
        <div class="form-group col-md-3 m-b-0">
            <select class="select" name="option3">
                <option value="">Sort By {{$option3['title']}}</option>
                @if(isset($option3['values']))
                    @foreach($option3['values'] as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                @endif
            </select>
        </div>
        @endif

        @if(isset($status))
        <div class="form-group col-md-3 m-b-0">
            <select class="select" name="status">
                <option value="">Sort By Status</option>
                @foreach($status as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(isset($mode))
        <div class="form-group col-md-3 m-b-0">
            <select class="select" name="mode">
                <option value="">Sort By Mode</option>
                @foreach($mode as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <div class="panel-body">
        <div class="form-group col-md-3 m-b-0">
            <input type="text" name="from_date" placeholder="Enter from date" autocomplete="off" class="form-control date {{$fromdate ?? ''}}">
        </div>
        <div class="form-group col-md-3 m-b-0">
            <input type="text" name="to_date" placeholder="Enter to date" autocomplete="off" class="form-control date {{$todate ?? ''}}">
        </div>
        @if(isset($type))
        <div class="form-group col-md-3 m-b-0">
            <input type="text" name="searchtext" placeholder="Search Text" class="form-control">
        </div>
        @endif

        <div class="form-group col-md-3 m-b-0 m-t-5">
            <button type="submit" class="btn btn-inverse waves-effect waves-light btn-sm waves-effect" data-loading-text="<i class='fa fa-spin fa-spinner'></i>"><i class="fa fa-search"></i> Search</button>
            <button type="button" class="btn btn-warning btn-sm waves-effect" id="formReset"><i class="fa fa-refresh"></i> Refresh</button>
            @if(isset($type))
            <button type="button" class="btn btn-white btn-sm waves-effect" id="moreDataFilter"><i class="fa fa-arrow-down"></i> More Filter</button>
            @endif
        </div>
    </div>
</form>