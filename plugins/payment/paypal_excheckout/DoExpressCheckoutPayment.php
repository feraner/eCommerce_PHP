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

include_once('CallerService.php');
$domain=ly200::get_domain();
ini_set('session.bug_compat_42',0);
ini_set('session.bug_compat_warn',0);

$reshashAry=$_SESSION['Gateway']['PaypalExcheckout']['reshash'];

$token=urlencode($reshashAry['TOKEN']);
$payerID=urlencode($reshashAry['PAYERID']);
$OId=$reshashAry['PAYMENTREQUEST_0_INVNUM'];	//订单号

/******************************************************************
$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_PAYMENTACTION='.Paypal_PaymentType;

$itemamt=0.00;
$nvpstr_product=$name=$amt=$qty='';
for($i=0; $i<10; $i++){
	$L_Name=$reshashAry["L_PAYMENTREQUEST_0_NAME{$i}"];
	$L_AMT=$reshashAry["L_PAYMENTREQUEST_0_AMT{$i}"];
	$L_QTY=$reshashAry["L_PAYMENTREQUEST_0_QTY{$i}"];
	if(!$L_Name || $L_AMT<=0 || !$L_QTY) break;
	
	$itemamt+=cart::iconv_price($L_AMT*$L_QTY, 2, '', 0);
	$name.="&L_PAYMENTREQUEST_0_NAME{$i}=".urlencode($L_NAME);
	$amt.="&L_PAYMENTREQUEST_0_AMT{$i}=".$L_AMT;
	$qty.="&L_PAYMENTREQUEST_0_QTY{$i}=".$L_QTY;
}
$nvpstr_product.=$name.$amt.$qty;//产品内容.$L_num
$nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=".$reshashAry['PAYMENTREQUEST_0_CURRENCYCODE'];
$nvpstr.="&PAYMENTREQUEST_0_AMT=".$reshashAry['PAYMENTREQUEST_0_AMT'];
$nvpstr.="&PAYMENTREQUEST_0_ITEMAMT=".$reshashAry['PAYMENTREQUEST_0_ITEMAMT'];
$nvpstr.='&PAYMENTREQUEST_0_INVNUM='.$reshashAry['PAYMENTREQUEST_0_INVNUM'];//订单号
$nvpstr.='&PAYMENTREQUEST_0_SHIPPINGAMT='.$reshashAry['PAYMENTREQUEST_0_SHIPPINGAMT'];//订单的邮费总额
$nvpstr.='&PAYMENTREQUEST_0_INSURANCEAMT='.$reshashAry['PAYMENTREQUEST_0_INSURANCEAMT'];//订单的邮寄保费总额
$nvpstr.='&PAYMENTREQUEST_0_SHIPDISCAMT='.-$reshashAry['PAYMENTREQUEST_0_SHIPDISCAMT'];//优惠券抵扣金额
$nvpstr.='&PAYMENTREQUEST_0_HANDLINGAMT='.$reshashAry['PAYMENTREQUEST_0_HANDLINGAMT'];//订单处理费用的总额
$nvpstr.=$nvpstr_product;
******************************************************************/


/********************************************************************/
$currCodeType=$reshashAry['PAYMENTREQUEST_0_CURRENCYCODE'];
$paymentAmount=$reshashAry['PAYMENTREQUEST_0_AMT'];

$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_PAYMENTACTION='.Paypal_PaymentType.'&PAYMENTREQUEST_0_AMT='.$paymentAmount.'&PAYMENTREQUEST_0_CURRENCYCODE='.$currCodeType;
/********************************************************************/


$resArray=hash_call("DoExpressCheckoutPayment", $nvpstr);

file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-DoExpress.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".$nvpstr."\r\n\r\n".print_r($resArray, true));

$ack=strtoupper($resArray["ACK"]);
if($ack!='SUCCESS' && $ack!='SUCCESSWITHWARNING'){
	$_SESSION['Gateway']['PaypalExcheckout']['reshash']=$resArray;
	header('Location: /payment/paypal_excheckout/APIError/');
}else{
	!$OId && $OId=$_GET['OId'];
	unset($_SESSION['Gateway']['PaypalExcheckout']);
	$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 3)");
	!$order_row && js::location('/');
	$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
	orders::orders_payment_result(1, $UserName, $order_row, '');
	
	$url="{$domain}/cart/success/{$order_row['OId']}.html";
	//!(int)$_SESSION['User']['UserId'] && $url="{$domain}/cart/complete/{$order_row['OId']}.html";
	js::location($url);
}
?>
