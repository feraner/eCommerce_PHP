<?php
$OId=$_GET['OId'];
$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
if(!$order_row){
	file::write_file('/_pay_log_/paypal_excheckout/credit_log/'.date('Y_m/d/', $c['time']), "{$OId}-error.txt", "订单数据为空\r\n\r\n".date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".print_r($_GET, true)."\r\n\r\n".print_r($_POST, true));
	ly200::e_json('', 0);
}
$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';

$state=strtolower($p_state);
if($state=='approved'){
	//买方批准交易
	orders::orders_payment_result(1, $UserName, $order_row, '');
	file::write_file('/_pay_log_/paypal_excheckout/credit_log/'.date('Y_m/d/', $c['time']), "{$OId}-DoExpress.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".print_r($_GET, true)."\r\n\r\n".print_r($_POST, true));
}else{
	//交易请求失败
	orders::orders_payment_result(0, $UserName, $order_row, '');
	file::write_file('/_pay_log_/paypal_excheckout/credit_log/'.date('Y_m/d/', $c['time']), "{$OId}-error.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".print_r($_GET, true)."\r\n\r\n".print_r($_POST, true));
}