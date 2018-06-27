<?php
/***********************************************************
SetExpressCheckout.php

This is the main web page for the Express Checkout sample.
The page allows the user to enter amount and currency type.
It also accept input variable paymentType which becomes the
value of the PAYMENTACTION parameter.

When the user clicks the Submit button, ReviewOrder.php is
called.

Called by index.html.

Calls ReviewOrder.php.

***********************************************************/
// clearing the session before starting new API Call

$where='c.'.$c['where']['cart'];
(int)$_POST['ShoppingCId'] && $where.=" and c.CId='{$_POST['ShoppingCId']}'";
if($_POST['CartCId']){
	$CIdStr=$_POST['CartCId'];
	$where.=" and c.CId in({$CIdStr})";
}
if($_POST['SourceType']=='shipping_cost'){//来自产品详细页
	$ProInfo=explode('&', $_POST['ProInfo']);
	$ProId=(int)str_replace('ProId=', '', $ProInfo[2]);
	$Qty=(int)str_replace('Qty=', '', $ProInfo[3]);
	$where.=" and c.ProId='{$ProId}' and c.Qty='{$Qty}'";
	$cart_row[0]=db::get_one('shopping_cart c left join products p on c.ProId=p.ProId', $where, 'c.*, p.Name_en, p.PicPath_0', 'c.CId desc');
	$CIdStr=$cart_row[0]['CId'];
}else{
	$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $where, 'c.*, p.Name_en, p.PicPath_0', 'c.CId desc');
}
!count($cart_row) && js::location("/cart/");
$UserId=(int)$_SESSION['User']['UserId'];

//-------------------------------------------生成订单号-------------------------------------------
while(1){
	$OId=date('ymdHis', $c['time']).mt_rand(10,99);
	if(!db::get_row_count('orders', "OId='$OId'")){
		break;
	}
}
//-------------------------------------------生成订单号-------------------------------------------

$i=1;
$TotalQty=$ProductPrice=0;
$insert_sql='';
$products_ary=array();
foreach($cart_row as $v){
	$insert_sql.=($i%100==1)?"insert into `shopping_excheckout` (OId, CId, UserId, SessionId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Volume, Price, Qty, Property, PropertyPrice, OvId, Discount, Remark, Language, AccTime) VALUES":',';
	$Name=str::str_code($v['Name'.$c['lang']], 'addslashes');
	$SKU=str::str_code($v['SKU'], 'addslashes');
	$Property=str::str_code($v['Property'], 'addslashes');
	$Remark=str::str_code($v['Remark'], 'addslashes');
	$insert_sql.="('{$OId}', '{$v['CId']}', '{$v['UserId']}', '{$v['SessionId']}', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$Name}', '{$SKU}', '{$v['PicPath']}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Volume']}', '{$v['Price']}', '{$v['Qty']}', '{$Property}', '{$v['PropertyPrice']}', '{$v['OvId']}', '{$v['Discount']}', '{$Remark}', '{$v['Language']}', '{$c['time']}')";
	
	$products_ary[]=array(
		'ProId'			=>	$v['ProId'],
		'CId'			=>	$v['CId'],
		'Name'			=>	$v['Name'.$c['lang']],
		'Price'			=>	($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1),
		'Qty'			=>	$v['Qty'],
	);
	
	$ProductPrice+=($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty'];
	$TotalQty+=$v['Qty'];
	
	if($i++%100==0){
		db::query($insert_sql);
		$insert_sql='';
	}
}
$insert_sql!='' && db::query($insert_sql);

//使用优惠券
$CouponPrice=$CouponDiscount=0;
$coupon=@trim($_POST['order_coupon_code']);
if($coupon!=''){
	include($c['root_path'].'static/static/do_action/cart.php');
	$coupon_row=cart_module::get_coupon_info($coupon, $ProductPrice, (int)$_SESSION['User']['UserId'], $CIdStr);
	if($coupon_row['status']==1){
		$CouponCode=addslashes($coupon_row['coupon']);
		if($coupon_row['type']==1){	//CouponType: [0, 打折] [1, 减价格]
			$CouponPrice=$coupon_row['cutprice'];
		}else{
			if($coupon_row['pro_price']){
				$CouponPrice=($coupon_row['pro_price']*(100-$coupon_row['discount'])/100);
			}else{
				$CouponDiscount=(100-$coupon_row['discount'])/100;
			}
		}
	}else{
		unset($_SESSION['Cart']['Coupon']);
	}
}

//自定义参数： 产品数量、国家ID、快递ID、快递名、快递类型、是否购买快递保险、快递运费、快递保险费、优惠券代码
if(is_array($_POST['SId']) || is_array($_POST['ShippingInsurance'])){ //PC端是数组格式，移动端是JSON格式
	$_SId=$_ShippingInsurance=array();
	foreach((array)$_POST['SId'] as $k=>$v){
		if((int)$v<1){ js::location('/cart/'); exit; }
		$_SId['OvId_'.$k]=$v;
	}
	foreach((array)$_POST['ShippingInsurance'] as $k=>$v){ $_ShippingInsurance['OvId_'.$k]=$v; }
	$OverseaAry=explode(',', $_POST['order_shipping_oversea']);
	foreach($Oversea as $v){//丢失海外仓
		if(!$_SId['OvId_'.$v]){ js::location('/cart/'); exit; }
	}
	$_SId=str::json_data($_SId);
	$_ShippingInsurance=str::json_data($_ShippingInsurance);
}else{
	$error = 0;
	$_SId=$_POST['SId'];
	$_ShippingInsurance=$_POST['ShippingInsurance'];
	$SIdAry=@str::json_data(str_replace(array('OvId_', '\\'), '', $_SId), 'decode');//发货方式
	count($SIdAry) || $error=1;
	foreach($SIdAry as $k=>$v){
		if((int)$v<1) $error=1;
	}
	if($error){js::location("/cart/"); exit;}
}
$CUSTOM=(int)count($products_ary).'|'.(int)$_POST['CId'].'|'.trim($_SId).'|'.trim($_POST['ShippingExpress']).'|'.trim($_POST['ShippingMethodType']).'|'.trim($_ShippingInsurance).'|'.trim($_POST['ShippingPrice']).'|'.trim($_POST['ShippingInsurancePrice']).'|'.trim($coupon_row['coupon']);
$CUSTOM=stripslashes($CUSTOM);
$CUSTOM=str_replace('OvId_', '', $CUSTOM);

//运费、保险费
$_POST['ShippingPrice']=str::json_data(stripslashes($_POST['ShippingPrice']), 'decode');
$_POST['ShippingInsurancePrice']=str::json_data(stripslashes($_POST['ShippingInsurancePrice']), 'decode');
$ShippingPrice=$ShippingInsurancePrice=0;
foreach((array)$_POST['ShippingPrice'] as $k=>$v){
	$ShippingPrice+=(float)$v;
}
foreach((array)$_POST['ShippingInsurancePrice'] as $k=>$v){
	$ShippingInsurancePrice+=(float)$v;
}

//购物满减促销
$Discount=$DiscountPrice=$AfterPrice_1=0;
$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=($cutArr['EndTime']+30)){//下单时间差30(s)
	foreach((array)$cutArr['Data'] as $k=>$v){
		if(cart::iconv_price($ProductPrice, 2, '', 0)<cart::iconv_price($k, 2, '', 0)) break;
		if($cutArr['Type']==1){
			$DiscountPrice=cart::iconv_price($v[1], 2, '', 0);
		}else{
			$Discount=(100-$v[0]);
		}
		$AfterPrice_1=$ProductPrice-($cutArr['Type']==1?$v[1]:($ProductPrice*(100-$v[0])/100));
	}
}
//会员折扣优惠
$AfterPrice_0=$UserDiscount=0;
if((int)$_SESSION['User']['UserId']){//实时查询当前会员等级 && (int)$_SESSION['User']['Level']
	(int)$_SESSION['User']['Level']=db::get_value('user', "UserId='{$_SESSION['User']['UserId']}'", 'Level');
	$UserDiscount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
	$UserDiscount=($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100;
	$AfterPrice_0=$ProductPrice*($UserDiscount/100);
}
if($AfterPrice_0 && $AfterPrice_1){
	if($AfterPrice_0<$AfterPrice_1){//会员优惠价 < 全场满减价
		$Discount=$DiscountPrice=0;
	}else{
		$UserDiscount=0;
	}
}

//最低消费设置
$_total_price=$ProductPrice*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100)*(1-$CouponDiscount)-$CouponPrice-$DiscountPrice;//订单折扣后的总价
if((int)$c['config']['global']['LowConsumption'] && $_total_price<(float)cart::iconv_price($c['config']['global']['LowPrice'], 2, '', 0)){
	js::location("/cart/", $c['lang_pack']['cart']['error']['low_error']); exit;
}

//优惠券减去的金额
$_total=$ProductPrice*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100);
$cutprice=0;
if($CouponPrice){
	$cutprice=$CouponPrice;
}elseif($CouponDiscount){
	$cutprice=$_total-($_total*(1-$CouponDiscount));
}
?>
<div id="excheckout_loading">
    <form action="/payment/paypal_excheckout/ReviewOrder/" method="POST" id="paypal_excheckout_form" style="visibility:hidden;">
        <?php
		$len=count($products_ary);
		foreach($products_ary as $v){
			if($DiscountPrice){
				$price=$v['Price']-($DiscountPrice/($len*$v['Qty']));
			}elseif($Discount || $UserDiscount){
				$price=$v['Price']*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100);
			}else{
				$price=$v['Price'];
			}
		?>
            <input type="hidden" name="L_NAME[]" value="<?=$v['Name'];?>" />
            <input type="hidden" name="L_AMT[]" value="<?=sprintf('%01.2f', $price);?>" />
            <input type="hidden" name="L_QTY[]" value="<?=$v['Qty'];?>" />
        <?php }?>
		<input type="submit" name="" value="submit" />
        <input type="hidden" name="OId" value="<?=$OId;?>" />
        <input type="hidden" name="CUSTOM" value="<?=htmlspecialchars($CUSTOM);?>" />
        <input type="hidden" name="ShippingInsurance" value="<?=(int)$_POST['ShippingInsurance'];?>" />
        <input type="hidden" name="ShippingPrice" value="<?=sprintf('%01.2f', $ShippingPrice);?>" />
        <input type="hidden" name="ShippingInsurancePrice" value="<?=sprintf('%01.2f', $ShippingInsurancePrice);?>" />
        <input type="hidden" name="CouponCutPrice" value="<?=sprintf('%01.2f', cart::iconv_price($cutprice, 2, '', 0));?>" />
    </form>
    <script>
    	document.getElementById('paypal_excheckout_form').submit();
    </script>
</div>
