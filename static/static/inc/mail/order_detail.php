<?php !isset($c) && exit();?>
<?php
$language=$c['lang']?$c['lang']:$c['manage']['web_lang'];
$IsOverseas=(int)($c['config']['global']?$c['config']['global']['Overseas']:$c['manage']['config']['Overseas']);

$isFee=1;//默认显示
$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");
$total_price=orders::orders_price($orders_row, $isFee);
$isFee && $HandingFee=$total_price-orders::orders_price($orders_row);

//订单产品信息
$OvId=0;
$oversea_id_ary=array();
$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='{$orders_row['OrderId']}'", 'o.*, o.SKU as OrderSKU, p.Prefix, p.Number, p.PicPath_0', 'o.OvId asc, o.LId asc');
foreach($order_list_row as $k=>$v){
	$OvId=$v['OvId'];
	!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
}
sort($oversea_id_ary); //排列正序

//收货地址
$paypal_address_row=str::str_code(db::get_one('orders_paypal_address_book', "OrderId='{$orders_row['OrderId']}' and IsUse=1"));
if($paypal_address_row){//Paypal账号收货地址
	$shipto_ary=array(
		'FirstName'			=>	$paypal_address_row['FirstName'],
		'LastName'			=>	$paypal_address_row['LastName'],
		'AddressLine1'		=>	$paypal_address_row['AddressLine1'],
		'AddressLine2'		=>	'',
		'City'				=>	$paypal_address_row['City'],
		'State'				=>	$paypal_address_row['State'],
		'Country'			=>	$paypal_address_row['Country'],
		'ZipCode'			=>	$paypal_address_row['ZipCode'],
		'CountryCode'		=>	$paypal_address_row['CountryCode'],
		'PhoneNumber'		=>	$paypal_address_row['PhoneNumber']
	);
}else{//会员账号收货地址
	$shipto_ary=array(
		'FirstName'			=>	$orders_row['ShippingFirstName'],
		'LastName'			=>	$orders_row['ShippingLastName'],
		'AddressLine1'		=>	$orders_row['ShippingAddressLine1'],
		'AddressLine2'		=>	$orders_row['ShippingAddressLine2'],
		'City'				=>	$orders_row['ShippingCity'],
		'State'				=>	$orders_row['ShippingState'],
		'Country'			=>	$orders_row['ShippingCountry'],
		'ZipCode'			=>	$orders_row['ShippingZipCode'],
		'CountryCode'		=>	$orders_row['ShippingCountryCode'],
		'PhoneNumber'		=>	$orders_row['ShippingPhoneNumber']
	);
}

//发货方式(多个发货地)
$shipping_ary=array(
	'ShippingOvExpress'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvExpress']), 'decode'),
	'ShippingOvSId'				=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvSId']), 'decode'),
	'ShippingOvType'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvType']), 'decode'),
	'ShippingOvInsurance'		=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurance']), 'decode'),
	'ShippingOvPrice'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvPrice']), 'decode'),
	'ShippingOvInsurancePrice'	=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurancePrice']), 'decode')
);

//发货信息
if(!$orders_row['TrackingNumber'] && $orders_row['ShippingTime']==0 && !$orders_row['Remarks']){ //多个发货地
	$shipped_ary=array(
		'Express'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvExpress']), 'decode'),
		'TrackingNumber'	=>	str::json_data(htmlspecialchars_decode($orders_row['OvTrackingNumber']), 'decode'),
		'ShippingTime'		=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingTime']), 'decode'),
		'Remarks'			=>	str::json_data(htmlspecialchars_decode($orders_row['OvRemarks']), 'decode')
	);
}else{ //单个发货地
	$shipped_ary=array(
		'Express'			=>	array($OvId=>$orders_row['ShippingExpress']),
		'TrackingNumber'	=>	array($OvId=>$orders_row['TrackingNumber']),
		'ShippingTime'		=>	array($OvId=>$orders_row['ShippingTime']),
		'Remarks'			=>	array($OvId=>$orders_row['Remarks'])
	);
}

//所有产品属性
$attribute_cart_ary=$vid_data_ary=array();
$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$language}, ParentId, CartAttr, ColorAttr"));
foreach($attribute_row as $v){
	$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$language}"]);
}
$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
foreach($value_row as $v){
	$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$language}"];
}

//发货地
$overseas_ary=array();
$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
foreach($overseas_row as $v){
	$v['OvId']==1 && $v['OvId']=0;//默认China
	$overseas_ary[$v['OvId']]=$v;
}
if (!$c['lang_pack']){
	$default_lang=$c['manage']?$c['manage']['config']['LanguageDefault']:$c['config']['global']['LanguageDefault'];
	$c['lang_pack']=@include($c['root_path'].'/static/static/lang/'.($c['lang']?substr($c['lang'], 1):$default_lang).'.php');//加载语言包
}
//表格样式
$style_details=' style="padding:7px; border-bottom:1px solid #ddd; font-size:12px; font-family:Arial;"';
?>
<a href="<?=ly200::get_domain().$member_url;?>/account/orders/view<?=$orders_row['OId'];?>.html" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['checkDetails'];?></a><br /><br />

<div style="border:1px solid #ddd; background:#f7f7f7; border-bottom:none; width:130px; height:26px; line-height:26px; text-align:center; font-size:12px; font-family:Arial;"><strong><?=$c['lang_pack_email']['details'];?></strong></div>
<div style="border:1px solid #ddd; padding:10px; font-size:12px; font-family:Arial;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="110"<?=$style_details;?>><?=$c['lang_pack_email']['orderNumber'];?>:</td>
			<td<?=$style_details;?>><?=$orders_row['OId'];?></td>
		</tr>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['orderDate'];?>:</td>
			<td<?=$style_details;?>><?=date('d/m-Y H:i:s', $orders_row['OrderTime']);?></td>
		</tr>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['orderStatus'];?>:</td>
			<td<?=$style_details;?>><?=$c['orders']['status'][$orders_row['OrderStatus']];?></td>
		</tr>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['paymentMethod'];?>:</td>
			<td<?=$style_details;?>><?=$orders_row['PaymentMethod'];?></td>
		</tr>
		<?php
		if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']==''){ //多个发货地
			foreach($oversea_id_ary as $k=>$v){
		?>
			<tr>
				<td<?=$style_details;?>><?=$k?'':$c['lang_pack']['user']['shippingMethod'].':';?></td>
				<td<?=$style_details;?>><strong><?=$c['config']['Overseas'][$v]['Name'.$language];?>:</strong>&nbsp;&nbsp;<?=$shipping_ary['ShippingOvExpress'][$v];?></td>
			</tr>
		<?php
			}
		}else{ //单个发货地
		?>
			<tr>
				<td<?=$style_details;?>><?=$c['lang_pack']['user']['shippingMethod'].':';?></td>
				<td<?=$style_details;?>><?=(int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?><?=$shipping_row['Brief']?" ({$shipping_row['Brief']})":'';?></td>
			</tr>
		<?php }?>
		<?php
		if($orders_row['OrderStatus']==5 || $orders_row['OrderStatus']==6){
			foreach($oversea_id_ary as $k=>$v){
				if(!$shipped_ary['TrackingNumber'][$v]) continue;
		?>
			<tr>
				<td<?=$style_details;?>><?=$c['lang_pack_email']['trackingNumber'];?>:</td>
				<td<?=$style_details;?>><?=$c['config']['Overseas'][$v]['Name'.$language].': '.$shipped_ary['TrackingNumber'][$v];?> (<?=date('m/d-Y', $shipped_ary['ShippingTime'][$v]);?>)</td>
			</tr>
			<tr>
				<td<?=$style_details;?>><?=$c['lang_pack_email']['remarks'];?>:</td>
				<td<?=$style_details;?>><?=$c['config']['Overseas'][$v]['Name'.$language].': '.$shipped_ary['Remarks'][$v];?></td>
			</tr>
		<?php
			}
		}?>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['itemCosts'];?>:</td>
			<td<?=$style_details;?>><em><?=$orders_row['Currency'];?></em><?=cart::iconv_price($orders_row['ProductPrice'], 0, $orders_row['Currency']);?></td>
		</tr>
		<?php if($orders_row['Discount']>0){?>
			<tr>
				<td<?=$style_details;?>><?=$c['lang_pack_email']['discount'];?>:</td>
				<td<?=$style_details;?>><?=$orders_row['Discount'];?>%</td>
			</tr>
			<tr>
				<td<?=$style_details;?>><?=$c['lang_pack_email']['save'];?>:</td>
				<td<?=$style_details;?>><?=cart::iconv_price($orders_row['Discount']*$orders_row['TotalPrice']);?></td>
			</tr>
		<?php }?>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['shipInsurance'];?>:</td>
			<td<?=$style_details;?>><em><?=$orders_row['Currency'];?></em><?=cart::iconv_price($orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice'], 0, $orders_row['Currency']);?></td>
		</tr>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['fee'];?>:</td>
			<td<?=$style_details;?>><em><?=$orders_row['Currency'];?></em><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($HandingFee, 0, $orders_row['Currency']);?></td>
		</tr>
		<?php
		if($orders_row['CouponCode'] && ($orders_row['CouponPrice']>0 || $orders_row['CouponDiscount']>0)){
		  $discountPrice=$orders_row['CouponPrice']>0?$orders_row['CouponPrice']:$orders_row['ProductPrice']*$orders_row['CouponDiscount'];
		?>
		<tr>
			<td<?=$style_details;?>>(-) <?=$c['lang_pack_email']['coupon'];?>:</td>
			<td<?=$style_details;?>><em><?=$orders_row['Currency'];?></em><?=cart::iconv_price($discountPrice, 0, $orders_row['Currency']);?></td>
		</tr>
		<?php }?>
		<tr>
			<td<?=$style_details;?>><?=$c['lang_pack_email']['grandTotal'];?>:</td>
			<td<?=$style_details;?>><em><?=$orders_row['Currency'];?></em><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($total_price, 0, $orders_row['Currency']);?></td>
		</tr>
	</table>
	<div style="margin:0px auto; clear:both; height:20px; font-size:1px; overflow:hidden;"></div>
	<div style="clear:both; zoom:1;">
		<div style="width:45%; float:left;">
			<div style="font-weight:bold; height:22px; line-height:22px; font-size:12px; font-family:Arial;"><?=$c['lang_pack_email']['yShipAddress'];?>:</div>
			<div style="border:1px solid #ddd; background:#fdfdfd; padding:8px; line-height:160%; min-height:78px; font-size:12px; font-family:Arial; font-size:12px;">
				<strong><?=$shipto_ary['FirstName'].' '.$shipto_ary['LastName'];?></strong> (<?=$shipto_ary['AddressLine1'].($shipto_ary['AddressLine2']?', '.$shipto_ary['AddressLine2']:'').', '.$shipto_ary['City'].', '.$shipto_ary['ZipCode'].' - '.$shipto_ary['State'].', '.$shipto_ary['Country'];?>)<br>
                <?=$c['lang_pack_email']['phone'];?>:<?=$shipto_ary['CountryCode'].' '.$shipto_ary['PhoneNumber'];?>
			</div>
		</div>
		<div style="width:45%; float:left; margin-left:10px;">
			<div style="font-weight:bold; height:22px; line-height:22px; font-size:12px; font-family:Arial;"><?=$c['lang_pack_email']['yBillAddress'];?>:</div>
			<div style="border:1px solid #ddd; background:#fdfdfd; padding:8px; line-height:160%; min-height:78px; font-size:12px; font-family:Arial; font-size:12px;">
				<strong><?=$orders_row['BillFirstName'].' '.$orders_row['BillLastName'];?></strong> (<?=$orders_row['BillAddressLine1'].($orders_row['BillAddressLine2']?', '.$orders_row['BillAddressLine2']:'').', '.$orders_row['BillCity'].', '.$orders_row['BillZipCode'].' - '.$orders_row['BillState'].', '.$orders_row['BillCountry'];?>)<br>
                <?=$c['lang_pack_email']['phone'];?>:<?=$orders_row['BillCountryCode'].' '.$orders_row['BillPhoneNumber'];?>
			</div>
		</div>
		<div style="margin:0px auto; clear:both; height:0px; font-size:0px; overflow:hidden;"></div>
	</div>
	<?php /*
	<div style="margin:0px auto; clear:both; height:20px; font-size:1px; overflow:hidden;"></div>
	<div style="border-bottom:2px solid #ddd; height:24px; line-height:24px; font-weight:bold; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['shippingMethod'];?>:</div>
	<div style="line-height:150%; margin-top:5px; font-family:Arial;"><?=(int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?><?=$shipping_row['Brief']?" ({$shipping_row['Brief']})":'';?><br /><?=$c['lang_pack_email']['shipInsurance'];?>: <em><?=$orders_row['Currency'];?></em><?=cart::iconv_price($orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice'], 0, $orders_row['Currency']);?></div>
	*/?>
	<div style="margin:0px auto; clear:both; height:20px; font-size:1px; overflow:hidden;"></div>
	<div style="border-bottom:2px solid #ddd; height:24px; line-height:24px; font-weight:bold; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['special'];?>:</div>
	<div style="line-height:180%; font-family:Arial; font-size:12px;"><?=str::format($orders_row['Comments']);?></div>
	<div style="margin:0px auto; clear:both; height:20px; font-size:1px; overflow:hidden;"></div>
	<div style="border-bottom:2px solid #ddd; height:24px; line-height:24px; font-weight:bold; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['orderItems'];?>:</div>
	<?php
	$pro_ary=$order_list_ary=$pro_qty_ary=$waybill_ary=array();
	$_OvId=0;
	foreach($order_list_row as $v){
		$pro_ary[$v['LId']]=$v; //订单产品信息
		$order_list_ary[$v['OvId']]['00'][]=$pro_ary[$v['LId']]; //总信息
		$pro_qty_ary[$v['LId']]=$v['Qty'];
		$_OvId=$v['OvId'];
	}
	$orders_waybill_row=db::get_all('orders_waybill', "OrderId='{$orders_row['OrderId']}'");
	foreach($orders_waybill_row as $v){
		$i=0;
		$waybill_ary[$v['Number']]=$v;
		$ProInfo=str::json_data(htmlspecialchars_decode($v['ProInfo']), 'decode');
		foreach($ProInfo as $k2=>$v2){ //$k2==LId $v2==QTY
			$ovid=$pro_ary[$k2]['OvId'];
			$order_list_ary[$ovid][$v['Number']][$i]=$pro_ary[$k2];
			$order_list_ary[$ovid][$v['Number']][$i]['Qty']=$v2;
			$pro_qty_ary[$k2]-=$v2;
			++$i;
		}
	}
	//发货信息
	if(!$orders_row['TrackingNumber'] && $orders_row['ShippingTime']==0 && !$orders_row['Remarks']){ //多个发货地
		$ship_ary=array(
			'ShippingExpress'		=>	$shipping_ary['ShippingOvExpress'],
			'ShippingMethodSId'		=>	$shipping_ary['ShippingOvSId'],
			'ShippingType'			=>	$shipping_ary['ShippingOvType'],
			'ShippingInsurance'		=>	$shipping_ary['ShippingOvInsurance'],
			'ShippingPrice'			=>	$shipping_ary['ShippingOvPrice'],
			'ShippingInsurancePrice'=>	$shipping_ary['ShippingOvInsurancePrice'],
			'TrackingNumber'		=>	str::json_data(htmlspecialchars_decode($orders_row['OvTrackingNumber']), 'decode'),
			'ShippingTime'			=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingTime']), 'decode'),
			'Remarks'				=>	str::json_data(htmlspecialchars_decode($orders_row['OvRemarks']), 'decode'),
			'Status'				=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingStatus']), 'decode')
		);
	}else{ //单个发货地
		$ship_ary=array(
			'ShippingExpress'		=>	array($_OvId=>$orders_row['ShippingExpress']),
			'ShippingMethodSId'		=>	array($_OvId=>(int)$orders_row['ShippingMethodSId']),
			'ShippingType'			=>	array($_OvId=>$orders_row['ShippingType']),
			'ShippingInsurance'		=>	array($_OvId=>(int)$orders_row['ShippingInsurance']),
			'ShippingPrice'			=>	array($_OvId=>(float)$orders_row['ShippingPrice']),
			'ShippingInsurancePrice'=>	array($_OvId=>(float)$orders_row['ShippingInsurancePrice']),
			'TrackingNumber'		=>	array($_OvId=>$orders_row['TrackingNumber']),
			'ShippingTime'			=>	array($_OvId=>$orders_row['ShippingTime']),
			'Remarks'				=>	array($_OvId=>$orders_row['Remarks']),
			'Status'				=>	array($_OvId=>($orders_row['TrackingNumber']?1:0))
		);
	}
	//检查此会员一个月内的产品评论订单ID
	$reviewAry=array();
	$time=3600*30;
	((int)$_SESSION['User']['UserId'] && $c['where']['user']) && $review_orders_row=(int)$_SESSION['User']['UserId']?db::get_all('products_review', "UserId={$_SESSION['User']['UserId']} and AccTime>({$c['time']}-{$time})", 'ProId, OrderId'):array();
	foreach((array)$review_orders_row as $k=>$v){
		$reviewAry[$v['ProId']][]=$v['OrderId'];
	}
	foreach($order_list_ary as $OvId=>$row){
		foreach($row as $key=>$val){
			$total=$amount=$quantity=0;
	?>
			<div style="height:30px; line-height:30px; font-size:12px;">
				<strong><?=$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$language];?></strong>
				<?php if($key=='00' && $ship_ary['TrackingNumber'][$OvId]){?>
					<span style="margin-left:15px;"><?=$c['lang_pack']['user']['trackNo'].': '.$ship_ary['TrackingNumber'][$OvId];?></span>
				<?php }elseif($key!='00' && $waybill_ary[$key]['TrackingNumber']){?>
					<span style="margin-left:15px;"><?=$c['lang_pack']['user']['trackNo'].': '.$waybill_ary[$key]['TrackingNumber'];?></span>
				<?php }?>
				<span style="margin-left:15px;"><?=$c['lang_pack']['user']['status'].': '.$c['lang_pack']['user']['OrderStatusAry'][$ship_ary['Status'][$OvId]+4];?></span>
			</div>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border:1px solid #ddd; margin-bottom:15px;">
				<tr>
					<td width="14%" style="border-right:1px solid #ddd; height:28px; font-weight:bold; text-align:center; background:#e1e1e1; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['pictures'];?></td>
					<td width="50%" style="border-right:1px solid #ddd; height:28px; font-weight:bold; text-align:center; background:#e1e1e1; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['product'];?></td>
					<td width="12%" style="border-right:1px solid #ddd; height:28px; font-weight:bold; text-align:center; background:#e1e1e1; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['price'];?></td>
					<td width="12%" style="border-right:1px solid #ddd; height:28px; font-weight:bold; text-align:center; background:#e1e1e1; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['qty'];?></td>
					<td width="12%" style="border-right:1px solid #ddd; height:28px; font-weight:bold; text-align:center; background:#e1e1e1; font-family:Arial; font-size:12px; border-right:none;"><?=$c['lang_pack_email']['total'];?></td>
				</tr>
				<?php
				foreach((array)$val as $v){
					if($key=='00'){
						$qty=(int)$pro_qty_ary[$v['LId']];
					}else{
						$qty=(int)$v['Qty'];
					}
					if($qty<1) continue;
					if($v['BuyType']==4){
						$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
						if(!$package_row) continue;
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
						$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
						$pro_where=='' && $pro_where=0;
						$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
						$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
						$amount+=($v['Price']*$qty);
						$len=count($products_row);
						foreach((array)$products_row as $k2=>$v2){
							$total+=$qty;
							$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
							$url=ly200::get_url($v2, 'products', $language);
				?>
						<tr align="center">
							<td valign="top" style="padding:7px 5px; border-top:1px solid #ddd;"><table width="92" border="0" cellpadding="0" cellspacing="0" align="center"><tr><td height="92" width="92" align="center" style="border:1px solid #ccc; padding:0; background:#fff;"><a href="<?=ly200::get_domain().$url;?>" title="<?=$v2['Name'.$language];?>" target="_blank"><img src="<?=ly200::get_domain().$img;?>" style="max-width:100%; max-height:100%;" alt="<?=$v2['Name'.$language];?>" /></a></td></tr></table></td>
							<td align="left" style="line-height:150%; font-size:12px; font-family:Arial; padding:7px 5px; border-top:1px solid #ddd;">
								<a href="<?=ly200::get_domain().$url;?>" title="<?=$v2['Name'.$language];?>" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;"><?=$v2['Name'.$language];?></a><br />
								<?=$v2['Number']!=''?'<div>'.$v2['Prefix'].$v2['Number'].'</div>':'';?>
								<?php if($k2==0){?>
									<div>
										<?php
										foreach((array)$attr as $k=>$z){
											if($k=='Overseas' && ($IsOverseas==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
											echo '<div>'.($k=='Overseas'?$c['lang_pack_email']['shipsFrom']:$k).': '.$z.'</div>';
										}
										if($IsOverseas==1 && $v['OvId']==1){
											echo '<div>'.$c['lang_pack_email']['shipsFrom'].': '.$overseas_ary[$v['OvId']]['Name'.$language].'</div>';
										}?>
									</div>
								<?php }elseif($data_ary[$v2['ProId']]){?>
									<div>
										<?php
										$OvId=0;
										foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
											if($k3=='Overseas'){ //发货地
												$OvId=str_replace('Ov:', '', $v3);
												if($IsOverseas==0 || $OvId==1) continue; //发货地是中国，不显示
												echo '<div>'.$c['lang_pack_email']['shipsFrom'].': '.$overseas_ary[$OvId]['Name'.$language].'</div>';
											}else{
												echo '<div>'.$attribute_ary[$k3][1].': '.$vid_data_ary[$k3][$v3].'</div>';
											}
										}
										if($IsOverseas==1 && $OvId==1){
											echo '<div>'.$c['lang_pack_email']['shipsFrom'].': '.$overseas_ary[$OvId]['Name'.$language].'</div>';
										}
										?>
									</div>
								<?php }?>
							</td>
							<?php if($k2==0){?>
								<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;" rowspan="<?=$len;?>"><?=cart::iconv_price($v['Price'], 0, $orders_row['Currency']);?></td>
								<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;" rowspan="<?=$len;?>"><?=$qty;?></td>
								<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;" rowspan="<?=$len;?>"><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::iconv_price($v['Price']*$qty, 2, $orders_row['Currency']);?></td>
							<?php }?>
						</tr>
				<?php
						}
					}else{
						$v['Name'.$language]=$v['Name'];
						$price=$v['Price']+$v['PropertyPrice'];
						$v['Discount']<100 && $price*=$v['Discount']/100;
						$amount+=($price*$qty);
						$url=ly200::get_url($v, 'products');
						$name=$v['Name'];
						$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						!$attr && $attr=str::json_data($v['Property'], 'decode');
						$url=ly200::get_url($v, 'products', $language);
						$total+=$qty;
				?>
						<tr align="center">
							<td valign="top" style="padding:7px 5px; border-top:1px solid #ddd;"><table width="92" border="0" cellpadding="0" cellspacing="0" align="center"><tr><td height="92" width="92" align="center" style="border:1px solid #ccc; padding:0; background:#fff;"><a href="<?=ly200::get_domain().$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=ly200::get_domain().$v['PicPath'];?>" style="max-width:100%; max-height:100%;" alt="<?=$name;?>" /></a></td></tr></table></td>
							<td align="left" style="line-height:150%; font-size:12px; font-family:Arial; padding:7px 5px; border-top:1px solid #ddd;">
								<a href="<?=ly200::get_domain().$url;?>" title="<?=$name;?>" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;"><?=$name;?></a><br />
								<?php
								echo $v['Number']!=''?'<div>'.$v['Prefix'].$v['Number'].'</div>':'';
								foreach((array)$attr as $k=>$v2){
									if($k=='Overseas' && ($IsOverseas==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									echo '<div>'.($k=='Overseas'?$c['lang_pack_email']['shipsFrom']:$k).': '.$v2.'</div>';
								}
								if($IsOverseas==1 && $v['OvId']==1){
									echo '<p class="pro_attr">'.$c['lang_pack_email']['shipsFrom'].': '.$overseas_ary[$v['OvId']]['Name'.$language].'</p>';
								}
								echo $v['Remark']!=''?'<div>'.$v['Remark'].'</div>':'';
								?>
							</td>
							<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;"><?=cart::iconv_price($price, 0, $orders_row['Currency']);?></td>
							<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;"><?=$qty;?></td>
							<td style="font-family:Arial; font-size:12px; padding:7px 5px; border-top:1px solid #ddd;"><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::iconv_price($price*$qty, 2, $orders_row['Currency']);?></td>
						</tr>
				<?php
					}
				}?>
				<tr>
					<td colspan="3" style="height:26px; background:#efefef; text-align:center; color:#B50C08; font-size:12px; font-weight:bold; font-family:Arial;">&nbsp;</td>
					<td style="height:26px; background:#efefef; text-align:center; color:#B50C08; font-size:12px; font-weight:bold; font-family:Arial;"><?=$total;?></td>
					<td style="height:26px; background:#efefef; text-align:center; color:#B50C08; font-size:12px; font-weight:bold; font-family:Arial;"><?=cart::iconv_price($amount, 0, $orders_row['Currency']);?></td>
				</tr>
			</table>
	<?php
		}
	}
	?>
</div>
<div style="margin:0px auto; clear:both; height:20px; font-size:1px; overflow:hidden;"></div>