<?php
/*************************************************
APIError.php

Displays error parameters.

Called by DoDirectPaymentReceipt.php, TransactionDetails.php,
GetExpressCheckoutDetails.php and DoExpressCheckoutPayment.php.

*************************************************/

session_start();
$OId=$_GET['OId'];
$domain=ly200::get_domain();
$resArray=$_SESSION['Gateway']['PaypalExcheckout']['reshash'];
$is_mobile=ly200::is_mobile_client(1);

if(!$is_mobile){//PC端
?>
	<html>
	<head>
	<title>PayPal API <?=$OId?'Payment':'Error';?></title>
	</head>
<?php
}else{//移动端
?>
	<!DOCTYPE HTML>
	<html lang="<?=substr($c['lang'], 1);?>">
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta content="telephone=no" name="format-detection" />
	<title>PayPal API <?=$OId?'Payment':'Error';?></title>
	</head>
<?php }?>

<body alink="#0000FF" vlink="#0000FF">
<?php
if($OId){
	$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3)");
	if($order_row && (int)$order_row['PId']==1){ //自动切换成“普通Paypal支付”
		$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
		$account=str::json_data($payment_row['Attribute'], 'decode');
		$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
		$LogoPath=db::get_value('config', "GroupId='global' and Variable='LogoPath'", 'Value');
		
		$ceil_ary=array('TWD', 'JPY');//不需要金额小数点后两位
		$form_data = array(
			'cmd'			=>	'_xclick',
			'business'		=>	$account['Account'],
			'item_name'		=>	$order_row['OId'],
			'amount'		=>	@in_array($order_row['Currency'], $ceil_ary)?ceil($total_price):$total_price,
			'currency_code'	=>	$order_row['Currency'],
			'return'		=>	"{$domain}/cart/success/{$order_row['OId']}.html",
			'invoice'		=>	$order_row['OId'],
			'charset'		=>	'utf-8',
			'cancel_return'	=>	"{$domain}/account/orders/",
			'notify_url'	=>	"{$domain}/payment/paypal/notify/{$order_row['OId']}.html",
			'cpp_logo_image'=>	$domain.$LogoPath,
			'bn'			=>	'ueeshop_Cart',
		);
		(int)$payment_row['IsCreditCard']==1 && $form_data['landingpage']='billing';//信用卡支付
		
		echo '<form id="paypal_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		
		foreach((array)$form_data as $key=>$value){
			echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
		
		echo '<input type="submit" value="Submit" style="width:1px; height:1px; display:none;" /></form><script language="javascript">document.getElementById("paypal_form").submit();document.getElementById("paypal_form").innerHTML="";</script>';
		exit;
	}
}

$OId=$_GET['OOId'];
!$OId && $OId=$_SESSION['Gateway']['PaypalExcheckout']['nvpReqArray']['PAYMENTREQUEST_0_INVNUM'];
$order_row=db::get_one('orders', "OId='$OId'");
$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
if($order_row){//已生成订单
	$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
	$Currency=db::get_value('currency', "Currency='{$order_row['Currency']}'", 'Symbol');
}else{//未生产订单
	$total_price=sprintf('%01.2f', $_SESSION['Gateway']['PaypalExcheckout']['nvpReqArray']['PAYMENTREQUEST_0_AMT']);
	$Currency=db::get_value('currency', "Currency='{$_SESSION['Gateway']['PaypalExcheckout']['PAYMENTREQUEST_0_CURRENCYCODE']}'", 'Symbol');
}
if(!$_SESSION['Cart']['PaymentError']){//发送站内信
	$Content='';
	if(isset($_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'])){
		$Content="Error Number: {$_SESSION['Gateway']['PaypalExcheckout']['curl_error_no']}\r\nError Message: {$_SESSION['Gateway']['PaypalExcheckout']['curl_error_msg']}";
	}else{
		foreach((array)$resArray as $k=>$v){
			$Content.="{$k}: {$v}\r\n";
		}
	}
	$data=array(
		'UserId'	=>	0,
		'Subject'	=>	addslashes("{$c['orders']['status'][3]} ({$payment_row['Method']}) ({$OId})"),
		'Content'	=>	addslashes($Content),
		'IsRead'	=>	0,
		'AccTime'	=>	$c['time']
	);
	db::insert('user_message', $data);
	$_SESSION['Cart']['PaymentError']=1;//防止重复刷新提交
}
?>
<style>
*{font-family:Verdana;}
body{font-size:12px;}
.success_info{margin:0 auto; border:1px #f0f0f0 solid;}
.success_info .hd{margin:10px 10px 0; height:50px; line-height:50px; background:url(../../../static/themes/default/images/global/normal.png) no-repeat -580px -137px #f6f6f6;}
.success_info .hd>h3{font-size:18px; color:#66a355; margin:0; padding-left:70px; font-weight:normal;}
.success_info .hd_error{background-position:-812px -209px;}
.success_info .hd_error>h3{color:#c00;}
<?php if($is_mobile){?>
	.success_info .bd{width:90%; margin:11px auto 50px;}
<?php }else{?>
	.success_info .bd{width:350px; margin:11px auto 50px;}
<?php }?>
.success_info .bd .rows{line-height:23px; padding:3px 0; font-size:14px;}
.success_info .bd .title{height:inherit; line-height:30px; padding:10px 0;}
.success_info .bd .title strong{font-size:20px;}
.success_info .bd .textbtn{color:#963; font:14px/14px Verdana; text-decoration:none; padding:7px 20px; display:inline-block; text-shadow:0 1px 0 #fff9a0; -webkit-transition:border-color .218s; -moz-transition:border .218s; -o-transition:border-color .218s; transition:border-color .218s; background:#FDEFB7 url(../../../static/themes/default/images/cart/button-bg.png) repeat-x; border:solid 1px #d0af76; border-radius:2px; -webkit-border-radius:2px; -moz-border-radius:2px; margin:0 10px 0 0; cursor:pointer; outline:none;}
</style>
<div class="success_info">
	<div class="hd hd_error"><h3><?=$c['lang_pack']['cart']['paySent'];?></h3></div>
	<div class="bd">
		<div class="rows title">
			<label>
				<strong><?=$c['lang_pack']['cart']['errorfully'];?></strong>
			</label>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label><?=$c['lang_pack']['cart']['orderNo'];?></label>
			<span class="red"><?=$OId;?></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label><?=$c['lang_pack']['cart']['totalamount'];?>:</label>
			<span class="red"><?=$Currency.' '.$total_price;?></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label><?=$c['lang_pack']['cart']['status'];?>:</label>
			<span><?=$c['orders']['status'][3];?></span>
			<div class="clear"></div>
		</div>
		<?php
		if(isset($_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'])){
		?>
			<div class="rows">
				<label>Error Number:</label>
				<span><?=$_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'];?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>Error Message:</label>
				<span><?=$_SESSION['Gateway']['PaypalExcheckout']['curl_error_msg'];?></span>
				<div class="clear"></div>
			</div>
		<?php
		}else{
			foreach((array)$resArray as $k=>$v){
		?>
				<div class="rows">
					<label><?=$k;?>:</label>
					<span><?=$v;?></span>
					<div class="clear"></div>
				</div>
		<?php
			}
		}
		?>
		<div class="rows" style="margin-top:15px;">
			<label></label>
			<span><a href="javascript:;" class="textbtn" id="btn_close"><?=$c['lang_pack']['cart']['close'];?></a></span>
			<div class="clear"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	document.getElementById('btn_close').onclick=function(){
		window.opener=null;
		window.open('', '_self');
		window.close();
	}
</script>
<?php
/*
//it will print if any URL errors 
if(isset($_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'])){ 
?>
    <div style="text-align:center;">
        <table width="280" align="center">
            <tr>
                <td colspan="2" class="header">The PayPal API has returned an error!</td>
            </tr>
            <tr>
                <td>Error Number:</td>
                <td><?=$_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'];?></td>
            </tr>
            <tr>
                <td>Error Message:</td>
                <td><?=$_SESSION['Gateway']['PaypalExcheckout']['curl_error_msg'];?></td>
            </tr>
        </table>
    </div>
<?php
	unset($_SESSION['Gateway']);
}else{
	//If there is no URL Errors, Construct the HTML page with 
	//Response Error parameters.   
?>
    <div style="text-align:center;">
        <span style="color:blank; font-family:Verdana;"><b></b></span>
        <br />
		<br />
        <b>PayPal API Error</b><br><br>
    	<?php include('ShowAllResponse.php');?>
    </div>		
<?php }*/?>
</body>
</html>

