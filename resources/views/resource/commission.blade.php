@extends('layouts.app')
@section('title', 'Scheme Manager')
@section('pagetitle',  'Scheme Manager')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h6 class="panel-title">My Commission</h6>
                </div>

                <div class="panel-body no-padding">
                    <div class="tabbable">
                        <ul class="nav nav-tabs nav-tabs-bottom nav-justified no-margin">
                           
                            
                            @foreach ($commission as $key => $value)
                                <li class="{{($key == 'mobile') ? 'active' : ''}}"><a href="#{{$key}}" data-toggle="tab" class="legitRipple" aria-expanded="true">{{ucfirst($key)}}</a></li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach ($commission as $key => $value)
                                <div class="tab-pane {{($key == 'mobile') ? 'active' : ''}}" id="{{$key}}">
                                    <table class="table table-bordered" cellspacing="0" style="width:100%">
                                        <thead>
                                                <th>Provider</th>
                                                <th>Type</th>
                                                @if(Myhelper::hasRole('admin'))
                                                    <th>Admin</th>
                                                @endif
                                                @if(Myhelper::hasRole('whitelable'))
                                                    <th>Channel Partner</th>
                                                @endif
                                                @if(Myhelper::hasRole('md'))
                                                <th>Md</th>
                                                @endif
                                                @if(Myhelper::hasRole('distributor'))
                                                <th>Distributor</th>
                                                @endif
                                                @if(Myhelper::hasRole('retailer'))
                                                <th>Retailer</th>
                                                @endif
                                        </thead>

                                        <tbody>
                                            @foreach ($value as $comm)
                                                <tr>
                                                    <td>{{ucfirst($comm->provider->name)}}</td>
                                                    <td>{{ucfirst($comm->type)}}</td>
                                                    @if(Myhelper::hasRole('admin'))
                                                    <td>{{ucfirst($comm->admin)}}</td>
                                                    @endif
                                                    @if(Myhelper::hasRole('whitelable'))
                                                    <td>{{ucfirst($comm->whitelable)}}</td>
                                                    @endif
                                                    @if(Myhelper::hasRole('md'))
                                                    <td>{{ucfirst($comm->md)}}</td>
                                                    @endif
                                                    @if(Myhelper::hasRole('distributor'))
                                                    <td>{{ucfirst($comm->distributor)}}</td>
                                                    @endif
                                                    @if(Myhelper::hasRole('retailer'))
                                                    <td>{{ucfirst($comm->retailer)}}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection