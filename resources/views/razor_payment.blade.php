<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script> 
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>

 $( document ).ready(function() {
    var totalAmount = $(this).attr("data-amount");
    var product_id =  $(this).attr("data-id");
    var options = {
    "key": "rzp_live_ILgsfZCZoFIKMb",
    "amount": (1*100), // 2000 paise = INR 20
    "name": "Tutsmake",
    "description": "Payment",
    "image": "{{asset('')}}public/logos/{{Auth::user()->company->logo}}",
    "handler": function (response){
          $.ajax({
            url: "{{route('razorvalidate')}}",
            type: 'post',
            dataType: 'json',
            data: {
                razorpay_payment_id: response.razorpay_payment_id , totalAmount : totalAmount ,product_id : product_id,
            }, 
            success: function (msg) {

               window.location.href = "{{route('razorsuccess')}}";
            }
        });
     
    },

    "theme": {
        "color": "#528FF0"
    }
  };
  var rzp1 = new Razorpay(options);
  rzp1.open();
  e.preventDefault();
  });