<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class orders_module{
	public static function orders_del(){	//删除订单
		global $c;
		$OrderId=(int)$_GET['OrderId'];
		$orders_row=db::get_one('orders', "OrderId='$OrderId'", 'OId,OrderTime');
		$month_dir=$c['orders']['path'].date('ym', $orders_row['OrderTime']).'/';
		file::del_dir($month_dir.$orders_row['OId'].'/');
		db::delete('orders_products_list', "OrderId={$OrderId}");
		db::delete('orders_payment_info', "OrderId={$OrderId}");
		db::delete('orders', "OrderId={$OrderId}");
		db::delete('orders_log', "OrderId={$OrderId}");
		$lang=$c['manage']['lang_pack'];
		manage::operation_log($lang['global']['del'].$lang['orders']['orders'].':'.$orders_row['OId']);
		ly200::e_json('', 1);
	}

	public static function orders_del_bat(){	//批量删除订单
		global $c;
		$del_bat_id=str_replace('-',',',$_GET['group_orderid']);
		$del_bat_id=='' && ly200::e_json('');
		$i=0;
		$orders_row=db::get_all('orders', "OrderId in($del_bat_id)", 'OId,OrderTime');
		foreach($orders_row as $v){
			$month_dir=$c['orders']['path'].date('ym', $v['OrderTime']).'/';
			file::del_dir($month_dir.$v['OId'].'/');
		}
		db::delete('orders_products_list', "OrderId in($del_bat_id)");
		db::delete('orders', "OrderId in($del_bat_id)");
		db::delete('orders_log', "OrderId in($del_bat_id)");
		$lang=$c['manage']['lang_pack'];
		manage::operation_log($lang['global']['del_bat'].$lang['orders']['orders']);
		ly200::e_json('', 1);
	}
	
	public static function orders_custom_column(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Custom=addslashes(str::json_data(str::str_code($p_Custom, 'stripslashes')));
		$data=array(
			'Orders'	=>	$p_Custom
		);
		manage::config_operaction($data, 'custom_column');
		manage::operation_log('订单自定义列');
		ly200::e_json('', 1);
	}
	
	public static function orders_edit_sales(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_SalesId=(int)$p_SalesId;
		$result='';
		if($p_SalesId>0 && (float)db::get_value('orders', "OrderId='{$p_OrderId}'", 'SalesId')!=$p_SalesId){
			db::update('orders', "OrderId='{$p_OrderId}'", array('SalesId'=>$p_SalesId, 'UpdateTime'=>$c['time']));
			manage::operation_log('订单修改业务员');
		}
		$result=db::get_value('manage_sales', "SalesId='{$p_SalesId}'", 'UserName');
		ly200::e_json($result, 1);
	}
	
	public static function select_country(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		
		$country_row=str::str_code(db::get_one('country', "CId='{$p_CId}'"));
		$CountryCode=$country_row['Code'];
		if($country_row['HasState']==1){
			$state_row=str::str_code(db::get_all('country_states', "CId='{$p_CId}'", '*', 'States asc'));
			$data=$state_row;
		}else{
			$data=-1;
		}
		
		unset($country_row, $state_row);
		exit(str::json_data(array('status'=>1, 'cid'=>$p_CId, 'code'=>$CountryCode, 'contents'=>$data)));
	}
	
	public static function orders_mod_address(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$p_country_id=(int)$p_country_id;
		$p_Province=(int)$p_Province;
		
		$order_row=db::get_one('orders', "OrderId='$p_OrderId'");
		!$order_row && exit(str::json_data(array('status'=>-1)));
		
		$v['CodeOption']=(int)$p_tax_code_type;
		$v['TaxCode']=$p_tax_code_value;
		$tax_ary=user::get_tax_info($v);
		$data=array(
			'ShippingFirstName'		=>	$p_FirstName,
			'ShippingLastName'		=>	$p_LastName,
			'ShippingAddressLine1'	=>	$p_AddressLine1,
			'ShippingAddressLine2'	=>	$p_AddressLine2,
			'ShippingCountryCode'	=>	$p_CountryCode,
			'ShippingPhoneNumber'	=>	$p_PhoneNumber,
			'ShippingCity'			=>	$p_City,
			'ShippingState'			=>	$p_State?$p_State:db::get_value('country_states', "SId='$p_Province' and CId='$p_country_id'", 'States'),
			'ShippingSId'			=>	$p_Province,
			'ShippingCountry'		=>	db::get_value('country', "CId='$p_country_id'", 'Country'),
			'ShippingCId'			=>	$p_country_id,
			'ShippingZipCode'		=>	$p_ZipCode,
			'ShippingCodeOption'	=>	$tax_ary['CodeOption'],
			'ShippingCodeOptionId'	=>	$tax_ary['CodeOptionId'],
			'ShippingTaxCode'		=>	$tax_ary['TaxCode'],
			'UpdateTime'			=>	$c['time']
		);
		db::update('orders', "OrderId='$p_OrderId'", $data);
		
		$info=array(
			'name'		=>	$data['ShippingFirstName']." ".$data['ShippingLastName'],
			'address'	=>	$data['ShippingAddressLine1'].($data['ShippingAddressLine2']?', '.$data['ShippingAddressLine2']:'').'<br />'.$data['ShippingCity'].', '.$data['ShippingState'].', '.$data['ShippingZipCode'].'<br />'.$data['ShippingCountry'].(($data['ShippingCodeOption']&&$data['ShippingTaxCode'])?'#'.$data['ShippingCodeOption'].': '.$data['ShippingTaxCode']:''),
			'phone'		=>	$data['ShippingCountryCode']." ".$data['ShippingPhoneNumber'],
			'zipcode'	=>	$data['ShippingZipCode'],
		);
		$returnData=array(
			'OrderId'		=>	$p_OrderId,
			'FirstName'		=>	$data['ShippingFirstName'],
			'LastName'		=>	$data['ShippingLastName'],
			'AddressLine1'	=>	$data['ShippingAddressLine1'],
			'AddressLine2'	=>	$data['ShippingAddressLine2'],
			'City'			=>	$data['ShippingCity'],
			'State'			=>	$data['ShippingState'],
			'SId'			=>	$data['ShippingSId'],
			'Country'		=>	$data['ShippingCountry'],
			'CId'			=>	$data['ShippingCId'],
			'ZipCode'		=>	$data['ShippingZipCode'],
			'CodeOption'	=>	$data['ShippingCodeOption'],
			'CodeOptionId'	=>	$data['ShippingCodeOptionId'],
			'TaxCode'		=>	$data['ShippingTaxCode'],
			'CountryCode'	=>	$data['ShippingCountryCode'],
			'PhoneNumber'	=>	$data['ShippingPhoneNumber'],
		);
		$json_data=str::json_data(str::str_code($returnData, 'stripslashes'));
		
		unset($order_row, $tax_ary, $data, $returnData);
		exit(str::json_data(array('status'=>1, 'info'=>$info, 'text'=>$json_data)));
	}
	
	public static function orders_mod_paypal_address(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$p_IsUse=(int)$p_IsUse;
		$row=db::get_one('orders_paypal_address_book', "OrderId='$p_OrderId'");
		if($row){
			db::update('orders_paypal_address_book', "OrderId='$p_OrderId'", array('IsUse'=>$p_IsUse));
		}
		ly200::e_json('', 1);
	}

	public static function orders_mod_info(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		
		$order_row=db::get_one('orders', "OrderId='$p_OrderId'");
		!$order_row && exit(str::json_data(array('status'=>-1)));
		
		!$order_row['ShippingInsurance'] && $p_ShippingInsurancePrice=0;
		$shipping_data=array(
			'ShippingPrice'				=>	(float)$p_ShippingPrice,
			'ShippingInsurance'			=>	(int)$order_row['ShippingInsurance'],
			'ShippingInsurancePrice'	=>	(float)$p_ShippingInsurancePrice,
			'TotalWeight'				=>	$order_row['TotalWeight'],
		);
		$p_UserDiscount=$p_UserDiscount?(100-$p_UserDiscount):0;
		$p_CouponDiscount=$p_CouponDiscount?($p_CouponDiscount/100):0;
		$data=array(
			'ProductPrice'				=>	(float)$p_ProductPrice,
			'Discount'					=>	(float)$p_Discount,
			'DiscountPrice'				=>	(float)$p_DiscountPrice,
			'UserDiscount'				=>	(float)$p_UserDiscount,
			'CouponDiscount'			=>	(float)$p_CouponDiscount,
			'CouponPrice'				=>	(float)$p_CouponPrice,
			'ShippingPrice'				=>	(float)$p_ShippingPrice,
			'ShippingInsurancePrice'	=>	(float)$p_ShippingInsurancePrice,
			'PayAdditionalFee'			=>	(float)$p_PayAdditionalFee,
			'PayAdditionalAffix'		=>	(float)$p_PayAdditionalAffix,
			'UpdateTime'				=>	$c['time']
		);
		db::update('orders', "OrderId='$p_OrderId'", $data);
		
		$total_price=sprintf('%01.2f', orders::orders_price($data, 1, 1));

		$data['OrderId']=$p_OrderId;
		$data['HandingFee']=$total_price-orders::orders_price($data, 0, 1);
		$data['TotalAmount']=$total_price;
		$p_UserDiscount || $data['UserDiscount'] = 100-(float)$p_UserDiscount;
		$json_data=str::json_data(str::str_code($data, 'stripslashes'));
		$json_shipping_data=str::json_data(str::str_code($shipping_data, 'stripslashes'));
		manage::operation_log('修改订单价格:'.$order_row['OId']);
		unset($order_row, $shipping_data);
		exit(str::json_data(array('status'=>1, 'info'=>$data, 'text'=>$json_data, 'shipping'=>$json_shipping_data)));
	}
	
	public static function orders_shipping_method(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$order_row=db::get_one('orders', "OrderId='$p_OrderId'");
		!$order_row && ly200::e_json('', -1);
		
		if($order_row['ShippingExpress']=='' && $order_row['ShippingMethodSId']==0 && $order_row['ShippingMethodType']==''){ //多个发货地
			$isOverseas=1;
		}else{ //单个发货地
			$isOverseas=0;
		}
		
		$info=$pro_info_ary=array();
		$sProdInfo=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$p_OrderId'", "o.*,p.IsFreeShipping");
		$total_weight=$total_volume=$s_total_weight=$s_total_volume=0;
		foreach($sProdInfo as $v){
			$price=($v['Price']+$v['PropertyPrice'])*$v['Qty']*($v['Discount']<100?$v['Discount']/100:1);
			if(!$pro_info_ary[$v['OvId']]){
				$pro_info_ary[$v['OvId']]=array('Weight'=>0, 'Volume'=>0, 'tWeight'=>0, 'tVolume'=>0, 'Price'=>0, 'IsFreeShipping'=>0);
			}
			$pro_info_ary[$v['OvId']]['tWeight']+=($v['Weight']*$v['Qty']);
			$pro_info_ary[$v['OvId']]['tVolume']+=($v['Volume']*$v['Qty']);
			$pro_info_ary[$v['OvId']]['tQty']+=$v['Qty'];
			$pro_info_ary[$v['OvId']]['Price']+=$price;
			if((int)$v['IsFreeShipping']==1){//免运费
				$pro_info_ary[$v['OvId']]['IsFreeShipping']=1; //其中有免运费
			}else{
				$pro_info_ary[$v['OvId']]['Weight']+=($v['Weight']*$v['Qty']);
				$pro_info_ary[$v['OvId']]['Volume']+=($v['Volume']*$v['Qty']);
				$pro_info_ary[$v['OvId']]['Qty']+=$v['Qty'];
			}
		}
		//产品包装重量
		$cartProAry=orders::orders_product_weight($p_OrderId, 1);
		foreach((array)$cartProAry['tWeight'] as $k=>$v){//$k是OvId
			foreach((array)$v as $k2=>$v2){//$k2是ProId
				$pro_info_ary[$k]['tWeight']+=$v2;
			}
		}
		foreach((array)$cartProAry['Weight'] as $k=>$v){//$k是OvId
			foreach((array)$v as $k2=>$v2){//$k2是ProId
				$pro_info_ary[$k]['Weight']+=$v2;
			}
		}
		ksort($pro_info_ary); //排列正序
		
		$shipping_cfg=db::get_one('shipping_config', "Id='1'");
		$weight=@ceil($total_weight);
		$config_ary=str::json_data(htmlspecialchars_decode(db::get_value('config', "GroupId='products_show' and Variable='Config'", 'Value')), 'decode');
		
		$row=db::get_all('shipping_area a left join shipping s on a.SId=s.SId', "a.AId in(select AId from shipping_country where CId='{$order_row['ShippingCId']}')", 's.Express, s.IsWeightArea, s.WeightArea, s.ExtWeightArea, s.VolumeArea, s.IsUsed, s.IsAPI, s.FirstWeight, s.ExtWeight, s.StartWeight, s.MinWeight, s.MaxWeight, s.MinVolume, s.MaxVolume, s.FirstMinQty, s.FirstMaxQty, s.ExtQty, s.WeightType, a.*', 'if(s.MyOrder>0, s.MyOrder, 100000) asc, a.SId asc, a.AId asc');
		$row_ary=array();
		foreach($row as $v){
			!$row_ary[$v['SId']] && $row_ary[$v['SId']]=array('info'=>$v, 'overseas'=>array());
			$row_ary[$v['SId']]['overseas'][$v['OvId']]=$v;
		}
		unset($row);
		foreach($row_ary as $key=>$val){
			$row=$val['info'];
			$isOvId=0;
			foreach($pro_info_ary as $k=>$v){ $val['overseas'][$k] && $isOvId+=1; }//循环产品数据
			if($isOvId==0){
				$info[1][]=array('SId'=>'', 'Name'=>'', 'Brief'=>'', 'IsAPI'=>'', 'type'=>'', 'ShippingPrice'=>'-1');
				continue;
			}
			//循环产品数据 Start
			foreach($pro_info_ary as $k=>$v){
				$overseas=$val['overseas'][$k];
				$open=0;//默认不通过
				if(in_array($row['IsWeightArea'], array(0,1,2)) && ((float)$row['MaxWeight']?($v['tWeight']>=$row['MinWeight'] && $v['tWeight']<=$row['MaxWeight']):($v['tWeight']>=$row['MinWeight']))){//重量限制
					$open=1;
				}elseif($row['IsWeightArea']==4 && ($v['tWeight']>=$row['MinWeight'] || $v['tVolume']>=$row['MinVolume'])){//重量限制+体积限制
					$open=1;
				}elseif($row['IsWeightArea']==3){//按数量计算，直接不限制
					$open=1;
				}
				if($overseas && $row['IsUsed']==1 && $open==1){
					$sv=array(
						'SId'		=>	$row['SId'],
						'Name'		=>	$row['Express'],
						'type'		=>	'',
					);
					if(($v['IsFreeShipping']==1 && $v['Weight']==0) || ((int)$config_ary['freeshipping'] && $v['Weight']==0) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingPrice']>0 && $v['Price']>=$overseas['FreeShippingPrice']) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingWeight']>0 && $v['Weight']<$overseas['FreeShippingWeight']) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingPrice']==0 && $overseas['FreeShippingWeight']==0)){
						$shipping_price=0;
					}else{
						$shipping_price=0;
						if($overseas['IsWeightArea']==1 || ($overseas['IsWeightArea']==2 && $v['Weight']>=$overseas['StartWeight'])){
							//重量区间 重量混合
							$WeightArea=str::json_data($overseas['WeightArea'], 'decode');
							$WeightAreaPrice=str::json_data($overseas['WeightAreaPrice'], 'decode');
							$areaCount=count($WeightArea)-1;
							foreach($WeightArea as $k2=>$v2){
								if($k2<=$areaCount && (($WeightArea[$k2+1] && $v['Weight']<$WeightArea[$k2+1]) || (!$WeightArea[$k2+1] && $v['Weight']>=$v2))){
									if($overseas['WeightType']==1){//按每KG计算
										$shipping_price=$WeightAreaPrice[$k2]*$v['Weight'];
									}else{//按整价计算
										$shipping_price=$WeightAreaPrice[$k2];
									}
									break;
								}
							}
							$v['Weight']>$WeightArea[$areaCount] && $shipping_price=$WeightAreaPrice[$areaCount]*$v['Weight'];
						}elseif($overseas['IsWeightArea']==3){
							//按数量
							$shipping_price=$overseas['FirstQtyPrice'];//先收取首重费用
							$ExtQtyValue=$v['Qty']>$overseas['FirstMaxQty']?$v['Qty']-$overseas['FirstMaxQty']:0;//超出的数量
							if($ExtQtyValue){//续重
								$shipping_price+=(float)(@ceil($ExtQtyValue/$overseas['ExtQty'])*$overseas['ExtQtyPrice']);
							}
						}elseif($overseas['IsWeightArea']==4){
							//重量体积混合计算
							$weight_shipping_price=$volume_shipping_price=0;
							if($v['Weight']>=$overseas['MinWeight']){//重量
								$WeightArea=str::json_data($overseas['WeightArea'], 'decode');
								$WeightAreaPrice=str::json_data($overseas['WeightAreaPrice'], 'decode');
								$areaCount=count($WeightArea)-1;
								foreach($WeightArea as $k2=>$v2){
									if($k2<=$areaCount && (($WeightArea[$k2+1] && $v['Weight']<$WeightArea[$k2+1]) || (!$WeightArea[$k2+1] && $v['Weight']>=$v2))){
										if($overseas['WeightType']==1){//按每KG计算
											$weight_shipping_price=$WeightAreaPrice[$k2]*$v['Weight'];
										}else{//按整价计算
											$weight_shipping_price=$WeightAreaPrice[$k2];
										}
										break;
									}
								}
								$v['Weight']>$WeightArea[$areaCount] && $weight_shipping_price=$WeightAreaPrice[$areaCount]*$v['Weight'];
							}
							if($v['Volume']>=$overseas['MinVolume']){//体积
								$VolumeArea=str::json_data($overseas['VolumeArea'], 'decode');
								$VolumeAreaPrice=str::json_data($overseas['VolumeAreaPrice'], 'decode');
								$areaCount=count($VolumeArea)-1;
								foreach($VolumeArea as $k2=>$v2){
									if($k2<=$areaCount && (($VolumeArea[$k2+1] && $v['Volume']<$VolumeArea[$k2+1]) || (!$VolumeArea[$k2+1] && $v['Volume']>=$v2))){
										$volume_shipping_price=$VolumeAreaPrice[$k2]*$v['Volume'];
										break;
									}
								}
								$v['Volume']>$VolumeArea[$areaCount] && $volume_shipping_price=$VolumeAreaPrice[$areaCount]*$v['Volume'];
							}
							$shipping_price=max($weight_shipping_price, $volume_shipping_price);
						}else{
							//首重续重
							$ExtWeightArea=str::json_data($overseas['ExtWeightArea'], 'decode');
							$ExtWeightAreaPrice=str::json_data($overseas['ExtWeightAreaPrice'], 'decode');
							$areaCount=count($ExtWeightArea)-1;
							$ExtWeightValue=$v['Weight']>$overseas['FirstWeight']?$v['Weight']-$overseas['FirstWeight']:0;//超出的重量
							if($areaCount>0){
								$shipping_price=$overseas['FirstPrice'];//先收取首重费用
								foreach($ExtWeightArea as $k2=>$v2){
									if($v['Weight']>$v2 && $ExtWeightArea[$k2+1]){
										$ext=$v['Weight']>$ExtWeightArea[$k2+1]?($ExtWeightArea[$k2+1]-$v2):($v['Weight']-$v2);
										$shipping_price+=(float)(@ceil($ext/$overseas['ExtWeight'])*$ExtWeightAreaPrice[$k2]);
									}elseif($v['Weight']>$v2 && !$ExtWeightArea[$k2+1]){//达到以上费用
										$ext=$v['Weight']-$v2;
										$shipping_price+=(float)(@ceil($ext/$overseas['ExtWeight'])*$ExtWeightAreaPrice[$k2]);
									}
								}
							}else{
								$shipping_price=(float)(@ceil($ExtWeightValue/$overseas['ExtWeight'])*$ExtWeightAreaPrice[0]+$overseas['FirstPrice']);
							}
						}
						if($overseas['AffixPrice']){//附加费用
							$shipping_price+=$overseas['AffixPrice'];
						}
					}
					$sv['ShippingPrice']=$shipping_price;
					$sv['InsurancePrice']=cart::get_insurance_price_by_price($v['Price']+$shipping_price);
					$info[$k][]=$sv;
				}
			}
		}
		
		unset($order_row, $shipping_cfg, $air_row, $ocean_row, $sv, $row_ary, $shipping_data);
		if($info){
			$info_ary=array();
			foreach($info as $k=>$v){
				$sort_ary=array();
				foreach($v as $k2=>$v2){
					$sort_ary[$k2]=$v2['ShippingPrice'];
				}
				asort($sort_ary);
				foreach($sort_ary as $k2=>$v2){
					$info_ary[$k][]=$info[$k][$k2];
				}
			}
			ly200::e_json(array('isOverseas'=>$isOverseas, 'info'=>$info_ary), 1);
		}else{
			ly200::e_json('', 0);
		}
	}

	public static function orders_mod_shipping(){	//更新发货方式
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$orders_row=db::get_one('orders', "OrderId='$p_OrderId'");
		!$orders_row && ly200::e_json('', -1);
		
		$is_insurance=array();
		foreach($p_ShippingMethodSId as $k=>$v){
			$is_insurance[$k]=(int)$p_ShippingInsurance[$k];
		}
		if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']==''){ //多个发货地
			$data=array(
				'ShippingOvExpress'			=>	addslashes($orders_row['ShippingOvExpress']),
				'ShippingOvSId'				=>	addslashes(str::json_data($p_ShippingMethodSId)),
				'ShippingOvType'			=>	addslashes(str::json_data($p_ShippingMethodType)),
				'ShippingOvInsurance'		=>	addslashes(str::json_data($is_insurance)),
				'ShippingOvPrice'			=>	addslashes($orders_row['ShippingOvPrice']),
				'ShippingOvInsurancePrice'	=>	addslashes($orders_row['ShippingOvInsurancePrice'])
			);
		}else{ //单个发货地
			$data=array(
				'ShippingExpress'			=>	$orders_row['ShippingExpress'],
				'ShippingMethodSId'			=>	(int)implode('', $p_ShippingMethodSId),
				'ShippingMethodType'		=>	implode('', $p_ShippingMethodType),
				'ShippingInsurance'			=>	(int)implode('', (array)$p_ShippingInsurance),
				'ShippingPrice'				=>	$orders_row['ShippingPrice'],
				'ShippingInsurancePrice'	=>	$orders_row['ShippingInsurancePrice']
			);
		}
		
		if((int)$p_AutoModShippingPrice==1){ //更新运费
			$SId=$p_ShippingMethodSId;
			$sType=$p_ShippingMethodType;
			$sInsurance=$is_insurance;
			$sProdInfo=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$p_OrderId'", "o.*, p.IsFreeShipping");
			$total_weight=$total_volume=0;
			$pro_info_ary=array();
			foreach($sProdInfo as $v){
				$item_price=($v['Price']+$v['PropertyPrice'])*$v['Qty']*($v['Discount']<100?$v['Discount']/100:1);
				if(!$pro_info_ary[$v['OvId']]){
					$pro_info_ary[$v['OvId']]=array('Weight'=>0, 'Volume'=>0, 'tWeight'=>0, 'tVolume'=>0, 'Price'=>0, 'IsFreeShipping'=>0);
				}
				$pro_info_ary[$v['OvId']]['tWeight']+=($v['Weight']*$v['Qty']);
				$pro_info_ary[$v['OvId']]['tVolume']+=($v['Volume']*$v['Qty']);
				$pro_info_ary[$v['OvId']]['Price']+=$item_price;
				if((int)$v['IsFreeShipping']==1){//免运费
					$pro_info_ary[$v['OvId']]['IsFreeShipping']=1; //其中有免运费
				}else{
					$pro_info_ary[$v['OvId']]['Weight']+=($v['Weight']*$v['Qty']);
					$pro_info_ary[$v['OvId']]['Volume']+=($v['Volume']*$v['Qty']);
				}
			}
			//产品包装重量
			$cartProAry=orders::orders_product_weight($p_OrderId, 1);
			foreach((array)$cartProAry['tWeight'] as $k=>$v){//$k是OvId
				foreach((array)$v as $k2=>$v2){//$k2是ProId
					$pro_info_ary[$k]['tWeight']+=$v2;
				}
			}
			foreach((array)$cartProAry['Weight'] as $k=>$v){//$k是OvId
				foreach((array)$v as $k2=>$v2){//$k2是ProId
					$pro_info_ary[$k]['Weight']+=$v2;
				}
			}
			$shipping_ary=orders::orders_shipping_method($SId, $orders_row['ShippingCId'], $sType, $sInsurance, $pro_info_ary);
			!$shipping_ary && ly200::e_json('', -2);
			
			$data['ShippingExpress']=addslashes($shipping_ary['ShippingExpress']);
			$data['ShippingPrice']=$shipping_ary['ShippingPrice'];
			$data['ShippingInsurancePrice']=$shipping_ary['ShippingInsurancePrice'];
			$data['ShippingOvExpress']=addslashes($shipping_ary['ShippingOvExpress']);
			$data['ShippingOvPrice']=addslashes($shipping_ary['ShippingOvPrice']);
			$data['ShippingOvInsurancePrice']=addslashes($shipping_ary['ShippingOvInsurancePrice']);
		}else{ //不更新运费
			$data['ShippingExpress']=addslashes(str::str_code(db::get_value('shipping', "SId='{$p_ShippingMethodSId}'", 'Express')));
			$shipping_ov_express_ary=array();
			foreach($p_ShippingMethodSId as $k=>$v){
				if($v==0 && $p_ShippingMethodType[$k]=='air'){
					$shipping_ov_express_ary[$k]=str::str_code(db::get_value('shipping_config', "Id='1'", 'AirName'));
				}elseif($v==0 && $p_ShippingMethodType[$k]=='ocean'){
					$shipping_ov_express_ary[$k]=str::str_code(db::get_value('shipping_config', "Id='1'", 'OceanName'));
				}else{
					$shipping_ov_express_ary[$k]=str::str_code(db::get_value('shipping', "SId='$v'", 'Express'));
				}
			}
			$data['ShippingOvExpress']=addslashes(str::json_data($shipping_ov_express_ary));
		}
		$data['UpdateTime']=$c['time'];
		
		db::update('orders', "OrderId='$p_OrderId'", $data);
		
		$data['ShippingExpress']=$data['ShippingExpress'];
		$data['ShippingMethodSId']=(int)$data['ShippingMethodSId'];
		$data['ShippingMethodType']=$data['ShippingMethodType'];
		$data['ShippingOvExpress']=str::json_data(stripslashes($data['ShippingOvExpress']), 'decode');
		$data['ShippingOvSId']=str::json_data(stripslashes($data['ShippingOvSId']), 'decode');
		$data['ShippingOvType']=str::json_data(stripslashes($data['ShippingOvType']), 'decode');
		$data['ShippingOvInsurance'	]=str::json_data(stripslashes($data['ShippingOvInsurance']), 'decode');
		$data['ShippingOvPrice']=str::json_data(stripslashes($data['ShippingOvPrice']), 'decode');
		$data['ShippingOvInsurancePrice']=str::json_data(stripslashes($data['ShippingOvInsurancePrice']), 'decode');
		
		$json_ary=array(
			'OrderId'					=>	$p_OrderId,
			'ShippingMethodSId'			=>	$data['ShippingMethodSId'],
			'ShippingPrice'				=>	$data['ShippingPrice'],
			'ShippingInsurance'			=>	(int)$data['ShippingInsurance'],
			'ShippingInsurancePrice'	=>	$data['ShippingInsurancePrice'],
			'TotalWeight'				=>	$orders_row['TotalWeight'],
			'TotalVolume'				=>	$orders_row['TotalVolume'],
			'ShippingOvExpress'			=>	$data['ShippingOvExpress'],
			'ShippingOvSId'				=>	$data['ShippingOvSId'],
			'ShippingOvType'			=>	$data['ShippingOvType'],
			'ShippingOvInsurance'		=>	$data['ShippingOvInsurance'],
			'ShippingOvPrice'			=>	$data['ShippingOvPrice'],
			'ShippingOvInsurancePrice'	=>	$data['ShippingOvInsurancePrice']
		);
		$json_data=str::json_data($json_ary);
		
		$orders_row=db::get_one('orders', "OrderId='$p_OrderId'");
		$data['TotalAmount']=orders::orders_price($orders_row, 1, 1);
		
		unset($orders_row, $shipping_ary, $json_ary);
		ly200::e_json(array('info'=>$data, 'text'=>$json_data), 1);
	}
	
	public static function orders_mod_status(){//更新订单状态
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$OrderId=(int)$p_OrderId;
		$OrderStatus=(int)$p_OrderStatus;
		$PassOrderStatus=(int)$p_PassOrderStatus;
		!in_array($OrderStatus, $c['manage']['mod_order_status'][$PassOrderStatus]) && exit(str::json_data(array('status'=>-1, 'msg'=>$c['manage']['lang_pack']['global']['do_error'])));
		$orders_row=db::get_one('orders', "OrderId='$OrderId'");
		
		$data=array('OrderStatus'=>$OrderStatus, 'UpdateTime'=>$c['time']);
		if($OrderStatus==5){//发货
			if(!$p_TrackingNumber && !$p_ShippingTime && !$p_Remarks){//多个发货地
				$data['OvTrackingNumber']=addslashes(str::json_data($p_OvTrackingNumber));
				$data['OvRemarks']=addslashes(str::json_data($p_OvRemarks));
				$OvShippingStatus=array();
				foreach($p_OvShippingTime as $k=>$v){
					$p_OvShippingTime[$k]=strtotime($v);
					$OvShippingStatus[$k]=1;
				}
				$data['OvShippingStatus']=addslashes(str::json_data($OvShippingStatus));
				$data['OvShippingTime']=addslashes(str::json_data($p_OvShippingTime));
			}else{//单个发货地
				$data['TrackingNumber']=@trim($p_TrackingNumber);
				$data['ShippingTime']=@strtotime($p_ShippingTime);
				$data['Remarks']=$p_Remarks;
			}
			orders::orders_shipping_api('pre_alert_order', $orders_row['OId']);//申请API运单发货
		}elseif($OrderStatus==7){
			$data['Remarks']=$p_Remarks;
		}
		db::update('orders', "OrderId='$OrderId'", $data);
		$orders_row=db::get_one('orders', "OrderId='$OrderId'");//更新订单信息
		
		$Log='Update order status from '.$c['orders']['status'][$PassOrderStatus].' to '.$c['orders']['status'][$OrderStatus];
		orders::orders_log((int)$_SESSION['Manage']['UserId'], $_SESSION['Manage']['UserName'], $OrderId, $OrderStatus, $Log, 1);
		
		if((int)$c['manage']['config']['LessStock']==1 && $OrderStatus>3 && $OrderStatus<7){
			orders::orders_products_update($OrderStatus, $orders_row);//付款减库存
			orders::orders_shipping_api('create_order', $orders_row['OId']);//创建API运单
		}
		if((int)$c['manage']['config']['LessStock']==0 && $OrderStatus>3 && $OrderStatus<7){
			orders::orders_user_update($OrderId);//付款清算会员数据
		}
		if($OrderStatus==7){//取消订单
			orders::orders_products_update($OrderStatus, $orders_row, 1);
		}
		$OId=$orders_row['OId'];
		$OrderStatus==4 && orders::orders_sms($OId); //付款成功，发送短信
		/******************** 发邮件 ********************/
		$notice_config=str::json_data(db::get_value('config', 'GroupId="email" and Variable="notice"', 'Value'), 'decode');
		$ToAry=array($orders_row['Email']);
		$c['manage']['config']['AdminEmail'] && $ToAry[]=$c['manage']['config']['AdminEmail'];
		if($OrderStatus==4 && (int)$notice_config['order_payment']){//付款成功，等待发货
			include($c['root_path'].'/static/static/inc/mail/order_payment.php');
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
			//$c['manage']['config']['OrdersSms'] && ly200::sendsms($c['manage']['config']['OrdersSms'], "您的订单已经付款成功，订单号：{$OId}");
		}else if($OrderStatus==5 && (int)$notice_config['order_shipped']){//订单发货
			include($c['root_path'].'/static/static/inc/mail/order_shipped.php');
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
		}else if($OrderStatus==7 && (int)$notice_config['order_cancel']){//取消订单
			include($c['root_path'].'/static/static/inc/mail/order_cancel.php');
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
		}else{//其他状态
			if((int)$notice_config['order_change']){
				include($c['root_path'].'/static/static/inc/mail/order_change.php');
				ly200::sendmail($ToAry, $mail_title, $mail_contents);
			}
		}
		/******************** 发邮件结束 ********************/
		
		manage::operation_log('更新订单状态');
		
		unset($c, $data);
		exit(str::json_data(array('status'=>1)));
	}
	
	public static function orders_track_no(){	//更新运单号
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$OrderId=(int)$p_OrderId;
		db::update('orders', "OrderId='$OrderId'", array('TrackingNumber'=>@trim($p_TrackingNumber), 'UpdateTime'=>$c['time']));
		manage::operation_log('更新订单运单号');
		exit(str::json_data(array('status'=>1)));
	}
	
	public static function orders_remarks(){//更新备注内容
		global $c, $cfg;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$OrderId=(int)$p_OrderId;
		db::update('orders', "OrderId='$OrderId'", array('Remarks'=>@trim($p_Remarks), 'UpdateTime'=>$c['time']));
		manage::operation_log('更新订单备注内容');
		exit(str::json_data(array('status'=>1)));
	}
	
	public static function orders_remark_log(){//添加备注日志
		global $c, $cfg;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$OrderId=(int)$p_OrderId;
		db::insert('orders_remark_log', array('OrderId'=>$OrderId, 'Log'=>@trim($p_Log), 'AccTime'=>$c['time']));
		manage::operation_log('添加订单备注日志');
		exit(str::json_data(array('status'=>1)));
	}
	
	public static function orders_prod_edit_edit(){//订单产品编辑
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$OrderId=(int)$p_OrderId;
		$orders_row=str::str_code(db::get_one('orders', "OrderId='$OrderId'"));
		foreach($p_LId as $k=>$v){
			$Price=(float)$p_Price[$k];
			$Qty=(int)$p_Qty[$k];
			$Qty<1 && $Qty=1;
			$data=array(
				'Price'			=>	sprintf('%01.2f', $Price),
				'Qty'			=>	$Qty,
				'PropertyPrice'	=>	0, //属性价格清0
				'Discount'		=>	100 //折扣调成100
			);
			db::update('orders_products_list', "LId='$v'", $data);
		}
		$ProductPrice=0;
		$prod_row=db::get_all('orders_products_list', "OrderId='$OrderId'", 'Price, PropertyPrice, Discount, Qty');
		foreach($prod_row as $v){
			$ProductPrice+=($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty'];
		}
		db::update('orders', "OrderId='$OrderId'", array('ProductPrice'=>(float)$ProductPrice, 'UpdateTime'=>$c['time']));
		manage::operation_log('修改订单产品 OId:'.$orders_row['OId']);
		ly200::e_json('', 1);
	}
	
	public static function orders_prod_del(){//订单产品删除
		global $c;
		$LId=(int)$_GET['LId'];
		$prod_list_row=db::get_one('orders_products_list', "LId={$LId}");
		file::del_file($prod_list_row['PicPath']);//删除订单产品图片
		$OrderId=$prod_list_row['OrderId'];
		$orders_row=db::get_one('orders', "OrderId='$OrderId'", 'OId');
		db::delete('orders_products_list', "LId={$LId}");
		$ProductPrice=0;
		$prod_row=db::get_all('orders_products_list', "OrderId='$OrderId'", 'Price, PropertyPrice, Discount, Qty');
		foreach($prod_row as $v){
			$ProductPrice+=($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty'];
		}
		db::update('orders', "OrderId='$OrderId'", array('ProductPrice'=>(float)$ProductPrice, 'UpdateTime'=>$c['time']));
		manage::operation_log('删除订单产品 OId:'.$orders_row['OId']);
		ly200::e_json('', 1);
	}
	
	public static function orders_shipped(){ //提交运单号（运单管理）
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$p_OvId=(int)$p_OvId;
		$order_row=db::get_one('orders', "OrderId='{$p_OrderId}'");
		if($order_row){
			if(!$p_Data) ly200::e_json('', 0);
			$data=array();
			$IsCompleted=0;
			$total_count=(int)db::get_row_count('orders_waybill', "OrderId='{$p_OrderId}'");
			$completed_count=(int)db::get_row_count('orders_waybill', "OrderId='{$p_OrderId}' and Status=1");
			if($p_Number=='00'){ //默认
				if($order_row['ShippingExpress']=='' && $order_row['ShippingMethodSId']==0 && $order_row['ShippingMethodType']==''){ //多个发货地
					$TrackingNumber=str::json_data(htmlspecialchars_decode($order_row['OvTrackingNumber']), 'decode');
					$ShippingTime=str::json_data(htmlspecialchars_decode($order_row['OvShippingTime']), 'decode');
					$Remarks=str::json_data(htmlspecialchars_decode($order_row['OvRemarks']), 'decode');
					$TrackingNumber[$p_OvId]=trim($p_Data['TrackingNumber']);
					$ShippingTime[$p_OvId]=strtotime($p_Data['ShippingTime']);
					$Remarks[$p_OvId]=$p_Data['Remarks'];
					$ShippingStatus[$p_OvId]=1;
					$data['OvTrackingNumber']=addslashes(str::json_data($TrackingNumber));
					$data['OvShippingTime']=addslashes(str::json_data($ShippingTime));
					$data['OvRemarks']=addslashes(str::json_data($Remarks));
					$data['OvShippingStatus']=addslashes(str::json_data($ShippingStatus));
				}else{ //单个发货地
					$data['TrackingNumber']=addslashes(trim($p_Data['TrackingNumber']));
					$data['ShippingTime']=strtotime($p_Data['ShippingTime']);
					$data['Remarks']=addslashes($p_Data['Remarks']);
				}
				if($total_count==0 || ($total_count>0 && $total_count==$completed_count)){ //没有分单 或者 其余的分单都已发货
					$IsCompleted=1;
				}
				$data['UpdateTime']=$c['time'];
				db::update('orders', "OrderId='{$p_OrderId}'", $data);
			}else{ //分单
				$data['TrackingNumber']=addslashes(trim($p_Data['TrackingNumber']));
				$data['ShippingTime']=strtotime($p_Data['ShippingTime']);
				$data['Remarks']=addslashes($p_Data['Remarks']);
				$data['Status']=1;
				$orders_waybill_row=db::get_one('orders_waybill', "OrderId='{$p_OrderId}' and Number='{$p_Number}'");
				db::update('orders_waybill', "WId='{$orders_waybill_row['WId']}'", $data);
				if($total_count==$completed_count+1){ //其余的分单都已发货
					$IsCompleted=1;
				}
			}
			if($order_row['OrderStatus']==4 && $IsCompleted==1){
				db::update('orders', "OrderId='{$p_OrderId}'", array('OrderStatus'=>5, 'UpdateTime'=>$c['time']));
				/******************** 发邮件 ********************/
				$orders_row=$order_row; //传递给新的变量
				$notice_config=str::json_data(db::get_value('config', 'GroupId="email" and Variable="notice"', 'Value'), 'decode');
				$ToAry=array($orders_row['Email']);
				$c['manage']['config']['AdminEmail'] && $ToAry[]=$c['manage']['config']['AdminEmail'];
				if((int)$notice_config['order_shipped']){//订单发货
					$trackingNumberStr=trim($p_Data['TrackingNumber']);
					$ShippingTimeStr=strtotime($p_Data['ShippingTime']);
					include($c['root_path'].'/static/static/inc/mail/order_shipped.php');
					ly200::sendmail($ToAry, $mail_title, $mail_contents);
				}
				/******************** 发邮件结束 ********************/
				manage::operation_log('更新订单状态');
			}
		}
		
		ly200::e_json('', 1);
	}
	
	public static function orders_products_waybill(){ //订单运单设置
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId=(int)$p_OrderId;
		$order_row=db::get_one('orders', "OrderId='{$p_OrderId}'");
		if($order_row){
			if($p_Number=='00'){ //未处理
				if(!$p_Data) ly200::e_json('', 0);
				foreach($p_Data as $k=>$v){
					db::update('orders_products_list', "OrderId='{$p_OrderId}' and Status=0 and LId='{$k}'", array('Status'=>1));
				}
				$ProInfo=addslashes(str::json_data(str::str_code($p_Data, 'stripslashes')));
				$max=(int)db::get_max('orders_waybill', "OrderId='{$p_OrderId}'", 'Number');
				if($max){
					$Num=$max+1;
				}else{ //新创建
					$Num=1;
				}
				$Num<10 && $Num='0'.$Num;
				$data=array(
					'OrderId'	=>	$p_OrderId,
					'Number'	=>	$Num,
					'ProInfo'	=>	$ProInfo
				);
				db::insert('orders_waybill', $data);
			}else{ //已处理
				$orders_waybill_row=db::get_one('orders_waybill', "OrderId='{$p_OrderId}' and Number='{$p_Number}'");
				db::delete('orders_waybill', "WId='{$orders_waybill_row['WId']}'");
			}
		}
		ly200::e_json('', 1);
	}
	
	//订单资料批量操作 Start
	public static function orders_import(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;//当前分开数
		$p_Worksheet=(int)$p_Worksheet;
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		$errerTxt='';
		!file_exists($c['root_path'].$p_ExcelFile) && ly200::e_json('文件不存在！');
		
		$objPHPExcel=PHPExcel_IOFactory::load($c['root_path'].$p_ExcelFile);
		$sheet=$objPHPExcel->getSheet(0);//工作表0
		$highestRow=$sheet->getHighestRow();//取得总行数 
		$highestColumn=$sheet->getHighestColumn();//取得总列数
		
		//初始化第一阶段
		$Start=0;//开始执行位置
		$page_count=20;//每次分开导入的数量
		$total_pages=ceil(($highestRow-1)/$page_count);
		if($p_Number<$total_pages){//继续执行
			$Start=$page_count*$p_Number;
		}else{
			file::del_file($p_ExcelFile);
			manage::operation_log('订单批量上传快递');
			ly200::e_json('<p>批量上传完成</p>', 1);
		}
		//初始化第二阶段
		$all_order_ary=$all_order_id_ary=array();
		$orders_row=db::get_all('orders', 'OrderStatus=4');
		foreach($orders_row as $v){
			$all_order_id_ary[]=$v['OId'];
			$all_order_ary[$v['OId']]=$v;
		}
		//内容转换为数组 
		$data=$sheet->toArray();
		$data_ary=array();
		$i=-1;
		foreach($data as $k=>$v){//行
			if($k<1) continue;
			if($Start<=$k && $k<($Start+$page_count)){
				if($v[0]){
					$data_ary[]=$v;
				}
			}elseif($k>=($Start+$page_count)){
				break;
			}
		}
		unset($data, $orders_row);
		//开始导入
		$No=0;
		$update_sql=array();
		foreach((array)$data_ary as $key=>$val){
			$OId=trim($val[0]);//订单号
			if(!in_array($OId, $all_order_id_ary)){ $errerTxt.="<p>(上传失败) {$OId} 不属于“等待发货”状态</p>"; continue;}
			$TrackingNumber=trim($val[1]);//运单号
			if(!$TrackingNumber){ $errerTxt.="<p>(上传失败) {$OId} 运单号为空</p>"; continue;}
			$ShippingTime=trim($val[2]);//发货下单时间
			$ShippingTime=@strtotime($ShippingTime);
			if(!$ShippingTime){ $errerTxt.="<p>(上传失败) {$OId} 发货下单时间为空</p>"; continue;}
			$Remarks=trim($val[3]);//备注内容
			$OrderId=$all_order_ary[$OId]['OrderId'];
			//记录数据资料
			$data=array(
				'OrderId'			=>	$OrderId,
				'TrackingNumber'	=>	$TrackingNumber,
				'ShippingTime'		=>	$ShippingTime,
				'OrderStatus'		=>	5,
				'Remarks'			=>	addslashes($Remarks),
				'UpdateTime'		=>	$c['time']
			);
			foreach($data as $k=>$v){
				$update_sql[$k][$OrderId]=$v;
			}
			++$No;
			$OrderId && orders::orders_log((int)$_SESSION['Manage']['UserId'], $_SESSION['Manage']['UserName'], $OrderId, 5, 'Update order status from Awaiting Shipping to Shipment Shipped', 1);
		}
		if(is_array($update_sql) && count($update_sql)){
			$ides=implode(',', array_keys($update_sql['OrderId'])); 
			$len=count($update_sql)-1;
			$i=0;
			$sql="update orders set";
				foreach($update_sql as $k=>$v){
					if($k=='OrderId') continue;
					$sql.=" {$k} = case OrderId";
					foreach($v as $k2=>$v2){
						$sql.=sprintf(" when '%s' then '%s' ", $k2, $v2); 
					}
					$sql.='end'.(++$i<$len?',':'');
				}
			$sql.=" where OrderId in($ides)";
			$sql && db::query($sql);
		}
		/******************** 发邮件 ********************/
		$notice_config=str::json_data(db::get_value('config', 'GroupId="email" and Variable="notice"', 'Value'), 'decode');
		if((int)$notice_config['order_shipped']){ //邮件通知开关【订单发货】
			foreach((array)$update_sql['OrderId'] as $k=>$v){
				$orders_row=db::get_one('orders', "OrderId='$k'");
				if(!$orders_row) continue;
				$OId=$orders_row['OId'];
				$ToAry=array($orders_row['Email']);
				$c['manage']['config']['AdminEmail'] && $ToAry[]=$c['manage']['config']['AdminEmail'];
				include($c['root_path'].'/static/static/inc/mail/order_shipped.php');
				ly200::sendmail($ToAry, $mail_title, $mail_contents);
			}
		}
		/******************** 发邮件结束 ********************/
		unset($all_order_ary, $all_order_id_ary);
		if($p_Number<$total_pages){//继续执行
			$item=($No+1<$page_count)?($page_count*$p_Number+$No):($page_count*($p_Number+1));
			ly200::e_json(array(($p_Number+1), $errerTxt.'<p>已上传'.$item.'个</p>'), 2);
		}
	}
	
	public static function import_excel_download(){
		global $c;
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		$orders_row=str::str_code(db::get_all('orders', 'OrderStatus=4', '*', 'OrderId desc'));
		
		$objPHPExcel=new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
		$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("Test result file");
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $c['manage']['lang_pack']['orders']['oid']);
		$objPHPExcel->getActiveSheet()->setCellValue('B1', $c['manage']['lang_pack']['orders']['shipping']['track_no']);
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $c['manage']['lang_pack']['orders']['shipping']['ship'].$c['manage']['lang_pack']['orders']['time']);
		$objPHPExcel->getActiveSheet()->setCellValue('D1', $c['manage']['lang_pack']['orders']['payment']['contents']);
		
		$i=2;
		foreach($orders_row as $v){
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i, $v['OId'], PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, '');
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
			++$i;
		}
		
		//设置列的宽度  
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		
		//设置行的高度
		$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
		
		$objPHPExcel->getActiveSheet()->setTitle('导入快递');
		$objPHPExcel->setActiveSheetIndex(0);
		
		//保存Excel文件
		$ExcelName='order_export_'.str::rand_code();
		$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
		
		file::down_file("/tmp/{$ExcelName}.xls");
		file::del_file("/tmp/{$ExcelName}.xls");
		unset($c, $objPHPExcel, $ary, $attr_column);
		exit;
	}
	
	public static function orders_explode(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		if(!$p_Number) unset($_SESSION['OrderZip']);
		if($p_Time!=''){
			$Time_ary=@explode('/', $p_Time);
			$StartTime=@strtotime($Time_ary[0]);
			$EndTime=@strtotime($Time_ary[1]);
		}else{
			$month_time=3600*24*30;//30天内
			$StartTime=$c['time']-$month_time;
			$EndTime=$c['time'];
			$Time=@date('Y-m-d', $StartTime).'/'.@date('Y-m-d', $EndTime);
		}
		$p_OrderId='';
		if(!(int)$p_selectAll && $p_OrderIdStr){//不是全选
			$p_OrderIdStr=='|' && ly200::e_json('', 0);
			$p_OrderId=str_replace('|', ',', substr($p_OrderIdStr, 1, -1));
		}
		
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		//(A ~ EZ)
		$arr=range('A', 'Z');
		$ary=$arr;
		for($i=0; $i<5; ++$i){
			$num=$arr[$i];
			foreach($arr as $v){
				$ary[]=$num.$v;
			}
		}
		
		//所有产品属性
		$attribute_cart_ary=$vid_data_ary=array();
		$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['manage']['web_lang']}, ParentId, CartAttr, ColorAttr"));
		foreach($attribute_row as $v){
			$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['manage']['web_lang']}"]);
		}
		$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
		foreach($value_row as $v){
			$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['manage']['web_lang']}"];
		}
		
		//发货地
		$overseas_ary=array();
		$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
		foreach($overseas_row as $v){
			$overseas_ary[$v['OvId']]=$v;
		}
		
		//Add some data
		$page_count=600;//1000;//每次分开导出的数量
		$where='1';
		$p_Keyword && $where.=" and (OId like '%$p_Keyword%' or Email like '%$p_Keyword%' or concat(ShippingFirstName, ' ', ShippingLastName) like '%$p_Keyword%')";
		if($StartTime && $EndTime){
			if((int)$p_TimeType==1) $where.=" and PayTime>{$StartTime} and PayTime<{$EndTime}";
			else $where.=" and OrderTime>{$StartTime} and OrderTime<{$EndTime}";
		}
		$p_Status && $where.=" and OrderStatus='$p_Status'";
		$p_OrderId && $where.=" and OrderId in($p_OrderId)";
		$row_count=db::get_row_count('orders', $where, 'UserId');
		$total_pages=ceil($row_count/$page_count);
		$zipAry=array();//储存需要压缩的文件
		$save_dir='/tmp/';//临时储存目录
		file::mk_dir($save_dir);
		
		//$menu_ary=array('A'=>'订单ID', 'B'=>'订单号', 'C'=>'邮箱', 'D'=>'产品总额', 'E'=>'运费', 'F'=>'保险费', 'G'=>'订单总额', 'H'=>'总重量', 'I'=>'总体积', 'J'=>'订单状态', 'K'=>'配送方式', 'L'=>'付款方式', 'M'=>'时间', 'N'=>'优惠券', 'O'=>'收货姓名', 'P'=>'收货地址', 'Q'=>'收货地址2', 'R'=>'收货国家', 'S'=>'收货省份', 'T'=>'收货城市', 'U'=>'收货邮编', 'V'=>'收货电话', 'W'=>'账单姓名', 'X'=>'账单地址', 'Y'=>'账单地址2', 'Z'=>'账单国家', 'AA'=>'账单省份', 'AB'=>'账单城市', 'AC'=>'账单邮编', 'AD'=>'账单电话', 'AE'=>'运单号', 'AF'=>'备注内容', 'AG'=>'产品名称', 'AH'=>'产品编号', 'AI'=>'产品数量', 'AJ'=>'产品SKU');
		$menu_ary=array('A'=>$c['manage']['lang_pack']['orders']['orders'].' ID', 'B'=>$c['manage']['lang_pack']['orders']['oid'], 'C'=>$c['manage']['lang_pack']['global']['email'], 'D'=>$c['manage']['lang_pack']['orders']['info']['product_price'], 'E'=>$c['manage']['lang_pack']['orders']['info']['charges'], 'F'=>$c['manage']['lang_pack']['orders']['info']['insurance'], 'G'=>$c['manage']['lang_pack']['orders']['total_price'], 'H'=>$c['manage']['lang_pack']['orders']['info']['weight'], 'I'=>$c['manage']['lang_pack']['orders']['info']['volume'], 'J'=>$c['manage']['lang_pack']['orders']['orders_status'], 'K'=>$c['manage']['lang_pack']['orders']['info']['ship_info'], 'L'=>$c['manage']['lang_pack']['orders']['payment_method'], 'M'=>$c['manage']['lang_pack']['orders']['time'], 'N'=>$c['manage']['lang_pack']['orders']['info']['coupon'], 'O'=>$c['manage']['lang_pack']['orders']['export']['shipname'], 'P'=>$c['manage']['lang_pack']['orders']['export']['shipaddress'], 'Q'=>$c['manage']['lang_pack']['orders']['export']['shipaddress2'], 'R'=>$c['manage']['lang_pack']['orders']['export']['shipcountry'], 'S'=>$c['manage']['lang_pack']['orders']['export']['shipstate'], 'T'=>$c['manage']['lang_pack']['orders']['export']['shipcity'], 'U'=>$c['manage']['lang_pack']['orders']['export']['shipzip'], 'V'=>$c['manage']['lang_pack']['orders']['export']['shipphone'], 'W'=>$c['manage']['lang_pack']['orders']['export']['billname'], 'X'=>$c['manage']['lang_pack']['orders']['export']['billaddress'], 'Y'=>$c['manage']['lang_pack']['orders']['export']['billaddress2'], 'Z'=>$c['manage']['lang_pack']['orders']['export']['billcountry'], 'AA'=>$c['manage']['lang_pack']['orders']['export']['billstate'], 'AB'=>$c['manage']['lang_pack']['orders']['export']['billcity'], 'AC'=>$c['manage']['lang_pack']['orders']['export']['billzip'], 'AD'=>$c['manage']['lang_pack']['orders']['export']['billphone'], 'AE'=>$c['manage']['lang_pack']['orders']['shipping']['track_no'], 'AF'=>$c['manage']['lang_pack']['orders']['payment']['contents'], 'AG'=>$c['manage']['lang_pack']['orders']['export']['proname'], 'AH'=>$c['manage']['lang_pack']['orders']['export']['pronumber'], 'AI'=>$c['manage']['lang_pack']['orders']['export']['proqty'], 'AJ'=>$c['manage']['lang_pack']['orders']['export']['prosku'], 'AK'=>$c['manage']['lang_pack']['orders']['export']['proattr']);
		//$menu_row=str::str_code(db::get_value('config', 'GroupId="orders_export" and Variable="Menu"', 'Value'));
		//$menu_value=str::json_data(htmlspecialchars_decode($menu_row), 'decode');
		$menu_value=array();
		foreach((array)$p_Menu as $k=>$v){
			$menu_value[$v]=1;
		}
		
		if($p_Number<$total_pages){
			$page=$page_count*$p_Number;
			$orders_row=str::str_code(db::get_limit('orders', $where, '*', 'OrderId desc', $page, $page_count));
			$objPHPExcel=new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			$objPHPExcel->setActiveSheetIndex(0);
			$i=0;
			foreach($menu_value as $k=>$v){
				if($v==1){
					$objPHPExcel->getActiveSheet()->setCellValue($ary[$i].'1', $menu_ary[$k]);
					++$i;
				}
			}
			$num=2;
			foreach($orders_row as $val){
				$OrderId=$val['OrderId'];
				$pro_info=array(); //初始化
				if($menu_value['AG']==1 || $menu_value['AH']==1 || $menu_value['AI']==1 || $menu_value['AJ']==1){ //开启产品信息
					$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$OrderId'", 'o.KeyId, o.BuyType, o.Name, o.Qty, o.SKU as OrderSKU, o.Property, p.Prefix, p.Number, p.SKU', 'o.LId asc');
					foreach((array)$order_list_row as $k=>$v){
						if($v['BuyType']==4){
							//组合促销
							$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
							if(!$package_row) continue;
							$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
							$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
							$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
							$pro_where=='' && $pro_where=0;
							$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
							$attr=array();
							$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
							foreach((array)$products_row as $k2=>$v2){
								$pro_info['AG'][]='[Sales]'.$v2['Name'.$c['manage']['web_lang']];
								$pro_info['AH'][]=$v2['Prefix'].$v2['Number'];
								$pro_info['AI'][]=($k2==0?$v['Qty']:'');
								$pro_info['AJ'][]=($v2['OrderSKU']?$v2['OrderSKU']:$v2['SKU']);
								if($menu_value['AK']==1){
									$i=0;
									if($k2==0){
										foreach((array)$attr as $k3=>$z){
											if($k3=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
											$pro_info['AK'][]=($k3=='Overseas'?'Ships from':$k3).': '.$z;
											if($i>0) $pro_info['AG'][]=$pro_info['AH'][]=$pro_info['AI'][]=$pro_info['AJ'][]='';
											++$i;
										}
										if((int)$c['manage']['config']['Overseas']==1 && $v['OvId']==1){
											$pro_info['AK'][]='Ships from: '.$overseas_ary[$v['OvId']]['Name'.$c['manage']['web_lang']];
										}
									}else{
										$OvId=0;
										foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
											if($k3=='Overseas'){ //发货地
												$OvId=str_replace('Ov:', '', $v3);
												if((int)$c['manage']['config']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
												$pro_info['AK'][]='Ships from: '.$overseas_ary[$OvId]['Name'.$c['manage']['web_lang']];
											}else{
												$pro_info['AK'][]=$attribute_ary[$k3][1].': '.$vid_data_ary[$k3][$v3];
											}
											if($i>0) $pro_info['AG'][]=$pro_info['AH'][]=$pro_info['AI'][]=$pro_info['AJ'][]='';
											++$i;
										}
										if((int)$c['manage']['config']['Overseas']==1 && $OvId==1){
											$pro_info['AK'][]='Ships from: '.$overseas_ary[$OvId]['Name'.$c['manage']['web_lang']];
										}
									}
								}
							}
						}else{
							$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
							$pro_info['AG'][]=$v['Name'];
							$pro_info['AH'][]=$v['Prefix'].$v['Number'];
							$pro_info['AI'][]=$v['Qty'];
							$pro_info['AJ'][]=($v['OrderSKU']?$v['OrderSKU']:$v['SKU']);
							if($menu_value['AK']==1){
								$i=0;
								foreach((array)$attr as $k2=>$v2){
									if($k2=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									$pro_info['AK'][]=($k2=='Overseas'?'Ships from':$k2).': '.$v2;
									if($i>0) $pro_info['AG'][]=$pro_info['AH'][]=$pro_info['AI'][]=$pro_info['AJ'][]='';
									++$i;
								}
								if((int)$c['manage']['config']['Overseas']==1 && $v['OvId']==1){
									$pro_info['AK'][]='Ships from: '.$overseas_ary[$v['OvId']]['Name'.$c['manage']['web_lang']];
								}
							}
						}
					}
				}else{
					$pro_info=array('AG'=>array(0=>''), 'AH'=>array(0=>''), 'AI'=>array(0=>''), 'AJ'=>array(0=>''), 'AK'=>array(0=>''));
				}
				//收货信息
				$paypal_address_row=str::str_code(db::get_one('orders_paypal_address_book', "OrderId='$OrderId' and IsUse=1"));
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
						'FirstName'			=>	$val['ShippingFirstName'],
						'LastName'			=>	$val['ShippingLastName'],
						'AddressLine1'		=>	$val['ShippingAddressLine1'],
						'AddressLine2'		=>	$val['ShippingAddressLine2'],
						'City'				=>	$val['ShippingCity'],
						'State'				=>	$val['ShippingState'],
						'Country'			=>	$val['ShippingCountry'],
						'ZipCode'			=>	$val['ShippingZipCode'],
						'CountryCode'		=>	$val['ShippingCountryCode'],
						'PhoneNumber'		=>	$val['ShippingPhoneNumber']
					);
				}
				//快递信息
				$Express_ary=$TrackingNumber_ary=$Remarks_ary=array();
				if($val['ShippingExpress']=='' && $val['ShippingMethodSId']==0 && $val['ShippingMethodType']==''){ //多个发货地
					$shipping_ary=array(
						'ShippingExpress'		=>	str::json_data(htmlspecialchars_decode($val['ShippingOvExpress']), 'decode'),
						'ShippingSId'			=>	str::json_data(htmlspecialchars_decode($val['ShippingOvSId']), 'decode'),
						'ShippingType'			=>	str::json_data(htmlspecialchars_decode($val['ShippingOvType']), 'decode'),
						'TrackingNumber'		=>	str::json_data(htmlspecialchars_decode($val['OvTrackingNumber']), 'decode'),
						'Remarks'				=>	str::json_data(htmlspecialchars_decode($val['OvRemarks']), 'decode')
					);
					foreach($shipping_ary['ShippingSId'] as $k=>$v){
						$Express_ary[]=$shipping_ary['ShippingExpress'][$k];
						if($shipping_ary['TrackingNumber'][$k]) $TrackingNumber_ary[]=$shipping_ary['TrackingNumber'][$k];
						if($shipping_ary['Remarks'][$k]) $Remarks_ary[]=$shipping_ary['Remarks'][$k];
					}
				}else{ //单个发货地
					$shipping_cfg=(int)$val['ShippingMethodSId']?db::get_one('shipping', "SId='{$val['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
					$Express_ary[]=(int)$val['ShippingMethodSId']?$shipping_cfg['Express']:($val['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);
					$TrackingNumber_ary[]=$val['TrackingNumber'];
					$Remarks_ary[]=$val['Remarks'];
				}
				$orders_waybill_row=db::get_all('orders_waybill', "OrderId='{$OrderId}'");
				foreach($orders_waybill_row as $v){
					$TrackingNumber_ary[]=$v['TrackingNumber'];
					$Remarks_ary[]=$v['Remarks'];
				}
				
				$value_ary=array(
					'A'=>$OrderId,
					'B'=>$val['OId'],
					'C'=>$val['Email'],
					'D'=>$c['manage']['currency_symbol'].sprintf('%01.2f', $val['ProductPrice']),
					'E'=>$c['manage']['currency_symbol'].sprintf('%01.2f', $val['ShippingPrice']),
					'F'=>$c['manage']['currency_symbol'].sprintf('%01.2f', $val['ShippingInsurancePrice']),
					'G'=>$c['manage']['currency_symbol'].sprintf('%01.2f', orders::orders_price($val, 0, 1)),
					'H'=>$val['TotalWeight'],
					'I'=>$val['TotalVolume'],
					'J'=>$c['orders']['status'][$val['OrderStatus']],
					//'K'=>(int)$val['ShippingMethodSId']?$shipping_cfg['Express']:($val['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']),
					'K'=>implode('|', $Express_ary),
					'L'=>$val['PaymentMethod'],
					'M'=>date('Y-m-d H:i:s', $val['OrderTime']),
					'N'=>$val['CouponCode']?$val['CouponCode'].' '.($val['CouponPrice']?$c['manage']['currency_symbol'].sprintf('%01.2f', $val['CouponPrice']):$val['CouponDiscount'].'%'):'N/A',
					'O'=>$shipto_ary['FirstName'].' '.$shipto_ary['LastName'],
					'P'=>$shipto_ary['AddressLine1'],
					'Q'=>$shipto_ary['AddressLine2'],
					'R'=>$shipto_ary['Country'],
					'S'=>$shipto_ary['State'],
					'T'=>$shipto_ary['City'],
					'U'=>$shipto_ary['ZipCode'],
					'V'=>$shipto_ary['CountryCode'].' '.$shipto_ary['PhoneNumber'],
					'W'=>$val['BillFirstName'].' '.$val['BillLastName'],
					'X'=>$val['BillAddressLine1'],
					'Y'=>$val['BillAddressLine2'],
					'Z'=>$val['BillCountry'],
					'AA'=>$val['BillState'],
					'AB'=>$val['BillCity'],
					'AC'=>$val['BillZipCode'],
					'AD'=>$val['BillCountryCode'].' '.$val['BillPhoneNumber'],
					//'AE'=>$val['TrackingNumber'],
					'AE'=>implode('|', $TrackingNumber_ary),
					//'AF'=>$val['Remarks'],
					'AF'=>implode('|', $Remarks_ary),
					'AG'=>$pro_info['AG'],
					'AH'=>$pro_info['AH'],
					'AI'=>$pro_info['AI'],
					'AJ'=>$pro_info['AJ'],
					'AK'=>$pro_info['AK'],
				);
				
				foreach((array)$pro_info['AG'] as $k=>$v){
					$j=0;
					foreach((array)$menu_value as $kk=>$vv){
						if($vv==1){
							if($kk=='B' || $kk=='AE'){ //订单号、快递单号
								$objPHPExcel->getActiveSheet()->setCellValueExplicit($ary[$j].$num, ($k?'':$value_ary[$kk]), PHPExcel_Cell_DataType::TYPE_STRING);
							}elseif(in_array($kk, array('AG', 'AH', 'AI', 'AJ', 'AK'))){ //产品信息
								$objPHPExcel->getActiveSheet()->setCellValue($ary[$j].$num, (is_array($value_ary[$kk])?$value_ary[$kk][$k]:$value_ary[$kk]));
							}else{
								$objPHPExcel->getActiveSheet()->setCellValue($ary[$j].$num, ($k?'':$value_ary[$kk]));
							}
							++$j;
						}
					}
					++$num;
				}
			}
			
			//设置列的宽度
			$j=0;
			foreach($menu_value as $k=>$v){
				if($v==1){
					$objPHPExcel->getActiveSheet()->getColumnDimension($ary[$j])->setWidth(20);
					++$j;
				}
			}
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
			$objPHPExcel->getActiveSheet()->setTitle('Simple');
			$objPHPExcel->setActiveSheetIndex(0);
			$ExcelName='orders_'.str::rand_code();
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."{$save_dir}{$ExcelName}.xls");
			$_SESSION['OrderZip'][]="{$save_dir}{$ExcelName}.xls";
			unset($objPHPExcel, $objWriter, $orders_row, $shipping_cfg);
			ly200::e_json(array(($p_Number+1), "{$c['manage']['lang_pack']['global']['export']} {$save_dir}{$ExcelName}.xls<br />"), 2);
		}else{
			if(count($_SESSION['OrderZip'])){
				ly200::e_json('', 1);
			}else{
				ly200::e_json('');
			}
		}
	}
	
	public static function orders_explode_down(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		
		if($g_Status=='ok' && count($_SESSION['OrderZip'])){	//开始打包
			$zip=new ZipArchive();
			$zipname='/tmp/orders_'.str::rand_code().'.zip';
			
			if($zip->open($c['root_path'].$zipname, ZIPARCHIVE::CREATE)===TRUE){
				foreach($_SESSION['OrderZip'] as $path){
					if(is_file($c['root_path'].$path)) $zip->addFile($c['root_path'].$path, $path);
				}
				$zip->close();
				file::down_file($zipname);
				file::del_file($zipname);
				foreach($_SESSION['OrderZip'] as $path){
					if(is_file($c['root_path'].$path)) file::del_file($path);
				}
			}
		}
		unset($_SESSION['OrderZip']);
		exit();
	}
	
	public static function orders_explode_menu(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data_ary=array();
		$sort_ary=explode('|', $p_sort_order);
		foreach($sort_ary as $v){
			$data_ary[$v]=(in_array($v, $p_Menu)?1:0);
		}
		$MenuData=addslashes(str::json_data(str::str_code($data_ary, 'stripslashes')));
		manage::config_operaction(array('Menu'=>$MenuData), 'orders_export');
		manage::operation_log('添加订单备注日志');
		ly200::e_json($c['manage']['lang_pack']['orders']['export']['save_ok'], 1);
	}
	
	public static function orders_explode_products(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_OrderId=(int)$g_OrderId;
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		//Add some data
		//所有汇率资料
		$all_currency_ary=array();
		$currency_row=db::get_all('currency', '1', 'Currency, Symbol');
		foreach($currency_row as $k=>$v){
			$all_currency_ary[$v['Currency']]=$v;
		}
		//订单资料
		$orders_row=str::str_code(db::get_one('orders', "OrderId='$g_OrderId'"));
		$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$g_OrderId'", 'o.*, p.Prefix, p.Number, p.PicPath_0, p.Business', 'o.LId asc');
		//供应商资料
		$BId='0';
		foreach($order_list_row as $v){$v['Business'] && $BId.=','.$v['Business'];}
		if($BId!='0'){
			$business_row=str::str_code(db::get_all('business', "BId in($BId)", 'BId,Name,Url,Phone,TelePhone,Address'));
			$BusinessAry=array();
			foreach($business_row as $k=>$v){$BusinessAry[$v['BId']]=$v;}
		}
		//订单价格资料
		$Symbol=$orders_row['ManageCurrency']?$all_currency_ary[$orders_row['ManageCurrency']]['Symbol']:$c['manage']['currency_symbol'];
		$total_price=orders::orders_price($orders_row, 1, 1);
		$HandingFee=$total_price-orders::orders_price($orders_row, 0, 1);
		$UserDiscount=($orders_row['UserDiscount']>0 && $orders_row['UserDiscount']<100) ? $orders_row['UserDiscount'] : 100;
		$total_weight=$orders_row['TotalWeight'];
		//收货地址
		$paypal_address_row=str::str_code(db::get_one('orders_paypal_address_book', "OrderId='$g_OrderId' and IsUse=1"));
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
		//快递方式
		$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
		$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");
		
		if($orders_row && $order_list_row){
			$objPHPExcel=new PHPExcel();
			 
			//Set properties 
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			
			$objPHPExcel->setActiveSheetIndex(0);
			
			//设置列的宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
			
			//第一行
			$objPHPExcel->getActiveSheet()->setCellValue('A1', "{$c['manage']['lang_pack']['orders']['oid']}: {$orders_row['OId']}");
			$objPHPExcel->getActiveSheet()->mergeCells("A1:K1");//合并标题
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
			$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
			
			$objPHPExcel->getActiveSheet()->setCellValue('A2', $c['manage']['lang_pack']['global']['serial']);
			$objPHPExcel->getActiveSheet()->setCellValue('B2', $c['manage']['lang_pack']['products']['picture']);
			$objPHPExcel->getActiveSheet()->setCellValue('C2', $c['manage']['lang_pack']['orders']['export']['proname']);
			$objPHPExcel->getActiveSheet()->setCellValue('D2', $c['manage']['lang_pack']['orders']['export']['pronumber']);
			$objPHPExcel->getActiveSheet()->setCellValue('E2', $c['manage']['lang_pack']['orders']['export']['prosku']);
			$objPHPExcel->getActiveSheet()->setCellValue('F2', $c['manage']['lang_pack']['products']['attribute']);
			$objPHPExcel->getActiveSheet()->setCellValue('G2', $c['manage']['lang_pack']['orders']['remark']);
			$objPHPExcel->getActiveSheet()->setCellValue('H2', $c['manage']['lang_pack']['products']['products']['business']);
			$objPHPExcel->getActiveSheet()->setCellValue('I2', $c['manage']['lang_pack']['products']['products']['price']);
			$objPHPExcel->getActiveSheet()->setCellValue('J2', $c['manage']['lang_pack']['products']['products']['qty']);
			$objPHPExcel->getActiveSheet()->setCellValue('K2', $c['manage']['lang_pack']['orders']['amount']);
			
			$i=3;
			$prod_qty=$prod_price=0;
			foreach((array)$order_list_row as $k=>$v){
				if($v['BuyType']==4){
					//组合促销
					$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
					if(!$package_row) continue;
					$attr=array();
					$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
					$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
					$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
					$pro_where=='' && $pro_where=0;
					$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
					$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
					$prod_qty+=$v['Qty'];
					$prod_price+=$price*$v['Qty'];
					
					$start=$i;
					foreach((array)$products_row as $k2=>$v2){
						$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
						$jsonData=@str::json_data($BusinessAry[$v2['Business']]);
						$business_html='';
						if($v2['Business']){
							$business_html.=$BusinessAry[$v2['Business']]['Name']."\r\n";
							$business_html.=$c['manage']['lang_pack']['business']['business']['telephone'].': '.$BusinessAry[$v2['Business']]['Phone']."\r\n";
							$business_html.=$c['manage']['lang_pack']['business']['business']['phone'].': '.$BusinessAry[$v2['Business']]['Telephone']."\r\n";
							$business_html.=$c['manage']['lang_pack']['business']['business']['address'].': '.$BusinessAry[$v2['Business']]['Address']."\r\n";
						}
						$jsonProperty=@str::json_data(str::attr_decode($v2['Property']), 'decode');
						$Property_html='';
						$j=0;
						foreach((array)$jsonProperty as $k3=>$v3){
							if((int)$c['manage']['config']['Overseas']==0 && $k3=='Overseas') continue;
							$Property_html.=($j?"\r\n":'').$k3.':'.$v3;
							++$j;
						}
						
						$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $k+1);
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '');
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $v2['Name'.$c['manage']['web_lang']]);
						$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$i, $v2['Prefix'].$v2['Number'], PHPExcel_Cell_DataType::TYPE_STRING);
						$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $v2['SKU']);
						$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $Property_html);
						$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $v2['Remark']);
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $business_html);
						$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, ($k2==0?$Symbol.sprintf('%01.2f', $v['Price']):''));
						$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, ($k2==0?$v['Qty']:''));
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, ($k2==0?$Symbol.sprintf('%01.2f', $v['Price']):''));
						
						//添加图片
						if(is_file($c['root_path'].$img)){
							$objDrawing=new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('ZealImg');
							$objDrawing->setDescription('Image inserted by Zeal');
							$objDrawing->setPath($c['root_path'].$img);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setCoordinates('B'.$i);
							$objDrawing->setOffsetX(15);
							$objDrawing->setOffsetY(15);
							$objDrawing->getShadow()->setVisible(true);
							$objDrawing->getShadow()->setDirection(36);
							$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
						}
						
						$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);//设置行的宽度
						$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setWrapText(true);//自动换行
						$objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setWrapText(true);//自动换行
						$objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
						
						$end=$i;
						++$i;
					}
					//合并单元格
					if($start<$end){
						$objPHPExcel->getActiveSheet()->mergeCells("I{$start}:I{$end}");
						$objPHPExcel->getActiveSheet()->mergeCells("J{$start}:J{$end}");
						$objPHPExcel->getActiveSheet()->mergeCells("K{$start}:K{$end}");
					}
				}else{
					$LId=$v['LId'];
					$jsonData=@str::json_data($BusinessAry[$v['Business']]);
					$business_html='';
					if($v['Business']){
						$business_html.=$BusinessAry[$v['Business']]['Name']."\r\n";
						$business_html.=$c['manage']['lang_pack']['business']['business']['telephone'].': '.$BusinessAry[$v['Business']]['Phone']."\r\n";
						$business_html.=$c['manage']['lang_pack']['business']['business']['phone'].': '.$BusinessAry[$v['Business']]['Telephone']."\r\n";
						$business_html.=$c['manage']['lang_pack']['business']['business']['address'].': '.$BusinessAry[$v['Business']]['Address']."\r\n";
					}
					$jsonProperty=$v['Property']!=''?@str::json_data(str::attr_decode(stripslashes($v['Property'])), 'decode'):array();
					$Property_html='';
					$j=0;
					foreach((array)$jsonProperty as $k2=>$v2){
						if((int)$c['manage']['config']['Overseas']==0 && $k2=='Overseas') continue;
						$Property_html.=($j?"\r\n":'').$k2.':'.$v2;
						++$j;
					}
					$prod_qty+=$v['Qty'];
					$price=$v['Price']+$v['PropertyPrice'];
					$v['Discount']<100 && $price*=$v['Discount']/100;
					$prod_price+=$price*$v['Qty'];
					
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $k+1);
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '');
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $v['Name']);
					$objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$i, $v['Prefix'].$v['Number'], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $v['SKU']);
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $Property_html);
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $v['Remark']);
					$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $business_html);
					$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $Symbol.sprintf('%01.2f', $price));
					$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $v['Qty']);
					$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $Symbol.sprintf('%01.2f', $price*$v['Qty']));
					
					//添加图片
					if(is_file($c['root_path'].$v['PicPath'])){
						$objDrawing=new PHPExcel_Worksheet_Drawing();
						$objDrawing->setName('ZealImg');
						$objDrawing->setDescription('Image inserted by Zeal');
						$objDrawing->setPath($c['root_path'].$v['PicPath']);
						$objDrawing->setWidth(80);
						$objDrawing->setHeight(80);
						$objDrawing->setCoordinates('B'.$i);
						$objDrawing->setOffsetX(15);
						$objDrawing->setOffsetY(15);
						$objDrawing->getShadow()->setVisible(true);
						$objDrawing->getShadow()->setDirection(36);
						$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
					}elseif(is_file($c['root_path'].ly200::get_size_img($v['PicPath_0'], '240x240'))){
						$objDrawing=new PHPExcel_Worksheet_Drawing();
						$objDrawing->setName('ZealImg');
						$objDrawing->setDescription('Image inserted by Zeal');
						$objDrawing->setPath($c['root_path'].ly200::get_size_img($v['PicPath_0'], '240x240'));
						$objDrawing->setWidth(80);
						$objDrawing->setHeight(80);
						$objDrawing->setCoordinates('B'.$i);
						$objDrawing->setOffsetX(15);
						$objDrawing->setOffsetY(15);
						$objDrawing->getShadow()->setVisible(true);
						$objDrawing->getShadow()->setDirection(36);
						$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
					}
					
					$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);//设置行的宽度
					$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getAlignment()->setWrapText(true);//自动换行
					$objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setWrapText(true);//自动换行
					$objPHPExcel->getActiveSheet()->getStyle('H'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
					
					++$i;
				}
			}
			
			//产品总价(相加)
			$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:I{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("J{$i}", $prod_qty);
			$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $prod_price));
			++$i;
			
			//产品总价不一致才显示
			if($prod_price!=$orders_row['ProductPrice']){
				$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
				$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['info']['product_price']);
				$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $orders_row['ProductPrice']));
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				++$i;
			}
			
			//运费及保险费
			$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['info']['charges_insurance'].' (+)');
			$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice']));
			$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			++$i;
			
			//优惠券
			if($orders_row['CouponCode'] && ($orders_row['CouponPrice']>0 || $orders_row['CouponDiscount']>0)){
				$coupon_price=$orders_row['CouponPrice']>0?$orders_row['CouponPrice']:$orders_row['ProductPrice']*$orders_row['CouponDiscount'];
				$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
				$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['info']['coupon'].' (-)');
				$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $coupon_price));
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				++$i;
			}
			
			//会员优惠及其他优惠
			if($orders_row['Discount'] || $orders_row['DiscountPrice'] || $UserDiscount){
				$discount_price=$orders_row['ProductPrice']-($orders_row['ProductPrice']*((100-$orders_row['Discount'])/100)*($UserDiscount/100))+$orders_row['DiscountPrice'];
				$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
				$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['export']['othdiscount'].' (-)');
				$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $discount_price));
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				++$i;
			}
			
			//手续费
			if($HandingFee>0){
				$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
				$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['addfee'].' (+)');
				$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $HandingFee));
				$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				++$i;
			}
			
			//订单总额
			$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:J{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $c['manage']['lang_pack']['orders']['total_price']);
			$objPHPExcel->getActiveSheet()->setCellValue("K{$i}", $Symbol.sprintf('%01.2f', $total_price));
			$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			++$i;
			
			//订单总额
			$orders_info="{$c['manage']['lang_pack']['orders']['info']['order_info']}\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['oid']}: {$orders_row['OId']}\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['time']}: ".date('Y-m-d H:i:s', $orders_row['OrderTime'])."\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['orders_status']}: {$c['orders']['status'][$orders_row['OrderStatus']]}\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['info']['ship_info']}: ".((int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']))."\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['info']['weight']}: {$total_weight}KG\r\n";
			$orders_info.="{$c['manage']['lang_pack']['orders']['payment_method']}: {$orders_row['PaymentMethod']}\r\n";
			if($orders_row['OrderStatus']>4){
				$orders_info.="{$c['manage']['lang_pack']['orders']['shipping']['track_no']}: {$orders_row['TrackingNumber']}\r\n";
				$orders_info.="{$c['manage']['lang_pack']['orders']['remark']}: {$orders_row['Remarks']}\r\n";
			}
			$objPHPExcel->getActiveSheet()->mergeCells("A{$i}:C{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("A{$i}", $orders_info);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setWrapText(true);//自动换行
			$objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
			
			$ship_info="{$c['manage']['lang_pack']['orders']['export']['shipinfo']}\r\n";
			$ship_info.="{$c['manage']['lang_pack']['orders']['export']['shipname']}: {$shipto_ary['FirstName']} {$shipto_ary['LastName']}\r\n";
			$ship_info.="{$c['manage']['lang_pack']['orders']['export']['shipaddress']}: ".($shipto_ary['AddressLine1'].($shipto_ary['AddressLine2']?', '.$shipto_ary['AddressLine2']:'').', '.$shipto_ary['City'].', '.$shipto_ary['State'].', '.$shipto_ary['ZipCode'].', '.$shipto_ary['Country'].(($shipto_ary['CodeOption']&&$shipto_ary['TaxCode'])?'#'.$shipto_ary['CodeOption'].': '.$shipto_ary['TaxCode']:''))."\r\n";
			$ship_info.="{$c['manage']['lang_pack']['orders']['export']['shipphone']}: {$shipto_ary['CountryCode']}-{$shipto_ary['PhoneNumber']}\r\n";
			$objPHPExcel->getActiveSheet()->mergeCells("D{$i}:F{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("D{$i}", $ship_info);
			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setWrapText(true);//自动换行
			$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
			
			$bill_info="{$c['manage']['lang_pack']['orders']['export']['billinfo']}\r\n";
			$bill_info.="{$c['manage']['lang_pack']['orders']['export']['billname']}: {$orders_row['BillFirstName']} {$orders_row['BillLastName']}\r\n";
			$bill_info.="{$c['manage']['lang_pack']['orders']['export']['billaddress']}: ".($orders_row['BillAddressLine1'].($orders_row['BillAddressLine2']?', '.$orders_row['BillAddressLine2']:'').', '.$orders_row['BillCity'].', '.$orders_row['BillState'].', '.$orders_row['BillZipCode'].', '.$orders_row['BillCountry'].(($orders_row['BillCodeOption']&&$orders_row['BillTaxCode'])?'#'.$orders_row['BillCodeOption'].': '.$orders_row['BillTaxCode']:''))."\r\n";
			$bill_info.="{$c['manage']['lang_pack']['orders']['export']['billphone']}: {$orders_row['BillCountryCode']}-{$orders_row['BillPhoneNumber']}\r\n";
			$objPHPExcel->getActiveSheet()->mergeCells("G{$i}:K{$i}");//合并标题
			$objPHPExcel->getActiveSheet()->setCellValue("G{$i}", $bill_info);
			$objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setWrapText(true);//自动换行
			$objPHPExcel->getActiveSheet()->getStyle('G'.$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
			
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(150);//设置行的宽度
			++$i;
			
			//Rename sheet
			$objPHPExcel->getActiveSheet()->setTitle('Simple');
			
			//Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);
			
			//Save Excel 2007 file
			$ExcelName='orders_prod_'.$orders_row['OId'];
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
			unset($c, $objPHPExcel, $objWriter, $orders_row, $shipping_cfg);
			
			file::down_file("/tmp/{$ExcelName}.xls");
			file::del_file("/tmp/{$ExcelName}.xls");
			
			ly200::e_json('', 1);
		}else{
			js::location('?m=orders&a=orders');
		}
	}
	//订单资料批量操作 End
	
	//订单支付状态校验 Start
	public static function orders_payment_check(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OrderId='';
		$order_where='1';
		if(!(int)$p_selectAll && $p_OrderIdStr){//不是全选
			$p_OrderIdStr=='|' && ly200::e_json('', 0);
			$p_OrderId=str_replace('|', ',', substr($p_OrderIdStr, 1, -1));
			$p_OrderId && $order_where.=" and OrderId in($p_OrderId)";
		}else{
			$p_Keyword && $order_where.=" and (OId like '%$p_Keyword%' or Email like '%$p_Keyword%' or concat(ShippingFirstName, ' ', ShippingLastName) like '%$p_Keyword%')";
		}
		if($p_Time!=''){
			$Time_ary=@explode('/', $p_Time);
			$StartTime=@strtotime($Time_ary[0].' 00:00:00');
			$EndTime=@strtotime($Time_ary[1].' 23:59:59');
			$order_where.=" and OrderTime>{$StartTime} and OrderTime<{$EndTime}";
		}
		$payment_row=db::get_all('payment', "IsUsed=1 and Method like '{$p_Payment}%'", 'PId, Method, Attribute');
		foreach($payment_row as $k=>$pay){
			$account=str::json_data($pay['Attribute'], 'decode');
			$orders_row=db::get_limit('orders', $order_where." and OrderStatus<4 and PId='{$pay['PId']}'", 'OId', 'OrderId asc', 0, 100);
			if(!count($orders_row)) continue;
			
			if($p_Payment=='Globebill'){ //钱宝勾兑程序
				$orderNo='';
				foreach($orders_row as $v){$orderNo.=$v['OId'].',';}
				$orderNo=trim($orderNo, ',');
				$post=array(
					'merNo'		=>	$account['merNo'],
					'gatewayNo'	=>	$account['gatewayNo'],
					'orderNo'	=>	$orderNo,
					'signInfo'	=>	@hash("sha256", $account['merNo'].$account['gatewayNo'].$account['signkey'])
				);			
				//https://processor.globebill.com/servlet/NormalCustomerCheck
				//$result=ly200::curl('https://check.globebill.com/servlet/NormalCustomerCheck', $post);
				$result=ly200::curl('https://processor.globebill.com/servlet/NormalCustomerCheck', $post);
				$response=@simplexml_load_string($result);
				$data=array();
				$total_count=@count($response->tradeinfo);
				for($i=0; $i<$total_count; ++$i){
					$status=array(
						'merNo'			=>	(string)$response->tradeinfo[$i]->merNo,
						'gatewayNo'		=>	(string)$response->tradeinfo[$i]->gatewayNo,
						'orderNo'		=>	(string)$response->tradeinfo[$i]->orderNo,
						'authStatus'	=>	(string)$response->tradeinfo[$i]->authStatus,
						'queryResult'	=>	(string)$response->tradeinfo[$i]->queryResult,
						'tradeDate'		=>	(string)$response->tradeinfo[$i]->tradeDate,
					);
					$data[]=$status;
					if($status['merNo']=='' || $status['orderNo']=='') break;
					if($status['queryResult']=='1' || $status['queryResult']=='0'){
						$OId=$status['orderNo'];
						$order_row=db::get_one('orders', "OId='$OId' and OrderStatus<4");
						if(!$order_row) continue;
						if($status['queryResult']==1){
							$payment_result=orders::orders_payment_result(1, 'system', $order_row, '');
						}else if($status['queryResult']==0){
							if($order_row['OrderStatus']<3){
								$payment_result=orders::orders_payment_result(0, 'system', $order_row, '');
							}
							$payment_result='Payment wrong!';
						}
					}
					ob_start();
					print_r($status);
					echo "\r\n\r\n authStatus 授权状态";
					echo "\r\n\r\n $payment_result";
					$pay_log=ob_get_contents();
					ob_end_clean();
					file::write_file('/_pay_log_/globebill/verification/'.$pay['Method'].'/'.date('Y_m/', $c['time']), "{$OId}-".rand(1000, 9999).'.txt', $pay_log);//把返回数据写入文件
				}
				ob_start();
				print_r($response);
				print_r($post);
				print_r($data);
				echo '总数量：'.count($response->tradeinfo);
				$pay_log=ob_get_contents();
				ob_end_clean();
				file::write_file('/_pay_log_/globebill/verification/'.$pay['Method'].'/'.date('Y_m/', $c['time']), "0000-".date('YmdHis').".txt", $pay_log);//把返回数据写入文件
			}
		}
		
		ly200::e_json('', 1);
	}
	//订单支付状态校验 End
}
?>