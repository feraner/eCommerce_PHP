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

$token = $_REQUEST['token'];
if(! isset($token)) {
		$OId=trim($_POST['OId']);//自定义边框颜色
		$ShippingPrice=(float)$_POST['ShippingPrice'];	//运费
		$ShippingInsurance=(int)$_POST['ShippingInsurance']==1?1:0;	//是否需要快递保险
		$ShippingInsurancePrice=$ShippingInsurance==1?(float)$_POST['ShippingInsurancePrice']:0;	//快递保险费
		$CouponCutPrice=(float)$_POST['CouponCutPrice'];	//优惠券抵扣金额
		$CUSTOM=urlencode($_POST['CUSTOM']);//自定义值
		$LOGOIMG=urlencode(($pay_account['LogoImg']?$pay_account['LogoImg']:ly200::get_domain().$c['config']['global']['LogoPath']));//自定义LOGO
		$CARTBORDERCOLOR=trim($pay_account['BorderColor']);//自定义边框颜色
				
		//$cart_row=db::get_all('shopping_excheckout c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']} and c.OId='{$OId}'", 'c.*, p.Name_en, p.PicPath_0', 'c.CId asc');
		//!count($cart_row) && js::location("/?m=cart");
		$TotalQty=db::get_row_count('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'");
		!(int)$TotalQty && js::location("/?m=cart");
		
		//$total_weight=db::get_sum('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", 'Qty*Weight');
		$ProductPrice=db::get_sum('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", '(Price+PropertyPrice)*Qty');	//商品总价格
		
		$AdditionalFee=db::get_value('payment', 'IsUsed=1 and Method="Excheckout"', 'AdditionalFee');
		/* The servername and serverport tells PayPal where the buyer
		   should be directed back to after authorizing payment.
		   In this case, its the local webserver that is running this script
		   Using the servername and serverport, the return URL is the first
		   portion of the URL that buyers will return to after authorizing payment
		   */
		/*$serverName = $_SERVER['SERVER_NAME'];
		$serverPort = $_SERVER['SERVER_PORT'];
		$url=dirname('http://'.$serverName.':'.$serverPort.$_SERVER['REQUEST_URI']);*/
		
		//$SId= $_REQUEST['SId'];
		//echo $SId;die();
		
		
		$HANDLINGAMT=sprintf('%01.2f',($ProductPrice+$ShippingPrice+$ShippingInsurancePrice)*($AdditionalFee/100));
		$L_NAME		= $_POST['L_NAME'];
		$L_AMT		= $_POST['L_AMT'];
		$L_QTY		= $_POST['L_QTY'];
		//$L_NUMBER	= $_POST['L_NUMBER'];
		
		$itemamt=$_itemPrice=0.00;
		$nvpstr_product=$L_name=$L_amt=$L_qty=$L_num='';
		for($i=0; $i<count($L_NAME); $i++){
			$itemamt+=$L_AMT[$i]*$L_QTY[$i];
			$_itemPrice+=cart::iconv_price($L_AMT[$i], 2, '', 0)*$L_QTY[$i];
			//$L_name.='&L_PAYMENTREQUEST_0_NAME'.$i.'='.$L_NAME[$i];
			//$L_amt.='&L_PAYMENTREQUEST_0_AMT'.$i.'='.cart::iconv_price($L_AMT[$i], 2, '', 0);
			//$L_qty.='&L_PAYMENTREQUEST_0_QTY'.$i.'='.$L_QTY[$i];
			//$L_num.='&L_PAYMENTREQUEST_0_NUMBER'.$i.'='.$L_NUMBER[$i];
		}
		//$nvpstr_product.=$L_name.$L_amt.$L_qty;//产品内容.$L_num
		$amt=cart::iconv_price(($itemamt + $ShippingPrice + $ShippingInsurancePrice + $HANDLINGAMT - $CouponCutPrice), 2, '', 0);//订单总价
		$itemamt=cart::iconv_price($itemamt, 2, '', 0);
		if($_itemPrice!=$itemamt){ //有差价
			$surplus=abs(sprintf('%01.2f', $itemamt-$_itemPrice));
			$L_AMT[0]=$L_AMT[0]+$surplus; //第一个产品补回差价
		}
		for($i=0; $i<count($L_NAME); $i++){
			$L_name.='&L_PAYMENTREQUEST_0_NAME'.$i.'='.$L_NAME[$i];
			$L_amt.='&L_PAYMENTREQUEST_0_AMT'.$i.'='.cart::iconv_price($L_AMT[$i], 2, '', 0);
			$L_qty.='&L_PAYMENTREQUEST_0_QTY'.$i.'='.$L_QTY[$i];
		}
		$nvpstr_product.=$L_name.$L_amt.$L_qty;//产品内容
		
		/* The returnURL is the location where buyers return when a
		payment has been succesfully authorized.
		The cancelURL is the location buyers are sent to when they hit the
		cancel button during authorization of payment during the PayPal flow
		*/
		$domain=ly200::get_domain();
		$returnURL =urlencode("{$domain}/cart/excheckout/ReviewOrder.html?&currencyCodeType={$currencyCodeType}&paymentType={$paymentType}");
		$cancelURL =urlencode("{$domain}/cart/" );//&paymentType=$paymentType
	

		/* Construct the parameter string that describes the PayPal payment
		the varialbes were set in the web form, and the resulting string
		is stored in $nvpstr
		*/
		$nvpstr="";
		
		$nvpstr.="&RETURNURL=".$returnURL;//客户选择通过 PayPal 付款后其浏览器将返回到的 URL
		$nvpstr.="&CANCELURL=".$cancelURL;//客户不批准使用 PayPal 向您付款时将返回到的 URL
		
		$nvpstr.="&PAYMENTREQUEST_0_INVNUM=".$OId;//订单号

		$nvpstr.="&PAYMENTREQUEST_0_AMT=".$amt;//支付总金额
		$nvpstr.="&PAYMENTREQUEST_0_ITEMAMT=".$itemamt;//订单所有物品的价格
		$nvpstr.="&PAYMENTREQUEST_0_SHIPPINGAMT=".cart::iconv_price($ShippingPrice, 2);//订单的邮费总额
		$nvpstr.="&PAYMENTREQUEST_0_INSURANCEAMT=".cart::iconv_price($ShippingInsurancePrice, 2);//订单的邮寄保费总额
		$nvpstr.="&PAYMENTREQUEST_0_SHIPDISCAMT=".cart::iconv_price(-$CouponCutPrice, 2);//优惠券抵扣金额
		$nvpstr.="&PAYMENTREQUEST_0_HANDLINGAMT=".cart::iconv_price($HANDLINGAMT, 2);//订单处理费用的总额
		//$nvpstr.="&MAXAMT=".cart::iconv_price($maxamt, 2);//整个订单的预计最大总金额，包括运费和税金
		
		$nvpstr.="&PAYMENTREQUEST_0_CURRENCYCODE=".$_SESSION['Currency']['Currency'];//交易币种
		$nvpstr.="&PAYMENTREQUEST_0_PAYMENTACTION=Sale";//希望获取付款的方式
		$nvpstr.="&PAYMENTREQUEST_0_CUSTOM=".$CUSTOM;//用户可以根据自己的需求自定义的域
		$nvpstr.="&LOGOIMG=".$LOGOIMG;//自定义付款页面LOGO
		$nvpstr.="&CARTBORDERCOLOR=".$CARTBORDERCOLOR;//自定义付款页面边框颜色
		
		$nvpstr.="&ADDRESSOVERRIDE=0&ADDROVERRIDE=0".$nvpstr_product."&BRANDNAME=".$c['config']['global']['SiteName'];
		$nvpstr.="&ButtonSource=ueeshop_Cart";	//BN Code(来源标志)
		$nvpstr = $nvpHeader.$nvpstr;
		//echo $nvpstr;
		//exit;
		
		/* Make the call to PayPal to set the Express Checkout token
		If the API call succeded, then redirect the buyer to PayPal
		to begin to authorize payment.  If an error occured, show the
		resulting errors
		*/
		$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_AMT']=$amt;
		$resArray=hash_call("SetExpressCheckout",$nvpstr);
		$_SESSION['Gateway']['PaypalExcheckout']['reshash']=$resArray;
		
		$ack = strtoupper($resArray["ACK"]);
		//echo $ack;die();
		if($ack=="SUCCESS"){
			// Redirect to paypal.com here
			$token = urldecode($resArray["TOKEN"]);
			$payPalURL = PAYPAL_URL.$token;
			header("Location: ".$payPalURL);
		} else  {
			//Redirecting to APIError.php to display errors.
			//$location = "APIError.php";
			$location = '/cart/excheckout/APIError.html';
			header("Location: $location");
		}
		
} else {

		 /* At this point, the buyer has completed in authorizing payment
			at PayPal.  The script will now call PayPal with the details
			of the authorization, incuding any shipping information of the
			buyer.  Remember, the authorization is not a completed transaction
			at this state - the buyer still needs an additional step to finalize
			the transaction
			*/
		//$token =urlencode( $_REQUEST['token']);
		$token = $_REQUEST['token'];
		//exit($token);

		 /* Build a second API request to PayPal, using the token as the
			ID to get the details on the payment authorization
			*/
		$nvpstr="&TOKEN=".$token;

		$nvpstr = $nvpHeader.$nvpstr;
		 /* Make the API call and store the results in an array.  If the
			call was a success, show the authorization details, and provide
			an action to complete the payment.  If failed, show the error
			*/
		$resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);
		//$_SESSION['Gateway']['PaypalExcheckout']['reshash']=$resArray;
		$_SESSION['Gateway']['PaypalExcheckout']['reshash']=str::str_code($resArray, 'addslashes');
		$ack = strtoupper($resArray["ACK"]);
			
		//exit($ack);
		if($ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING'){
			require_once "GetExpressCheckoutDetails.php";
			
			$location="/cart/excheckout/checkout.html";
			header("Location: $location");
			//header("Location: checkout.php");
		} else  {
			//Redirecting to APIError.php to display errors.
			$location = "/cart/excheckout/APIError.html";
			header("Location: $location");
		}
}
?>