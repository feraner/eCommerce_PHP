<?php
//生成订单号
while(1){
	$OId=date('ymdHis', $c['time']).mt_rand(10,99);
	if(!db::get_row_count('orders', "OId='$OId'")){
		break;
	}
}
//自定义参数： 产品数量、国家ID、快递ID、快递名、快递类型、是否购买快递保险、快递运费、快递保险费、优惠券代码
if(is_array($_GET['SId']) || is_array($_GET['ShippingInsurance'])){ //PC端是数组格式，移动端是JSON格式
	$_SId=$_ShippingInsurance=array();
	foreach((array)$_GET['SId'] as $k=>$v){ $_SId['OvId_'.$k]=$v; }
	foreach((array)$_GET['ShippingInsurance'] as $k=>$v){ $_ShippingInsurance['OvId_'.$k]=$v; }
	$_SId=str::json_data($_SId);
	$_ShippingInsurance=str::json_data($_ShippingInsurance);
}else{
	$_SId=$_GET['SId'];
	$_ShippingInsurance=$_GET['ShippingInsurance'];
}
$CUSTOM=(int)count($products_ary).'|'.(int)$_GET['CId'].'|'.trim($_SId).'|'.trim($_GET['ShippingExpress']).'|'.trim($_GET['ShippingMethodType']).'|'.trim($_ShippingInsurance).'|'.trim($_GET['ShippingPrice']).'|'.trim($_GET['ShippingInsurancePrice']).'|'.trim($_GET['order_coupon_code']);
$CUSTOM=stripslashes($CUSTOM);
$CUSTOM=str_replace('OvId_', '', $CUSTOM);
//整理产品资料
$where='c.'.$c['where']['cart'];
$_GET['CartCId'] && $where.=" and c.CId in({$_GET['CartCId']})";
if($_GET['SourceType']=='shipping_cost'){//来自产品详细页
	$ProInfo=explode('&', $_GET['ProInfo']);
	$ProId=(int)str_replace('ProId=', '', $ProInfo[2]);
	$Qty=(int)str_replace('Qty=', '', $ProInfo[3]);
	$where.=" and c.ProId='{$ProId}' and c.Qty='{$Qty}'";
	$cart_row[0]=db::get_one('shopping_cart c left join products p on c.ProId=p.ProId', $where, 'c.*, p.Name_en, p.PicPath_0', 'c.CId desc');
}else{
	$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $where, 'c.*, p.Name_en, p.PicPath_0', 'c.CId desc');
}
$i=1;
$insert_sql='';
foreach($cart_row as $v){
	$insert_sql.=($i%100==1)?"insert into `shopping_excheckout` (OId, CId, UserId, SessionId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Volume, Price, Qty, Property, PropertyPrice, OvId, Discount, Remark, Language, AccTime) VALUES":',';
	$Name=str::str_code($v['Name'.$c['lang']], 'addslashes');
	$SKU=str::str_code($v['SKU'], 'addslashes');
	$Property=str::str_code($v['Property'], 'addslashes');
	$Remark=str::str_code($v['Remark'], 'addslashes');
	$insert_sql.="('{$OId}', '{$v['CId']}', '{$v['UserId']}', '{$v['SessionId']}', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$Name}', '{$SKU}', '{$v['PicPath']}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Volume']}', '{$v['Price']}', '{$v['Qty']}', '{$Property}', '{$v['PropertyPrice']}', '{$v['OvId']}', '{$v['Discount']}', '{$Remark}', '{$v['Language']}', '{$c['time']}')";
	if($i++%100==0){
		db::query($insert_sql);
		$insert_sql='';
	}
}
$insert_sql!='' && db::query($insert_sql);

ob_start();
print_r($_GET);
print_r($_POST);
echo "\r\n\r\n OId: $OId";
echo "\r\n\r\n CUSTOM: $CUSTOM";
echo "\r\n\r\n Source: ".(ly200::is_mobile_client(1)==1?'Mobile':'PC');
$log=ob_get_contents();
ob_end_clean();
file::write_file('/_pay_log_/paypal_excheckout/credit_log/'.date('Y_m/d/', $c['time']), $OId.'_SetExpress.txt', $log);//把返回数据写入文件

ly200::e_json(array('OId'=>$OId, 'CUSTOM'=>$CUSTOM), 1);