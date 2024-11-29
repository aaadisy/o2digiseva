<link href="https://fonts.googleapis.com/css?family=Satisfy" rel="stylesheet">
<style>
    .cert {
  border: 15px solid #94c120;
  border-right: 15px solid #50df39;
  border-left: 15px solid #50df39;
  width: 700px;
  font-family: arial;
  color: #383737;
}

.crt_title {
  margin-top: 30px;
  font-family: "Satisfy", cursive;
  font-size: 40px;
  letter-spacing: 1px;
  color: #ff4486;
}
.crt_logo img {
  width: 200px;
  height: auto;
  margin: auto;
  padding: 30px;
}
.colorGreen {
  color: #27ae60;
}
.crt_user {
  display: inline-block;
  width: 80%;
  padding: 5px 25px;
  margin-bottom: 0px;
  padding-bottom: 0px;
  font-family: "Satisfy", cursive;
  font-size: 40px;
  border-bottom: 1px dashed #cecece;
}

.afterName {
  font-weight: 100;
  color: #383737;
}
.colorGrey {
  color: grey;
}
.certSign {
  width: 100px;
}

@media (max-width: 700px) {
  .cert {
    width: 100%;
  }
}

 .page {
        width: 210mm;
        min-height: 297mm;
        padding: 20mm;
        margin: 10mm auto;
        border: 1px #D3D3D3 solid;
        border-radius: 5px;
        background: white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }
    .subpage {
        padding: 1cm;
        border: 5px red solid;
        height: 257mm;
        outline: 2cm #FFEAEA solid;
    }
    
    @page {
        size: A4;
        margin: 0;
    }
    @media print {
        html, body {
            width: 210mm;
            height: 297mm;        
        }
        .page {
            margin: 0;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            box-shadow: initial;
            background: initial;
            page-break-after: always;
        }
    }
    
    @media print {
    #printbtn {
        display :  none;
    }
}
</style>
<table class="cert" class="page">
  <tr>
    <td align="center" class="crt_logo" colspan=2>
      <img src="{{asset('')}}public/logos/{{Auth::user()->company->logo}}" alt="logo">

    </td>
  </tr>
  <tr>
    <td align="center" colspan=2>
      <h1 class="crt_title">Trained & Certified
        <h2>This is to certify that</h2>
        <h1 class="colorGreen crt_user">{{ ucwords(Auth::user()->name) }}</h1>
        <h3 class="afterName">is the authorised Bussiness Correspondent Agent of
        </h3>
        <h3>DIGISEVA </h3>
        <h4 style="line-height: 0;">India's Smart Payment System</h4>
    </td>
  </tr>
  
  <tr>
    <td align="center">
        @php
        $bbc = 'DIGI'.str_pad(Auth::user()->id, 4, '0', STR_PAD_LEFT);
        @endphp
      <p style="margin-top: 60px;margin-right: 255px;"><b>BC Agent ID</b> : <d style="border-bottom: 1px dashed #cecece;"><b>&nbsp;&nbsp;&nbsp;{{ $bbc }}&nbsp;&nbsp;&nbsp;</b></d></p>
      <p style="line-height: 0;margin-right: 255px;"><b>Mobile Number</b> : <d style="border-bottom: 1px dashed #cecece;"><b>&nbsp;&nbsp;&nbsp;{{ ucwords(Auth::user()->mobile) }}&nbsp;&nbsp;&nbsp;</b></d></p>
    
    </td>
    <td align="center">
      <img style="border-bottom: 1px dashed #cecece;" src="https://camo.githubusercontent.com/805e05b94844e39d7edd518f492c8599c71835b3/687474703a2f2f692e696d6775722e636f6d2f646e5873344e442e706e67" class="certSign" alt="sign">
      <h4 style="line-height: 0;">DIGISEVA </h4>
      <h4 style="line-height: 0;">Vice Precident</h4>
      <h4 style="line-height: 0;">Sales & Distribution</h4>
    </td>
  </tr>
  
</table>

<button id ="printbtn" onclick="window.print()">Print Certificate</button>
