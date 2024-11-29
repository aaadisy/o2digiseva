<!-- Page header -->
<div class="page-header page-header-default mb-10" style="max-height: 25px;">
    <div class="page-header-content">
        <div class="page-title">
            <div class="row">
                @if (\Session::has('success'))
    <div class="alert alert-success">
        <ul>
            <li>{!! \Session::get('success') !!}</li>
        </ul>
    </div>
    @else
    @if (\Session::has('error'))
    <div class="alert alert-danger">
        <ul>
            <li>{!! \Session::get('error') !!}</li>
        </ul>
    </div>
    @endif
    @endif
            </div>
            <div class="row">
                <h4  style="color:#fff;background-color:#FF5722;height: 30px;" class="col-md-3">@yield('pagehead', 'DIGISEVA NOTICE') - @yield('pagetitle')</h4>
               
                    
                     <h4 style="color:#fff;background-color:#3991ab;" class="col-md-9"><marquee style="height: 25px" onmouseover="this.stop();" onmouseout="this.start();">
                      {{$mydata['news']}}
                     </marquee></h4>
                    
                    
                
            </div>
        </div>
    </div>
</div>

@if (!Request::is('dashboard') && !Request::is('statsByDate') && !Request::is('delete-data-view') && !Request::is('bulkmsg') && !Request::is('banner') && !Request::is('ifaeps/aeps/*') && !Request::is('ifaeps/ap2fa/*') && !Request::is('ifaeps/aeps')  && !Request::is('ifaeps/ap2fa') && !Request::is('ifaeps/cw2fa') && !Request::is('ifaeps/cw2fa/*') &&  !Request::is('profile/*') && !Request::is('recharge/*') && !Request::is('billpay/*') && !Request::is('pancard/*') && !Request::is('member/*/create') && !Request::is('profile') && !Request::is('profile/*') && !Request::is('dmt') && !Request::is('resources/companyprofile') && !Request::is('aeps/*') && !Request::is('developer/*') && !Request::is('resources/commission') && !Request::is('setup/portalsetting') && !Request::is('member/active/now/members')  && !Request::is('setup/api/apiswitching') && !Request::is('setup/template/whatsapptemplate') && !Request::is('setup/video/tutorial') && !Request::is('member/user/transaction/total') && !Request::is('aeps_invoice') && !Request::is('wallet_invoice') )

<!-- /page header -->
<div class="content p-b-0">
    <form id="searchForm" class="form control">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Search</h4>
                <div class="heading-elements">
                    <button type="submit" class="btn bg-slate btn-xs btn-labeled legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i></b> Search</button>
                    <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Refreshing"><b><i class="icon-rotate-ccw3"></i></b> Refresh</button>
                    <button type="button" class="btn btn-primary btn-xs btn-labeled legitRipple {{ isset($export) ? '' : 'hide' }}" product="{{ $export ?? '' }}" id="reportExport"><b><i class="icon-cloud-download2"></i></b> Export</button></div>
            </div>
            <div class="panel-body p-tb-10">
                @if(isset($mystatus))
                    <input type="hidden" name="status" value="{{$mystatus}}">
                @endif
                <div class="row">
                    <div class="form-group col-md-2 m-b-10">
                        <input type="date" name="from_date" class="form-control mydate" placeholder="From Date" value="<?=date('Y-m-d')?>">
                    </div>
                    <div class="form-group col-md-2 m-b-10">
                        <input type="date" name="to_date" class="form-control mydate" placeholder="To Date">
                    </div>
                    <div class="form-group col-md-2 m-b-10">
                        <input type="text" name="searchtext" class="form-control" placeholder="Search Value">
                    </div>
                    @if (Myhelper::hasNotRole(['retailer', 'apiuser']))
                        <div class="form-group col-md-2 m-b-10 {{ isset($agentfilter) ? $agentfilter : ''}}">
                            <input type="text" name="agent" class="form-control" placeholder="Agent Id / Parent Id">
                        </div>
                    @endif

                    @if(isset($status))
                    <div class="form-group col-md-2">
                        <select name="status" class="form-control select">
                            <option value="">Select {{$status['type'] ?? ''}} Status</option>
                            @if (isset($status['data']) && sizeOf($status['data']) > 0)
                                @foreach ($status['data'] as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif

                    @if(isset($product))
                    <div class="form-group col-md-2">
                        <select name="product" class="form-control select">
                            <option value="">Select {{$product['type'] ?? ''}}</option>
                            @if (isset($product['data']) && sizeOf($product['data']) > 0)
                                @foreach ($product['data'] as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endif