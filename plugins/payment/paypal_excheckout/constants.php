<?php
/****************************************************
constants.php

This is the configuration file for the samples.This file
defines the parameters needed to make an API call.

PayPal includes the following API Signature for making API
calls to the PayPal sandbox:

API Username 	sdk-three_api1.sdk.com
API Password 	QFZCWN5HZM8VBG7Q
API Signature 	A.d9eRKfd1yVkRrtmMfCFLTqa6M9AyodL0SJkhYztxUi8W9pCXF6.4NI

Called by CallerService.php.
****************************************************/

$Attribute=db::get_value('payment', "PId='2'", 'Attribute');
$pay_account=str::json_data($Attribute, 'decode');

$API_UserName=trim($pay_account['Username']);//'320006220_sandbox_api1.qq.com';
$API_Password=trim($pay_account['Password']);//'MF6K67XDLQ53LZXP';
$API_Signature=trim($pay_account['Signature']);//'A9o85XY7APPqIGCSCBb651V-vny9AZ-turgG0.-w7BmX1xahC.3N.4XF';

$online=1;
if((int)$online==1){ //正式环境
	$API_EndPoint='https://api-3t.paypal.com/nvp';
	$API_Paypal_Url='https://www.paypal.com/webscr&cmd=_express-checkout&token=';
}else{ //测试
	$API_EndPoint='https://api-3t.sandbox.paypal.com/nvp';
	$API_Paypal_Url='https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';
}

define('API_USERNAME', $API_UserName);
define('API_PASSWORD', $API_Password);
define('API_SIGNATURE', $API_Signature);
define('API_ENDPOINT', $API_EndPoint);
define('SUBJECT', '');
/*for permission APIs ->token, signature, timestamp  are needed*/
//define('AUTH_TOKEN',"4oSymRbHLgXZVIvtZuQziRVVxcxaiRpOeOEmQw");
//define('AUTH_SIGNATURE',"+q1PggENX0u+6vj+49tLiw9CLpA=");
//define('AUTH_TIMESTAMP',"1284959128");
define('USE_PROXY', FALSE);
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');
define('PAYPAL_URL', $API_Paypal_Url);
define('Paypal_PaymentType', 'Sale');
define('VERSION', '65.1');
define('ACK_SUCCESS', 'SUCCESS');
define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');

?>