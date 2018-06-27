<?php
/********************************************************
GetExpressCheckoutDetails.php

This functionality is called after the buyer returns from
PayPal and has authorized the payment.

Displays the payer details returned by the
GetExpressCheckoutDetails response and calls
DoExpressCheckoutPayment.php to complete the payment
authorization.

Called by ReviewOrder.php.

Calls DoExpressCheckoutPayment.php and APIError.php.

********************************************************/


/* Collect the necessary information to complete the
   authorization for the PayPal payment
   */

$_SESSION['Gateway']['PaypalExcheckout']['token']=$_REQUEST['token'];
$_SESSION['Gateway']['PaypalExcheckout']['payer_id'] = $_REQUEST['PayerID'];

//$_SESSION['Gateway']['PaypalExcheckout']['paymentAmount']=$_REQUEST['paymentAmount'];
//$_SESSION['Gateway']['PaypalExcheckout']['currCodeType']=$_REQUEST['currencyCodeType'];
//$_SESSION['Gateway']['PaypalExcheckout']['paymentType']=$_REQUEST['paymentType'];

$resArray=$_SESSION['Gateway']['PaypalExcheckout']['reshash'];
//$_SESSION['TotalAmount']= $resArray['AMT'] + $resArray['SHIPDISCAMT'];
//$_SESSION['Gateway']['PaypalExcheckout']['TotalAmount']= $resArray['PAYMENTREQUEST_0_AMT'] + $resArray['PAYMENTREQUEST_0_SHIPDISCAMT'];
$_SESSION['Gateway']['PaypalExcheckout']['TotalAmount']= $resArray['PAYMENTREQUEST_0_AMT'];	
//$resArray['PAYMENTREQUEST_0_ITEMAMT'] + $resArray['PAYMENTREQUEST_0_SHIPPINGAMT'] + $resArray['PAYMENTREQUEST_0_INSURANCEAMT'] + $resArray['PAYMENTREQUEST_0_HANDLINGAMT']

/* Display the  API response back to the browser .
   If the response from PayPal was a success, display the response parameters
   */

?>
