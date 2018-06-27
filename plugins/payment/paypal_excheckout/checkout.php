<?php

//print_r($_SESSION['reshash']);

//-------------------------------------------------------------------处理返回信息（start）-------------------------------------------------------------------
$CUSTOM=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_CUSTOM'];	//自定义字段，用“|”分割，【产品数量、国家ID、快递ID、快递名、快递类型、是否购买快递保险、快递运费、快递保险费、优惠券代码】
$CUSTOM=htmlspecialchars_decode($CUSTOM);
$info_ary=@explode('|', $CUSTOM);
$shipping_ary=array(
	'TotalQty'				=>	(int)$info_ary[0],
	'ShippingCId'			=>	(int)$info_ary[1],
	'ShippingOvSId'			=>	str::json_data(str_replace('\\', '', $info_ary[2]), 'decode'),
	'ShippingOvExpress'		=>	str::json_data(str_replace('\\', '', $info_ary[3]), 'decode'),
	'ShippingOvType'		=>	str::json_data(str_replace('\\', '', $info_ary[4]), 'decode'),
	'ShippingOvInsurance'	=>	str::json_data(str_replace('\\', '', $info_ary[5]), 'decode'),
	'ShippingOvPrice'		=>	str::json_data(str_replace('\\', '', $info_ary[6]), 'decode'),
	'ShippingOvInsurancePrice'=>str::json_data(str_replace('\\', '', $info_ary[7]), 'decode'),
	'CouponCode'			=>	trim($info_ary[8])
);

$OId=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INVNUM']; //订单号
//$OrdersPrice=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_AMT']; //订单总价
//$ProductPrice=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_ITEMAMT']; //产品总价
//$handling_price=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_HANDLINGAMT']; //手续费
$shipping_ary['ShippingPrice']=(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPPINGAMT']; //商品运费
//$shipping_ary['ShippingInsurancePrice']=$shipping_ary['ShippingInsurance']==1?(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INSURANCEAMT']:0.00; //快递保险费
$shipping_ary['ShippingInsurancePrice']=(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_INSURANCEAMT']; //快递保险费
$CouponCutPrice=(float)$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPDISCAMT'];	//优惠券抵扣金额

//$EMAIL		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['EMAIL']; //邮箱
//$FirstName	= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['FIRSTNAME']; //姓
//$LastName		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['LASTNAME'];	//名
$Currency		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_CURRENCYCODE']; //币种
$CountryAcronym	= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']; //国家缩写编码
$Country		= $_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME']; //国家名称

/*if($Currency!=$_SESSION['ManageCurrency']['Currency']){ //运费和保险费，不是美元就自动换算成“后台默认货币”，用于保存默认“后台默认货币”的数据
	$shipping_ary['ShippingPrice']=cart::iconv_price($shipping_ary['ShippingPrice'], 2, $_SESSION['ManageCurrency']['Currency'], 0);
	$shipping_ary['ShippingInsurancePrice']=cart::iconv_price($shipping_ary['ShippingInsurancePrice'], 2, $_SESSION['ManageCurrency']['Currency'], 0);
}*/
//转换后台默认汇率
$ExchangeRate=db::get_value('currency', "Currency='{$Currency}'", 'ExchangeRate');
$ManageExchangeRate=db::get_value('currency', 'ManageDefault=1', 'ExchangeRate');
$shipping_ary['ShippingPrice']=cart::currency_price($shipping_ary['ShippingPrice'], $ExchangeRate, $ManageExchangeRate);
$shipping_ary['ShippingInsurancePrice']=cart::currency_price($shipping_ary['ShippingInsurancePrice'], $ExchangeRate, $ManageExchangeRate);
$CouponCutPrice=cart::currency_price($CouponCutPrice, $ExchangeRate, $ManageExchangeRate);

//-------------------------------------------------------------------处理返回信息（end）-------------------------------------------------------------------



$PId=2;
$payment_row=db::get_one('payment', "PId=2");
$payment_row['IsUsed']!=1 && js::location('/cart/');

$cart_row=db::get_all('shopping_excheckout c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']} and c.OId='{$OId}'", 'c.*, p.Name'.$c['lang'].', p.PicPath_0', 'c.Id asc');
!count($cart_row) && js::location('/cart/');

//-------------------------------------------------------------------地址处理（start）-------------------------------------------------------------------

//网站上选择的国家与Paypal地址上的国家不一致
$country_row=db::get_one('country', "CId='{$shipping_ary['ShippingCId']}'");
$Acronym=$country_row['Acronym'];
$Acronym=='CN' && $Acronym='C2';//Paypal那边的中国编号是C2
if($Acronym!=strtoupper($CountryAcronym)){
	db::delete('shopping_excheckout', "OId='{$OId}'");

	ob_start();
	echo (ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n";
	echo date('Y-m-d H:i:s', $c['time'])."\r\n\r\n"."Website set country: {$Acronym} \r\n\r\n Paypal set country: {$CountryAcronym}\r\n\r\n";
	print_r($_SESSION['Gateway']['PaypalExcheckout']);
	print_r($_GET);
	print_r($_POST);
	$log=ob_get_contents();
	ob_end_clean();
	file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-error.txt", $log);	//把返回数据写入文件

	js::location('/cart/', 'Parameters(Country) in session do not match those in PayPal API calls. (The Country of choice are inconsistent)');
}

//设置Paypal买家账号的收货地址信息
$PhoneNumber=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOPHONENUM'];
!$PhoneNumber && $PhoneNumber=$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PHONENUM'];
$shipto_ary=array(
	'Account'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['EMAIL'],
	'FirstName'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTONAME'],
	'LastName'		=>	'',
	'AddressLine1'	=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOSTREET'],
	'AddressLine2'	=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOSTREET2'],
	'CountryCode'	=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'],
	'PhoneNumber'	=>	$PhoneNumber,
	'City'			=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCITY'],
	'State'			=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOSTATE'],
	'Country'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME'],
	'ZipCode'		=>	$_SESSION['Gateway']['PaypalExcheckout']['reshash']['PAYMENTREQUEST_0_SHIPTOZIP'],
);
$shipto_ary['CId']=(int)db::get_value('country', "Country='{$shipto_ary['Country']}'", 'CId');

$user_data=user::check_login('', 1);
!$user_data && $user_data=array('UserId'=>0, 'Email'=>$_SESSION['Gateway']['PaypalExcheckout']['reshash']['EMAIL']);

$address_ary=$bill_ary=array();
if((int)$user_data['UserId']){
	//设置收货地址
	$ship_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$user_data['fetch_where']." and a.IsBillingAddress=0", 'a.*,c.Country,s.States as StateName'));
	$ship_tax_ary=user::get_tax_info($ship_row);
	$address_ary=array(
		'FirstName'		=>	$ship_row['FirstName'],		//姓
		'LastName'		=>	$ship_row['LastName'],		//名
		'AddressLine1'	=>	$ship_row['AddressLine1'],	//地址1
		'AddressLine2'	=>	$ship_row['AddressLine2'],	//地址2
		'CountryCode'	=>	$ship_row['CountryCode'],	//国家电话编号
		'PhoneNumber'	=>	$ship_row['PhoneNumber'],	//电话
		'City'			=>	$ship_row['City'], 			//城市
		'State'			=>	$ship_row['StateName']?$ship_row['StateName']:$ship_row['State'],//州、省
		'SId'			=>	$ship_row['SId'],			//州、省Id
		'Country'		=>	$ship_row['Country'],		//国家名称
		'CId'			=>	$ship_row['CId'],			//国家Id
		'ZipCode'		=>	$ship_row['ZipCode'],		//邮编
		'CodeOption'	=>	$ship_tax_ary['CodeOption'],
		'CodeOptionId'	=>	$ship_tax_ary['CodeOptionId'],
		'TaxCode'		=>	$ship_tax_ary['TaxCode'],
	);
	//设置账单地址
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
	unset($ship_row, $ship_tax_ary, $bill_row, $bill_tax_ary);
}else{
	$address_ary=$bill_ary=$shipto_ary;
}
//-------------------------------------------------------------------地址处理（end）-------------------------------------------------------------------


//$cartInfo=db::get_one('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", "sum((Price+PropertyPrice)*Qty) as ProductPrice, sum(Weight*Qty) as totalWeight, sum(Volume*Qty) as totalVolume");
$cartInfo=db::get_one('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'", "sum((Price+PropertyPrice)*Discount/100*Qty) as ProductPrice, sum(Weight*Qty) as totalWeight, sum(Volume*Qty) as totalVolume");
$ProductPrice=$cartInfo['ProductPrice'];//购物车产品总价
$totalWeight=$cartInfo['totalWeight'];//产品总重量
$totalVolume=$cartInfo['totalVolume'];//产品总体积

//-------------------------------------------------------------------快递处理（start）-------------------------------------------------------------------
if(count($shipping_ary['ShippingOvSId'])>1){ //多个发货地
	$shipping_ary['ShippingExpress']=$shipping_ary['ShippingMethodSId']=$shipping_ary['ShippingMethodType']=$shipping_ary['ShippingInsurance']='';
	$shipping_ary['ShippingOvExpress']=str::json_data($shipping_ary['ShippingOvExpress']);
	$shipping_ary['ShippingOvSId']=str::json_data($shipping_ary['ShippingOvSId']);
	$shipping_ary['ShippingOvType']=str::json_data($shipping_ary['ShippingOvType']);
	$shipping_ary['ShippingOvInsurance']=str::json_data($shipping_ary['ShippingOvInsurance']);
	$shipping_ary['ShippingOvPrice']=str::json_data($shipping_ary['ShippingOvPrice']);
	$shipping_ary['ShippingOvInsurancePrice']=str::json_data($shipping_ary['ShippingOvInsurancePrice']);
}else{ //单个发货地
	$shipping_ary['ShippingExpress']=implode('', $shipping_ary['ShippingOvExpress']);
	$shipping_ary['ShippingMethodSId']=implode('', $shipping_ary['ShippingOvSId']);
	$shipping_ary['ShippingMethodType']=implode('', $shipping_ary['ShippingOvType']);
	$shipping_ary['ShippingInsurance']=implode('', $shipping_ary['ShippingOvInsurance']);
	$shipping_ary['ShippingOvExpress']=$shipping_ary['ShippingOvSId']=$shipping_ary['ShippingOvType']=$shipping_ary['ShippingOvInsurance']=$shipping_ary['ShippingOvPrice']=$shipping_ary['ShippingOvInsurancePrice']='';
}
//-------------------------------------------------------------------快递处理（end）-------------------------------------------------------------------

//---------------------------------------------------------优惠券处理(start)----------------------------------------------------------------------------------
$CouponCode='';
$CouponPrice=$CouponDiscount=0;
if($shipping_ary['CouponCode']){
	$coupon_row=db::get_one('sales_coupon', "CouponNumber='{$shipping_ary['CouponCode']}'");
	if(!$coupon_row || $c['time']<$coupon_row['StartTime'] || $c['time']>$coupon_row['EndTime'] || ($coupon_row['UseNum'] && $coupon_row['BeUseTimes']>=$coupon_row['UseNum']) || $ProductPrice<$coupon_row['UseCondition']){
		$coupon_ary=array();
	}else{
		$CouponCode=addslashes($coupon_row['CouponNumber']);
		if($coupon_row['CouponType']==1){//CouponType: [0, 打折] [1, 减价格]
			$CouponPrice=$coupon_row['Money'];
		}else{
			$CouponPrice=($ProductPrice*(100-$coupon_row['Discount'])/100);
		}
		$coupon_data=array(
			'UsedTime'		=>	$c['time'],
			'BeUseTimes'	=>	$coupon_row['BeUseTimes']+1
		);
		//绑定会员
		if($user_data['UserId']){
			$UesdAry=$coupon_row['UsedUser']?@explode('|', $coupon_row['UsedUser']):array();
			$coupon_data['UsedUser']=$coupon_row['UsedUser']?$coupon_row['UsedUser']."|{$user_data['UserId']}":$user_data['UserId'];
		}
		db::update('sales_coupon', "CouponNumber='{$CouponCode}'", $coupon_data);
	}
	/*
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
			if((int)$coupon_ary['isuser']==2 && $user_data['UserId'] && !@in_array($user_data['UserId'], $UesdAry)){//所有会员都可使用，并且每个会员只能使用一次
				$coupon_data['UsedUser']=$coupon_ary['useduser']?$coupon_ary['useduser']."|{$user_data['UserId']}":$user_data['UserId'];
			}
		}
		db::update('sales_coupon', "CouponNumber='{$CouponCode}'", $coupon_data);
	}
	*/
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
	//$UserDiscount=($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100;
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
	ob_start();
	echo (ly200::is_mobile_client(1)==1?'Mobile':'PC')."\r\n\r\n";
	echo date('Y-m-d H:i:s', $c['time'])."\r\n\r\n 最低消费金额: {$c['config']['global']['LowPrice']} \r\n\r\n 订单金额: {$_total_price}\r\n\r\n";
	print_r($_SESSION['Gateway']['PaypalExcheckout']);
	print_r($_GET);
	print_r($_POST);
	$log=ob_get_contents();
	ob_end_clean();
	file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-error.txt", $log);	//把返回数据写入文件

	js::location('/cart/');
}

$order_data=array(
	/*******************订单基本信息*******************/
	'OId'					=>	$OId,	//订单号
	'UserId'				=>	$user_data['UserId'],
	'Source'				=>	ly200::is_mobile_client(0)?1:0,
	'RefererId'				=>	(int)$_COOKIE['REFERER'],
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
	'UpdateTime'			=>	$c['time'],
	/*******************优惠券信息*******************/
	'CouponCode'			=>	$CouponCode,
	'CouponPrice'			=>	$CouponPrice,
	'CouponDiscount'		=>	$CouponDiscount,
	/*******************收货地址*******************/
	'ShippingFirstName'		=>	addslashes($address_ary['FirstName']),
	'ShippingLastName'		=>	'',
	'ShippingAddressLine1'	=>	addslashes($address_ary['AddressLine1']),
	'ShippingAddressLine2'	=>	addslashes($address_ary['AddressLine2']),
	'ShippingCountryCode'	=>	'+'.$address_ary['CountryCode'],
	'ShippingPhoneNumber'	=>	$address_ary['PhoneNumber'],
	'ShippingCity'			=>	addslashes($address_ary['City']),
	'ShippingState'			=>	addslashes($address_ary['State']),
	'ShippingSId'			=>	0,
	'ShippingCountry'		=>	addslashes($address_ary['Country']),
	'ShippingCId'			=>	$address_ary['CId'],
	'ShippingZipCode'		=>	$address_ary['ZipCode'],
	/*'ShippingCodeOption'	=>	$tax_ary['CodeOption'],
	'ShippingCodeOptionId'	=>	$tax_ary['CodeOptionId'],
	'ShippingTaxCode'		=>	$tax_ary['TaxCode'],*/
	/*******************账单地址*******************/
	'BillFirstName'			=>	addslashes($bill_ary['FirstName']),
	'BillLastName'			=>	addslashes($bill_ary['LastName']),
	'BillAddressLine1'		=>	addslashes($bill_ary['AddressLine1']),
	'BillAddressLine2'		=>	addslashes($bill_ary['AddressLine2']),
	'BillCountryCode'		=>	'+'.$bill_ary['CountryCode'],
	'BillPhoneNumber'		=>	$bill_ary['PhoneNumber'],
	'BillCity'				=>	addslashes($bill_ary['City']),
	'BillState'				=>	addslashes($bill_ary['State']),
	'BillSId'				=>	$bill_ary['SId'],
	'BillCountry'			=>	addslashes($bill_ary['Country']),
	'BillCId'				=>	$bill_ary['CId'],
	'BillZipCode'			=>	$bill_ary['ZipCode'],
	'BillCodeOption'		=>	$bill_ary['CodeOption'],
	'BillCodeOptionId'		=>	$bill_ary['CodeOptionId'],
	'BillTaxCode'			=>	$bill_ary['TaxCode'],
	/*******************发货方式*******************/
	'ShippingExpress'		=>	addslashes($shipping_ary['ShippingExpress']?$shipping_ary['ShippingExpress']:''),
	'ShippingMethodSId'		=>	addslashes($shipping_ary['ShippingMethodSId']?$shipping_ary['ShippingMethodSId']:''),
	'ShippingMethodType'	=>	addslashes($shipping_ary['ShippingMethodType']?$shipping_ary['ShippingMethodType']:''),
	'ShippingInsurance'		=>	addslashes($shipping_ary['ShippingInsurance']?$shipping_ary['ShippingInsurance']:''),
	'ShippingPrice'			=>	$shipping_ary['ShippingPrice'],
	'ShippingInsurancePrice'=>	$shipping_ary['ShippingInsurancePrice'],
	'ShippingOvExpress'		=>	addslashes($shipping_ary['ShippingOvExpress']?$shipping_ary['ShippingOvExpress']:''),
	'ShippingOvSId'			=>	addslashes($shipping_ary['ShippingOvSId']?$shipping_ary['ShippingOvSId']:''),
	'ShippingOvType'		=>	addslashes($shipping_ary['ShippingOvType']?$shipping_ary['ShippingOvType']:''),
	'ShippingOvInsurance'	=>	addslashes($shipping_ary['ShippingOvInsurance']?$shipping_ary['ShippingOvInsurance']:''),
	'ShippingOvPrice'		=>	addslashes($shipping_ary['ShippingOvPrice']?$shipping_ary['ShippingOvPrice']:''),
	'ShippingOvInsurancePrice'=>addslashes($shipping_ary['ShippingOvInsurancePrice']?$shipping_ary['ShippingOvInsurancePrice']:''),
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
	$insert_sql.=($i%10==1)?"insert into `orders_products_list` (OrderId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Price, Qty, Property, PropertyPrice, OvId, Discount, Remark, Language, AccTime) VALUES":',';
	$insert_sql.="('$OrderId', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$Name}', '{$SKU}', '{$ImgPath}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Price']}', '{$v['Qty']}', '{$Property}', '{$v['PropertyPrice']}', '{$v['OvId']}', '{$v['Discount']}', '{$Remark}', '{$v['Language']}', '{$c['time']}')";
	if($i++%10==0){
		db::query($insert_sql);
		$insert_sql='';
	}
}
$insert_sql!='' && db::query($insert_sql);

/******** 记录PayPal买家账号的收货地址信息 Start ********/
$shipto_data=array(
	'Account'		=>	addslashes($shipto_ary['Account']),
	'FirstName'		=>	addslashes($shipto_ary['FirstName']),
	'LastName'		=>	'',
	'AddressLine1'	=>	addslashes($shipto_ary['AddressLine1']).', '.addslashes($shipto_ary['AddressLine2']),
	'CountryCode'	=>	'+'.$shipto_ary['CountryCode'],
	'PhoneNumber'	=>	$shipto_ary['PhoneNumber'],
	'City'			=>	addslashes($shipto_ary['City']),
	'State'			=>	addslashes($shipto_ary['State']),
	'Country'		=>	addslashes($shipto_ary['Country']),
	'ZipCode'		=>	addslashes($shipto_ary['ZipCode'])
);
if(db::get_row_count('orders_paypal_address_book', "OrderId='$OrderId'")){
	db::update('orders_paypal_address_book', "OrderId='$OrderId'", $shipto_data);
}else{
	$shipto_data['OrderId']=$OrderId;
	db::insert('orders_paypal_address_book', $shipto_data);
}
/******** 记录PayPal买家账号的收货地址信息 End ********/

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
		'FirstName'		=>	addslashes($address_ary['FirstName']),
		'LastName'		=>	addslashes($address_ary['LastName']),
		'AddressLine1'	=>	addslashes($address_ary['AddressLine1']),
		'AddressLine2'	=>	addslashes($address_ary['AddressLine2']),
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
$_SESSION['Cart']['Coupon']='';
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
	ly200::sendmail($ToAry, "Place an Order: ".$OId, $mail_contents);;
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

js::location("/payment/paypal_excheckout/DoExpressCheckoutPayment/{$OId}.html");

exit();