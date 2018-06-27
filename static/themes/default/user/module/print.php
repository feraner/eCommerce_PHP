<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
!(int)$_SESSION['User']['UserId'] && exit;
$OId=$_GET['OId'];
$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and UserId='{$_SESSION['User']['UserId']}'"));
!$orders_row && js::location('/account/');

if($orders_row['OrderStatus']<4){//未付款
	include($c['root_path']."/static/static/inc/mail/order_create.php");
}elseif($orders_row['OrderStatus']==4){//付款成功，等待发货
	include($c['root_path']."/static/static/inc/mail/order_create.php");
}elseif($orders_row['OrderStatus']==5){//订单发货
	include($c['root_path']."/static/static/inc/mail/order_shipped.php");
}elseif($orders_row['OrderStatus']==6){//订单收货
	include($c['root_path']."/static/static/inc/mail/order_change.php");
}elseif($orders_row['OrderStatus']==7){//取消订单
	include($c['root_path']."/static/static/inc/mail/order_cancel.php");
}

$include_header_html=$include_footer_html='';
?>
<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<?=$mail_contents;?>
<script type="text/javascript">window.print();</script>
</body>
</html>
<?php exit();?>
