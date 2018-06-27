<?php
include('CallerService.php');
ini_set('display_errors','on');
/********************************************
ReviewOrder.php

This file is called after the user clicks on a button during
the checkout process to use PayPal's Express Checkout. The
user logs in to their PayPal account.

This file is called twice.

On the first pass, the code executes the if statement:

if (! isset ($token))

The code collects transaction parameters from the form
displayed by SetExpressCheckout.html then constructs and
sends a SetExpressCheckout request string to the PayPal
server. The paymentType variable becomes the PAYMENTACTION
parameter of the request string. The RETURNURL parameter
is set to this file; this is how ReviewOrder.php is called
twice.

On the second pass, the code executes the else statement.

On the first pass, the buyer completed the authorization in
their PayPal account; now the code gets the payer details
by sending a GetExpressCheckoutDetails request to the PayPal
server. Then the code calls GetExpressCheckoutDetails.php.

Note: Be sure to check the value of PAYPAL_URL. The buyer is
sent to this URL to authorize payment with their PayPal
account. For testing purposes, this should be set to the
PayPal sandbox.

Called by SetExpressCheckout.html.

Calls GetExpressCheckoutDetails.php, CallerService.php,
and APIError.php.

********************************************/
/* An express checkout transaction starts with a token, that
   identifies to PayPal your transaction
   In this example, when the script sees a token, the script
   knows that the buyer has already authorized payment through
   paypal.  If no token was found, the action is to send the buyer
   to PayPal to first authorize payment
   */
//	print_r($_SESSION);
//	print_r($_REQUEST);die();
//	print_r($_GET);
//	print_r($_POST);

$token=$_REQUEST['token'];
if(!isset($token)){
	$OId=trim($_POST['OId']);//自定义边框颜色
	$ShippingPrice=(float)$_POST['ShippingPrice'];	//运费
	//$ShippingInsurance=(int)$_POST['ShippingInsurance']==1?1:0;	//是否需要快递保险
	//$ShippingInsurancePrice=$ShippingInsurance==1?(float)$_POST['ShippingInsurancePrice']:0;	//快递保险费
	$ShippingInsurancePrice=(float)$_POST['ShippingInsurancePrice'];	//快递保险费
	$CouponCutPrice=(float)$_POST['CouponCutPrice'];	//优惠券抵扣金额
	$CUSTOM=urlencode($_POST['CUSTOM']);//自定义值
	$LOGOIMG=urlencode(($pay_account['LogoImg']?$pay_account['LogoImg']:ly200::get_domain().$c['config']['global']['LogoPath']));//自定义LOGO
	$CARTBORDERCOLOR=trim($pay_account['BorderColor']);//自定义边框颜色
	
	$TotalQty=db::get_row_count('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'");
	!(int)$TotalQty && js::location('/cart/');
	//$ProductPrice=db::get_sum('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", '(Price+PropertyPrice)*Qty');	//商品总价格
	
	$AdditionalFee=db::get_value('payment', 'IsUsed=1 and Method="Excheckout"', 'AdditionalFee');
	
	$L_NAME=$_POST['L_NAME'];
	$L_AMT=$_POST['L_AMT'];
	$L_QTY=$_POST['L_QTY'];
	
	$itemamt=$_itemPrice=0.00;
	$nvpstr_product=$L_name=$L_amt=$L_qty=$L_num='';
	for($i=0; $i<count($L_NAME); ++$i){
		$itemamt+=(float)cart::iconv_price($L_AMT[$i], 2, '', 0)*$L_QTY[$i];
		$L_name.='&L_PAYMENTREQUEST_0_NAME'.$i.'='.urlencode($L_NAME[$i]);
		$L_amt.='&L_PAYMENTREQUEST_0_AMT'.$i.'='.cart::iconv_price($L_AMT[$i], 2, '', 0);
		$L_qty.='&L_PAYMENTREQUEST_0_QTY'.$i.'='.$L_QTY[$i];
	}
	$nvpstr_product.=$L_name.$L_amt.$L_qty;//产品内容.$L_num
	//$itemamt=cart::iconv_price($itemamt, 2, '', 0);
	$HANDLINGAMT=sprintf('%01.2f',($itemamt+$ShippingPrice+$ShippingInsurancePrice)*($AdditionalFee/100));
	$amt=$itemamt + $ShippingPrice + $ShippingInsurancePrice + $HANDLINGAMT - $CouponCutPrice;//订单总价
	
	$domain=ly200::get_domain();
	$returnURL=urlencode("{$domain}/payment/paypal_excheckout/ReviewOrder/?utm_nooverride=1");
	$cancelURL=urlencode("{$domain}/payment/paypal_excheckout/cancel/{$OId}.html?utm_nooverride=1" );//&paymentType=$paymentType

	$nvpstr ='';
	$nvpstr.='&RETURNURL='.$returnURL;//客户选择通过 PayPal 付款后其浏览器将返回到的 URL
	$nvpstr.='&CANCELURL='.$cancelURL;//客户不批准使用 PayPal 向您付款时将返回到的 URL
	$nvpstr.='&PAYMENTREQUEST_0_INVNUM='.$OId;//订单号
	$nvpstr.='&PAYMENTREQUEST_0_AMT='.$amt;//支付总金额
	$nvpstr.='&PAYMENTREQUEST_0_ITEMAMT='.$itemamt;//订单所有物品的价格
	$nvpstr.='&PAYMENTREQUEST_0_SHIPPINGAMT='.$ShippingPrice;//订单的邮费总额
	$nvpstr.='&PAYMENTREQUEST_0_INSURANCEAMT='.$ShippingInsurancePrice;//订单的邮寄保费总额
	$nvpstr.='&PAYMENTREQUEST_0_SHIPDISCAMT='.-$CouponCutPrice;//优惠券抵扣金额
	$nvpstr.='&PAYMENTREQUEST_0_HANDLINGAMT='.$HANDLINGAMT;//订单处理费用的总额
	//$nvpstr.='&MAXAMT='.cart::iconv_price($maxamt, 2);//整个订单的预计最大总金额，包括运费和税金
	$nvpstr.='&PAYMENTREQUEST_0_CURRENCYCODE='.$_SESSION['Currency']['Currency'];//交易币种
	$nvpstr.='&PAYMENTREQUEST_0_PAYMENTACTION='.Paypal_PaymentType;//希望获取付款的方式
	$nvpstr.='&PAYMENTREQUEST_0_CUSTOM='.$CUSTOM;//用户可以根据自己的需求自定义的域
	$nvpstr.='&LOGOIMG='.$LOGOIMG;//自定义付款页面LOGO
	$nvpstr.='&CARTBORDERCOLOR='.$CARTBORDERCOLOR;//自定义付款页面边框颜色
	//$nvpstr.='&ADDRESSOVERRIDE=0&ADDROVERRIDE=0'.$nvpstr_product.'&BRANDNAME='.$c['config']['global']['SiteName'];
	$nvpstr.='&ADDRESSOVERRIDE=0'.$nvpstr_product.'&BRANDNAME='.$c['config']['global']['SiteName'];
	$nvpstr.='&ButtonSource=ueeshop_Cart';	//BN Code(来源标志)
	(int)db::get_value('payment', 'IsUsed=1 and Method="Excheckout"', 'IsCreditCard')==1 && $nvpstr.='&LANDINGPAGE=billing'; //信用卡支付
	
	/*
	$nvpstr.='&ADDROVERRIDE=1';
	$nvpstr.='&EMAIL='.urlencode('320006220@qq.com');
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTONAME='.urlencode('Louis Lau');
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOSTREET='.urlencode('3216 E 3rd St');
	//$nvpstr.='&PAYMENTREQUEST_0_SHIPTOSTREET2=Nova';
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOCITY='.urlencode('Los Angeles');
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOSTATE=CA';
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOZIP=90063';
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=US';
	$nvpstr.='&PAYMENTREQUEST_0_SHIPTOPHONENUM='.urlencode('+1 (213) 478-5759');
	*/
	
	$nvpstr =$nvpHeader.$nvpstr;
	
	$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_AMT']=$amt;
	$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_CURRENCYCODE']=$_SESSION['Currency']['Currency'];
	$resArray=hash_call('SetExpressCheckout',$nvpstr);
	$_SESSION['Gateway']['PaypalExcheckout']['reshash']=$resArray;
	file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-SetExpress.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".$nvpstr."\r\n\r\n".print_r($_SESSION['Gateway'], true));	//把返回数据写入文件
	
	$ack=strtoupper($resArray['ACK']);
	if($ack=='SUCCESS'){ //Redirect to paypal.com here
		$token=urldecode($resArray['TOKEN']);
		$payPalURL=PAYPAL_URL.$token;
		js::location($payPalURL);
		//header("Location: $payPalURL");
	}else{ //Redirecting to APIError.php to display errors.
		$location = '/payment/paypal_excheckout/APIError/';
		js::location($location);
		//header('Location: /payment/paypal_excheckout/APIError/');
	}
}else{
	$nvpstr=$nvpHeader.'&TOKEN='.$token;
	$resArray=hash_call('GetExpressCheckoutDetails', $nvpstr);
	$_SESSION['Gateway']['PaypalExcheckout']['reshash']=str::str_code($resArray, 'addslashes');
	
	$OId=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INVNUM'];
	file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-GetExpress.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".(ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n".print_r($_REQUEST, true)."\r\n\r\n".$nvpstr."\r\n\r\n".print_r($_SESSION['Gateway']['PaypalExcheckout'], true));	//把返回数据写入文件
	
	$ack=strtoupper($resArray['ACK']);
	if($ack=='SUCCESS' || $ack=='SUCCESSWITHWARNING'){
		//include_once('GetExpressCheckoutDetails.php');
		$location="/payment/paypal_excheckout/checkout/";
		js::location($location);
		//header('Location: /payment/paypal_excheckout/checkout/');
	}else{
		$location = "/payment/paypal_excheckout/APIError/";
		js::location($location);
		//header('Location: /payment/paypal_excheckout/APIError/');
	}
}
?>