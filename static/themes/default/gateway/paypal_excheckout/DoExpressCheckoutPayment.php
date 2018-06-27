<?php
/**********************************************************
DoExpressCheckoutPayment.php

This functionality is called to complete the payment with
PayPal and display the result to the buyer.

The code constructs and sends the DoExpressCheckoutPayment
request string to the PayPal server.

Called by GetExpressCheckoutDetails.php.

Calls CallerService.php and APIError.php.

**********************************************************/

require_once 'CallerService.php';
$domain=ly200::get_domain();
ini_set('session.bug_compat_42',0);
ini_set('session.bug_compat_warn',0);

/* Gather the information to make the final call to
   finalize the PayPal payment.  The variable nvpstr
   holds the name value pairs
   */
$token=urlencode($_SESSION['Gateway']['PaypalExcheckout']['token']);
$payerID=urlencode($_SESSION['Gateway']['PaypalExcheckout']['payer_id']);

$OId=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INVNUM'];	//订单号
$currCodeType=$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_CURRENCYCODE'];
$paymentAmount=$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_AMT'];

$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_PAYMENTACTION='.Paypal_PaymentType.'&PAYMENTREQUEST_0_AMT='.$paymentAmount.'&PAYMENTREQUEST_0_CURRENCYCODE='.$currCodeType;



 /* Make the call to PayPal to finalize payment
    If an error occured, show the resulting errors
    */
$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);

file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-DoExpress.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".$nvpstr."\r\n\r\n".print_r($resArray, true));

/* Display the API response back to the browser.
   If the response from PayPal was a success, display the response parameters'
   If the response was an error, display the errors received using APIError.php.
   */
$ack = strtoupper($resArray["ACK"]);

if($ack != 'SUCCESS' && $ack != 'SUCCESSWITHWARNING')
{
	$_SESSION['Gateway']['PaypalExcheckout']['reshash']=$resArray;
	//$location = "APIError.php";
	//header("Location: $location");
	$location = "/cart/excheckout/APIError.html";
	header("Location: $location");
}
else
{
	unset($_SESSION['Gateway']['PaypalExcheckout']['token'],$_SESSION['Gateway']['PaypalExcheckout']['TotalAmount'],$_SESSION['Gateway']['PaypalExcheckout']['paymentType'],$_SESSION['Gateway']['PaypalExcheckout']['currCodeType'],$_SESSION['Gateway']['PaypalExcheckout']['payer_id'],$_SERVER['Ueeshop']['SERVER_NAME'],$_SESSION['Gateway']['PaypalExcheckout']['reshash']);
	$OId=$_GET['OId'];
	$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 3)");
	!$order_row && js::location('/');
	
	$Log='Update order status from '.$c['orders']['status'][$order_row['OrderStatus']].' to '.$c['orders']['status'][4];
	db::update('orders', "OId='$OId'", array('OrderStatus'=>4));
	orders::orders_log((int)$_SESSION['User']['UserId'], ((int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist'), $order_row['OrderId'], 4, $Log);
	orders::orders_products_update(4, $order_row);
	$errstr='payment success';
	
	$ToAry=array($order_row['Email']);
	$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
	include($c['static_path'].'/inc/mail/order_payment.php');
	//function_exists('fastcgi_finish_request') && fastcgi_finish_request();
	ly200::sendmail($ToAry, "We have received from your payment for order#".$OId, $mail_contents);
	orders::orders_sms($OId);
	
	//$url="{$domain}/account/orders/view{$order_row['OId']}.html?&act=payonline";
	$url="{$domain}/cart/success/{$order_row['OId']}.html";
	!(int)$_SESSION['User']['UserId'] && $url="{$domain}/cart/complete/{$order_row['OId']}.html";
	//js_location("$cart_url?module=complete&OId={$order_row['OId']}&act=payonline");
	js::location($url);
}
?>
