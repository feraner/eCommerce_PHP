<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class orders{
	public static function orders_sms($OId){//订单短信通知
		global $c;
		$config=$c['config']['global']?$c['config']['global']:$c['manage']['config'];
		if($config['OrdersSms']){
			$Orderstatus=db::get_value('orders', "OId='$OId'", 'OrderStatus');
			if((int)$config['OrdersSmsStatus'][0]==1 && $Orderstatus==4){
				ly200::sendsms($config['OrdersSms'], array('您的订单已经付款成功', $OId));
			}elseif((int)$config['OrdersSmsStatus'][1]==1 && $Orderstatus==1){
				ly200::sendsms($config['OrdersSms'], array('您的网站有新的订单', $OId));
			}
		}
	}

	public static function orders_product_price($orders_row, $method=0){//订单产品总金额，防止切换汇率导致计算精度下降
		global $c;
		$price=0;
		$Symbol=cart::iconv_price(0, 1, $orders_row['Currency']);
		$row=db::get_all('orders_products_list', "OrderId='{$orders_row['OrderId']}'", 'Price, PropertyPrice, Discount, Qty');
		foreach((array)$row as $k=>$v){
			$price+=cart::iconv_price(($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1), 2, $orders_row['Currency'], 0)*$v['Qty'];
		}
		
		return ($method==0?$Symbol:'').sprintf('%01.2f', $price);
	}
	
	public static function orders_price($orders_row, $fee=0, $is_message=0){//统一给成汇率后的金额
		global $c;
		$v=$orders_row;
		$UserDiscount=($v['UserDiscount']>0 && $v['UserDiscount']<100) ? $v['UserDiscount'] : 100;
		//$prod_price=$v['ProductPrice']*((100-$v['Discount'])/100)*($UserDiscount/100)*(1-$v['CouponDiscount']);
		$prod_price=$v['ProductPrice'];//产品总价
		$prod_price-=$v['ProductPrice']*(1-(100-$v['Discount'])/100);//折扣
		$prod_price-=$v['ProductPrice']*(1-$UserDiscount/100);//会员折扣
		$prod_price-=$v['ProductPrice']*$v['CouponDiscount'];//优惠券折扣
		if($is_message){//后台固定默认货币显示
			$total_price=$prod_price+$v['ShippingPrice']+$v['ShippingInsurancePrice']-$v['CouponPrice']-$v['DiscountPrice'];//订单原始总价
		}else{
			$_price=orders::orders_product_price($v, 1);
			if($prod_price==$_price){
				$total_price=$_price+cart::iconv_price($v['ShippingPrice']+$v['ShippingInsurancePrice']-$v['CouponPrice']-$v['DiscountPrice'], 2, $v['Currency'], 0);
			}else{
				$total_price=cart::iconv_price($prod_price+$v['ShippingPrice']+$v['ShippingInsurancePrice']-$v['CouponPrice']-$v['DiscountPrice'], 2, $v['Currency'], 0);//订单原始总价
			}
		}
		$fee==1 && $total_price=$total_price*(1+$v['PayAdditionalFee']/100)+$v['PayAdditionalAffix'];//加上付款手续费
		$total_price=(float)substr(sprintf('%01.3f', $total_price), 0, -1);//舍弃四舍五入
		return $total_price;
	}
	
	/**
	 * 获取订单产品里面各自产品的包装重量计算
	 *
	 * @param: $method[int]		0 返回各自产品重量(自身+包装)，1 返回各自产品重量(包装)，2 返回总重量(自身+包装)
	 * @return array
	 */
	public static function orders_product_weight($OrderId, $method=0){//订单产品总金额，防止切换汇率导致计算精度下降
		global $c;
		$pro_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$OrderId'", 'o.ProId, o.Weight, o.Qty, o.OvId, p.PackingStart, p.PackingQty, p.PackingWeight, p.IsFreeShipping', 'o.LId desc');
		$pro_ary=array();
		$total=0;
		foreach((array)$pro_list_row as $k=>$v){
			$total+=($v['Weight']*$v['Qty']);
			if($method==1){//仅计算包装重量
				$pro_ary['tWeight'][$v['OvId']][$v['ProId']]+=0;
				$pro_ary['Weight'][$v['OvId']][$v['ProId']]+=0;
			}else{
				$pro_ary['tWeight'][$v['OvId']][$v['ProId']]+=($v['Weight']*$v['Qty']);
				(int)$v['IsFreeShipping']==0 && $pro_ary['Weight'][$v['OvId']][$v['ProId']]+=($v['Weight']*$v['Qty']);
			}
			!$pro_ary['Qty'][$v['OvId']][$v['ProId']] && $pro_ary['Qty'][$v['OvId']][$v['ProId']]=0;
			(int)$v['IsFreeShipping']==0 && $pro_ary['Qty'][$v['OvId']][$v['ProId']]+=$v['Qty'];//产品自身免运费，后面无需计算
			$pro_ary['Packing'][$v['ProId']]=array('Start'=>$v['PackingStart'], 'Qty'=>$v['PackingQty'], 'Weight'=>$v['PackingWeight']);
		}
		foreach((array)$pro_ary['Qty'] as $OvId=>$v){
			foreach((array)$v as $ProId=>$v2){
				if($pro_ary['Qty'][$OvId][$ProId]>0 && $v2>$pro_ary['Packing'][$ProId]['Start']){//包装计算
					$ext_qty=$v2-$pro_ary['Packing'][$ProId]['Start'];
					$packing_weight=(float)(@ceil($ext_qty/$pro_ary['Packing'][$ProId]['Qty'])*$pro_ary['Packing'][$ProId]['Weight']);
					$pro_ary['tWeight'][$OvId][$ProId]+=$packing_weight;
					$pro_ary['Weight'][$OvId][$ProId]+=$packing_weight;
					$total+=$packing_weight;
				}
			}
		}
		if($method==2){
			$return=$total;
		}else{
			$return=array(
				'tWeight'	=>	$pro_ary['tWeight'],
				'Weight'	=>	$pro_ary['Weight']
			);
		}
		return $return;
	}
	
	public static function orders_shipping_method($SId, $CId, $type, $insurance, $pro_info_ary, $shipping_api=array()){
		/*********************************************************************
		计算订单运费，返回数组
		'ShippingMethodSId',		快递公司Id 【数组】
		'ShippingMethodType',		非快递，[air]空运，[ocean]海运 【数组】
		'ShippingExpress',			运费名称 【数组】
		'ShippingPrice',			运费金额
		'ShippingInsurance',		运费保险，0 未启用，1 启用 【数组】
		'ShippingInsurancePrice'	保险费用
		*********************************************************************/
		global $c;
		if(!$SId && !$type) return false;	//未选择发货方式或者发货方式不存在
		$shipping_ary=array(
			'ShippingMethodSId'		=>	$SId,
			'ShippingMethodType'	=>	$type,
			'ShippingInsurance'		=>	$insurance
		);
		$error=0;
		foreach((array)$SId as $key=>$val){
			$_OvId=$key;
			$_SId=$val;
			$_Type=$type[$key];
			$_IsInsurance=(int)$insurance[$key];
			$_pro_info=$pro_info_ary[$_OvId];
			//计算 Start
			if($_SId){
				$shipping_cfg=db::get_one('shipping', "SId='{$_SId}'");
				$shipping_row=db::get_one('shipping_area', "SId='{$_SId}' and OvId='{$_OvId}' and AId in(select AId from shipping_country where SId='{$_SId}' and  CId='{$CId}')");
				!$shipping_row && $error=1;//发货方式不存在或者所选国家地区没有相应发货方式
				$shipping_ary['ShippingExpress'][$_OvId]=$shipping_cfg['Express'];
				if(($_pro_info['IsFreeShipping']==1 && $_pro_info['Weight']==0) || ((int)$c['config']['products_show']['Config']['freeshipping'] && $_pro_info['Weight']==0) || ($shipping_row['IsFreeShipping']==1 && $shipping_row['FreeShippingPrice']>0 && $_pro_info['Price']>=$shipping_row['FreeShippingPrice']) || ($shipping_row['IsFreeShipping']==1 && $shipping_row['FreeShippingWeight']>0 && $_pro_info['Weight']<$shipping_row['FreeShippingWeight']) || ($shipping_row['IsFreeShipping']==1 && $shipping_row['FreeShippingPrice']==0 && $shipping_row['FreeShippingWeight']==0)){
					$shipping_price=0;
				}elseif($shipping_cfg['IsAPI']>0 && $shipping_api[$shipping_cfg['IsAPI']]){
					$shipping_price=(float)$shipping_api[$shipping_cfg['IsAPI']];
				}else{
					$shipping_price=0;
					if($shipping_cfg['IsWeightArea']==1 || ($shipping_cfg['IsWeightArea']==2 && $_pro_info['Weight']>=$shipping_cfg['StartWeight'])){
						//重量区间 重量混合
						$WeightArea=str::json_data($shipping_cfg['WeightArea'], 'decode');
						$WeightAreaPrice=str::json_data($shipping_row['WeightAreaPrice'], 'decode');
						$areaCount=count($WeightArea)-1;
						foreach((array)$WeightArea as $k2=>$v2){
							if($k2<=$areaCount && (($WeightArea[$k2+1] && $_pro_info['Weight']<$WeightArea[$k2+1]) || (!$WeightArea[$k2+1] && $_pro_info['Weight']>=$v2))){
								if($shipping_cfg['WeightType']==1){//按每KG计算
									$shipping_price=$WeightAreaPrice[$k2]*$_pro_info['Weight'];
								}else{//按整价计算
									$shipping_price=$WeightAreaPrice[$k2];
								}
								break;
							}
						}
						$_pro_info['Weight']>$WeightArea[$areaCount] && $shipping_price=$WeightAreaPrice[$areaCount]*$_pro_info['Weight'];
					}elseif($shipping_cfg['IsWeightArea']==3){
						//按数量
						$shipping_price=$shipping_row['FirstQtyPrice'];//先收取首重费用
						$ExtQtyValue=$_pro_info['Qty']>$shipping_cfg['FirstMaxQty']?$_pro_info['Qty']-$shipping_cfg['FirstMaxQty']:0;//超出的数量
						if($ExtQtyValue){//续重
							$shipping_price+=(float)(@ceil($ExtQtyValue/$shipping_cfg['ExtQty'])*$shipping_row['ExtQtyPrice']);
						}
					}elseif($shipping_cfg['IsWeightArea']==4){
						//重量体积混合计算
						$weight_shipping_price=$volume_shipping_price=0;
						if($_pro_info['Weight']>=$shipping_cfg['MinWeight']){//重量
							$WeightArea=str::json_data($shipping_cfg['WeightArea'], 'decode');
							$WeightAreaPrice=str::json_data($shipping_row['WeightAreaPrice'], 'decode');
							$areaCount=count($WeightArea)-1;
							foreach((array)$WeightArea as $k2=>$v2){
								if($k2<=$areaCount && (($WeightArea[$k2+1] && $_pro_info['Weight']<$WeightArea[$k2+1]) || (!$WeightArea[$k2+1] && $_pro_info['Weight']>=$v2))){
									if($shipping_cfg['WeightType']==1){//按每KG计算
										$weight_shipping_price=$WeightAreaPrice[$k2]*$_pro_info['Weight'];
									}else{//按整价计算
										$weight_shipping_price=$WeightAreaPrice[$k2];
									}
									break;
								}
							}
							$_pro_info['Weight']>$WeightArea[$areaCount] && $weight_shipping_price=$WeightAreaPrice[$areaCount]*$_pro_info['Weight'];
						}
						if($_pro_info['Volume']>=$shipping_cfg['MinVolume']){//体积
							$VolumeArea=str::json_data($shipping_cfg['VolumeArea'], 'decode');
							$VolumeAreaPrice=str::json_data($shipping_row['VolumeAreaPrice'], 'decode');
							$areaCount=count($VolumeArea)-1;
							foreach((array)$VolumeArea as $k2=>$v2){
								if($k2<=$areaCount && (($VolumeArea[$k2+1] && $_pro_info['Volume']<$VolumeArea[$k2+1]) || (!$VolumeArea[$k2+1] && $_pro_info['Volume']>=$v2))){
									$volume_shipping_price=$VolumeAreaPrice[$k2]*$_pro_info['Volume'];
									break;
								}
							}
							$_pro_info['Volume']>$VolumeArea[$areaCount] && $volume_shipping_price=$VolumeAreaPrice[$areaCount]*$_pro_info['Volume'];
						}
						$shipping_price=max($weight_shipping_price, $volume_shipping_price);
					}else{
						//首重续重
						$ExtWeightArea[0]=$shipping_cfg['ExtWeight'];
						$ExtWeightAreaPrice[0]=$shipping_row['ExtPrice'];
						$shipping_cfg['ExtWeightArea'] && $ExtWeightArea=str::json_data($shipping_cfg['ExtWeightArea'], 'decode');
						$shipping_row['ExtWeightAreaPrice'] && $ExtWeightAreaPrice=str::json_data($shipping_row['ExtWeightAreaPrice'], 'decode');
						$areaCount=@count($ExtWeightArea)-1;
						$ExtWeightValue=$_pro_info['Weight']>$shipping_cfg['FirstWeight']?$_pro_info['Weight']-$shipping_cfg['FirstWeight']:0;//超出的重量
						if($areaCount>0){
							$shipping_price=$shipping_row['FirstPrice'];//先收取首重费用
							foreach((array)$ExtWeightArea as $k2=>$v2){
								if($_pro_info['Weight']>$v2 && $ExtWeightArea[$k2+1]){
									$ext=$_pro_info['Weight']>$ExtWeightArea[$k2+1]?($ExtWeightArea[$k2+1]-$v2):($_pro_info['Weight']-$v2);
									$shipping_price+=(float)(@ceil($ext/$shipping_cfg['ExtWeight'])*$ExtWeightAreaPrice[$k2]);
								}elseif($_pro_info['Weight']>$v2 && !$ExtWeightArea[$k2+1]){//达到以上费用
									$ext=$_pro_info['Weight']-$v2;
									$shipping_price+=(float)(@ceil($ext/$shipping_cfg['ExtWeight'])*$ExtWeightAreaPrice[$k2]);
								}
							}
						}else{
							$shipping_price=(float)(@ceil($ExtWeightValue/$shipping_cfg['ExtWeight'])*$ExtWeightAreaPrice[0]+$shipping_row['FirstPrice']);
						}
					}
					if($shipping_row['AffixPrice']){//附加费用
						$shipping_price+=$shipping_row['AffixPrice'];
					}
				}
				$shipping_ary['ShippingPrice'][$_OvId]=$shipping_price;
				$shipping_ary['ShippingInsurancePrice'][$_OvId]=$_IsInsurance==1?cart::get_insurance_price_by_price($_pro_info['Price']+$shipping_price, 0):0;
			}
			//计算 End
		}
		$shipCount=count($shipping_ary['ShippingExpress']);
		if($shipCount>1){ //多个发货地
			foreach((array)$shipping_ary as $k=>$v){
				$key=str_replace('Shipping', 'ShippingOv', $k);
				$shipping_ary[$key]=str::json_data($v);
				if($k=='ShippingPrice' || $k=='ShippingInsurancePrice'){ //合计运费和保险费
					$price=0;
					foreach((array)$v as $k2=>$v2){ $price+=(float)$v2; }
					$shipping_ary[$k]=$price;
				}else{ //储存为JSON格式
					$shipping_ary[$k]=''; //清空
				}
			}
		}else{ //仅有一个发货地
			foreach((array)$shipping_ary as $k=>$v){
				foreach((array)$v as $k2=>$v2){
					$shipping_ary[$k]=$v2;
				}
			}
		}
		return $shipping_ary;
	}

	public static function orders_log($UserId, $UserName, $OrderId, $OrderStatus, $Log, $IsAdmin=0){//订单日志
		global $c;
		db::insert('orders_log', array(
				'UserId'		=>	$UserId,
				'IsAdmin'		=>	$IsAdmin,
				'UserName'		=>	addslashes($UserName),
				'OrderId'		=>	$OrderId,
				'OrderStatus'	=>	$OrderStatus,
				'Ip'			=>	ly200::get_ip(),
				'Log'			=>	addslashes(stripslashes($Log)),
				'AccTime'		=>	$c['time']
			)
		);
		$OrderStatus==4 && db::update('orders', "OrderId='$OrderId'", array('PayTime'=>$c['time']));
	}
	
	public static function orders_user_update($OrderId){
		global $c;
		$orders_row=db::get_one('orders', "OrderId='$OrderId'");
		if($orders_row['UserId'] && $orders_row['OrderStatus']>3 && $orders_row['OrderStatus']<7 && $orders_row['CutUser']==0){//判断是否有会员，没有就直接跳过
			$isFee=($orders_row['OrderStatus']>=4 && $orders_row['OrderStatus']!=7)?1:0;
			$data=array();
			//更新会员消费记录
			$total_price=orders::orders_price($orders_row, $isFee, 1);
			$user_row=db::get_one('user', "UserId='{$orders_row['UserId']}'", 'Consumption, IsLocked');
			$Consumption=(float)$user_row['Consumption'];
			$IsLocked=(int)$user_row['IsLocked'];
			$CurPrice=$Consumption+$total_price;
			$data['Consumption']=$CurPrice;
			//更新会员等级
			if(!$IsLocked){//没有固定会员的会员等级
				$level_row=db::get_all('user_level', 'IsUsed=1', '*', 'FullPrice desc');
				foreach((array)$level_row as $v){
					if($CurPrice>$v['FullPrice']){
						$data['Level']=$v['LId'];
						break;
					}
				}
			}
			db::update('user', "UserId='{$orders_row['UserId']}'", $data);
			//即时更新会员等级
			(int)$_SESSION['User']['UserId']==$orders_row['UserId'] && $_SESSION['User']['Level']=$data['Level'];
			db::update('orders', "OrderId='{$orders_row['OrderId']}'", array('CutUser'=>1, 'UpdateTime'=>$c['time']));
		}
	}
	
	/**
	 * 添加购物车的价格计算
	 *
	 * @param: $OrderStatus[int]	订单状态
	 * @param: $orders_row[object]	订单数据
	 * @param: $type[int]			处理类型 1:返还库存 0:减库存
	 * @return
	 */
	public static function orders_products_update($OrderStatus, $orders_row, $type=0){
		global $c;
		if(($type==0 && $OrderStatus<7 && $orders_row['CutStock']==0) || ($type==1 && $OrderStatus==7 && $orders_row['CutCancel']==0)){
			$prod_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='Config'", 'Value'), 'decode');
			$pro_where='0';
			$item_row=db::get_all('orders_products_list', "OrderId='{$orders_row['OrderId']}'", '*', 'LId asc');
			foreach((array)$item_row as $v){
				$pro_row=db::get_one('products', "ProId='{$v['ProId']}'", 'CateId, ExtAttr, MaxOQ, Stock, IsCombination');
				$pro_where.=','.$v['ProId'];
				if($v['BuyType']==2){//秒杀产品
					db::query("update sales_seckill set RemainderQty=RemainderQty".($type==1?'+':'-')."{$v['Qty']} where ProId='{$v['ProId']}' and RemainderQty>0");
				}else{
					$pro_stock_where="Stock=Stock".($type==1?'+':'-')."{$v['Qty']}";
					//$type==0 && $pro_row['MaxOQ']>($pro_row['Stock']-$v['Qty']) && $pro_stock_where.=", MaxOQ=Stock";//如果最大购买量大于总库存
					$type==0 && $pro_row['MaxOQ']>($pro_row['Stock']-$v['Qty']) && $pro_stock_where.=', MaxOQ='.($pro_row['Stock']-$v['Qty']);//如果最大购买量大于总库存
					db::query("update products set {$pro_stock_where} where ProId='{$v['ProId']}'");
				}
				if($v['BuyType']==1){//团购产品
					db::query("update sales_tuan set BuyerCount=BuyerCount".($type==1?'-':'+')."{$v['Qty']} where ProId='{$v['ProId']}'");
				}
				//更改产品销售量
				db::query("update products set Sales=Sales".($type==1?'-':'+')."{$v['Qty']} where ProId='{$v['ProId']}'");
				//属性产品，更新对应的属性库存
				if($v['Property']){
					$attr_name_ary=$attr_value_ary=$ary=array();
					$attr_value='';
					//$Property=str::json_data($v['Property'], 'decode');
					$Property=str::json_data(str::attr_decode(stripslashes($v['Property'])), 'decode');
					$category_row=db::get_one('products_category', "CateId='{$pro_row['CateId']}'", 'AttrId, UId');
					$AttrId=(int)$category_row['AttrId'];
					if($category_row['UId']!='0,'){
						$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
						$AttrId=db::get_value('products_category', "CateId='$TopCateId'", 'AttrId');
					}
					$attr_row=db::get_all('products_attribute', "ParentId='{$AttrId}' and CartAttr=1", "AttrId, Name_{$v['Language']}");
					$attr_ary=array();
					$attr_where='-1';
					foreach((array)$attr_row as $v2){
						$attr_ary[$v2['AttrId']]=$v2["Name_{$v['Language']}"];
						$attr_where.=",{$v2['AttrId']}";
					}
					$attr_value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in($attr_where)", "VId, AttrId, Value_{$v['Language']}"));
					$attr_value_array=array();
					foreach((array)$attr_value_row as $v2){
						$attr_value_array['"'.$attr_ary[$v2['AttrId']].'":"'.$v2["Value_{$v['Language']}"].'"']=$v2['VId'];
						$attr_value_array[$attr_ary[$v2['AttrId']]][$v2["Value_{$v['Language']}"]]=$v2['VId'];
					}
					$OvId=1;
					if((int)$pro_row['IsCombination']){//开启规格组合
						$j=0;
						foreach((array)$Property as $k2=>$v2){
							if($k2=='Overseas'){//发货地
								$OvId=$v['OvId'];
							}else{
								$ary[]=$attr_value_array[$k2][$v2];
							}
						}
						sort($ary); //从小到大排序
						$Combination='|'.implode('|', $ary).'|';
						$combination_row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$v['ProId']}' and Combination='{$Combination}' and OvId='{$OvId}'"));
						if($combination_row){
							$combinatin_stock=(int)$combination_row['Stock'];
							if($type==1) $combinatin_stock+=$v['Qty'];//返还库存
							else $combinatin_stock-=$v['Qty'];//减库存
							$combinatin_stock<0 && $combinatin_stock=0;//库存低于0，固定为0
							db::query("update products_selected_attribute_combination set Stock='{$combinatin_stock}' where CId='{$combination_row['CId']}'");
						}
					}
				}
			}
			db::update('products', "ProId in($pro_where) and MaxOQ<0", array('MaxOQ'=>0));
			db::update('products', "ProId in($pro_where) and Stock<0", array('Stock'=>0));
			db::update('sales_seckill', "ProId in($pro_where) and RemainderQty<0", array('RemainderQty'=>0));
			$type==0 && db::update('orders', "OrderId='{$orders_row['OrderId']}'", array('CutStock'=>1, 'UpdateTime'=>$c['time']));//减库存
			$type==1 && db::update('orders', "OrderId='{$orders_row['OrderId']}'", array('CutCancel'=>1, 'UpdateTime'=>$c['time']));//返还库存
		}
		if($OrderStatus>3 && $OrderStatus<7 && $orders_row['CutUser']==0) orders::orders_user_update($orders_row['OrderId']);
	}
	
	public static function orders_payment_result($status, $UserName, $order_row, $error_log){ //status 1:支付成功 2:支付待处理 0:支付失败
		global $c;
		$OId=$order_row['OId'];
		$UserId=(int)($_SESSION['User']['UserId']?$_SESSION['User']['UserId']:$order_row['UserId']);
		if($status==1){ //支付成功
			$Log='Update order status from '.$c['orders']['status'][$order_row['OrderStatus']].' to '.$c['orders']['status'][4].'<br />'.$error_log;
			db::update('orders', "OId='$OId'", array('OrderStatus'=>4, 'UpdateTime'=>$c['time']));
			orders::orders_log($UserId, $UserName, $order_row['OrderId'], 4, $Log);
			orders::orders_products_update(4, $order_row);
			if((int)$c['config']['email']['notice']['order_payment']){ //邮件通知开关【付款成功】
				$ToAry=array($order_row['Email']);
				$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
				include($c['static_path'].'/inc/mail/order_payment.php');
				ly200::sendmail($ToAry, $mail_title, $mail_contents);
			}
			$c['config']['global']['OrdersSmsStatus'][0] && orders::orders_sms($OId);
			$result='Payment successful!';
			self::orders_shipping_api('create_order', $OId);
		}elseif($status==2){ //支付待处理
			$Log='Update order status from '.$c['orders']['status'][$order_row['OrderStatus']].' to '.$c['orders']['status'][2].'<br />'.$error_log;
			db::update('orders', "OId='$OId'", array('OrderStatus'=>2, 'UpdateTime'=>$c['time']));
			orders::orders_log($UserId, $UserName, $order_row['OrderId'], 2, $Log);
			$result='Payment processed! '.$error_log;
		}else{ //支付失败
			$Log='Update order status from '.$c['orders']['status'][$order_row['OrderStatus']].' to '.$c['orders']['status'][3].'<br />'.$error_log;
			db::update('orders', "OId='$OId'", array('OrderStatus'=>3, 'UpdateTime'=>$c['time']));
			orders::orders_log($UserId, $UserName, $order_row['OrderId'], 3, $Log);
			$result='Payment wrong! '.$error_log;
		}
		return $result;
	}
	
	public static function orders_shipping_api($Type, $OId){ //订单的运单API执行
		global $c;
		$c['plugin']=new plugin('api');
		$orders_row=db::get_one('orders', "OId='{$OId}'");
		$SIdAry=array(1=>$orders_row['ShippingMethodSId']);//发货方式(单个发货地)
		$orders_row['ShippingOvSId'] && $SIdAry=str::json_data(htmlspecialchars_decode($orders_row['ShippingOvSId']), 'decode');//发货方式(多个发货地)
		foreach($SIdAry as $k=>$v){
			$api_row=db::get_one('shipping_api', "1 and AId in(select IsAPI from shipping where IsAPI>0 and SId='{$v}')");
			if($api_row){
				$ApiName=strtolower($api_row['Name']);
				$ApiName=='4px' && $ApiName='_4px';
				$Attribute=str::json_data($api_row['Attribute'], 'decode');
				if($c['plugin']->trigger($ApiName, '__config', $Type)=='enable'){//API插件是否存在
					$api_data=array(
						'OrderId'	=>	$orders_row['OrderId'],
						'account'	=>	$Attribute
					);
					$c['plugin']->trigger($ApiName, $Type, $api_data);//调用API插件
				}
			}
		}
	}
	
	/**
	 * You may also like
	 *
	 * @param:	$Position[int]	使用位置 (0:购物车，1:会员中心/订单付款成功)
	 * @param:	$Num[int]		产品显示数量
	 * @return	object
	 */
	public static function you_may_also_like($Position, $Num=8){
		global $c;
		$IsUser=(int)$_SESSION['User']['UserId'];//是否登录了会员
		$Time=86400*30*3;//三个月
		if($Position==0){//购物车查询
			$ShoppingAry=array();
			$ShoppingRow=db::get_all('shopping_cart', $c['where']['cart'], "CId, ProId");
			foreach($ShoppingRow as $v){ $ShoppingAry[]=$v['ProId']; }
		}
		//if($IsUser){
		//	//会员  查找该会员三个月之内，所有的订单信息和订单产品信息
		//	$ProIdRow=db::get_all('orders_products_list', "OrderId in(select OrderId from orders where UserId='{$_SESSION['User']['UserId']}' and {$c['time']}<OrderTime+{$Time})", "LId, OrderId, ProId");
		//}else{
			//非会员  查找所有会员三个月之内，所有的订单信息和订单产品信息
			$ProIdRow=db::get_all('orders_products_list', "OrderId in(select OrderId from orders where {$c['time']}<OrderTime+{$Time})", "LId, OrderId, ProId");
		//}
		//初始化
		$ProIdAry=$OrderIdAry=$LikeProdAry=$ReturnAry=array();
		//当前订单的所有产品
		foreach($ProIdRow as $v){ $ProIdAry[]=$v['ProId']; }
		$ProIdAry=array_unique($ProIdAry);
		count($ShoppingAry)>0 && $ProIdAry=array_merge($ProIdAry, $ShoppingAry);//含有购物车产品信息，合并起来
		$ProWhere=(count($ProIdAry)>0?@implode(',', $ProIdAry):'0');
		//当前订单的产品ID所关联的其他订单ID
		$OrderIdRow=db::get_all('orders_products_list', "ProId in({$ProWhere})", 'OrderId');
		foreach($OrderIdRow as $v){ $OrderIdAry[]=$v['OrderId']; }
		$OrderIdAry=array_unique($OrderIdAry);
		$OrderWhere=(count($OrderIdAry)>0?@implode(',', $OrderIdAry):'0');
		//根据订单ID查出所有相关的订单产品ID
		$LikeProdRow=db::get_all('orders_products_list', "OrderId in({$OrderWhere}) and ProId not in({$ProWhere})", 'ProId');
		foreach($LikeProdRow as $v){ $LikeProdAry[]=$v['ProId']; }
		$LikeProdAry=array_unique($LikeProdAry);
		$LikeWhere=(count($LikeProdAry)>0?@implode(',', $LikeProdAry):'0');
		//产品列表
		$Column="ProId, IsPromotion, StartTime, EndTime, PicPath_0, Name{$c['lang']}, Price_0, Price_1, PageUrl, Wholesale, PromotionType, PromotionDiscount, PromotionPrice";
		$products_row=str::str_code(db::get_limit('products', '1'.$c['where']['products']." and ProId in({$LikeWhere})", $Column, $c['my_order'].'ProId desc', 0, $Num));
		$bestdeals_row=str::str_code(db::get_limit('products', '1'.$c['where']['products']." and ProId not in({$LikeWhere}) and IsBestDeals=1", $Column, $c['my_order'].'ProId desc', 0, $Num));//畅销产品，作为补充
		$products_list_row=array_merge($products_row, $bestdeals_row);//合并起来
		$i=0;
		foreach($products_list_row as $k=>$v){
			if($i<$Num){
				$ReturnAry[]=$v;
			}
			++$i;
		}
		return $ReturnAry;
	}
}
?>