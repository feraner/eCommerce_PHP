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
//$_POST['CartCId'] && $where.=" and c.CId in('{$_POST['CartCId']}')";
$_POST['CartCId'] && $where.=" and c.CId in({$_POST['CartCId']})";
$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $where, 'c.*, p.Name_en, p.PicPath_0', 'c.CId asc');
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
	$insert_sql.=($i%100==1)?"insert into `shopping_excheckout` (OId, CId, UserId, SessionId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Volume, Price, Qty, Property, PropertyPrice, Discount, Remark, Language, AccTime) VALUES":',';
	$insert_sql.="('{$OId}', '{$v['CId']}', '{$v['UserId']}', '{$v['SessionId']}', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$v['Name']}', '{$v['SKU']}', '{$v['PicPath']}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Volume']}', '{$v['Price']}', '{$v['Qty']}', '{$v['Property']}', '{$v['PropertyPrice']}', '{$v['Discount']}', '{$v['Remark']}', '{$v['Language']}', '{$c['time']}')";
	
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
$coupon=@trim($_POST['order_coupon_code']);
if($coupon!=''){
	$coupon_row=db::get_one('sales_coupon', "CouponNumber='$coupon'");
	$data=array('coupon'=>$coupon);
	if(!$coupon_row || $c['time']<$coupon_row['StartTime'] || $c['time']>$coupon_row['EndTime'] || ($coupon_row['UseNum'] && $coupon_row['BeUseTimes']>=$coupon_row['UseNum']) || $ProductPrice<$coupon_row['UseCondition']){
		$info=array();
	}else{
		$info=array(
			'status'	=>	1,
			'coupon'	=>	$coupon_row['CouponNumber'],
			'type'		=>	$coupon_row['CouponType'],
			'discount'	=>	$coupon_row['Discount'],
			'cutprice'	=>	$coupon_row['Money'],
			'beusetimes'=>	$coupon_row['BeUseTimes'],
			'end'		=>	$coupon_row['EndTime'],
			'isuser'	=>	$coupon_row['IsUser'],
			'useduser'	=>	$coupon_row['UsedUser']
		);
	}
	
	if((int)$coupon_row['IsUser']){//绑定会员
		$UesdAry=array();
		$UesdAry=@explode('|', $coupon_row['UsedUser']);
		if(!$UserId ||((int)$coupon_row['IsUser']==1 && (int)$coupon_row['UserId'] && (int)$coupon_row['UserId']!=$UserId) || ((int)$coupon_row['IsUser']==2 && @in_array($UserId, $UesdAry))){
			$info=array();
		}
	}
	//$info=cart::get_coupon_info($coupon, $ProductPrice, (int)$_SESSION['User']['UserId']);
	
	if($info['status']==1){
		$CouponCode = addslashes($info['coupon']);
		if($info['type']==1){	//CouponType: [0, 打折] [1, 减价格]
			$CouponPrice = $info['cutprice'];
		}else{
			$CouponDiscount = (100 - $info['discount']) / 100;
		}
		$coupon_data=array(
			'UsedTime'		=>	$c['time'],
			'BeUseTimes'	=>	$info['beusetimes']+1
		);
		if((int)$info['isuser']){//绑定会员
			$UesdAry=array();
			$UesdAry=@explode('|', $info['useduser']);
			if((int)$info['isuser']==2 && $_SESSION['User']['UserId'] && @in_array($_SESSION['User']['UserId'], $UesdAry)){//所有会员都可使用，并且每个会员只能使用一次
				$coupon_data['UsedUser']=$info['useduser']?"|{$_SESSION['User']['UserId']}":$_SESSION['User']['UserId'];
			}
		}
		db::update('sales_coupon', "CouponNumber='{$CouponCode}'", $coupon_data);
	}else{
		unset($_SESSION['Cart']['Coupon']);
	}
}

//产品数量、国家ID、快递ID、快递名、快递类型、是否购买快递保险、优惠券代码
$CUSTOM=(int)count($products_ary).'|'.(int)$_POST['CId'].'|'.(int)$_POST['SId'].'|'.trim($_POST['ShippingExpress']).'|'.trim($_POST['ShippingMethodType']).'|'.(int)$_POST['ShippingInsurance'].'|'.trim($info['coupon']);

//购物满减促销
/*
$IsDiscount=0;
$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
if($cutArr['IsUsed']==1 && $cutArr['UseCondition']<=$ProductPrice && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
	$IsDiscount=1;
	$eachPrice=sprintf('%01.2f', $cutArr['Money']/$TotalQty);
}
*/
$Discount=$DiscountPrice=$AfterPrice_1=0;
$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=($cutArr['EndTime']+30)){//下单时间差30(s)
	foreach((array)$cutArr['Data'] as $k=>$v){
		if(cart::iconv_price($ProductPrice, 2)<$k) break;
		if($cutArr['Type']==1){
			$DiscountPrice=$v[1];
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
if((int)$c['config']['global']['LowConsumption'] && $_total_price<(float)cart::iconv_price($c['config']['global']['LowPrice'], 2)){
	//ly200::e_json(cart::iconv_price($c['config']['global']['LowPrice']), -5);
	js::location("/cart/", $c['lang_pack']['cart']['error']['low_error']); exit;
}

//优惠券减去的金额
$_total=$ProductPrice*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100);
if($CouponPrice){
	$cutprice=$CouponPrice;
}else{
	$cutprice=$_total-($_total*(1-$CouponDiscount));
}
?>
<div id="excheckout_loading">
    <form action="/cart/excheckout/ReviewOrder.html" method="POST"  id="paypal_excheckout_form" style="visibility:hidden;">
        <?php
		$len=count($products_ary);
		foreach($products_ary as $v){
			if($DiscountPrice){
				$price=$v['Price']-($DiscountPrice/$len);
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
        <input type="hidden" name="CUSTOM" value="<?=$CUSTOM;?>" />
        <input type="hidden" name="ShippingInsurance" value="<?=(int)$_POST['ShippingInsurance'];?>" />
        <input type="hidden" name="ShippingPrice" value="<?=sprintf('%01.2f', $_POST['ShippingPrice']);?>" />
        <input type="hidden" name="ShippingInsurancePrice" value="<?=sprintf('%01.2f', $_POST['ShippingInsurancePrice']);?>" />
        <input type="hidden" name="CouponCutPrice" value="<?=sprintf('%01.2f', $cutprice);?>" />
    </form>
    <script>
    	document.getElementById('paypal_excheckout_form').submit();
    </script>
</div>
