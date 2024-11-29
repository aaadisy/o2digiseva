@extends('layouts.app')
@section('title', "Aeps Invoice")
@section('pagetitle',  "Aeps Invoice")

@php
    $table = "yes";
    $export = "aeps";

    
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Aeps Invoice</h4>
                </div>
                
             <div class="col-sm-12">
                 <form class="form control" method="post" action="{{ route('aeps_invoice') }}" >
                     {{ csrf_field() }}
                    <div class="form-group col-md-2 m-b-10">
                        <input type="date" name="from_date" class="form-control mydate" placeholder="From Date">
                    </div>
                    <div class="form-group col-md-2 m-b-10">
                        <input type="date" name="to_date" class="form-control mydate" placeholder="To Date">
                    </div>
                    <div  class="form-group col-md-2 m-b-10">
                        <button type="submit" class="btn bg-slate btn-xs btn-labeled legitRipple btn-lg" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i></b> Search</button>
                    </div>
                    
                    </form>               
            </div>


</br>
</br></br>
</br>
@if($data['datashow'] == 'view')
<div style="background:#fff;" id="resutprint">
<style>
			.invoice-box {
max-width: 800px;
margin: auto;
padding: 30px;
border: 1px solid #eee;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
font-size: 16px;
line-height: 24px;
font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
color: #555;
			}
			.invoice-box table {
width: 100%;
line-height: inherit;
text-align: left;
			}
			.invoice-box table td {
padding: 5px;
vertical-align: top;
			}
			.invoice-box table tr td:nth-child(2) {
text-align: right;
			}
			.invoice-box table tr.top table td {
padding-bottom: 20px;
			}
			.invoice-box table tr.top table td.title {
font-size: 45px;
line-height: 45px;
color: #333;
			}
			.invoice-box table tr.information table td {
padding-bottom: 40px;
			}
			.invoice-box table tr.heading td {
background: #eee;
border-bottom: 1px solid #ddd;
font-weight: bold;
			}
			.invoice-box table tr.details td {
padding-bottom: 20px;
			}
			.invoice-box table tr.item td {
border-bottom: 1px solid #eee;
			}
			.invoice-box table tr.item.last td {
border-bottom: none;
			}
			.invoice-box table tr.total td:nth-child(2) {
border-top: 2px solid #eee;
font-weight: bold;
			}
@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
width: 100%;
display: block;
text-align: center;
				}
				.invoice-box table tr.information table td {
width: 100%;
display: block;
text-align: center;
				}
			}
/** RTL **/
			.invoice-box.rtl {
direction: rtl;
font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}
			.invoice-box.rtl table {
text-align: right;
			}
			.invoice-box.rtl table tr td:nth-child(2) {
text-align: left;
			}
</style>

<div  class="invoice-box">
<table cellpadding="0" cellspacing="0">
<tr class="top">
<td colspan="2">
<table>
<tr>
<td class="title">
   
<img src="{{asset('')}}public/logos/{{Auth::user()->company->logo}}" style="width: 20%; max-width: 300px" />
</td>
<td>
									
									From: {{ date('d M Y', strtotime($data['from_date'])) }}<br />
									To: {{ date('d M Y', strtotime($data['to_date'])) }}<br />
									
</td>
</tr>
</table>
</td>
</tr>
<tr class="information">
<td colspan="2">
<table>
<tr>
<td>
									{{Auth::user()->company->companyname}}<br />
									
</td>
<td>
									{{Auth::user()->name}}<br />
</td>
</tr>
</table>
</td>
</tr>
<tr class="heading">
<td>Total Transaction</td>
<td>₹ {{$data['amount']}}</td>
</tr>

<tr class="item">
<td>Total Commision</td>
<td>₹ {{$data['commission']}}</td>
</tr>
<tr class="item">
<td>Total TDS</td>
<td>₹ {{$data['tds']}}</td>
</tr>
<tr class="item">
<td>Total GST</td>
<td>₹ {{$data['gst']}}</td>
</tr>

<tr class="total">
<td></td>
<td>Grand Total: ₹ {{$data['amount']+$data['commission']}}</td>
</tr>
</table>
</div>

   

           
</div>
<a href="#" id="download"><button style="background:aqua; cursor:pointer">Get Invoice</button> </a>
               <script>
    $("#download").on('click', function(){
        window.scrollTo(0,0); 
        html2canvas($("#resutprint"), {
            onrendered: function (canvas) {
                var url = canvas.toDataURL();

                var triggerDownload = $("<a>").attr("href", url).attr("download", "aeps_invoice.jpeg").appendTo("body");
                triggerDownload[0].click();
                triggerDownload.remove();
            }
        });
    })
</script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
@endif
 </div>
        </div>
    </div>
</div>
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/fetch')}}/aepsstatement/0";
        
    });

</script>
@endpush