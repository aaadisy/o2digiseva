@if(\Myhelper::handleFingAeps())
<!-- Main sidebar -->
<div class="sidebar sidebar-main sidebar-default sidebar-fixed">
    <div class="sidebar-content">
        <div class="sidebar-user-material">
            <div class="category-content">
                <div class="sidebar-user-material-content">
                    <span style="font-weight: 500; margin-top: 10px">Welcome</span>
                    <h5 class="no-margin-bottom" style="font-weight: 500; color: white; margin-top: 0px">
                        {{ explode(' ',ucwords(Auth::user()->name))[0] }} (Id - {{Auth::id()}})
                    </h5>
                    <span style="font-weight: 500">Member Type - {{Auth::user()->role->name}}</span>
                </div>
                                            
                <div class="sidebar-user-material-menu">
                    <a href="#user-nav" data-toggle="collapse"><span>My Account</span> <i class="caret"></i></a>
                </div>
            </div>
            
            <div class="navigation-wrapper collapse" id="user-nav">
                <ul class="navigation">
                    <li><a href="{{route('profile')}}"><i class="icon-user-plus"></i> <span>My profile</span></a></li>
                    @if (Myhelper::hasNotRole('admin') && Myhelper::can('view_commission'))
                        <li><a href="{{route('resource', ['type' => 'commission'])}}"><i class="icon-coins"></i> <span>My Commission</span></a></li>
                        <li><a target="_blank" href="{{route('download_certificate')}}"><i class="icon-certificate"></i> <span>My Certificate</span></a></li>
                    @endif
                    <li><a href="{{route('logout')}}"><i class="icon-switch2"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>

        <!-- Main navigation -->
        <div class="sidebar-category sidebar-category-visible">
            <div class="category-content no-padding">
                <ul class="navigation navigation-main navigation-accordion">
                    <li class="navigation-header" style="border: none;"><span>Navigation</span> <i class="icon-menu" title="" data-original-title="Main pages"></i></li>
                    <li><a href="{{route('home')}}"><i class="icon-home4"></i> <span>Dashboard</span></a></li><li><a href="{{route('statistics')}}"><i class="icon-home4"></i> <span>Statistics</span></a></li>

                    @if (Myhelper::hasNotRole('admin'))
                        @if (Myhelper::can(['recharge_service']))
                        <li>
                            <a href="javascript:void(0)"><i class="fa fa-bolt" style="padding:0px 4px"></i> <span>Utility Recharge</span></a>
                            <ul>
                                @if (Myhelper::can('recharge_service'))
                                    <li><a href="{{route('recharge' , ['type' => 'mobile'])}}">Mobile</a></li>
                                    <li><a href="{{route('recharge' , ['type' => 'dth'])}}">Dth</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if (Myhelper::can(['billpayment_service']))
                        <li>
                            <a href="javascript:void(0)"><i class="fa fa-bolt" style="padding:0px 4px"></i> <span>Bill Payment</span></a>
                            <ul>
                                @if (Myhelper::can('billpayment_service'))
                                   <li><a href="{{route('bill' , ['type' => 'electricity'])}}">Electricity</a></li>
                                @endif
                                @if (Myhelper::can('billpayment_service'))
                                    <li><a href="{{route('bill' , ['type' => 'insurance'])}}">Insurance</a></li>
                                @endif
                                 
                            </ul>
                        </li>
                        @endif

                        @if (Myhelper::can(['utipancard_service']))
                        <li>
                            <a href="javascript:void(0)"><i class="fa fa-credit-card"></i> <span>Pancard</span></a>
                            <ul>
                                @if (Myhelper::can('utipancard_service'))
                                    <li><a href="{{route('pancard' , ['type' => 'uti'])}}">Uti</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if (Myhelper::can(['currentaccount_service']))
                     <!--   <li>
                            <a href="javascript:void(0)"><i class="fa fa-credit-card"></i> <span>Current Account</span></a>
                            <ul>
                                @if (Myhelper::can('currentaccount_service'))
                                   
                                @endif
                            </ul>
                        </li>  -->
                        @endif

                        @if (Myhelper::can(['dmt1_service', 'aeps_service']))
                            <li>
                                <a href="javascript:void(0)"><i class="fa fa-inr" style="padding:0px 4px"></i> <span>Banking Service</span></a>
                                <ul>
                                    @if (Myhelper::can('dmt1_service'))
                                        <li><a href="{{route('dmt1')}}">M-Money Transfer</a></li>
                                    @endif

                                    @if (Myhelper::can('aeps_service'))
                                        <li><a href="{{route('ifaeps' , ['type' => 'aeps'])}}">Aeps</a></li>
                                        <li><a href="{{route('ifaeps' , ['type' => 'ap2fa'])}}">AadharPay 2FA</a></li>
                                        <li><a href="{{route('ifaeps' , ['type' => 'cw2fa'])}}">AEPS 2FA</a></li>
                                  
                                    @endif
                                    @if (Myhelper::can('billpayment_service'))
                                    <li><a href="{{route('bill' , ['type' => 'cashdeposit'])}}">Cash Deposit / Express Payout</a></li>
                                    @endif
                                    @if (Myhelper::can('billpayment_service'))
                                    <li><a href="{{route('bill' , ['type' => 'wt'])}}">Wallet / Profit Move To Bank</a></li>
                                    @endif

                                    
                                </ul>
                            </li>
                        @endif
                        @if (Myhelper::can('recharge_service'))
                        <li>
                                <a href="javascript:void(0)"><i class="fa fa-inr" style="padding:0px 4px"></i> <span>Invoice</span></a>
                                <ul>
                        @if (Myhelper::can('aeps_service'))
                                        <li><a href="{{route('aeps_invoice')}}">Aeps Invoice</a></li>
                        @endif
                        <li><a href="{{route('wallet_invoice')}}">Main Invoice</a></li>
                        </ul>
                        </li>
                        @endif
                    @endif

                    @if (Myhelper::hasNotRole('retailer'))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-wrench"></i> <span>Resources</span></a>
                        <ul>
                            @if (Myhelper::hasRole('admin'))
                                <li><a href="{{route('resource', ['type' => 'scheme'])}}">Scheme Manager</a></li>
                            @endif

                            @if (Myhelper::can('company_manager'))
                                <li><a href="{{route('resource', ['type' => 'company'])}}">Company Manager</a></li>
                            @endif

                            @if (Myhelper::can('change_company_profile'))
                                <li><a href="{{route('resource', ['type' => 'companyprofile'])}}">Company Profile</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if (Myhelper::can(['view_whitelable', 'view_md', 'view_distributor', 'view_retailer', 'view_apiuser', 'view_other', 'view_kycpending', 'view_kycsubmitted', 'view_kycrejected']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-user"></i> <span>Member</span></a>
                        <ul>
                            @if (Myhelper::can(['active_member']))
                            <li><a href="{{route('active')}}">Active Members</a></li>
                            <li><a href="{{route('notloggedin')}}">Not Loggedin Member</a></li>
                            @endif
                            @if (Myhelper::can(['view_whitelable']))
                            <li><a href="{{route('member', ['type' => 'whitelable'])}}">Channel Partner</a></li>
                            @endif
                            @if (Myhelper::can(['view_md']))
                            <li><a href="{{route('member', ['type' => 'md'])}}">Master Distributor</a></li>
                            @endif
                            @if (Myhelper::can(['view_distributor']))
                            <li><a href="{{route('member', ['type' => 'distributor'])}}">Distributor</a></li>
                            @endif
                            @if (Myhelper::can(['view_retailer']))
                            <li><a href="{{route('member', ['type' => 'retailer'])}}">Retailer</a></li>
                            @endif
                            
                        </ul>
                    </li>
                    @endif

                    @if (Myhelper::can(['fund_transfer', 'fund_return', 'fund_request_view', 'fund_report', 'fund_request']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-wallet"></i> <span>Fund</span>
                        <span class="label bg-danger fundCount {{Myhelper::hasRole('admin')?'' : 'hide'}}" >0</span></a>
                        <ul>
                            @if (Myhelper::can(['fund_transfer', 'fund_return']))
                            <li><a href="{{route('fund', ['type' => 'tr'])}}">Transfer/Return</a></li>
                            @endif
                            @if (Myhelper::can(['setup_bank']))
                            <li><a href="{{route('fund', ['type' => 'requestview'])}}">Request 
                                <span class="label bg-blue fundCount {{Myhelper::hasRole('admin')?'' : 'hide'}}">0</span></a>
                            </li>
                            @endif
                            @if (Myhelper::hasNotRole('admin') && Myhelper::can('fund_request'))
                            <li><a href="{{route('fund', ['type' => 'request'])}}">Load Wallet</a></li>
                            @endif
                            @if (Myhelper::can(['fund_report']))
                            <li><a href="{{route('fund', ['type' => 'requestviewall'])}}">Request Report</a></li>
                            <li><a href="{{route('fund', ['type' => 'statement'])}}">All Fund Report</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if (Myhelper::can(['aeps_fund_request', 'aeps_fund_view', 'aeps_fund_report']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-wallet"></i> <span>Aeps Fund</span>
                        <span class="label bg-danger aepsfundCount {{Myhelper::hasRole('admin')?'' : 'hide'}}">0</span></a>
                        <ul>
                            @if (Myhelper::can(['aeps_fund_request']))
                            <li><a href="{{route('fund', ['type' => 'aeps'])}}">Move To Bank</a></li>
                            @endif

                            @if (Myhelper::can(['aeps_fund_view']))
                            <li><a href="{{route('fund', ['type' => 'aepsrequest'])}}">Pending Manual Request 
                                <span class="label bg-blue aepsfundCount {{Myhelper::hasRole('admin')?'' : 'hide'}}">0</span></a>
                            </li>
                            <li><a href="{{route('fund', ['type' => 'payoutrequest'])}}">Pending Payout Request 
                                <span class="label bg-blue aepspayoutfundCount {{Myhelper::hasRole('admin')?'' : 'hide'}}">0</span></a>
                            </li>
                            @endif
                             @if (Myhelper::can('billpayment_statement'))
                                <li><a href="{{route('statement', ['type' => 'cashdepositbillpay'])}}">Cash Deposit Statement</a></li>
                            @endif
                             @if (Myhelper::can('billpayment_statement'))
                                <li><a href="{{route('statement', ['type' => 'wallettransferbillpay'])}}">Wallet / Profit Move To Bank Statement</a></li>
                            @endif

                            @if (Myhelper::can(['aeps_fund_report']))
                            <li><a href="{{route('fund', ['type' => 'aepsrequestall'])}}">All Request Report</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if (Myhelper::can(['utiid_statement', 'aepsid_statement']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-user"></i> <span>Agent List</span></a>
                        <ul>
                            @if (Myhelper::can('aepsid_statement'))
                                <li><a href="{{route('statement', ['type' => 'icicikyc'])}}">Aeps </a></li>
                            @endif

                            @if (Myhelper::can('utiid_statement'))
                                <li><a href="{{route('statement', ['type' => 'utiid'])}}">Uti</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    @if (Myhelper::can(['account_statement', 'utiid_statement', 'utipancard_statement', 'recharge_statement', 'billpayment_statement']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-history"></i> <span>Transaction History</span></a>
                        <ul>
                            @if (Myhelper::can('aeps_statement'))
                                <li><a href="{{route('statement', ['type' => 'aeps'])}}">Aeps Statement</a></li>
                            @endif
                            @if (Myhelper::can('aeps_statement'))
                                <li><a href="{{route('statement', ['type' => 'ministatement'])}}">Mini Statement</a></li>
                            @endif
                            @if (Myhelper::can('aeps_statement'))
                                <li><a href="{{route('statement', ['type' => 'matm'])}}">Matm Statement</a></li>
                            @endif
                            @if (Myhelper::can('aeps_statement'))
                                <li><a href="{{route('statement', ['type' => 'aadharpay'])}}">Aadhay Pay Statement</a></li>
                            @endif
                            
                            @if (Myhelper::can('billpayment_statement'))
                                <li><a href="{{route('statement', ['type' => 'billpay'])}}">Electricity Statement</a></li>
                            @endif
                            @if (Myhelper::can('billpayment_statement'))
                                <li><a href="{{route('statement', ['type' => 'insurancebillpay'])}}">Insurance Bill Statement</a></li>
                            @endif
                            
                           

                            @if (Myhelper::can('money_statement'))
                                <li><a href="{{route('statement', ['type' => 'money'])}}">Money Transfer Statement</a></li>
                            @endif

                            @if (Myhelper::can('recharge_statement'))
                                <li><a href="{{route('statement', ['type' => 'recharge'])}}">Recharge Statement</a></li>
                            @endif

                            @if (Myhelper::can('utipancard_statement'))
                                <li><a href="{{route('statement', ['type' => 'utipancard'])}}">Uti Pancard Statement</a></li>
                            @endif
                        </ul>
                    </li>

                    @if (Myhelper::can(['account_statement', 'awallet_statement']))
                        <li>
                            <a href="javascript:void(0)"><i class="icon-menu6"></i> <span>Account Statement</span></a>
                            <ul>
                                @if (Myhelper::can('account_statement'))
                                    <li><a href="{{route('statement', ['type' => 'account'])}}">Main Wallet</a></li>
                                @endif
                                @if (Myhelper::can('awallet_statement'))
                                <li><a href="{{route('statement', ['type' => 'awallet'])}}">Aeps Wallet</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @endif
    <li><a href="{{route('statement', ['type' => 'apilog'])}}"><i class="icon-history"></i> <span>3Way Logs</span></a></li>
                        @if (Myhelper::can(['check_complaint']))
                        <li><a href="{{route('complaint')}}"><i class="icon-cog"></i> <span>Complaints</span></a></li>
                        <li><a href="{{route('aepscomplaint')}}"><i class="icon-cog"></i> <span>AEPS Complaints</span></a></li>
                        @endif
                        @if (Myhelper::can(['check_dispute']))
                         <li><a href="{{route('dispute')}}"><i class="icon-cog"></i> <span>Disputes</span></a></li>
                         <li><a href="{{route('aepsdispute')}}"><i class="icon-cog"></i> <span>AEPS Disputes</span></a></li>
                         @endif
                    @if (Myhelper::can(['kyc_manager']))
                            <li><a href="{{route('member', ['type' => 'kycpending'])}}"><i class="icon-users"></i> <span>KYC Manager</span></a></li>
                    @endif
                    @if (Myhelper::can(['manage_employee']))
                            <li><a href="{{route('member', ['type' => 'other'])}}"><i class="icon-users"></i> <span>Employee</span></a></li>
                            <li>
                                <a href="{{route('statsByDate')}}"><i class="icon-money"></i> <span>Stats By Date </span></a></li>
                    @endif
                    @if (Myhelper::can(['setup_bank', 'api_manager', 'setup_operator']))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-cog3"></i> <span>Setup Tools</span></a>
                        <ul>
                            @if (Myhelper::can('api_manager'))
                            <li><a href="{{route('deletedataview')}}">Delete Log & Data</a></li>
                            <li><a href="{{route('setup', ['type' => 'api'])}}">Api Manager</a></li>
                            @endif
                             
                            @if (Myhelper::can('setup_bank'))
                            <li><a href="{{route('setup', ['type' => 'bank'])}}">Bank Account</a></li>
                            @endif
                            @if (Myhelper::can('complaint_subject'))
                            <li><a href="{{route('setup', ['type' => 'complaintsub'])}}">Complaint Subject</a></li>
                            @endif
                            @if (Myhelper::can('setup_operator'))
                            <li><a href="{{route('setup', ['type' => 'operator'])}}">Operator Manager</a></li>
                            @endif
                            @if (Myhelper::hasRole('admin'))
                            <li><a href="{{route('setup', ['type' => 'portalsetting'])}}">Portal Setting</a></li>
                            @endif
                            @if (Myhelper::hasRole('admin'))
                            <li><a href="{{route('banners')}}">Banners</a></li>
                            @endif
                            @if (Myhelper::hasRole('admin'))
                            <li><a href="{{route('notification')}}">Notification</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    <li><a href="{{route('video')}}"><i class="icon-lock"></i>Video Tutorials</a></li>
                    
                    @if (Myhelper::can('user_transaction'))
                    <li><a href="{{route('user_transaction')}}"><i class="icon-lock"></i>User Transcation</a></li>
                    @endif
                     @if (Myhelper::can('sms_template'))
                    <li><a href="{{route('whatsapptemplate')}}"><i class="icon-lock"></i>SMS Template</a></li>
                    @endif
                     @if (Myhelper::can('bulk_message'))
                      <li><a href="{{route('bulkmsg')}}"><i class="icon-lock"></i>Bulk SMS/ Email</a></li>
                      @endif
                    
                    @if (Myhelper::hasRole('admin'))
                  <li><a href="{{route('statement', ['type' => 'commission'])}}"><i class="icon-lock"></i>Commission Report</a></li>
                  <li><a href="{{route('statement', ['type' => 'aepscommission'])}}"><i class="icon-lock"></i>AEPS Commission Report</a></li>
                    @endif

                    <li>
                        <a href="javascript:void(0)"><i class="icon-cog2"></i> <span>Account Settings</span></a>
                        <ul>
                            <li><a href="{{route('profile')}}">Profile Setting</a></li>
                        </ul>
                    </li>

                    @if (Myhelper::hasRole('admin'))
                    <li>
                        <a href="javascript:void(0)"><i class="icon-lock"></i> <span>Roles & Permissions</span></a>
                        <ul>
                            <li><a href="{{route('tools' , ['type' => 'roles'])}}">Roles</a></li>
                            <li><a href="{{route('tools' , ['type' => 'permissions'])}}">Permission</a></li>
                        </ul>
                    </li>
                    @endif

                </ul>
            </div>
        </div>
        <!-- /main navigation -->
    </div>
</div>
@endif
<!-- /main sidebar -->

<div id="profilePic" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Profile Upload</h6>
            </div>
            <div class="modal-body">
                <form class="dropzone" id="profileupload" action="{{route('profileUpdate')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{Auth::id()}}">
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
