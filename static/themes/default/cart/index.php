<?php !isset($c) && exit();?>
<?php
$login_module_ary=array('checkout', 'buynow', 'ajax_get_coupon_info', 'remove_coupon', 'placeorder', 'complete', 'payment', 'success');	//需要登录的模块列表
$un_login_module_ary=array('list', 'additem', 'add_success', 'modify', 'remove', 'set_no_login_address', 'get_default_country', 'get_shipping_methods', 'get_excheckout_country', 'excheckout', 'offline_payment');	//不需要登录的模块列表

if((int)$_SESSION['User']['UserId']){	//已登录
	$module_ary=array_merge($un_login_module_ary, $login_module_ary);
}else{	//未登录
	if($c['orders']['mode']==0){	//必须登录方可下订单
		@in_array($a, $login_module_ary) && js::location("/account/login.html?jump_url=".urlencode($_SERVER['REQUEST_URI']));	//访问需要登录的模块但用户并未登录  .'?'.ly200::query_string()
		$module_ary=$un_login_module_ary;
	}else{	//未登录也可以下订单
		$module_ary=@array_merge($un_login_module_ary, $login_module_ary);
	}
}
($a=='' || !in_array($a, $module_ary)) && $a=$module_ary[0];

//快捷支付模板
if($a=='excheckout'){
	$d_ary=array('SetExpressCheckout', 'ReviewOrder', 'APIError', 'checkout', 'DoExpressCheckoutPayment', 'GetExpressCheckoutDetails', 'cancel');
	$d=$_GET['d']?$_GET['d']:$_POST['d'];
	!in_array($d, $d_ary) && $d=$d_ary[0];
	
	include("{$c['root_path']}static/themes/default/gateway/paypal_excheckout/{$d}.php");
	exit();
}else{
	if($a=='list' || $a=='checkout' || $a=='buynow'){//自动更新产品信息
		cart::open_update_cart();
	}
	
	$cutArr=str::json_data(db::get_value('config', 'GroupId="cart" and Variable="discount"', 'Value'), 'decode');//全场满减的数据
	$StyleData=(int)db::get_row_count('config_module', 'IsDefault=1')?db::get_value('config_module', 'IsDefault=1', 'StyleData'):db::get_value('config_module', "Themes='{$c['theme']}'", 'StyleData');//模板风格色调的数据
	$style_data=str::json_data($StyleData, 'decode');
	
	$file_name=$a;
	$a=='buynow' && $file_name='checkout';//BuyNow页面归纳到Checkout页面
	ob_start();
	include("module/{$file_name}.php");
	$cart_page_contents=ob_get_contents();
	ob_end_clean();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
echo ly200::seo_meta();
include("{$c['static_path']}/inc/static.php");
echo ly200::load_static('/static/themes/default/css/cart.css', "/static/themes/{$c['theme']}/css/cart.css", '/static/themes/default/js/cart.js', '/static/js/plugin/tool_tips/tool_tips_web.js');

//analytics统计
if($a=='checkout' || $a=='buynow'){
	echo '<script type="text/javascript">$(function(){ analytics_click_statistics(4) });</script>';
}elseif($a=='success'){
	echo '<script type="text/javascript">$(function(){ analytics_click_statistics(6) });</script>';
}

if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed']){
	//Facebook Pixel
	if($a=='checkout' || $a=='buynow'){
		//Payment information is added during checkout.
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
}
?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php
if($a=='checkout' || $a=='buynow'){//独立页面
	include("{$c['static_path']}inc/header.php"); //加载公共头部文件
	echo '<div id="cart_checkout_container">'.$cart_page_contents.'</div>';
	echo ly200::out_put_third_code();
}else{//公共页面
	include("{$c['theme_path']}/inc/header.php");
	echo '<div id="cart_container">'.$cart_page_contents.'</div>';
	include("{$c['theme_path']}/inc/footer.php");
}
?>
</body>
</html>