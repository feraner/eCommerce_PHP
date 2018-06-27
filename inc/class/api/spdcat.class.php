<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791

spdcat (速猫ERP)
*/

class spdcat_api{
	/*************************************************************************************************************
	主控制面板请求【同步产品信息】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_product(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		$errerTxt='';
		$resize_ary=array('default', '500x500', '240x240');
		$Name=$BriefDescription=$Description=$SeoTitle=$SeoKeyword=$SeoDescription=$data=$Desc_data=$Seo_data=array();
		//产品名称
		$p_Name=@explode(';', trim($p_Name));
		foreach((array)$p_Name as $v){
			$v=@explode(':', trim($v));
			$Name[$v[0]]=$v[1];
		}
		if(!$Name){ $errerTxt.="(上传失败) 产品名称为空 "; }
		//基本数据
		$Currency=trim($p_Currency);//货币符号
		$CateId=(int)$p_CateId;
		$Number=trim($p_No);//编号
		if(!$Number){ $errerTxt.="(上传失败) 产品编号为空 "; }
		$SKU=trim($p_SKU);//SKU
		$PurchasePrice=(float)trim($p_PurchasePrice);//进货价
		$Price_0=(float)trim($p_Price_0);//市场价
		$Price_1=(float)trim($p_Price_1);//商城价
		if($Price_1<=0){ $errerTxt.="(上传失败) 商城价为空 "; }
		$Wholesale=trim($p_Wholesale);//批发价
		$PicPath=@explode(';', trim($p_PicPath));//图片
		$Weight=(float)trim($p_Weight);//重量
		$cubage_ary=array('*', 'x', 'X');
		$Cubage=str_replace($cubage_ary, ',', trim($p_Cubage));//体积
		$MOQ=(int)trim($p_MOQ);//起订量
		$MaxOQ=(int)trim($p_MaxOQ);//最大购买量
		$Stock=(int)trim($p_Stock);//库存
		$SoldOut=(int)trim($p_SoldOut);//下架
		$IsFreeShipping=(int)trim($p_IsFreeShipping);//免运费
		$IsNew=(int)trim($p_IsNew);//新品
		$IsHot=(int)trim($p_IsHot);//热卖
		$IsBestDeals=(int)trim($p_IsBestDeals);//畅销
		//简单介绍
		$p_Brief=trim($p_Brief)?@explode(';', trim($p_Brief)):array();
		foreach((array)$p_Brief as $v){
			$v=@explode(':', trim($v));
			$BriefDescription[$v[0]]=$v[1];
		}
		//详细介绍
		$lang_value=db::get_value('config', "GroupId='global' and Variable='Language'", 'Value');
		$lang_ary=explode(',', $lang_value);
		foreach($lang_ary as $k=>$v){
			$Description[$v]=${'p_Description_'.$v};
		}
		//SEO标题
		$p_SeoTitle=trim($p_SeoTitle)?@explode(';', trim($p_SeoTitle)):array();
		foreach((array)$p_SeoTitle as $v){
			$v=@explode(':', trim($v));
			$SeoTitle[$v[0]]=$v[1];
		}
		//SEO关键词
		$p_SeoKeyword=trim($p_SeoKeyword)?@explode(';', trim($p_SeoKeyword)):array();
		foreach((array)$p_SeoKeyword as $v){
			$v=@explode(':', trim($v));
			$SeoKeyword[$v[0]]=$v[1];
		}
		//SEO简述
		$p_SeoDescription=trim($p_SeoDescription)?@explode(';', trim($p_SeoDescription)):array();
		foreach((array)$p_SeoDescription as $v){
			$v=@explode(':', trim($v));
			$SeoDescription[$v[0]]=$v[1];
		}
		//批发价
		$wholesale_ary=array();
		if($Wholesale){
			$Qty=@explode(',', $Wholesale);
			foreach((array)$Qty as $k=>$v){
				if($k>4) break;
				$arr=@explode(':', $v);
				$wholesale_ary[$arr[0]]=$arr[1];
			}
		}
		//价格与汇率
		$ManageCurrency=db::get_one('currency', "ManageDefault=1");//后台默认货币
		if($Currency!=$ManageCurrency['Currency']){//不一致
			$ExchangeRate=db::get_value('currency', "Currency='{$Currency}'", 'ExchangeRate');
			$PurchasePrice=cart::currency_price($PurchasePrice, $ExchangeRate, $ManageCurrency['ExchangeRate']);
			$Price_0=cart::currency_price($Price_0, $ExchangeRate, $ManageCurrency['ExchangeRate']);
			$Price_1=cart::currency_price($Price_1, $ExchangeRate, $ManageCurrency['ExchangeRate']);
			foreach((array)$wholesale_ary as $k=>$v){
				$wholesale_ary[$k]=cart::currency_price($v, $ExchangeRate, $ManageCurrency['ExchangeRate']);
			}
		}
		$Wholesale=addslashes(str::json_data(str::str_code($wholesale_ary, 'stripslashes')));
		//更新代码
		$prod_row=db::get_one('products', "Number='{$Number}'", 'ProId, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4');
		$ProId=(int)$prod_row['ProId'];
		$save_dir='/u_file/'.date('ym/').'products/'.date('d/');
		if($errerTxt==''){//没有任何错误提示
			//图片上传
			$ImgPath=array();
			foreach((array)$PicPath as $k=>$v){
				if($k>4) break;//最多下载5张图片
				if(stripos($v, 'u_file')!==false || (!substr_count($v, 'http://') && !substr_count($v, 'https://'))) continue; //本地图片，跳出执行
				$water_ary=array();
				$filepath=ly200::curl($v);
				if($filepath){//检查图片是否存在
					$ext_name=file::get_ext_name($v);//图片文件后缀名
					$new_path=file::write_file($save_dir, str::rand_code().'.'.$ext_name, $filepath);
				}
				if(is_file($c['root_path'].$new_path)){
					if($c['manage']['config']['IsWater']) $water_ary[]=$new_path;
					if($resize_ary){
						if(in_array('default', $resize_ary)){//保存不加水印的原图
							@copy($c['root_path'].$new_path, $c['root_path'].$new_path.".default.{$ext_name}");
						}
						if($c['manage']['config']['IsWater'] && $c['manage']['config']['IsThumbnail']){//缩略图加水印
							img::img_add_watermark($new_path);
							$water_ary=array();
						}
						foreach((array)$resize_ary as $v2){
							if($v2=='default') continue;
							$size_w_h=explode('x', $v2);
							$resize_path=img::resize($new_path, $size_w_h[0], $size_w_h[1]);
						}
					}
					foreach((array)$water_ary as $v2){
						img::img_add_watermark($v2);
					}
					$ImgPath[$k]=$new_path;
					//删除原图片
					if($prod_row){
						foreach($resize_ary as $v2){
							$ext_name=file::get_ext_name($prod_row['PicPath_'.$k]);
							file::del_file($prod_row['PicPath_'.$k].".{$v2}.{$ext_name}");
						}
						file::del_file($prod_row['PicPath_'.$k]);
					}
				}else{
					$ImgPath[$k]=$prod_row['PicPath_'.$k];
				}
			}
			foreach((array)$ImgPath as $k=>$v){
				$ext_name=file::get_ext_name($v);
				foreach((array)$resize_ary as $v2){
					if($v2=='default') continue;
					if(!is_file($c['root_path'].$v.".{$v2}.{$ext_name}")){
						$size_w_h=explode('x', $v2);
						$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
					}
				}
				if(!is_file($c['root_path'].$v.".default.{$ext_name}")){
					@copy($c['root_path'].$v, $c['root_path'].$v.".default.{$ext_name}");
				}
			}
			//上传数据
			$data=array(
				'CateId'					=>	$CateId,
				'Number'					=>	$Number,
				'SKU'						=>	$SKU,
				'PurchasePrice'				=>	(float)$PurchasePrice,
				'Price_0'					=>	(float)$Price_0,
				'Price_1'					=>	(float)$Price_1,
				'Wholesale'					=>	$Wholesale,
				'PicPath_0'					=>	$ImgPath[0],
				'PicPath_1'					=>	$ImgPath[1],
				'PicPath_2'					=>	$ImgPath[2],
				'PicPath_3'					=>	$ImgPath[3],
				'PicPath_4'					=>	$ImgPath[4],
				'Weight'					=>	(float)$Weight,
				'Cubage'					=>	$Cubage,
				'MOQ'						=>	$MOQ,
				'MaxOQ'						=>	$MaxOQ,
				'Stock'						=>	(int)$Stock,
				'SoldOut'					=>	$p_SoldOut,
				'IsFreeShipping'			=>	(int)$p_IsFreeShipping,
				'IsNew'						=>	(int)$p_IsNew,
				'IsHot'						=>	(int)$p_IsHot,
				'IsBestDeals'				=>	(int)$p_IsBestDeals,
			);
			$column_products_ary=db::get_table_fields('products', 1);
			$column_products_seo_ary=db::get_table_fields('products_seo', 1);
			$column_products_description_ary=db::get_table_fields('products_description', 1);
			foreach((array)$Name as $k=>$v){//产品名称
				if(@!in_array("Name_{$k}", $column_products_ary)) continue;
				$data["Name_{$k}"]=addslashes(stripslashes($v));
			}
			foreach((array)$BriefDescription as $k=>$v){//简短介绍
				if(@!in_array("BriefDescription_{$k}", $column_products_ary)) continue;
				$data["BriefDescription_{$k}"]=addslashes(stripslashes($v));
			}
			foreach((array)$Description as $k=>$v){//详细介绍
				if(@!in_array("Description_{$k}", $column_products_description_ary)) continue;
				$Desc_data["Description_{$k}"]=addslashes(stripslashes($v));
			}
			foreach((array)$SeoTitle as $k=>$v){//SEO标题
				if(@!in_array("SeoTitle_{$k}", $column_products_seo_ary)) continue;
				$Seo_data["SeoTitle_{$k}"]=addslashes(stripslashes($v));
			}
			foreach((array)$SeoKeyword as $k=>$v){//SEO关键词
				if(@!in_array("SeoKeyword_{$k}", $column_products_seo_ary)) continue;
				$Seo_data["SeoKeyword_{$k}"]=addslashes(stripslashes($v));
			}
			foreach((array)$SeoDescription as $k=>$v){//SEO简述
				if(@!in_array("SeoDescription_{$k}", $column_products_seo_ary)) continue;
				$Seo_data["SeoDescription_{$k}"]=addslashes(stripslashes($v));
			}
			//执行
			if($ProId){
				$data['EditTime']=$c['time'];
				db::update('products', "ProId='$ProId'", $data);
				if(!db::get_row_count('products_seo', "ProId='$ProId'")){
					db::insert('products_seo', array('ProId'=>$ProId));
				}
				if(!db::get_row_count('products_description', "ProId='$ProId'")){
					db::insert('products_description', array('ProId'=>$ProId));
				}
				db::update('products_seo', "ProId='$ProId'", $Seo_data);
				db::update('products_description', "ProId='$ProId'", $Desc_data);
				manage::operation_log('修改产品');
			}else{
				$data['AccTime']=$c['time'];
				db::insert('products', $data);
				$ProId=db::get_insert_id();
				$Seo_data['ProId']=$ProId;
				db::insert('products_seo', $Seo_data);
				$Desc_data['ProId']=$ProId;
				db::insert('products_description', $Desc_data);
				manage::operation_log('添加产品');
			}
		}
		//返回结果
		if(!$ProId){ $errerTxt.="(上传失败) 数据提交失败 "; }
		if($errerTxt){//上传失败
			$result=$errerTxt;
			$ret=0;
		}else{//上传成功
			$result='success';
			$ret=1;
		}
		
		ly200::e_json($result, $ret);
		exit;
	}
	
	
	/*************************************************************************************************************
	网站请求【同步订单】【网站->主控制面板】
	*************************************************************************************************************/
	public static function sync_get_orders(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		$p_Count=(int)$p_Count;
		($p_Count<=0 || $p_Count>200) && $p_Count=200;
		$Page=(int)$p_Page;
		$page=$Page>0?$Page:1;
		$where="OrderStatus in(4,5,6,7)";
		(int)$p_OrderNo && $where.=" and OId='$p_OrderNo'";
		(int)$p_PayStartTime && $where.=" and PayTime>'$p_PayStartTime'";
		(int)$p_PayEndTime && $where.=" and PayTime<'$p_PayEndTime'";
		$row_count=db::get_row_count('orders', $where, 'OrderId');
		if($p_Count){
			$orders_row=db::get_limit('orders', $where, '*', 'OrderId desc', ($page-1)*$p_Count, $p_Count);
			$total_page=ceil($row_count/$p_Count);
		}else{
			$orders_row=db::get_all('orders', $where, '*', 'OrderId desc');
			$total_page=1;
		}
		if(!count($orders_row)){
			echo str::json_data(array(
					'msg'		=>	'没有可同步订单！',
					'ret'		=>	0,
					'TotalPage'	=>	0
				)
			);
			exit;
		}
		//默认语言
		$language=db::get_value('config', "GroupId='global' and Variable='LanguageDefault'", 'Value');
		!$language && $language='en';
		//发货地
		$overseas_ary=array();
		$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', 'OvId asc'));
		foreach($overseas_row as $v){
			$overseas_ary[$v['OvId']]=$v;
		}
		//获取订单信息
		$order_data=$orderid=$products_list=$states_data=array();
		foreach($orders_row as $v){$orderid[]=$v['OrderId'];}
		$orderid_list=implode(',', $orderid);
		$orders_products_list=db::get_all('orders_products_list', "OrderId in($orderid_list)");
		$state_row=db::get_all('country_states', 1, 'SId,States', 'CId asc, if(MyOrder>0, if(MyOrder=999, 1000001, MyOrder), 1000000) asc');
		foreach($state_row as $v){$states_data[$v['SId']]=$v['States'];}
		$shipping_cfg=db::get_one('shipping_config', "Id='1'", 'AirName,OceanName');
		//订单产品
		foreach($orders_products_list as $v){
			$num=$v['OrderId'];
			if($v['BuyType']==4){//组合促销
				$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
				if(!$package_row) continue;
				$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
				$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
				$pro_where=='' && $pro_where=0;
				$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
				foreach($products_row as $k2=>$v2){
					$_ary=array(
						'Name'			=>	$v2['Name_'.$language],
						'SKU'			=>	$v2['SKU'],
						'PicPath'		=>	$v2['PicPath_0'],
						'Weight'		=>	$v2['Weight'],
						'Volume'		=>	0.000,
						'Price'			=>	$v2['Price_1'],
						'Qty'			=>	$v['Qty'],
						'Property'		=>	'',
						'PropertyPrice'	=>	0.00,
						'Discount'		=>	100,
						'Remark'		=>	$v['Remark'],
						'ProductId'		=>	$v2['ProId'],
						'Overseas'		=>	$overseas_ary[$v2['OvId']]['Name_'.$language]
					);
					$products_list[$num][]=$_ary;
				}
			}else{
				$v['ProductId']=$v['ProId'];
				$v['Overseas']=$overseas_ary[$v['OvId']]['Name_'.$language];
				unset($v['LId'],$v['OrderId'],$v['ProId'],$v['BuyType'],$v['KeyId'],$v['StartFrom'],$v['Language'],$v['AccTime'],$v['OvId'],$v['Status']);
				$products_list[$num][]=$v;
			}
		}
		//导出订单信息
		foreach($orders_row as $v){
			$num=$v['OrderId'];
			$v['OrderTotalPrice']=orders::orders_price($v, 1, 1);
			!(int)$v['ShippingInsurance'] && $v['ShippingInsurancePrice']=0;
			((int)$v['ShippingSId'] && !$v['ShippingState']) && $v['ShippingState']=$states_data[$v['ShippingSId']];
			((int)$v['BillSId'] && !$v['BillState']) && $v['BillState']=$states_data[$v['BillSId']];
			$CodeOptionAry=array(1=>'CPF', 2=>'CNPJ', 3=>'Personal ID', 4=>'VAT ID');
			(int)$v['ShippingCodeOption'] && $v['ShippingCodeOption']=$CodeOptionAry[$v['ShippingCodeOption']];
			(int)$v['BillCodeOption'] && $v['BillCodeOption']=$CodeOptionAry[$v['BillCodeOption']];
			if(!$v['ShippingMethodSId'] && $v['ShippingMethodType']){
				$v['ShippingMethodType']=='air' && $v['ShippingExpress']==$shipping_cfg['AirName'];
				$v['ShippingMethodType']=='ocean' && $v['ShippingExpress']==$shipping_cfg['OceanName'];
			}
			$v['Currency']=$v['ManageCurrency'];
			foreach($overseas_ary as $k2=>$v2){
				$v['ShippingOvExpress']=str_replace('"'.$k2.'":', '"'.$v2['Name_'.$language].'":', $v['ShippingOvExpress']);
				$v['OvTrackingNumber']=str_replace('"'.$k2.'":', '"'.$v2['Name_'.$language].'":', $v['OvTrackingNumber']);
				$v['OvShippingTime']=str_replace('"'.$k2.'":', '"'.$v2['Name_'.$language].'":', $v['OvShippingTime']);
				$v['OvRemarks']=str_replace('"'.$k2.'":', '"'.$v2['Name_'.$language].'":', $v['OvRemarks']);
			}
			$orders_log=array();
			$log_row=db::get_all('orders_log', "OrderId='{$v['OrderId']}'", 'OrderId, UserName, Log, OrderStatus, AccTime', 'LId desc');
			foreach($log_row as $k2=>$v2){
				$_ary=array(
					'Name'			=>	$v2['UserName'],
					'OrderStatus'	=>	$c['orders']['status'][$v2['OrderStatus']],
					'Log'			=>	$v2['Log'],
					'AccTime'		=>	$v2['AccTime']//date('Y-m-d H:i:s', $v2['AccTime'])
				);
				$orders_log[]=$_ary;
			}
			$paypal_address_row=db::get_one('orders_paypal_address_book', "OrderId={$v['OrderId']}");
            if($paypal_address_row){
				$v['PaypalFirstName']=$paypal_address_row['FirstName'];
				$v['PaypalLastName']=$paypal_address_row['LastName'];
				$v['PaypalAddressLine1']=$paypal_address_row['AddressLine1'];
				$v['PaypalCountryCode']=$paypal_address_row['CountryCode'];
				$v['PaypalPhoneNumber']=$paypal_address_row['PhoneNumber'];
				$v['PaypalCity']=$paypal_address_row['City'];
				$v['PaypalState']=$paypal_address_row['State'];
				$v['PaypalCountry']=$paypal_address_row['Country'];
				$v['PaypalZipCode']=$paypal_address_row['ZipCode'];
			}
			unset($v['OrderId'],$v['UserId'],$v['SalesId'],$v['Source'],$v['ManageCurrency'],$v['ShippingCId'],$v['ShippingSId'],$v['ShippingCodeOptionId'],$v['BillCId'],$v['BillSId'],$v['BillCodeOptionId'],$v['ShippingMethodSId'],$v['ShippingMethodType'],$v['ShippingInsurance'],$v['ShippingOvSId'],$v['ShippingOvType'],$v['ShippingOvInsurance'],$v['ShippingOvPrice'],$v['ShippingOvInsurancePrice'],$v['OvShippingStatus'],$v['Comments'],$v['PId'],$v['CancelReason'],$v['CutStock'],$v['CutUser'],$v['CutSuccess']);
			$order_data[]=array(
				'orders'				=>	$v,
				'orders_products_list'	=>	$products_list[$num],
				'orders_log'			=>	$orders_log
			);
		}
		//返回结果
		echo str::json_data(array(
				'msg'		=>	$order_data,
				'ret'		=>	1,
				'TotalPage'	=>	$total_page
			)
		);
		exit;
	}
	
	
	/*************************************************************************************************************
	主控制面板请求【发货同步】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_orders_track(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		!$p_Trackinfo && ly200::e_json('没有运单号数据', 0);
		$time=$c['time'];
		$Trackinfo=str::json_data(str::str_code(htmlspecialchars_decode($p_Trackinfo), 'stripslashes'), 'decode');
		$RemarksAry=(array)str::json_data(str::str_code(htmlspecialchars_decode($p_Remarks), 'stripslashes'), 'decode');
		$where='OId in(0';
		$i=0;
		foreach((array)$Trackinfo as $k=>$v){
			if($i++>100){break;}
			$where.=','.$k;
		}
		$where.=')';
		//订单信息
		$order_ary=array();
		$orders_row=db::get_all('orders', $where." and OrderStatus=4");
		foreach((array)$orders_row as $k=>$v){
			$order_ary[$v['OId']]=$v;
			orders::orders_log($v['UserId'], 'API', $v['OrderId'], 5, 'Update order status from Awaiting Shipping to Shipment Shipped', 1);
		}
		//默认语言
		$language=db::get_value('config', "GroupId='global' and Variable='LanguageDefault'", 'Value');
		!$language && $language='en';
		//更新数据
		$result_data=array();
		foreach((array)$Trackinfo as $k=>$v){
			if($order_ary[$k]){//有订单数据
				$OId=$k;
				$OrderId=$order_ary[$OId]['OrderId'];
				$Remarks=$RemarksAry[$k];
				$data=array(
					'TrackingNumber'	=>	addslashes(stripslashes($v)),
					'ShippingTime'		=>	$time,
					'OrderStatus'		=>	5,
					'Remarks'			=>	addslashes(stripslashes($Remarks)),
					'UpdateTime'		=>	$c['time']
				);
				db::update('orders', "OrderId='{$OrderId}'", $data);
				$result_data[$OId]=1;
			}else{//没订单数据
				$result_data[$k]=0;
			}
		}
		//发邮件
		$c['lang']='_'.$language;
		$orders_row_email=db::get_all('orders', $where." and OrderStatus=5");
		foreach((array)$orders_row_email as $k=>$v){
			$orders_row=$v;
			$OId=$v['OId'];
			$orders_row['OrderStatus']=5;
			$ToAry=array($v['Email']);
			include($c['root_path'].'/static/static/inc/mail/order_shipped.php');
			ly200::sendmail($ToAry, "Your order#{$OId} has shipped.", $mail_contents);
		}
		//返回结果
		ly200::e_json($result_data, 1);
	}
	
	
	/*************************************************************************************************************
	主控制面板请求【同步产品库存信息】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_product_stock(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		$p_SKU=$p_SKU;//产品SKU
		$p_Quantity=(int)$p_Quantity;//库存数量
		!$p_SKU && ly200::e_json('没有相关的产品SKU', 0);
		$Status='OnSale'; //OnSale在售（上架） InStock仓库中（下架）
		//产品
		$products_row=db::get_one('products', "SKU='{$p_SKU}'");
		if($products_row){
			($p_Quantity<$products_row['MOQ'] || $p_Quantity<1 || $products_row['SoldOut'] || ($products_row['IsSoldOut'] && ($products_row['SStartTime']>$c['time'] || $c['time']>$products_row['SEndTime']))) && $Status='InStock';
			db::update('products', "ProId='{$products_row['ProId']}'", array('Stock'=>$p_Quantity));
			
			ly200::e_json(array('Status'=>$Status), 1);
		}
		//产品关联属性
		$combination_row=db::get_one('products_selected_attribute_combination', "SKU='{$p_SKU}'");
		$products_row=db::get_one('products', "ProId='{$combination_row['ProId']}'");
		if($combination_row){
			($p_Quantity<$products_row['MOQ'] || $p_Quantity<1 || $products_row['SoldOut'] || ($products_row['IsSoldOut'] && ($products_row['SStartTime']>$c['time'] || $c['time']>$products_row['SEndTime']))) && $Status='InStock';
			db::update('products_selected_attribute_combination', "CId='{$combination_row['CId']}'", array('Stock'=>$p_Quantity));
			
			ly200::e_json(array('Status'=>$Status), 1);
		}
		
		ly200::e_json('没有相关的产品SKU', 0);
	}
	
	
	/*************************************************************************************************************
	主控制面板请求【获取产品分类】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_get_products_category(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		!$c['lang_name'][$p_Language] && ly200::e_json('没有相关的语言信息', 0);
		$page_count=(int)$p_Count?(int)$p_Count:1000;
		$category_row=db::get_limit('products_category', '1', "CateId, Category_{$p_Language}", 'CateId asc', 0, $page_count);
		!count($category_row) && ly200::e_json('没有相关的产品分类', 0);
		//产品分类
		$category_data=array();
		foreach($category_row as $v){ //产品主图
			$category_data[$v['CateId']]=array(
				'ID'	=>	$v['CateId'],
				'Name'	=>	$v["Category_{$p_Language}"]
			);
		}
		//返回结果
		ly200::e_json($category_data, 1);
	}
	
	
	/*************************************************************************************************************
	主控制面板请求【获取产品基础信息】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_get_products_list(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		$config_row=db::get_value('config', "GroupId='global' and Variable='Language'", 'Value');
		$lang_ary=@explode(',', $config_row);
		$Column='Number, SKU, CateId';
		foreach($c['lang_name'] as $k=>$v){
			if(!in_array($k, $lang_ary)) continue;
			$Column.=", Name_{$k}";
		}
		$page_count=(int)$p_Count?(int)$p_Count:50;
		$where='1';
		if($p_CateId){
			$where.=" and (CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($p_CateId)."%') or CateId='{$p_CateId}')";
		}
		$products_row=db::get_limit('products', $where, $Column, 'ProId asc', 0, $page_count);
		!count($products_row) && ly200::e_json('没有相关的产品', 0);
		//产品
		foreach($products_row as $v){
			$products_data[]=$v;
		}
		//返回结果
		ly200::e_json($products_data, 1);
	}
	
	
	/*************************************************************************************************************
	主控制面板请求【获取产品详细信息】【主控制面板->网站】
	*************************************************************************************************************/
	public static function sync_get_products(){
		global $c;
		@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
		//初始化
		!$c['lang_name'][$p_Language] && ly200::e_json('没有相关的语言信息', 0);
		$where='1';
		$p_No && $where.=" and Number='$p_No'";
		$p_SKU && $where.=" and SKU='$p_SKU'";
		$row=str::str_code(db::get_one('products', $where, '*', 'ProId desc'));
		!count($row) && ly200::e_json('没有相关的产品信息', 0);
		//信息
		$ProId=$row['ProId'];
		$seo_row=str::str_code(db::get_one('products_seo', "ProId='$ProId'"));
		$desc_row=str::str_code(db::get_one('products_description', "ProId='$ProId'"));
		//产品
		$Wholesale='';
		$wholesale_price=str::json_data(htmlspecialchars_decode($row['Wholesale']), 'decode');
		if($wholesale_price){
			$wholesale_ary=array();
			foreach((array)$wholesale_price as $k=>$v){
				$wholesale_ary[]="{$k}:{$v}";
			}
			$Wholesale=implode(',', $wholesale_ary);
		}
		$products_data=array(
			'Name'			=>	addslashes(stripslashes($row["Name_{$p_Language}"])),
			'Number'		=>	$row['Number'],
			'SKU'			=>	$row['SKU'],
			'CateId'		=>	$row['CateId'],
			'PurchasePrice'	=>	$row['PurchasePrice'],
			'Price_0'		=>	$row['Price_0'],
			'Price_1'		=>	$row['Price_1'],
			'Wholesale'		=>	$Wholesale,
			'Weight'		=>	$row['Weight'],
			'Cubage'		=>	str_replace(',', 'x', $row['Cubage']),
			'MOQ'			=>	$row['MOQ'],
			'MaxOQ'			=>	$row['MaxOQ'],
			'Stock'			=>	$row['Stock'],
			'SoldOut'		=>	$row['SoldOut'],
			'IsFreeShipping'=>	$row['IsFreeShipping'],
			'IsNew'			=>	$row['IsNew'],
			'IsHot'			=>	$row['IsHot'],
			'IsBestDeals'	=>	$row['IsBestDeals'],
			'Brief'			=>	addslashes(stripslashes($row["BriefDescription_{$p_Language}"]))
		);
		if($seo_row){
			$products_data['SeoTitle']=addslashes(stripslashes($seo_row["SeoTitle_{$p_Language}"]));
			$products_data['SeoKeyword']=addslashes(stripslashes($seo_row["SeoKeyword_{$p_Language}"]));
			$products_data['SeoDescription']=addslashes(stripslashes($seo_row["SeoDescription_{$p_Language}"]));
		}
		if($desc_row){
			$products_data['Description']=addslashes(stripslashes($desc_row["Description_{$p_Language}"]));
		}
		//返回结果
		ly200::e_json($products_data, 1);
	}
}
