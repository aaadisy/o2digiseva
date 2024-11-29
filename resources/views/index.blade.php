
<!DOCTYPE >
<html class="no-js" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{$mydata['company']->companyname}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta name="description" /><meta name="keywords" /><meta name="author" />
<meta name="MobileOptimized" content="320" />

<link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900" rel="stylesheet" />

    <!-- favicon links -->
    <link rel="shortcut icon" type="image/ico" href="assets/images/Favicon.png" />

    <!-- stylesheet start -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/animate.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/swiper.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/fonts.css" />
    <link rel="stylesheet" type="text/css" href="assets/fonts/themfiy/themify-icons.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/responsive.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/slick.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/base.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/settings.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/preloader.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
</head>
<body>
  <div class="preloader">
        <div class="preloader-body">
                <img src="assets/images/rotate.gif" alt="" width="75" height="auto">
        </div>
    </div> 

    <!-- Header Banner start -->
    <div class="rural_top_header">
        <div class="rural_header">
            <div class="header-Middle-bar">
                <div class="container">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-10">
                            <div class="header-informations hidden-xs">
                                <div class="pull-right upper-right clearfix">
                                    <div class="upper-column info-box">
                                        <div class="icon-box"><span class="ti-email"></span></div>
                                        <ul>
                                            <li><a href="#">{{$mydata['supportemail']}}</a></li>
                                        </ul>
                                    </div>
                                    <div class="upper-column info-box">
                                        <div class="icon-box"><span class="ti-mobile"></span></div>
                                        <ul>
                                            <li><a href="#">{{$mydata['supportnumber']}}</a></li>
                                        </ul>
                                    </div>
                                    <div class="upper-column info-box">
                                        
                                        <ul>
                                            <li>
                                                
                                                <form action="{{ route('websitecallback') }}" method="post" class="form-control">
                                                       {{ csrf_field() }}
                                                     <input type="text" placeholder="Your number" name="number" required >
                                                     <input style="color:black;" type="submit" value="Enquiry Now">
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rural_menu_header">
                <div class="container">
                    <div class="row">
                        <div class="col-md-3 col-7">
                            <div class="rural_logo"> <a href="Default.aspx"> <img src="{{asset('public/logos')}}/{{$mydata['company']->logo}}" alt="Paydigi Logo" /></a> </div>
                        </div>
                        <div class="col-md-9 col-5">
                            <div class="rural_menus">
                                <ul>
                                    <li><a href="#">Home</a></li>
                                    <li><a href="#aboutus">About</a></li>
                                    <li><a href="#Services">Services</a></li>
                                    <li><a href="#Contact">Contact Us</a></li>
                                    <li><a href="signin" class="rural_btn">Agent Login</a></li>
                                    <li><a href="#Products">Products</a></li>
                                </ul>
                                <div class="rural_toggle">
                                    <div class="bar1"></div>
                                    <div class="bar2"></div>
                                    <div class="bar3"></div>
                                </div>
                            </div>
                            <div class="navbar-contact-detail toggle-original-elements">
                                <span class="social-link">
                                    <ul>
                                        <li><a href="#"><i class="ti-facebook"></i></a></li>
                                        <li><a href="#"><i class="ti-twitter"></i></a></li>
                                        <li><a href="#"><i class="ti-instagram"></i></a></li>
                                        <li><a href="#"><i class="ti-google"></i></a></li>
                                        <li><a href="#"><i class="ti-pinterest"></i></a></li>
                                        <li><a href="#"><i class="fab fa-google-play"></i></a></li>
                                    </ul>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    <div class="slideshow">
        <div class="tp-banner-container">
            <div class="tp-banner">
                <ul>
                    @foreach(\Myhelper::get_banners() as $banner)
                    <li data-transition="random" data-slotamount="7" data-masterspeed="1500">
                        <img src="{{ url('/public/images') }}/{{ $banner->banner }}" alt="" data-bgfit="cover" data-bgposition="center top" data-bgrepeat="no-repeat" />
                        <div class="slideshow-bg"
                             data-y="310"
                             data-x="center"
                             data-start="0"></div>
                    </li>
                    @endforeach
                    
                </ul>
            </div>
        </div>
        <!-- End tp-banner-container -->
    </div>
        <!-- About Section STARTS -->
     <section id="aboutus" class="aboutus-section">
        <div class="container">
            <div class="shape1"><img src="assets/images/resource/1.png" alt="shape1" /></div>
            <div class="about-title wow fadeInUp   animated" data-wow-duration="2s" style="visibility: visible; animation-duration: 2s; animation-name: fadeInUp;">
                <h2>Welcome to <span>{{$mydata['company']->companyname}}</span></h2>
                <marquee behavior="active" direction="right"> <h4>B2B Software Company In India</h4> </marquee>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="abcontent wow fadeInLeft   animated" data-wow-duration="2s" style="visibility: visible; animation-duration: 2s; animation-name: fadeInLeft;">
                        <p>
                            <span>
                                {{$mydata['company']->companyname}} is a Professional B2b Software Company Based in India. We at {{$mydata['company']->companyname}} provide Recharge (Mobile, Dth, Data Card), Bill Payment System
                                (Electricity, Landline, Mobile Bill Payment), Aadhaar Enabled Payment System (AEPS), Domestic Money Transfer, Nsdl/Uti Pan card, Travel Booking ( Bus,
                                Hotel & Flight ), mPOS Machine And Prepaid Card Services.
                            </span>
                        </p>
                        <p>
                            We offer a complete online recharge business solution, where internet users can recharge their Mobile (postpaid / prepaid), DTH, Data card, Landline,
                            Gas, Electricity etc and will make payment through payment gateway. If recharge goes fail then amount will be credited into customer's wallet. Our
                            online recharge includes cash back and reward points features helpful to attract customers. Apart from that coupon API can also be integrated.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about_video wow fadeInRight   animated" data-wow-duration="2s" style="visibility: visible; animation-duration: 2s; animation-name: fadeInRight;">
                        <p href="#" target="_blank"> <img src="assets/images/resource/home_about.png" alt="" /> </p>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="shape2 rotateme"><img src="assets/images/resource/2.png" alt="shape2" /></div>
        <div class="shape3 rotateme"><img src="assets/images/resource/3.png" alt="shape3" /></div>
        <div class="shape4"><img src="assets/images/resource/4.png" alt="shape4" /></div>
        <div></div>
    </section>

    <!-- About Section end -->
    <div  id="Services" class="rural_about_shape01">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="rural_about_detail">
                        <h5 class="rural_center rural_center">Our Services</h5>
                        <h2 class="text_span">Our Recharge &amp; Bill Payment</h2>
                    </div>
                </div>
            </div>
            <div id="Service-section">
                <div class="row align-items-md-center">
                    <div class="col-xl-3">
                        <h5>What we can offer</h5>
                        <ul class="nav list-category">
                            <li class="list-category-item wow fadeIn rollIn" data-wow-delay="0ms"> <a href="#tab-service1" data-toggle="tab" class="active show">Mobile Recharge</a> </li>
                            <li class="list-category-item wow fadeIn rollIn" data-wow-delay="400ms"> <a href="#tab-service2" data-toggle="tab" class="">Dth Recharge</a> </li>
                            <li class="list-category-item wow fadeIn rollIn" data-wow-delay="800ms"> <a href="#tab-service3" data-toggle="tab" class="">Data Card Recharge</a> </li>
                            <li class="list-category-item wow fadeIn rollIn" data-wow-delay="1200ms"> <a href="#tab-service4" data-toggle="tab" class="">Electricity Bill Payment</a> </li>
                            <li class="list-category-item wow fadeIn rollIn" data-wow-delay="1200ms"> <a href="#tab-service5" data-toggle="tab" class="">Landline Bill Payment</a> </li>
                        </ul>
                    </div>
                    <div class="col-xl-9">
                        <div class="tab-content wow fadeInLeft" style="visibility: visible; animation-name: fadeInLeft;">
                            <div class="tab-pane fade active show" id="tab-service1">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="service-left-heading">Mobile Recharge</div>
                                        <p>
                                            Mobile phones have become an important component of our daily life. It's hard to imagine your life without mobile. Gone are the
                                            days when it was regarded as a necessity for rich. Mobile phones have become a necessity for everyone these days. But a mobile
                                            without talktime is absolutely useless. Catering to this need of mobile users we provide instant and easy online recharge of prepaid
                                            mobile. {{$mydata['company']->companyname}} provides online recharge service in India for the mobile networks like Airtel, BSNL, Idea, Loop (BPL), MTNL,
                                            Reliance Jio, Uninor - Special, Uninor - Top Up, Virgin - CDMA, Virgin - GSM and Vodafone. Our online recharge service is convenient
                                            and fast that facilitates recharge of prepaid mobile at competitive prices through internet. Recharging is simple, convenient, secure
                                            and totally.
                                        </p>
                                    </div>
                                    <div class="col-sm-9 col-md-6 col-lg-6">
                                        <div class="owl-service-style">
                                            <div class="owl-service">
                                                <div class="service_overlay_bg"></div>
                                                <div class="service-stage-outer"> <img src="assets/images/service_img/mobile_recharge.png" alt="" /> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-service2">
                                <div class="row">
                                    <div class="col-sm-11 col-md-6 col-lg-6">
                                        <div class="service-left-heading">Dth Recharge</div>
                                        <p>
                                            {{$mydata['company']->companyname}} makes the recharge of prepaid DTH account quick and comfortable. You do not have to visit the retail outlets
                                            for getting your online DTH account recharged. Online DTH Recharge through {{$mydata['company']->companyname}} facilitates recharging of prepaid DTH
                                            account for operators like Dish TV, Big TV, Sun Direct, Videocon D2H and Tata Sky. {{$mydata['company']->companyname}} in makes for a very convenient way to
                                            recharge all DTH in INDIA. DTH allows consumers to stay directly connected with the broadcaster. {{$mydata['company']->companyname}} is simple and easy offer
                                            its exciting range of DTH recharge services for various DTH Services. You can recharge it at the comfort of your home or office with
                                            few clicks of mouse. All you need to have is a computer with internet connection and online recharge of your prepaid DTH account is
                                            just few clicks away.
                                        </p>
                                    </div>
                                    <div class="col-sm-9 col-md-6 col-lg-6">
                                        <div class="owl-service-style">
                                            <div class="service_overlay_bg"></div>
                                            <div class="owl-service">
                                                <div class="service-stage-outer"> <img src="assets/images/service_img/Dth_recharge.png" alt="" /> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-service3">
                                <div class="row">
                                    <div class="col-sm-11 col-md-6 col-lg-6">
                                        <div class="service-left-heading">Data Card Recharge</div>
                                        <p>
                                            Data card recharging service is another amenity that furthers in the list of the services provided by us. Through our data card
                                            recharge service you can recharge prepaid data card connection easily and instantly for BSNL Data Card, Docomo Photon+, Idea Data
                                            Card, MTS M-Blaze, MTS M-Browse, Reliance Netconnect, Reliance Netconnect+, Tata Photon+ and Tata Photon Whiz. You can now recharge
                                            the prepaid data card using our online recharging facility. So, the customers can now enjoy the benefit of anytime and anywhere
                                            connectivity via Internet wirelessly. {{$mydata['company']->companyname}} is a leading provider of online b2b Datacard recharge offers brings to users an online
                                            platform for Datacrd recharge. We provide online data card recharge plans for different providers including Airtel, Idea, Aircel
                                            , MTS, Vodafone, Reliance and more.
                                        </p>
                                    </div>
                                    <div class="col-sm-9 col-md-6 col-lg-6">
                                        <div class="owl-service-style">
                                            <div class="owl-service">
                                                <div class="service_overlay_bg"></div>
                                                <div class="service-stage-outer"> <img src="assets/images/service_img/datacard_recharge.png" alt="" /> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-service4">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="service-left-heading">Electricity Bill Payment</div>
                                        <p>
                                            Electricity is a commodity that has become an integral part of our lives. In this modern age, almost any technology requires
                                            electricity to function. This has made electricity bill payments a frequent necessity. Paying electricity bills online usually
                                            requires you to physically go to an outlet and stand in long queues to clear the payment. Well not anymore! {{$mydata['company']->companyname}} is your
                                            one-stop destination for all of your bill payment requirements, with no extra charges. It even provides you with exclusive offers
                                            that will save your time and money while providing you an easier way for online bill payments. {{$mydata['company']->companyname}} provides you with a
                                            trusted online wallet that can make your online electricity bill payments as easy as you want them to be. {{$mydata['company']->companyname}} also
                                            includes additional benefits like "SuperCash" that will enable you to save money on each of your online electricity bill
                                            payments.
                                        </p>
                                    </div>
                                    <div class="col-sm-9 col-md-6 col-lg-6">
                                        <div class="owl-service-style">
                                            <div class="owl-service">
                                                <div class="service_overlay_bg"></div>
                                                <div class="service-stage-outer"> <img src="assets/images/service_img/electricity_bill.png" alt="" /> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-service5">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6 col-lg-6">
                                        <div class="service-left-heading">Landline Bill Payment</div>
                                        <p>
                                            Landline bill payment has turned as a convenient task with {{$mydata['company']->companyname}}, now pay landline bills online through our website and
                                            get rid of offline hustles &amp; bustles! No more rushing to the market for landline bill payment, we offer you the easiest online
                                            bill payment service. Now no need to go to the store for submitting bills, online payment is a better solution! No matter where
                                            you are, you can securely make landline bills online payment effortlessly online in few easy clicks. {{$mydata['company']->companyname}} allows it
                                            consumers to pay landline bill online for Airtel, BSNL, MTNL, Reliance-Jio and other operators. It is really hassle-free
                                            &amp; handy for you to make online bill payment through our portal. You can even make broadband bill payment, landline bill payment,
                                            recharge online &amp; more using multiple payment options like Debit/Credit Cards, Net Banking or {{$mydata['company']->companyname}} Wallet as per
                                            your preference.
                                        </p>
                                    </div>
                                    <div class="col-sm-9 col-md-6 col-lg-6">
                                        <div class="owl-service-style">
                                            <div class="owl-service">
                                                <div class="service_overlay_bg"></div>
                                                <div class="service-stage-outer"> <img src="assets/images/service_img/landline_bill.png" alt="" /> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

     <!-- We Provide Quality Services -->
    <section class="booking_form_area wow fadeInUp" data-wow-delay="400ms">
        <div class="ui-decor ui_decor_Lefttop"></div>
        <div class="container">
            <div class="heading-service-title">We Provide Quality Services</div>
            <div class="booking_slider slick">
                <div class="booking_form_info">
                    <div class="tab_img">
                        <div class="b_overlay_bg"></div>
                        <img src="assets/images/service_img/dmt.png" alt="" />
                    </div>
                    <div class="boking_content">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="service-content">
                                    <h2>Domestic Money Transfer</h2>
                                    <p>
                                        {{$mydata['company']->companyname}} Launched Instant Domestic Money Transfer (DMT) Services. {{$mydata['company']->companyname}} DMT brings you the convenience of transferring money from your
                                        place of residence to any Bank account across the country. {{$mydata['company']->companyname}}, Money Transfer enables domestic moneytransfer through a very safe
                                        channel to almost all banks across India. The money transfer service is available at our all retailers' mobile apps and on website login.
                                        It enables walk -in customers even without a bank account from place of transfer, to transfer funds to any bank account anywhere in India.
                                        The DMT platform support dual transfer mode, IMPS (Immediate Payment Service) & NEFT (National Electronic Fund Transfer). Thank to National
                                        Payment Corporation of India (NPCI), IMPS works 24 X 7 & the recipient receive the amount in the account within few seconds. The NPIC settles
                                        the IMPS request on a real time basis (24 X 7 X 365).
                                    </p>
                                    <div class="form-group"> <a href="#" class="btn slider_btn dark_hover">Read More !</a> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="booking_form_info two">
                    <div class="tab_img">
                        <div class="b_overlay_bg"></div>
                        <img src="assets/images/service_img/aeps.png" alt="" />
                    </div>
                    <div class="boking_content">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="service-content">
                                    <h2>Aadhaar Enabled Payment System</h2>
                                    <p>
                                        AEPS is a new payment service offered by the National Payments Corporation of India to banks, financial institutions using 'AADHAAR'. AEPS
                                        stands for 'AADHAAR Enabled Payment System'. AEPS is a bank led model, which allows online financial inclusion transaction at Micro-ATM through
                                        the Business correspondent of any bank using the AADHAAR authentication. This system is designed to handle both ONUS and OFFUS requests seamlessly
                                        in an effective way by enabling authentication gateway for all AADHAAR linked account holders. Any resident of India holding an AADHAAR number
                                        and having a bank account which is linked with the AADHAAR can avail the benefits of AEPS. A Customer may visit a BC Customer Access Point.
                                        The BC using the Point of Sale (Micro-ATM) device will be able to process transactions like Cash Withdrawal & Balance Enquiry by selecting
                                        the transaction of their choice.
                                    </p>
                                    <a href="#" class="btn slider_btn dark_hover">Read More !</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="booking_form_info six">
                    <div class="tab_img">
                        <div class="b_overlay_bg"></div>
                        <img src="assets/images/service_img/prepaid_card.png" alt="" />
                    </div>
                    <div class="boking_content">
                        <div class="row booking_form">
                            <div class="col-md-12">
                                <div class="service-content">
                                    <h2>Prepaid Card</h2>
                                    <p>
                                        Prepaid cards have become more and more popular over the past few years, not least because they offer an alternative to a more traditional
                                        debit card or credit card. On the one hand, prepaid cards are very similar to debit/credit cards, insofar that they allow you to make purchases
                                        online, in-store and at ATMs. Prepaid card is a physical card and have a unique no. Use the Prepaid Card for all of the things you pay for
                                        with cash. Whether you want to shop, pay bills or manage your spending more effectively, prepaid cards will give you a simple, effective and
                                        easier way to use and manage your money. we recharge this card like the sim card, DTH card metro card and use this amount in online shopping,
                                        in the shops, on the petrol pumps etc. {{$mydata['company']->companyname}} one of the best company which provide the prepaid card API. Using this API you can use all
                                        the prepaid card services.
                                    </p>
                                    <a href="#" class="btn slider_btn dark_hover">Read More !</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-light-dark-skew booking_skew_top"></div>
    </section>
 
    <!-- Our clints Newsletter Section start -->
    <div class="rural_newsletter wow fadeInUp" data-wow-delay="0.3s">
        <div class="container">
            <div class="rural_newsletter_shape">
                <div class="rural_partner_section wow fadeInUp" data-wow-delay="0.3s">
                    <h5 class="rural_center">Our Clients</h5>
                    <h1 class="text_span">Partners</h1>
                    <div class="row">
                        <div class="col-12">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide"><img src="assets/images/partner/p-1.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-2.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-3.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-4.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-5.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-6.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-1.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-2.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-3.png" alt="" /></div>
                                    <div class="swiper-slide"><img src="assets/images/partner/p-4.png" alt="" /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Y CHOOSE OUR SERVICE-->
    <div id="Products" class="rural_about">

        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 wow fadeInLeft" data-wow-delay="0.3s" style="visibility: visible; animation-delay: 0.3s; animation-name: fadeInLeft;">
                    <div class="rural_about_detail">
                        <h5 class="rural_center rural_left">Why Choose Our Service</h5>
                        <h1 class="text_span">Why Choose {{$mydata['company']->companyname}}</h1>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="rural_about_sources rural_about_mt01">
                        <img src="assets/images/resource/Innovative-Ideas.png" alt="" />
                        <h5>Easy And Fast</h5>
                        <p>Our Portal Working is very easy and fast.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="rural_about_sources rural_about_mt02">
                        <img src="assets/images/resource/support.png" alt="" />
                        <h5>24/7 Support</h5>
                        <p>Anytime we are ready for Customer support</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="rural_about_sources rural_about_mt01">
                        <img src="assets/images/resource/successful.png" alt="" />
                        <h5>Heights Commission &amp; Margins</h5>
                        <p>We are Provide Heights commission and Margins for all Agents in over india.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="rural_about_sources rural_about_mt02">
                        <img src="assets/images/resource/technologies.png" alt="" />
                        <h5>Safe And Secure</h5>
                        <p>Our Company is Recognition in startup India program by Govt. of India so it’s Safe and Secure.</p>
                    </div>
                </div>
            </div>


        </div>
        <div class="rural_about_gradient_left whychoose_skew_bottom"></div>
    </div>


    <!-- Counters Section Start -->
    <div class="rural_counter wow fadeInUp" data-wow-delay="0.3s">
        <section id="statistic" class="statistic-section one-page-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="counter fun-counted1 wow fadeIn rollIn" data-wow-delay="0ms">
                            <i class="fa fa-handshake-o fa-2x stats-icon"></i>
                            <h2 class="timer count-title count count1">500</h2>
                            <div class="stats-line-black"></div>
                            <p class="stats-text">Business Partners </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="counter fun-counted2 wow fadeIn rollIn" data-wow-delay="400ms">
                            <i class="fa fa-users fa-2x stats-icon"></i>
                            <h2 class="timer count-title count count2"> 100 </h2>
                            <div class="stats-line-black"></div>
                            <p class="stats-text"> Satisfied Clients</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="counter fun-counted3 wow fadeIn rollIn" data-wow-delay="800ms">
                            <i class="fa fa-th stats-icon" aria-hidden="true"></i>
                            <h2 class="timer count-title count count3">500</h2>
                            <div class="stats-line-black"></div>
                            <p class="stats-text">Distributors</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="counter fun-count fun-counted4 wow fadeIn rollIn" data-wow-delay="800ms">
                            <i class="fa fa-user-secret stats-icon" aria-hidden="true"></i>
                            <h2 class="timer count-title count count4">400</h2>
                            <div class="stats-line-black"></div>
                            <p class="stats-text">Retailers</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Counters Section end -->
  
    <!-- Reviews -->
    <div class="testimonial">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-center m-auto">
                    <h2>OUR <span>REVIEWS</span></h2>
                    <div id="myCarousel" class="carousel slide" data-ride="carousel">
                        <!-- Carousel -->
                        <div class="carousel-inner">
                            <div class="item carousel-item active">
                                <div class="img-box"><img src="assets/images/testimonial-2.jpg" alt=""></div>
                                <p class="testimonial">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam eu sem tempor</p>
                                <p class="overview"><b>Jennifer Smith</b>, Office Worker</p>
                            </div>
                            <div class="item carousel-item">
                                <div class="img-box"><img src="assets/images/testimonial-4.jpg" alt=""></div>
                                <p class="testimonial">uscipit tincidunt. Utmtc tempus dictum risus. Pellenteat mattis.Alidio.</p>
                                <p class="overview"><b>Dauglas McNun</b>, Financial Advisor</p>
                            </div>
                            <div class="item carousel-item">
                                <div class="img-box"><img src="assets/images/testimonial-2.jpg" alt=""></div>
                                <p class="testimonial">Phasellus vitae suscipit justo. Mauris pharetra  Etiam hendrerit dolor eget rutrum.</p>
                                <p class="overview"><b>Hellen Wright</b>, Athelete</p>
                            </div>
                        </div>
                        <!-- Carousel Controls -->
                        <a class="carousel-control left carousel-control-prev" href="#myCarousel" data-slide="prev">
                            <i class="fa fa-angle-left"></i>
                        </a>
                        <a class="carousel-control right carousel-control-next" href="#myCarousel" data-slide="next">
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


      <!-- Footer section starts -->
    <div id="Contact" class="solar_footer">
        <div class="container">
            <div class="solar_footer_shape">
                <div class="solar_footer_gradient"></div>
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="solar_footer_ab">
                            <a class="footer-logo" href="#"> <img src="assets/images/logo.png" alt="solar-installation-footer-logo" /></a>
                            <p>
                                we provide all online services like Mobile, DTH and Data Card Recharges, Postpaid Bill Payment, Electricty Bill Payment,
                                Landline Bill Payment, Flight Booking, Remittance / Money-transfers, Recharge
                                Websites and Software, Recharge and DTH Direct Operator API Provider and Many More.
                            </p>
                            <div class="top_solar_btn">
                                <ul>
                                    <li><a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                                    <li><a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                                    <li><a href="#"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
                                    <li><a href="#"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 col-sm-6">
                        <div class="solar_footer_links">
                            <h3>useful links</h3>
                            <div class="unerline"><span></span><span></span><span></span></div>
                            <div class="solar_links">
                                <ul>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Home</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">About Us</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Services</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Contact Us</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Privacy Policy</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Faqs </a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Site Map </a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Terms & Conditions </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="solar_footer_links">
                            <h3>Services</h3>
                            <div class="unerline"><span></span><span></span><span></span></div>
                            <div class="solar_links">
                                <ul>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Recharges</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Bill Payment</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Money Transer</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Aeps</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Pan Card</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Travel Booking</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">mPOS Machine</a></li>
                                    <li><i class="fa fa-angle-double-right" aria-hidden="true"></i><a href="#">Prepaid Card</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <div class="solar_footer_contact">
                            <h3>contact us</h3>
                            <div class="unerline"><span></span><span></span><span></span></div>
                            <div class="solar_information">
                                <ul>
                                    <li><i class="fa fa-map-marker" aria-hidden="true"></i>Dist-Purba Medinipur,State-West Bengal</li>
                                    <li><i class="fa fa-envelope" aria-hidden="true"></i>{{$mydata['supportemail']}}</li>
                                </ul>
                            </div>
                            <p>Call Now</p>
                            <h1>{{$mydata['supportnumber']}}</h1>
                        </div>
                    </div>
                </div>

                <!-- whatsapp  -->
                <!--<div class="whatsapp">
                    <a href="https://wa.me/message/MQ2E5PYX4HX5M1" target="_blank">
                    <img src="assets/images/products/whatsapp.png" class="img-responsive" alt="whatsapp">
                    </a>
                </div>-->

            </div>
            <div class="solar_copyright">
                <p>Copyright © {{ date('Y') }} {{$mydata['company']->companyname}} | All Rights Reserved |</p>
            </div>
        </div>
    </div>

    <!-- Footer Section end -->
    
   @if(\Myhelper::website_popup()) 
    <div  class="newsletter-overlay">

  <div id="newsletter-popup">
    <a style="color:red;" href="#" class="popup-close">X</a>
    <div class="newsletter-in">
      
         {!! nl2br(\Myhelper::website_popup()) !!}
   
    </div>
  </div>
</div>
 @endif
<style>
    
#newsletter-popup{
  margin: 70px auto;
  padding:30px 40px 40px;
  background: rgba(255, 255, 255, 0);
  border-radius: 5px;
  width: 25%;
  position: relative;
  transition: all 1s ease-in-out;
}

@media screen and (max-width: 1366px){
  #newsletter-popup{
    width: 40%;
  }
}

@media screen and (max-width: 992px){
  #newsletter-popup{
    width: 70%;
  }
}

.newsletter-overlay{
  z-index:999999;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  transition: opacity 500ms;
  visibility: visible;
  opacity: 1;
  display: none;
}

#newsletter-popup h3{
  color: #fff;
  font-size: 24px;
  margin: 0 0 10px;
  font-family: 'Gloria Hallelujah',cursive;
}

#newsletter-popup input[type="text"]{
    width: 100%;
    height: 36px;
    border: none;
    text-indent: 10px;
    font-size: 13px;
    border-bottom: 2px solid #faeaec;
    border-top: 2px solid #fff;
    padding: 0;
    color: #666;
    margin-bottom: 15px;
}

#newsletter-popup input[type="submit"]{
  background: #6EC5D9;
    border: none;
    border-bottom: 3px solid #57B8CE;
    color: #fff;
    text-align: center;
    display: block;
    padding: 0;
    line-height: 1.5;
    width: 100%;
    cursor: pointer;
    margin: 0;
    font-size: 21px;
    font-family: "Gloria Hallelujah",cursive;
}

#newsletter-popup .popup-close{
  color: #fff;
  height: 30px;
  width: 30px;
  position: absolute;
  top: 10px;
  right: 10px;
  text-align: center;
  text-decoration: none;
  line-height: 30px;
  font-family:  "Gloria Hallelujah",cursive;
  font-weight: bold;
}
</style>

 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
 @if(\Myhelper::website_popup())

<script>
    var delay = 100; //in milleseconds

jQuery(document).ready(function($){
  setTimeout(function(){ showNewsletterPopup(); }, delay);
  
  $('.popup-close').click(function(){
      $('.newsletter-overlay').hide();
      
      //when closed create a cookie to prevent popup to show again on refresh
      //setCookie('newsletter-popup', 'popped', 30);
  });
});

function showNewsletterPopup(){
  if( getCookie('newsletter-popup') == ""){
     $('.newsletter-overlay').show();
     //setCookie('newsletter-popup', 'popped', 30);
  }
  else{
    console.log("Newsletter popup blocked.");
  }
}


function setCookie(cname,cvalue,exdays)
{
    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires+"; path=/";
}

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) 
    {
        var c = jQuery.trim(ca[i]);
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
}
</script>
@endif
    <div class="rural_top_icon"> <a id="button"></a> </div>
    
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
     <style>
         .float{
	position:fixed;
	width:60px;
	height:60px;
	bottom:40px;
	left:40px;
	background-color:#25d366;
	color:#FFF;
	border-radius:50px;
	text-align:center;
  font-size:30px;
	box-shadow: 2px 2px 3px #999;
  z-index:100;
}

.my-float{
	margin-top:16px;
}
     </style>
     
     @php
     $userid = Auth::id();
     $supportnum = $mydata['supportnumber'];
     @endphp
<a href="https://api.whatsapp.com/send?phone=91{{$supportnum}}&text=ID%3A%20%0AI%20am%20looking%20for%20support." class="float" target="_blank">
<i class="fa fa-whatsapp my-float"></i>
<span style="background: #22eb2c;
margin-left: -26px;">Support</span>
</a>
    <!-- javascript start -->
<!--    <script src="assets/js/jquery.min.js"></script>-->
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.themepunch.plugins.min.js"></script>
    <script src="assets/js/jquery.themepunch.revolution.min.js"></script>
    <script src="assets/js/slick.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>


</form>
</body>
</html>
