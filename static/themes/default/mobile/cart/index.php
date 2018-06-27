<?php !isset($c) && exit();?>
<?php
$login_module_ary=array('checkout', 'buynow', 'quick', 'ajax_get_coupon_info', 'placeorder', 'complete', 'success');	//需要登录的模块列表
$un_login_module_ary=array('list', 'additem', 'add_success', 'modify', 'remove', 'address', 'set_no_login_address', 'get_default_country', 'get_shipping_methods', 'get_excheckout_country', 'excheckout', 'offline_payment');	//不需要登录的模块列表

if((int)$_SESSION['User']['UserId']){	//已登录
	$module_ary=array_merge($un_login_module_ary, $login_module_ary);
	$cart_where="UserId='{$_SESSION['User']['UserId']}'";
}else{	//未登录
	if($c['orders']['mode']==0){	//必须登录方可下订单
		@in_array($a, $login_module_ary) && js::location("{$c['mobile_url']}/cart/?&jump_url=".urlencode($_SERVER['REQUEST_URI']));	//访问需要登录的模块但用户并未登录  .'?'.ly200::query_string()
		$module_ary=$un_login_module_ary;
	}else{	//未登录也可以下订单
		$module_ary=@array_merge($un_login_module_ary, $login_module_ary);
	}

	$cart_where="SessionId='{$c['session_id']}'";
}

($a=='' || !in_array($a, $module_ary)) && $a=$module_ary[0];

$do_action=$a;
if($do_action && method_exists(cart, $do_action)){
	eval("cart::{$do_action}();");
}

//快捷支付模板
if($a=='excheckout'){
	$d_ary=array('SetExpressCheckout', 'ReviewOrder', 'APIError', 'checkout', 'DoExpressCheckoutPayment', 'GetExpressCheckoutDetails', 'cancel');
	$d=$_GET['d']?$_GET['d']:$_POST['d'];
	!in_array($d, $d_ary) && $d=$d_ary[0];
	
	include("{$c['root_path']}static/themes/default/gateway/paypal_excheckout/{$d}.php");
	exit();
}

$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');

if($a=='list' || $a=='checkout' || $a=='buynow'){//自动更新产品信息
	cart::open_update_cart();
}
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?php
echo ly200::seo_meta();
include("{$c['mobile']['theme_path']}inc/resource.php");
echo ly200::load_static('/static/themes/default/css/user.css', "{$c['mobile']['tpl_dir']}js/cart.js", "{$c['mobile']['tpl_dir']}js/user.js");

//analytics统计
if($a=='checkout' || $a=='buynow'){
	$analytics=4;
}elseif($a=='quick'){
	$analytics=3;
}elseif($a=='success'){
	$analytics=6;
}
if($analytics>0) echo '<script type="text/javascript">$(function(){ if($.isFunction(analytics_click_statistics)){ analytics_click_statistics('.$analytics.') } });</script>';

if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed']){
	//Facebook Pixel
	if($a=='checkout' || $a=='buynow' || $a=='quick'){
		//When someone starts a checkout flow but does not complete it yet.
		$iconv_total_price=cart::cart_total_price(($_GET['CId']?' and CId in('.str_replace('.', ',', $_GET['CId']).')':''), 1);
?>
		<!-- Facebook Pixel Code -->
		<script type="text/javascript">
		$.fn.fbq_checkout=function(){
			fbq('track', 'InitiateCheckout', {
				content_ids: ['0'],
				value:'<?=$iconv_total_price;?>',
				currency:'<?=$_SESSION['Currency']['Currency'];?>'
			});
		}
		</script>
		<!-- End Facebook Pixel Code -->
<?php
	}elseif($a=='complete'){
		//Payment information is added during checkout.
		$OId=trim($_GET['OId']);
		$order_row=db::get_one('orders', "OId='$OId'");	
		$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
		$Number_ary=array();
		$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='{$order_row['OrderId']}'", 'p.Prefix,p.Number', 'o.LId asc');
		foreach($order_list_row as $k=>$v){
			$v['Number']!='' && $Number_ary[$k]=$v['Prefix'].$v['Number'];
		}
?>
		<!-- Facebook Pixel Code -->
		<script type="text/javascript">
		fbq('track', 'AddPaymentInfo', {
			content_name: 'Shipping Cart',//关键词
			content_type: 'product',//产品类型为产品
			content_ids: ['<?=@implode("','", $Number_ary);?>'],//产品ID
			value:'<?=$total_price;?>',
			currency:'<?=$_SESSION['Currency']['Currency'];?>'
		});
		</script>
		<!-- End Facebook Pixel Code -->
<?php
	}elseif($a=='success'){
		//When a purchase is made or checkout flow is completed.
		$OId=trim($_GET['OId']);
		$order_row=db::get_one('orders', "OId='$OId'");	
		if((int)$order_row['OrderStatus']==4 && (int)$order_row['CutSuccess']==0){ //仅有付款成功才执行
			$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
			$Number_ary=array();
			$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='{$order_row['OrderId']}'", 'p.Prefix,p.Number', 'o.LId asc');
			foreach($order_list_row as $k=>$v){
				$v['Number']!='' && $Number_ary[$k]=addslashes(htmlspecialchars_decode($v['Prefix'].$v['Number']));
			}
			
			if((int)$order_row['CutSuccess']==0){ //记录已经打开过
				db::update('orders', "OId='$OId'", array('CutSuccess'=>1));
			}
?>
		<!-- Facebook Pixel Code -->
		<script type="text/javascript">
		fbq('track', 'Purchase', {
			content_type: 'product',//产品类型为产品
			content_ids: ['<?=@implode("','", $Number_ary);?>'],//产品ID
			value: <?=$total_price;?>,//订单总金额
			currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
		});
		</script>
		<!-- End Facebook Pixel Code -->
<?php
		}
	}
}?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<?php
	if($a=='address'){
		include("{$c['theme_path']}user/module/{$a}.php");
	}else{
		include("module/{$a}.php");
	}
	echo ly200::out_put_third_code();
	?>
</div>
</body>
</html>