<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class cart{
	/**
	 * 产品类型的详细解析
	 *
	 * @param:	$Data[object]	产品数据
	 * @key:	Type[int]		产品类型 (0:普通产品，1:团购，2:秒杀，3:组合购买，4:组合促销)
	 * @key:	KeyId[int]		主ID (包含，产品ID、团购ID、秒杀ID、组合促销ID)
	 * @key:	Qty[int]		购买的数量
	 * @key:	Attr[json]		产品属性 {"110":"1", "120":"1", "Overseas":"Ov:1"}
	 * @return	object
	 */
	public static function product_type($Data){
		global $c;
		$IsError=0;
		$Type=(int)$Data['Type'];
		$KeyId=(int)$Data['KeyId'];
		$Qty=(int)$Data['Qty'];
		$Attr=$Data['Attr'];
		$BuyType=$Type;
		$StartFrom=$Price=$Weight=$Volume=0;
		$Discount=100;//默认100% OFF折扣
		if($Type==1){
			//团购产品
			$tuan_row=db::get_one('sales_tuan', "TId='$KeyId' and BuyerCount<TotalCount and {$c['time']} between StartTime and EndTime", 'ProId,Price');
			if($tuan_row){
				$ProId=$tuan_row['ProId'];
				$Price=$tuan_row['Price'];
				$Qty=1;
			}else{
				$IsError=1;
			}
		}elseif($Type==4){
			//组合促销
			$package_row=db::get_one('sales_package', "PId='$KeyId'");
			$data_ary=str::json_data($package_row['Data'], 'decode');
			$pro_where=@str_replace('|', ',', $package_row['ProId'].substr($package_row['PackageProId'], 0, -1));
			$Qty=1;
			$Price=$package_row['CurPrice'];
			$IsCombination=0; //组合属性
			$pro_ary=@explode(',', $pro_where);
			foreach($pro_ary as $proid){
				$combinatin_ary=array();
				$pro_row=str::str_code(db::get_one('products', "ProId='{$proid}'"));
				$volume_ary=$pro_row['Cubage']?@explode(',', $pro_row['Cubage']):array(0,0,0);
				$Volume+=$volume_ary[0]*$volume_ary[1]*$volume_ary[2];
				$sProWeight=$pro_row['Weight'];//产品默认重量
				if((int)$pro_row['IsVolumeWeight']){//体积重
					$sVolumeWeight=($Volume*1000000)/5000;//先把立方米转成立方厘米，再除以5000
					$sVolumeWeight>$sProWeight && $sProWeight=$sVolumeWeight;
				}
				$package_row['ProId']==$proid && $IsCombination=(int)$pro_row['IsCombination'];
				$OvId=1; //发货地ID
				if($pro_row['AttrId'] && $data_ary[$proid]){//子产品的产品属性
					$ext_ary=array();
					foreach((array)$data_ary[$proid] as $k=>$v){
						if($k=='Overseas'){ //发货地
							$OvId=(int)str_replace('Ov:', '', $v);
							!$OvId && $OvId=1;//丢失发货地，自动默认China
						}else{
							$ext_ary[]=$v;
						}
					}
					sort($ext_ary); //从小到大排序
					$Combination='|'.implode('|', $ext_ary).'|';
					$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$proid}' and Combination='{$Combination}' and OvId='{$OvId}'"));
					if($row){
						$combinatin_ary=array($row['Price'], $row['Stock'], $row['Weight'], $row['SKU'], $row['IsIncrease']);
						if((int)$c['config']['products_show']['Config']['weight']){//产品属性重量+产品默认重量
							$Weight+=(float)$sProWeight+(float)$combinatin_ary[2];
						}else{//各自重量分开
							$Weight+=abs((float)$combinatin_ary[2]);//产品属性重量
						}
					}
				}else{
					$Weight+=(float)$sProWeight;//产品默认重量
				}
			}
			$Volume=sprintf('%.f', $Volume);//防止数额太大，程序自动转成科学计数法
			$products_row=array(
				'ProId'			=>	0,
				'Price'			=>	$Price,
				'Weight'		=>	$Weight,
				'Volume'		=>	$Volume,
				'Stock'			=>	1,
				'IsCombination'	=>	$IsCombination
			);
		}else{
			$ProId=$KeyId;
			if($Type==2){//秒杀产品
				$seckill_row=str::str_code(db::get_one('sales_seckill', "SId='$KeyId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
				if($seckill_row){
					$ProId=(int)$seckill_row['ProId'];
					$Price=$seckill_row['Price'];
				}else{
					$IsError=1;
				}
			}
			if($Type==3){//组合购买，检查是否为秒杀产品
				$BuyType=0;//自动换成普通产品类型
				$seckill_row=str::str_code(db::get_one('sales_seckill', "ProId='$KeyId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
				if($seckill_row){
					$ProId=(int)$seckill_row['ProId'];
					$Price=$seckill_row['Price'];
					$BuyType=2;//自动换成秒杀产品类型
				}
			}
		}
		if($ProId){
			$products_row=str::str_code(db::get_one('products', "SoldOut=0 and ProId='$ProId'", 'ProId, Name'.$c['lang'].', CateId, Number, SKU, Price_1, PicPath_0, Weight, Cubage, IsVolumeWeight, IsCombination, Stock, MOQ, MaxOQ, PageUrl, IsPromotion, PromotionType, PromotionPrice, PromotionDiscount, StartTime, EndTime, AttrId, Wholesale, SoldOut, IsSoldOut, SStartTime, SEndTime'));
			if($products_row){
				$volume_ary=$products_row['Cubage']?@explode(',', $products_row['Cubage']):array(0,0,0);
				$Volume=(float)$volume_ary[0]*(float)$volume_ary[1]*(float)$volume_ary[2];
				$Volume=sprintf('%.f', $Volume);//防止数额太大，程序自动转成科学计数法
				$products_row['Volume']=$Volume;
				$Weight=$ProWeight=$products_row['Weight'];//产品默认重量
				if((int)$products_row['IsVolumeWeight']){//体积重
					$VolumeWeight=($Volume*1000000)/5000;//先把立方米转成立方厘米，再除以5000
					$VolumeWeight>$ProWeight && $Weight=$ProWeight=$VolumeWeight;
				}
				if($BuyType==0 && $products_row['IsPromotion'] && $products_row['PromotionType'] && $products_row['StartTime']<$c['time'] && $c['time']<$products_row['EndTime']){//促销折扣
					$Discount=$products_row['PromotionDiscount'];
				}
				$StartFrom=(int)$products_row['MOQ']>0?(int)$products_row['MOQ']:1;	//起订量
				$Qty<$StartFrom && $Qty=$StartFrom;	//小于起订量
			}else{
				$IsError=1;
			}
		}
		if($Attr){//产品属性
			$Attr=@str::str_code(str::json_data(stripslashes($Attr), 'decode'), 'addslashes');
			ksort($Attr);
		}
		if($IsError==0){//返回成功
			return array(
				'BuyType'	=> $BuyType, 	//产品类型
				'KeyId'		=> $KeyId,		//主ID
				'ProId'		=> $ProId,		//产品ID
				'Price'		=> $Price,		//产品目前的单价
				'StartFrom'	=> $StartFrom,	//产品目前的起订量
				'Qty'		=> $Qty,		//购买的数量
				'Discount'	=> $Discount,	//折扣
				'Attr'		=> $Attr,		//产品属性
				'ProdRow'	=> $products_row,//产品数据
				'SeckRow'	=> $seckill_row	//秒杀数据
			);
		}else{//返回失败
			return false;
		}
	}
	
	/**
	 * 产品属性的详细解析
	 *
	 * @param:	$Data[object]		产品数据
	 * @key:	Type[int]			设置产品的属性搭配 (1:获取属性名称, 0:不获取属性名称)
	 * @key:	BuyType[int]		产品类型 (0:普通产品，1:团购，2:秒杀，3:组合购买，4:组合促销)
	 * @key:	ProId[int]			产品ID
	 * @key:	Price[float]		产品单价
	 * @key:	Attr[object]		产品属性 array("110"=>"1", "120"=>"1", "Overseas"=>"Ov:1")
	 * @key:	IsCombination[int]	产品组合属性开关 (1:开启，0:关闭)
	 * @key:	SKU[string]			产品SKU
	 * @key:	Weight[float]		产品重量
	 * @return	object
	 */
	public static function get_product_attribute($Data){
		global $c;
		//初始化
		$IsError=0;
		$Type=(int)$Data['Type'];
		$BuyType=(int)$Data['BuyType'];
		$ProId=(int)$Data['ProId'];
		$Price=(float)$Data['Price'];
		$Attr=$Data['Attr'];
		$IsCombination=(int)$Data['IsCombination'];
		$SKU=$Data['SKU'];
		$Weight=(float)$Data['Weight'];
		//初始化 二阶段
		$OvId=1;
		$PropertyPrice=0;
		$ProWeight=$Weight;
		$Property=$attrid_list=$vid_list='';
		$attr_name_ary=$combinatin_ary=$all_value_ary=$AttId_ary=$VId_ary=array();
		//列出所有颜色属性
		$ColorId=0;//颜色属性产品图片ID
		$color_id_ary=array();
		$color_row=db::get_all('products_attribute', 'ColorAttr=1', 'AttrId');
		foreach((array)$color_row as $v){ $color_id_ary[]=$v['AttrId']; }
		//执行
		$selected_count=db::get_row_count('products_selected_attribute', "ProId='{$ProId}' and IsUsed=1");
		if(count($Attr) && count($selected_count)>0){
			if($Type==1){
				$Property=array();
				foreach($Attr as $k=>$v){
					$key=str_replace('_', '', $k);
					$Attr[$key]=$v;//去掉包含_的键值
					if($key!=$k) unset($Attr[$k]);//删掉包含_的键值
					if($key=='Overseas') continue;//踢走发货地
					$AttId_ary[]=(int)$key;
					$VId_ary[]=(int)$v;
				}
				$attrid_list=str::ary_format($AttId_ary, 2);
				!$attrid_list && $attrid_list='-1';
				$cart_attr_row=db::get_all('products_attribute', "AttrId in ($attrid_list) and CartAttr=1", "AttrId, Name{$c['lang']}");
				foreach($cart_attr_row as $v){ $attr_name_ary[$v['AttrId']]=$v['Name'.$c['lang']]; }
				$vid_list=str::ary_format($VId_ary, 2);
				!$vid_list && $vid_list='-1';
				$value_row=str::str_code(db::get_all('products_attribute_value', "VId in ($vid_list)", "AttrId, VId, Value{$c['lang']}", $c['my_order'].'VId asc')); //属性选项
				foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v['Value'.$c['lang']]; }
			}
			if((int)$IsCombination && db::get_row_count('products_selected_attribute_combination', "ProId='{$ProId}'")){//开启规格组合
				$ext_ary=array();
				foreach((array)$Attr as $k=>$v){
					if($k=='Overseas'){//发货地
						$OvId=(int)str_replace('Ov:', '', $v);
						if(!$OvId){//丢失发货地，自动默认China
							$OvId=1;
							$Attr[$k]="Ov:{$OvId}";
						}
						$Type==1 && $Property['Overseas']=$c['config']['Overseas'][$OvId]['Name'.$c['lang']];
					}else{
						$ext_ary[]=(int)$v;
						$Type==1 && $Property[$attr_name_ary[(int)$k]]=$all_value_ary[(int)$k][(int)$v];
					}
					if(@in_array($k, $color_id_ary)) $ColorId=(int)$v;//颜色属性
				}
				sort($ext_ary); //从小到大排序
				$Combination='|'.implode('|', $ext_ary).'|';
				$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$ProId}' and Combination='{$Combination}' and OvId='{$OvId}'"));
				if($row){
					$combinatin_ary=array($row['Price'], $row['Stock'], $row['Weight'], $row['SKU'], $row['IsIncrease']);
					if((int)$combinatin_ary[4]){ //加价
						$PropertyPrice=(float)$combinatin_ary[0];
					}else{ //单价
						$BuyType!=1 && $BuyType!=2 && $Price=(float)$combinatin_ary[0];
					}
					if((int)$c['config']['products_show']['Config']['weight']){//产品属性重量+产品默认重量
						$Weight=(float)$ProWeight+(float)$combinatin_ary[2];
					}else{//各自重量分开
						$Weight=abs((float)$combinatin_ary[2]);//产品属性重量
					}
				}else{//产品属性被删或者丢失错误
					$IsError=1;
				}
				!$c['config']['products_show']['Config']['freeshipping'] && !$Weight && $Weight=$ProWeight;
				$SKU=$combinatin_ary[3]?$combinatin_ary[3]:$SKU;//产品属性SKU
			}else{//关闭规格组合
				foreach((array)$Attr as $k=>$v){
					if($k=='Overseas'){//发货地
						$OvId=(int)str_replace('Ov:', '', $v);
						if(!$OvId){//丢失发货地，自动默认China
							$OvId=1;
							$Attr[$k]="Ov:{$OvId}";
						}
						$Type==1 && $Property['Overseas']=$c['config']['Overseas'][$OvId]['Name'.$c['lang']];
					}else{
						$Type==1 && $Property[$attr_name_ary[(int)$k]]=$all_value_ary[(int)$k][(int)$v];
					}
					$v=(int)$v;
					if(@in_array($k, $color_id_ary)) $ColorId=(int)$v; //颜色属性
					$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$ProId}' and Combination='|{$v}|' and OvId=1"));
					if($row){
						$PropertyPrice+=(float)$row['Price'];//固定是加价
					}
				}
			}
			$Property && $Property=str::json_data($Property);//属性名称
		}
		if($IsError==0){//返回成功
			return array(
				'Property'		=> $Property,		//产品属性名称
				'Price'			=> $Price,			//产品单价
				'PropertyPrice'	=> $PropertyPrice,	//产品属性价格
				'Combinatin'	=> $combinatin_ary,	//产品属性的数据
				'OvId'			=> $OvId,			//发货地ID
				'ColorId'		=> $ColorId,		//颜色ID
				'Weight'		=> $Weight,			//产品重量
				'SKU'			=> $SKU,			//产品SKU
				'Attr'			=> $Attr			//产品属性数据
			);
		}else{//返回失败
			return false;
		}
	}
	
	/**
	 * 检查产品的各种库存情况
	 *
	 * @param:	$Data[object]		传递数据
	 * @key:	IsStock[int]		设置规格属性库存的开关 (1:会以默认0为无库存，无法购买此属性的产品, 0:规格属性库存默认0为无限库存)
	 * @key:	BuyType[int]		产品类型 (0:普通产品，1:团购，2:秒杀，3:组合购买，4:组合促销)
	 * @key:	Qty[int]			购买的数量
	 * @key:	ProdRow[object]		产品数据
	 * @key:	Combinatin[object]	当前产品的属性数据 array("价格", "库存", "重量", "SKU", "开启加价")
	 * @key:	Seckill[object]		秒杀数据
	 * @return	int
	 */
	public static function check_product_stock($Data){
		global $c;
		//检查产品的各种库存情况
		$IsError=0;
		$CId=(int)$Data['CId'];
		$IsStock=(int)$Data['IsStock'];
		$BuyType=(int)$Data['BuyType'];
		$Qty=(int)$Data['Qty'];
		$ProdRow=$Data['ProdRow'];
		$CombinatinAry=$Data['Combinatin'];
		$SeckillRow=$Data['Seckill'];
		$Qty<$ProdRow['MOQ'] && $Qty=$ProdRow['MOQ'];//最小起订量
		if($IsStock && $Qty>$ProdRow['Stock']){//产品总库存量
			$Qty=$ProdRow['Stock'];
		}
		if($ProdRow['MaxOQ'] && $Qty>$ProdRow['MaxOQ']){//最大购买量
			$Qty=$ProdRow['MaxOQ'];
		}
		($IsStock && (int)$ProdRow['IsCombination'] && $CombinatinAry && (int)$CombinatinAry[1]==0) && $IsError=1;//产品属性库存量不足
		if($CombinatinAry && (int)$CombinatinAry[1]>0 && $Qty>(int)$CombinatinAry[1]){//超出产品属性库存量
			$Qty=(int)$CombinatinAry[1];
		}
		if($BuyType==2 && $SeckillRow){//秒杀产品
			if($Qty>(int)$SeckillRow['RemainderQty']){//秒杀剩余量
				$Qty=(int)$SeckillRow['RemainderQty'];
			}
			if((int)$SeckillRow['MaxQty'] && $Qty>(int)$SeckillRow['MaxQty'] && (int)$SeckillRow['RemainderQty']>0){//秒杀的购买上限
				$Qty=(int)$SeckillRow['MaxQty'];
			}
			//检查购买的秒杀产品总数 是否超过 可秒杀的产品库存量
			$NowTotalQty=(int)db::get_row_count('shopping_cart', "{$c['where']['cart']} and CId!='{$CId}' and ProId='{$ProdRow['ProId']}' and BuyType=2 and KeyId='{$SeckillRow['SId']}'");
			($NowTotalQty+$Qty>$SeckillRow['MaxQty']) && $IsError=1;
		}
		$BuyType==4 && $Qty=1;//组合促销只固定数量一个
		$Qty<1 && $IsError=1;//最终数量还是小于1
		if($IsError==0){//返回成功
			return $Qty;
		}else{//返回失败
			return false;
		}
	}
	
	/**
	 * 检查购物车列表的总价格变动后，优惠情况的变动
	 *
	 * @param:	$total_price[float]			总价格
	 * @param:	$iconv_total_price[float]	总价格(计算汇率后)
	 * @param:	$fullcoupon(int)			全场满减(用于购物车列表页显示)
	 * @return	object
	 */
	public static function check_list_discounts($total_price, $iconv_total_price, $fullcoupon=0){
		global $c;
		//全场满减
		$cutprice=0;
		$FullCondition = array();
		$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
		if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=($cutArr['EndTime']+30)){//下单时间差30(s)
			foreach((array)$cutArr['Data'] as $k=>$v){
				if($total_price<$k){
					if($fullcoupon){
						$FullCondition[0]=1;
						$f_0 = cart::iconv_price($k-$total_price);
						$f_1 = $cutArr['Type']==1? ($c['lang']=='_en' ? cart::iconv_price($v[1]).' discount' : '-'.cart::iconv_price($v[1])) : $v[0].'% off';
						$FullCondition[1] = str_replace(array('%price%','%off%'), array($f_0,$f_1), $c['lang_pack']['cart']['fulldiscount']);
					}
					break;
				} 
				$cutprice=$cutArr['Type']==1?$v[1]:($iconv_total_price*(100-$v[0])/100);
			}
		}
		//优惠券
		$IsCoupon=1;
		if($_SESSION['Cart']['Coupon']){
			$coupon_row=db::get_one('sales_coupon', "CouponNumber='{$_SESSION['Cart']['Coupon']}'");
			if($total_price<(float)self::iconv_price($coupon_row['UseCondition'], 2, '', 0)){ //产品总额未达到使用条件
				unset($_SESSION['Cart']['Coupon']);
				$IsCoupon=0;
			}
		}
		return array(
			'cutprice'	=> $cutprice,
			'IsCoupon'	=> $IsCoupon,
			'fullcondition'	=> $FullCondition,
		);
	}
	
	/**
	 * “会员优惠”和“全场满减”之间的优惠对比
	 *
	 * @param:	$ProductsPrice[float]	产品总价格
	 * @return	object
	 */
	public static function discount_contrast($ProductsPrice){
		global $c;
		//全场满减促销
		$Discount=$DiscountPrice=0;
		$AfterPrice_1=-1;
		$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
		if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=($cutArr['EndTime']+30)){//下单时间差30(s)
			foreach((array)$cutArr['Data'] as $k=>$v){
				if(self::iconv_price($ProductsPrice, 2, '', 0)<self::iconv_price($k, 2, '', 0)) break;
				if($cutArr['Type']==1){
					$DiscountPrice=self::iconv_price($v[1], 2, '', 0);
				}else{
					$Discount=(100-$v[0]);
				}
				$AfterPrice_1=$ProductsPrice-($cutArr['Type']==1?$v[1]:($ProductsPrice*(100-$v[0])/100));
			}
		}
		//会员折扣优惠
		$UserDiscount=0;
		$AfterPrice_0=-1;
		if((int)$_SESSION['User']['UserId']){//实时查询当前会员等级
			(int)$_SESSION['User']['Level']=db::get_value('user', "UserId='{$_SESSION['User']['UserId']}'", 'Level');
			$UserDiscount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
			$UserDiscount>0 && $AfterPrice_0=$ProductsPrice*($UserDiscount/100);
		}
		//两者对比
		if($AfterPrice_0>=0 && $AfterPrice_1>=0){
			if($AfterPrice_0<$AfterPrice_1){//会员优惠价 < 全场满减价
				$Discount=$DiscountPrice=0;
			}else{
				$UserDiscount=0;
			}
		}
		return array(
			'DiscountPrice'	=> $DiscountPrice,
			'Discount'		=> $Discount,
			'UserDiscount'	=> $UserDiscount
		);
	}
	
	/**
	 * 添加购物车的价格计算
	 *
	 * @param: $row[object]			产品数据
	 * @param: $Qty[int]			数量
	 * @param: $AttrPrice[float]	属性单价
	 * @return float
	 */
	public static function products_add_to_cart_price($row, $Qty=1, $AttrPrice=-1){
		global $c;
		$Price=$row['Price_1'];
		$AttrPrice>=0 && $Price=$AttrPrice;//有属性单价
		$WholesalePrice=0;
		$Qty<$row['MOQ'] && $Qty=$row['MOQ'];
		if($row['Wholesale'] && $row['Wholesale']!='[]'){//批发价
			$wholesale_price=str::json_data(htmlspecialchars_decode($row['Wholesale']), 'decode');
			foreach($wholesale_price as $k=>$v){
				if($Qty<$k) break;
				if($AttrPrice>=0){//有属性单价
					$WholesalePrice=$Price=((float)$v/$row['Price_1'])*$AttrPrice;
				}else{
					$WholesalePrice=$Price=(float)$v;
				}
			}
		}
		if($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime']){//优惠价
			if(!$row['PromotionType']){//价格促销
				$Price=$row['PromotionPrice'];
				if($WholesalePrice && $WholesalePrice<$Price){//批发和优惠相冲突的情况下，以最低价优先
					$Price=$WholesalePrice;
				}
			//}else{//折扣促销
				//$Price=$row['Price_1'];
				//$AttrPrice>0 && $Price=$AttrPrice;//有属性单价
			}
		}
		return $Price;
	}
	
	/**
	 * 价格格式显示
	 *
	 * @param: $price[float]		价格
	 * @param: $method[int]			0 符号+价格，1 符号，2 价格
	 * @param: $currency[string]	货币缩写
	 * @param: $show[int]			0 不已货币格式显示，1 货币格式显示
	 * @return string
	 */
	public static function iconv_price($price, $method=0, $currency='', $show=1){
		$return='';
		$currency_row=array();
		$currency!='' && $currency_row=db::get_one('currency', "Currency='{$currency}'");//设置固定货币
		!$currency_row && $currency_row=$_SESSION['Currency'];//未设置默认货币，选择网站默认货币
		$rate=(float)$currency_row['Rate'];
		$Symbol=$currency_row['Symbol'];
		if($method==0){
			$return=(float)substr(sprintf('%01.3f', $price*$rate), 0, -1);
			$show && $return=self::currency_format($return, 0, $currency_row['Currency']);
			$return=$Symbol.$return;
		}elseif($method==1){
			$return=$Symbol;
		}else{
			$return=(float)substr(sprintf('%01.3f', $price*$rate), 0, -1);
			$show && $return=self::currency_format($return, 0, $currency_row['Currency']);
		}
		return $return;
	}
	
	/**
	 * 两个货币之间的汇率切换
	 *
	 * @param: $price[float]		价格数字
	 * @param: $currency[float]		源货币的汇率
	 * @param: $to_currency[float]	目标货币的汇率
	 * @return float
	 */
	public static function currency_price($price, $currency='', $to_currency=''){
		$return='';
		$rate=100/((100/$to_currency/100)*$currency)/100;
		$rate=round($rate, 4);
		$return=(float)substr(sprintf('%01.3f', $price*$rate), 0, -1);
		return $return;
	}
	
	/**
	 * 带有批发价的价格区间格式显示
	 *
	 * @param: $row[object]		产品数据
	 * @param: $method[int]		0 不带符号，1 带符号
	 * @return string
	 */
	public static function range_price($row, $method=0){
		global $c;
		$CurPrice=$row['Price_1'];
		$is_wholesale=($row['Wholesale'] && $row['Wholesale']!='[]');
		if($is_wholesale){
			$wholesale_price=str::json_data(htmlspecialchars_decode($row['Wholesale']), 'decode');
			foreach($wholesale_price as $k=>$v){
				if($row['MOQ']<$k) break;
				$CurPrice=(float)$v;
			}
			$maxPrice=reset($wholesale_price);
			$minPrice=end($wholesale_price);
		}
		if($row['IsPromotion'] && !$row['PromotionType'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime']){
			$CurPrice=$row['PromotionPrice'];
		}
		if($is_wholesale){
			$CurPrice>$maxPrice && $maxPrice=$CurPrice;
			$CurPrice<$minPrice && $minPrice=$CurPrice;
		}
		if($is_wholesale && !$method){
			return $_SESSION['Currency']['Symbol'].self::iconv_price($minPrice, 2).' - '.self::iconv_price($maxPrice, 2);
		}elseif($is_wholesale && $method){
			return self::iconv_price($minPrice);
		}elseif(!$is_wholesale || $method){
			return self::iconv_price($CurPrice);
		}
	}
	
	/**
	 * 输出产品的最便宜的价格数值
	 *
	 * @param: $row[object]		产品数据
	 * @param: $method[int]		0 符号+价格，1 符号，2 价格
	 * @return array
	 */
	public static function range_price_ext($row, $method=0){
		global $c;
		$data=array();
		$is_promition=($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime'])?1:0;
		$is_wholesale=($row['Wholesale'] && $row['Wholesale']!='[]');
		if((int)$is_promition){
			$data[0]=$row['PromotionType']==1?(float)substr(sprintf('%01.3f', $row['Price_1']*$row['PromotionDiscount']/100), 0, -1):$row['PromotionPrice'];
		}elseif($row['Wholesale'] && $row['Wholesale']!='[]'){
			$CurPrice=$row['Price_1'];
			$wholesale_price=str::json_data(htmlspecialchars_decode($row['Wholesale']), 'decode');
			foreach($wholesale_price as $k=>$v){
				if($row['MOQ']<$k) break;
				$CurPrice=(float)$v;
			}
			$maxPrice=reset($wholesale_price);
			$minPrice=end($wholesale_price);
			$CurPrice>$maxPrice && $maxPrice=$CurPrice;
			$CurPrice<$minPrice && $minPrice=$CurPrice;
			$data=array(
				0	=>	$minPrice,
				1	=>	$maxPrice
			);
		}else{
			$data[0]=$row['Price_1'];	
		}
		return $data;
	}
	
	/**
	 * 价格格式显示（不带有批发价）
	 *
	 * @param: $row[object]		产品数据
	 * @param: $method[int]		0 不带符号，1 带符号
	 * @return string
	 */
	public static function show_price($row, $method=0){
		global $c;
		$data=array();
		$is_promition=($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime'])?1:0;
		if((int)$is_promition){
			$data[0]=$row['PromotionType']==1?sprintf('%01.2f', $row['Price_1']*$row['PromotionDiscount']/100):$row['PromotionPrice'];
		}else{
			$data[0]=$row['Price_1'];	
		}
		return $data;
	}
	
	/**
	 * 价格格式显示（不带有批发价）
	 *
	 * @param: $where[string]	搜索条件 
	 * @param: $method[int]		0 不带符号，1 带符号
	 * @param: $iconv			1 当前汇率的价格 0 后台默认汇率的价格
	 * @return string
	 */
	public static function cart_total_price($where='', $method=0, $iconv=1){//购物车总金额，防止切换汇率导致计算精度下降
		global $c;
		$total=0;
		$Symbol=self::iconv_price(0, 1);
		$row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']}".$where, "c.ProId, c.Price, c.PropertyPrice, c.Discount, c.Qty, p.Name{$c['lang']}, p.Number", 'c.CId desc');
		foreach((array)$row as $k=>$v){
			if($v['ProId'] && !$v['Name'.$c['lang']] && !$v['Number']) continue; //产品资料丢失
			$price=($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1);
			$iconv && $price=self::iconv_price($price, 2, '', 0);
			$total+=$price*$v['Qty'];
		}
		if($method==0){
			return $Symbol.sprintf('%01.2f', $total);
		}
		return sprintf('%01.2f', $total);
	}
	
	/**
	 * 更新会员在购物车里的资料【在登录会员后执行】
	 */
	public static function login_update_cart(){
		global $c;
		$left_time=$c['time']-$c['cart']['timeout'];
		db::delete('shopping_cart', "UserId=0 and AddTime<$left_time");
		if($c['session_id']!=''){
			//检查产品会否有重复
			$cart_row=db::get_all('shopping_cart', "SessionId='{$c['session_id']}'");
			foreach((array)$cart_row as $v){
				$Property=addslashes($v['Property']);
				$attr=addslashes($v['Attr']);
				$row=db::get_one('shopping_cart', "UserId='{$_SESSION['User']['UserId']}' and ProId='{$v['ProId']}' and BuyType='{$v['BuyType']}' and KeyId='{$v['KeyId']}' and Property='{$Property}' and Attr='{$attr}'");//已有的产品
				if($row){
					db::delete('shopping_cart', "CId='{$v['CId']}'");//删掉多余的产品
					db::query("update shopping_cart set Qty=Qty+{$v['Qty']} where CId='{$row['CId']}'");//附加到已有的产品
				}
			}
			db::update('shopping_cart', "SessionId='{$c['session_id']}'", array(
					'SessionId'	=>	'',
					'UserId'	=>	(int)$_SESSION['User']['UserId']
				)
			);
			$c['session_id']='';
		}
		//自动取消订单
		if((int)$c['config']['global']['AutoCanceled']){
			$time=86400*$c['config']['global']['AutoCanceledDay'];//一天*日数
			db::update('orders', "UserId='{$_SESSION['User']['UserId']}' and ({$c['time']}-OrderTime)>{$time} and OrderStatus<4", array('OrderStatus'=>7));
		}
	}
	
	/**
	 * 计算保险费用
	 *
	 * @param: $price[float]	订单总金额
	 * @param: $currency[int]	0:不换算汇率 1:换算汇率
	 * @return float
	 */
	public static function get_insurance_price_by_price($price, $currency=1){
		$insurance_price=0;
		$insurance_row=str::json_data(db::get_value('shipping_insurance', "Id='1'", 'AreaPrice'), 'decode');
		foreach($insurance_row as $v){
			$price>=$v[0] && $insurance_price=$v[1];
		}
		return $currency==1?self::iconv_price($insurance_price, 2, '', 0):$insurance_price;
	}
	
	/**
	 * 更新购物车产品的批发计算
	 *
	 * @param: $ProId[int]				产品ID
	 * @param: $products_row[object]	产品数据
	 * @param: $CId[int]				购物车产品ID
	 * @param: $GET[int]				是否筛选后产品数据
	 * @return float
	 */
	public static function update_cart_wholesale_price($ProId, $products_row='', $CId=0, $GET=0){
		global $c;
		$Price=0;
		if((int)$c['config']['products_show']['Config']['wholesale_type']){//开启同一产品不同属性组合
			!$products_row && $products_row=str::str_code(db::get_one('products', "ProId='{$ProId}'"));
			$cart_row=db::get_all('shopping_cart', "{$c['where']['cart']} and ProId='{$ProId}' and BuyType not in(1, 2)".($CId?" and CId='{$CId}'":''));//团购or秒杀产品不纳入处理范围
			$total_qty=db::get_sum('shopping_cart', "{$c['where']['cart']} and ProId='{$ProId}' and BuyType not in(1, 2)".($GET?" and CId='{$CId}'":''), 'Qty');//此产品的总数量
			if(!$total_qty) return $Price;//直接中止
			//组合属性数据 Start
			$combination_ary=array();
			$combinatin_row=db::get_all('products_selected_attribute_combination', "ProId='{$ProId}'");
			foreach((array)$combinatin_row as $v){ $combinatin_ary[$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']); }
			//组合属性数据 End
			foreach($cart_row as $row){
				$Price=(float)$products_row['Price_1'];
				$WholesalePrice=0;
				$AttrPrice=-1;
				$OvId=1;
				if($products_row['AttrId'] && $row['Attr']){//产品属性资料
					$ext_ary=array();
					$attr_ary=@str::json_data(str::attr_decode($row['Attr']), 'decode');
					foreach((array)$attr_ary as $k=>$v){
						if($k=='Overseas'){ //发货地
							$OvId=(int)str_replace('Ov:', '', $v);
							!$OvId && $OvId=1;//丢失发货地，自动默认China
						}else{
							$ext_ary[]=$v;
						}
					}
					sort($ext_ary);//从小到大排序
					$Combination='|'.implode('|', $ext_ary).'|';
					if((int)$products_row['IsCombination'] && $combinatin_ary[$Combination][$OvId] && (int)$combinatin_ary[$Combination][$OvId][4]==0){//属性单价
						$Price=$AttrPrice=$combinatin_ary[$Combination][$OvId][0];
					}
				}
				if($products_row['Wholesale'] && $products_row['Wholesale']!='[]'){	//批发价
					$wholesale_price=str::json_data(htmlspecialchars_decode($products_row['Wholesale']), 'decode');
					foreach($wholesale_price as $k=>$v){
						if($total_qty<$k) break;
						if($AttrPrice>0){//有属性单价
							$WholesalePrice=$Price=((float)$v/$products_row['Price_1'])*$AttrPrice;
						}else{
							$WholesalePrice=$Price=(float)$v;
						}
					}
				}
				if($products_row['IsPromotion'] && !$products_row['PromotionType'] && $products_row['StartTime']<$c['time'] && $c['time']<$products_row['EndTime']){//优惠价
					$Price=$products_row['PromotionPrice'];
					if($WholesalePrice && $WholesalePrice<$Price){//批发和优惠相冲突的情况下，以最低价优先
						$Price=$WholesalePrice;
					}
				}
				$Price && db::update('shopping_cart', "{$c['where']['cart']} and ProId='{$ProId}' and CId='{$row['CId']}'", array('Price'=>$Price));
			}
		}
		if($CId) return $Price;
	}
	
	/**
	 * 获取购物车里面各自产品的包装重量计算
	 *
	 * @param: $where[string]	搜索条件 
	 * @param: $method[int]		0 返回各自产品重量(自身+包装)，1 返回各自产品重量(包装)，2 返回总重量(自身+包装)
	 * @return array
	 */
	public static function cart_product_weight($where='', $method=0){//购物车总金额，防止切换汇率导致计算精度下降
		global $c;
		$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']}".$where, 'c.ProId, c.Weight as CartWeight, c.Qty, c.OvId, p.PackingStart, p.PackingQty, p.PackingWeight, p.IsFreeShipping', 'c.CId desc');
		$pro_ary=array();
		$total=0;
		foreach($cart_row as $k=>$v){
			$total+=($v['CartWeight']*$v['Qty']);
			if($method==1){//仅计算包装重量
				$pro_ary['tWeight'][$v['OvId']][$v['ProId']]+=0;
				$pro_ary['Weight'][$v['OvId']][$v['ProId']]+=0;
			}else{
				$pro_ary['tWeight'][$v['OvId']][$v['ProId']]+=($v['CartWeight']*$v['Qty']);
				$pro_ary['Weight'][$v['OvId']][$v['ProId']]+=($v['CartWeight']*$v['Qty']);
			}
			(int)$v['IsFreeShipping']==0 && $pro_ary['Qty'][$v['OvId']][$v['ProId']]+=$v['Qty'];//产品自身免运费，后面无需计算
			$pro_ary['Packing'][$v['ProId']]=array('Start'=>$v['PackingStart'], 'Qty'=>$v['PackingQty'], 'Weight'=>$v['PackingWeight']);
		}
		foreach($pro_ary['Qty'] as $OvId=>$v){
			foreach($v as $ProId=>$v2){
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
	
	/**
	 * 不同货币的显示方式
	 *
	 * @param: $price[float]		产品价格
	 * @param: $method[int]			【已弃用】
	 * @param: $currency[string]	货币代号
	 * @return string
	 */
	public static function currency_format($price, $method=0, $currency=''){
		$return=0;
		switch($currency){
			case 'USD':
			case 'GBP':
			case 'CAD':
			case 'AUD':
			case 'CHF':
			case 'HKD':
			case 'ILS':
			case 'MXN':
			case 'CNY':
			case 'SAR':
			case 'SGD':
			case 'NZD':
			case 'AED':
				$return=number_format($price, 2, '.', ','); break;
			case 'RUB':
				$return=number_format($price, 2, ',', ' '); break;
			case 'EUR':
			case 'BRL':
			case 'ARS':
				$return=number_format($price, 2, ',', '.'); break;
			case 'CLP':
			case 'NOK':
			case 'DKK':
			case 'COP':
				$return=number_format($price, 0, '', '.'); break;
			case 'JPY':
			case 'SEK':
			case 'KRW':
			case 'INR':
				$return=number_format($price, 0, '', ','); break;
			default: $return=number_format($price, 2, '.', ',');
		}
		return $return;
	}
	
	/**
	 * 更新购物车产品信息
	 */
	public static function open_update_cart(){
		global $c;
		$w='';
		if($_GET['CId']){
			$in_where=str::ary_format(@str_replace('.', ',', $_GET['CId']), 2);
			$w=" and CId in({$in_where})";
		}
		$update_ary=array();//需要更新的购物车产品信息
		$cart_row=db::get_all('shopping_cart', $c['where']['cart'].$w, '*', 'CId desc');
		foreach((array)$cart_row as $k=>$v){
			$attr=addslashes($v['Attr']);
			$row=db::get_one('shopping_cart', $c['where']['cart'].$w." and CId!='{$v['CId']}' and ProId='{$v['ProId']}' and BuyType='{$v['BuyType']}' and KeyId='{$v['KeyId']}' and Attr='{$attr}'");//已有的产品
			if($row){
				db::delete('shopping_cart', "CId='{$v['CId']}'");//删掉多余的产品
				db::query("update shopping_cart set Qty=Qty+{$v['Qty']} where CId='{$row['CId']}'");//附加到已有的产品
				$update_ary[]=$v['CId'];
				$update_ary[]=$row['CId'];
			}
			if(!isset($_SESSION['Cart']['IsUpdate']) && !$_SESSION['Cart']['IsUpdate']){//第一次打开自动更新购物车产品信息
				$update_ary[]=$v['CId'];
			}
			if((int)$c['config']['products_show']['Config']['wholesale_type']){//开启了混批功能
				$update_ary[]=$v['CId'];
			}
		}
		$update_ary=array_unique($update_ary);//去掉重复的CId
		if(count($update_ary)>0){//有更新数据
			$_SESSION['Cart']['IsUpdate']=1;
			$IsStock=(int)$c['config']['products_show']['Config']['stock'];
			$not_error='0';
			$row=db::get_all('shopping_cart', $c['where']['cart'].$w, '*', 'CId desc');
			foreach((array)$row as $k=>$v){
				if(!in_array($v['CId'], $update_ary)) continue;
				//产品类型的处理
				$Data=self::product_type(array(
					'Type'	=> $v['BuyType'],
					'KeyId'	=> $v['KeyId'],
					'Qty'	=> $v['Qty'],
					'Attr'	=> $v['Attr']
				));
				if($Data===false){
					$not_error.=",{$v['CId']}";
					continue;
				}
				$BuyType	= $Data['BuyType'];	//产品类型
				$KeyId		= $Data['KeyId'];	//主ID
				$ProId		= $Data['ProId'];	//产品ID
				$StartFrom	= $Data['StartFrom'];//产品目前的起订量
				$Price		= $Data['Price'];	//产品目前的单价
				$Qty		= $Data['Qty'];		//购买的数量
				$Discount	= $Data['Discount'];//折扣
				$Attr		= $Data['Attr'];	//产品属性
				$Weight		= $Data['ProdRow']['Weight'];//产品目前的重量
				$Volume		= $Data['ProdRow']['Volume'];//产品目前的体积
				$SKU		= $Data['ProdRow']['SKU'];//产品默认SKU
				$seckill_row= $Data['SeckRow'];	//秒杀数据
				$OvId		= 1;//发货地ID
				$Qty<1 && $Qty=1;
				$cart_row[$key]['ProId']=$ProId;//记录产品ID
				$cart_row[$key]['Name']=$Data['ProdRow']['Name'.$c['lang']];//记录产品名称
				if($BuyType!=4){//“组合促销”除外
					if(!$Data['ProdRow']['Number']){//既不是组合促销，产品也同时不存在
						$not_error.=",{$v['CId']}";
						continue;
					}
					if(($IsStock && ($Data['ProdRow']['Stock']<$Qty || $Data['ProdRow']['Stock']<$Data['ProdRow']['MOQ'] || $Data['ProdRow']['Stock']<1)) || $Data['ProdRow']['SoldOut'] || ($Data['ProdRow']['IsSoldOut'] && ($Data['ProdRow']['SStartTime']>$c['time'] || $c['time']>$Data['ProdRow']['SEndTime'])) || in_array($Data['ProdRow']['CateId'], $c['procate_soldout'])){//产品库存量不足（包括产品下架）
						$not_error.=",{$v['CId']}";
						continue;
					}
				}
				//产品属性的处理
				$AttrData=self::get_product_attribute(array(
					'Type'			=> 0,//不用获取产品属性名称
					'BuyType'		=> $BuyType,
					'ProId'			=> $ProId,
					'Price'			=> $Price,
					'Attr'			=> $Attr,
					'IsCombination'	=> $Data['ProdRow']['IsCombination'],
					'SKU'			=> $Data['ProdRow']['SKU'],
					'Weight'		=> $Weight
				));
				if($AttrData===false){
					$not_error.=",{$v['CId']}";
					continue;
				}
				if((int)$IsStock && (int)$Data['ProdRow']['IsCombination'] && $combinatin_ary && (!$combinatin_ary[1] || $Qty>$combinatin_ary[1])){//产品属性库存量不足
					$not_error.=",{$v['CId']}";
					continue;
				}
				$Price			= $AttrData['Price'];		//产品单价
				$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
				$combinatin_ary	= $AttrData['Combinatin'];	//产品属性的数据
				$OvId			= $AttrData['OvId'];		//发货地ID
				$ColorId		= $AttrData['ColorId'];		//颜色ID
				$Weight			= $AttrData['Weight'];		//产品重量
				$SKU			= $AttrData['SKU'];			//产品SKU
				$Attr			= $AttrData['Attr'];		//产品属性数据
				//普通产品，重新计算价格，开始判断批发价和促销价
				if($BuyType==0){
					if(!$combinatin_ary || (int)$Data['ProdRow']['IsCombination']==0 || ($combinatin_ary && (int)$combinatin_ary[4]==1)){ //没有属性，或者，属性类型是“加价”
						$Price=self::products_add_to_cart_price($Data['ProdRow'], $Qty);
					}else{ //没有属性
						$Price=self::products_add_to_cart_price($Data['ProdRow'], $Qty, (float)$combinatin_ary[0]);
					}
				}
				//产品库存量不足
				if(!$Qty){
					$not_error.=",{$v['CId']}";
					continue;
				}
				//更新产品信息
				$update_data=array(
					'SKU'			=>	addslashes(stripslashes($SKU?$SKU:$v['SKU'])),
					'Weight'		=>	(float)($Weight?$Weight:$v['Weight']),
					'Volume'		=>	(float)sprintf('%01.3f', ($Volume?$Volume:$v['Volume'])),
					'Price'			=>	(float)sprintf('%01.2f', ($Price?$Price:$v['Price'])),
					'Qty'			=>	$Qty,
					'PropertyPrice'	=>	(float)sprintf('%01.2f', ($PropertyPrice?$PropertyPrice:$v['PropertyPrice'])),
					'Discount'		=>	(int)($Discount?$Discount:$val['Discount'])
				);
				db::update('shopping_cart', "CId='{$v['CId']}'", $update_data);//更新购物车产品信息
				if($BuyType==0){//检查产品混批功能
					 self::update_cart_wholesale_price($ProId, $Data['ProdRow'], $v['CId'], ($_GET['CId']?1:0));
				}
			}
			if($not_error!='0'){//产品库存量不足 产品已删除 产品下架
				db::delete('shopping_cart', "CId in ({$not_error})");
			}
		}
	}
}
?>