<style type="text/css">
<!--
body,td,th {
	color: #FF0000;
}
body {
	background-color: #000000;
	background-image: url();
	background-repeat: repeat-y;
}
.style5 {
	font-size: 36px;
	font-family: Geneva, Arial, Helvetica, sans-serif;
}
-->
</style>
<title>DESKTOP</title><body>
<div align="center" class="style5">DESK<strong>TO</strong>P</div>
<center>
</center>
<br>
<hr>
<?php
$pasta = "./log";
$pasta = opendir($pasta);
$conta = 0;
$lista = array();

while ($file = readdir($pasta)) {
       if (!is_dir($file) && $file != "." && $file != "..") {
               ++$conta;
               $lista[] = $file;
       }
}

print "<font face=\"verdana\">&rsaquo;clientes [$conta]<br>\n";
for ($i = 0; $i < count($lista); $i++) {
       print $lista[$i] . "<br>\n";
}
?>
</font>
<hr>
</body>