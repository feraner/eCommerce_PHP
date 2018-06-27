<?php !isset($c) && exit();?>
<?php
switch($_GET['d']){
	case 'APIError' 				: include("{$c['default_path']}gateway/paypal_excheckout/APIError.php"); break;
	case 'CallerService' 			: include("{$c['default_path']}gateway/paypal_excheckout/CallerService.php"); break;
	case 'cancel' 					: include("{$c['default_path']}gateway/paypal_excheckout/cancel.php"); break;
	case 'checkout'					: include("{$c['default_path']}gateway/paypal_excheckout/checkout.php"); break;
	case 'constants'				: include("{$c['default_path']}gateway/paypal_excheckout/constants.php"); break;
	case 'DoExpressCheckoutPayment' : include("{$c['default_path']}gateway/paypal_excheckout/DoExpressCheckoutPayment.php"); break;
	case 'GetExpressCheckoutDetails': include("{$c['default_path']}gateway/paypal_excheckout/GetExpressCheckoutDetails.php"); break;
	case 'ReviewOrder'				: include("{$c['default_path']}gateway/paypal_excheckout/ReviewOrder.php"); break;
	case 'SetExpressCheckout'		: include("{$c['default_path']}gateway/paypal_excheckout/SetExpressCheckout.php"); break;
	case 'ShowAllResponse'			: include("{$c['default_path']}gateway/paypal_excheckout/ShowAllResponse.php"); break;
}
exit();
?>