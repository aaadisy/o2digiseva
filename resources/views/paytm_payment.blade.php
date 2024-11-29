<html>
    <head>
        <title>Merchant Checkout Page</title>
    </head>
    <body>
        <center><h1>Please do not refresh this page...</h1></center>
        <form method='post' action='<?php echo $transactionURL; ?>' name='paytm' id="paytm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <?php
                foreach($paytmParams as $name => $value) {
                    echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
                }
            ?>
            <input type="hidden" name="CHECKSUMHASH" value="<?php echo $paytmChecksum ?>">
            <input type="submit" name="submit" value="submit" />
        </form>
     
    </body>
    <script>            
    document.addEventListener("DOMContentLoaded", function(event) {
            document.createElement('form').submit.call(document.getElementById('paytm'));
            });         
</script>
</html>