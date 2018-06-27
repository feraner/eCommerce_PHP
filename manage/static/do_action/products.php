<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class products_module{
	public static function products_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ProId=(int)$p_ProId;
		//基本信息
		$CateId=(int)$p_CateId;
		$PromotionDiscount=(int)($p_PromotionDiscount?$p_PromotionDiscount:100);
		$PromotionDiscount=$PromotionDiscount>100?100:$PromotionDiscount;
		$p_PromotionTime=@explode('/', $p_PromotionTime);
		$StartTime=@strtotime($p_PromotionTime[0]);
		$EndTime=@strtotime($p_PromotionTime[1]);
		//上传图片
		$PicPath=$p_PicPath;
		$ColorPath=$p_ColorPath;
		$p_UpdateWater=(int)$p_UpdateWater;
		//产品属性
		$p_Cubage=@implode(',', $p_Cubage);
		$p_MOQ=(int)$p_MOQ;
		$p_MOQ=(1<$p_MOQ && $p_MOQ<=$p_Stock)?$p_MOQ:1;
		$p_MaxOQ=(int)$p_MaxOQ;
		($p_MaxOQ && $p_MaxOQ<$p_MOQ) && $p_MaxOQ=$p_MOQ;
		$p_MaxOQ=($p_MaxOQ<0)?0:$p_MaxOQ;
		
		$p_Stock=(int)$p_Stock;
		$AttrId=(int)$p_AttrId;
		$p_SoldOut=(int)$p_SoldOut;
		$p_IsSoldOut=(int)$p_IsSoldOut;
		$p_SoldOut && $p_IsSoldOut=0;
		$SoldOutTime=@explode('/', $p_SoldOutTime);
		$SStartTime=@strtotime($SoldOutTime[0]);
		$SEndTime=@strtotime($SoldOutTime[1]);
		($p_DefaultReviewRating<0 || $p_DefaultReviewRating>5) && $p_DefaultReviewRating=5;
		$Description=$p_Description;
		$IsDesc=addslashes(str::json_data(str::str_code($p_IsDesc, 'stripslashes')));
		
		//产品编号自动排序
		$Prefix='';
		$cfg_row=str::str_code(db::get_all('config', 'GroupId="products_show"'));
		foreach($cfg_row as $v){
			$cfg_ary[$v['GroupId']][$v['Variable']]=$v['Value'];
		}
		$used_row=str::json_data(htmlspecialchars_decode($cfg_ary['products_show']['Config']), 'decode');
		$Number=$p_Number;
		if($cfg_ary['products_show']['myorder'] && $used_row['myorder']){
			$Prefix=$cfg_ary['products_show']['myorder'];
		}
		if($p_Prefix) $Prefix=$p_Prefix;
		
		if($p_Number && db::get_row_count('products', "ProId!='$ProId' and Prefix='$Prefix' and Number='$Number'")) ly200::e_json(manage::get_language('products.products.number_tips'));//存草稿 和 草稿产品 除外
		if(!count($PicPath)) ly200::e_json(manage::get_language('products.products.pic_tips'));
		
		//扩展分类
		if($p_ExtCateId){
			$p_ExtCateId=array_unique($p_ExtCateId);
			if($zero=array_search(0, $p_ExtCateId)){
				unset($p_ExtCateId[$zero]);
			}
			$ExtCateId=','.implode(',',$p_ExtCateId).',';
		}
		
		//图片上传
		$ImgPath=array();
		$resize_ary=$c['manage']['resize_ary']['products'];
		$save_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		if((int)$p_SaveDrafts==0){//存草稿箱，不保存图片
			foreach((array)$PicPath as $k=>$v){
				if(!is_file($c['root_path'].$v)) continue;
				$ImgPath[]=file::photo_tmp_upload($v, $save_dir, $resize_ary);
			}
			if(!count($ImgPath)) ly200::e_json(manage::get_language('products.products.pic_tips'));
		}
		if($p_UpdateWater && $c['manage']['config']['IsWater']){//更新水印图片
			foreach((array)$ImgPath as $k=>$v){
				$water_ary=array($v);
				$ext_name=file::get_ext_name($v);
				@copy($c['root_path'].$v.".default.{$ext_name}", $c['root_path'].$v);//覆盖大图
				if($c['manage']['config']['IsThumbnail']){//缩略图加水印
					img::img_add_watermark($v);
					$water_ary=array();
				}
				foreach($resize_ary as $v2){
					if($v=='default') continue;
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
				}
				foreach((array)$water_ary as $v2){
					img::img_add_watermark($v2);
				}
			}
		}
		foreach((array)$ImgPath as $k=>$v){
			$ext_name=file::get_ext_name($v);
			foreach($resize_ary as $v2){
				if(!is_file($c['root_path'].$v.".{$v2}.{$ext_name}")){
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
				}
			}
			if(!is_file($c['root_path'].$v.".default.{$ext_name}")){
				@copy($c['root_path'].$v, $c['root_path'].$v.".default.{$ext_name}");
			}
		}
		
		//批发价
		$wholesale_ary=array();
		foreach((array)$p_Qty as $k=>$v){
			$Qty=(int)$p_Qty[$k];
			$Price=(float)$p_Price[$k];
			if($Qty && $Price) $wholesale_ary[$Qty]=$Price;
		}
		ksort($wholesale_ary);//从小到大排序，防止故意乱填
		$Wholesale=addslashes(str::json_data(str::str_code($wholesale_ary, 'stripslashes')));
		
		//单位
		if($p_Unit){
			$unit_list=str::json_data(htmlspecialchars_decode(db::get_value('config', 'GroupId="products" and Variable="Unit"', 'Value')), 'decode');
			if(!in_array($p_Unit, $unit_list)){ //追加新的单位
				$unit_list[]=$p_Unit;
				$json_unit=addslashes(str::json_data(str::str_code($unit_list, 'stripslashes')));
				manage::config_operaction(array('Unit'=>$json_unit), 'products');
			}
		}
		
		//平台导流
		$platform_ary=array('amazon','aliexpress','wish','ebay','alibaba');
		$platform=array();
		foreach($platform_ary as $k => $v){
			foreach((array)$c['manage']['config']['Language'] as $v1){
				$Url='p_Platform_'.$k.'_Url_'.$v1;
				$Name='p_Platform_'.$k.'_Name_'.$v1;
				$$Url=array_filter($$Url);
				if(!$$Url) continue;
				//目前只支持1个，暂时不需要循环
				$Name=$$Name;
				$Url=$$Url;
				$platform[$v][0]['Name_'.$v1]=$Name[0];
				$platform[$v][0]['Url_'.$v1]=$Url[0];
			}
		}
		
		//存草稿
		(int)$p_SaveDrafts==1 && $p_SoldOut=1;//产品自动下架
		
		$data = array(
			'CateId'					=>	$CateId,
			'ExtCateId'					=>	$ExtCateId,
			'Prefix'					=>	$Prefix,
			'Number'					=>	$Number,
			'SKU'						=>	$p_SKU,
			'Business'					=>	(int)$p_Business,
			'PurchasePrice'				=>	(float)$p_PurchasePrice,
			'Price_0'					=>	(float)$p_Price_0,
			'Price_1'					=>	(float)$p_Price_1,
			'IsPromotion'				=>	(int)$p_IsPromotion,
			'PromotionType'				=>	(int)$p_PromotionType,
			'PromotionPrice'			=>	(float)$p_PromotionPrice,
			'PromotionDiscount'			=>	(int)$PromotionDiscount,
			'StartTime'					=>	$StartTime,
			'EndTime'					=>	$EndTime,
			'Wholesale'					=>	$Wholesale,
			'PicPath_0'					=>	$ImgPath[0],
			'PicPath_1'					=>	$ImgPath[1],
			'PicPath_2'					=>	$ImgPath[2],
			'PicPath_3'					=>	$ImgPath[3],
			'PicPath_4'					=>	$ImgPath[4],
			'PicPath_5'					=>	$ImgPath[5],
			'PicPath_6'					=>	$ImgPath[6],
			'PicPath_7'					=>	$ImgPath[7],
			'PicPath_8'					=>	$ImgPath[8],
			'PicPath_9'					=>	$ImgPath[9],
			'Weight'					=>	(float)$p_Weight,
			'Cubage'					=>	$p_Cubage,
			'Unit'						=>	$p_Unit,
			'MOQ'						=>	$p_MOQ,
			'MaxOQ'						=>	$p_MaxOQ,
			'Sales'						=>	(int)$p_Sales,
			'Stock'						=>	(int)$p_Stock,
			'WarnStock'					=>	(int)$p_WarnStock,
			'StockOut'					=>	(int)$p_StockOut,
			//'IsIncrease'				=>	(int)$p_IsIncrease,
			'IsCombination'				=>	(int)$p_IsCombination,
			'AttrId'					=>	$AttrId,
			'SoldOut'					=>	$p_SoldOut,
			'IsSoldOut'					=>	$p_IsSoldOut,
			'SStartTime'				=>	$SStartTime,
			'SEndTime'					=>	$SEndTime,
			'IsFreeShipping'			=>	(int)$p_IsFreeShipping,
			'IsVolumeWeight'			=>	(int)$p_IsVolumeWeight,
			'IsNew'						=>	(int)$p_IsNew,
			'IsHot'						=>	(int)$p_IsHot,
			'IsBestDeals'				=>	(int)$p_IsBestDeals,
			'IsIndex'					=>	(int)$p_IsIndex,
			'IsDefaultReview'			=>	(int)$p_IsDefaultReview,
			'DefaultReviewRating'		=>	(float)$p_DefaultReviewRating,
			'DefaultReviewTotalRating'	=>	(int)$p_DefaultReviewTotalRating,
			'FavoriteCount'				=>	(int)$p_FavoriteCount,
			'PackingStart'				=>	(int)$p_PackingStart,
			'PackingQty'				=>	(int)$p_PackingQty,
			'PackingWeight'				=>	(float)$p_PackingWeight,
			'IsDesc'					=>	$IsDesc,
			'PageUrl'					=>	ly200::str_to_url($p_PageUrl),
			'Platform'					=>	str::json_data($platform),
			'MyOrder'					=>	(int)$p_MyOrder
		);
		
		if($ProId){
			$data['EditTime']=$c['time'];
			db::update('products', "ProId='$ProId'", $data);
			if(!db::get_row_count('products_seo', "ProId='$ProId'")){
				db::insert('products_seo', array('ProId'=>$ProId));
			}
			if(!db::get_row_count('products_description', "ProId='$ProId'")){
				db::insert('products_description', array('ProId'=>$ProId));
			}
			manage::operation_log('修改产品');
		}else{
			$data['AccTime']=$c['time'];
			db::insert('products', $data);
			$ProId=db::get_insert_id();
			db::insert('products_seo', array('ProId'=>$ProId));
			db::insert('products_description', array('ProId'=>$ProId));
			manage::operation_log('添加产品');
		}
		
		//产品属性
		if(count($p_Attr)>0 || $p_AttrTxt){
			$cart_len=0; //规格属性勾选总数
			$vid_ary=$cart_attr_ary=$attrid_ary=array();
			foreach((array)$p_Attr as $v){
				if(!strstr($v, 'Ov:')){
					$vid_ary[]=$v;
				}
			}
			$vid_list=@implode(',', $vid_ary);
			!$vid_list && $vid_list='0';
			$attribute_row=str::str_code(db::get_all('products_attribute', "ParentId='$AttrId' and CartAttr=1", 'AttrId')); //所有规格属性的选项
			foreach((array)$attribute_row as $v){ $cart_attr_ary[]=$v['AttrId']; }
			$attr_row=str::str_code(db::get_all('products_attribute_value', "VId in ($vid_list)", 'VId, AttrId'));
			foreach((array)$attr_row as $v){ $attrid_ary[$v['VId']]=$v['AttrId']; }
			$insert_ary=$update_ary=$exist_ary=array();
			$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId='$ProId'"));
			foreach((array)$selected_row as $v){
				if($v['AttrId']==0 && $v['VId']==0 && $v['OvId']>0){ //发货地
					$exist_ary['Overseas'][$v['OvId']]=$v['SeleteId'];
				}else if($v['AttrId']>0 && $v['VId']==0 && $v['OvId']==0){ //文本框
					$exist_ary['Text'][$v['AttrId']]=$v['SeleteId'];
				}else{ //规格属性
					$exist_ary['List'][$v['VId']]=$v['SeleteId'];
				}
			}
			unset($selected_row);
			//列表选择
			if(count($p_Attr)>0){
				foreach((array)$p_Attr as $v){
					if(strstr($v, 'Ov:')){ //发货地
						$v=(int)str_replace('Ov:', '', $v);
						if($exist_ary['Overseas'][$v]){ //已存在
							$update_ary[$exist_ary['Overseas'][$v]]=array(
								'IsUsed'	=>	1
							);
						}else{ //不存在
							$insert_ary[]=array(
								'ProId'		=>	$ProId,
								'AttrId'	=>	'',
								'OvId'		=>	$v,
								'Value'		=>	'',
								'IsUsed'	=>	1
							);
						}
						$cart_len+=1; //记录 规格属性勾选总数 发货地一并计入
					}else{ //规格属性
						if($exist_ary['List'][$v]){ //已存在
							$update_ary[$exist_ary['List'][$v]]=array(
								'IsUsed'	=>	1
							);
						}else{ //不存在
							$insert_ary[]=array(
								'ProId'		=>	$ProId,
								'AttrId'	=>	$attrid_ary[$v],
								'VId'		=>	$v,
								'Value'		=>	'',
								'IsUsed'	=>	1
							);
						}
					}
					in_array($attrid_ary[$v], $cart_attr_ary) && $cart_len+=1; //记录 规格属性勾选总数
				}
			}else{
				$update_ary[0]=array();//清空勾选项
			}
			//文本框
			foreach((array)$p_AttrTxt as $k=>$v){
				if($exist_ary['Text'][$k]){ //已存在
					$update_ary[$exist_ary['Text'][$k]]=array(
						'Value'		=>	addslashes($v),
						'IsUsed'	=>	1
					);
				}else{ //不存在
					$insert_ary[]=array(
						'ProId'		=>	$ProId,
						'AttrId'	=>	$k,
						'VId'		=>	0,
						'Value'		=>	addslashes($v),
						'IsUsed'	=>	1
					);
				}
			}
			db::update('products_selected_attribute', "ProId='$ProId'", array('IsUsed'=>0)); //先默认全部关闭
			if(count($update_ary)){
				foreach((array)$update_ary as $k=>$v){
					$k>0 && db::update('products_selected_attribute', "SeleteId='$k'", $v);
				}
			}
			foreach((array)$insert_ary as $k=>$v){
				db::insert('products_selected_attribute', $v);
			}
			if(db::get_row_count('products_selected_attribute', "ProId='$ProId' and IsUsed=0")){//清空多余没用的选项数据
				db::delete('products_selected_attribute', "ProId='$ProId' and IsUsed=0");
			}
			
			if($cart_len>0){ //有规格属性勾选
				$stock=$total_stock=$attr_count=0;
				$insert_ary=$update_ary=$ext_ary=$exist_ary=$cid_ary=array();
				$combination_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='$ProId'"));
				foreach((array)$combination_row as $v){ $exist_ary[$v['Combination']][$v['OvId']]=$v['CId']; }
				foreach((array)$p_AttrPrice as $k=>$v){
					if($k=='XXX') continue;
					$key_ary=explode('_', $k);
					$OvId=1;
					$key=array();
					foreach((array)$key_ary as $v2){
						if(strstr($v2, 'Ov:')){
							$OvId=(int)str_replace('Ov:', '', $v2);
						}else{
							$key[]=$v2;
						}
					}
					$key='|'.@implode('|', $key).'|';
					$ext_ary[$key][$OvId]=array($p_AttrSKU[$k], (int)$p_AttrIsIncrease[$k], $p_AttrPrice[$k], $p_AttrStock[$k], ($used_row['weight']?(float)$p_AttrWeight[$k]:abs((float)$p_AttrWeight[$k])));
				}
				foreach((array)$ext_ary as $k=>$v){
					foreach((array)$v as $k2=>$v2){
						$stock=(int)$v2[3];
						$stock<0 && $stock=0;
						$total_stock+=$stock;
						$attr_count+=1;
						if($exist_ary[$k][$k2]){ //已存在
							$cid_ary[]=$exist_ary[$k][$k2];
							$update_ary[$exist_ary[$k][$k2]]=array(
								'SKU'			=>	$v2[0],
								'IsIncrease'	=>	$v2[1],
								'Price'			=>	$v2[2],
								'Stock'			=>	$stock,
								'Weight'		=>	$v2[4]
							);
						}else{ //不存在
							$insert_ary[]=array(
								'ProId'			=>	$ProId,
								'Combination'	=>	$k,
								'OvId'			=>	$k2,
								'SKU'			=>	$v2[0],
								'IsIncrease'	=>	$v2[1],
								'Price'			=>	$v2[2],
								'Stock'			=>	$stock,
								'Weight'		=>	$v2[4]
							);
						}
					}
				}
				foreach((array)$update_ary as $k=>$v){
					db::update('products_selected_attribute_combination', "CId='$k'", $v);
				}
				if(count($cid_ary)>0){//清空多余没用的选项数据
					$cid_str=implode(',', $cid_ary);
					db::delete('products_selected_attribute_combination', "ProId='$ProId' and CId not in($cid_str)");
				}else{//清空已经取消勾选的选项数据
					db::delete('products_selected_attribute_combination', "ProId='$ProId'");
				}
				foreach((array)$insert_ary as $k=>$v){
					db::insert('products_selected_attribute_combination', $v);
				}
				if($p_IsCombination){//开启组合属性，统计产品属性总库存，覆盖到产品库存上
					if((int)$used_row['stock']==0 && $total_stock<1){ //规格属性无限库存 和 合计库存小于1
						$total_stock=($data['Stock']<1?99999:$data['Stock']);
					}elseif((int)$used_row['stock']==1 && $total_stock<1){ //规格属性无限库存 和 合计库存小于1
						$total_stock=$data['Stock'];
					}
					(int)$attr_count && db::update('products', "ProId='$ProId'", array('Stock'=>$total_stock));
				}/*else{//关闭组合属性
					$total_stock=($data['Stock']<1?99999:$data['Stock']);
					count($update_ary)==0 && count($insert_ary)==0 && $total_stock=$data['Stock'];
					db::update('products', "ProId='$ProId'", array('Stock'=>$total_stock));
				}*/
			}else{ //没有规格属性勾选
				db::delete('products_selected_attribute_combination', "ProId='$ProId'");
			}
		}else{//取消所有属性选择
			//删除产品颜色图片
			$row=db::get_all('products_color', "ProId='$ProId'", 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9');
			foreach($row as $v){
				for($i=0; $i<10; $i++){
					$PicPath=$v["PicPath_$i"];
					if(is_file($c['root_path'].$PicPath)){
						foreach($resize_ary as $v2){
							$ext_name=file::get_ext_name($PicPath);
							file::del_file($PicPath.".{$v2}.{$ext_name}");
						}
						file::del_file($PicPath);
					}
				}
			}
			db::delete('products_selected_attribute', "ProId='$ProId'");
			db::delete('products_selected_attribute_combination', "ProId='$ProId'");
			db::delete('products_color', "ProId='$ProId'");
		}
		
		//颜色套图上传
		$CPath=array();
		foreach((array)$ColorPath as $k=>$v){
			for($i=0; $i<10; ++$i){
				if(!is_file($c['root_path'].$v[$i])) continue;
				$CPath[$k][]=file::photo_tmp_upload($v[$i], $save_dir, $resize_ary);
			}
			for($i=0; $i<10; ++$i){//填补空白
				if(!$CPath[$k][$i]){
					$CPath[$k][$i]='';
				}
			}
		}
		if($CPath){
			if($p_UpdateWater && $c['manage']['config']['IsWater']){//更新水印图片
				foreach((array)$CPath as $k=>$v){
					foreach((array)$v as $k2=>$v2){
						$water_ary=array($v2);
						$ext_name=file::get_ext_name($v2);
						@copy($c['root_path'].$v2.".default.{$ext_name}", $c['root_path'].$v2);//覆盖大图
						if($c['manage']['config']['IsThumbnail']){//缩略图加水印
							img::img_add_watermark($v2);
							$water_ary=array();
						}
						foreach($resize_ary as $v3){
							if($v2=='default') continue;
							$size_w_h=explode('x', $v3);
							$resize_path=img::resize($v2, $size_w_h[0], $size_w_h[1]);
						}
						foreach((array)$water_ary as $v3){
							img::img_add_watermark($v3);
						}
					}
				}
			}
			foreach((array)$CPath as $k=>$v){
				$c_data=array();
				foreach((array)$v as $k2=>$v2){
					$c_data['PicPath_'.$k2]=$v2;
				}
				if(db::get_one('products_color', "ProId='$ProId' and VId='$k'")){
					db::update('products_color', "ProId='$ProId' and VId='$k'", $c_data);
				}else{
					$c_data['ProId']=$ProId;
					$c_data['VId']=$k;
					db::insert('products_color', $c_data);
				}
			}
		}
		manage::database_language_operation('products', "ProId='$ProId'", array('Name'=>1, 'BriefDescription'=>2));
		manage::database_language_operation('products_seo', "ProId='$ProId'", array('SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		$desc_data=array('Description'=>3);
		$desc_data=array_merge($desc_data, array('TabName_0'=>0, 'TabName_1'=>0, 'TabName_2'=>0, 'Tab_0'=>3, 'Tab_1'=>3, 'Tab_2'=>3));
		manage::database_language_operation('products_description', "ProId='$ProId'", $desc_data);
		//产品最低价格 Start
		$LowestPrice=cart::range_price_ext(db::get_one('products', "ProId='{$ProId}'"));
		db::update('products', "ProId='{$ProId}'", array('LowestPrice'=>$LowestPrice[0]));
		//产品最低价格 End
		if((int)$p_SaveDrafts==1 && $ProId){//存草稿
			ly200::e_json(array('tips'=>'资料已经保存到草稿箱', 'ProId'=>$ProId), 1);
		}else{
			ly200::e_json(array('jump'=>$p_back_action?$p_back_action:"?m=products&a=products&CateId=$CateId"), 1);
		}
	}
	
	public static function products_get_attr(){	//产品属性
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Content=$html_attr=$html_item='';
		$p_AttrId=(int)$p_AttrId; //属性ID
		$CateId=(int)$p_CateId; //产品分类ID
		$VId=(int)$p_VId; //产品属性选项ID
		$ProId=(int)$p_ProId; //产品ID
		$Type=(int)$p_Type; //执行类型 0:获取当前分类里的所有属性 1:获取当前的属性 2:获取当前属性的选项
		$p_IsCartAttr=(int)$p_IsCartAttr; //是否为购物车属性
		$Lang=$c['manage']['web_lang']; //语言
		$category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'", 'UId, AttrId'));
		if($category_row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($category_row['UId']);
			$category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'", 'AttrId'));
		}
		$ParentId=$category_row['AttrId'];
		if($ParentId && $p_AttrId && $ParentId==$p_AttrId){//相同的属性就不用继续执行
			ly200::e_json('', 0);
		}
		$callback=''; //返回函数
		$selected_ary=$combinatin_ary=$cart_attr_ary=$all_value_ary=$attrid=array();
		$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId='$ProId' and IsUsed=1", 'SeleteId, AttrId, VId, OvId, Value', 'SeleteId asc'));
		foreach($selected_row as $v){
			$selected_ary['Id'][$v['AttrId']][]=$v['VId']; //记录勾选属性ID
			$v['AttrId']>0 && $v['VId']==0 && $v['Value'] && $v['OvId']<2 && $selected_ary['Value'][$v['AttrId']]=stripslashes($v['Value']); //文本框内容
			$v['AttrId']==0 && $v['VId']==0 && $v['OvId']>0 && $selected_ary['Overseas'][]=$v['OvId']; //记录勾选属性ID 发货地
		}
		unset($selected_row);
		$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='$ProId'", '*', 'CId asc')); //属性组合数据
		foreach($combinatin_row as $v){ $combinatin_ary[$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']); }
		unset($combinatin_row);
		if($ParentId){
			$AttrName=str::str_code(db::get_value('products_attribute', "AttrId='$ParentId'", "Name{$Lang}")); //属性分类名称
			if(!$AttrName) ly200::e_json('', 0);//属性数据丢失就不用继续执行
			$row=str::str_code(db::get_all('products_attribute', "ParentId='$ParentId' and CartAttr='$p_IsCartAttr'", '*', $c['my_order'].'AttrId asc'));
			foreach($row as $v){ $attrid[]=$v['AttrId']; }
			$attrid_list=implode(',', $attrid);
			!$attrid_list && $attrid_list='0';
			$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
			foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
			unset($value_row);
			foreach($row as $v){
				$html='<div class="rows"><label>'.$v['Name'.$Lang].'</label><span class="input">';
				$id=$v['AttrId'];
				if($v['Type']){
					foreach((array)$all_value_ary[$id] as $k2=>$v2){
						$v['CartAttr']==1 && $cart_attr_ary[$id][$k2]=$v2['Value'.$Lang]; //购物车属性
						$data_ary=$data_oth_ary=array(
							'AttrId'	=>	$id,
							'Column'	=>	str_replace(array('&quot;', '"', "'"), array('"', '\"', '@8#'), $v['Name'.$Lang]),
							'Num'		=>	$k2,
							'Name'		=>	str_replace(array('"', "'"), array('\"', '@8#'), $v2['Value'.$Lang]),
							'Cart'		=>	$v['CartAttr'],
							'Color'		=>	$v['ColorAttr']
						);
						$data_ary['Name']=str_replace('&quot;', '"', $data_ary['Name']);
						$data=str::json_data($data_ary);
						$data_oth=str::json_data($data_oth_ary);
						//$data='{"AttrId":"'.$id.'","Column":"'.str_replace(array('&quot;', '"', "'"), array('"', '\"', '@8#'), $v['Name'.$Lang]).'","Num":"'.$k2.'","Name":"'.str_replace(array('"', "'"), array('\"', '@8#'), $v2['Value'.$Lang]).'","Cart":"'.$v['CartAttr'].'","Color":"'.$v['ColorAttr'].'"}';
						$checked=$check_on='';
						if(@in_array($k2, $selected_ary['Id'][$id])){
							$checked=' checked';
							$check_on=' current';
							$value=implode(',', (array)$combinatin_ary["|{$k2}|"][1]);
							$callback.="products_obj.products_edit_attr_init('{$data_oth}', 'Attr_{$k2}', '{$value}', 0);";
						}
						$_html="<span class='choice_btn{$check_on}'>{$v2['Value'.$Lang]}<input type='checkbox' name='Attr[]' value='{$k2}' id='Attr_{$k2}' class='form_check' data='{$data}'{$checked} /></span>";
						$html.=$_html;
						if($Type==2 && $k2==$VId) $html_item=$_html;
					}
					//$html.='<br /><a href="javascript:;" class="add_customize" data-id="'.$id.'" data-cart="'.$v['CartAttr'].'">+'.$c['manage']['lang_pack']['global']['add'].$c['manage']['lang_pack']['products']['model']['customize_attr'].'</a>';
					$html.='<a href="javascript:;" class="add_customize" data-id="'.$id.'" data-cart="'.$v['CartAttr'].'">+</a>';
				}else{
					$html.='<div class="txt"><input type="text" name="AttrTxt['.$id.']" value="'.$selected_ary['Value'][$id].'" class="form_input" size="30" maxlength="100" /></div>';
				}
				$html.='</span></div>';
				$Content.=$html;
				if($Type==1 && $v['AttrId']==$p_AttrId) $html_attr=$html;
			}
		}
		//海外仓 Start
		$shipping_overseas_row=db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc');
		$oversea_len=count($shipping_overseas_row);
		if($oversea_len>0 && $p_IsCartAttr==1){
			$IsCombination=(int)db::get_value('products', "ProId='$ProId'", 'IsCombination');
			$html='<div class="rows" id="overseas_rows"'.(((int)$c['manage']['config']['Overseas']==0 || $oversea_len==1 || $IsCombination==0)?' style="display:none;"':'').'><label>'.manage::language('{/shipping.area.ships_from/}').'</label><span class="input">';
			foreach($shipping_overseas_row as $k=>$v){
				$Ovid='Ov:'.$v['OvId'];
				$cart_attr_ary['Overseas'][$Ovid]=$v['Name'.$c['manage']['web_lang']];
				$data_ary=$data_oth_ary=array(
					'AttrId'	=>	'Overseas',
					'Column'	=>	'Overseas',
					'Num'		=>	$Ovid,
					'Name'		=>	$v['Name'.$c['manage']['web_lang']],
					'Cart'		=>	1,
					'Color'		=>	0
				);
				$data=str::json_data($data_ary);
				$data_oth=str::json_data($data_oth_ary);
				$checked=$check_on='';
				if($oversea_len==1 || (@in_array($v['OvId'], $selected_ary['Overseas']))){
					$checked=' checked';
					$check_on=' current';
					$value=implode(',', (array)$combinatin_ary['||'][$v['OvId']]);
					$callback.="products_obj.products_edit_attr_init('{$data_oth}', 'Attr_{$Ovid}', '{$value}', 0);";
				}
				$_html="<span class='choice_btn{$check_on}'>{$data_ary['Name']}<input type='checkbox' name='Attr[]' value='{$Ovid}' id='Attr_{$Ovid}' class='form_check' data='{$data}'{$checked} /></span>";
				$html.=$_html;
			}
			$html.='</span></div>';
			$Content.=$html;
		}
		//海外仓 End
		ly200::e_json(array(($html_item?$html_item:($html_attr?$html_attr:$Content)), str::json_data($cart_attr_ary), $ParentId, $AttrName, $callback), 1);
	}
	
	public static function products_get_color(){	//产品颜色图片
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$result='';
		$ProId=(int)$g_ProId;
		$AttrId=(int)$g_AttrId;
		$ColorId=(int)$g_ColorId;
		$Name=$g_Name;
		$color_row=str::str_code(db::get_one('products_color', "ProId='$ProId' and VId='{$ColorId}'"));
		if($AttrId){
			if(db::get_row_count('products_attribute_value', "VId='$ColorId'")){
				$result.='<span class="multi_img upload_file_multi ColorDetail" id="ColorDetail_'.$ColorId.'">';
				for($i=0; $i<10; ++$i){
					$pic=$color_row['PicPath_'.$i];
					$isFile=is_file($c['root_path'].$pic);
					$result.='<dl class="img" num="'.$i.'">';
					$result.=	'<dt class="upload_box preview_pic">';
					$result.=		'<input type="button" id="ColorPath_'.$ColorId.'_'.$i.'" class="btn_ok upload_btn" name="ColorPath['.$ColorId.']" value="'.manage::language('{/global.upload_pic/}').'" tips="'.sprintf(manage::language('{/notes.pic_size_tips/}'), '500*500').'" style="'.($isFile?'display:none;':'').'" />';
					$result.=		'<input type="hidden" name="ColorPath['.$ColorId.'][]" value="'.$pic.'" save="'.($isFile?1:0).'" />';
									if($isFile){
										$result.='<a href="javascript:;"><img src="'.ly200::get_size_img($pic, '240x240').'"><em></em></a><a href="'.$pic.'" class="zoom" target="_blank"></a>';
									}
					$result.=	'</dt>';
					$result.=	'<dd class="pic_btn">
									<a href="javascript:;" label="'.manage::language('{/global.edit/}').'" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
									<a href="javascript:;" label="'.manage::language('{/global.del/}').'" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
								</dd>';
					$result.='</dl>';
				}
				$result.='</span>';
				$result.='<div class="tips">'.sprintf(manage::language('{/notes.pic_size_tips/}'), '500*500').'</div>';
			}
		}
		ly200::e_json(array($result), 1);
	}
	
	public static function products_img_del(){	//删除单个产品图片
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$Model=$g_Model;
		$PicPath=$g_Path;
		$Index=(int)$g_Index;
		$resize_ary=$c['manage']['resize_ary'][$Model];	//products
		if(is_file($c['root_path'].$PicPath)){
			foreach($resize_ary as $v){
				$ext_name=file::get_ext_name($PicPath);
				file::del_file($PicPath.".{$v}.{$ext_name}");
			}
			file::del_file($PicPath);
		}
		//参数纯粹作记录
		$g_ProId=(int)$g_ProId;
		$pro_row=db::get_one('products', "ProId='{$g_ProId}'", 'Prefix, Number');
		$_GET['Number']=$pro_row['Prefix'].$pro_row['Number'];
		manage::operation_log('删除产品图片');
		ly200::e_json(array($Index), 1);
	}
	
	public static function products_unit_del(){	//删除单个产品单位
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$unit_list=str::json_data(htmlspecialchars_decode(db::get_value('config', 'GroupId="products" and Variable="Unit"', 'Value')), 'decode');
		if($unit_list[$p_Key]){
			unset($unit_list[$p_Key]);
			$json_unit=addslashes(str::json_data(str::str_code($unit_list, 'stripslashes')));
			manage::config_operaction(array('Unit'=>$json_unit), 'products');
		}
		$item=db::get_value('config', 'GroupId="products_show" and Variable="item"', 'Value');
		if($item==$p_Unit){ //如果删除单位的名称 跟 产品自定义单位的名称 一致
			manage::config_operaction(array('item'=>''), 'products_show');
		}
		ly200::e_json('', 1);
	}
	
	public static function products_edit_category(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CateId=(int)$p_CateId;
		$result='';
		if((float)db::get_value('products', "ProId='{$p_ProId}'", 'CateId')!=$p_CateId){
			db::update('products', "ProId='{$p_ProId}'", array('CateId'=>$p_CateId));
			manage::operation_log('产品修改分类');
			
			//返回分类资料
			$cate_ary=str::str_code(db::get_all('products_category', '1', '*'));
			$category_ary=array();
			foreach((array)$cate_ary as $v) $category_ary[$v['CateId']]=$v;
			$UId=$category_ary[$p_CateId]['UId'];
			if($UId){
				$key_ary=@explode(',',$UId);
				array_shift($key_ary);
				array_pop($key_ary);
				foreach((array)$key_ary as $k2=>$v2){
					$result.=$category_ary[$v2]['Category'.$c['manage']['web_lang']].'->';
				}
			}
			$result.=$category_ary[$p_CateId]['Category'.$c['manage']['web_lang']];
			unset($cate_ary, $category_ary);
		}
		ly200::e_json($result, 1);
	}
	
	public static function products_edit_price(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Price=(float)$p_Price;
		if((float)db::get_value('products', "ProId='{$p_ProId}'", $p_Type)!=$p_Price){
			db::update('products', "ProId='{$p_ProId}'", array($p_Type=>$p_Price));
			manage::operation_log('产品修改价格');
		}
		ly200::e_json('', 1);
	}
	
	public static function products_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('products', "ProId='{$p_ProId}'", array('MyOrder'=>$p_Number));
		manage::operation_log('产品修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function products_copy(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$ProId=(int)$g_ProId;
		$data=$seo_data=$desc_data=array();
		$row=db::get_one('products', "ProId='$ProId'");
		foreach($row as $k=>$v){
			$data[$k]=addslashes($v);
		}
		unset($data['ProId'], $data['Prefix'], $data['Number']);
		$seo_row=db::get_one('products_seo', "ProId='$ProId'");
		foreach($seo_row as $k=>$v){
			$seo_data[$k]=addslashes($v);
		}
		unset($seo_data['SId']);
		$desc_row=db::get_one('products_description', "ProId='$ProId'");
		foreach($desc_row as $k=>$v){
			$desc_data[$k]=addslashes($v);
		}
		unset($desc_data['DId']);
		$resize_ary=$c['manage']['resize_ary']['products'];
		$temp_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($temp_dir);
		for($i=0; $i<10; ++$i){
			$PicPath=$row["PicPath_$i"];
			if(is_file($c['root_path'].$PicPath)){
				$ext_name=file::get_ext_name($PicPath);
				$data["PicPath_$i"]=$temp=$temp_dir.str::rand_code().'.'.$ext_name;
				foreach($resize_ary as $v){
					$RePicPath=$PicPath.".{$v}.{$ext_name}";
					@copy($c['root_path'].$RePicPath, $c['root_path'].ltrim($temp.".{$v}.{$ext_name}", '/'));
				}
				@copy($c['root_path'].$PicPath, $c['root_path'].ltrim($temp, '/'));
			}
		}
		db::insert('products', $data);
		$proid=db::get_insert_id();
		$seo_data['ProId']=$desc_data['ProId']=$proid;
		db::insert('products_seo', $seo_data);
		db::insert('products_description', $desc_data);
		$insert_attr=$insert_combination='';
		$attr_row=db::get_all('products_selected_attribute', "ProId='$ProId'");
		foreach($attr_row as $k=>$v){
			$insert_attr.=($k?',':'')."('{$v['AttrId']}', '{$proid}', '{$v['VId']}', '{$v['Value']}', '{$v['IsUsed']}')";
		}
		$combination_row=db::get_all('products_selected_attribute_combination', "ProId='$ProId'");
		foreach($combination_row as $k=>$v){
			$insert_combination.=($k?',':'')."('{$proid}', '{$v['Combination']}', '{$v['OvId']}', '{$v['SKU']}', '{$v['IsIncrease']}', '{$v['Price']}', '{$v['Stock']}', '{$v['Weight']}')";
		}
		$insert_attr && db::query('insert into products_selected_attribute (AttrId, ProId, VId, Value, IsUsed) values'.$insert_attr);
		$insert_combination && db::query('insert into products_selected_attribute_combination (ProId, Combination, OvId, SKU, IsIncrease, Price, Stock, Weight) values'.$insert_combination);
		manage::operation_log('复制产品');
		ly200::e_json($proid, 1);
	}
	
	public static function products_sold_bat(){
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_proid && ly200::e_json('');
		$g_sold=(int)$g_sold;
		$bat_where="ProId in(".str_replace('-',',',$g_group_proid).")";
		db::update('products', $bat_where, array('SoldOut'=>$g_sold));
		manage::operation_log($g_sold?'批量产品下架':'批量产品上架');
		ly200::e_json('', 1);
	}
	
	public static function products_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$ProId=(int)$g_ProId;
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=str::str_code(db::get_one('products', "ProId='$ProId'", 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9'));
		for($i=0; $i<10; $i++){
			$PicPath=$row["PicPath_$i"];
			if(is_file($c['root_path'].$PicPath)){
				foreach($resize_ary as $v){
					$ext_name=file::get_ext_name($PicPath);
					file::del_file($PicPath.".{$v}.{$ext_name}");
				}
				file::del_file($PicPath);
			}
		}
		//删除产品颜色图片
		$row=str::str_code(db::get_all('products_color', "ProId='$ProId'", 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9'));
		foreach($row as $v){
			for($i=0; $i<10; $i++){
				$PicPath=$v["PicPath_$i"];
				if(is_file($c['root_path'].$PicPath)){
					foreach($resize_ary as $v2){
						$ext_name=file::get_ext_name($PicPath);
						file::del_file($PicPath.".{$v2}.{$ext_name}");
					}
					file::del_file($PicPath);
				}
			}
		}
		db::delete('products', "ProId='$ProId'");
		db::delete('products_seo', "ProId='$ProId'");
		db::delete('products_description', "ProId='$ProId'");
		db::delete('products_selected_attribute', "ProId='$ProId'");
		db::delete('products_selected_attribute_combination', "ProId='$ProId'");
		db::delete('products_color', "ProId='$ProId'");
		db::delete('user_favorite', "ProId='$ProId'");
		manage::operation_log('删除产品');
		ly200::e_json('', 1);
	}
	
	public static function products_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_proid && ly200::e_json('');
		$del_where="ProId in(".str_replace('-',',',$g_group_proid).")";
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=str::str_code(db::get_all('products', $del_where, 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9'));
		foreach($row as $v){
			for($i=0; $i<10; $i++){
				$PicPath=$v["PicPath_$i"];
				if(is_file($c['root_path'].$PicPath)){
					foreach($resize_ary as $v2){
						$ext_name=file::get_ext_name($PicPath);
						file::del_file($PicPath.".{$v2}.{$ext_name}");
					}
					file::del_file($PicPath);
				}
			}
		}
		//删除产品颜色图片
		$row=str::str_code(db::get_all('products_color', $del_where, 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9'));
		foreach($row as $v){
			for($i=0; $i<10; $i++){
				$PicPath=$v["PicPath_$i"];
				if(is_file($c['root_path'].$PicPath)){
					foreach($resize_ary as $v2){
						$ext_name=file::get_ext_name($PicPath);
						file::del_file($PicPath.".{$v2}.{$ext_name}");
					}
					file::del_file($PicPath);
				}
			}
		}
		db::delete('products', $del_where);
		db::delete('products_seo', $del_where);
		db::delete('products_description', $del_where);
		db::delete('products_selected_attribute', $del_where);
		db::delete('products_selected_attribute_combination', $del_where);
		db::delete('products_color', $del_where);
		db::delete('user_favorite', $del_where);
		manage::operation_log('批量删除产品');
		ly200::e_json('', 1);
	}
	
	public static function products_custom_column(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Custom=addslashes(str::json_data(str::str_code($p_Custom, 'stripslashes')));
		$data=array(
			'Products'	=>	$p_Custom
		);
		manage::config_operaction($data, 'custom_column');
		manage::operation_log('产品自定义列');
		ly200::e_json('', 1);
	}
	
	public static function products_new_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($p_ProId){
			$p_ProId=(array)$p_ProId;
			foreach((array)$p_ProId as $k=>$v){
				$ProId=(int)$v;
				if(!$ProId) continue;
				
				$SoldOutTime=@explode('/', $p_SoldOutTime[$k]);
				$SStartTime=@strtotime($SoldOutTime[0]);
				$SEndTime=@strtotime($SoldOutTime[1]);
				$data=array(
							'Price_0'		=>	$p_Price_0[$k],
							'Price_1'		=>	$p_Price_1[$k],
							'PurchasePrice'	=>	$p_PurchasePrice[$k],
							'SoldOut'		=>	(int)$p_SoldOut[$ProId],
							'IsSoldOut'		=>	(int)$p_IsSoldOut[$ProId],
							'SStartTime'	=>	$SStartTime,
							'SEndTime'		=>	$SEndTime,
							'IsIndex'		=>	(int)$p_IsIndex[$ProId],
							'IsNew'			=>	(int)$p_IsNew[$ProId],
							'IsHot'			=>	(int)$p_IsHot[$ProId],
							'IsBestDeals'	=>	(int)$p_IsBestDeals[$ProId],
							'IsFreeShipping'=>	(int)$p_IsFreeShipping[$ProId],
							'MyOrder'		=>	(int)$p_MyOrder[$k]
						);
				db::update('products', "ProId='$ProId'", $data);
			}
		}else{
			$p_CateId=(int)$p_CateId;
			$p_Type=(int)$p_Type;
			$where=1;
			if($p_CateId){
				$cate_row=db::get_one('products_category', "CateId='$p_CateId'");
				$where.=' and '.category::get_search_where_by_CateId($p_CateId, 'products_category');
			}
			if($p_Type){	//百分比
				$p_Rate=(float)$p_Rate;
				$p_Rate=1+($p_Rate/100);
				db::query("update products set Price_1=Price_1*$p_Rate where $where");
			}else{	//价格
				$p_Price=(float)$p_Price;
				db::query("update products set Price_1=Price_1+$p_Price where $where");
			}
		}
		ly200::e_json('批量修改成功<br />', 1);
	}
	
	public static function products_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Model=(int)$p_Model;//0:加 1:减 2:乘 3:除
		$p_Price_0=(float)$p_Price_0;//市场价
		$p_Price_1=(float)$p_Price_1;//会员价
		$p_Price_2=(float)$p_Price_2;//进货价
		$p_Price_3=(float)$p_Price_3;//批发价
		$p_Price_4=(float)$p_Price_4;//促销价
		
		$where='1';
		$p_CateId && $where.=" and CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($p_CateId)."%') or CateId='{$p_CateId}'";
		$products_row=str::str_code(db::get_all('products', $where, '*', $c['my_order'].'ProId desc'));
		
		if($products_row){
			foreach($products_row as $k=>$v){
				$data=$wholesale_ary=array();
				$WholesalePrice=str::json_data(htmlspecialchars_decode($v['Wholesale']), 'decode');
				switch($p_Model){
					case 0:
						$p_Price_0 && $data['Price_0']=$v['Price_0']+$p_Price_0;
						$p_Price_1 && $data['Price_1']=$v['Price_1']+$p_Price_1;
						$p_Price_2 && $data['PurchasePrice']=$v['PurchasePrice']+$p_Price_2;
						$p_Price_4 && $data['PromotionPrice']=$v['PromotionPrice']+$p_Price_4;
						if($p_Price_3 && $WholesalePrice){
							foreach((array)$WholesalePrice as $kk=>$vv){
								$wholesale_ary[$kk]=(float)$vv+$p_Price_3;
							}
						}
						break;
					case 1:
						$p_Price_0 && $data['Price_0']=$v['Price_0']-$p_Price_0;
						$p_Price_1 && $data['Price_1']=$v['Price_1']-$p_Price_1;
						$p_Price_2 && $data['PurchasePrice']=$v['PurchasePrice']-$p_Price_2;
						$p_Price_4 && $data['PromotionPrice']=$v['PromotionPrice']-$p_Price_4;
						if($p_Price_3 && $WholesalePrice){
							foreach((array)$WholesalePrice as $kk=>$vv){
								$wholesale_ary[$kk]=(float)$vv-$p_Price_3;
							}
						}
						break;
					case 2:
						$p_Price_0 && $data['Price_0']=$v['Price_0']*$p_Price_0;
						$p_Price_1 && $data['Price_1']=$v['Price_1']*$p_Price_1;
						$p_Price_2 && $data['PurchasePrice']=$v['PurchasePrice']*$p_Price_2;
						$p_Price_4 && $data['PromotionPrice']=$v['PromotionPrice']*$p_Price_4;
						if($p_Price_3 && $WholesalePrice){
							foreach((array)$WholesalePrice as $kk=>$vv){
								$wholesale_ary[$kk]=(float)$vv*$p_Price_3;
							}
						}
						break;
					case 3:
						$p_Price_0 && $data['Price_0']=$v['Price_0']/$p_Price_0;
						$p_Price_1 && $data['Price_1']=$v['Price_1']/$p_Price_1;
						$p_Price_2 && $data['PurchasePrice']=$v['PurchasePrice']/$p_Price_2;
						$p_Price_4 && $data['PromotionPrice']=$v['PromotionPrice']/$p_Price_4;
						if($p_Price_3 && $WholesalePrice){
							foreach((array)$WholesalePrice as $kk=>$vv){
								$wholesale_ary[$kk]=(float)$vv/$p_Price_3;
							}
						}
						break;
				}
				if($wholesale_ary) $data['Wholesale']=addslashes(str::json_data(str::str_code($wholesale_ary, 'stripslashes')));
				$data && db::update('products', "ProId='{$v['ProId']}'", $data);
			}
		}else{
			ly200::e_json('', 0);
		}
		unset($c, $products_row);
		ly200::e_json('批量修改价格成功<br />', 1);
	}
	
	public static function products_batch_sold_out_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_SoldOut=(int)$p_SoldOut;//0:上架 1:下架
		
		$where='1';
		$p_CateId && $where.=" and CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($p_CateId)."%') or CateId='{$p_CateId}'";
		$products_row=str::str_code(db::get_all('products', $where, '*', $c['my_order'].'ProId desc'));
		
		if($products_row){
			foreach($products_row as $k=>$v){
				db::update('products', "ProId='{$v['ProId']}'", array('SoldOut'=>$p_SoldOut));
			}
		}else{
			ly200::e_json('', 0);
		}
		unset($c, $products_row);
		ly200::e_json('批量修改'.($p_SoldOut?'下':'上').'架成功<br />', 1);
	}
	
	public static function products_batch_move_category_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CateId=(int)$p_CateId;//来源分类
		$p_CateIdTo=(int)$p_CateIdTo;//目标分类
		if(!$p_CateId || !$p_CateIdTo) ly200::e_json('', 0);
		if($p_CateId==$p_CateIdTo) ly200::e_json(manage::language('{/products.batch.equal_tips/}'), 0);
		
		$where="1 and CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($p_CateId)."%') or CateId='{$p_CateId}'";
		$prod_count=db::get_row_count('products', $where);
		
		if($prod_count){
			db::update('products', $where, array('CateId'=>$p_CateIdTo));
		}else{
			ly200::e_json('', 0);
		}
		unset($c, $prod_count);
		ly200::e_json('批量移动产品成功<br />', 1);
	}
	
	public static function products_explode(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CateId=(int)$p_CateId;
		$p_Number=(int)$p_Number;
		if(!$p_Number) unset($_SESSION['ProZip']);
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		//(A ~ EZ)
		$arr=range('A', 'Z');
		$ary=$arr;
		for($i=0; $i<10; ++$i){
			$num=$arr[$i];
			foreach($arr as $v){
				$ary[]=$num.$v;
			}
		}
		//myorder
		$myOrderAry=array(
			0	=>	'',
			1	=>	'p.Sales desc,',
			2	=>	'p.Sales asc,'
		);
		//语言版本
		$language=$p_Language;
		if(!$language || !in_array($language, $c['manage']['web_lang_list'])){//找不到相对应的语言，默认为可用语言里面的第一个
			$language=$c['manage']['web_lang_list'][0];
		}
		//初始化
		//$cart_attr_count=0;
		$cart_attr_ary=array();
		$allbusiness_ary=$attribute_ary=$attribute_top_ary=$all_value_ary=$vid_data_ary=$selected_ary=$combination_ary=array();
		//所有供应商数据
		$allbusiness_row=str::str_code(db::get_all('business', '1', "BId, Name", 'BId asc'));
		foreach((array)$allbusiness_row as $k=>$v) $allbusiness_ary[$v['BId']]=$v['Name'];
		//产品分类
		$CateId=$p_CateId;
		$uid=category::get_UId_by_CateId($CateId);
		$uid && $uid!='0,' && $CateId=category::get_top_CateId_by_UId($uid);
		$products_category_row=str::str_code(db::get_one('products_category', "CateId='{$CateId}'"));
		$AttrId=$products_category_row['AttrId'];
		
		//准备工作
		$page_count=$p_PageNum;//每次分开导出的数量
		$where='1';
		$p_CateId && $where.=" and (p.CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($p_CateId)."%') or p.CateId='{$p_CateId}')";
		$row_count=db::get_row_count('products p', $where, 'ProId');
		$total_pages=ceil($row_count/$page_count);
		
		//产品属性
		$cart_attr_ary=array();//记录规格属性的总数
		$attribute_row=str::str_code(db::get_all('products_attribute', "ParentId='{$AttrId}'", "AttrId, Type, Name_{$language}, ParentId, CartAttr, ColorAttr"));
		$_attribute_value_where='0';
		foreach($attribute_row as $v){
			$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name_{$language}"], 2=>$v['CartAttr']);
			$_attribute_value_where.=",{$v['AttrId']}";
		}
		unset($attribute_row);
		$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in($_attribute_value_where)", '*', $c['my_order'].'VId asc')); //所有属性选项
		foreach($value_row as $v){
			$all_value_ary[$v['AttrId']][$v['VId']]=$v;
			$vid_data_ary[$v['VId']]=$v;
		}
		unset($value_row);
		$selected_row=db::get_all('products_selected_attribute', "IsUsed=1 and AttrId in($_attribute_value_where)", 'SeleteId, ProId, AttrId, VId, Value, OvId', 'SeleteId asc');
		foreach($selected_row as $v){
			if($v['VId']==0){
				if($v['AttrId']==0){//记录勾选属性ID 发货地
					$selected_ary[$v['ProId']]['Overseas'][]=$v['OvId'];
				}else{//文本框内容
					$selected_ary[$v['ProId']]['Value'][$v['AttrId']]=$v['Value'];
				}
			}else{
				$selected_ary[$v['ProId']]['Id'][$v['AttrId']][]=$v['VId']; //记录勾选属性ID
				if($attribute_ary[$v['AttrId']][2]==1 && !in_array($v['AttrId'], (array)$cart_attr_ary[$v['ProId']])){ $cart_attr_ary[$v['ProId']][]=$v['AttrId']; }//记录规格属性的总数
			}
		}
		//$cart_attr_count=count($cart_attr_ary);
		unset($selected_row);
		$combinatin_row=db::get_all('products_selected_attribute_combination', "ProId in(select ProId from products p where {$where})", '*', 'CId asc');
		foreach($combinatin_row as $v){
			$combinatin_ary[$v['ProId']][$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
		}
		unset($combinatin_row);
		
		//海外仓
		$shipping_overseas_row=db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc');
		$oversea_len=count($shipping_overseas_row);
		$oversea_name_ary=array();
		foreach($shipping_overseas_row as $v){
			$oversea_name_ary[$v['OvId']]=$v["Name_{$language}"];
		}
				
		$zipAry=array();//储存需要压缩的文件
		$save_dir='/tmp/';//临时储存目录
		file::mk_dir($save_dir);
		if($p_Number<$total_pages){
			$num=0;
			$page=$page_count*$p_Number;
			$products_row=str::str_code(db::get_limit('products p left join products_seo s on p.ProId=s.ProId left join products_description d on p.ProId=d.ProId', $where, 'p.*, s.*, d.*', $myOrderAry[$g_MyOrder].'if(p.MyOrder>0, p.MyOrder, 100000) asc, p.ProId desc', $page, $page_count));
			$objPHPExcel=new PHPExcel();
			//Set properties 
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			$objPHPExcel->setActiveSheetIndex(0);
			//'产品名称', '产品分类', '产品多分类', '产品编号', 'SKU', '供货商', '市场价', '会员价', '进货价', '批发价', '开启促销', '促销价格', '促销折扣', '促销时间', '自定义地址', '图片', '属性组合', '属性', '属性关联项', '颜色关联项', '重量', '体积', '体积重', '起订量', '最大购买量', '产品销量', '库存', '警告库存', '脱销状态', '下架', '定时上架', '定时上架时间', '免运费', '收藏数', '新品', '热卖', '畅销', '首页显示', '开启默认评论', '默认评论平均分', '默认评论人数', '标题', '关键字', '描述', '简单介绍', '详细介绍'
			$attr_column=array($c['manage']['lang_pack']['orders']['export']['proname'], $c['manage']['lang_pack']['products']['classify'], $c['manage']['lang_pack']['products']['products']['expand'], $c['manage']['lang_pack']['products']['products']['number'], $c['manage']['lang_pack']['products']['products']['sku'], $c['manage']['lang_pack']['products']['products']['business'], $c['manage']['lang_pack']['products']['products']['price_ary'][0], $c['manage']['lang_pack']['products']['products']['price_ary'][1], $c['manage']['lang_pack']['products']['products']['price_ary'][2], $c['manage']['lang_pack']['products']['products']['wholesale_price'], $c['manage']['lang_pack']['products']['products']['promotion'], $c['manage']['lang_pack']['products']['products']['promotion_price'], $c['manage']['lang_pack']['products']['products']['discount'], $c['manage']['lang_pack']['products']['products']['promotion'].$c['manage']['lang_pack']['global']['time'], $c['manage']['lang_pack']['page']['page']['custom_url'], $c['manage']['lang_pack']['products']['picture'], $c['manage']['lang_pack']['products']['products']['combination'], $c['manage']['lang_pack']['products']['attribute'], $c['manage']['lang_pack']['products']['attribute'].$c['manage']['lang_pack']['products']['products']['relation'], $c['manage']['lang_pack']['products']['color'].$c['manage']['lang_pack']['products']['products']['relation'], $c['manage']['lang_pack']['products']['products']['weight'], $c['manage']['lang_pack']['products']['products']['cubage'], $c['manage']['lang_pack']['products']['products']['volume_weight'], $c['manage']['lang_pack']['products']['products']['moq'], $c['manage']['lang_pack']['products']['products']['maxoq'], $c['manage']['lang_pack']['products']['products']['product_sales'], $c['manage']['lang_pack']['products']['products']['stock'], $c['manage']['lang_pack']['products']['products']['warn_stock'], $c['manage']['lang_pack']['products']['products']['stock_out_status'], $c['manage']['lang_pack']['products']['products']['sold_out'], $c['manage']['lang_pack']['products']['products']['sold_in_time'], $c['manage']['lang_pack']['products']['products']['sold_in_time'].$c['manage']['lang_pack']['global']['time'], $c['manage']['lang_pack']['products']['products']['free_shipping'], $c['manage']['lang_pack']['products']['products']['favorite'], $c['manage']['lang_pack']['products']['products']['is_new'], $c['manage']['lang_pack']['products']['products']['is_hot'], $c['manage']['lang_pack']['products']['products']['is_best_deals'], $c['manage']['lang_pack']['products']['products']['is_index'], $c['manage']['lang_pack']['products']['products']['default_review'], $c['manage']['lang_pack']['products']['products']['default_review_rating'], $c['manage']['lang_pack']['products']['products']['default_review_count'], $c['manage']['lang_pack']['products']['products']['seo_title'], $c['manage']['lang_pack']['news']['news']['seo_keyword'], $c['manage']['lang_pack']['products']['products']['seo_brief'], $c['manage']['lang_pack']['products']['products']['briefdescription'], $c['manage']['lang_pack']['products']['products']['description']);
			$m=2;
			$lang_ary=array('SeoTitle_', 'SeoKeyword_', 'SeoDescription_', 'BriefDescription_', 'Description_');
			foreach($products_row as $v){
				$num=$mMax_1=0;
				$mMax=1;
				$ProId=$v['ProId'];
				$ExtCateId=$v['ExtCateId'];
				$ExtCateId && $ExtCateId=substr($v['ExtCateId'], 1, -1);
				$Wholesale=str_replace(array('{', '}', '"'), '', htmlspecialchars_decode($v['Wholesale']));
				$Wholesale=='[]' && $Wholesale='';
				$Cubage=str_replace(',', '*', $v['Cubage']);
				$data=array($v['Name_'.$language], $v['CateId'], $ExtCateId, $v['Prefix'].$v['Number'], $v['SKU'], $allbusiness_ary[$v['Business']], (float)$v['Price_0'], (float)$v['Price_1'], (float)$v['PurchasePrice'], $Wholesale, (int)$v['IsPromotion'], (float)$v['PromotionPrice'], (int)$v['PromotionDiscount'], date('Y/m/d H:i:s', $v['StartTime']).' - '.date('Y/m/d H:i:s', $v['EndTime']), $v['PageUrl'], $v['PicPath_0'], (int)$v['IsCombination'], '', '', '', (float)$v['Weight'], $Cubage, (int)$v['IsVolumeWeight'], (int)$v['MOQ'], (int)$v['MaxOQ'], (int)$v['Sales'], (int)$v['Stock'], (int)$v['WarnStock'], (int)$v['StockOut'], (int)$v['SoldOut'], (int)$v['IsSoldOut'], date('Y/m/d H:i:s', $v['SStartTime']).' - '.date('Y/m/d H:i:s', $v['SEndTime']), (int)$v['IsFreeShipping'], (int)$v['FavoriteCount'], (int)$v['IsNew'], (int)$v['IsHot'], (int)$v['IsBestDeals'], (int)$v['IsIndex'], (int)$v['IsDefaultReview'], (float)$v['DefaultReviewRating'], (int)$v['DefaultReviewTotalRating']);
				//设置单元格的值
				foreach($data as $k2=>$v2){
					$objPHPExcel->getActiveSheet()->setCellValue($ary[$k2].$m, $v2);
					++$num;
				}
				foreach($lang_ary as $k2=>$v2){
					$objPHPExcel->getActiveSheet()->setCellValue($ary[$num+$k2].$m, htmlspecialchars_decode($v[$v2.$language]));
				}
				//图片
				for($i=1; $i<10; ++$i){
					if(is_file($c['root_path'].$v["PicPath_{$i}"])){
						$objPHPExcel->getActiveSheet()->setCellValue($ary[15].($m+$i), $v["PicPath_{$i}"]);
						++$mMax;
					}
				}
				//属性
				if($selected_ary[$ProId]){
					$mMax_1=0;
					foreach((array)$selected_ary[$ProId]['Id'] as $k2=>$v2){ //列表选择
						if(!$v2) continue;
						$value='';
						$_arr=array();
						foreach($v2 as $v3){
							$_arr[]=$vid_data_ary[$v3]['Value_'.$language];
						}
						$value=implode(',', $_arr);
						$objPHPExcel->getActiveSheet()->setCellValue($ary[17].($m+$mMax_1), $attribute_ary[$k2][1].'['.$value.']');
						++$mMax_1;
					}
					foreach((array)$selected_ary[$ProId]['Value'] as $k2=>$v2){ //文本框
						if(!$v2) continue;
						$objPHPExcel->getActiveSheet()->setCellValue($ary[17].($m+$mMax_1), $attribute_ary[$k2][1].'['.$v2.']');
						++$mMax_1;
					}
					if((int)$c['manage']['config']['Overseas']==1 && count($selected_ary[$ProId]['Overseas'])>1){ //发货地
						$value='';
						foreach((array)$selected_ary[$ProId]['Overseas'] as $k2=>$v2){
							if(!$v2) continue;
							$value.=($k2?',':'').$oversea_name_ary[$v2];
						}
						$objPHPExcel->getActiveSheet()->setCellValue($ary[17].($m+$mMax_1), 'Oversea['.$value.']');
						++$mMax_1;
					}
					$mMax_1>$mMax && $mMax=$mMax_1;
				}
				//属性关联项
				if($combinatin_ary[$ProId]){
					$mMax_1=0;
					foreach((array)$combinatin_ary[$ProId] as $k2=>$v2){
						$_arr=array();
						$key=explode('|', substr($k2, 1, -1));
						if(((int)$v['IsCombination']==0 && count($key)==1) || ((int)$v['IsCombination']==1 && count($key)==count($cart_attr_ary[$ProId]))){
							foreach($v2 as $ovid=>$v3){
								$value=$com_data='';
								foreach($key as $v4){
									$_attrid=$vid_data_ary[$v4]['AttrId'];
									$value.=$attribute_ary[$_attrid][1].'['.$vid_data_ary[$v4]['Value_'.$language].']';
								}
								if((int)$c['manage']['config']['Overseas']==1 && count($selected_ary[$ProId]['Overseas'])>1){ //开启海外仓
									$value.='Oversea['.$oversea_name_ary[$ovid].']';
								}
								$com_data=implode(',', $v3);
								$objPHPExcel->getActiveSheet()->setCellValue($ary[18].($m+$mMax_1), $value.'='.$com_data);
								++$mMax_1;
							}
						}
					}
					$mMax_1>$mMax && $mMax=$mMax_1;
				}
				//颜色关联项
				$row=str::str_code(db::get_all('products_color', "ProId='$ProId'"));
				if($row){
					$mMax_1=0;
					foreach((array)$row as $v2){
						$value='';
						$PicPathAry=array();
						for($i=0; $i<10; ++$i){
							if(is_file($c['root_path'].$v2["PicPath_$i"])){
								$PicPathAry[]=$v2["PicPath_$i"];
							}
						}
						if(count($PicPathAry)>0){
							$_attrid=$vid_data_ary[$v2['VId']]['AttrId'];
							$value=$attribute_ary[$_attrid][1].'['.$vid_data_ary[$v2['VId']]['Value_'.$language].']';
							$objPHPExcel->getActiveSheet()->setCellValue($ary[19].($m+$mMax_1), $value.'='.implode(',', $PicPathAry));
							++$mMax_1;
						}
					}
					$mMax_1>$mMax && $mMax=$mMax_1;
				}
				$m+=$mMax;
			}
			foreach($attr_column as $k=>$v){
				//设置单元格的值(第一二行标题)
				$objPHPExcel->getActiveSheet()->setCellValue($ary[$k].'1', $v);
				$objPHPExcel->getActiveSheet()->getStyle($ary[$k].'1')->getAlignment()->setWrapText(true);//自动换行
				$objPHPExcel->getActiveSheet()->getStyle($ary[$k].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
				//设置列的宽度
				$objPHPExcel->getActiveSheet()->getColumnDimension($ary[$k])->setWidth($k>16 && $k<20?30:13);
			}
			
			//设置行的高度
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
			$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);//默认行高
			
			$objPHPExcel->getActiveSheet()->setTitle('产品导出');//Rename sheet
			$objPHPExcel->setActiveSheetIndex(0);//指针返回第一个工作表
			$ExcelName='products_'.str::rand_code();
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."{$save_dir}{$ExcelName}.xls");
			$_SESSION['ProZip'][]="{$save_dir}{$ExcelName}.xls";
			unset($objPHPExcel, $objWriter, $products_row);
			ly200::e_json(array(($p_Number+1), "{$c['manage']['lang_pack']['global']['export']} {$save_dir}{$ExcelName}.xls<br />"), 2);
		}else{
			if(count($_SESSION['ProZip'])){
				ly200::e_json('', 1);
			}else{
				ly200::e_json('');
			}
		}
	}
	
	public static function products_explode_down(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if($g_Status=='ok' && count($_SESSION['ProZip'])){	//开始打包
			$zip=new ZipArchive();
			$zipname='/tmp/products_'.str::rand_code().'.zip';
			if($zip->open($c['root_path'].$zipname, ZIPARCHIVE::CREATE)===TRUE){
				foreach($_SESSION['ProZip'] as $path){
					if(is_file($c['root_path'].$path)) $zip->addFile($c['root_path'].$path, $path);
				}
				$zip->close();
				file::down_file($zipname);
				file::del_file($zipname);
				foreach($_SESSION['ProZip'] as $path){
					if(is_file($c['root_path'].$path)) file::del_file($path);
				}
			}
		}
		unset($_SESSION['UserZip']);
		exit();
	}
	
	public static function model_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$AttrId=(int)$p_AttrId;
		$data=array();
		if($AttrId){
			manage::operation_log('修改产品属性分类');
		}else{
			db::insert('products_attribute', $data);
			$AttrId=db::get_insert_id();
			manage::operation_log('添加产品属性分类');
		}
		manage::database_language_operation('products_attribute', "AttrId='$AttrId'", array('Name'=>0));
		ly200::e_json('', 1);
	}
	
	public static function model_attribute_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$AttrId=(int)$p_AttrId;
		$p_ParentId=(int)$p_ParentId;
		$p_CartAttr=(int)$p_CartAttr;
		$p_ColorAttr=(int)$p_ColorAttr;
		$p_Type=(int)$p_Type;
		$p_CartAttr && $p_Type=1;
		!$p_CartAttr && $p_ColorAttr=0;
		($p_ColorAttr && db::get_sum('products_attribute', "ParentId='$p_ParentId' and AttrId!=$AttrId", 'ColorAttr')) && ly200::e_json(manage::get_language('products.model.color_attr_tips'));
		if($p_Position=='products' && !$p_ParentId){ //在产品编辑页执行的，没有属性分类，马上创建一个
			!$p_CateId && ly200::e_json(manage::get_language('products.model.no_cate_tips'));//没有属性分类，不能进行创建
			$TopCateId=$p_CateId;
			$category_row=str::str_code(db::get_one('products_category', "CateId='$p_CateId'"));
			if($category_row['UId']!='0,'){
				$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
				$category_row=str::str_code(db::get_one('products_category', "CateId='$TopCateId'"));
			}
			$data=array();
			foreach($c['manage']['config']['Language'] as $k=>$v){
				$data['Name_'.$v]=addslashes(stripslashes($category_row['Category_'.$v]));
			}
			db::insert('products_attribute', $data);
			$p_ParentId=db::get_insert_id();
			manage::operation_log('添加产品属性分类 ID:'.$p_ParentId);
			db::update('products_category', "CateId='$TopCateId'", array('AttrId'=>$p_ParentId));
		}else{ //在产品属性编辑页执行的
			!$p_ParentId && ly200::e_json(manage::get_language('products.model.parent_tips'));
		}
		$data=array(
			'ParentId'	=>	$p_ParentId,
			'CartAttr'	=>	$p_CartAttr,
			'ColorAttr'	=>	$p_ColorAttr,
			'Type'		=>	$p_Type
		);
		if($AttrId){
			db::update('products_attribute', "AttrId='$AttrId'", $data);
			manage::operation_log('修改产品属性');
		}else{
			db::insert('products_attribute', $data);
			$AttrId=db::get_insert_id();
			manage::operation_log('添加产品属性');
		}
		/********************** products_attribute_value Start **********************/
		$insert_ary=$update_ary=$vid_ary=array();
		$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId='{$AttrId}'", '*', $c['my_order'].'VId asc'));
		foreach((array)$value_row as $k=>$v){
			$vid_ary[$k]=$v['VId'];
		}
		unset($value_row);
		$first_ary=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$i=1;
			foreach((array)${'p_Value_'.$v} as $k2=>$v2){
				$v2=str_replace('	', ' ', $v2); //把制表符换成空格（防止导出后自动变成空格）
				if($vid_ary[$k2]){ //已存在
					$update_ary[$vid_ary[$k2]]['Value_'.$v]=$v2;
					!$update_ary[$vid_ary[$k2]]['MyOrder'] && $update_ary[$vid_ary[$k2]]['MyOrder']=$i; //未设置排序，设置一下
					!$update_ary[$vid_ary[$k2]]['AttrId'] && $update_ary[$vid_ary[$k2]]['AttrId']=$AttrId; //未设置AttrId，设置一下
				}else{ //新选项
					$insert_ary[$k2]['Value_'.$v]=$v2;
					!$insert_ary[$k2]['MyOrder'] && $insert_ary[$k2]['MyOrder']=$i; //未设置排序，设置一下
					!$insert_ary[$k2]['AttrId'] && $insert_ary[$k2]['AttrId']=$AttrId; //未设置AttrId，设置一下
				}
				++$i;
			}
		}
		foreach((array)$update_ary as $k=>$v){
			db::update('products_attribute_value', "VId='$k'", $v);
			//manage::operation_log('修改产品属性选项: VId:'.$k);
		}
		foreach((array)$insert_ary as $k=>$v){
			db::insert('products_attribute_value', $v);
			$VId=db::get_insert_id();
			manage::operation_log('添加产品属性选项: VId:'.$VId);
		}
		foreach((array)$vid_ary as $v){
			if(!$update_ary[$v]){ //如果在更新数组里，没有相应的VId数值，就证明数据已经被删掉
				db::delete('products_attribute_value', "VId='{$v}'");
				manage::operation_log('删除产品属性选项: VId:'.$v);
			}
		}
		/********************** products_attribute_value End **********************/
		manage::database_language_operation('products_attribute', "AttrId='$AttrId'", array('Name'=>0));
		if($p_Position=='products'){ //在产品编辑页执行的
			ly200::e_json(array('AttrId'=>$AttrId), 1);
		}else{ //在产品属性编辑页执行的
			ly200::e_json('', 1);
		}
	}
	
	public static function model_attribute_value_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AttrId=(int)$p_AttrId;
		$MaxMyOrder=db::get_value('products_attribute_value', "AttrId='{$p_AttrId}'", 'MyOrder', 'MyOrder desc');
		db::insert('products_attribute_value', array(
				'AttrId'	=>	$p_AttrId,
				'MyOrder'	=>	$MaxMyOrder+1
			)
		);
		$VId=db::get_insert_id();
		manage::database_language_operation('products_attribute_value', "VId='$VId'", array('Value'=>0));
		manage::operation_log('添加产品属性自定义项');
		ly200::e_json(array('AttrId'=>$p_AttrId, 'VId'=>$VId), 1);
	}
	
	public static function model_order(){
		global $c;
		$AttrIdAry=@array_filter(@explode('-', $_GET['group_attrid']));
		foreach($AttrIdAry as $k=>$v){
			db::update('products_attribute', "AttrId='$v'", array('MyOrder'=>$k+1));
		}
		manage::operation_log('产品属性排序');
		ly200::e_json('', 1);
	}
	
	public static function model_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$AttrId=(int)$g_AttrId;
		$row=str::str_code(db::get_one('products_attribute', "AttrId='$AttrId'", 'ParentId'));
		if(!$row['ParentId']){
			$attrid_ary=array();
			$attrid_list='0';
			$attr_row=str::str_code(db::get_all('products_attribute', "ParentId='$AttrId'", 'AttrId'));
			foreach((array)$attr_row as $v){ $attrid_ary[]=$v['AttrId']; }
			$attrid_ary && $attrid_list=implode(',', $attrid_ary);
			!$attrid_list && $attrid_list='0';
			db::delete('products_attribute', "ParentId='$AttrId'");
			db::delete('products_attribute_value', "AttrId in ($attrid_list)");
		}
		db::delete('products_attribute', "AttrId='$AttrId'");
		db::delete('products_attribute_value', "AttrId='$AttrId'");
		db::update('products_category', "AttrId='$AttrId'",array('AttrId'=>0));
		manage::operation_log('删除产品'.($row['ParentId']?'属性':'属性分类'));
		ly200::e_json('', 1);
	}
	
	public static function model_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_attrid && ly200::e_json('');
		$del_where="AttrId in(".str_replace('-', ',', $g_group_attrid).")";
		$row=str::str_code(db::get_all('products_attribute', $del_where));
		foreach($row as $val){
			if(!$val['ParentId']){
				$attrid_ary=array();
				$attr_row=str::str_code(db::get_all('products_attribute', "ParentId='{$val['AttrId']}'", 'AttrId'));
				foreach((array)$attr_row as $v){ $attrid_ary[]=$v['AttrId']; }
				$attrid_list=implode(',', $attrid_ary);
				!$attrid_list && $attrid_list='0';
				db::delete('products_attribute', "ParentId='{$val['AttrId']}'");
				db::delete('products_attribute_value', "AttrId in ($attrid_list)");
			}
		}
		db::delete('products_attribute', $del_where);
		db::update('products_category', $del_where,array('AttrId'=>0));
		manage::operation_log('批量删除产品属性分类');
		ly200::e_json('', 1);
	}
	
	public static function model_category_select(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_ParentId=(int)$p_ParentId;
		$p_AttrId=(int)$p_AttrId;
		$ParentId=db::get_value('products_attribute', "AttrId='$p_AttrId'", 'ParentId');
		echo ly200::form_select(db::get_all('products_attribute','ParentId=0','*','AttrId asc'), 'ParentId', ($ParentId?$ParentId:$p_ParentId), 'Name'.$c['manage']['web_lang'], 'AttrId', $c['manage']['lang_pack']['global']['select_index'], 'notnull');
		exit;
	}
	
	public static function category_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$UnderTheCateId=(int)$p_UnderTheCateId;
		$p_AttrId=(int)$p_AttrId;
		$IsDesc=addslashes(str::json_data(str::str_code($p_IsDesc, 'stripslashes')));
		$PicPath=$p_PicPath;
		$status=0;
		if($UnderTheCateId==0){
			$UId='0,';
			$Dept=1;
		}else{
			$UId=category::get_UId_by_CateId($UnderTheCateId, 'products_category');
			$Dept=substr_count($UId, ',');
			$p_AttrId=0;
		}
		if($CateId){
			$cate_row=db::get_one('products_category', "CateId='$CateId'");
			$status=1;
		}
		$data=array(
			'UId'		=>	$UId,
			'AttrId'	=>	$p_AttrId,
			'PicPath'	=>	$PicPath,
			'Dept'		=>	$Dept,
			'IsIndex'	=>	(int)$p_IsIndex,
			'IsSoldOut'	=>	(int)$p_IsSoldOut,
			'IsDesc'	=>	$IsDesc
		);
		if($CateId){
			db::update('products_category', "CateId='$CateId'", $data);
			manage::operation_log('修改产品分类');
		}else{
			db::insert('products_category', $data);
			$CateId=db::get_insert_id();
			db::insert('products_category_description', array('CateId'=>$CateId));
			manage::operation_log('添加产品分类');
		}
		manage::database_language_operation('products_category', "CateId='$CateId'", array('Category'=>1, 'BriefDescription'=>2, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		$desc_data=array('Description'=>3);
		$desc_data=array_merge($desc_data, array('TabName_0'=>0, 'TabName_1'=>0, 'TabName_2'=>0, 'Tab_0'=>3, 'Tab_1'=>3, 'Tab_2'=>3));
		manage::database_language_operation('products_category_description', "CateId='$CateId'", $desc_data);
		if($status && $cate_row['UId']!=$UId){
			$s_where="UId like '{$cate_row['UId']}{$CateId},%' and CateId!='$CateId'";
			if($cate_row['Dept']==1) $s_where="UId like '0,{$CateId},%'";
			$category_row=db::get_all('products_category', $s_where, 'UId, CateId', 'MyOrder desc, CateId asc');
			foreach($category_row as $v){
				$uid=$cate_row['Dept']==1?$UId.substr($v['UId'], 2):str_replace($cate_row['UId'], $UId, $v['UId']);
				db::update('products_category', "CateId='{$v['CateId']}'", array(
						'UId'	=>	$uid,
						'Dept'	=>	substr_count($uid, ',')
					)
				);
			}
		}
		$UId!='0,' && $CateId=category::get_top_CateId_by_UId($UId);
		$statistic_where.=category::get_search_where_by_CateId($CateId, 'products_category');
		if($status && $cate_row['Dept']!=1){
			$statistic_where.=' or CateId='.category::get_FCateId_by_UId($cate_row['UId']);//父
		}else{
			$statistic_where.=" or CateId='$CateId'";//自己本身
		}
		category::category_subcate_statistic('products_category', $statistic_where);
		ly200::e_json('', 1);
	}
	
	public static function category_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `products_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量产品分类排序');
		ly200::e_json('', 1);
	}
	
	public static function category_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CateId=(int)$g_CateId;
		$cate_row=db::get_one('products_category', "CateId='$CateId'");
		$del_where=category::get_search_where_by_CateId($CateId, 'products_category');
		db::delete('products_category_description', $del_where);
		db::delete('products_category', $del_where);
		manage::operation_log('删除产品分类');
		if($cate_row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($cate_row['UId']);
			$statistic_where=category::get_search_where_by_CateId($CateId, 'products_category');
			category::category_subcate_statistic('products_category', $statistic_where);
		}
		ly200::e_json('', 1);
	}
	
	public static function category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_cateid && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_cateid).")";
		$row=str::str_code(db::get_all('products_category', $del_where));
		foreach($row as $v){
			if(is_file($c['root_path'].$v['PicPath'])) file::del_file($v['PicPath']);
			$sub_del_where=category::get_search_where_by_CateId($v['CateId'], 'products_category');
			db::delete('products_category_description', $sub_del_where);
			db::delete('products_category', $sub_del_where);
		}
		db::delete('products_category_description', $del_where);
		db::delete('products_category', $del_where);
		manage::operation_log('批量删除产品分类');
		foreach($row as $v){
			if($v['UId']!='0,'){
				$CateId=category::get_top_CateId_by_UId($v['UId']);
				$statistic_where=category::get_search_where_by_CateId($CateId, 'products_category');
				category::category_subcate_statistic('products_category', $statistic_where);
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function business_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$BId=(int)$p_BId;
		$CateId=(int)$p_CateId;
		$Name=$p_Name;
		$Address=$p_Address;
		$Remark=$p_Remark;
		$Entity=$p_Entity;
		$Contacts=$p_Contacts;
		$Phone=$p_Phone;
		$Telephone=$p_Telephone;
		$Fax=$p_Fax;
		$QQ=$p_QQ;
		$ImgPath=array();
		$PicPath=array($p_ImgPath, $p_PicPath);
		foreach((array)$PicPath as $k=>$v){
			$ImgPath[$k]=$v;
		}
		!$Name && ly200::e_json(manage::get_language('business.business.name_tips'));
		$data=array(
			'CateId'	=>	$CateId,
			'Name'		=>	$Name,
			'Url'		=>	$p_Url,
			'Address'	=>	$Address,
			'Remark'	=>	$Remark,
			'ImgPath'	=>	$ImgPath[0],
			'PicPath'	=>	$ImgPath[1],
			'Entity'	=>	$Entity,
			'Contacts'	=>	$Contacts,
			'Phone'		=>	$Phone,
			'Telephone'	=>	$Telephone,
			'Fax'		=>	$Fax,
			'QQ'		=>	$QQ,
			'AccTime'	=>	$c['time']
		);
		if($BId){
			db::update('business',"BId='$BId'",$data);
			manage::operation_log('修改供应商');
		}else{
			db::insert('business',$data);
			manage::operation_log('添加供应商');
		}
		ly200::e_json('', 1);
	}
	
	public static function business_my_order(){
		global $c;
		$BIdAry=@array_filter(@explode('-', $_GET['group_id']));
		$MyOrderAry=@array_filter(@explode('-', $_GET['my_order_value']));
		foreach($BIdAry as $k=>$v){
			db::update('business', "BId='$v'", array('MyOrder'=>$MyOrderAry[$k]));
		}
		manage::operation_log('批量供应商排序');
		ly200::e_json('', 1);
	}
	
	public static function business_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$BId=(int)$g_BId;
		$row=str::str_code(db::get_one('business', "BId='{$BId}'", 'ImgPath, PicPath'));
		if($row['ImgPath'] && is_file($c['root_path'].$row['ImgPath'])){
			file::del_file($row['ImgPath']);
		}
		if($row['PicPath'] && is_file($c['root_path'].$row['PicPath'])){
			file::del_file($row['PicPath']);
		}
		db::delete('business', "BId='$BId'");
		manage::operation_log('删除供应商');
		ly200::e_json('', 1);
	}
	
	public static function business_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="BId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_all('business', $del_where, 'ImgPath, PicPath'));
		foreach($row as $v){
			if($v['ImgPath'] && is_file($c['root_path'].$v['ImgPath'])){
				file::del_file($v['ImgPath']);
			}
			if($v['PicPath'] && is_file($c['root_path'].$v['PicPath'])){
				file::del_file($v['PicPath']);
			}
		}
		db::delete('business',$del_where);
		manage::operation_log('批量删除供应商');
		ly200::e_json('', 1);
	}
	
	public static function business_uesd(){
		global $c;
		$row=str::str_code(db::get_one('config', 'GroupId="business" and Variable="IsUsed"'));
		if((int)$row['Value']){
			$status='关闭';
			$is_used=0;
		}else{
			$status='开启';
			$is_used=1;
		}
		manage::config_operaction(array('IsUsed'=>$is_used), 'business');
		manage::operation_log($status.'供应商功能');
		ly200::e_json('', 1);
	}
	
	public static function business_category_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$Category=$p_Category;
		$UnderTheCateId=(int)$p_UnderTheCateId;
		if($UnderTheCateId==0){
			$UId='0,';
			$Dept=1;
		}else{
			$UId=category::get_UId_by_CateId($UnderTheCateId, 'business_category');
			$Dept=substr_count($UId, ',');
		}
		$data = array(
			'Category'	=>	$Category,
			'UId'		=>	$UId,
			'Dept'		=>	$Dept
		);
		if($CateId){
			db::update('business_category', "CateId='$CateId'", $data);
			manage::operation_log('修改供应商分类');
		}else{
			db::insert('business_category', $data);
			$CateId=db::get_insert_id();
			manage::operation_log('添加供应商分类');
		}
		$UId!='0,' && $CateId=category::get_top_CateId_by_UId($UId);
		$statistic_where.=category::get_search_where_by_CateId($CateId, 'business_category');
		category::category_subcate_statistic('business_category', $statistic_where);
		ly200::e_json('', 1);
	}
	
	public static function business_category_my_order(){
		global $c;
		$CateIdAry=@array_filter(@explode('-', $_GET['group_id']));
		$MyOrderAry=@array_filter(@explode('-', $_GET['my_order_value']));
		foreach($CateIdAry as $k=>$v){
			db::update('business_category', "CateId='$v'", array('MyOrder'=>$MyOrderAry[$k]));
		}
		manage::operation_log('批量供应商分类排序');
		ly200::e_json('', 1);
	}
	
	public static function business_category_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CateId=(int)$g_CateId;
		$row=db::get_one('business_category', "CateId='$CateId'", 'UId');
		$del_where=category::get_search_where_by_CateId($CateId, 'business_category');
		db::delete('business_category', $del_where);
		manage::operation_log('删除供应商分类');
		if($row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($row['UId']);
			$statistic_where=category::get_search_where_by_CateId($CateId, 'business_category');
			category::category_subcate_statistic('business_category', $statistic_where);
		}
		ly200::e_json('', 1);
	}
	
	public static function business_category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_all('business_category', $del_where));
		db::delete('business_category', $del_where);
		manage::operation_log('批量删除供应商分类');
		foreach($row as $v){
			if($v['UId']!='0,'){
				$CateId=category::get_top_CateId_by_UId($v['UId']);
				$statistic_where=category::get_search_where_by_CateId($CateId, 'business_category');
				category::category_subcate_statistic('business_category', $statistic_where);
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function review_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$RId=(int)$g_RId;
		$resize_ary=array('85x85');
		$review_row=str::str_code(db::get_one('products_review', "RId='$RId'", 'Audit, UserId, ProId, Rating, PicPath_0, PicPath_1, PicPath_2'));
		if($review_row){
			for($i=0; $i<3; $i++){
				$PicPath=$review_row["PicPath_$i"];
				if(is_file($c['root_path'].$PicPath)){
					foreach($resize_ary as $v){
						$ext_name=file::get_ext_name($PicPath);
						file::del_file($PicPath.".{$v}.{$ext_name}");
					}
					file::del_file($PicPath);
				}
			}
			$ProId=$review_row['ProId'];
			db::delete('products_review', "RId='{$RId}' or ReId='{$RId}'");
			$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
			$count=(int)db::get_row_count('products_review', "ProId='{$ProId}'".($review_cfg['display']==2?'':' and Audit=1 and IsMove=1'));
			$rating=(float)db::get_sum('products_review', "ProId='{$ProId}'".($review_cfg['display']==2?'':' and Audit=1 and IsMove=1'), 'Rating');
			db::update('products', "ProId='{$ProId}'", array('Rating'=>($count?ceil($rating/$count):0), 'TotalRating'=>$count));
			manage::operation_log('删除产品评论');
		}
		ly200::e_json('', 1);
	}
	
	public static function review_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_rid && ly200::e_json('');
		$rid_ary=explode('-', $g_group_rid);
		$resize_ary=array('85x85');
		$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
		foreach($rid_ary as $v){
			$review_row=str::str_code(db::get_one('products_review', "RId='$v'", 'Audit, UserId, ProId, Rating'));
			if($review_row){
				for($i=0; $i<3; $i++){
					$PicPath=$review_row["PicPath_$i"];
					if(is_file($c['root_path'].$PicPath)){
						foreach($resize_ary as $v2){
							$ext_name=file::get_ext_name($PicPath);
							file::del_file($PicPath.".{$v2}.{$ext_name}");
						}
						file::del_file($PicPath);
					}
				}
				$ProId=$review_row['ProId'];
				db::delete('products_review', "RId='{$v}' or ReId='{$v}'");
				$count=(int)db::get_row_count('products_review', "ProId='{$ProId}'".($review_cfg['display']==2?'':' and Audit=1 and IsMove=1'));
				$rating=(float)db::get_sum('products_review', "ProId='{$ProId}'".($review_cfg['display']==2?'':' and Audit=1 and IsMove=1'), 'Rating');
				db::update('products', "ProId='{$ProId}'", array('Rating'=>($count?ceil($rating/$count):0), 'TotalRating'=>$count));
			}
		}
		manage::operation_log('批量删除产品评论');
		ly200::e_json('', 1);
	}
	
	public static function review_reply(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_RId=(int)$p_RId;
		$p_ProId=(int)$p_ProId;
		$p_Audit=(int)$p_Audit;
		$review_row=str::str_code(db::get_one('products_review', "RId='$p_RId'", 'Audit, UserId, CustomerName, Rating'));
		!$review_row && ly200::e_json('', 0);
		if($p_RId>0){
			db::update('products_review', "RId='$p_RId'", array('Audit'=>$p_Audit));
			db::update('products_review', "ReId='$p_RId'", array('Audit'=>0));//回复审核清0
		}
		if($p_ReAudit){//回复审核
			$ids=implode(',', array_keys($p_ReAudit)); 
			$sql="update products_review set Audit = case RId "; 
			foreach($p_ReAudit as $k=>$v){
				$sql.=sprintf("when %d then %d ", $k, $v); 
			}
			$sql.="end where RId in($ids)"; 
			db::query($sql);
		}
		
		if($review_row['Audit']!=$p_Audit){
			$w="ProId='{$p_ProId}' and Audit=1 and ReId=0";
			$count=(int)db::get_row_count('products_review', $w);
			$rating=(float)db::get_sum('products_review', $w, 'Rating');
			db::update('products', "ProId='{$p_ProId}'", array('Rating'=>($count?ceil($rating/$count):0), 'TotalRating'=>$count));
		}
			
		if($p_ReviewComment){//管理员回复
			$data=array(
				'ProId'			=>	$p_ProId,
				'UserId'		=>	0,
				'ReId'			=>	$p_RId,
				'CustomerName'	=>	'Manager',
				'Content'		=>	$p_ReviewComment,
				'Audit'			=>	1,
				'Ip'			=>	ly200::get_ip(),
				'AccTime'		=>	$c['time'],
			);
			db::insert('products_review', $data);
			$Email=str::str_code(db::get_value('user', "UserId='{$review_row['UserId']}'", 'Email'));
			ly200::sendmail($Email, $review_row['CustomerName'], ly200::get_domain().' administrator reply your comments', ly200::get_domain().' administrator reply your comments');
		}
		manage::operation_log("修改产品评论 产品ID:{$p_ProId} 评论ID:{$p_RId}");
		ly200::e_json('', 1);
	}
	
	public static function upload_new(){
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
			manage::operation_log('产品批量上传');
			ly200::e_json('<p>批量上传完成</p>', 1);
		}
		//初始化第二阶段
		$language=$p_Language;//语言版本
		if(!$language || !in_array($language, $c['manage']['web_lang_list'])){//找不到相对应的语言，默认为可用语言里面的第一个
			$language=$c['manage']['web_lang_list'][0];
		}
		$insert_ary=$update_ary=$category_ary=$category_top_ary=$business_ary=$attribute_ary=$attribute_top_ary=$attribute_cart_ary=$attribute_color_ary=$vid_data_ary=array();
		//产品分类
		$category_row=db::get_all('products_category', '1', "CateId, UId, AttrId", 'UId asc');
		foreach($category_row as $v){
			$category_ary[$v['CateId']]=$v['UId'];
			if($v['UId']=='0,'){//顶级分类
				$category_top_ary[$v['CateId']]=$v['AttrId'];
			}
		}
		//供应商
		$business_row=str::str_code(db::get_all('business', '1', 'BId, Name'));
		foreach($business_row as $v){
			$business_ary[md5($v['Name'])]=$v['BId'];
		}
		//产品属性
		$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name_{$language}, ParentId, CartAttr, ColorAttr"));
		foreach($attribute_row as $v){
			$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name_{$language}"]);
			if($v['ParentId']){//收录父亲属性
				$attribute_top_ary[$v['ParentId']][$v["Name_{$language}"]]=$v['AttrId'];
			}
			if($v['CartAttr']){//购物车属性
				$attribute_cart_ary[$v['ParentId']][]=$v['AttrId'];
			}
			if($v['ColorAttr']){//颜色属性
				$attribute_color_ary[$v['ParentId']]=$v['AttrId'];
			}
		}
		unset($attribute_row);
		$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
		foreach($value_row as $v){
			$vid_data_ary[$v['AttrId']][$v["Value_{$language}"]]=$v['VId'];
		}
		unset($value_row);
		//海外仓
		$shipping_overseas_row=db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc');
		$oversea_len=count($shipping_overseas_row);
		$oversea_name_ary=array();
		foreach($shipping_overseas_row as $v){
			$oversea_name_ary[$v["Name_{$language}"]]=$v['OvId'];
		}
		//自动排序
		$cfg_row=str::str_code(db::get_all('config', 'GroupId="products_show"'));
		foreach($cfg_row as $v){
			$cfg_ary[$v['GroupId']][$v['Variable']]=$v['Value'];
		}
		$used_row=str::json_data(htmlspecialchars_decode($cfg_ary['products_show']['Config']), 'decode');
		//字段数组
		$column_products_ary=db::get_table_fields('products', 1);
		$column_products_description_ary=db::get_table_fields('products_description', 1);
		$column_products_seo_ary=db::get_table_fields('products_seo', 1);
		//内容转换为数组 
		$data=$sheet->toArray();
		$num=count($c['manage']['web_lang_list']);
		$un_data_ary=$data_ary=array();
		$i=-1;
		foreach($data as $k=>$v){//行
			if($k<1) continue;
			if($v[0] && $v[3]){//一个产品的资料
				++$i;
				$un_data_ary[$i]=$v;
			}else{//产品的附属参数
				$v[15] && $un_data_ary[$i][15].=','.$v[15];//图片
				$v[17] && $un_data_ary[$i][17].=';'.$v[17];//属性
				$v[18] && $un_data_ary[$i][18].=';'.$v[18];//属性关联项
				$v[19] && $un_data_ary[$i][19].=';'.$v[19];//颜色关联项
			}
		}
		foreach($un_data_ary as $k=>$v){//产品
			if($Start<=$k && $k<($Start+$page_count)){
				if(db::get_row_count('products', "concat_ws('', Prefix, Number)='{$v[3]}'")){//更新数据库
					$data_ary['update'][]=$v;
				}else{//插入数据库
					$data_ary['insert'][]=$v;
				}
			}elseif($k>=($Start+$page_count)){
				break;
			}
		}
		$tab_title_ary=array(0=>$data[0][46], 1=>$data[0][47], 2=>$data[0][48]);//选项卡标题
		unset($data, $un_data_ary);
		//图片储存位置
		$resize_ary=$c['manage']['resize_ary']['products'];
		$save_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		//过滤敏感词
		@include_once($c['root_path'].'/manage/static/inc/filter.library.php');
		@include_once($c['root_path'].'/inc/un_filter_keywords.php');
		$filter_keywords_ary=$FilterKeyArr['Keyword'];
		$un_filter_keywords_ary=(array)@str::str_code($un_filter_keywords, 'strtolower');
		//开始导入
		foreach((array)$data_ary as $a=>$b){
			$No=0;
			$insert_sql=$update_sql=$database_sql=array();
			foreach($b as $key=>$val){
				$Name=trim($val[0]);//名称
				$CateId=(int)trim($val[1]);//分类
				if(!$CateId || ($CateId && !$category_ary[$CateId])){ $errerTxt.="<p>(上传失败) {$Name} 分类不存在</p>"; continue;}
				$ExtCateId=trim($val[2]);//多分类
				$ExtCateId && $ExtCateId=','.$ExtCateId.',';
				$Number=trim($val[3]);//编号
				if(!$Number){ $errerTxt.="<p>(上传失败) {$Name} 编号为空</p>"; continue;}
				$SKU=trim($val[4]);//SKU
				$Business=(int)$business_ary[md5(trim($val[5]))];//供应商
				$Price_0=(float)trim($val[6]);//市场价
				$Price_1=(float)trim($val[7]);//会员价
				$PurchasePrice=(float)trim($val[8]);//进货价
				$Wholesale=trim($val[9]);//批发价
				$IsPromotion=(int)trim($val[10]);//开启促销
				$PromotionType=0;//默认类型为促销价格
				$PromotionPrice=(float)trim($val[11]);//促销价格
				$PromotionDiscount=(int)trim($val[12]);//促销折扣
				$PromotionDiscount=$PromotionDiscount?($PromotionDiscount>100?100:$PromotionDiscount):100;
				if($PromotionDiscount) $PromotionType=1;//促销折扣有填写，自动默认开启折扣类型
				$PromotionTime=@explode('/', trim($val[13]));//促销时间
				$StartTime=(int)@strtotime($PromotionTime[0]);
				$EndTime=(int)@strtotime($PromotionTime[1]);
				$PageUrl=trim($val[14]);//自定义地址
				$PicPath=@explode(',', trim($val[15]));
				$IsCombination=(int)trim($val[16]);//属性组合
				$Attr=trim($val[17]);//属性
				$ExtAttr=trim($val[18]);//属性关联项
				$ColorAttr=trim($val[19]);//颜色关联项
				$Weight=(float)trim($val[20]);//重量
				$Cubage=str_replace('*', ',', trim($val[21]));//体积
				$IsVolumeWeight=(int)trim($val[22]);//体积重
				$MOQ=(int)trim($val[23]);//起订量
				$MaxOQ=(int)trim($val[24]);//最大购买量
				$Sales=(int)trim($val[25]);//产品销量
				$Stock=(int)trim($val[26]);//库存
				$WarnStock=(int)trim($val[27]);//警告库存
				$StockOut=(int)trim($val[28]);//脱销状态
				$SoldOut=(int)trim($val[29]);//下架
				$IsSoldOut=(int)trim($val[30]);//定时上架
				$SoldOut && $IsSoldOut=0;
				$SoldOutTime=@explode('/', trim($val[31]));//定时上架时间
				$SStartTime=(int)@strtotime($SoldOutTime[0]);
				$SEndTime=(int)@strtotime($SoldOutTime[1]);
				$IsFreeShipping=(int)trim($val[32]);//免运费
				$FavoriteCount=(int)trim($val[33]);//收藏数
				$IsNew=(int)trim($val[34]);//新品
				$IsHot=(int)trim($val[35]);//热卖
				$IsBestDeals=(int)trim($val[36]);//畅销
				$IsIndex=(int)trim($val[37]);//首页显示
				$IsDefaultReview=(int)trim($val[38]);//开启默认评论
				$DefaultReviewRating=(float)trim($val[39]);//默认评论平均分
				$DefaultReviewTotalRating=(int)trim($val[40]);//默认评论人数
				$SeoTitle=trim($val[41]);//标题
				$SeoKeyword=trim($val[42]);//关键字
				$SeoDescription=trim($val[43]);//描述
				$BriefDescription=trim($val[44]);//简单介绍
				$Description=trim($val[45]);//详细介绍
				$Tab0=trim($val[46]);//选项卡1
				$Tab1=trim($val[47]);//选项卡2
				$Tab2=trim($val[48]);//选项卡3
				$MyOrder=0;
				//更新代码
				$a=='update' && $prod_row=db::get_one('products', "concat_ws('', Prefix, Number)='{$Number}'", 'ProId, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9');
				//如果产品编号为空，并且有启用产品编号自动排序，会按照自动排序生成产品编号
				$Prefix='';
				if(!$Number && $cfg_ary['products_show']['myorder'] && $used_row['myorder']){
					$Prefix=$cfg_ary['products_show']['myorder'];
					$max_num=(int)db::get_max('products', "Prefix='{$cfg_ary['products_show']['myorder']}' and Number!=''", 'ProId');
					$Number=$max_num+$key+1;
				}
				//批发价
				if($Wholesale){
					$Qty=@explode(',', $Wholesale);
					$wholesale_ary=array();
					foreach((array)$Qty as $k=>$v){
						if($k>4) break;
						$arr=@explode(':', $v);
						$wholesale_ary[$arr[0]]=$arr[1];
					}
					$Wholesale=addslashes(str::json_data(str::str_code($wholesale_ary, 'stripslashes')));
				}
				//过滤敏感词
				$arr_filter=array($Name, $SeoTitle, $SeoKeyword, $SeoDescription, $BriefDescription, $Description);
				if((int)count($arr_filter)){
					$filterArr=$arr_filter;
				}else{
					$filterArr=$_POST;
					unset($filterArr['do_action'], $filterArr['PicPath'], $filterArr['FilePath'], $filterArr['UId'], $filterArr['ColorPath'], $filterArr['Number']);
				}
				$str=' '.@implode(' -- ', $filterArr).' ';
				$result=0;
				$keyword='';
				foreach($filter_keywords_ary as $v2){
					if(@count($un_filter_keywords_ary) && @in_array(strtolower(trim($v2)), $un_filter_keywords_ary)) continue;
					if(@substr_count(strtolower(stripslashes($str)), strtolower($v2))){
						$keyword=$v2;
						$result=1;
						break;
					}
				}
				unset($arr_filter, $filterArr);
				if($result==1){
					$errerTxt.="<p>(上传失败) {$Name} 带有敏感词:{$keyword}</p>";
					continue;
				}
				//产品属性
				if($category_ary[$CateId]=='0,'){
					$AttrId=(int)$category_top_ary[$CateId];
				}else{
					$TopCateId=(int)category::get_top_CateId_by_UId($category_ary[$CateId]);//获取顶级分类ID
					$AttrId=(int)$category_top_ary[$TopCateId];
				}
				if($AttrId){
					$attr_ary=$ext_ary=$color_ary=$color_id_ary=array();
					if($Attr){//属性
						$Attr=explode(';', $Attr);
						foreach((array)$Attr as $v){
							$ary=@explode('[', substr($v, 0, -1));
							$value=trim($ary[1]);
							if($ary[0]=='Oversea'){//海外仓
								$id='Oversea';//$oversea_name_ary['']
								$arr=array();
								$value=@explode(',', $value);
								foreach($value as $v2){
									if($v2) $arr[]=$oversea_name_ary[$v2];
								}
							}else{//属性
								$id=$attribute_top_ary[$AttrId][$ary[0]];
								if($attribute_ary[$id][0]){//列表选择
									$arr=array();
									$value=@explode(',', $value);
									foreach($value as $v2){
										if($v2) $arr[]=$vid_data_ary[$id][$v2];
									}
								}else{
									$arr=$value;
								}
							}
							$attr_ary[$id]=$arr;
						}
					}
					if($ExtAttr){//组合属性
						$ExtAttr=explode(';', $ExtAttr);
						foreach((array)$ExtAttr as $v){
							$ary=@explode('=', $v);
							$name_ary=@explode(']', $ary[0]);
							$value=array();
							$OvId=1;
							foreach($name_ary as $v2){
								if(!$v2) continue;
								$arr=@explode('[', $v2);
								if($arr[0]=='Oversea'){//海外仓
									$OvId=$oversea_name_ary[$arr[1]];
								}else{//属性
									$value[]=$vid_data_ary[$attribute_top_ary[$AttrId][$arr[0]]][$arr[1]];
								}
							}
							sort($value);
							$ext_ary['|'.implode('|', $value).'|'][$OvId]=explode(',', $ary[1]);
						}
					}
					/************* 检查购物车属性，是否有缺少填写勾选购物车属性的属性关联项 Start *************/
					$check_ary=$key_ary=array();
					if(is_array($attribute_cart_ary[$AttrId])){
						foreach((array)$attr_ary as $k=>$v){
							if(in_array($k, $attribute_cart_ary[$AttrId])){
								$key_ary[]=$k;
								$check_ary[$k]=$v;
							}
						}
						$check_ext_ary=self::check_cart_attr($check_ary, $key_ary, 0);
						foreach($check_ext_ary as $v){
							$value=@explode('|', $v);
							sort($value);
							$value='|'.implode('|', $value).'|';
							if(!$ext_ary[$value]){ $ext_ary[$value]=array(0, 0, 0, '', 0); }
						}
					}
					/************* 检查购物车属性，是否有缺少填写勾选购物车属性的属性关联项 End *************/
					if(!$attr_ary['Oversea']){//检查海外仓是否遗漏勾选China
						$attr_ary['Oversea'][0]=1;
					}
					if($ColorAttr){//颜色属性
						$ColorAttr=explode(';', $ColorAttr);
						$ColorId=$attribute_color_ary[$AttrId];
						foreach((array)$ColorAttr as $v){
							$ary=@explode('=', $v);
							$ary[0]=substr(strstr($ary[0], '['), 1, -1);
							$color_ary[$vid_data_ary[$ColorId][$ary[0]]]=explode(',', $ary[1]);
						}
					}
				}
				//图片上传
				$ImgPath=array();
				foreach((array)$PicPath as $k=>$v){
					if(stripos($v, 'u_file')!==false) continue; //本地图片，跳出执行
					$water_ary=array();
					$filepath='/tmp/madeimg/'.$v;
					if(is_file($c['root_path'].$filepath)){//检查图片是否存在
						$ext_name=file::get_ext_name($filepath);//图片文件后缀名
						$new_path=$save_dir.str::rand_code().'.'.$ext_name;//图片重新命名
						@copy($c['root_path'].$filepath, $c['root_path'].$new_path);//先把目标图片复制到u_file
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
						foreach($resize_ary as $v2){
							$ext_name=file::get_ext_name($prod_row['PicPath_'.$k]);
							file::del_file($prod_row['PicPath_'.$k].".{$v2}.{$ext_name}");
						}
						file::del_file($prod_row['PicPath_'.$k]);
					}else{
						$ImgPath[$k]=$prod_row['PicPath_'.$k];
					}
				}
				foreach((array)$ImgPath as $k=>$v){
					$ext_name=file::get_ext_name($v);
					foreach($resize_ary as $v2){
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
				
				//记录数据资料
				$data=array(
					"Name_{$language}"	=>	addslashes($Name),
					'CateId'			=>	$CateId,
					'ExtCateId'			=>	$ExtCateId,
					'Prefix'			=>	$Prefix,
					'Number'			=>	$Number,
					'SKU'				=>	$SKU,
					'Business'			=>	$Business,
					'PurchasePrice'		=>	$PurchasePrice,
					'Price_0'			=>	$Price_0,
					'Price_1'			=>	$Price_1,
					'IsPromotion'		=>	$IsPromotion,
					'PromotionType'		=>	$PromotionType,
					'PromotionPrice'	=>	$PromotionPrice,
					'PromotionDiscount'	=>	$PromotionDiscount,
					'StartTime'			=>	$StartTime,
					'EndTime'			=>	$EndTime,
					'Wholesale'			=>	$Wholesale,
					'PicPath_0'			=>	$ImgPath[0]?$ImgPath[0]:$prod_row['PicPath_0'],
					'PicPath_1'			=>	$ImgPath[1]?$ImgPath[1]:$prod_row['PicPath_1'],
					'PicPath_2'			=>	$ImgPath[2]?$ImgPath[2]:$prod_row['PicPath_2'],
					'PicPath_3'			=>	$ImgPath[3]?$ImgPath[3]:$prod_row['PicPath_3'],
					'PicPath_4'			=>	$ImgPath[4]?$ImgPath[4]:$prod_row['PicPath_4'],
					'PicPath_5'			=>	$ImgPath[5]?$ImgPath[5]:$prod_row['PicPath_5'],
					'PicPath_6'			=>	$ImgPath[6]?$ImgPath[6]:$prod_row['PicPath_6'],
					'PicPath_7'			=>	$ImgPath[7]?$ImgPath[7]:$prod_row['PicPath_7'],
					'PicPath_8'			=>	$ImgPath[8]?$ImgPath[8]:$prod_row['PicPath_8'],
					'PicPath_9'			=>	$ImgPath[9]?$ImgPath[9]:$prod_row['PicPath_9'],
					'Weight'			=>	$Weight,
					'Cubage'			=>	$Cubage,
					'IsVolumeWeight'	=>	$IsVolumeWeight,
					'MOQ'				=>	(1<$MOQ && $MOQ<=$Stock)?$MOQ:1,
					'MaxOQ'				=>	($MaxOQ<0)?0:$MaxOQ,
					'Sales'				=>	$Sales,
					'Stock'				=>	$Stock,
					'WarnStock'			=>	$WarnStock,
					'StockOut'			=>	$StockOut,
					'IsIncrease'		=>	0,
					'IsCombination'		=>	$IsCombination,
					'AttrId'			=>	$AttrId,
					'SoldOut'			=>	$SoldOut,
					'IsSoldOut'			=>	$IsSoldOut,
					'SStartTime'		=>	$SStartTime,
					'SEndTime'			=>	$SEndTime,
					'IsFreeShipping'	=>	$IsFreeShipping,
					'FavoriteCount'		=>	$FavoriteCount,
					'IsNew'				=>	$IsNew,
					'IsHot'				=>	$IsHot,
					'IsBestDeals'		=>	$IsBestDeals,
					'IsIndex'			=>	$IsIndex,
					'IsDefaultReview'	=>	$IsDefaultReview,
					'DefaultReviewRating'=>	$DefaultReviewRating,
					'DefaultReviewTotalRating'=>$DefaultReviewTotalRating,
					'PageUrl'			=>	ly200::str_to_url($PageUrl),
					"BriefDescription_{$language}"=>addslashes($BriefDescription),
					'IsDesc'			=>	str::json_data(array(trim($Tab0)?1:0,trim($Tab1)?1:0,trim($Tab2)?1:0)),
					'AccTime'			=>	$c['time'],
					'MyOrder'			=>	$MyOrder
				);
				$database_sql[$data['Prefix'].$data['Number']]=array(
					'Seo'		=>	array(//标题与标签
										"SeoTitle_{$language}"		=>	addslashes($SeoTitle),
										"SeoKeyword_{$language}"	=>	addslashes($SeoKeyword),
										"SeoDescription_{$language}"=>	addslashes($SeoDescription)
									),
					'Desc'		=>	array("Description_{$language}"	=>	addslashes($Description)),//详细介绍
					'Tab_0'		=>	array(//选项卡1
										"TabName_0_{$language}"		=>	addslashes($tab_title_ary[0]),
										"Tab_0_{$language}"			=>	addslashes($Tab0)
									),
					'Tab_1'		=>	array(//选项卡2
										"TabName_1_{$language}"		=>	addslashes($tab_title_ary[1]),
										"Tab_1_{$language}"			=>	addslashes($Tab1)
									),
					'Tab_2'		=>	array(//选项卡3
										"TabName_2_{$language}"		=>	addslashes($tab_title_ary[2]),
										"Tab_2_{$language}"			=>	addslashes($Tab2)
									),
					'Attr'		=>	$attr_ary,//属性
					'ExtAttr'	=>	$ext_ary,//组合属性
					'Color'		=>	$color_ary//颜色属性
				);
				if($a=='update'){//更新数据库
					$ProId=$prod_row['ProId'];
					unset($data['Prefix'], $data['Number']);//不更新产品编号
					foreach($data as $k=>$v){
						$update_sql[$k][$ProId]=$v;
					}
				}else{//插入数据库
					$insert_sql['Product'].=($No?',':'')."('".$data["Name_{$language}"]."', {$data['CateId']}, '{$data['ExtCateId']}', '{$data['Prefix']}', '{$data['Number']}', '{$data['SKU']}', '{$data['Business']}', {$data['PurchasePrice']}, {$data['Price_0']}, {$data['Price_1']}, '{$data['IsPromotion']}', '{$data['PromotionType']}', '{$data['PromotionPrice']}', '{$data['PromotionDiscount']}', '{$data['StartTime']}', '{$data['EndTime']}', '{$data['Wholesale']}', '{$data['PicPath_0']}', '{$data['PicPath_1']}', '{$data['PicPath_2']}', '{$data['PicPath_3']}', '{$data['PicPath_4']}', '{$data['PicPath_5']}', '{$data['PicPath_6']}', '{$data['PicPath_7']}', '{$data['PicPath_8']}', '{$data['PicPath_9']}', {$data['Weight']}, '{$data['Cubage']}', {$data['MOQ']}, {$data['MaxOQ']}, {$data['Sales']}, {$data['Stock']}, {$data['WarnStock']}, {$data['StockOut']}, {$data['IsIncrease']}, {$data['IsCombination']}, {$data['AttrId']}, '{$data['Attr']}', '{$data['ExtAttr']}', {$data['SoldOut']}, {$data['IsSoldOut']}, '{$data['SStartTime']}', '{$data['SEndTime']}', {$data['IsFreeShipping']}, {$data['FavoriteCount']}, {$data['IsNew']}, {$data['IsHot']}, {$data['IsBestDeals']}, {$data['IsIndex']}, {$data['IsDefaultReview']}, {$data['DefaultReviewRating']}, {$data['DefaultReviewTotalRating']}, '{$data['PageUrl']}', '".$data["BriefDescription_{$language}"]."', '".$data["IsDesc"]."', {$data['MyOrder']}, {$data['AccTime']})";
				}
				++$No;
			}
			
			if($a=='update'){//更新数据库
				if(is_array($update_sql) && count($update_sql)){
					$ides=implode(',', array_keys($update_sql['CateId'])); 
					$len=count($update_sql);
					$i=0;
					$sql="update products set";
						foreach($update_sql as $k=>$v){
							$sql.=" {$k} = case ProId";
							foreach($v as $k2=>$v2){
								$sql.=sprintf(" when %s then '%s' ", $k2, $v2); 
							}
							$sql.='end'.(++$i<$len?',':'');
						}
					$sql.=" where ProId in($ides)";
					$sql && db::query($sql);
				}
			}else{//插入数据库
				$insert_sql['Product'] && db::query('insert into products (Name_'.$language.', CateId, ExtCateId, Prefix, Number, SKU, Business, PurchasePrice, Price_0, Price_1, IsPromotion, PromotionType, PromotionPrice, PromotionDiscount, StartTime, EndTime, Wholesale, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9, Weight, Cubage, MOQ, MaxOQ, Sales, Stock, WarnStock, StockOut, IsIncrease, IsCombination, AttrId, Attr, ExtAttr, SoldOut, IsSoldOut, SStartTime, SEndTime, IsFreeShipping, FavoriteCount, IsNew, IsHot, IsBestDeals, IsIndex, IsDefaultReview, DefaultReviewRating, DefaultReviewTotalRating, PageUrl, BriefDescription_'.$language.', IsDesc, MyOrder, AccTime) values'.$insert_sql['Product']);
			}
			
			//其他数据表的内容更新
			if($database_sql){
				$proid_where=$sid_where=$did_where=$seid_where=$comid_where=$cid_where='';
				$insert_sql=$update_sql=$proid_ary=array();
				reset($database_sql);
				$num_where=@implode("','", array_keys($database_sql));
				$row=str::str_code(db::get_all('products', "concat_ws('', Prefix, Number) in('{$num_where}')", 'ProId, Prefix, Number'));
				foreach((array)$row as $k=>$v){
					$proid_ary[htmlspecialchars_decode($v['Prefix'].$v['Number'])]=$v['ProId'];
				}
				$proid_where=@implode(',', $proid_ary);
				if($proid_where){
					if($a=='update'){//更新数据库
						$sid_ary=$did_ary=$seid_ary=$comid_ary=$_comid_ary=$cid_ary=$pic_color_ary=$un_pic_color_ary=$update_ary=$insert_ary=array();
						$insert_sql['Desc']=$insert_sql['Attr']=$insert_sql['ExtAttr']=$insert_sql['Color']='';
						//列出SEO资料
						$seo_row=str::str_code(db::get_all('products_seo', "ProId in($proid_where)", 'SId, ProId'));
						foreach($seo_row as $k=>$v){
							$sid_ary[$v['ProId']]=$v['SId'];
						}
						$sid_where=@implode(',', $sid_ary);
						!$sid_where && $sid_where='0';
						//列出详细介绍资料
						$desc_row=str::str_code(db::get_all('products_description', "ProId in($proid_where)", 'DId, ProId'));
						foreach($desc_row as $k=>$v){
							$did_ary[$v['ProId']]=$v['DId'];
						}
						$did_where=@implode(',', $did_ary);
						!$did_where && $did_where='0';
						//列出产品属性勾选资料
						$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId in($proid_where)"));
						foreach($selected_row as $k=>$v){
							$seid_ary[$v['ProId'].'_'.$v['AttrId'].'_'.$v['VId'].'_'.$v['OvId']]=$v['SeleteId'];
						}
						unset($selected_row);
						$seid_where=@implode(',', $seid_ary);
						!$seid_where && $seid_where='0';
						//列出产品组合属性关联项资料
						$combination_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId in($proid_where)"));
						foreach($combination_row as $k=>$v){
							$comid_ary[$v['ProId']][$v['Combination']][$v['OvId']]=$v['CId'];
							$_comid_ary[]=$v['CId'];
						}
						$comid_where=@implode(',', $_comid_ary);
						!$comid_where && $comid_where='0';
						//列出颜色图片资料
						$color_row=str::str_code(db::get_all('products_color', "ProId in($proid_where)"));
						foreach($color_row as $k=>$v){
							$cid_ary[$v['ProId'].'_'.$v['VId']]=$v['CId'];
							$pic_color_ary[]=array($v['PicPath_0'], $v['PicPath_1'], $v['PicPath_2'], $v['PicPath_3'], $v['PicPath_4'], $v['PicPath_5'], $v['PicPath_6'], $v['PicPath_7'], $v['PicPath_8'], $v['PicPath_9']);
						}
						$cid_where=@implode(',', $cid_ary);
						!$cid_where && $cid_where='0';
						//准备
						$i=$j=$ExtAttr_update=0;
						$len=count($database_sql);
						//更新数值重新排列
						foreach($database_sql as $k=>$v){
							foreach((array)$v as $k2=>$v2){
								foreach((array)$v2 as $k3=>$v3){
									if($k2=='Color'){
										foreach($v3 as $k4=>$v4){
											$update_ary[$k2][$k4][$k3][$k]=$v4;
										}
									}else{
										$update_ary[$k2][$k3][$k]=$v3;
									}
								}
							}
						}
						foreach($update_ary as $k=>$v){
							if($k=='Seo'){//SEO
								$j=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Seo'].=($j?',':'')." {$k2} = case SId";
									foreach($v2 as $k3=>$v3){
										$id=$proid_ary[$k3];//产品ID
										$update_sql['Seo'].=sprintf(" when %s then '%s' ", $sid_ary[$id], $v3);
									}
									$update_sql['Seo'].='end';
									++$j;
								}
							}
							if($k=='Desc'){//详细介绍
								$j=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Desc'].=($j?',':'')." {$k2} = case DId";
									foreach($v2 as $k3=>$v3){
										$id=$proid_ary[$k3];//产品ID
										//$update_sql['Desc'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3);
										if($did_ary[$id]){//已存在
											$update_sql['Desc'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3);
										}else{//不存在
											$insert_ary['Desc'][$id]="'{$v3}'";
										}
									}
									$update_sql['Desc'].='end';
									++$j;
								}
							}
							if($k=='Tab_0'){//选项卡1
								$j=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Tab_0'].=($j?',':'')." {$k2} = case DId";
									foreach($v2 as $k3=>$v3){
										$id=$proid_ary[$k3];//产品ID
										//$update_sql['Tab_0'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3); 
										if($did_ary[$id]){//已存在
											$update_sql['Tab_0'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3);
										}else{//不存在
											$insert_ary['Desc'][$id].=",'{$v3}'";
										}
									}
									$update_sql['Tab_0'].='end';
									++$j;
								}
							}
							if($k=='Tab_1'){//选项卡2
								$j=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Tab_1'].=($j?',':'')." {$k2} = case DId";
									foreach($v2 as $k3=>$v3){
										$id=$proid_ary[$k3];//产品ID
										//$update_sql['Tab_1'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3); 
										if($did_ary[$id]){//已存在
											$update_sql['Tab_1'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3);
										}else{//不存在
											$insert_ary['Desc'][$id].=",'{$v3}'";
										}
									}
									$update_sql['Tab_1'].='end';
									++$j;
								}
							}
							if($k=='Tab_2'){//选项卡3
								$j=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Tab_2'].=($j?',':'')." {$k2} = case DId";
									foreach($v2 as $k3=>$v3){
										$id=$proid_ary[$k3];//产品ID
										//$update_sql['Tab_2'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3); 
										if($did_ary[$id]){//已存在
											$update_sql['Tab_2'].=sprintf(" when %s then '%s' ", $did_ary[$id], $v3);
										}else{//不存在
											$insert_ary['Desc'][$id].=",'{$v3}'";
										}
									}
									$update_sql['Tab_2'].='end';
									++$j;
								}
							}
							if($k=='Attr'){//产品属性
								$j=$m=0;
								$IsUsed_sql=" IsUsed = case SeleteId";
								$Value_sql=", Value = case SeleteId";
								foreach((array)$v as $k2=>$v2){
									if($k2=='Oversea' || $attribute_ary[$k2][0]==1){//海外仓 or 列表选择
										foreach($v2 as $k3=>$v3){
											$id=$proid_ary[$k3];//产品ID
											foreach($v3 as $v4){
												$OvId=1;
												if($k2=='Oversea'){//海外仓
													$OvId=$v4;
													$k2=$v4=0;
												}
												if($seid_ary[$id.'_'.$k2.'_'.$v4.'_'.$OvId]){//已存在
													$IsUsed_sql.=sprintf(" when %s then '%s' ", $seid_ary[$id.'_'.$k2.'_'.$v4.'_'.$OvId], 1); 
												}else{//不存在
													$insert_sql['Attr'].=($m?',':'')."({$id}, {$k2}, '{$v4}', '{$OvId}', '', 1)"; 
													++$m;
												}
											}
										}
									}else{//文本框
										foreach($v2 as $k3=>$v3){
											$id=$proid_ary[$k3];//产品ID
											if($seid_ary[$id.'_'.$k2.'_0']){//已存在
												$IsUsed_sql.=sprintf(" when %s then '%s' ", $seid_ary[$id.'_'.$k2.'_0'], 1); 
												$Value_sql.=sprintf(" when %s then '%s' ", $seid_ary[$id.'_'.$k2.'_0'], $v3); 
											}else{//不存在
												$insert_sql['Attr'].=($m?',':'')."({$id}, {$k2}, 0, 1, '".addslashes($v3)."', 1)"; 
												++$m;
											}
										}
									}
									++$j;
								}
								$IsUsed_sql.='end';
								$Value_sql.=' end';
								$update_sql['Attr']=$IsUsed_sql.($Value_sql!=', Value = case SeleteId end'?$Value_sql:'');
							}
							if($k=='ExtAttr'){//产品组合属性关联项
								$j=$m=0;
								$OvId_sql=" OvId = case CId";
								$SKU_sql=", SKU = case CId";
								$IsIncrease_sql=", IsIncrease = case CId";
								$Price_sql=", Price = case CId";
								$Stock_sql=", Stock = case CId";
								$Weight_sql=", Weight = case CId";
								foreach((array)$v as $k2=>$v2){
									foreach($v2 as $k3=>$v3){//$k3=Combination
										$id=$proid_ary[$k3];//产品ID
										foreach($v3 as $k4=>$v4){//$k4=OvId
											$k4<1 && $k4=1;
											if($comid_ary[$id][$k2][$k4]){//已存在
												$ExtAttr_update++;
												$OvId_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $k4);
												$SKU_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $v4[3]);
												$IsIncrease_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $v4[4]);
												$Price_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $v4[0]);
												$Stock_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $v4[1]);
												$Weight_sql.=sprintf(" when %s then '%s' ", $comid_ary[$id][$k2][$k4], $v4[2]);
											}else{//不存在
												$insert_sql['ExtAttr'].=($m?',':'')."({$id}, '{$k2}', '{$k4}', '{$v4[3]}', '{$v4[4]}', '{$v4[0]}', '{$v4[1]}', '{$v4[2]}')"; 
												++$m;
											}
										}
									}
									++$j;
								}
								$OvId_sql.='end';
								$SKU_sql.='end';
								$IsIncrease_sql.='end';
								$Price_sql.='end';
								$Stock_sql.='end';
								$Weight_sql.='end';
								$update_sql['ExtAttr']=$OvId_sql.$SKU_sql.$IsIncrease_sql.$Price_sql.$Stock_sql.$Weight_sql;
							}
							if($k=='Color'){//颜色图片
								$j=$m=0;
								foreach((array)$v as $k2=>$v2){
									$update_sql['Color'].=($j?',':'')." PicPath_{$k2} = case CId";
									foreach((array)$v2 as $k3=>$v3){
										$k3<1 && $k3=1;
										foreach((array)$v3 as $k4=>$v4){
											$id=$proid_ary[$k4];//产品ID
											if($v4){
												if(stripos($v4, 'u_file')!==false){ //本地图片，直接跳出
													$un_pic_color_ary[]=$v4; //记录到不删除原图片数组上
													$pic_ary=array(0=>$v4);
												}else{
													$pic_ary=self::upload_picture_color(array($v4), $save_dir, $resize_ary);
												}
											}else{
												$pic_ary=array(0=>'');
											}
											if($cid_ary[$id.'_'.$k3]){//已存在
												$update_sql['Color'].=sprintf(" when %s then '%s' ", $cid_ary[$id.'_'.$k3], $pic_ary[0]);
											}else{//不存在
												$insert_sql['Color'].=($m?',':'')."('{$id}', '{$k3}', '{$pic_ary[0]}', '{$pic_ary[1]}', '{$pic_ary[2]}', '{$pic_ary[3]}', '{$pic_ary[4]}', '{$pic_ary[5]}', '{$pic_ary[6]}', '{$pic_ary[7]}', '{$pic_ary[8]}', '{$pic_ary[9]}')";
												++$m;
											}
										}
									}
									$update_sql['Color'].='end';
									++$j;
								}
							}
							++$i;
						}
						$i=0;
						foreach((array)$insert_ary['Desc'] as $k=>$v){//目前只单单针对详细介绍
							$insert_sql['Desc'].=($i?',':'')."('{$k}',{$v})";
							++$i;
						}
						$seid_where && db::update('products_selected_attribute', "SeleteId in($seid_where)", array('IsUsed'=>0)); //先默认全部关闭
						($update_sql['Seo'] && $sid_where) && db::query("update products_seo set".$update_sql['Seo']." where SId in({$sid_where})");
						($update_sql['Desc'] && $did_where) && db::query("update products_description set".$update_sql['Desc']." where DId in($did_where)");
						($update_sql['Tab_0'] && $did_where) && db::query("update products_description set".$update_sql['Tab_0']." where DId in($did_where)");
						($update_sql['Tab_1'] && $did_where) && db::query("update products_description set".$update_sql['Tab_1']." where DId in($did_where)");
						($update_sql['Tab_2'] && $did_where) && db::query("update products_description set".$update_sql['Tab_2']." where DId in($did_where)");
						($update_sql['Attr'] && $seid_where) && db::query("update products_selected_attribute set".$update_sql['Attr']." where SeleteId in($seid_where)");
						($update_sql['ExtAttr'] && $comid_where && $ExtAttr_update) && db::query("update products_selected_attribute_combination set".$update_sql['ExtAttr']." where CId in($comid_where)");
						($update_sql['Color'] && $cid_where) && db::query("update products_color set".$update_sql['Color']." where CId in($cid_where)");
						$insert_sql['Desc'] && db::query("insert into products_description (ProId, Description_{$language}, TabName_0_{$language}, Tab_0_{$language}, TabName_1_{$language}, Tab_1_{$language}, TabName_2_{$language}, Tab_2_{$language}) values".$insert_sql['Desc']);
						$insert_sql['Attr'] && db::query("insert into products_selected_attribute (ProId, AttrId, VId, OvId, Value, IsUsed) values".$insert_sql['Attr']);
						$insert_sql['ExtAttr'] && db::query("insert into products_selected_attribute_combination (ProId, Combination, OvId, SKU, IsIncrease, Price, Stock, Weight) values".$insert_sql['ExtAttr']);
						$insert_sql['Color'] && db::query("insert into products_color (ProId, VId, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9) values".$insert_sql['Color']);
						//删除原来保存的产品颜色图片
						foreach((array)$pic_color_ary as $v){
							foreach((array)$v as $v2){
								if(!$v2 || in_array($v2, $un_pic_color_ary)) continue;//没有数据 或者 存在于不删除原图片数组
								foreach($resize_ary as $v3){
									$ext_name=file::get_ext_name($v2);
									file::del_file($v2.".{$v3}.{$ext_name}");
								}
								file::del_file($v2);
							}
						}
					}else{//插入数据库
						$insert_sql['Seo']=$insert_sql['Desc']=$insert_sql['Attr']=$insert_sql['ExtAttr']=$insert_sql['Color']='';
						$i=0;
						$len=count($database_sql);
						foreach((array)$database_sql as $k=>$v){
							$id=$proid_ary[$k];//产品ID
							foreach((array)$v as $k2=>$v2){
								if($k2=='Seo'){//SEO
									$insert_sql['Seo'].=($i?',':'')."({$id}";
									foreach((array)$v2 as $k3=>$v3){
										$insert_sql['Seo'].=",'{$v3}'"; 
									}
									$insert_sql['Seo'].=')';
								}
								if($k2=='Desc' || $k2=='Tab_0' || $k2=='Tab_1' || $k2=='Tab_2'){//详细介绍，选项卡1，选项卡2，选项卡3
									if($k2=='Desc') $insert_sql['Desc'].=($i?',':'')."({$id}";
									foreach((array)$v2 as $k3=>$v3){
										$insert_sql['Desc'].=",'{$v3}'"; 
									}
									if($k2=='Tab_2') $insert_sql['Desc'].=')';
								}
								if($k2=='Attr'){//产品属性
									$j=0;
									foreach((array)$v2 as $k3=>$v3){
										if($k3=='Oversea' || $attribute_ary[$k3][0]==1){//海外仓 or 列表选择
											foreach($v3 as $v4){
												$OvId=1;
												if($k3=='Oversea'){//海外仓
													$OvId=$v4;
													$k3=$v4=0;
												}
												$insert_sql['Attr'].=(($i || $j)?',':'')."({$id}, {$k3}, '{$v4}', '{$OvId}', '', 1)"; 
												++$j;
											}
										}else{//文本框
											$insert_sql['Attr'].=(($i || $j)?',':'')."({$id}, {$k3}, 0, 1, '".addslashes($v3)."', 1)"; 
											++$j;
										}
										++$j;
									}
								}
								if($k2=='ExtAttr'){//产品组合属性关联项
									$j=0;
									foreach((array)$v2 as $k3=>$v3){//$k3=Combination
										foreach($v3 as $k4=>$v4){//$k4=OvId
											$k4<1 && $k4=1;
											$insert_sql['ExtAttr'].=(($i || $j)?',':'')."({$id}, '{$k3}', '{$k4}', '{$v4[3]}', '{$v4[4]}', '{$v4[0]}', '{$v4[1]}', '{$v4[2]}')"; 
											++$j;
										}
									}
								}
								if($k2=='Color'){//颜色图片
									$j=0;
									foreach((array)$v2 as $k3=>$v3){
										$k3<1 && $k3=1;
										$pic_ary=self::upload_picture_color(array($v3[0], $v3[1], $v3[2], $v3[3], $v3[4], $v3[5], $v3[6], $v3[7], $v3[8], $v3[9]), $save_dir, $resize_ary);
										$insert_sql['Color'].=(($i || $j)?',':'')."({$id}, '{$k3}', '{$pic_ary[0]}', '{$pic_ary[1]}', '{$pic_ary[2]}', '{$pic_ary[3]}', '{$pic_ary[4]}', '{$pic_ary[5]}', '{$pic_ary[6]}', '{$pic_ary[7]}', '{$pic_ary[8]}', '{$pic_ary[9]}')"; 
										++$j;
									}
								}
							}
							++$i;
						}
						$insert_sql['Seo'] && db::query("insert into products_seo (ProId, SeoTitle_{$language}, SeoKeyword_{$language}, SeoDescription_{$language}) values".$insert_sql['Seo']);
						$insert_sql['Desc'] && db::query("insert into products_description (ProId, Description_{$language}, TabName_0_{$language}, Tab_0_{$language}, TabName_1_{$language}, Tab_1_{$language}, TabName_2_{$language}, Tab_2_{$language}) values".$insert_sql['Desc']);
						$insert_sql['Attr'] && db::query("insert into products_selected_attribute (ProId, AttrId, VId, OvId, Value, IsUsed) values".$insert_sql['Attr']);
						$insert_sql['ExtAttr'] && db::query("insert into products_selected_attribute_combination (ProId, Combination, OvId, SKU, IsIncrease, Price, Stock, Weight) values".$insert_sql['ExtAttr']);
						$insert_sql['Color'] && db::query("insert into products_color (ProId, VId, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9) values".$insert_sql['Color']);
					}
				}
			}
		}
		if($p_Number<$total_pages){//继续执行
			$item=($No+1<$page_count)?($page_count*$p_Number+$No):($page_count*($p_Number+1));
			ly200::e_json(array(($p_Number+1), $errerTxt.'<p>已上传'.$item.'个</p>'), 2);
		}
	}
	
	//颜色图片上传
	public static function upload_picture_color($pic_ary, $save_dir, $resize_ary){
		global $c;
		$result=array();
		foreach((array)$pic_ary as $k=>$v){
			$water_ary=array();
			$filepath='/tmp/madeimg/'.$v;
			$ext_name=file::get_ext_name($filepath);//图片后缀名
			$new_path=$save_dir.str::rand_code().'.'.$ext_name;
			if(is_file($c['root_path'].$filepath)){//检查图片是否存在
				@copy($c['root_path'].$filepath, $c['root_path'].$new_path);//先把目标图片复制到u_file
				if($c['manage']['config']['IsWater']) $water_ary[]=$new_path;
				if($resize_ary){
					if(in_array('default', $resize_ary)){//保存不加水印的原图
						@copy($new_path, $new_path.".default.{$ext_name}");
					}
					if($c['manage']['config']['IsWater'] && $c['manage']['config']['IsThumbnail']){//缩略图加水印
						img::img_add_watermark($new_path);
						$water_ary=array();
					}
					foreach((array)$resize_ary as $value){
						if($value=='default') continue;
						$size_w_h=explode('x', $value);
						$resize_path=img::resize($new_path, $size_w_h[0], $size_w_h[1]);
					}
				}
				foreach((array)$water_ary as $value){
					img::img_add_watermark($value);
				}
				$result[$k]=$new_path;
				foreach($resize_ary as $value){
					if(!is_file($c['root_path'].$result[$k].".{$value}.{$ext_name}")){
						$size_w_h=explode('x', $value);
						$resize_path=img::resize($result[$k], $size_w_h[0], $size_w_h[1]);
					}
				}
				if(!is_file($c['root_path'].$result[$k].".default.{$ext_name}")){
					@copy($c['root_path'].$result[$k], $c['root_path'].$result[$k].".default.{$ext_name}");
				}
			}else{
				$result[$k]='';
			}
		}
		return $result;
	}
	
	//自动组合规格属性的关联项
	public static function check_cart_attr($check_ary, $key_ary, $num, $ary=array()){
		$_arr=array();
		$count=count($check_ary);
		if($num==0){
			foreach((array)$check_ary[$key_ary[$num]] as $v){
				$ary[]=$v;
			}
		}else{
			foreach((array)$ary as $v){
				foreach((array)$check_ary[$key_ary[$num]] as $v2){
					$_arr[]=$v.'|'.$v2;
				}
			}
			$ary=$_arr;
		}
		++$num;
		if($num<$count){
			return self::check_cart_attr($check_ary, $key_ary, $num, $ary);
		}else{
			return $ary;
		}
	}
	
	public static function upload_new_excel_download(){
		global $c;
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		$objPHPExcel=new PHPExcel();
		
		//Set properties 
		$objPHPExcel->getProperties()->setCreator("Sheldon");//创建者
		$objPHPExcel->getProperties()->setLastModifiedBy("Sheldon");//最后修改者
		$objPHPExcel->getProperties()->setTitle("Products Upload");//标题
		$objPHPExcel->getProperties()->setSubject("Products Upload");//主题
		$objPHPExcel->getProperties()->setKeywords("Products Upload");//标记
		$objPHPExcel->getProperties()->setDescription('Products Upload');//备注
		$objPHPExcel->getProperties()->setCategory("Products Upload");//类别
		
		//Add some data
		//(A ~ EZ)
		$arr=range('A', 'Z');
		$ary=$arr;
		for($i=0; $i<10; ++$i){
			$num=$arr[$i];
			foreach($arr as $v){
				$ary[]=$num.$v;
			}
		}
		$m_lang=$c['manage']['config']['ManageLanguage']=='en'?0:1;
		
		$fixed_ary=array();
		$fixed_ary[]=array($m_lang?'产品名称':'Name', 'test');
		$fixed_ary[]=array($m_lang?'产品分类':'Category', '304');
		$fixed_ary[]=array($m_lang?'产品多分类':'Category List', '334,202,247');
		$fixed_ary[]=array($m_lang?'产品编号':'Serial Number', 'EZ019384');
		$fixed_ary[]=array('SKU', 'EZ019384');
		$fixed_ary[]=array($m_lang?'供货商':'Supplier', 'test');
		$fixed_ary[]=array($m_lang?'市场价':'Market Price', '21.50');
		$fixed_ary[]=array($m_lang?'商城价':'Shop Price', '14.99');
		$fixed_ary[]=array($m_lang?'进货价':'Purchase Price', '12.99');
		$fixed_ary[]=array($m_lang?'批发价':'Whole Sale Price', '5:29.5,10:28,50:25');
		$fixed_ary[]=array($m_lang?'开启促销':'Open Promotion', '1');
		$fixed_ary[]=array($m_lang?'促销价格':'Promotion Price', '10.50');
		$fixed_ary[]=array($m_lang?'促销折扣':'Promotion Discount', '46');
		$fixed_ary[]=array($m_lang?'促销时间':'Promotion time', '2012/11/10 11:51:04 - 2019/11/10 11:51:04');
		$fixed_ary[]=array($m_lang?'自定义地址':'Custom Link', 'about-us');
		$fixed_ary[]=array($m_lang?'图片':'Picture', '001.jpg', '002.jpg', '003.jpg', '004.jpg', '005.jpg', '006.jpg', '007.jpg', '008.jpg', '009.jpg', '010.jpg');
		$fixed_ary[]=array($m_lang?'属性组合':'Combination', '1');
		$fixed_ary[]=array($m_lang?'属性':'Attribute', 'Color[black,white,blue]', 'Size[S,M,XL]');
		$fixed_ary[]=array($m_lang?'属性关联项':'Attribute Association', 'Color[black]Size[S]=5.99,999,0.523,EZ019350,1', 'Color[black]Size[M]=5.99,999,0.523,EZ019351,1', 'Color[black]Size[XL]=5.99,999,0.523,EZ019352,1', 'Color[white]Size[S]=5.99,999,0.523,EZ019353,1', 'Color[white]Size[M]=5.99,999,0.523,EZ019354,1', 'Color[white]Size[XL]=5.99,999,0.523,EZ019355,1', 'Color[blue]Size[S]=5.99,999,0.523,EZ019356,1', 'Color[blue]Size[M]=5.99,999,0.523,EZ019357,1', 'Color[blue]Size[XL]=5.99,999,0.523,EZ019358,1');
		$fixed_ary[]=array($m_lang?'颜色关联项':'Color Association', 'Color[black]=101.jpg,102.jpg,103.jpg', 'Color[white]=101.jpg,102.jpg,103.jpg', 'Color[blue]=101.jpg,102.jpg,103.jpg');
		$fixed_ary[]=array($m_lang?'重量':'Weight', '90');
		$fixed_ary[]=array($m_lang?'体积':'Volume', '10*20*5');
		$fixed_ary[]=array($m_lang?'体积重':'Volume weight', '0');
		$fixed_ary[]=array($m_lang?'起订量':'Minimum Order Quantity', '1');
		$fixed_ary[]=array($m_lang?'最大购买量':'Maximum Order Quantity', '999');
		$fixed_ary[]=array($m_lang?'产品销量':'Product Sale', '826');
		$fixed_ary[]=array($m_lang?'库存':'Inventory', '999');
		$fixed_ary[]=array($m_lang?'警告库存':'Warning inventory', '5');
		$fixed_ary[]=array($m_lang?'脱销状态':'Out-of-stock status', '0');
		$fixed_ary[]=array($m_lang?'下架':'Off Sales', '0');
		$fixed_ary[]=array($m_lang?'定时上架':' Timing Sales', '0');
		$fixed_ary[]=array($m_lang?'定时上架时间':'On SalesTime', '2012/11/10 11:51:04 - 2019/11/10 11:51:04');
		$fixed_ary[]=array($m_lang?'免运费':'Freight Free', '1');
		$fixed_ary[]=array($m_lang?'收藏数':'Collection Number', '22');
		$fixed_ary[]=array($m_lang?'新品':'New Product', '1');
		$fixed_ary[]=array($m_lang?'热卖':'Hot Product', '1');
		$fixed_ary[]=array($m_lang?'畅销':'Best Selling', '1');
		$fixed_ary[]=array($m_lang?'首页显示':'Home Page', '1');
		$fixed_ary[]=array($m_lang?'开启默认评论':'Default Comment', '1');
		$fixed_ary[]=array($m_lang?'默认评论平均分':'Default average score of comment', '5');
		$fixed_ary[]=array($m_lang?'默认评论人数':'Default member of comment', '8');
		$lang_ary=array($m_lang?'标题':'Title', $m_lang?'关键字':'Keywords', $m_lang?'描述':'Sketch', $m_lang?'简短介绍':'Brief Introduction', $m_lang?'详细介绍':'Detailed Introduction', $m_lang?'选项卡1':'Tab1', $m_lang?'选项卡2':'Tab2', $m_lang?'选项卡3':'Tab3');
		for($i=0; $i<count($lang_ary); ++$i){
			$fixed_ary[]=array($lang_ary[$i], 'test');
		}
		$fixed_number=count($fixed_ary);
		
		//按语言版本
		$objPHPExcel->setActiveSheetIndex(0);//设置当前的sheet
		$objPHPExcel->getActiveSheet()->setTitle('批量上传');//设置sheet的name
		
		$colmun_ary=$fixed_ary;
		$attr_column=array();//初始化
		
		foreach($colmun_ary as $k=>$v){//固定项
			$attr_column[$k]=$v[0];
		}
		ksort($attr_column);
		
		foreach($attr_column as $k=>$v){
			//设置单元格的值(第一二行标题)
			$objPHPExcel->getActiveSheet()->setCellValue($ary[$k].'1', $v);
			$objPHPExcel->getActiveSheet()->getStyle($ary[$k].'1')->getAlignment()->setWrapText(true);//自动换行
			$objPHPExcel->getActiveSheet()->getStyle($ary[$k].'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
			
			//设置单元格的值
			for($i=0; $i<10; ++$i){
				$value=$colmun_ary[$k][$i+1];
				$objPHPExcel->getActiveSheet()->setCellValue($ary[$k].($i+2), $value);
			}
			
			//设置列的宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension($ary[$k])->setWidth($k>16 && $k<20?30:13);
		}
		
		//设置行的高度
		$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(40);
		$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);//默认行高
		
		$objPHPExcel->setActiveSheetIndex(0);//指针返回第一个工作表
		
		//保存Excel文件
		$ExcelName='upload_'.str::rand_code();
		$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
		
		file::down_file("/tmp/{$ExcelName}.xls");
		file::del_file("/tmp/{$ExcelName}.xls");
		unset($c, $objPHPExcel, $ary, $attr_column, $fixed_ary);
		exit;
	}
	
	public static function watermark_update(){	//批量更新水印
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;//当前分类
		$p_Number=(int)$p_Number;//当前分开数
		
		if($CateId){
			$where="CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($CateId)."%') or CateId='{$CateId}'";
			$prod_count=db::get_row_count('products', $where);//产品总数
		}
		
		//初始化
		$Start=0;//开始执行位置
		$page_count=10;//每次分开更新的数量
		$total_pages=ceil($prod_count/$page_count);
		if($p_Number<$total_pages){//继续执行
			$Start=$page_count*$p_Number;
		}else{
			manage::operation_log('产品水印图片批量更新');
			ly200::e_json('<p>批量更新完成</p>', 1);
		}
		$data_ary=$pro_id_ary=array();
		$products_row=db::get_limit('products', $where, 'ProId, PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, PicPath_6, PicPath_7, PicPath_8, PicPath_9', 'ProId desc', $Start, $page_count);
		foreach($products_row as $v){
			$pro_id_ary[]=$v['ProId'];
			$data_ary[$v['ProId']]['pro']=$v;
		}
		$products_color_row=db::get_all('products_color', 'ProId in ('.implode(',', $pro_id_ary).')', '*', 'ProId desc');
		foreach($products_color_row as $v){
			$data_ary[$v['ProId']]['color'][$v['ColorId']]=$v;
		}
		//图片储存位置
		$resize_ary=$c['manage']['resize_ary']['products'];
		$save_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		//开始更新
		$No=0;
		foreach((array)$data_ary as $ProId=>$obj){
			$ImgPath=$CPath=array();
			foreach($obj['pro'] as $key=>$val){	//产品主图片
				if($key=='ProId') continue;
				if(!is_file($c['root_path'].$val)) continue;
				//图片上传
				$ImgPath[]=$val;
				$water_ary=array($val);
				$ext_name=file::get_ext_name($val);
				@copy($c['root_path'].$val.".default.{$ext_name}", $c['root_path'].$val);//覆盖大图
				if($c['manage']['config']['IsThumbnail']){//缩略图加水印
					img::img_add_watermark($val);
					$water_ary=array();
				}
				foreach($resize_ary as $v2){
					if($v2=='default') continue;
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($val, $size_w_h[0], $size_w_h[1]);
				}
				foreach((array)$water_ary as $v2){
					img::img_add_watermark($v2);
				}
			}
			foreach((array)$obj['color'] as $ColorId=>$row){	//产品颜色图片
				foreach($row as $key=>$val){
					if($key=='CId' || $key=='ProId' || $key=='ColorId') continue;
					if(!is_file($c['root_path'].$val)) continue;
					$CPath[$ColorId][]=$val;
					$water_ary=array($val);
					$ext_name=file::get_ext_name($val);
					@copy($c['root_path'].$val.".default.{$ext_name}", $c['root_path'].$val);//覆盖大图
					if($c['manage']['config']['IsThumbnail']){//缩略图加水印
						img::img_add_watermark($val);
						$water_ary=array();
					}
					foreach($resize_ary as $v2){
						if($v2=='default') continue;
						$size_w_h=explode('x', $v2);
						$resize_path=img::resize($val, $size_w_h[0], $size_w_h[1]);
					}
					foreach((array)$water_ary as $v2){
						img::img_add_watermark($v2);
					}
				}
			}
			++$No;
		}
		if($p_Number<$total_pages){//继续执行
			$item=($No<$page_count)?($page_count*$p_Number+$No):($page_count*($p_Number+1));
			ly200::e_json(array(($p_Number+1), '<p>已更新'.$item.'个</p>'), 2);
		}
	}
		
	public static function set(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		manage::config_operaction(array('Config'=>$p_Checked), 'products_show');
		manage::operation_log('修改产品显示设置');
		ly200::e_json('', 1);
	}
	
	public static function set_ext(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		manage::config_operaction(array($p_Name=>$p_Checked), 'products_show');
		manage::operation_log('修改'.manage::language('{/products.show.'.$p_Name.'/}').'显示设置');
		ly200::e_json($p_Checked, 1);
	}
	
	public static function set_products_number_prefix(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		manage::config_operaction(array('myorder'=>$p_Value), 'products_show');
		manage::operation_log('修改产品编号前缀');
		ly200::e_json('提交成功', 1);
	}
	
	public static function set_products_favorite_range(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$date_ary=array($p_Value0, $p_Value1);
		$Value=addslashes(str::json_data(str::str_code($date_ary, 'stripslashes')));
		manage::config_operaction(array('favorite'=>$Value), 'products_show');
		manage::operation_log('修改产品收藏自定义范围');
		ly200::e_json('提交成功', 1);
	}
	
	public static function set_products_item(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$unit_list=str::json_data(htmlspecialchars_decode(db::get_value('config', 'GroupId="products" and Variable="Unit"', 'Value')), 'decode');
		if(!in_array($p_Value, $unit_list)){ //追加新的单位
			$unit_list[]=$p_Value;
			$json_unit=addslashes(str::json_data(str::str_code($unit_list, 'stripslashes')));
			manage::config_operaction(array('Unit'=>$json_unit), 'products');
		}
		manage::config_operaction(array('item'=>$p_Value), 'products_show');
		manage::operation_log('修改产品自定义单位');
		ly200::e_json('提交成功', 1);
	}
	
	/***********************************************************数据同步(start)*******************************************************************/
	/*****************************************
	速卖通同步部分(start)
	*****************************************/
	public static function change_aliexpress_authorization_account(){//切换当前速卖通账号
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$AccountId=(int)$p_AccountId;
		!$AccountId && ly200::e_json('请选择切换的账号！');
		$AccountId==$_SESSION['Manage']['Aliexpress']['Token']['AuthorizationId'] && ly200::e_json('');
		
		aliexpress::set_default_authorization($AccountId);
		ly200::e_json('', 1);
	}

	public static function aliexpress_products_sync(){//同步产品
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');

		$ConditionAry=array(
			//onSelling、offline、auditing、editingRequired
			'productStatusType'	=>	@in_array($p_productStatusType, $c['manage']['sync_ary']['aliexpress'])?$p_productStatusType:'onSelling'
		);
		(int)$p_GroupId && $ConditionAry['groupId']=(int)$p_GroupId;
		$Account=aliexpress::set_default_authorization();
		$data=array(
			'ApiKey'		=>	'ueeshop_sync',
			'Action'		=>	'sync_products',
			'ApiName'		=>	'aliexpress',
			'Number'		=>	$c['Number'],
			'Account'		=>	$Account,
			'ConditionInfo'	=>	str::json_data($ConditionAry),
			'notify_url'	=>	ly200::get_domain().'/gateway/',
			'timestamp'		=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		if($result['ret']==1){
			$TaskId=$result['msg']['TaskId'];
			$taskData=array(
				'Platform'	=>	'aliexpress',
				'TaskId'	=>	$TaskId,
				'AccTime'	=>	time()
			);
			!db::get_row_count('products_sync_task', "Platform='aliexpress' and TaskId='{$TaskId}'") && db::insert('products_sync_task', $taskData);
		}
		
		ly200::e_json($TaskId?array('TaskId'=>$TaskId):$result['msg'], (int)$result['ret']);
	}
	
	public static function aliexpress_products_sync_status(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$TaskId=(int)$p_TaskId;
		!$TaskId && ly200::e_json('');
		
		$row=db::get_one('products_sync_task', "Platform='aliexpress' and TaskId='$TaskId'");
		$data=array(
			'status'	=>	$row['TaskStatus'],
			'tips'		=>	($row['TaskStatus']==2?'产品数据已同步完成！<a class="close">关闭</a>':('同步任务完成：'.$row['CompletionRate'].'%'))
		);
		
		ly200::e_json($data, 1);
	}
	
	public static function aliexpress_grouplist_sync(){//同步产品分组
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$data=array(
			'ApiKey'	=>	'ueeshop_sync',
			'ApiName'	=>	'aliexpress',
			'Action'	=>	'sync_grouplist',
			'Number'	=>	$c['Number'],
			'Account'	=>	$_SESSION['Manage']['Aliexpress']['Token']['Account'],
			'notify_url'=>	ly200::get_domain().'/gateway/',
			'timestamp'	=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');

		$result['ret']==1 && $groupHtml=self::get_group_list($_SESSION['Manage']['Aliexpress']['Token']['Account'], (int)$p_GroupId);
		ly200::e_json($groupHtml, $result['ret']);
	}
	
	public static function get_group_list($Account, $GroupId){//分类下拉内容
		global $c;
		$group_row=str::str_code(db::get_all('products_aliexpress_grouplist', "Account='{$Account}'"));
		foreach($group_row as $v){
			if($v['UpperAliexpressGroupId']){
				$group_data[$v['UpperAliexpressGroupId']]['childGroup'][]=$v;
			}else{
				$group_data[$v['AliexpressGroupId']]=$v;
			}
		}
		$groupData="<option value=''>{$c['manage']['lang_pack']['global']['select_index']}</option>";
		foreach((array)$group_data as $v){
			if(@count((array)$v['childGroup'])){
				$groupData.="<optgroup label=\"{$v['AliexpressGroupName']}\">";
				foreach((array)$v['childGroup'] as $val){
					$selected=((int)$GroupId==$val['AliexpressGroupId']?' selected':'');
					$groupData.="<option value=\"{$val['AliexpressGroupId']}\"{$selected}>{$val['AliexpressGroupName']}</option>";
				}
				$groupData.="</optgroup>";
			}else{
				$selected=((int)$GroupId==$v['AliexpressGroupId']?' selected':'');
				$groupData.="<option value=\"{$v['AliexpressGroupId']}\"{$selected}>{$v['AliexpressGroupName']}</option>";
			}
		}
		return $groupData;
	}
	
	public static function aliexpress_service_template_sync(){//同步服务模板
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$Account=aliexpress::set_default_authorization();
		$data=array(
			'ApiKey'	=>	'ueeshop_sync',
			'ApiName'	=>	'aliexpress',
			'Action'	=>	'sync_service_template',
			'Number'	=>	$c['Number'],
			'Account'	=>	$Account,
			'notify_url'=>	ly200::get_domain().'/gateway/',
			'timestamp'	=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		
		if($result['ret']==1){
			$service_row=str::str_code(db::get_all('products_aliexpress_service_template', "Account='{$Account}'"));
			$serviceHtml=ly200::form_select($service_row, 'promiseTemplateId', $p_ServiceId, 'Name', 'templateId', $c['manage']['lang_pack']['global']['select_index'], 'notnull');
		}
		ly200::e_json($serviceHtml, $result['ret']);
	}
	
	public static function aliexpress_freight_template_sync(){//同步运费模板
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$Account=aliexpress::set_default_authorization();
		$data=array(
			'ApiKey'	=>	'ueeshop_sync',
			'ApiName'	=>	'aliexpress',
			'Action'	=>	'sync_freight_template',
			'Number'	=>	$c['Number'],
			'Account'	=>	$Account,
			'notify_url'=>	ly200::get_domain().'/gateway/',
			'timestamp'	=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		
		if($result['ret']==1){
			$freight_row=str::str_code(db::get_all('products_aliexpress_freight_template', "Account='{$Account}'"));
			$freightHtml=ly200::form_select($freight_row, 'freightTemplateId', $p_FId, 'templateName', 'templateId', $c['manage']['lang_pack']['global']['select_index'], 'notnull');
		}
		ly200::e_json($freightHtml, $result['ret']);
	}
	
	public static function aliexpress_sizechart_template_sync(){//同步尺码表
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		!(int)$p_categoryId && ly200::e_json('');
		
		$Account=aliexpress::set_default_authorization();
		$data=array(
			'ApiKey'	=>	'ueeshop_sync',
			'ApiName'	=>	'aliexpress',
			'Action'	=>	'sync_sizechart_template',
			'Number'	=>	$c['Number'],
			'Account'	=>	$Account,
			'categoryId'=>	$p_categoryId,
			'notify_url'=>	ly200::get_domain().'/gateway/',
			'timestamp'	=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		
		ly200::e_json('', 1);
	}

	public static function aliexpress_load_account(){	//速卖通账号相关信息
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$group_data=array();

		$groupHtml=self::get_group_list($p_Account, (int)$p_GroupId);
		
		$freight_row=str::str_code(db::get_all('products_aliexpress_freight_template', "Account='{$p_Account}'"));
		$freightHtml=ly200::form_select($freight_row, 'freightTemplateId', $p_freightId, 'templateName', 'templateId', $c['manage']['lang_pack']['global']['select_index'], 'notnull');
		
		$service_row=str::str_code(db::get_all('products_aliexpress_service_template', "Account='{$p_Account}'"));
		$serviceHtml=ly200::form_select($service_row, 'promiseTemplateId', $p_serviceId, 'Name', 'templateId', $c['manage']['lang_pack']['global']['select_index'], 'notnull');
		
		ly200::e_json(array($groupHtml, $freightHtml, $serviceHtml), 1);
	}
	
	public static function sync_aliexpress_products_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');

		/*******************图片上传(end)*******************/
		//产品主图 6 张
		$ImgPath=$CustomImgPath=array();
		$resize_ary=$c['manage']['resize_ary']['products'];
		$save_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		foreach((array)$p_PicPath as $k=>$v){
			$ext_name=file::get_ext_name($v);
			if(!is_file($c['root_path'].$v)) continue;	//速卖通仅允许jpg图片 || $ext_name!='jpg'
			$ImgPath[]=file::photo_tmp_upload($v, $save_dir, $resize_ary);
		}
		if(!count($ImgPath)) ly200::e_json(manage::get_language('products.products.pic_tips'));
		foreach((array)$ImgPath as $k=>$v){
			$ext_name=file::get_ext_name($v);
			foreach($resize_ary as $v2){
				if(!is_file($c['root_path'].$v.".{$v2}.{$ext_name}")){
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
				}
			}
			if(!is_file($c['root_path'].$v.".default.{$ext_name}")){
				@copy($c['root_path'].$v, $c['root_path'].$v.".default.{$ext_name}");
			}
		}
		
		//自定义图片
		foreach((array)$p_ImagePath as $k=>$v){
			$ext_name=file::get_ext_name($v);
			if(!is_file($c['root_path'].$v)) continue;	//速卖通仅允许jpg图片 || $ext_name!='jpg'
			$CustomImgPath[$k]=file::photo_tmp_upload($v, $save_dir, $resize_ary);
		}
		foreach((array)$CustomImgPath as $k=>$v){
			$ext_name=file::get_ext_name($v);
			foreach($resize_ary as $v2){
				if(!is_file($c['root_path'].$v.".{$v2}.{$ext_name}")){
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
				}
			}
			if(!is_file($c['root_path'].$v.".default.{$ext_name}")){
				@copy($c['root_path'].$v, $c['root_path'].$v.".default.{$ext_name}");
			}
		}
		/*******************图片上传(end)*******************/
		
		/*******************属性部分(start)*******************/
		$PropertysData=$SKUsData=$sku_key_list=array();
		//普通属性
		foreach((array)$p_property as $k=>$v){
			foreach((array)$v as $key=>$val){
				if(!$val) continue;
				if(@is_numeric($key)){
					$PropertysData[]=array('attrValueId'=>$val, 'attrNameId'=>$k);
				}else{
					$PropertysData[]=array('attrNameId'=>$k, 'attrValue'=>$val);
				}
			}
		}
		foreach((array)$p_attrName as $k=>$v){
			$PropertysData[]=array('attrName'=>$v, 'attrValue'=>$p_attrValue[$k]);
		}
		$aeopAeProductPropertys=str::json_data($PropertysData);
		
		//SKU属性
		foreach((array)$p_sku as $k=>$v){
			foreach((array)$v as $val){
				$sku_key_list[$val]="{$k}:{$val}";
			}
		}
		foreach((array)$p_skuPrice as $k=>$v){
			if($k=='XXX') continue;
			$idList=$attrList=array();
			$key_list=@explode('_', $k);
			foreach($key_list as $val){
				$propertyList=array(
					'propertyValueId'	=>	$val,
					'skuPropertyId'		=>	@reset(explode(':', $sku_key_list[$val]))
				);
				$CustomImgPath[$val] && $propertyList['skuImage']=$CustomImgPath[$val];
				$p_CustomName[$val] && $propertyList['propertyValueDefinitionName']=$p_CustomName[$val];
				
				$attrList[]=$propertyList;
				$idList[]=$sku_key_list[$val];
			}
			$data_ary=array(
				'id'				=>	@implode(';', $idList),
				'currencyCode'		=>	'USD',
				'ipmSkuStock'		=>	(int)$p_skuStock[$k],
				'skuPrice'			=>	(float)$v,
				'skuStock'			=>	(int)$p_skuStock[$k]?1:0,
				'aeopSKUProperty'	=>	$attrList,
				'skuCode'			=>	$p_skuCode[$k]
			);
			$SKUsData[]=$data_ary;
		}
		!count($SKUsData) && $SKUsData=array(
			array(
				'id'				=>	'<none>',
				'currencyCode'		=>	'USD',
				'ipmSkuStock'		=>	(int)$p_ipmSkuStock,
				'skuPrice'			=>	(float)$p_productPrice,
				'skuStock'			=>	(int)$p_ipmSkuStock?1:0,
				'aeopSKUProperty'	=>	array(),
				'skuCode'			=>	$p_ipmSkuCode
			)
		);
		$aeopAeProductSKUs=str::json_data($SKUsData);
		/*******************属性部分(end)*******************/

		$data=array(
			'Account'			=>	$p_Account,
			'categoryId'		=>	(int)$p_categoryId,
			'GroupId'			=>	(int)$p_GroupId,
			'GroupIds'			=>	str::json_data(array((int)$p_GroupId)),
			'Subject'			=>	addslashes(stripslashes($p_Subject)),
			'productPrice'		=>	$SKUsData[0]['skuPrice'],
			'PicPath_0'			=>	$ImgPath[0],
			'PicPath_1'			=>	$ImgPath[1],
			'PicPath_2'			=>	$ImgPath[2],
			'PicPath_3'			=>	$ImgPath[3],
			'PicPath_4'			=>	$ImgPath[4],
			'PicPath_5'			=>	$ImgPath[5],
			'aeopAeProductPropertys'	=>	$aeopAeProductPropertys,
			'aeopAeProductSKUs'	=>	$aeopAeProductSKUs,
			'deliveryTime'		=>	(int)$p_deliveryTime,
			'packageType'		=>	(int)$p_packageType,
			'packageLength'		=>	(int)$p_packageLength,
			'packageWidth'		=>	(int)$p_packageWidth,
			'packageHeight'		=>	(int)$p_packageHeight,
			'grossWeight'		=>	(float)$p_grossWeight,
			'isPackSell'		=>	(int)$p_isPackSell,
			'reduceStrategy'	=>	$p_reduceStrategy,
			'productUnit'		=>	(int)$p_productUnit,
			'wsValidNum'		=>	(int)$p_wsValidNum,
			'freightTemplateId'	=>	(int)$p_freightTemplateId,
			'promiseTemplateId'	=>	(int)$p_promiseTemplateId,					//服务模板
			'gmtModified'		=>	$c['time'],									//商品最后更新时间
		);
		(int)$p_packageType && $data['lotNum']=(int)$p_lotNum;//销售方式：打包出售
		if((int)$p_isPackSell){//自定义计重
			$data['baseUnit']=(int)$p_baseUnit;
			$data['addUnit']=(int)$p_addUnit;
			$data['addWeight']=(float)$p_addWeight;
		}
		if((int)$p_IsWholeSale){//批发价设置
			$data['bulkOrder']=(int)$p_bulkOrder;
			$data['bulkDiscount']=(int)$p_bulkDiscount;
		}
		
		$ProId=(int)$p_ProId;
		if($ProId){
			db::update('products_aliexpress', "ProId='$ProId'", $data);
			if(!db::get_row_count('products_aliexpress_description', "ProId='$ProId'")){
				db::insert('products_aliexpress_description', array(
						'Account'		=>	$p_Account,	
						'ProId'			=>	$ProId,
						'productId'		=>	db::get_value('products_aliexpress', "ProId='$ProId'", 'productId'),
						'Detail'		=>	$p_Detail,
						'mobileDetail'	=>	$p_mobileDetail
					)
				);
			}else{
				db::update('products_aliexpress_description', "ProId='$ProId'", array(
						'Account'		=>	$p_Account,
						'Detail'		=>	$p_Detail,
						'mobileDetail'	=>	$p_mobileDetail
					)
				);
			}
			manage::operation_log('修改速卖通产品');
		}else{
			$data['gmtCreate']=$c['time'];
			db::insert('products_aliexpress', $data);
			$ProId=db::get_insert_id();
			db::insert('products_aliexpress_description', array(
					'Account'		=>	$p_Account,	
					'ProId'			=>	$ProId,
					'Detail'		=>	$p_Detail,
					'mobileDetail'	=>	$p_mobileDetail
				)
			);
			manage::operation_log('添加速卖通产品');
		}

		ly200::e_json(array('jump'=>$p_back_action?$p_back_action:"?m=products&a=sync&d=aliexpress"), 1);
	}
	
	public static function aliexpress_products_img_del(){	//删除单个产品图片
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$Model=$g_Model;
		$PicPath=$g_Path;
		$Index=(int)$g_Index;
		$resize_ary=$c['manage']['resize_ary'][$Model];	//products
		if(is_file($c['root_path'].$PicPath)){
			foreach($resize_ary as $v){
				$ext_name=file::get_ext_name($PicPath);
				file::del_file($PicPath.".{$v}.{$ext_name}");
			}
			file::del_file($PicPath);
			aliexpress::remove_aliexpress_images($PicPath);
		}
		manage::operation_log('删除速卖通产品图片');
		ly200::e_json(array($Index), 1);
	}
	
	public static function aliexpress_get_category_list_by_UId($UId){
		if(!$UId) return;
		
		$str='';
		$row=db::get_all('products_aliexpress_category', "AliexpressCateId in({$UId})", 'AliexpressCategory', 'Level asc');
		foreach($row as $k=>$v){
			$str.=($k==0?'':' &gt; ')."{$v['AliexpressCategory']}";
		}
		return $str;
	}

	public static function aliexpress_get_attr(){	//产品属性
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Content=$html_attr=$html_item='';
		$all_attr_data=$property_data=$property_ary=$sku_data=$attribute_data=$sku_property_data=$sku_selected_data=$attributeAry=array();
		$Lang=@substr($c['manage']['config']['ManageLanguage'],0,2);//语言
		$language=$c['manage']['lang_pack'];
		
		$categoryId=(int)$p_categoryId; //产品分类ID
		$AttrId=(int)$p_AttrId; //切换前的分类ID
		$ProId=(int)$p_ProId; //产品ID
		if(!$categoryId) ly200::e_json('请选择分类', -1);//未选择分类
		if($AttrId && $AttrId==$categoryId) ly200::e_json('相同分类，不切换！', -2);//相同的分类就不用继续执行
		
		$products_category_row=str::str_code(db::get_one('products_aliexpress_category', "AliexpressCateId='{$categoryId}'"));
		//分类面包屑
		$categoryStr=self::aliexpress_get_category_list_by_UId($products_category_row['AliexpressUId'].$products_category_row['AliexpressCateId']);
		//分类属性内容
		$AttributeValue=str::str_code($products_category_row['AttributeValue'], 'htmlspecialchars_decode');
		if($AttributeValue==''){
			$Account=aliexpress::set_default_authorization();
			$sync_data=array(
				'ApiKey'	=>	'ueeshop_sync',
				'ApiName'	=>	'aliexpress',
				'Action'	=>	'sync_attributes',
				'Number'	=>	$c['Number'],
				'Account'	=>	$Account,
				'categoryId'=>	$products_category_row['AliexpressCateId'],
				'timestamp'	=>	$c['time']
			);
			$sync_data['sign']=ly200::sign($sync_data, $c['ApiKey']);
			$result=str::json_data(ly200::curl($c['sync_url'], $sync_data, '', $curl_opt), 'decode');
			$AttributeValue=@gzuncompress(base64_decode($result['msg']['AttributeValue']));
			db::update('products_aliexpress_category', "AliexpressCateId='{$categoryId}'", array('AttributeValue'=>addslashes($AttributeValue)));
		}
		$attributeAry=@str::json_data($AttributeValue, 'decode');
		foreach($attributeAry as $v){
			if($v['sku']==1){
				$sku_property_data[$v['spec']]=$v;
			}else $attribute_data[]=$v;
		}
		
		//当前产品选中项
		if($ProId){
			$products_row=db::get_one('products_aliexpress', "ProId='$ProId'", 'aeopAeProductPropertys,aeopAeProductSKUs');
			$property_ary=str::json_data(str::str_code($products_row['aeopAeProductPropertys'], 'htmlspecialchars_decode'), 'decode');
			$sku_data=str::json_data(str::str_code($products_row['aeopAeProductSKUs'], 'htmlspecialchars_decode'), 'decode');
			foreach($property_ary as $v){
				$key=$v['attrNameId']?'system':'custom';
				$property_data[$key][(int)$v['attrNameId']][]=$v;
			}
			$ext_attr_data=array();
			foreach($sku_data as $v){
				$key='';
				foreach($v['aeopSKUProperty'] as $v1){
					$key.=(!$key?'':'_').$v1['propertyValueId'];
					$sku_selected_data[$v1['skuPropertyId']][$v1['propertyValueId']]=$v1;
					!@in_array($v1['propertyValueId'], $sku_selected_data[$v1['skuPropertyId']]['selected']) && $sku_selected_data[$v1['skuPropertyId']]['selected'][]=$v1['propertyValueId'];
				}
				$ext_attr_data[$key]=array($v['skuPrice'], $v['ipmSkuStock'], $v['skuCode']);
			}
		}
		
		//普通属性内容
		$generalAttrHtml=$customAttrHtml=$skuAttrHtml='';
		foreach($attribute_data as $v){
			$important=(int)$v['required']?'<font class="fc_red">*</font> ':((int)$v['keyAttribute']?'<font color="#009900">!</font> ':'');
			$required=(int)$v['required']?' notnull':'';
			
			$checkboxClass=($v['attributeShowTypeValue']=='check_box' && (int)$v['required'])?' form-checkbox-notnull':'';
			$generalAttrHtml.='<div class="form-item"><em>'.($important . $v['names'][$Lang]).'</em><div class="form-control'.$checkboxClass.'" title="'.$v['names'][$Lang].'">';
			if($v['attributeShowTypeValue']=='list_box'){
				$generalAttrHtml.="<select name=\"property[{$v['id']}][]\"{$required}>";
				$generalAttrHtml.='<option value="">'.$language['global']['select_index'].'</option>';
					foreach((array)$v['values'] as $k1=>$v1){
						$selected=$v1['id']==$property_data['system'][$v['id']][0]['attrValueId']?' selected="selected"':'';
						$generalAttrHtml.="<option value=\"{$v1['id']}\"{$selected}>{$v1['names']['en']}({$v1['names']['zh']})</option>";
					}
				$generalAttrHtml.="</select>";
			}elseif($v['attributeShowTypeValue']=='check_box'){
				$data=array();
				foreach((array)$property_data['system'][$v['id']] as $v0){$data[]=$v0['attrValueId'];}
				foreach((array)$v['values'] as $k1=>$v1){
					$checked=@in_array($v1['id'], $data)?' checked="checked"':'';
					
					$generalAttrHtml.='<label for="value_'.$v1['id'].'">';
					$generalAttrHtml.='<input type="checkbox" id="value_'.$v1['id'].'" name="property['.$v['id'].'][]" value="'.$v1['id'].'"'.$required.$checked.' /> '.$v1['names']['en'].'('.$v1['names']['zh'].')';
					$generalAttrHtml.='</label>';
				}
			}elseif($v['attributeShowTypeValue']=='input'){
				$generalAttrHtml.='<input name="property['.$v['id'].'][value]" value="'.$property_data['system'][$v['id']][0]['attrValue'].'" type="text" class="form_input" size="40" maxlength="70"'.$required.' />';
			}
			$generalAttrHtml.='</div><div class="clear"></div></div>';
		}
		
		//自定义属性，首次加载页面才返回，切换分类保持不变
		if(!$AttrId){
			foreach((array)$property_data['custom'][0] as $v){
				$customAttrHtml.='<div class="custom-property-item">';
				$customAttrHtml.='<input name="attrName[]" value="'.$v['attrName'].'" type="text" class="form_input" size="35" maxlength="40" notnull /> : ';
				$customAttrHtml.='<input name="attrValue[]" value="'.$v['attrValue'].'" type="text" class="form_input" size="60" maxlength="70" notnull /> ';
				$customAttrHtml.='<a href="javascript:;" class="green del">'.$language['global']['del'].'</a></div>';
			}
			$customAttrHtml.='<div class="add-property"><a href="javascript:;" class="btn_ok add">+'.$language['global']['add'].$language['products']['custom'].$language['products']['attribute'].'</a></div>';
			$customAttrHtml.='<div class="clear"></div>';
		}
		
		//SKU属性
		$callback=''; //返回函数
		foreach($sku_property_data as $v){
			$skuAttrHtml.='<div class="rows sku-label-list">';
				$skuAttrHtml.='<label>'.$v['names'][$Lang].'</label>';
				$skuAttrHtml.='<span class="input"><div class="sku-property-list'.($v['skuStyleValue']=='colour_atla'?' color-property':'').'">';
					foreach((array)$v['values'] as $k1=>$v1){
						$data_ary=array(
							'AttrId'		=>	$v['id'],
							'Column'		=>	str_replace(array('&quot;', '"', "'"), array('"', '\"', '@8#'), $v['names'][$Lang]),
							'Name'			=>	str_replace(array('&quot;', '"', "'"), array('"', '\"', '@8#'), $v1['names']['en']),
							'Num'			=>	$v1['id'],
							'customizedName'=>	$v['customizedName'],
							'customizedPic'	=>	$v['customizedPic']
						);
						$sku_selected_data[$v['id']][$v1['id']]['propertyValueDefinitionName'] && $data_ary['customName']=$sku_selected_data[$v['id']][$v1['id']]['propertyValueDefinitionName'];
						if(@is_file($c['root_path'].$sku_selected_data[$v['id']][$v1['id']]['skuImage'])){
							$data_ary['skuImage']=$sku_selected_data[$v['id']][$v1['id']]['skuImage'];
							$data_ary['skuImageExt']=ly200::get_size_img($sku_selected_data[$v['id']][$v1['id']]['skuImage'], '240x240');
						}
						$data=str::json_data($data_ary);
						
						$all_attr_data[$v['id']][$v1['id']]=$v1['names']['en'];
						$checked=@in_array($v1['id'], $sku_selected_data[$v['id']]['selected'])?' checked="checked"':'';
					   
						$skuAttrHtml.='<label title="'.$v1['names'][$Lang].'">';
						$skuAttrHtml.="<input type='checkbox' id='Attr_{$v1['id']}' name='sku[{$v['id']}][]' value='{$v1['id']}' data='{$data}'{$checked} />";//value_
						$v['skuStyleValue']=='colour_atla' && $skuAttrHtml.='<span class="property-title" style="background-color:'.str_replace(' ','',$v1['names']['en']).';"></span> ';
						$skuAttrHtml.=$v1['names']['zh'].'('.$v1['names']['en'].')';
						$skuAttrHtml.='</label>';

						if(@in_array($v1['id'], $sku_selected_data[$v['id']]['selected'])){
							$callback.="sync_obj.aliexpress_edit_attr_init('{$data}', 'Attr_{$v1['id']}', 0);";
						}

					}
				$skuAttrHtml.='</div><div class="clear"></div>';
				
				if($v['customizedName'] || $v['customizedPic']){//有自定义项内容
					$skuAttrHtml.='<div id="sku-custom-property-'.$v['id'].'" class="sku-custom-property hide">';
						$skuAttrHtml.='<table border="0" cellpadding="5" cellspacing="0" class="relation_box"><thead><tr>';
							$skuAttrHtml.='<td width="20%">'.$v['names'][$Lang].'</td>';
							$v['customizedName'] && $skuAttrHtml.='<td width="40%">'.$language['products']['sync']['customName'].'</td>';
							$v['customizedPic'] && $skuAttrHtml.='<td width="40%">'.$language['products']['pic_upfile'].' '.$language['products']['sync']['picTips'].'</td>';
						$skuAttrHtml.='</tr></thead><tbody></tbody></table>';
					$skuAttrHtml.='</div>';
				}
				$skuAttrHtml.='</span><div class="clear"></div>';
			$skuAttrHtml.='</div>';
		}
		
		ly200::e_json(array($categoryStr, $generalAttrHtml, $customAttrHtml, $skuAttrHtml, str::json_data($all_attr_data), str::json_data($ext_attr_data), $callback), 1);
	}
	
	public static function aliexpress_products_del(){	//删除速卖通产品
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$ProId=$g_ProId;
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=db::get_one('products_aliexpress', "ProId='$ProId'", 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, PicPath_5, aeopAeProductSKUs');
		for($i=0;$i<6;$i++){
			$PicPath=$row["PicPath_$i"];
			if(is_file($c['root_path'].$PicPath)){
				foreach($resize_ary as $v){
					$ext_name=file::get_ext_name($PicPath);
					file::del_file($PicPath.".{$v}.{$ext_name}");
				}
				file::del_file($PicPath);
				aliexpress::remove_aliexpress_images($PicPath);
			}
		}
		//删除产品颜色图片
		$aeopAeProductSKUs=str::json_data($row['aeopAeProductSKUs'], 'decode');
		foreach($aeopAeProductSKUs as $v){
			foreach($v['aeopSKUProperty'] as $val){
				if($val['skuImage'] && is_file($c['root_path'].$val['skuImage'])){
					foreach($resize_ary as $value){
						$ext_name=file::get_ext_name($val['skuImage']);
						file::del_file($val['skuImage'].".{$value}.{$ext_name}");
					}
					file::del_file($val['skuImage']);
				}
			}
		}
		db::delete('products_aliexpress', "ProId='$ProId'");
		db::delete('products_aliexpress_description', "ProId='$ProId'");
		manage::operation_log('删除速卖通产品');
		ly200::e_json('', 1);
	}
	
	public static function aliexpress_products_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_proid && ly200::e_json('');
		$del_where="ProId in(".str_replace('-',',',$g_group_proid).")";
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=db::get_all('products_aliexpress', $del_where, 'PicPath_0, PicPath_1, PicPath_2, PicPath_3, PicPath_4, aeopAeProductSKUs');
		foreach($row as $v){
			for($i=0; $i<6; $i++){
				$PicPath=$v["PicPath_$i"];
				if(is_file($c['root_path'].$PicPath)){
					foreach($resize_ary as $v2){
						$ext_name=file::get_ext_name($PicPath);
						file::del_file($PicPath.".{$v2}.{$ext_name}");
					}
					file::del_file($PicPath);
					aliexpress::remove_aliexpress_images($PicPath);
				}
			}
			//删除产品颜色图片
			$aeopAeProductSKUs=str::json_data($v['aeopAeProductSKUs'], 'decode');
			foreach($aeopAeProductSKUs as $v2){
				foreach($v2['aeopSKUProperty'] as $val){
					if($val['skuImage'] && is_file($c['root_path'].$val['skuImage'])){
						foreach($resize_ary as $value){
							$ext_name=file::get_ext_name($val['skuImage']);
							file::del_file($val['skuImage'].".{$value}.{$ext_name}");
						}
						file::del_file($val['skuImage']);
					}
				}
			}
		}

		db::delete('products_aliexpress', $del_where);
		db::delete('products_aliexpress_description', $del_where);
		manage::operation_log('批量删除速卖通产品');
		ly200::e_json('', 1);
	}
	
	public static function copy_alexpress_to_products(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$group_ids=str_replace('-', ',', $p_group_id);
		$CateId=(int)$p_CateId;
		!$group_ids && ly200::e_json('请选择要复制的产品!');
		!$CateId && ly200::e_json('请选择产品分类!');
		
		$row=str::str_code(db::get_all('products_aliexpress', "ProId in($group_ids)"));
		foreach($row as $v){
			//库存、SKU
			$skuCode='';
			$Stock=0;
			$aeopAeProductSKUs=str::json_data(htmlspecialchars_decode($v['aeopAeProductSKUs']), 'decode');
			foreach($aeopAeProductSKUs as $val){
				$Stock+=$val['ipmSkuStock'];
				(!$skuCode && $val['skuCode']) && $skuCode=$val['skuCode'];
			}
			!$skuCode && $skuCode=$v['productId']+200;
			
			$data=array(
				'CateId'							=>	$CateId,
				'Source'							=>	'aliexpress',
				'SourceId'							=>	$v['productId'],
				"Name{$c['manage']['web_lang']}"	=>	addslashes(stripslashes($v['Subject'])),
				'SKU'								=>	$skuCode,
				'Number'							=>	$skuCode,
				'Price_1'							=>	$v['productPrice'],
				'Stock'								=>	$Stock,
				'Weight'							=>	$v['grossWeight'],
				'Cubage'							=>	@implode(',', array(sprintf('%01.2f', $v['packageLength']/100), sprintf('%01.2f', $v['packageWidth']/100), sprintf('%01.2f', $v['packageHeight']/100))),
				'SoldOut'							=>	1
			);
			//批发价
			($v['bulkOrder'] && $v['bulkDiscount']) && $data['Wholesale']=str::json_data(array($v['bulkOrder']=>sprintf('%01.2f', $v['productPrice']*(100-$v['bulkDiscount'])/100)));
			
			$ProId=(int)db::get_value('products', "Source='aliexpress' and SourceId='{$v['productId']}'", 'ProId');
			if(!$ProId){
				$resize_ary=$c['manage']['resize_ary']['products'];
				$temp_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
				file::mk_dir($temp_dir);
				$j=0;
				for($i=0; $i<10; ++$i){
					$PicPath=$v["PicPath_$i"];
					if(is_file($c['root_path'].$PicPath)){
						$ext_name=file::get_ext_name($PicPath);
						$data['PicPath_'.$j++]=$temp=$temp_dir.str::rand_code().'.'.$ext_name;
						foreach($resize_ary as $size){
							$RePicPath=$PicPath.".{$size}.{$ext_name}";
							@copy($c['root_path'].$RePicPath, $c['root_path'].ltrim($temp.".{$size}.{$ext_name}", '/'));
						}
						@copy($c['root_path'].$PicPath, $c['root_path'].ltrim($temp, '/'));
					}
				}
			}

			if($ProId){
				$w="ProId='{$ProId}'";
				$data['EditTime']=$c['time'];
				db::update('products', $w, $data);
				!db::get_row_count('products_seo', $w) && db::insert('products_seo', array('ProId'=>$products_row['ProId']));
				if(!db::get_row_count('products_description', $w)){
					db::insert('products_description', array(
							'ProId'									=>	$ProId,
							"Description{$c['manage']['web_lang']}"	=>	addslashes(stripslashes(db::get_value('products_aliexpress_description', "productId='{$v['productId']}'", 'Detail')))
						)
					);
				}else{
					db::update('products_description', $w, array(
							"Description{$c['manage']['web_lang']}"	=>	addslashes(stripslashes(db::get_value('products_aliexpress_description', "productId='{$v['productId']}'", 'Detail')))
						)
					);
				}
			}else{
				$data['AccTime']=$c['time'];
				db::insert('products', $data);
				$ProId=db::get_insert_id();
				db::insert('products_seo', array('ProId'=>$ProId));
				db::insert('products_description', array(
						'ProId'									=>	$ProId,
						"Description{$c['manage']['web_lang']}"	=>	addslashes(stripslashes(db::get_value('products_aliexpress_description', "productId='{$v['productId']}'", 'Detail')))
					)
				);
			}
		}
		manage::operation_log('批量复制速卖通产品到本地');
		ly200::e_json('复制完成!', 1);
	}
	/*****************************************
	速卖通同步部分(end)
	*****************************************/


	/*****************************************
	亚马逊同步部分(start)
	*****************************************/
	public static function change_amazon_authorization_account(){//切换当前速卖通账号
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$AccountId=(int)$p_AccountId;
		!$AccountId && ly200::e_json('请选择切换的账号！');
		$AccountId==$_SESSION['Manage']['Amazon']['AId'] && ly200::e_json('');
		
		amazon::set_default_authorization($AccountId);
		ly200::e_json('', 1);
	}

	public static function amazon_products_sync(){//同步产品
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($c['FunVersion']<2) return;

		$data=array(
			'ApiKey'		=>	'ueeshop_sync',
			'Action'		=>	'sync_products',
			'ApiName'		=>	'amazon',
			'Number'		=>	$c['Number'],
			'Account'		=>	$_SESSION['Manage']['Amazon']['Account'],
			'notify_url'	=>	ly200::get_domain().'/gateway/',
			'timestamp'		=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		if($result['ret']==1){
			$TaskId=$result['msg']['TaskId'];
			$taskData=array(
				'Platform'	=>	'amazon',
				'TaskId'	=>	$TaskId,
				'AccTime'	=>	time()
			);
			!db::get_row_count('products_sync_task', "Platform='amazon' and TaskId='{$TaskId}'") && db::insert('products_sync_task', $taskData);
		}
		
		ly200::e_json($TaskId?array('TaskId'=>$TaskId):$result['msg'], (int)$result['ret']);
	}
	
	public static function sync_amazon_products_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($c['FunVersion']<2) return;
		
		$ItemDimensions=$p_ItemDimensions;
		if(!$ItemDimensions['Length'][0]) unset($ItemDimensions['Length']);
		if(!$ItemDimensions['Width'][0]) unset($ItemDimensions['Width']);
		if(!$ItemDimensions['Height'][0]) unset($ItemDimensions['Height']);
		$PackageDimensions=$p_PackageDimensions;
		if(!$PackageDimensions['Weight'][0]) unset($PackageDimensions['Weight']);
		if(!$PackageDimensions['Length'][0]) unset($PackageDimensions['Length']);
		if(!$PackageDimensions['Width'][0]) unset($PackageDimensions['Width']);
		if(!$PackageDimensions['Height'][0]) unset($PackageDimensions['Height']);
		
		$data=array(
			'`MerchantId`'			=>	$p_Account,
			'`item-name`'			=>	addslashes(stripslashes($_POST['item-name'])),
			'`price`'				=>	sprintf('%01.2f', $p_price),
			'`ListPrice`'			=>	$p_ListPrice['Amount']?str::json_data($p_ListPrice):'',
			'`quantity`'			=>	(int)$p_quantity,
			'`ItemDimensions`'		=>	$ItemDimensions?str::json_data($ItemDimensions):'',
			'`PackageDimensions`'	=>	$PackageDimensions?str::json_data($PackageDimensions):'',
			'`Feature`'				=>	str::str_code(str::json_data(str::str_code($p_Feature, 'stripslashes')), 'addslashes'),
			'`item-description`'	=>	addslashes(stripslashes($_POST['item-description']))
		);
		
		db::update('products_amazon', "ProId='{$p_ProId}'", $data);
		manage::operation_log('编辑亚马逊产品：'.$_POST['seller-sku']);
		ly200::e_json('', 1);
	}
	
	public static function amazon_products_del(){	//删除亚马逊产品
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if($c['FunVersion']<2) return;

		$ProId=(int)$g_ProId;
		$where="ProId='$ProId'";
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=db::get_one('products_amazon', $where, '`image-url`');
		$ImgPath=$row['image-url'];
		if(is_file($c['root_path'].$ImgPath)){
			foreach($resize_ary as $v){
				$ext_name=file::get_ext_name($ImgPath);
				file::del_file($ImgPath.".{$v}.{$ext_name}");
			}
			file::del_file($ImgPath);
		}
		db::delete('products_amazon', $where);
		manage::operation_log('删除亚马逊产品');
		ly200::e_json('', 1);
	}
	
	public static function amazon_products_del_bat(){//批量删除亚马逊产品
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if($c['FunVersion']<2) return;

		!$g_group_proid && ly200::e_json('');
		$del_where="ProId in(".str_replace('-',',',$g_group_proid).")";
		//删除产品图片
		$resize_ary=$c['manage']['resize_ary']['products'];
		$row=db::get_all('products_amazon', $del_where, '`image-url`');
		foreach($row as $v){
			$ImgPath=$v["image-url"];
			if(is_file($c['root_path'].$ImgPath)){
				foreach($resize_ary as $v2){
					$ext_name=file::get_ext_name($ImgPath);
					file::del_file($ImgPath.".{$v2}.{$ext_name}");
				}
				file::del_file($ImgPath);
			}
		}

		db::delete('products_amazon', $del_where);
		manage::operation_log('批量删除亚马逊产品');
		ly200::e_json('', 1);
	}
	
	public static function copy_amazon_to_products(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($c['FunVersion']<2) return;

		$group_ids=str_replace('-', ',', $p_group_id);
		$CateId=(int)$p_CateId;
		!$group_ids && ly200::e_json('请选择要复制的产品!');
		!$CateId && ly200::e_json('请选择产品分类!');
		
		$row=str::str_code(db::get_all('products_amazon', "ProId in($group_ids)"));
		foreach($row as $v){
			$Feature=$ListPrice=$ItemDimensions=$PackageDimensions=$Cubage=array();
			$v['Feature'] && $Feature=str::json_data(htmlspecialchars_decode($v['Feature']), 'decode');
			$v['ListPrice'] && $ListPrice=str::json_data(htmlspecialchars_decode($v['ListPrice']), 'decode');
			$v['ItemDimensions'] && $ItemDimensions=str::json_data(htmlspecialchars_decode($v['ItemDimensions']), 'decode');
			$v['PackageDimensions'] && $PackageDimensions=str::json_data(htmlspecialchars_decode($v['PackageDimensions']), 'decode');
			
			$v=str::str_code(str::str_code($v, 'stripslashes'), 'addslashes');
			$data=array(
				'CateId'							=>	$CateId,
				'Source'							=>	'amazon',
				'SourceId'							=>	$v['product-id']?$v['product-id']:$v['asin1'],
				"Name{$c['manage']['web_lang']}"	=>	$v['item-name'],
				'SKU'								=>	$v['seller-sku']?$v['seller-sku']:$v['asin1'],
				'Number'							=>	$v['listing-id']?$v['listing-id']:$v['asin1'],
				'Price_0'							=>	$ListPrice['Amount'],
				'Price_1'							=>	$v['price'],
				'Stock'								=>	$v['quantity'],
				'SoldOut'							=>	1
			);
			//产品重量
			$PackageDimensions['Weight'] && $data['Weight']=$PackageDimensions['Weight'][0];//包装后重量
			$ItemDimensions['Weight'] && $data['Weight']=$ItemDimensions['Weight'][0];//产品毛重
			//产品长宽高
			($PackageDimensions['Length'] || $PackageDimensions['Width'] || $PackageDimensions['Height']) && $data['Cubage']=@implode(',', array(sprintf('%01.2f', $PackageDimensions['Length'][0]/100), sprintf('%01.2f', $PackageDimensions['Width'][0]/100), sprintf('%01.2f', $PackageDimensions['Height'][0]/100)));//包装后长宽高
			($ItemDimensions['Length'] || $ItemDimensions['Width'] || $ItemDimensions['Height']) && $data['Cubage']=@implode(',', array(sprintf('%01.2f', $ItemDimensions['Length'][0]/100), sprintf('%01.2f', $ItemDimensions['Width'][0]/100), sprintf('%01.2f', $ItemDimensions['Height'][0]/100)));//包装前长宽高
			
			
			$ProId=(int)db::get_value('products', "Source='amazon' and SourceId!='' and SourceId='{$v['product-id']}'", 'ProId');
			!$ProId && $ProId=(int)db::get_value('products', "Source='amazon' and SourceId!='' and SourceId='{$v['asin1']}'", 'ProId');
			if(!$ProId && is_file($c['root_path'].$v['image-url'])){
				$resize_ary=$c['manage']['resize_ary']['products'];
				$save_dir=$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
				file::mk_dir($save_dir);
				
				$ext_name=file::get_ext_name($v['image-url']);
				$data['PicPath_0']=$save_dir.str::rand_code().'.'.$ext_name;
				//直接复制产品不同规格图片
				@copy($c['root_path'].$v['image-url'], $c['root_path'].ltrim($data['PicPath_0'], '/'));
				foreach($resize_ary as $size){
					$RePicPath=$v['image-url'].".{$size}.{$ext_name}";
					@copy($c['root_path'].$RePicPath, $c['root_path'].ltrim($data['PicPath_0'].".{$size}.{$ext_name}", '/'));
				}
				
			}
			
			$Description=addslashes(stripslashes(@implode($Feature, "<br />")."<br /><br /><br />".htmlspecialchars_decode($v['item-description'])));
			if($ProId){
				$w="ProId='{$ProId}'";
				$data['EditTime']=$c['time'];
				db::update('products', $w, $data);
				!db::get_row_count('products_seo', $w) && db::insert('products_seo', array('ProId'=>$products_row['ProId']));
				if(!db::get_row_count('products_description', $w)){
					db::insert('products_description', array(
							'ProId'									=>	$ProId,
							"Description{$c['manage']['web_lang']}"	=>	$Description
						)
					);
				}else{
					db::update('products_description', $w, array("Description{$c['manage']['web_lang']}"=>$Description));
				}
			}else{
				$data['AccTime']=$c['time'];
				db::insert('products', $data);
				$ProId=db::get_insert_id();
				db::insert('products_seo', array('ProId'=>$ProId));
				db::insert('products_description', array(
						'ProId'									=>	$ProId,
						"Description{$c['manage']['web_lang']}"	=>	$Description
					)
				);
			}
		}
		manage::operation_log('批量复制亚马逊产品到本地');
		ly200::e_json('复制完成!', 1);
	}
	/*****************************************
	亚马逊同步部分(end)
	*****************************************/
	/***********************************************************数据同步(end)*******************************************************************/
	
	public static function tags_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'g');
		$TId=$g_TId;
		if($TId){
			manage::operation_log('修改产品标签');
		}else{
			db::insert('products_tags',array('MyOrder'=>0));
			$TId=db::get_insert_id();
			manage::operation_log('添加产品标签');
		}
		manage::database_language_operation('products_tags', "TId='$TId'", array('Name'=>1));
		ly200::e_json('', 1);
	}
	public static function tags_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_TId=(int)$g_TId;
		db::delete('products_tags',"TId='$g_TId'");
		manage::operation_log('删除产品标签');
		ly200::e_json('', 1);
	}
	public static function tags_del_bat(){
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$del_where="TId in(".str_replace('-', ',', $g_group_tid).")";
		db::delete('products_tags',$del_where);
		manage::operation_log('批量删除产品标签');
		ly200::e_json('', 1);
	}
	public static function tags_set(){
		@extract($_POST, EXTR_PREFIX_ALL, 'g');
		$g_TId=(int)$g_TId;
		$ProId_ary=explode('|',$g_ProId);
		$ProId_ary=array_filter($ProId_ary);
		$ProId_Old_ary=explode('|',$g_ProIdOld);
		$ProId_Old_ary=array_filter($ProId_Old_ary);
		if($ProId_Old_ary){	//先清除旧的
			$prod_id_old_ary=implode(',',$ProId_Old_ary);
			$prod_id_old_ary && $products_old_rows=db::get_all('products',"ProId in ($prod_id_old_ary)",'ProId,Tags');
			foreach((array)$products_old_rows as $v){
				$value=str_replace('|'.$g_TId.'|','|',$v['Tags']);
				$value=$value=='|'?'':$value;
				$update_old_sql.="when {$v['ProId']} then '$value' ";
			}
			$update_old_sql && db::query("update products set Tags = case ProId $update_old_sql end where ProId in ($prod_id_old_ary)");
		}
		$prod_id_ary=implode(',',$ProId_ary);
		$prod_id_ary && $products_rows=db::get_all('products',"ProId in ($prod_id_ary)",'ProId,Tags');
		foreach((array)$products_rows as $v){
			$value=$v['Tags']?$v['Tags'].$g_TId.'|':'|'.$g_TId.'|';
			$update_sql.="when {$v['ProId']} then '$value' ";
		}
		$update_sql && db::query("update products set Tags = case ProId $update_sql end where ProId in ($prod_id_ary)");
		manage::operation_log('设置产品标签');
		ly200::e_json('', 1);
	}
}
?>