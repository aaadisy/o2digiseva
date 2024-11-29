<!-- Main navbar -->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-header">
        @if (Auth::user()->company->logo)
            <a class="navbar-brand no-padding" href="{{route('home')}}">
                <img src="{{asset('')}}public/logos/{{Auth::user()->company->logo}}" class=" img-responsive" alt="" style="width: 260px;height: 56px;">
            </a>
        @else
            <a class="navbar-brand" href="{{route('home')}}" style="padding: 20px">
                <span class="companyname" style="color: white">{{Auth::user()->company->companyname}}</span>
            </a>
        @endif

        <ul class="nav navbar-nav visible-xs-block">
            <li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
            <li><a class="sidebar-mobile-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
        </ul>
    </div>

    <div class="navbar-collapse collapse" id="navbar-mobile">
        <ul class="nav navbar-nav">
            <li><a class="sidebar-control sidebar-main-toggle hidden-xs"><i class="icon-paragraph-justify3"></i></a></li>
            @if (Myhelper::hasRole('admin'))
            <li><a href="javascript:void(0)" style="padding: 13px"><button type="button" class="btn bg-slate btn-labeled btn-xs legitRipple" data-toggle="modal" data-target="#walletLoadModal"><b><i class="icon-wallet"></i></b> Load Wallet</button></a></li>
            @endif
        </ul>

        <div class="navbar-right">
            <!-- <p class="navbar-text"><i class="icon-time"></i>Auto Logout : <span id="timeauto" class="autologout"></span> Min.</p> -->
            <script>
//                 function startTimer(duration, display) {
//     var timer = duration, minutes, seconds;
//     setInterval(function () {
//         minutes = parseInt(timer / 60, 10);
//         seconds = parseInt(timer % 60, 10);

//         minutes = minutes < 10 ? "0" + minutes : minutes;
//         seconds = seconds < 10 ? "0" + seconds : seconds;

//         display.textContent = minutes + ":" + seconds;

//         if (--timer < 0) {
//            window.location.reload();

//         }
//     }, 1000);
// }

// setTimeout(function(){
//                 sessionOut();
//             }, "880000"); 
            
// window.onload = function () {
//     var fiveMinutes = 60 * 15,
//         display = document.querySelector('#timeauto');
//     startTimer(fiveMinutes, display);
    
// };

                    </script>
                    @if (Myhelper::hasRole('admin'))
            <p class="navbar-text"><i class="icon-wallet"></i> Wallet : <span class="">{{Myhelper::allmemberwallet()}}</span> /-</p>
            <p class="navbar-text"><i class="icon-wallet"></i> Aeps : <span class="">{{Myhelper::allmemberaepswallet()}}</span> /-</p>
            
           
            @else
            <p class="navbar-text"><i class="icon-wallet"></i> Wallet : <span class="mainwallet">{{Auth::user()->mainwallet}}</span> /-</p>
            <p class="navbar-text"><i class="icon-wallet"></i> Aeps : <span class="aepsbalance">{{Auth::user()->aepsbalance}}</span> /-</p>
            @endif
            <a class="navbar-text" href="{{route('logout')}}"><i class="icon-switch2"></i> <span>Logout</span></a>

            <style>
               

a:visited{
  color: black;
}

.box::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	background-color: #F5F5F5;
  border-radius: 5px
}

.box::-webkit-scrollbar
{
	width: 10px;
	background-color: #F5F5F5;
  border-radius: 5px
}

.box::-webkit-scrollbar-thumb
{
	background-color: black;
	border: 2px solid black;
  border-radius: 5px
}

header{
  -moz-box-shadow: 10px 10px 23px 0px rgba(0,0,0,0.1);
  box-shadow: 10px 10px 23px 0px rgba(0,0,0,0.1);
  height: 110px;
  vertical-align: middle;
}

h1{
 float: left;
  padding: 10px 30px
}



.icons{
  display: inline;
  float: right
}

.notification{
  padding-top: 5px;
  position: relative;
  display: inline-block;
}

.number{
  width:  22px;
  background-color: #d63031;
  border-radius: 20px;
  color: white;
  text-align: center;
  position: absolute;
  top: 10px;
  left: 60px;
  padding: 3px;
  border-style: solid;
  border-width: 2px;
}

.number:empty {
   display: none;
}

.notBtn{
  transition: 0.5s;
  cursor: pointer
}

.fas{
  font-size: 25pt;
  padding-bottom: 10px;
  color: black;
  margin-right: 40px;
  margin-left: 40px;
}

.box{
  width: 400px;
  height: 0px;
  border-radius: 10px;
  transition: 0.5s;
  position: absolute;
  overflow-y: scroll;
  padding: 0px;
  left: -300px;
  margin-top: 5px;
  background-color: #F4F4F4;
  -webkit-box-shadow: 10px 10px 23px 0px rgba(0,0,0,0.2);
  -moz-box-shadow: 10px 10px 23px 0px rgba(0,0,0,0.1);
  box-shadow: 10px 10px 23px 0px rgba(0,0,0,0.1);
  cursor: context-menu;
}

.fas:hover {
  color: #d63031;
}

.notBtn:hover > .box{
  height: 60vh
}

.content{
  padding: 20px;
  color: black;
  vertical-align: middle;
  text-align: left;
}

.gry{
  background-color: #F4F4F4;
}

.top{
  color: black;
  padding: 10px
}

.display{
  position: relative;
}

.cont{
  position: absolute;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: #F4F4F4;
}

.cont:empty{
  display: none;
}

.stick{
  text-align: center;  
  display: block;
  font-size: 50pt;
  padding-top: 70px;
  padding-left: 80px
}

.stick:hover{
  color: black;
}

.cent{
  text-align: center;
  display: block;
}

.sec{
  padding: 25px 10px;
  background-color: #F4F4F4;
  transition: 0.5s;
}

.profCont{
}

.profile{
}

.txt{
  vertical-align: top;
  font-size: 1.25rem;
}

.sub{
  font-size: 1rem;
  color: grey;
}

.new{
  border-style: none none solid none;
  border-color: red;
}

.sec:hover{
  background-color: #BFBFBF;
}




            </style>
            <div class = "notification">
              <a href = "#">
              <div class = "notBtn" href = "#">
                <!--Number supports double digets and automaticly hides itself when there is nothing between divs -->
                <div id="not_count" class = "number">0</div>
                <i class="fas fa-bell"></i>
                  <div class = "box">
                    <div class = "display">
                      <div class = "nothing"> 
                        <i class="fas fa-child stick"></i> 
                        <div class = "cent">Looks Like your all caught up!</div>
                      </div>
                      <div class = "cont"><!-- Fold this div and try deleting evrything inbetween -->
                     </div>
                    </div>
                 </div>
              </div>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- /main navbar -->