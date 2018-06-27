<?php !isset($c) && exit();?>
<?php
$d_ary=array('SetExpressCheckout', 'ReviewOrder', 'APIError', 'checkout', 'DoExpressCheckoutPayment', 'GetExpressCheckoutDetails');
$d=$_GET['d']?$_GET['d']:$_POST['d'];
!in_array($d, $d_ary) && $d=$d_ary[0];

if(!(int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1")){
	@include($c['default_path'].'404.php');
	exit;
}

include("{$c['default_path']}gateway/paypal_excheckout/{$d}.php");
?>