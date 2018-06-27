<?php !isset($c) && exit();?>
<?php if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed']){?>
	<!-- Facebook Pixel Code -->
	<script type="text/javascript">
	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?=$c['config']['Platform']['Facebook']['Pixel']['Data']['PixelID'];?>');
	fbq('track', "PageView");
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=$c['config']['Platform']['Facebook']['Pixel']['Data']['PixelID'];?>&ev=PageView&noscript=1" /></noscript>
	<!-- End Facebook Pixel Code -->
    <!-- Facebook Pixel Code -->
    <script type="text/javascript">fbq('track', 'InitiateCheckout',{
    	value:'0.00',
		currency:'<?=$_SESSION['Currency']['Currency'];?>'
    });</script>
    <!-- End Facebook Pixel Code -->
<?php }?>

<?php
!$OId && $OId=trim($_GET['OId']);
!$order_row && $order_row=db::get_one('orders', "OId='$OId'");	
!$payment_row && $payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
$account=str::json_data($payment_row['Attribute'], 'decode');
$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
$shipping_price=cart::iconv_price($order_row['ShippingPrice']+$order_row['ShippingInsurancePrice'], 2, $order_row['Currency']);
$domain=ly200::get_domain();

$method_path=strtolower($payment_row['Method']);

if(@substr_count($method_path, 'payssion')){
	$method_path='payssion_'.substr($method_path, 8);
	$form_action='https://www.payssion.com/payment/create.html';//支付网关
}
if(@substr_count($method_path, 'globebill')){
	$method_path='globebill_'.substr($method_path, 9);
	$form_action='https://pay.asiabill.com/Interface';
}
if($method_path=='globebill_creditcard'){ //钱宝信用卡
	$method_path='globebill_credit_card';
}

$_SESSION['Cart']['PaymentError']=0; //现在重新支付，防止支付失败页面的站内信发送重复提交，数值清空
$c['plugin']=new plugin('payment');//插件类(支付插件)
if($c['plugin']->trigger($method_path, '__config', 'do_payment')=='enable'){//支付插件是否存在
	$pay_data=array(
		'order_row'		=>	$order_row,
		'account'		=>	$account,
		'total_price'	=>	$total_price,
		'shipping_price'=>	$shipping_price,
		'domain'		=>	$domain,
		'method_path'	=>	$method_path,
		'form_action'	=>	$form_action,
		'IsCreditCard'	=>	$payment_row['IsCreditCard']
	);
	$c['plugin']->trigger($method_path, 'do_payment', $pay_data);//调用支付插件
}else{
	$method_path=='scoinpay' && $method_path='sp_checkout';
	@substr_count($method_path, 'globebill') && $method_path='globebill';
	include("{$c['default_path']}gateway/{$method_path}/payment.php");
}
