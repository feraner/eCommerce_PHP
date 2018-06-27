<?php

//print_r($_SESSION['reshash']);

//-------------------------------------------------------------------处理返回信息（start）-------------------------------------------------------------------
$CUSTOM=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_CUSTOM'];	//自定义字段，用“|”分割，【产品数量、国家ID、快递ID、快递名、快递类型、是否购买快递保险】
$info_ary=@explode('|', $CUSTOM);
$shipping_ary=array(
	'TotalQty'			=>	(int)$info_ary[0],
	'ShippingCId'		=>	(int)$info_ary[1],
	'SId'				=>	(int)$info_ary[2],
	'ShippingExpress'	=>	trim($info_ary[3]),
	'ShippingMethodType'=>	trim($info_ary[4]),
	'ShippingInsurance'	=>	(int)$info_ary[5],
	'CouponCode'		=>	trim($info_ary[6])
);

$OId=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INVNUM'];	//订单号
//$OrdersPrice=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_AMT'];	//订单总价
//$ProductPrice=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_ITEMAMT'];	//产品总价
//$handling_price=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_HANDLINGAMT'];	//手续费
$shipping_ary['ShippingPrice']=(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPPINGAMT'];	//商品运费
$shipping_ary['ShippingInsurancePrice']=$shipping_ary['ShippingInsurance']==1?(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INSURANCEAMT']:0.00;	//快递保险费
$CouponCutPrice=(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPDISCAMT'];	//优惠券抵扣金额

//$EMAIL		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['EMAIL']; //邮箱
//$FirstName	= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['FIRSTNAME']; //姓
//$LastName		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['LASTNAME'];	//名
$Currency		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_CURRENCYCODE'];	//币种
$CountryAcronym	= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'];	//国家缩写编码
$Country		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME'];	//国家名称

//-------------------------------------------------------------------处理返回信息（end）-------------------------------------------------------------------



$PId=2;
$payment_row=db::get_one('payment', "PId=2");
$payment_row['IsUsed']!=1 &&  js::location("/cart/");

$cart_row=db::get_all('shopping_excheckout c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']} and c.OId='{$OId}'", 'c.*, p.Name'.$c['lang'].', p.PicPath_0', 'c.Id asc');
!count($cart_row) && js::location("/cart/");

//-------------------------------------------------------------------地址处理（start）-------------------------------------------------------------------

//网站上选择的国家与Paypal地址上的国家不一致
$country_row=db::get_one('country', "CId='{$shipping_ary['ShippingCId']}'");
if($country_row['Acronym']!=strtoupper($CountryAcronym)){
	db::delete('shopping_excheckout', "OId='{$OId}'");
	js::location("/cart/");
}
$address_ary=array(
	'FirstName'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTONAME'],		//姓
	'AddressLine1'	=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOSTREET'],		//地址
	'City'			=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCITY'], 		//城市
	'State'			=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOSTATE'],		//州、省
	'ZipCode'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOZIP'],			//邮编
	'PhoneNumber'	=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOPHONENUM'],	//电话
	'CId'			=>	$country_row['CId'],			//国害Id
	'Country'		=>	$country_row['Country'],		//国家名称
	'CountryCode'	=>	$country_row['CountryCode'],	//国家电话编号
);


$user_data=user::check_login('', 1);
!$user_data && $user_data=array('UserId'=>0, 'Email'=>$_SESSION['Gateway']['PaypalExcheckout']['reshash']['EMAIL']);
//设置账单地址
$bill_ary=array();
if((int)$user_data['UserId']){
	$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$user_data['fetch_where']." and a.IsBillingAddress=1", 'a.*,c.Country,s.States as StateName'));
	$bill_tax_ary=user::get_tax_info($bill_row);
	$bill_ary=array(
		'FirstName'		=>	$bill_row['FirstName'],		//姓
		'LastName'		=>	$bill_row['LastName'],		//名
		'AddressLine1'	=>	$bill_row['AddressLine1'],	//地址1
		'AddressLine2'	=>	$bill_row['AddressLine2'],	//地址2
		'CountryCode'	=>	$bill_row['CountryCode'],	//国家电话编号
		'PhoneNumber'	=>	$bill_row['PhoneNumber'],	//电话
		'City'			=>	$bill_row['City'], 			//城市
		'State'			=>	$bill_row['StateName']?$bill_row['StateName']:$bill_row['State'],//州、省
		'SId'			=>	$bill_row['SId'],			//州、省Id
		'Country'		=>	$bill_row['Country'],		//国家名称
		'CId'			=>	$bill_row['CId'],			//国家Id
		'ZipCode'		=>	$bill_row['ZipCode'],		//邮编
		'CodeOption'	=>	$bill_tax_ary['CodeOption'],
		'CodeOptionId'	=>	$bill_tax_ary['CodeOptionId'],
		'TaxCode'		=>	$bill_tax_ary['TaxCode'],
	);
	unset($bill_row, $bill_tax_ary);
}else{
	$bill_ary=$address_ary;
}
//-------------------------------------------------------------------地址处理（end）-------------------------------------------------------------------


$cartInfo=db::get_one('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", "sum((Price+PropertyPrice)*Qty) as ProductPrice, sum(Weight*Qty) as totalWeight, sum(Volume*Qty) as totalVolume");
$ProductPrice=$cartInfo['ProductPrice'];//购物车产品总价
$totalWeight=$cartInfo['totalWeight'];//产品总重量
$totalVolume=$cartInfo['totalVolume'];//产品总体积

//---------------------------------------------------------优惠券处理(start)----------------------------------------------------------------------------------
$CouponCode='';
$CouponPrice=$CouponDiscount=0;
if($shipping_ary['CouponCode']){
	$coupon_row=db::get_one('sales_coupon', "CouponNumber='{$shipping_ary['CouponCode']}'");
	//$data=array('coupon'=>$shipping_ary['CouponCode']);
	if(!$coupon_row || $c['time']<$coupon_row['StartTime'] || $c['time']>$coupon_row['EndTime'] || ($coupon_row['UseNum'] && $coupon_row['BeUseTimes']>=$coupon_row['UseNum']) || $ProductPrice<$coupon_row['UseCondition']){
		$coupon_ary=array();
	}else{
		$coupon_ary=array(
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
		if(!(int)$user_data['UserId'] ||((int)$coupon_row['IsUser']==1 && (int)$coupon_row['UserId'] && (int)$coupon_row['UserId']!=(int)$user_data['UserId']) || ((int)$coupon_row['IsUser']==2 && @in_array((int)$user_data['UserId'], $UesdAry))){
			$coupon_ary=array();
		}
	}
	
	if($coupon_ary['status']==1){
		$CouponCode = addslashes($coupon_ary['coupon']);
		if($coupon_ary['type']==1){	//CouponType: [0, 打折] [1, 减价格]
			$CouponPrice = $coupon_ary['cutprice'];
		}else{
			$CouponDiscount = (100 - $coupon_ary['discount']) / 100;
		}
		$coupon_data=array(
			'UsedTime'		=>	$c['time'],
			'BeUseTimes'	=>	$coupon_ary['beusetimes']+1
		);
		if((int)$coupon_ary['isuser']){//绑定会员
			$UesdAry=array();
			$UesdAry=@explode('|', $coupon_ary['useduser']);
			if((int)$coupon_ary['isuser']==2 && (int)$user_data['UserId'] && @in_array($user_data['UserId'], $UesdAry)){//所有会员都可使用，并且每个会员只能使用一次
				$coupon_data['UsedUser']=$coupon_ary['useduser']?"|{$user_data['UserId']}":$user_data['UserId'];
			}
		}
		db::update('sales_coupon', "CouponNumber='{$CouponCode}'", $coupon_data);
	}
}
//---------------------------------------------------------优惠券处理(end)----------------------------------------------------------------------------------

//---------------------------------------------------------购物满减促销(start)----------------------------------------------------------------------------------
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
//---------------------------------------------------------购物满减促销(end)----------------------------------------------------------------------------------
//会员折扣优惠
$AfterPrice_0=$UserDiscount=0;
if((int)$user_data['UserId']){//实时查询当前会员等级 && (int)$user_data['Level']
	(int)$user_data['Level']=db::get_value('user', "UserId='{$user_data['UserId']}'", 'Level');
	$UserDiscount=(float)db::get_value('user_level', "LId='{$user_data['Level']}' and IsUsed=1", 'Discount');
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
	js::location("/cart/");
}

$order_data=array(
	/*******************订单基本信息*******************/
	'OId'					=>	$OId,	//订单号
	'UserId'				=>	$user_data['UserId'],
	'Source'				=>	ly200::is_mobile_client(0)?1:0,
	'Email'					=>	$user_data['Email'],
	'Discount'				=>	$Discount,
	'DiscountPrice'			=>	$DiscountPrice,
	'UserDiscount'			=>	$UserDiscount,
	'ProductPrice'			=>	$ProductPrice,
	'Currency'				=>	$Currency,
	'ManageCurrency'		=>	$_SESSION['ManageCurrency']['Currency'],
	'TotalWeight'			=>	$totalWeight,
	'TotalVolume'			=>	$totalVolume,
	'OrderTime'				=>	$c['time'],
	/*******************优惠券信息*******************/
	'CouponCode'			=>	$CouponCode,
	'CouponPrice'			=>	$CouponPrice,
	'CouponDiscount'		=>	$CouponDiscount,
	/*******************收货地址*******************/
	'ShippingFirstName'		=>	$address_ary['FirstName'],
	'ShippingLastName'		=>	'',
	'ShippingAddressLine1'	=>	$address_ary['AddressLine1'],
	'ShippingCountryCode'	=>	'+'.$address_ary['CountryCode'],
	'ShippingPhoneNumber'	=>	$address_ary['PhoneNumber'],
	'ShippingCity'			=>	$address_ary['City'],
	'ShippingState'			=>	$address_ary['State'],
	'ShippingSId'			=>	0,
	'ShippingCountry'		=>	$address_ary['Country'],
	'ShippingCId'			=>	$address_ary['CId'],
	'ShippingZipCode'		=>	$address_ary['ZipCode'],
	/*'ShippingCodeOption'	=>	$tax_ary['CodeOption'],
	'ShippingCodeOptionId'	=>	$tax_ary['CodeOptionId'],
	'ShippingTaxCode'		=>	$tax_ary['TaxCode'],*/
	/*******************账单地址*******************/
	'BillFirstName'			=>	$bill_ary['FirstName'],
	'BillLastName'			=>	$bill_ary['LastName'],
	'BillAddressLine1'		=>	$bill_ary['AddressLine1'],
	'BillAddressLine2'		=>	$bill_ary['AddressLine2'],
	'BillCountryCode'		=>	'+'.$bill_ary['CountryCode'],
	'BillPhoneNumber'		=>	$bill_ary['PhoneNumber'],
	'BillCity'				=>	$bill_ary['City'],
	'BillState'				=>	$bill_ary['State'],
	'BillSId'				=>	$bill_ary['SId'],
	'BillCountry'			=>	$bill_ary['Country'],
	'BillCId'				=>	$bill_ary['CId'],
	'BillZipCode'			=>	$bill_ary['ZipCode'],
	'BillCodeOption'		=>	$bill_ary['CodeOption'],
	'BillCodeOptionId'		=>	$bill_ary['CodeOptionId'],
	'BillTaxCode'			=>	$bill_ary['TaxCode'],
	/*******************发货方式*******************/
	'ShippingExpress'		=>	$shipping_ary['ShippingExpress'],
	'ShippingMethodSId'		=>	$shipping_ary['SId'],
	'ShippingMethodType'	=>	$shipping_ary['ShippingMethodType'],
	'ShippingInsurance'		=>	$shipping_ary['ShippingInsurance'],
	'ShippingPrice'			=>	$shipping_ary['ShippingPrice'],
	'ShippingInsurancePrice'=>	$shipping_ary['ShippingInsurancePrice'],
	/*******************付款方式*******************/
	'PId'					=>	$payment_row['PId'],
	'PaymentMethod'			=>	$payment_row['Name'.$c['lang']],
	'PayAdditionalFee'		=>	$payment_row['AdditionalFee'],
);

//生成订单
db::insert('orders', $order_data);
$OrderId=db::get_insert_id();

$i=1;
$CId_ary=array();
$insert_sql='';
$order_pic_dir=$c['orders']['path'].date('ym', $c['time'])."/{$OId}/";
!is_dir($c['root_path'].$order_pic_dir) && file::mk_dir($order_pic_dir);
foreach($cart_row as $v){
	$CId_ary[]=$v['CId'];
	$ext_name=file::get_ext_name($v['PicPath']);
	$ImgPath=$order_pic_dir.str::rand_code().'.'.$ext_name;
	@copy($c['root_path'].$v['PicPath'].'.240x240.'.$ext_name, $c['root_path'].$ImgPath);
	$Name=str::str_code($v['Name'.$c['lang']], 'addslashes');
	$SKU=str::str_code($v['SKU'], 'addslashes');
	$Property=str::str_code($v['Property'], 'addslashes');
	$Remark=str::str_code($v['Remark'], 'addslashes');
	$insert_sql.=($i%10==1)?"insert into `orders_products_list` (OrderId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Price, Qty, Property, PropertyPrice, Discount, Remark, Language, AccTime) VALUES":',';
	$insert_sql.="('$OrderId', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$Name}', '{$SKU}', '{$ImgPath}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Price']}', '{$v['Qty']}', '{$Property}', '{$v['PropertyPrice']}', '{$v['Discount']}', '{$Remark}', '{$v['Language']}', '{$c['time']}')";
	if($i++%10==0){
		db::query($insert_sql);
		$insert_sql='';
	}
}
$insert_sql!='' && db::query($insert_sql);

if((int)$c['config']['global']['AutoRegister'] && !db::get_row_count('user', "Email='{$user_data['Email']}'")){//下单后自动注册会员
	//随机生成密码
	$PasswordStr='';
	$char='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	for($i=0; $i<10; ++$i){
		$PasswordStr.=substr($char, rand(0,strlen($char)-1), 1);
	}
	$Password=ly200::password($PasswordStr);
	$time=$c['time'];
	$ip=ly200::get_ip();
	$data=array(
		'Language'		=>	'en',
		'Email'			=>	$user_data['Email'],
		'Password'		=>	$Password,
		'RegTime'		=>	$time,
		'RegIp'			=>	$ip,
		'LastLoginTime'	=>	$time,
		'LastLoginIp'	=>	$ip,
		'LoginTimes'	=>	1,
		'Status'		=>	1,
		'IsLocked'		=>	0
	);
	db::insert('user', $data);
	$UserId=db::get_insert_id();
	$data_oth=array(
		'UserId'		=>	$UserId,
		'FirstName'		=>	$address_ary['FirstName'],
		'LastName'		=>	$address_ary['LastName'],
		'AddressLine1'	=>	addslashes($address_ary['AddressLine1']),
		'City'			=>	addslashes($address_ary['City']),
		'State'			=>	addslashes($address_ary['StateName']?$address_ary['StateName']:$address_ary['State']),
		'SId'			=>	$address_ary['SId'],
		'CId'			=>	$address_ary['CId'],
		'CodeOption'	=>	$tax_ary['CodeOption'],
		'TaxCode'		=>	$tax_ary['TaxCode'],
		'ZipCode'		=>	$address_ary['ZipCode'],
		'CountryCode'	=>	$address_ary['CountryCode'],
		'PhoneNumber'	=>	$address_ary['PhoneNumber'],
		'AccTime'		=>	$time
	);
	db::insert('user_address_book', $data_oth);//Shipping Address
	$data_oth['IsBillingAddress']=1;
	db::insert('user_address_book', $data_oth);//Billing Address
	$_SESSION['User']=$data;
	$_SESSION['User']['UserId']=$UserId;
	db::update('orders', "OrderId='$OrderId'", array('UserId'=>$UserId));
	//更新会员等级
	$LId=(int)db::get_value('user_level', 'IsUsed=1 and FullPrice<=0', 'LId');
	if(!$_SESSION['User']['IsLocked'] && $LId){
		db::update('user', "UserId='$UserId'", array('Level'=>$LId));
		$_SESSION['User']['Level']=$LId;
	}
	user::operation_log($UserId, '会员注册');
	include($c['static_path'].'/inc/mail/create_account.php');
	ly200::sendmail($user_data['Email'], 'Welcome to '.ly200::get_domain(0), $mail_contents);
}

db::delete('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'");
if(count($CId_ary)){
	$CId_List=@implode(',', $CId_ary);
	db::delete('shopping_cart', "CId in($CId_List)");
}
unset($shipping_ary, $cart_row, $address_ary, $bill_ary, $order_data, $cart_attr, $cart_attr_value, $cart_attr_data, $json_id_ary, $json_value_ary, $order_list);

//订单无产品
$row_count=(int)db::get_row_count('orders_products_list', "OrderId='{$OrderId}'");
if(!$row_count){
	db::delete('orders', "OrderId='{$OrderId}'");
	js::location('/');
}

orders::orders_log((int)$user_data['UserId'], $user_data['UserId']?($_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']):'System', $OrderId, 1, "Place an Order: ".$OId);
if((int)$c['config']['global']['LessStock']==0){//下单减库存
	$orders_row=db::get_one('orders', "OrderId='$OrderId'");
	orders::orders_products_update(1, $orders_row);
}

if((int)$c['config']['global']['CheckoutEmail']){//下单后邮件通知
	$ToAry=array($user_data['Email']);
	include($c['static_path'].'/inc/mail/order_create.php');
	$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
	ly200::sendmail($ToAry, "Place an Order: ".$OId, $mail_contents);
	//$c['config']['global']['OrdersSms'] && ly200::sendsms($c['config']['global']['OrdersSms'], "您的网站有新的订单，订单号：{$OId}");
	orders::orders_sms($OId);	
}

ob_start();
print_r($_SESSION['Gateway']['PaypalExcheckout']['reshash']);
print_r($_GET);
print_r($_POST);
echo "\r\n\r\n$payment_result";
$log=ob_get_contents();
ob_end_clean();
file::write_file('/_pay_log_/paypal_excheckout/'.date('Y_m/', $c['time']), "{$OId}.txt", $log);	//把返回数据写入文件

js::location("/?m=cart&a=excheckout&d=DoExpressCheckoutPayment&OId=$OId");
//js_location("DoExpressCheckoutPayment.php?OId=$OId");
exit();


