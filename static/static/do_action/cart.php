<?php
/*
 * Powered by ueeshop.com   http://www.ueeshop.com
 * 广州联雅网络科技有限公司   020-83226791
 * Note: 购物车操作事件
 */

class cart_module{
	public static function additem(){	//add product to shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_back=(int)$p_back;
		$p_IsBuyNow=(int)$p_IsBuyNow;//是否为立即购买
		$products_type=(int)$p_products_type;//产品类型，0：普通产品，1：团购，2：秒杀，3:组合购买，4:组合促销
		//$p_id && $p_Attr=str::json_data(str::str_code($p_id, 'stripslashes'));//注册后自动返回立即添加购物车的属性数据
		/*
		$p_id=str::str_code($p_id, 'stripslashes');
		$p_Attr=$p_id?addslashes(str::json_data($p_id)):$p_Attr; //注册后自动返回立即添加购物车的属性数据
		*/
		if($p_id){	//注册后自动返回立即添加购物车的属性数据
			$id_ary=array();
			foreach((array)$p_id as $k=>$v){
				$key=($k=='Overseas'?$k:(int)$k);
				$value=($k=='Overseas'?('Ov:'.(int)str_replace('Ov:','',$v)):(int)$v);
				$id_ary[$key]=$value;
			}
			$p_Attr=str::json_data($id_ary);
		}
		$IsStock=(int)$c['config']['products_show']['Config']['stock'];
		//初始化
		if($products_type==1){//团购
			$_KeyId=array($p_TId);
			$_Attr=array($p_Attr);
		}else{
			if($products_type==2){//秒杀
				$_KeyId=array($p_SId);
				$_Qty=array($p_Qty);
				$_Attr=array($p_Attr);
			}elseif($products_type==3){//组合购买
				$_KeyId=explode(',', $p_ProId);
				$_Attr[0]=$p_Attr;	//主产品属性
				if($p_ExtAttr){
					$package_data=str::json_data(htmlspecialchars_decode(stripslashes($p_ExtAttr)), 'decode');
				}else{
					$p_PId=(int)$p_PId;
					$PackageData=db::get_value('sales_package', "PId='$p_PId'", 'PackageData');
					$package_data=str::json_data($PackageData, 'decode');
				}
				foreach($_KeyId as $k=>$v){	//附属产品的产品属性
					if($k==0) continue;
					$_Attr[$k]='';
					if($package_data[$v]){
						$pachage_attr=array();
						foreach((array)$package_data[$v] as $key=>$value){
							$key!='Overseas' && $key=(int)$key;
							$pachage_attr[$key]=($key=='Overseas'?('Ov:'.(int)str_replace('Ov:','',$value)):(int)$value);
						}
						$_Attr[$k]=addslashes(str::json_data($pachage_attr));
					}
					//$_Attr[$k]=$package_data[$v]?addslashes(str::json_data($package_data[$v])):'';
				}
			}elseif($products_type==4){//组合促销
				$_KeyId=array($p_PId);
				$_Attr[0]=$p_Attr;	//主产品属性
			}else{//普通产品
				if(!@is_array($p_ProId)){
					$_KeyId=array($p_ProId);
					$_Qty=array($p_Qty);
					$_Attr=array($p_Attr);
				}else{
					$_KeyId=$p_ProId;
					$_Qty=$p_Qty;
					$_Attr=$p_Attr;
				}
			}
		}
		for($m=0; $m<count($_KeyId); $m++){
			//初始化
			$Data=cart::product_type(array(
				'Type'	=> $products_type,
				'KeyId'	=> $_KeyId[$m],
				'Qty'	=> $_Qty[$m],
				'Attr'	=> $_Attr[$m]
			));
			if($Data===false) continue;
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
			$in_cart	= true;
			$cart_where	= "{$c['where']['cart']} and ProId='{$ProId}' and BuyType='{$BuyType}' and KeyId='{$KeyId}'".($_Attr[$m]?" and Attr='{$_Attr[$m]}'":'');
			//产品属性
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 1,//获取产品属性名称
				'BuyType'		=> $BuyType,
				'ProId'			=> $ProId,
				'Price'			=> $Price,
				'Attr'			=> $Attr,
				'IsCombination'	=> $Data['ProdRow']['IsCombination'],
				'SKU'			=> $SKU,
				'Weight'		=> $Weight
			));
			if($AttrData===false) continue;
			$Property		= $AttrData['Property'];	//产品属性名称
			$Price			= $AttrData['Price'];		//产品单价
			$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
			$combinatin_ary	= $AttrData['Combinatin'];	//产品属性的数据
			$OvId			= $AttrData['OvId'];		//发货地ID
			$ColorId		= $AttrData['ColorId'];		//颜色ID
			$Weight			= $AttrData['Weight'];		//产品重量
			$SKU			= $AttrData['SKU'];			//产品SKU
			$Attr			= $AttrData['Attr'];		//产品属性数据
			//没有库存
			if(!(int)$Data['ProdRow']['Stock']){
				if($IsStock || (!$IsStock && count($combinatin_ary)==0)){//关闭无限库存 or 开启无限库存，没有属性
					if($p_back){ //返回JSON格式
						ly200::e_json($c['lang_pack']['cart']['error']['additem_stock'], 2);
					}else{ //跳转执行
						js::location(ly200::get_url($Data['ProdRow'], 'products'), $c['lang_pack']['cart']['error']['additem_stock']);
					}
				}
			}
			$cart_where.=" and OvId='{$OvId}'";//记入发货地的条件
			//更新？插入？
			if(db::get_row_count('shopping_cart', $cart_where)){
				//!db::get_row_count('shopping_cart', "$cart_where and Property='$Property'") && $in_cart=false;
				$in_cart=true;
			}else{
				$in_cart=false;
			}
			if($p_excheckout==1) $in_cart=false;//产品详细页的快捷支付，单独创建
			//库存检测
			$NowQty=0;
			if($in_cart==true){
				$NowQty=(int)db::get_value('shopping_cart', $cart_where, 'Qty');
				$Qty=($Qty+$NowQty);//更新后数量
			}
			$Qty=cart::check_product_stock(array(
				'IsStock'	=> $IsStock,
				'BuyType'	=> $BuyType,
				'Qty'		=> $Qty,
				'ProdRow'	=> $Data['ProdRow'],
				'Combinatin'=> $combinatin_ary,
				'Seckill'	=> $seckill_row
			));
			if($Qty===false) continue;
			if($BuyType==0){//普通产品，计算价格，开始判断批发价和促销价
				if(!$combinatin_ary || ($combinatin_ary && (int)$combinatin_ary[4]==1)){ //没有属性，或者，属性类型是“加价”
					$Price=cart::products_add_to_cart_price($Data['ProdRow'], $Qty);
				}else{ //属性类型是“单价”
					$Price=cart::products_add_to_cart_price($Data['ProdRow'], $Qty, (float)$combinatin_ary[0]);
				}
			}
			//产品图片
			$PicPath=$Data['ProdRow']['PicPath_0'];
			//颜色属性产品图片
			if($ColorId){
				$Path=db::get_value('products_color', "ProId='{$ProId}' and VId='{$ColorId}'", 'PicPath_0');
				@is_file($c['root_path'].$Path) && $PicPath=$Path;
			}
			if($p_excheckout==1){//产品详细页的快捷支付
				$low_ary=self::check_low_consumption(1, ($Price+$PropertyPrice)*$Discount/100*$Qty);
				if($low_ary){//未达到最低消费金额
					ly200::e_json(array('qty'=>$Qty, 'price'=>($Price+$PropertyPrice)*$Discount/100, 'low_price'=>$low_ary['low_price'], 'difference'=>$low_ary['difference']), 2);
				}
			}
			if($in_cart==false){
				db::insert('shopping_cart', array(
						'UserId'		=>	(int)$_SESSION['User']['UserId'],
						'SessionId'		=>	$c['session_id'],
						'ProId'			=>	$ProId,
						'BuyType'		=>	$BuyType,
						'KeyId'			=>	$KeyId,
						'SKU'			=>	addslashes($SKU),
						'PicPath'		=>	$PicPath,
						'StartFrom'		=>	$StartFrom,
						'Weight'		=>	(float)$Weight,
						'Volume'		=>	$Volume,
						'Price'			=>	$Price,
						'Qty'			=>	$Qty,
						'Property'		=>	addslashes($Property),
						'PropertyPrice'	=>	$PropertyPrice,
						'Attr'			=>	$_Attr[$m],
						'OvId'			=>	$OvId,
						'Discount'		=>	$Discount,
						'Language'		=>	substr($c['lang'], 1),
						'AddTime'		=>	$c['time']
					)
				);
			}else{
				db::update('shopping_cart', $cart_where, array(
						'Price'			=>	$Price,
						'Qty'			=>	$Qty,
						'PropertyPrice'	=>	$PropertyPrice,
					)
				);
			}
			if(!$p_excheckout && $BuyType==0){//检查产品混批功能
				cart::update_cart_wholesale_price($ProId, $Data['ProdRow']);
			}
			if($p_IsBuyNow) $CId=(int)db::get_value('shopping_cart', $cart_where, 'CId', 'CId desc');
		}
		if($p_back){
			if($p_IsBuyNow){
				//立即购买
				ly200::e_json(array('location'=>'/cart/buynow.html?Data='.base64_encode("CId={$CId}"), 'CId'=>$CId, 'item_price'=>cart::iconv_price((($Price+$PropertyPrice)*100/$Discount)*($Qty-$NowQty), 2, '', 0)), 1);
			}else{
				//添加购物车
				if((int)$_SESSION['User']['UserId']){	//已登录
					$UserId=$_SESSION['User']['UserId'];
					$user_where="UserId='{$UserId}'";
				}else{	//未登录
					$user_where="SessionId='{$c['session_id']}'";
				}
				$total_quantity=(int)db::get_row_count('shopping_cart', $user_where, 'CId');
				$default_total_price=(float)cart::cart_total_price('', 1, 0);
				$total_price=(float)cart::cart_total_price('', 1);
				$DiscountData=cart::check_list_discounts($default_total_price, $total_price, 1);//检查购物车列表的总价格变动后，优惠情况的变动
				$low_ary=self::check_low_consumption(1);
				ly200::e_json(array('qty'=>$total_quantity, 'price'=>($low_ary['s_total']?$low_ary['s_total']:$total_price), 'low_price'=>$low_ary['low_price'], 'difference'=>$low_ary['difference'], 'total_price'=>$total_price, 'item_price'=>cart::iconv_price((($Price+$PropertyPrice)*100/$Discount)*($Qty-$NowQty), 2, '', 0),'FullCondition'=>$DiscountData['fullcondition']), 1);
			}
		}else{
			js::location('/cart/', '', '.top');
		}
	}
	
	public static function select(){	//select item product of shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($p_CId=='0'){
			ly200::e_json(array('total_count'=>0, 'total_price'=>0, 'cutprice'=>0), 1);
		}else{
			$p_CId=str::ary_format($p_CId, 2);
			$cart_row=db::get_all('shopping_cart', "{$c['where']['cart']} and CId in({$p_CId})");
			$total_price=$total_count=0;
			foreach($cart_row as $k=>$v){
				$total_price+=(float)cart::iconv_price(($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty'], 2, '', 0);
				$total_count+=$v['Qty'];
			}
			$iconv_total_price=(float)cart::cart_total_price(($p_CId?" and c.CId in({$p_CId})":''), 1);
			$DiscountData=cart::check_list_discounts($total_price, $iconv_total_price);//检查购物车列表的总价格变动后，优惠情况的变动
			ly200::e_json(array('total_count'=>$total_count, 'total_price'=>$iconv_total_price, 'cutprice'=>$DiscountData['cutprice']), 1);
		}
	}
	
	public static function modify(){	//modify item quantity of shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		//初始化
		$IsBuyNow=(int)$_GET['BuyNow'];
		$p_Qty=(int)$p_Qty;
		$p_CId=(int)$p_CId;
		$p_ProId=(int)$p_ProId;
		$cart_row=db::get_one('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}' and ProId='{$p_ProId}'");
		$products_row=str::str_code(db::get_one('products', "ProId='{$p_ProId}'"));
		$IsStock=(int)$c['config']['products_show']['Config']['stock'];
		$AttrQty=0;
		$OvId=1;
		//产品属性
		if($products_row['AttrId'] && $cart_row['Attr']){
			$ext_ary=array();
			$attr_ary=@str::json_data(str::attr_decode($cart_row['Attr']), 'decode');
			foreach((array)$attr_ary as $k=>$v){
				if($k=='Overseas'){ //发货地
					$OvId=(int)str_replace('Ov:', '', $v);
					!$OvId && $OvId=1;//丢失发货地，自动默认China
				}else{
					$ext_ary[]=(int)$v;
				}
			}
			sort($ext_ary); //从小到大排序
			$Combination='|'.implode('|', $ext_ary).'|';
			$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$p_ProId}' and Combination='{$Combination}' and OvId='{$OvId}'"));
			if($row){
				$combinatin_ary=array($row['Price'], $row['Stock'], $row['Weight'], $row['SKU'], $row['IsIncrease']);
				$AttrQty=(int)$combinatin_ary[1];
			}
		}
		if($cart_row['BuyType']==2){//秒杀产品
			$seckill_row=str::str_code(db::get_one('sales_seckill', "SId='{$cart_row['KeyId']}' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
		}
		//检查库存
		$Qty=cart::check_product_stock(array(
			'CId'		=> $p_CId,
			'IsStock'	=> $IsStock,
			'BuyType'	=> $cart_row['BuyType'],
			'Qty'		=> $p_Qty,
			'ProdRow'	=> $products_row,
			'Combinatin'=> $combinatin_ary,
			'Seckill'	=> $seckill_row
		));
		if($Qty===false) ly200::e_json('', 0);
		$Price=(int)$cart_row['BuyType']?$cart_row['Price']:cart::products_add_to_cart_price($products_row, $Qty);
		if($cart_row['BuyType']==0){//普通产品，计算价格，开始判断批发价和促销价
			if(!$combinatin_ary || (int)$products_row['IsCombination']==0 || ($combinatin_ary && (int)$combinatin_ary[4]==1)){//没有属性，或者，属性类型是“加价”
				$Price=cart::products_add_to_cart_price($products_row, $Qty);
			}else{//属性类型是“单价”
				$Price=cart::products_add_to_cart_price($products_row, $Qty, (float)$combinatin_ary[0]);
			}
		}
		$IsWholesale=0;//是不是混批产品
		db::update('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}' and ProId='{$p_ProId}'", array(
				'Qty'	=>	$Qty,
				'Price'	=>	$Price
			)
		);
		if($cart_row['BuyType']==0){//检查产品混批功能
			$result=(float)cart::update_cart_wholesale_price($p_ProId, $products_row, $p_CId);//检查产品混批功能
			if($result){
				$Price=$result;
				$IsWholesale=1;
			}
			cart::update_cart_wholesale_price($p_ProId, $products_row);//检查产品混批功能
		}
		$Price=sprintf('%01.2f', $Price);
		//统计产品的改动单价计算
		$PriceAry=$AmountAry=array();
		$pro_list_row=db::get_all('shopping_cart', "{$c['where']['cart']} and ProId='{$p_ProId}'".($IsWholesale==0?" and CId='{$p_CId}'":''), 'CId, Price, PropertyPrice, Discount, Qty');
		foreach($pro_list_row as $k=>$v){
			$_p=cart::iconv_price(($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1), 2, '', 0);
			$PriceAry[$v['CId']]=$_p;
			$AmountAry[$v['CId']]=$_p*$v['Qty'];
		}
		$p_CIdAry=str::ary_format($p_CIdAry, 2);
		$tWhere=$c['where']['cart'].($IsBuyNow==1?" and CId='{$p_CId}'":'').($p_CIdAry?" and CId in({$p_CIdAry})":'');
		$total_weight=(float)db::get_sum('shopping_cart', $tWhere." and ProId='{$p_ProId}'", 'Weight*Qty');
		if(!$p_CIdAry){//一个列表产品都没有勾选
			$total_count=$total_price=$iconv_total_price=0;
		}else{//有勾选产品
			$total_count=(int)db::get_sum('shopping_cart', $tWhere, 'Qty');
			$total_price=(float)db::get_sum('shopping_cart', $tWhere, '(Price+PropertyPrice)*Discount/100*Qty');
			$iconv_total_price=(float)cart::cart_total_price(($IsBuyNow==1?" and c.CId='{$p_CId}'":'').($p_CIdAry?" and c.CId in({$p_CIdAry})":''), 1);
		}
		$cutArr=str::json_data(db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value'), 'decode');
		$DiscountData=cart::check_list_discounts($total_price, $iconv_total_price,1);//检查购物车列表的总价格变动后，优惠情况的变动
		ly200::e_json(array('qty'=>$Qty, 'price'=>$PriceAry, 'amount'=>$AmountAry, 'total_count'=>$total_count, 'total_price'=>$iconv_total_price, 'total_weight'=>$total_weight, 'cutprice'=>$DiscountData['cutprice'], 'IsCoupon'=>$DiscountData['IsCoupon'], 'FullCondition'=>$DiscountData['fullcondition']), 1);
	}

	public static function modify_remart(){	//modify item remark of shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		$p_ProId=(int)$p_ProId;
		db::update('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}' and ProId='{$p_ProId}'", array('Remark'=>$p_Remark,));
		ly200::e_json('', 1);
	}
	
	public static function modify_attribute(){	//modify item attribute of shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array();
		$result=1;
		$IsBuyNow=(int)$_GET['BuyNow'];
		$p_CId=(int)$p_CId;
		$p_ProId=(int)$p_ProId;
		$cart_row=db::get_one('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}'");
		$products_row=str::str_code(db::get_one('products', "SoldOut=0 and ProId='{$p_ProId}'"));
		$IsStock=(int)$c['config']['products_show']['Config']['stock'];
		$Price=$cart_row['Price'];//购物车的价格
		$Qty=$cart_row['Qty'];//购物车的数量
		$Volume=$cart_row['Volume'];//购物车的体积
		$Weight=$products_row['Weight'];//产品默认重量
		if((int)$products_row['IsVolumeWeight']){//体积重
			$VolumeWeight=($Volume*1000000)/5000;//先把立方米转成立方厘米，再除以5000
			$VolumeWeight>$Weight && $Weight=$VolumeWeight;
		}
		if(count($p_Attr)){ //产品属性执行
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 1,//获取产品属性名称
				'BuyType'		=> $cart_row['BuyType'],
				'ProId'			=> $p_ProId,
				'Price'			=> $Price,
				'Attr'			=> $p_Attr,
				'IsCombination'	=> $products_row['IsCombination'],
				'SKU'			=> $products_row['SKU'],
				'Weight'		=> $Weight
			));
			if($AttrData===false) ly200::e_json('', 0);
			$Property		= $AttrData['Property'];	//产品属性名称
			$Price			= $AttrData['Price'];		//产品单价
			$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
			$combinatin_ary	= $AttrData['Combinatin'];	//产品属性的数据
			$OvId			= $AttrData['OvId'];		//发货地ID
			$ColorId		= $AttrData['ColorId'];		//颜色ID
			$Weight			= $AttrData['Weight'];		//产品重量
			$SKU			= $AttrData['SKU'];			//产品SKU
			$Attr			= $AttrData['Attr'];		//产品属性数据
			if($cart_row['BuyType']==2){//秒杀产品
				$seckill_row=str::str_code(db::get_one('sales_seckill', "SId='{$cart_row['KeyId']}' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
			}
			//检查库存
			$Qty=cart::check_product_stock(array(
				'CId'		=> $p_CId,
				'IsStock'	=> $IsStock,
				'BuyType'	=> $cart_row['BuyType'],
				'Qty'		=> $Qty,
				'ProdRow'	=> $products_row,
				'Combinatin'=> $combinatin_ary,
				'Seckill'	=> $seckill_row
			));
			if($Qty===false) ly200::e_json('', 0);
			//价格
			if($cart_row['BuyType']==0){ //普通产品，重新计算价格，开始判断批发价和促销价
				if(!$combinatin_ary || ($combinatin_ary && (int)$combinatin_ary[4]==1)){ //没有属性，或者，属性类型是“加价”
					$Price=cart::products_add_to_cart_price($products_row, $Qty);
				}else{ //属性类型是“单价”
					$Price=cart::products_add_to_cart_price($products_row, $Qty, (float)$combinatin_ary[0]);
				}
			}
			//数据
			$PropertyData=addslashes($Property);
			$Attr=str_replace('_', '', str::json_data(str::str_code($Attr, 'stripslashes')));
			$data=array(
				'Price'			=>	(float)$Price,
				'Qty'			=>	$Qty,
				'Weight'		=>	(float)$Weight,
				'Property'		=>	$PropertyData,
				'PropertyPrice'	=>	$PropertyPrice,
				'SKU'			=>	$SKU,
				'Attr'			=>	$Attr,
				'OvId'			=>	$OvId
			);
			//颜色属性产品图片
			if($ColorId){
				$Path=db::get_value('products_color', "ProId='{$p_ProId}' and VId='{$ColorId}'", 'PicPath_0');
				@is_file($c['root_path'].$Path) && $data['PicPath']=$Path;
			}
			//更新
			db::update('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}' and ProId='{$p_ProId}'", $data);
			$IsWholesale=0;//是不是混批产品
			if($cart_row['BuyType']==0){//检查产品混批功能
				$wholesale_price=(float)cart::update_cart_wholesale_price($p_ProId, $products_row, $p_CId);//检查产品混批功能
				if($wholesale_price){
					$Price=$wholesale_price;
					$IsWholesale=1;
				}
			}
			if($data['PicPath']) $data['PicPath']=ly200::get_size_img($Path, '240x240');
			//返回数据
			$data['Property']=$Property;//还原到格式化之前的数据
			//统计产品的改动单价计算
			$data['itemprice']=$data['amount']=array();
			$pro_list_row=db::get_all('shopping_cart', "{$c['where']['cart']} and ProId='{$p_ProId}'".($IsWholesale==0?" and CId='{$p_CId}'":''), 'CId, Price, PropertyPrice, Discount, Qty');
			foreach($pro_list_row as $k=>$v){
				$_p=cart::iconv_price(($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1), 2, '', 0);
				$data['itemprice'][$v['CId']]=$_p;
				$data['amount'][$v['CId']]=$_p*$v['Qty'];
			}
			$data['qty']=$Qty;
			$p_CIdAry=str::ary_format($p_CIdAry, 2);
			$tWhere=$c['where']['cart'].($IsBuyNow==1?" and CId='{$p_CId}'":'').($p_CIdAry?" and CId in({$p_CIdAry})":'');
			$data['total_weight']=(float)db::get_sum('shopping_cart', $tWhere." and ProId='{$p_ProId}'", 'Weight*Qty');
			if(!$p_CIdAry){//一个列表产品都没有勾选
				$data['total_count']=$data['total_price']=$data['iconv_total_price']=0;
			}else{//有勾选产品
				$data['total_count']=(int)db::get_sum('shopping_cart', $tWhere, 'Qty');
				$data['total_price']=(float)db::get_sum('shopping_cart', $tWhere, '(Price+PropertyPrice)*Discount/100*Qty');
				$data['iconv_total_price']=(float)cart::cart_total_price(($IsBuyNow==1?" and c.CId='{$p_CId}'":'').($p_CIdAry?" and c.CId in({$p_CIdAry})":''), 1);
			}
			$DiscountData=cart::check_list_discounts($data['total_price'], $data['iconv_total_price']);//检查购物车列表的总价格变动后，优惠情况的变动
			$data['cutprice']=$DiscountData['cutprice'];
			$data['IsCoupon']=$DiscountData['IsCoupon'];
		}
		if(isset($p_Remark)){//备注
			$Remark=trim($p_Remark);
			db::update('shopping_cart', "{$c['where']['cart']} and CId='{$p_CId}' and ProId='{$p_ProId}'", array('Remark'=>$Remark));
			$data=array_merge($data, array('Remark'=>$Remark));
			count($p_Attr)==0 && $result=2;
		}
		$data=array_merge($data, array('CId'=>$p_CId, 'ProId'=>$p_ProId));
		ly200::e_json($data, $result);
	}

	public static function remove(){	//remove item from shopping cart
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		$ProId=(int)db::get_value('shopping_cart', "{$c['where']['cart']} and CId='$p_CId'", 'ProId');
		db::delete('shopping_cart', "{$c['where']['cart']} and CId='$p_CId'");
		$ProId && cart::update_cart_wholesale_price($ProId);//检查产品混批功能
		js::back();
	}

	public static function bacth_remove(){	//remove item from shopping cart
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($p_cid_list){
			$p_cid_list=str::ary_format($p_cid_list, 2);
			$rows=db::get_all('shopping_cart', "{$c['where']['cart']} and CId in($p_cid_list)", 'ProId');
			db::delete('shopping_cart', "{$c['where']['cart']} and CId in($p_cid_list)");
			$proid_ary=array();
			foreach($rows as $v){//检查产品混批功能
				if($v['ProId'] && (!count($proid_ary) || !@in_array($v['ProId'], $proid_ary))){
					cart::update_cart_wholesale_price($v['ProId']);
					$proid_ary[]=$v['ProId'];
				}
			}
			ly200::e_json('', 1);
		}
		ly200::e_json('');
	}
	
	public static function checkout_submit(){
		global $c;
		foreach((array)$_POST as $k=>$v){
			if((int)$k && $v){
				db::update('shopping_cart', "{$c['where']['cart']} and CId='{$k}'", array('Remark'=>addslashes($v)));
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function check_low_consumption($BackType=0, $Price=0){ //1:返回数组, 0:返回JSON格式
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$w=$c['where']['cart'];
		if($p_CId){
			$p_CId=str::ary_format(@str_replace('.', ',', $p_CId), 2);
			$w.=" and CId in({$p_CId})";
		}
		if($_GET['t'] && $p_CId=='0'){//来自产品详细页的传入，不用检查
			// ly200::e_json(array(), 1);
			if($p_Attr){//产品属性
				$Attr=str::str_code(str::json_data(stripslashes($p_Attr), 'decode'), 'addslashes');
				ksort($Attr);
			}
			$proInfo=db::get_one('products', "ProId='$p_ProId'");
			$StartFrom=(int)$proInfo['MOQ']>0?(int)$proInfo['MOQ']:1;	//起订量
			$p_Qty<$StartFrom && $p_Qty=$StartFrom;	//小于起订量
			$CurPrice=cart::products_add_to_cart_price($proInfo, $p_Qty);
			$PropertyPrice=0;
			//产品属性
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 0,//不用获取产品属性名称
				'ProId'			=> $p_ProId,
				'Price'			=> $CurPrice,
				'Attr'			=> $Attr,
				'IsCombination'	=> $proInfo['IsCombination'],
			));
			if($AttrData){
				$CurPrice		= $AttrData['Price'];		//产品单价
				$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
			}
			//产品数据
			if($proInfo['IsPromotion'] && $proInfo['PromotionType'] && $proInfo['StartTime']<$c['time'] && $c['time']<$proInfo['EndTime']){
				$CurPrice=$CurPrice*($proInfo['PromotionDiscount']/100);
			}
			$total_price=cart::iconv_price(($CurPrice+$PropertyPrice),2)*$p_Qty;
		}
		if($Price>0){//有传递产品总价格数据
			$ProductPrice=$Price;
		}else{
			$cartInfo=db::get_all('shopping_cart s left join products p on s.ProId=p.ProId', $w, "p.*,s.CId,s.BuyType,s.Price,s.PropertyPrice,s.Qty,s.Discount,s.Weight as CartWeight,s.Volume,s.Attr as CartAttr", 's.CId desc');
			$ProductPrice=0;
			foreach($cartInfo as $val){
				$ProductPrice+=($val['Price']+$val['PropertyPrice'])*$val['Qty']*($val['Discount']<100?$val['Discount']/100:1);
			}
		}
		$DisData=cart::discount_contrast($ProductPrice);//“会员优惠”和“全场满减”之间的优惠对比
		$DiscountPrice	= $DisData['DiscountPrice'];	//全场满减的抵现金
		$Discount		= $DisData['Discount'];			//全场满减的折扣
		$UserDiscount	= $DisData['UserDiscount'];		//会员优惠的折扣
		//最低消费设置
		$difference=0;
		$ret=1;
		$result=array();
		$low_price=cart::iconv_price($c['config']['global']['LowPrice'], 2, '', 0);
		$_total_price=$ProductPrice*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100)-$DiscountPrice;//订单折扣后的总价
		if($_GET['t'] && $p_CId=='0') $_total_price=$total_price;
		if((int)$c['config']['global']['LowConsumption'] && cart::iconv_price($_total_price, 2, '', 0)<$low_price){
			$ret=0;
			$difference=$low_price-cart::iconv_price($_total_price, 2, '', 0);
			$result=array('s_total'=>cart::iconv_price($_total_price, 2, '', 0), 'low_price'=>$low_price, 'difference'=>$difference);
		}
		if($BackType==1){
			return $result;
		}else{
			ly200::e_json($result, $ret);
		}
	}

	public static function get_excheckout_country(){//get excheckout country
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$products_ary=array();//产品数据
		if($g_Type=='shipping_cost'){//产品详细页
			$g_ProId=(int)$g_ProId;
			$g_Qty=(int)$g_Qty;
			$g_SId=(int)$g_SId;
			if($g_Attr){//产品属性
				$Attr=str::json_data(stripslashes($g_Attr), 'decode');
				ksort($Attr);
			}
			$proInfo=db::get_one('products', "ProId='$g_ProId'");
			$StartFrom=(int)$proInfo['MOQ']>0?(int)$proInfo['MOQ']:1;//起订量
			$g_Qty<$StartFrom && $g_Qty=$StartFrom;//小于起订量
			$CurPrice=cart::products_add_to_cart_price($proInfo, $g_Qty);
			$Discount=100;//默认100% OFF折扣
			$IconvPropertyPrice=0;
			//产品属性
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 0,//不用获取产品属性名称
				'BuyType'		=> $g_proType,
				'ProId'			=> $g_ProId,
				'Price'			=> $CurPrice,
				'Attr'			=> $Attr,
				'IsCombination'	=> $proInfo['IsCombination'],
			));
			$CurPrice		= $AttrData['Price'];		//产品单价
			$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
			$combinatin_ary	= $AttrData['Combinatin'];	//产品属性的数据
			$OvId			= $AttrData['OvId'];		//发货地ID
			$Attr			= $AttrData['Attr'];		//产品属性数据
			//产品数据
			if($proInfo['IsPromotion'] && $proInfo['PromotionType'] && $proInfo['StartTime']<$c['time'] && $c['time']<$proInfo['EndTime']){
				$Discount=$proInfo['PromotionDiscount'];
			}
			if($g_proType==0){//普通产品，计算价格，开始判断批发价和促销价
				if(!$combinatin_ary || ($combinatin_ary && (int)$combinatin_ary[4]==1)){//没有属性，或者，属性类型是“加价”
					$CurPrice=cart::products_add_to_cart_price($proInfo, $g_Qty);
				}else{//属性类型是“单价”
					$CurPrice=cart::products_add_to_cart_price($proInfo, $g_Qty, (float)$combinatin_ary[0]);
				}
			}
			if($g_proType==2){//秒杀产品
				$seckill_row=str::str_code(db::get_one('sales_seckill', "SId='$g_SId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
				if($seckill_row){
					$CurPrice=$seckill_row['Price'];
					$Discount=100;
				}
			}
			$ProductsPrice=($CurPrice+$PropertyPrice)*$Discount/100*$g_Qty;
			$IconvPropertyPrice=cart::iconv_price(($CurPrice+$PropertyPrice)*$Discount/100, 2, '', 0)*$g_Qty;
			$OvId_where="a.OvId='$OvId'";
			$products_ary[]=array(
				'Name'	=>	$proInfo['Name'.$c['lang']],
				'Price'	=>	($CurPrice+$PropertyPrice)*$Discount/100,
				'Qty'	=>	$g_Qty
			);
		}else{//购物车
			$where='';
			if($g_CId){
				$g_CId=str::ary_format($g_CId, 2);
				$where.=" and CId in({$g_CId})";
			}
			$ProductsPrice=db::get_sum('shopping_cart', $c['where']['cart'].$where, "(Price+PropertyPrice)*Discount/100*Qty");//购物车产品总价
			$IconvPropertyPrice=cart::cart_total_price($where, 1);
			$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $c['where']['cart'].$where, "c.OvId, p.Name{$c['lang']}, c.Price, c.PropertyPrice, c.Discount, c.Qty", 'c.CId desc');
			$OvId_where='a.OvId in(-1';
			foreach($cart_row as $v){
				$products_ary[]=array(
					'Name'	=>	$v['Name'.$c['lang']],
					'Price'	=>	($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1),
					'Qty'	=>	$v['Qty']
				);
				$OvId_where.=",{$v['OvId']}";
			}
			$OvId_where.=')';
		}
		$hot_country_len=0;//记录热门国家的总数
		$country_data=$country_ary=$hot_country_data=array();
		$data=db::get_all('country', 'IsUsed=1', 'CId,Country,Acronym,FlagPath,IsDefault,CountryData,IsHot', 'Country asc');
		$shipping_country_row=db::get_all('shipping_area a left join shipping_country c on a.AId=c.AId', $OvId_where.' Group By c.CId', 'c.CId');
		foreach($shipping_country_row as $v){ $country_ary[]=$v['CId']; }
		foreach($data as $k=>$v){//所有快递方式都没有的国家，给过滤掉
			if(in_array($v['CId'], $country_ary)){
				$country_data[]=$v;
				if($v['IsHot']){
					$hot_country_data[]=$v;
					$hot_country_len+=1;
				}
			}
		}
		if($hot_country_len<10) $hot_country_data=array();//热门国家总数在10个以上才显示 
		$DisData=cart::discount_contrast($ProductsPrice);//“会员优惠”和“全场满减”之间的优惠对比
		$DiscountPrice	= $DisData['DiscountPrice'];	//全场满减的抵现金
		$Discount		= $DisData['Discount'];			//全场满减的折扣
		$UserDiscount	= $DisData['UserDiscount'];		//会员优惠的折扣
		$total			= $ProductsPrice;//*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100);
		//优惠券优惠
		$coupon=$_SESSION['Cart']['Coupon'];
		$couponData=array('coupon'=>'', 'cutprice'=>0);
		if($coupon!='' || $g_Type=='shipping_cost'){
			$info=self::get_coupon_info($coupon, cart::iconv_price($ProductsPrice, 2, '', 0), (int)$_SESSION['User']['UserId'], $g_CId, $g_ProId);
			if($info['status']==1){
				$coupon_total=$total;
				if($info['pro_price']){
					$coupon_total=$info['pro_price'];//*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100);
				}
				$_SESSION['Cart']['Coupon']=$info['coupon'];
				$cutPrice=$info['type']==1?$info['cutprice']:($coupon_total*(100-$info['discount'])/100);//CouponType: [0, 打折] [1, 减价格]
				$info['cutprice']=cart::iconv_price($cutPrice, 2, '', 0);
				$info['end']=@date('m/d/Y', $info['end']);
				$couponData=$info;
			}
		}
		//海外仓
		if($g_Type=='shipping_cost'){//产品详细页海外仓情况
			$oversea_id_ary[$OvId]=$c['config']['Overseas'][$OvId]['Name'.$c['lang']];
		}else{//购物车海外仓情况
			$oversea_id_ary=array();
			foreach($cart_row as $v){
				!$oversea_id_ary[$v['OvId']] && $oversea_id_ary[$v['OvId']]=$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']];
			}
			ksort($oversea_id_ary); //排列正序
		}
		//检查产品的货币小数点和汇率
		$ceil_ary=array('TWD', 'JPY');//不需要金额小数点后两位
		foreach($products_ary as $k=>$v){
			$v['Price']=cart::iconv_price($v['Price'], 2, '', 0);
			if(@in_array($_SESSION['Currency']['Currency'], $ceil_ary)){
				$products_ary[$k]['Price']=intval($v['Price']);
			}
		}
		$DiscountPrice==0 && $DiscountPrice=$ProductsPrice-$total;
		$DiscountPrice=cart::iconv_price($DiscountPrice, 2, '', 0);
		$IsCreditCard=(int)db::get_value('payment', 'IsUsed=1 and Method="Excheckout"', 'IsCreditCard');//是否开启信用卡支付
		$AdditionalFee=db::get_value('payment', 'IsUsed=1 and Method="Excheckout"', 'AdditionalFee');//手续费
		if(@in_array($_SESSION['Currency']['Currency'], $ceil_ary)){//取整
			$IconvPropertyPrice=intval($IconvPropertyPrice);
			$DiscountPrice=ceil($DiscountPrice);
		}
		$result=array(
			'country'			=>	$country_data,
			'hot_country'		=>	$hot_country_data,
			'v'					=>	(int)$c['FunVersion'],
			'coupon'			=>	$couponData,
			'oversea'			=>	$oversea_id_ary,
			'CartProductPrice'	=>	$IconvPropertyPrice,
			'DiscountPrice'		=>	$DiscountPrice,
			'IsCreditCard'		=>	$IsCreditCard,
			'AdditionalFee'		=>	$AdditionalFee,
			'Item'				=>	$products_ary
		);
		ly200::e_json($result, 1);
	}

	public static function set_no_login_address(){//非会员购物地址设置
		$data=array();
		$info='';
		foreach($_POST as $k=>$v){
			if($k=='edit_address_id') continue;
			$k=='tax_code_type' && $k='CodeOption';
			$k=='tax_code_value' && $k='TaxCode';
			if($k=='country_id'){
				$k='CId';
				$v=(int)$v;
				$data['Country']=db::get_value('country', "CId='{$v}'", 'Country');
			}
			if($k=='Province'){
				$v=(int)$v;
				$CId=(int)$_POST['country_id'];
				$data['StateName']=db::get_value('country_states', "CId='{$CId}' and SId='{$v}'", 'States');
			}
			$data[$k]=$v;
			$info.="&{$k}=$v";
		}
		if(!ly200::is_mobile_client(0)){ //非会员填写 PC端 负责记录临时地址信息
			$_SESSION['Cart']['ShippingAddress']=$data;
			//$_SESSION['Cart']['ShippingAddress']['Email']=$data['Email'];
			$_SESSION['Cart']['ShippingAddress']['Province']=$data['SId'];
			$_SESSION['Cart']['ShippingAddress']['country_id']=$data['CId'];
			$_SESSION['Cart']['ShippingAddress']['tax_code_type']=$data['CodeOption'];
			$_SESSION['Cart']['ShippingAddress']['tax_code_value']=$data['TaxCode'];
			$_SESSION['Cart']['ShippingAddress']['typeAddr']=1;
		}
		ly200::e_json(array('info'=>$info, 'v'=>$data), 1);
	}
	
	public static function get_shipping_methods(){	//get shipping methods
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		$p_ProId=(int)$p_ProId;
		$p_Qty=(int)$p_Qty;
		$info=array();
		$IsFreeShipping=0;
		$pro_info_ary=array();
		if($p_Type=='shipping_cost'){
			if($p_Attr){//产品属性
				$Attr=str::str_code(str::json_data(stripslashes($p_Attr), 'decode'), 'addslashes');
				ksort($Attr);
			}
			$proInfo=db::get_one('products', "ProId='$p_ProId'");
			$Weight=$proInfo['Weight'];//产品默认重量
			$volume_ary=$proInfo['Cubage']?@explode(',', $proInfo['Cubage']):array(0,0,0);
			$Volume=$volume_ary[0]*$volume_ary[1]*$volume_ary[2];
			$Volume=sprintf('%.f', $Volume);//防止数额太大，程序自动转成科学计数法
			if((int)$proInfo['IsVolumeWeight']){//体积重
				$VolumeWeight=($Volume*1000000)/5000;//先把立方米转成立方厘米，再除以5000
				$VolumeWeight>$Weight && $Weight=$VolumeWeight;
			}
			$StartFrom=(int)$proInfo['MOQ']>0?(int)$proInfo['MOQ']:1;	//起订量
			$p_Qty<$StartFrom && $p_Qty=$StartFrom;	//小于起订量
			$CurPrice=cart::products_add_to_cart_price($proInfo, $p_Qty);
			$PropertyPrice=0;
			//产品属性
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 0,//不用获取产品属性名称
				'ProId'			=> $p_ProId,
				'Price'			=> $CurPrice,
				'Attr'			=> $Attr,
				'IsCombination'	=> $proInfo['IsCombination'],
				'Weight'		=> $Weight
			));
			$CurPrice		= $AttrData['Price'];		//产品单价
			$PropertyPrice	= $AttrData['PropertyPrice'];//产品属性价格
			$combinatin_ary	= $AttrData['Combinatin'];	//产品属性的数据
			$OvId			= $AttrData['OvId'];		//发货地ID
			$Weight			= $AttrData['Weight'];		//产品重量
			$Attr			= $AttrData['Attr'];		//产品属性数据
			//产品数据
			if($proInfo['IsPromotion'] && $proInfo['PromotionType'] && $proInfo['StartTime']<$c['time'] && $c['time']<$proInfo['EndTime']){
				$CurPrice=$CurPrice*($proInfo['PromotionDiscount']/100);
			}
			$total_price=($CurPrice+$PropertyPrice)*$p_Qty;
			$IsFreeShipping=(int)$proInfo['IsFreeShipping'];
			$total_volume=$Volume*$p_Qty;
			$total_weight=$Weight*$p_Qty;
			$s_total_weight=$total_weight;
			$s_total_volume=$total_volume;
			//产品的海外仓数据
			$pro_info_ary[$OvId]['Weight']=$pro_info_ary[$OvId]['tWeight']=$total_weight;
			$pro_info_ary[$OvId]['Volume']=$pro_info_ary[$OvId]['tVolume']=$total_volume;
			$pro_info_ary[$OvId]['Price']=$total_price;
			$pro_info_ary[$OvId]['Qty']=$p_Qty;
			if($IsFreeShipping==1){
				$pro_info_ary[$OvId]['Weight']=$pro_info_ary[$OvId]['Volume']=0;
			}
		}else{
			if($p_CId=='-1'){
				ly200::e_json('',-1);
			}
			$CIdStr='';
			if($p_order_cid){
				$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
				$CIdStr=" and CId in({$in_where})";
			}
			$cartInfo=db::get_all('shopping_cart s left join products p on s.ProId=p.ProId', $c['where']['cart'].$CIdStr, "p.*,s.CId,s.BuyType,s.Price,s.PropertyPrice,s.Qty,s.Discount,s.Weight as CartWeight,s.Volume,s.Attr as CartAttr", 's.CId desc');
			$IsFreeShipping=0;
			$total_price=$total_weight=$total_volume=0;
			foreach($cartInfo as $val){
				$OvId=1;
				$CartAttr=str::json_data(stripslashes($val['CartAttr']), 'decode');
				if(count($CartAttr)){ //include attribute values
					foreach((array)$CartAttr as $k=>$v){
						if($k=='Overseas'){ //发货地
							$OvId=str_replace('Ov:', '', $v);
							!$OvId && $OvId=1;//丢失发货地，自动默认China
						}
					}
				}
				$price=($val['Price']+$val['PropertyPrice'])*$val['Qty']*($val['Discount']<100?$val['Discount']/100:1);
				$total_price+=$price;
				if(!$pro_info_ary[$OvId]){
					$pro_info_ary[$OvId]=array('Weight'=>0, 'Volume'=>0, 'tWeight'=>0, 'tVolume'=>0, 'Price'=>0, 'IsFreeShipping'=>0);
				}
				$pro_info_ary[$OvId]['tWeight']+=($val['CartWeight']*$val['Qty']);
				$pro_info_ary[$OvId]['tVolume']+=($val['Volume']*$val['Qty']);
				$pro_info_ary[$OvId]['tQty']+=$val['Qty'];
				$pro_info_ary[$OvId]['Price']+=$price;
				if((int)$val['IsFreeShipping']==1){//免运费
					$pro_info_ary[$OvId]['IsFreeShipping']=1; //其中有免运费
				}else{
					$pro_info_ary[$OvId]['Weight']+=($val['CartWeight']*$val['Qty']);
					$pro_info_ary[$OvId]['Volume']+=($val['Volume']*$val['Qty']);
					$pro_info_ary[$OvId]['Qty']+=$val['Qty'];
				}
			}
			//产品包装重量
			$cartProAry=cart::cart_product_weight($CIdStr, 1);
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
			$total_weight=$pro_info_ary[$OvId]['Weight'];
			$total_volume=$pro_info_ary[$OvId]['tVolume'];
		}
		ksort($pro_info_ary); //排列正序
		
		$shipping_cfg=db::get_one('shipping_config', "Id='1'");
		$weight=@ceil($total_weight);
		
		$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));
		$row=db::get_all('shipping_area a left join shipping s on a.SId=s.SId', "a.AId in(select AId from shipping_country where CId='{$p_CId}')", 's.Express, s.Logo, s.IsWeightArea, s.WeightArea, s.ExtWeightArea, s.VolumeArea, s.IsUsed, s.IsAPI, s.FirstWeight, s.ExtWeight, s.StartWeight, s.MinWeight, s.MaxWeight, s.MinVolume, s.MaxVolume, s.FirstMinQty, s.FirstMaxQty, s.ExtQty, s.WeightType, a.*', 'if(s.MyOrder>0, s.MyOrder, 100000) asc, a.SId asc, a.AId asc');
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
			if($isOvId==0 && $pro_info_ary['1']){
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
				if($overseas && (int)$row['IsUsed']==1 && $open==1){
					$sv=array(
						'SId'		=>	$row['SId'],
						'Name'		=>	$overseas['Express'],
						'Logo'		=>	($overseas['Logo'] && is_file($c['root_path'].$overseas['Logo']))?$overseas['Logo']:'',
						'Brief'		=>	$overseas['Brief'],
						'IsAPI'		=>	$overseas['IsAPI'],
						'type'		=>	'',
						'weight'	=>	$v['Weight']
					);
					if($IsFreeShipping || ($v['IsFreeShipping']==1 && $v['Weight']==0) || ((int)$c['config']['products_show']['Config']['freeshipping'] && $v['Weight']==0) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingPrice']>0 && $v['Price']>=$overseas['FreeShippingPrice']) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingWeight']>0 && $v['Weight']<$overseas['FreeShippingWeight']) || ($overseas['IsFreeShipping']==1 && $overseas['FreeShippingPrice']==0 && $overseas['FreeShippingWeight']==0)){
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
					$sv['ShippingPrice']=cart::iconv_price($shipping_price, 2, '', 0);
					$sv['InsurancePrice']=cart::get_insurance_price_by_price($v['Price']+$shipping_price);
					$info[$k][]=$sv;
				}
			}
			//循环产品数据 End
		}
		if($info){
			$info_ary=array();
			foreach($info as $k=>$v){
				$sort_ary=$price_ary=array();
				foreach($v as $k2=>$v2){
					$sort_ary[$k2]=$v2['ShippingPrice'];
					$price_ary[$v2['ShippingPrice']][]=$k2;
				}
				ksort($price_ary);
				foreach($price_ary as $k2=>$v2){
					foreach($v2 as $k3=>$v3){
						$info_ary[$k][]=$info[$k][$v3];
					}
				}
			}
			ly200::e_json(array('Symbol'=>cart::iconv_price(0, 1), 'info'=>$info_ary, 'IsInsurance'=>$IsInsurance, 'total_weight'=>$total_weight), 1);
		}else{
			ly200::e_json('', 0);
		}
	}
	
	public static function ajax_get_api_info(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_IsAPI=(int)$p_IsAPI;
		$api_row=db::get_one('shipping_api', "AId='$p_IsAPI'");
		if($api_row){
			$c['plugin']=new plugin('api');
			$ApiName=strtolower($api_row['Name']);
			$ApiName=='4px' && $ApiName='_4px';
			$Attribute=str::json_data($api_row['Attribute'], 'decode');
			!count($Attribute) && ly200::e_json('');//没有插件参数
		}else{
			ly200::e_json('');//没有插件数据
		}
		$ShippingCharge=-1;//快递运费
		$CId=(int)$p_CId;
		$AId=(int)$p_AId;
		if($AId>0){
			//收货地址
			$address_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.{$c['where']['cart']} and a.AId='$AId' and a.IsBillingAddress=0", 'a.*,c.Country,c.Acronym,s.States as StateName'));
		}else{
			$address_row=array(
				'Acronym'	=>	db::get_value('country', "CId='{$CId}'", 'Acronym'),
				'ZipCode'	=>	'',
			);
		}
		$p_cCId=(int)$p_cCId;
		$p_ProId=(int)$p_ProId;
		$p_Qty=(int)$p_Qty;
		$info=array();
		$IsFreeShipping=0;
		$pro_info_ary=array();
		if($p_Type=='shipping_cost'){
			if($p_Attr){//产品属性
				$Attr=str::str_code(str::json_data(stripslashes($p_Attr), 'decode'), 'addslashes');
				ksort($Attr);
			}
			$proInfo=db::get_one('products', "ProId='$p_ProId'");
			$Weight=$proInfo['Weight'];//产品默认重量
			$volume_ary=$proInfo['Cubage']?@explode(',', $proInfo['Cubage']):array(0,0,0);
			$Volume=$volume_ary[0]*$volume_ary[1]*$volume_ary[2];
			$Volume=sprintf('%.f', $Volume);//防止数额太大，程序自动转成科学计数法
			if((int)$proInfo['IsVolumeWeight']){//体积重
				$VolumeWeight=($Volume*1000000)/5000;//先把立方米转成立方厘米，再除以5000
				$VolumeWeight>$Weight && $Weight=$VolumeWeight;
			}
			$StartFrom=(int)$proInfo['MOQ']>0?(int)$proInfo['MOQ']:1;//起订量
			$p_Qty<$StartFrom && $p_Qty=$StartFrom;//小于起订量
			//产品属性
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 0,//不用获取产品属性名称
				'ProId'			=> $p_ProId,
				'Attr'			=> $Attr,
				'IsCombination'	=> $proInfo['IsCombination'],
				'Weight'		=> $Weight
			));
			$Weight=$AttrData['Weight'];//产品重量
			$IsFreeShipping=(int)$proInfo['IsFreeShipping'];
			$total_volume=$Volume*$p_Qty;
			$total_weight=$Weight*$p_Qty;
			if($IsFreeShipping==1){
				$total_weight=$total_volume=0;
			}
		}else{
			$CIdStr=' and p.IsFreeShipping=0';
			if($p_order_cid){
				$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
				$CIdStr=" and CId in({$in_where})";
			}
			$cartInfo=db::get_all('shopping_cart s left join products p on s.ProId=p.ProId', $c['where']['cart'].$CIdStr, 's.Weight, s.Volume, s.Qty, s.OvId', 's.CId desc');
			$IsFreeShipping=0;
			$total_weight=$total_volume=0;
			foreach($cartInfo as $val){
				$OvId=$val['OvId'];
				if(!$pro_info_ary[$OvId]){
					$pro_info_ary[$OvId]=array('Weight'=>0, 'Volume'=>0, 'IsFreeShipping'=>0);
				}
				$pro_info_ary[$OvId]['Weight']+=($val['Weight']*$val['Qty']);
				$pro_info_ary[$OvId]['Volume']+=($val['Volume']*$val['Qty']);
				if((int)$val['IsFreeShipping']==1){//免运费
					$pro_info_ary[$OvId]['IsFreeShipping']=1; //其中有免运费
				}else{
					$pro_info_ary[$OvId]['Weight']+=($val['Weight']*$val['Qty']);
					$pro_info_ary[$OvId]['Volume']+=($val['Volume']*$val['Qty']);
				}
			}
			//产品包装重量
			$cartProAry=cart::cart_product_weight($CIdStr, 1);
			foreach((array)$cartProAry['Weight'] as $k=>$v){//$k是OvId
				foreach((array)$v as $k2=>$v2){//$k2是ProId
					$pro_info_ary[$k]['Weight']+=$v2;
				}
			}
			$total_weight=$pro_info_ary[$OvId]['Weight'];
			$total_volume=$pro_info_ary[$OvId]['Volume'];
		}
		(float)$total_weight<=0 && ly200::e_json('');
		if($p_IsAPI==1 && $api_row['Name']=='DHL' && $AId){//需要会员的收货地址
			//DHL插件
			$txt='<?xml version="1.0" encoding="UTF-8"?><p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">';
			$txt.="<GetQuote>
				<Request>
				  <ServiceHeader>
					<MessageTime>".date('Y-m-d', $c['time']).'T'.date('H:i:s.000', $c['time'])."</MessageTime>
					<MessageReference>1234567890123460000000000000000</MessageReference>
					<SiteID>".$Attribute['Siteid']."</SiteID>
					<Password>".$Attribute['Password']."</Password>
				  </ServiceHeader>
				</Request>
				<From>
					<CountryCode>".$Attribute['CountryCode']."</CountryCode>
					<Postalcode>".$Attribute['Postalcode']."</Postalcode>
				</From>
				<BkgDetails>
					<PaymentCountryCode>CN</PaymentCountryCode>
					<Date>".date('Y-m-d', $c['time'])."</Date>
					<ReadyTime>PT10H21M</ReadyTime>
					<ReadyTimeGMTOffset>+01:00</ReadyTimeGMTOffset>
					<DimensionUnit>CM</DimensionUnit>
					<WeightUnit>KG</WeightUnit>
					<Pieces>
						<Piece>
							<PieceID>1</PieceID>
							<Weight>".$total_weight."</Weight>
						</Piece>
					</Pieces>
					<PaymentAccountNumber>".$Attribute['Account']."</PaymentAccountNumber>
					<IsDutiable>Y</IsDutiable>
					<NetworkTypeCode>AL</NetworkTypeCode>	
					<InsuredValue>400.000</InsuredValue>
					<InsuredCurrency>IDR</InsuredCurrency>
				</BkgDetails>
				<To>
					<CountryCode>{$address_row['Acronym']}</CountryCode>
					<Postalcode>{$address_row['ZipCode']}</Postalcode>
					<City>{$address_row['City']}</City>
				</To>
				<Dutiable>
					<DeclaredCurrency>EUR</DeclaredCurrency>
					<DeclaredValue>0</DeclaredValue>
				</Dutiable>
			  </GetQuote>";
			$txt.='</p:DCTRequest>';
			$url='http://xmlpi-ea.dhl.com/XMLShippingServlet';
			$ch=curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $txt);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$result=curl_exec($ch);
			curl_close($ch);
			//解析返回的xml参数
			$payXml=@simplexml_load_string($result);
			$len=count($payXml->GetQuoteResponse->BkgDetails->QtdShp);
			for($i=0; $i<$len; ++$i){
				$object=$payXml->GetQuoteResponse->BkgDetails->QtdShp[$i];
				if($object->ProductShortName=='EXPRESS WORLDWIDE'){
					$ShippingCharge=(float)$object->ShippingCharge;
				}
			}
			//汇率处理
			if((float)$Attribute['ExchangeRate']){
				$ShippingCharge=sprintf('%01.2f', $ShippingCharge*(float)$Attribute['ExchangeRate']);
			}
		}else{
			//其他插件
			if($c['plugin']->trigger($ApiName, '__config', 'charge_calculate')=='enable'){//API插件是否存在
				$api_data=array(
					'Weight'	=>	$total_weight,
					'Acronym'	=>	$address_row['Acronym'],
					'PostCode'	=>	$address_row['ZipCode'],
					'account'	=>	$Attribute
				);
				$result=$c['plugin']->trigger($ApiName, 'charge_calculate', $api_data);//调用API插件
				$result=str::json_data($result, 'decode');
				if($result['ack']=='Success'){//返回成功
					$ShippingCharge=(float)$result['totalAmount'];
				}
			}
			//汇率处理 人民币=>后台默认货币
			if($result['currencyCode']=='RMB'){
				$ExchangeRate=db::get_value('currency', "Currency='CNY'", 'ExchangeRate');
				$ShippingCharge=cart::currency_price($ShippingCharge, $ExchangeRate, $_SESSION['ManageCurrency']['ExchangeRate']);
			}
		}
		$return=array(
			'OvId'	=>	$p_OvId,
			'AId'	=>	$p_AId,
			'Name'	=>	$p_Name,
			'IsAPI'	=>	$p_IsAPI,
			'Price'	=>	$ShippingCharge
		);
		if($ShippingCharge>=0){
			ly200::e_json($return, 1);
		}else{
			ly200::e_json($return, 0);
		}
	}

	public static function ajax_get_coupon_info(){	//获取优惠券信息	get coupon info
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$coupon=@trim($p_coupon);
		$price=(float)$p_price;
		$p_userprice=(float)$p_userprice; //会员折扣的优惠金额
		$p_order_discount_price=(float)$p_order_discount_price; //全场满减的优惠金额
		$w=$c['where']['cart'];
		if($p_order_cid){//购物车产品ID
			$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
			$w.=" and CId in({$in_where})";
		}
		if($p_CId){//购物车产品ID
			$in_where=str::ary_format($p_CId, 2);
			$w.=" and CId in({$in_where})";
			$p_order_cid=$p_CId;
		}
		$ProId=0;
		if($_POST['Type']=='shipping_cost'){//来自产品详细页
			$ProId=(int)$_POST['ProId'];
		}
		if($p_jsonData!=''){
			$product_row=str::json_data(str::str_code($p_jsonData, 'stripslashes'), 'decode');
			$ProductsPrice=($product_row['Price']+$product_row['PropertyPrice'])*$product_row['Qty'];
		}else{
			$ProductsPrice=db::get_sum('shopping_cart', $w, "(Price+PropertyPrice)*Qty");	//购物车产品总价
		}
		$ProductsPrice=cart::iconv_price($ProductsPrice, 2, '', 0);
		$exchange=1;
		if($price && $ProductsPrice!=$price){
			$exchange=0;
			$ProductsPrice=$price;
		}
		$info=self::get_coupon_info($coupon, $ProductsPrice, (int)$_SESSION['User']['UserId'], $p_order_cid, $ProId);
		if($info['status']==1){
			$info['pro_price'] && $ProductsPrice=$info['pro_price'];
			$_SESSION['Cart']['Coupon']=$info['coupon'];
			$cutPrice=$info['type']==1?cart::iconv_price($info['cutprice'], 2, '', 0):($ProductsPrice*(100-$info['discount'])/100);//CouponType: [0, 打折] [1, 减价格]
			$info['cutprice']=$cutPrice;//cart::iconv_price($cutPrice, 2, '', 0);
			$info['end']=@date('m/d/Y', $info['end']);
		}else{
			unset($_SESSION['Cart']['Coupon']);
		}
		ly200::e_json($info, $info['status']==1?1:0);
	}

	public static function get_coupon_info($coupon, $price, $UserId=0, $p_order_cid, $ProId){//获取优惠券信息 get coupon info
		//优惠券编码、用户ID
		global $c;
		$coupon_row=db::get_one('sales_coupon', "CouponNumber='$coupon'");
		$data=array('coupon'=>$coupon);
		if(!$coupon_row){
			$data['status']=0;	//未开始
		}elseif($c['time']<$coupon_row['StartTime']){
			$data['status']=-1;	//未开始
		}elseif($c['time']>$coupon_row['EndTime']){
			$data['status']=-2;	//已结束
		}elseif($coupon_row['UseNum'] && $coupon_row['BeUseTimes']>=$coupon_row['UseNum']){
			$data['status']=-3;	//已无可使用次数
		}else{
			$data=array(
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
		
		//限制条件 和 应用范围
		$pro_price=$price;
		$UserAry=$LevelAry=$CateIdAry=$ProIdAry=$TagIdAry=array();
		if($coupon_row['UserId'] && $data['status']==1){ //会员
			$_UserId=$coupon_row['UserId'];
			!strstr($_UserId, '|') && $_UserId='|'.$_UserId.'|';
			$UserAry=explode('|', substr($_UserId, 1, -1));
			if($UserAry && $UserId && (in_array($UserId, $UserAry) || in_array('-1', $UserAry))){}
			else $data['status']=-5; //非指定会员
		}
		if($coupon_row['LevelId'] && $data['status']==1){ //会员等级
			$LevelAry=explode('|', substr($coupon_row['LevelId'], 1, -1));
			if($LevelAry && (in_array($_SESSION['User']['Level'], $LevelAry) || in_array('-1', $LevelAry))){}
			else $data['status']=-5; //非指定会员等级
		}
		if($coupon_row['CateId']){ //产品分类
			$CateAry=explode('|', substr($coupon_row['CateId'], 1, -1));
			foreach((array)$CateAry as $v){
				$CateIdAry[]=$v;
				$UId=category::get_UId_by_CateId($v);
				$category_row=db::get_all('products_category', "UId like '{$UId}%'", 'CateId');
				foreach($category_row as $k2=>$v2){ $CateIdAry[]=$v2['CateId']; }
			}
		}
		if($coupon_row['ProId']){ //产品
			$ProIdAry=explode('|', substr($coupon_row['ProId'], 1, -1));
		}
		if($coupon_row['TagId']){ //产品标签
			$TagIdAry=explode('|', substr($coupon_row['TagId'], 1, -1));
		}
		if($data['status']==1 && ($CateIdAry || $ProIdAry || $TagIdAry)){
			$count=0;
			$pro_price=0;
			if($ProId){//来自产品详细页
				$pro_row=db::get_one('products', "ProId='$ProId'", "ProId, CateId, Tags");
				$proInfo=array($pro_row);
				$tags=array();
				$pro_row['Tags'] && $tags=explode('|', substr($pro_row['Tags'], 1, -1)); //产品标签
				if(($CateIdAry && (in_array($pro_row['CateId'], $CateIdAry) || in_array('-1', $CateIdAry))) || ($ProIdAry && (in_array($pro_row['ProId'], $ProIdAry) || in_array('-1', $ProIdAry))) || ($TagIdAry && count(array_intersect($TagIdAry, $tags))>0)){ //允许使用此优惠券
					$pro_price+=$price;
				}else{
					$count=1;
				}
			}else{
				$w=$c['where']['cart'];
				if($p_order_cid){//购物车产品ID
					$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
					$w.=" and s.CId in({$in_where})";
				}
				$proInfo=db::get_all('shopping_cart s left join products p on s.ProId=p.ProId', $w, "s.Price, s.PropertyPrice, s.Qty, s.Discount, p.ProId, p.CateId, p.Tags");
				foreach($proInfo as $v){
					$tags=array();
					$v['Tags'] && $tags=explode('|', substr($v['Tags'], 1, -1)); //产品标签
					if(($CateIdAry && (in_array($v['CateId'], $CateIdAry) || in_array('-1', $CateIdAry))) || (($ProIdAry && (in_array($v['ProId'], $ProIdAry) || in_array('-1', $ProIdAry)) || in_array('-1', $ProIdAry))) || (($TagIdAry && count(array_intersect($TagIdAry, $tags))>0) || in_array('-1', $TagIdAry))){ //允许使用此优惠券
						$pro_price+=($v['Price']+$v['PropertyPrice'])*($v['Discount']/100)*$v['Qty'];
					}else{
						$count+=1;
					}
				}
			}
			if(count($proInfo)==$count){
				$data['status']=-6;	//没有一个产品能使用此优惠券
			}
		}
		if($pro_price<(float)cart::iconv_price($coupon_row['UseCondition'], 2, '', 0)){
			$data['status']=-4;	//产品总额未达到使用条件
		}
		$data['pro_price']=$pro_price; //购物车产品总价
		
		return $data;
	}

	public static function remove_coupon(){//清除优惠券
		unset($_SESSION['Cart']['Coupon']);
	}
	
	public static function placeorder(){	//下订单 place an order
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		//初始化
		if(!(int)$c['config']['global']['TouristsShopping']){
			$user=user::check_login('', 1);
		}
		//发货方式
		$Oversea=explode(',', $p_order_shipping_oversea);
		$SId=@str::json_data(str_replace(array('OvId_', '\\'), '', $p_order_shipping_method_sid), 'decode');//发货方式
		$sInsurance=@str::json_data(str_replace(array('OvId_', '\\'), '', $p_order_shipping_insurance), 'decode');//运费保险
		$sType=@str::json_data(str_replace(array('OvId_', '\\'), '', $p_order_shipping_method_type), 'decode');//海运或空运
		foreach($Oversea as $v){//丢失海外仓
			!$SId[$v] && ly200::e_json('', -2);
		}
		foreach($SId as $k=>$v){
			((!$v && $sType[$k]=='') || $v==-1) && ly200::e_json('', -2);
		}
		//付款方式
		$PId=(int)$p_order_payment_method_pid;
		(!$PId || $PId==-1) && ly200::e_json('', -3);
		$payment_row=str::str_code(db::get_one('payment', "IsUsed=1 and PId='$PId'"));
		//发货地址、账单地址
		$typeAddr=(int)$p_typeAddr;//是否非会员购物【0，否；1，是】
		if($typeAddr==1){
			$data_user=array('UserId'=>0, 'Email'=>$p_Email);
			!$data_user['Email'] && ly200::e_json('', -1);
			$tax_ary=$bill_tax_ary=array();
			$address_country_row=db::get_one('country', "CId='$p_CId'", 'Country, Code');
			$StateName=db::get_value('country_states', "CId='$p_CId' and SId='{$p_Province}'", 'States');
			$address_row=array(
				'FirstName'		=>	str_replace(array('\\', '\\\\'), '', $p_FirstName),
				'LastName'		=>	str_replace(array('\\', '\\\\'), '', $p_LastName),
				'AddressLine1'	=>	str_replace(array('\\', '\\\\'), '', $p_AddressLine1),
				'AddressLine2'	=>	str_replace(array('\\', '\\\\'), '', $p_AddressLine2),
				'CountryCode'	=>	($address_country_row['Code']?$address_country_row['Code']:(int)$p_CountryCode),
				'PhoneNumber'	=>	$p_PhoneNumber,
				'City'			=>	str_replace(array('\\', '\\\\'), '', $p_City),
				'SId'			=>	(int)$p_Province,
				'State'			=>	str_replace(array('\\', '\\\\'), '', $p_State),
				'StateName'		=>	$StateName,
				'CId'			=>	(int)$p_CId,
				'Country'		=>	$address_country_row['Country'],
				'ZipCode'		=>	$p_ZipCode,
				'CodeOption'	=>	$p_CodeOption,
				'TaxCode'		=>	$p_TaxCode,
			);
			$bill_row=$address_row;
			$tax_ary=$bill_tax_ary=user::get_tax_info($address_row);
		}else{
			$data_user=array('UserId'=>$_SESSION['User']['UserId'], 'Email'=>$_SESSION['User']['Email']);
			$AId=(int)$p_order_shipping_address_aid;//收货地址ID
			(!$AId || $AId==-1 || !$data_user['Email']) && ly200::e_json('', -1);
			//收货地址
			$tax_ary=array();
			$address_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.{$c['where']['cart']} and a.AId='$AId' and a.IsBillingAddress=0", 'a.*, c.Country, s.States as StateName'));
			(!$address_row['FirstName'] || !$address_row['LastName'] || !$address_row['AddressLine1'] || !$address_row['City'] || !$address_row['CId'] || !$address_row['ZipCode'] || !$address_row['PhoneNumber']) && ly200::e_json('', -1);
			$tax_ary=user::get_tax_info($address_row);
			//账单地址
			$bill_tax_ary=array();
			$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.{$c['where']['cart']} and a.IsBillingAddress=1", 'a.*, c.Country, s.States as StateName'));
			$bill_tax_ary=user::get_tax_info($bill_row);
		}
		//检查产品的信息是否正常
		$cart_where='';
		if($p_order_cid){
			$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
			$cart_where=" and CId in({$in_where})";
		}
		//$cart_row=db::get_all('shopping_cart', $c['where']['cart'].$cart_where, "CId, BuyType, KeyId, PicPath, Price, Property, PropertyPrice, Discount, Qty, Weight as CartWeight, Volume, OvId, Remark, Language, SKU as CartSKU, Attr as CartAttr", 'CId desc');
		$cart_row=db::get_all('shopping_cart s left join products p on s.ProId=p.ProId', 's.'.$c['where']['cart'].$cart_where, "s.CId, s.ProId, s.BuyType, s.KeyId, s.PicPath, s.Price, s.Property, s.PropertyPrice, s.Discount, s.Qty, s.Weight as CartWeight, s.Volume, s.OvId, s.Remark, s.Language, s.SKU as CartSKU, s.Attr as CartAttr, p.IsFreeShipping", 's.CId desc');
		!count($cart_row) && ly200::e_json('', -4);
		$IsStock=(int)$c['config']['products_show']['Config']['stock'];
		$stock_error='0';
		$IsFreeShipping=$ProductPrice=$totalWeight=$totalVolume=$s_totalWeight=$s_totalVolume=0;
		$pro_info_ary=array();//统计产品重量体积，用于运费计算
		foreach($cart_row as $key=>$val){
			//产品类型的处理
			$Data=cart::product_type(array(
				'Type'	=> $val['BuyType'],
				'KeyId'	=> $val['KeyId'],
				'Qty'	=> $val['Qty'],
				'Attr'	=> $val['CartAttr']
			));
			if($Data===false) $stock_error.=",{$val['CId']}";
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
				!$Data['ProdRow']['Number'] && $stock_error.=",{$val['CId']}";//既不是组合促销，产品也同时不存在
				if(($IsStock && ($Data['ProdRow']['Stock']<$Qty || $Data['ProdRow']['Stock']<$Data['ProdRow']['MOQ'] || $Data['ProdRow']['Stock']<1)) || $Data['ProdRow']['SoldOut'] || ($Data['ProdRow']['IsSoldOut'] && ($Data['ProdRow']['SStartTime']>$c['time'] || $c['time']>$Data['ProdRow']['SEndTime'])) || in_array($Data['ProdRow']['CateId'], $c['procate_soldout'])){//产品库存量不足（包括产品下架）
					$stock_error.=",{$val['CId']}";
				}
			}
			//产品属性的处理
			$AttrData=cart::get_product_attribute(array(
				'Type'			=> 0,//不用获取产品属性名称
				'BuyType'		=> $BuyType,
				'ProId'			=> $ProId,
				'Price'			=> $Price,
				'Attr'			=> $Attr,
				'IsCombination'	=> $Data['ProdRow']['IsCombination'],
				'SKU'			=> $Data['ProdRow']['SKU'],
				'Weight'		=> $Weight
			));
			if($AttrData===false) $stock_error.=",{$val['CId']}";
			if((int)$IsStock && (int)$Data['ProdRow']['IsCombination'] && $combinatin_ary && (!$combinatin_ary[1] || $Qty>$combinatin_ary[1])){//产品属性库存量不足
				$stock_error.=",{$val['CId']}";
			}
			if((int)$IsStock==0 && (int)$Data['ProdRow']['IsCombination']==0 && $combinatin_ary && $combinatin_ary[1]>0 && $Qty>$combinatin_ary[1]){//产品属性库存量不足
				$stock_error.=",{$val['CId']}";//开启无限库存、不是组合属性、属性库存不是0
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
					$Price=cart::products_add_to_cart_price($Data['ProdRow'], $Qty);
				}else{ //没有属性
					$Price=cart::products_add_to_cart_price($Data['ProdRow'], $Qty, (float)$combinatin_ary[0]);
				}
			}
			//更新产品信息
			$update_data=array(
				'Weight'		=>	(float)($Weight?$Weight:$val['CartWeight']),
				'Volume'		=>	(float)sprintf('%01.3f', ($Volume?$Volume:$val['Volume'])),
				'Price'			=>	(float)sprintf('%01.2f', ($Price?$Price:$val['Price'])),
				'Qty'			=>	$Qty,
				'PropertyPrice'	=>	(float)sprintf('%01.2f', ($PropertyPrice?$PropertyPrice:$val['PropertyPrice'])),
				'Discount'		=>	(int)($Discount?$Discount:$val['Discount'])
			);
			if($BuyType==0){//检查产品混批功能
				$result=(float)cart::update_cart_wholesale_price($val['ProId'], $val, 1, ($p_order_cid?1:0));//检查产品混批功能
				$result && $update_data['Price']=$result;
			}
			db::update('shopping_cart', "CId='{$val['CId']}'", $update_data);//更新购物车产品信息
			if($BuyType==0){//检查产品混批功能
				$result=(float)cart::update_cart_wholesale_price($val['ProId'], $val, $val['CId'], ($p_order_cid?1:0));//检查产品混批功能
				$result && $update_data['Price']=$result;
			}
			//整理产品的信息数据
			$cart_row[$key]=array_merge($cart_row[$key], $update_data);
			$item_price=($update_data['Price']+$update_data['PropertyPrice'])*($update_data['Discount']<100?$update_data['Discount']/100:1)*$Qty;
			$ProductPrice+=$item_price;
			$totalVolume+=($update_data['Volume']*$Qty);
			if(!$pro_info_ary[$OvId]){
				$pro_info_ary[$OvId]=array('Weight'=>0, 'Volume'=>0, 'tWeight'=>0, 'tVolume'=>0, 'Price'=>0, 'IsFreeShipping'=>0);
			}
			$pro_info_ary[$OvId]['tWeight']+=($update_data['Weight']*$Qty);
			$pro_info_ary[$OvId]['tVolume']+=($update_data['Volume']*$Qty);
			$pro_info_ary[$OvId]['tQty']+=$Qty;
			$pro_info_ary[$OvId]['Price']+=$item_price;
			if((int)$val['IsFreeShipping']==1){//免运费
				$pro_info_ary[$OvId]['IsFreeShipping']=1; //其中有免运费
			}else{
				$pro_info_ary[$OvId]['Weight']+=($update_data['Weight']*$Qty);
				$pro_info_ary[$OvId]['Volume']+=($update_data['Volume']*$Qty);
				$pro_info_ary[$OvId]['Qty']+=$Qty;
			}
			if(${'p_Remark_'.$val['ProId'].'_'.$val['CId']}){//记录备注
				$cart_row[$key]['Remark']=${'p_Remark_'.$val['ProId'].'_'.$val['CId']};
			}
		}
		$stock_error!='0' && ly200::e_json($stock_error, -6);//产品库存量不足
		//产品包装重量
		$weight_where='';
		if($p_order_cid){
			$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
			$weight_where=" and c.CId in({$in_where})";
		}
		$cartProAry=cart::cart_product_weight($weight_where, 1);
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
		//计算快递运费
		$shipping_ary=orders::orders_shipping_method($SId, $address_row['CId'], $sType, $sInsurance, $pro_info_ary, $p_order_shipping_api);
		!$shipping_ary && ly200::e_json('', -2);
		//总重量
		$totalWeight=cart::cart_product_weight($weight_where, 2);
		//优惠券处理
		$CouponCode='';
		$CouponPrice=$CouponDiscount=0;
		if($p_order_coupon_code){
			$coupon_row=self::get_coupon_info($p_order_coupon_code, cart::iconv_price($ProductPrice, 2, '', 0), $data_user['UserId'], $p_order_cid);
			if($coupon_row['status']==1){
				$CouponCode=addslashes($coupon_row['coupon']);
				if($coupon_row['type']==1){//CouponType: [0, 打折] [1, 减价格]
					$CouponPrice=$coupon_row['cutprice'];
				}else{
					if($coupon_row['pro_price']){
						$CouponPrice=($coupon_row['pro_price']*(100-$coupon_row['discount'])/100);
						$CouponPrice=cart::currency_price($CouponPrice, $_SESSION['Currency']['ExchangeRate'], $_SESSION['ManageCurrency']['ExchangeRate']);//换成后台默认的货币
					}else{
						$CouponDiscount=(100-$coupon_row['discount'])/100;
					}
				}
				$coupon_data=array('UsedTime'=>$c['time'], 'BeUseTimes'=>$coupon_row['beusetimes']+1);
				if((int)$coupon_row['isuser']){//绑定会员
					$UesdAry=array();
					$UesdAry=@explode('|', $coupon_row['useduser']);
					if((int)$coupon_row['isuser']==2 && $data_user['UserId'] && !@in_array($data_user['UserId'], $UesdAry)){//所有会员都可使用，并且每个会员只能使用一次
						$coupon_data['UsedUser']=$coupon_row['useduser']?$coupon_row['useduser']."|{$data_user['UserId']}":$data_user['UserId'];
					}
				}
			}
		}
		//“会员优惠”和“全场满减”之间的优惠对比
		$DisData=cart::discount_contrast($ProductPrice);
		$DiscountPrice	= $DisData['DiscountPrice'];	//全场满减的抵现金
		$Discount		= $DisData['Discount'];			//全场满减的折扣
		$UserDiscount	= $DisData['UserDiscount'];		//会员优惠的折扣
		//最低消费设置
		$_total_price=$ProductPrice*((100-$Discount)/100)*((($UserDiscount>0 && $UserDiscount<100)?$UserDiscount:100)/100)*(1-$CouponDiscount)-$CouponPrice-$DiscountPrice;//订单折扣后的总价
		if((int)$c['config']['global']['LowConsumption'] && cart::iconv_price($_total_price, 2, '', 0)<cart::iconv_price($c['config']['global']['LowPrice'], 2, '', 0)){
			ly200::e_json(cart::iconv_price($c['config']['global']['LowPrice'], 0, '', 0), -5);
		}
		//确认这订单能够正常下单，才进行更新操作的优惠券信息
		if($coupon_data){
			db::update('sales_coupon', "CouponNumber='{$CouponCode}'", $coupon_data);
		}
		//游客，自动获取上次最新下单的业务员数据（相同邮箱地址）
		$SalesId=0;
		if((int)$data_user['UserId']==0){
			$SalesId=(int)db::get_value('orders', "Email='{$data_user['Email']}' and SalesId>0", 'SalesId', 'OrderId desc');
		}
		//创建订单号
		while(1){
			$OId=date('ymdHis', $c['time']).mt_rand(10,99);
			if(!db::get_row_count('orders', "OId='$OId'")){ break; }
		}
		$order_data=array(
			/*******************订单基本信息*******************/
			'OId'					=>	$OId,
			'UserId'				=>	$data_user['UserId'],
			'Source'				=>	ly200::is_mobile_client(0)?1:0,
			'RefererId'				=>	(int)$_COOKIE['REFERER'],
			'Email'					=>	$data_user['Email'],
			'SalesId'				=>	$SalesId,
			'Discount'				=>	$Discount,
			'DiscountPrice'			=>	$DiscountPrice,
			'UserDiscount'			=>	$UserDiscount,
			'ProductPrice'			=>	(float)substr(sprintf('%01.3f', $ProductPrice), 0, -1),//$ProductPrice,
			'Currency'				=>	$_SESSION['Currency']['Currency'],
			'ManageCurrency'		=>	$_SESSION['ManageCurrency']['Currency'],
			'TotalWeight'			=>	$totalWeight,
			'TotalVolume'			=>	$totalVolume,
			'OrderTime'				=>	$c['time'],
			'UpdateTime'			=>	$c['time'],
			'Note'					=>	$p_Note,
			/*******************优惠券信息*******************/
			'CouponCode'			=>	$CouponCode,
			'CouponPrice'			=>	$CouponPrice,
			'CouponDiscount'		=>	$CouponDiscount,
			/*******************收货地址*******************/
			'ShippingFirstName'		=>	addslashes($address_row['FirstName']),
			'ShippingLastName'		=>	addslashes($address_row['LastName']),
			'ShippingAddressLine1'	=>	addslashes($address_row['AddressLine1']),
			'ShippingAddressLine2'	=>	addslashes($address_row['AddressLine2']),
			'ShippingCountryCode'	=>	'+'.$address_row['CountryCode'],
			'ShippingPhoneNumber'	=>	$address_row['PhoneNumber'],
			'ShippingCity'			=>	addslashes($address_row['City']),
			'ShippingState'			=>	addslashes($address_row['StateName']?$address_row['StateName']:$address_row['State']),
			'ShippingSId'			=>	$address_row['SId'],
			'ShippingCountry'		=>	addslashes($address_row['Country']),
			'ShippingCId'			=>	$address_row['CId'],
			'ShippingZipCode'		=>	$address_row['ZipCode'],
			'ShippingCodeOption'	=>	$tax_ary['CodeOption'],
			'ShippingCodeOptionId'	=>	$tax_ary['CodeOptionId'],
			'ShippingTaxCode'		=>	$tax_ary['TaxCode'],
			/*******************账单地址*******************/
			'BillFirstName'			=>	addslashes($bill_row['FirstName']),
			'BillLastName'			=>	addslashes($bill_row['LastName']),
			'BillAddressLine1'		=>	addslashes($bill_row['AddressLine1']),
			'BillAddressLine2'		=>	addslashes($bill_row['AddressLine2']),
			'BillCountryCode'		=>	'+'.$bill_row['CountryCode'],
			'BillPhoneNumber'		=>	$bill_row['PhoneNumber'],
			'BillCity'				=>	addslashes($bill_row['City']),
			'BillState'				=>	addslashes($bill_row['StateName']?$bill_row['StateName']:$bill_row['State']),
			'BillSId'				=>	$bill_row['SId'],
			'BillCountry'			=>	addslashes($bill_row['Country']),
			'BillCId'				=>	$bill_row['CId'],
			'BillZipCode'			=>	$bill_row['ZipCode'],
			'BillCodeOption'		=>	$bill_tax_ary['CodeOption'],
			'BillCodeOptionId'		=>	$bill_tax_ary['CodeOptionId'],
			'BillTaxCode'			=>	$bill_tax_ary['TaxCode'],
			/*******************发货方式*******************/
			'ShippingExpress'		=>	addslashes($shipping_ary['ShippingExpress']?$shipping_ary['ShippingExpress']:''),
			'ShippingMethodSId'		=>	addslashes($shipping_ary['ShippingMethodSId']?$shipping_ary['ShippingMethodSId']:''),
			'ShippingMethodType'	=>	addslashes($shipping_ary['ShippingMethodType']?$shipping_ary['ShippingMethodType']:''),
			'ShippingInsurance'		=>	addslashes($shipping_ary['ShippingInsurance']?$shipping_ary['ShippingInsurance']:''),
			'ShippingPrice'			=>	$shipping_ary['ShippingPrice'],
			'ShippingInsurancePrice'=>	$shipping_ary['ShippingInsurancePrice'],
			'ShippingOvExpress'		=>	addslashes($shipping_ary['ShippingOvExpress']?$shipping_ary['ShippingOvExpress']:''),
			'ShippingOvSId'			=>	addslashes($shipping_ary['ShippingOvMethodSId']?$shipping_ary['ShippingOvMethodSId']:''),
			'ShippingOvType'		=>	addslashes($shipping_ary['ShippingOvMethodType']?$shipping_ary['ShippingOvMethodType']:''),
			'ShippingOvInsurance'	=>	addslashes($shipping_ary['ShippingOvInsurance']?$shipping_ary['ShippingOvInsurance']:''),
			'ShippingOvPrice'		=>	addslashes($shipping_ary['ShippingOvPrice']?$shipping_ary['ShippingOvPrice']:''),
			'ShippingOvInsurancePrice'=>addslashes($shipping_ary['ShippingOvInsurancePrice']?$shipping_ary['ShippingOvInsurancePrice']:''),
			/*******************付款方式*******************/
			'PId'					=>	$PId,
			'PaymentMethod'			=>	$payment_row['Name'.$c['lang']],//db::get_value('payment', "IsUsed=1 and PId='$PId'", "Name{$c['lang']}"),
			'PayAdditionalFee'		=>	$payment_row['AdditionalFee'],
			'PayAdditionalAffix'	=>	$payment_row['AffixPrice']
		);
		//生成订单
		db::insert('orders', $order_data);
		$OrderId=db::get_insert_id();
		//删除购物车相关的信息
		$_SESSION['Cart']['Coupon']='';
		$where=$c['where']['cart'];
		if($p_order_cid){
			$in_where=str::ary_format(@str_replace('.', ',', $p_order_cid), 2);
			$where.=" and CId in({$in_where})";
		}
		db::delete('shopping_cart', $where);
		unset($tax_ary, $shipping_ary, $coupon_row, $_SESSION['Cart']['Coupon']);
		//购物车产品的数据转移
		$i=1;
		$insert_sql='';
		$order_pic_dir=$c['orders']['path'].date('ym', $c['time'])."/{$OId}/";
		!is_dir($c['root_path'].$order_pic_dir) && file::mk_dir($order_pic_dir);
		foreach($cart_row as $v){
			$ext_name=file::get_ext_name($v['PicPath']);
			$ImgPath=$order_pic_dir.str::rand_code().'.'.$ext_name;
			@copy($c['root_path'].$v['PicPath'].'.240x240.'.$ext_name, $c['root_path'].$ImgPath);
			$Name=str::str_code($v['Name'], 'addslashes');
			$SKU=str::str_code(($v['CartSKU']?$v['CartSKU']:$v['SKU']), 'addslashes');
			$Property=str::str_code($v['Property'], 'addslashes');
			$Remark=str::str_code($v['Remark'], 'addslashes');
			$insert_sql.=($i%100==1)?"insert into `orders_products_list` (OrderId, ProId, BuyType, KeyId, Name, SKU, PicPath, StartFrom, Weight, Price, Qty, Property, PropertyPrice, OvId, Discount, Remark, Language, AccTime) VALUES":',';
			$insert_sql.="('$OrderId', '{$v['ProId']}', '{$v['BuyType']}', '{$v['KeyId']}', '{$Name}', '{$SKU}', '{$ImgPath}', '{$v['StartFrom']}', '{$v['Weight']}', '{$v['Price']}', '{$v['Qty']}', '{$Property}', '{$v['PropertyPrice']}', '{$v['OvId']}', {$v['Discount']}, '{$Remark}', '{$v['Language']}', '{$c['time']}')";
			if($i++%100==0){
				db::query($insert_sql);
				$insert_sql='';
			}
		}
		$insert_sql!='' && db::query($insert_sql);
		//下单后自动注册会员
		if((int)$c['config']['global']['AutoRegister'] && !db::get_row_count('user', "Email='{$data_user['Email']}'")){
			//随机生成密码
			$PasswordStr=str::rand_code();
			$Password=ly200::password($PasswordStr);
			$time=$c['time'];
			$ip=ly200::get_ip();
			$data=array(
				'Language'		=>	'en',
				'FirstName'		=>	addslashes($address_row['FirstName']),
				'LastName'		=>	addslashes($address_row['LastName']),
				'Email'			=>	$data_user['Email'],
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
				'FirstName'		=>	addslashes($address_row['FirstName']),
				'LastName'		=>	addslashes($address_row['LastName']),
				'AddressLine1'	=>	addslashes($address_row['AddressLine1']),
				'City'			=>	addslashes($address_row['City']),
				'State'			=>	addslashes($address_row['StateName']?$address_row['StateName']:$address_row['State']),
				'SId'			=>	$address_row['SId'],
				'CId'			=>	$address_row['CId'],
				'CodeOption'	=>	$tax_ary['CodeOption'],
				'TaxCode'		=>	$tax_ary['TaxCode'],
				'ZipCode'		=>	$address_row['ZipCode'],
				'CountryCode'	=>	$address_row['CountryCode'],
				'PhoneNumber'	=>	$address_row['PhoneNumber'],
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
			if(!$_SESSION['User']['IsLocked'] && $LId){//没有固定会员等级
				db::update('user', "UserId='$UserId'", array('Level'=>$LId));
				$_SESSION['User']['Level']=$LId;
			}
			user::operation_log($UserId, '会员注册');
			if((int)$c['config']['email']['notice']['create_account']){ //邮件通知开关【会员注册】
				include($c['static_path'].'/inc/mail/create_account.php');
				ly200::sendmail($data_user['Email'], $mail_title, $mail_contents);
			}
		}
		unset($address_row, $data_oth);
		orders::orders_log((int)$data_user['UserId'], $data_user['UserId']?addslashes($_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']):'System', $OrderId, 1, "Place an Order: ".$OId);
		if((int)$c['config']['global']['LessStock']==0){//下单减库存
			$orders_row=db::get_one('orders', "OrderId='$OrderId'");
			orders::orders_products_update(1, $orders_row);
		}
		if((int)$c['config']['global']['CheckoutEmail']){//下单后邮件通知
			$ToAry=array($data_user['Email']);
			include($c['static_path'].'/inc/mail/order_create.php');
			$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
			$c['config']['global']['OrdersSmsStatus'][1] && orders::orders_sms($OId);
		}
		ly200::e_json(array('OId'=>$OId, 'Method'=>$payment_row['Method']), 1);
	}
	
	public static function payment_ready(){//付款前的例行检查
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$order_row=db::get_one('orders', "OId='$p_OId'");
		//订单不存在
		!$order_row && ly200::e_json('', -1);
		//订单无产品
		if(!(int)db::get_row_count('orders_products_list', "OrderId='{$order_row['OrderId']}'")){
			db::delete('orders', "OrderId='{$order_row['OrderId']}'");
			ly200::e_json('', -1);
		}
		//会员订单，未登录不允许付款
		((int)$order_row['UserId'] && !(int)$_SESSION['User']['UserId']) && ly200::e_json('', -1);
		//当前会员非订单会员
		((int)$order_row['UserId'] && (int)$order_row['UserId']!=(int)$_SESSION['User']['UserId']) && y200::e_json('', -1);
		//会员订单发货后状态在会员中心查询
		((int)$_SESSION['User']['UserId'] && (int)$order_row['OrderStatus']>4) && y200::e_json('', -1);
		//订单总金额低于等于0
		$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
		if($total_price<=0 && (int)$order_row['OrderStatus']<4){
			if((int)$order_row['OrderStatus']==1){//更改为“待确认”状态
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				$Log='Update order status from '.$c['orders']['status'][1].' to '.$c['orders']['status'][2];
				db::update('orders', "OId='$OId'", array('OrderStatus'=>2, 'UpdateTime'=>$c['time']));
				orders::orders_log($order_row['UserId'], $UserName, $order_row['OrderId'], 2, $Log);
			}
			ly200::e_json('', 0);
		}
		//付款跳转
		$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
		if($payment_row['IsOnline']==1 && (int)$order_row['OrderStatus']<4){//支付方式为在线付款，并且状态为未付款
			ly200::e_json('', 1);
		}else{//线下付款
			ly200::e_json('', 2);
		}
	}

	public static function offline_payment(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$orders_row=db::get_one('orders', "OId='$p_OId'");
		!$orders_row && ly200::e_json('', -1);
		($orders_row['OrderStatus']!=1 && $orders_row['OrderStatus']!=3) && ly200::e_json('', -2);
		$payment_row=db::get_one('payment', "PId='{$orders_row['PId']}'");
		(!(int)$payment_row['IsUsed'] || (int)$payment_row['IsOnline'] || $p_PaymentMethod!=$payment_row['Method']) && ly200::e_json('', -3);
		(!$p_FirstName || !$p_LastName || !$p_MTCNNumber) && ly200::e_json('', -1);
		$data=array(
			'OrderId'		=>	$orders_row['OrderId'],
			'FirstName'		=>	$p_FirstName,
			'LastName'		=>	$p_LastName,
			'SentMoney'		=>	(float)$p_SentMoney,
			'MTCNNumber'	=>	(int)$p_MTCNNumber,
			'Currency'		=>	$p_Currency,
			'Country'		=>	db::get_value('country', "CId='{$p_Country}' and IsUsed=1", 'Country'),
			'Contents'		=>	$p_Contents,
			'AccTime'		=>	$c['time']
		);
		db::update('orders', "OrderId='{$orders_row['OrderId']}'", array('OrderStatus'=>2, 'UpdateTime'=>$c['time']));
		db::insert('orders_payment_info', $data);
		$UserId=(int)($_SESSION['User']['UserId']?$_SESSION['User']['UserId']:$orders_row['UserId']);
		$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
		$log="Payment by ".$payment_row['Name'.$c['lang']]."(#{$p_MTCNNumber}: {$p_Currency} {$p_SentMoney})";
		orders::orders_log($UserId, $UserName, $orders_row['OrderId'], 2, $log);
		if((int)$c['config']['email']['notice']['order_change']){ //邮件通知开关
			$OId=$orders_row['OId'];
			$ToAry=array(0=>$orders_row['Email']);
			include($c['static_path'].'/inc/mail/order_change.php');
			$c['config']['global']['AdminEmail'] && $ToAry[1]=$c['config']['global']['AdminEmail'];
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
		}
		ly200::e_json($data, 1);
	}
	
	/********************************** 快捷支付处理(信用卡支付) start **********************************/
	public static function paypal_checkout_payment_log(){
		global $c;
		include("{$c['root_path']}/plugins/payment/paypal_excheckout/credit/CreditPayment.php");
	}
	
	public static function paypal_checkout_complete_log(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		include("{$c['root_path']}/plugins/payment/paypal_excheckout/credit/CreditCheckout.php");
		ly200::e_json('', 1);
	}
	
	public static function paypal_checkout_success_log(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		include("{$c['root_path']}/plugins/payment/paypal_excheckout/credit/CreditSuccess.php");
		ly200::e_json('', 1);
	}
	/********************************** 快捷支付处理(信用卡支付) end **********************************/
	
	/********************************** 订单处理 start **********************************/
	public static function get_payment_methods(){	//get payment methods
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$orders_row=db::get_one('orders', "OId='{$p_OId}'");
		!$orders_row && ly200::e_json('', 0);
		$total_price=orders::orders_price($orders_row);
		$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', $c['my_order'].'IsOnline desc,PId asc');
		
		$info=array();
		foreach($payment_row as $v){
			if($v['MaxPrice']>0?($total_price<$v['MinPrice'] || $total_price>$v['MaxPrice']):($total_price<$v['MinPrice'])) continue;
			$data=array(
				'PId'			=>	$v['PId'],
				'LogoPath'		=>	$v['LogoPath'],
				'Name'			=>	$v['Name'.$c['lang']],
				'AdditionalFee'	=>	$v['AdditionalFee'],//手续费，百分比
				'AffixPrice'	=>	cart::iconv_price($v['AffixPrice'], 2, $orders_row['Currency']),//附加费用
				'Description'	=>	$v['Description'.$c['lang']]
			);
			$info[]=$data;
		}
		if(count($info)){
			ly200::e_json(array('total_price'=>cart::iconv_price($total_price, 1, $orders_row['Currency']).$total_price, 'currency_symbols'=>cart::iconv_price($total_price, 1, $orders_row['Currency']), 'currency'=>$orders_row['Currency'], 'info'=>$info), 1);
		}else{
			ly200::e_json('', 0);
		}
	}
	
	public static function orders_payment_update(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_PId=(int)$p_PId;
		$orders_row=db::get_one('orders', "OId='{$p_OId}'");
		$payment_row=db::get_one('payment', "PId='{$p_PId}'");
		if($orders_row && $payment_row){
			db::update('orders', "OId='{$p_OId}'", array('PId'=>$p_PId, 'PaymentMethod'=>$payment_row['Name'.$c['lang']], 'PayAdditionalFee'=>$payment_row['AdditionalFee'], 'PayAdditionalAffix'=>$payment_row['AffixPrice'], 'UpdateTime'=>$c['time']));
			ly200::e_json('', 1);
		}
		ly200::e_json('', 0);
	}
	
	public static function orders_payment_update_loaction(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_PId=(int)$p_PId;
		$orders_row=db::get_one('orders', "OId='{$p_OId}'");
		$payment_row=db::get_one('payment', "PId='{$p_PId}'");
		if($orders_row && $payment_row){
			db::update('orders', "OId='{$p_OId}'", array('PId'=>$p_PId, 'PaymentMethod'=>$payment_row['Name'.$c['lang']], 'PayAdditionalFee'=>$payment_row['AdditionalFee'], 'PayAdditionalAffix'=>$payment_row['AffixPrice'], 'UpdateTime'=>$c['time']));
		}
		js::loaction("/cart/complete/{$p_OId}.html");
	}
	
	public static function orders_create_account(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$orders_row=db::get_one('orders', "OId='{$p_OId}'");
		$Email=$orders_row['Email'];
		$p_Password=trim($p_Password);
		if(!$orders_row || empty($Email) || empty($p_Password)){
			ly200::e_json('', 0);
		}
		if(!db::get_row_count('user', "Email='{$Email}'")){
			$Password=ly200::password($p_Password);
			$time=$c['time'];
			$ip=ly200::get_ip();
			$data=array(
				'Language'		=>	'en',
				'FirstName'		=>	addslashes($orders_row['ShippingFirstName']),
				'LastName'		=>	addslashes($orders_row['ShippingLastName']),
				'Email'			=>	$Email,
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
			if($ParentId=db::get_value('sales_coupon',"CouponWay=2 and ({$c['time']} < EndTime and {$c['time']} > StartTime)",'CId')){
				user::get_user_coupons($UserId,$ParentId); //会员注册送优惠券
			}
			$data_oth=array(
				'UserId'		=>	$UserId,
				'FirstName'		=>	addslashes($orders_row['ShippingFirstName']),
				'LastName'		=>	addslashes($orders_row['ShippingLastName']),
				'AddressLine1'	=>	addslashes($orders_row['ShippingAddressLine1']),
				'City'			=>	addslashes($orders_row['ShippingCity']),
				'State'			=>	addslashes($orders_row['ShippingState']),
				'SId'			=>	$orders_row['ShippingSId'],
				'CId'			=>	$orders_row['ShippingCId'],
				'CodeOption'	=>	$orders_row['CodeOption'],
				'TaxCode'		=>	$orders_row['ShippingTaxCode'],
				'ZipCode'		=>	$orders_row['ShippingZipCode'],
				'CountryCode'	=>	$orders_row['ShippingCountryCode'],
				'PhoneNumber'	=>	$orders_row['ShippingPhoneNumber'],
				'AccTime'		=>	$time
			);
			db::insert('user_address_book', $data_oth);//Shipping Address
			$data_oth['IsBillingAddress']=1;
			db::insert('user_address_book', $data_oth);//Billing Address
			$_SESSION['User']=$data;
			$_SESSION['User']['UserId']=$UserId;
			db::update('orders', "OrderId='{$orders_row['OrderId']}'", array('UserId'=>$UserId, 'UpdateTime'=>$c['time']));
			//更新会员等级
			$LId=(int)db::get_value('user_level', 'IsUsed=1 and FullPrice<=0', 'LId');
			if(!$_SESSION['User']['IsLocked'] && $LId){//没有固定会员等级
				db::update('user', "UserId='$UserId'", array('Level'=>$LId));
				$_SESSION['User']['Level']=$LId;
			}
			user::operation_log($UserId, '会员注册');
			if((int)$c['config']['email']['notice']['create_account']){ //邮件通知开关【会员注册】
				include($c['static_path'].'/inc/mail/create_account.php');
				ly200::sendmail($Email, $mail_title, $mail_contents);
			}
			ly200::e_json('', 1);
		}
		ly200::e_json('', 0);
	}
	/********************************** 订单处理 end **********************************/
}
?>