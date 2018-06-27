<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class sales_module{
	//节日模板管理 Start
	public static function holiday_select(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_HId=(int)$p_HId;
		
		db::update('sales_holiday', "HId!='$p_HId'", array('IsUsed'=>0));
		db::update('sales_holiday', "HId='$p_HId'", array('IsUsed'=>1));
		manage::operation_log('选择节目模板');
		ly200::e_json('', 1);
	}
	
	public static function holiday_edit(){
		global $c;

		//----------------------------过滤敏感词-------------------------------
		$resultArr=str::keywords_filter();
		$resultArr[0]==1 && ly200::e_json('带有敏感词：'.$resultArr[1], 0);
		//----------------------------过滤敏感词-------------------------------

		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Theme=$p_Theme;
		$p_Number=(int)$p_Number;
		$p_ContentsType=(int)$p_ContentsType;
		$theme_url="/static/themes/default/holiday/{$p_Theme}/";
		$current_lang_ext = '_'.($p_Lang ? trim($p_Lang) : $c['manage']['config']['LanguageDefault']);
		$theme_object=file_get_contents($c['root_path'].$theme_url."themes{$current_lang_ext}.json");
		$theme_ary=str::json_data($theme_object, 'decode');
		
		$save_dir=$theme_url.'images/';
		file::mk_dir($save_dir);
		$PicPath=$p_PicPath;
		
		$ImgPath=array();
		if($p_ImgPath){
			foreach($p_ImgPath as $k=>$v){
				$ImgPath[$k]=$v;
			}
		}
		
		if($p_ContentsType){
			$theme_ary[$p_Number]['Title']=$p_TitleList;
			$theme_ary[$p_Number]['Url']=$p_UrlList;
			$theme_ary[$p_Number]['PicPath']=$ImgPath;
		}else{
			$theme_ary[$p_Number]['Title']=$p_Title;
			$theme_ary[$p_Number]['Url']=$p_Url;
			$theme_ary[$p_Number]['PicPath']=$PicPath;
		}
		
		$theme_content=str::json_data(str::str_code($theme_ary, 'stripslashes'));
		file::write_file($theme_url, "themes{$current_lang_ext}.json", $theme_content);
		manage::operation_log('修改节目模板');
		ly200::e_json(array($p_Theme), 1);
	}
	
	public static function holiday_set_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$theme_url="/static/themes/default/holiday/{$p_Theme}/";
		
		$save_dir=$theme_url.'images/';
		file::mk_dir($save_dir);
		$LogoPath=$p_LogoPath;
		
		if(!$LogoPath) ly200::e_json();
		
		db::update('sales_holiday', "Number='$p_Theme'", array('LogoPath'=>$LogoPath));
		manage::operation_log('修改节目模板基本设置');
		ly200::e_json(array($p_Theme), 1);
	}
	
	public static function holiday_products_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		$holiday_row=db::get_value('sales_holiday', "Number='$p_Theme'", 'ProId');
		$holiday_ary=str::json_data($holiday_row, 'decode');

		$merge_ary=@explode('|', substr($p_ProIdAry, 1, -1));
		$merge_ary=@array_unique($merge_ary);
		sort($merge_ary);
		$holiday_ary[$p_Number]=$merge_ary;
		$holiday_obj=addslashes(str::json_data(str::str_code($holiday_ary, 'stripslashes')));
		if(!$p_ProIdAry) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		
		db::update('sales_holiday', "Number='$p_Theme'", array('ProId'=>$holiday_obj));
		manage::operation_log('修改节目模板');
		ly200::e_json(array($p_Theme), 1);
	}
	//节日模板管理 End
	
	//产品促销管理 Start
	public static function sales_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PackageProId=explode('|', substr($p_PackageProId, 1, -1));
		if(!$p_PackageProId) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		$ary=array();
		$column_ary=array('IsPromotion', 'PromotionType', 'PromotionPrice', 'PromotionDiscount', 'StartTime', 'EndTime');
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach($PackageProId as $k=>$v){
			$ary['IsPromotion'][$v]=1;
			$ary['PromotionType'][$v]=$p_PromotionType[$v];
			$ary['PromotionPrice'][$v]=$p_PromotionPrice[$k];
			$ary['PromotionDiscount'][$v]=$p_PromotionDiscount[$k];
			$ary['StartTime'][$v]=$start_time;
			$ary['EndTime'][$v]=$end_time;
		}
		$ides=implode(',', $PackageProId); 
		$i=0;
		$len=count($column_ary);
		$sql="update products set";
			foreach($column_ary as $v){
				$sql.=" {$v} = case ProId";
				foreach($ary[$v] as $kk=>$vv){
					$sql.=sprintf(" when %s then '%s' ", $kk, $vv); 
				}
				$sql.='end'.(++$i<$len?',':'');
			}
		$sql.=" where ProId in($ides)";
		$sql && db::query($sql);
		manage::operation_log('添加产品促销');
		ly200::e_json('', 1);
	}
	
	public static function sales_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_ProId=(int)$p_ProId;
		$PromotionDiscount=(int)($p_PromotionDiscount?$p_PromotionDiscount:100);
		$PromotionDiscount=$PromotionDiscount>100?100:$PromotionDiscount;
		$p_PromotionTime=@explode('/', $p_PromotionTime);
		$StartTime=@strtotime($p_PromotionTime[0]);
		$EndTime=@strtotime($p_PromotionTime[1]);
		
		$data=array(
			'PromotionType'		=>	(int)$p_PromotionType,
			'PromotionPrice'	=>	(float)$p_PromotionPrice,
			'PromotionDiscount'	=>	(int)$PromotionDiscount,
			'StartTime'			=>	$StartTime,
			'EndTime'			=>	$EndTime
		);
		
		db::update('products', "ProId='$p_ProId'", $data);
		manage::operation_log('修改产品促销');
		
		ly200::e_json('', 1);
	}
	
	public static function sales_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ProId_ary = $p_ProId;
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach((array)$ProId_ary as $k=>$v){
			$v=(int)$v;
			$PromotionType=$p_PromotionType[$v];
			$PromotionPrice=$p_PromotionPrice[$k];
			$PromotionDiscount=$p_PromotionDiscount[$k];
			$PromotionDiscount=(int)($PromotionDiscount?$PromotionDiscount:100);
			$PromotionDiscount=$PromotionDiscount>100?100:$PromotionDiscount;
			$data=array(
				'PromotionType'		=> $PromotionType,
				'PromotionPrice'	=> $PromotionPrice,
				'PromotionDiscount'	=> $PromotionDiscount,
				'StartTime'			=> $start_time,
				'EndTime'			=> $end_time,
				);
			db::update('products',"ProId = '{$v}'",$data);
		}
		manage::operation_log('批量修改产品促销');
		ly200::e_json('', 1);
	}

	public static function sales_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_ProId=(int)$g_ProId;
		
		db::update('products', "ProId='$g_ProId'", array('IsPromotion'=>0));
		manage::operation_log('关闭产品促销');
		ly200::e_json('', 1);
	}
	
	public static function sales_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$update_where="ProId in(".str_replace('-', ',', $g_group_id).")";
		db::update('products', $update_where, array('IsPromotion'=>0));
		manage::operation_log('批量关闭产品促销');
		ly200::e_json('', 1);
	}
	//产品促销管理 End
	
	//限时秒杀管理 Start
	public static function seckill_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PackageProId=explode('|', substr($p_PackageProId, 1, -1));
		
		if(!$p_PackageProId) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		
		$sql='insert into sales_seckill (ProId, StartTime, EndTime, Price, Qty, RemainderQty, MaxQty, AccTime) values'; 
		$sql_to='';
		$i=0;
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach($PackageProId as $k=>$v){
			if(db::get_row_count('sales_seckill', "ProId='{$v}' and (({$start_time} between StartTime and EndTime) or ({$end_time} between StartTime and EndTime))")) continue;
			$sql_to.=($i?',':'')."({$v}, {$start_time}, {$end_time}, {$p_Price[$k]}, {$p_Qty[$k]}, {$p_RemainderQty[$k]}, {$p_MaxQty[$k]}, {$c['time']})";
			++$i;
		}
		$sql_to && db::query($sql.$sql_to);
		manage::operation_log('添加限时秒杀');
		ly200::e_json('', 1);
	}
	
	public static function seckill_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId=(int)$p_SId;
		$p_PromotionTime=@explode('/', $p_PromotionTime);
		$StartTime=@strtotime($p_PromotionTime[0]);
		$EndTime=@strtotime($p_PromotionTime[1]);
		
		if(db::get_row_count('sales_seckill', "ProId='$p_ProId' and SId!='$SId' and (({$StartTime} between StartTime and EndTime) or ({$EndTime} between StartTime and EndTime))")) ly200::e_json(manage::get_language('sales.time_tips'));
		
		$data=array(
			'ProId'			=>	(int)$p_ProId,
			'StartTime'		=>	$StartTime,
			'EndTime'		=>	$EndTime,
			'Price'			=>	(float)$p_Price,
			'Qty'			=>	(int)$p_Qty,
			'RemainderQty'	=>	(int)$p_RemainderQty,
			'MaxQty'		=>	(int)$p_MaxQty
		);
		
		db::update('sales_seckill', "SId='$SId'", $data);
		manage::operation_log('修改限时秒杀');
		
		ly200::e_json('', 1);
	}
	
	public static function seckill_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId_ary = $p_SId;
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach((array)$SId_ary as $k=>$v){
			$v=(int)$v;
			$Price=(float)$p_Price[$k];
			$Qty=(int)$p_Qty[$k];
			$RemainderQty=(int)$p_RemainderQty[$k];
			$MaxQty=(int)$p_MaxQty[$k];
			$RemainderQty>$Qty && $RemainderQty=$Qty;
			$MaxQty>$Qty && $MaxQty=$Qty;
			$data = array(
				'Price' 		=> $Price,
				'Qty' 			=> $Qty,
				'RemainderQty' 	=> $RemainderQty,
				'MaxQty' 	=> $MaxQty,
				'StartTime' 	=> $start_time,
				'EndTime' 		=> $end_time,
				'AccTime'		=> $c['time'],
				);
			db::update('sales_seckill',"SId='{$v}'",$data);
		}
		manage::operation_log('批量修改限时秒杀');
		ly200::e_json('', 1);
	}

	public static function seckill_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('sales_seckill', "SId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('限时秒杀修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function seckill_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_SId=(int)$g_SId;
		
		db::delete('sales_seckill', "SId='$g_SId'");
		manage::operation_log('删除限时秒杀');
		ly200::e_json('', 1);
	}
	
	public static function seckill_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="SId in(".str_replace('-', ',', $g_group_id).")";
		db::delete('sales_seckill', $del_where);
		manage::operation_log('批量删除限时秒杀');
		ly200::e_json('', 1);
	}
	//限时秒杀管理 End
	
	//团购管理 Start
	public static function tuan_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PackageProId=explode('|', substr($p_PackageProId, 1, -1));
		
		if(!$p_PackageProId) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		
		$sql='insert into sales_tuan (ProId, StartTime, EndTime, Price, BuyerCount, TotalCount, AccTime) values'; 
		$sql_to;
		$i=0;
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach($PackageProId as $k=>$v){
			$Price=(float)$p_Price[$k];
			$BuyerCount=(int)$p_BuyerCount[$k];
			$TotalCount=(int)$p_TotalCount[$k];
			$BuyerCount>$TotalCount && $TotalCount=$BuyerCount;
			if(!$Price || !$BuyerCount || !$TotalCount){
				ly200::e_json(manage::get_language('sales.tuan.tips'), 0);
			}
			if(db::get_row_count('sales_tuan', "ProId='{$v}' and (({$start_time} between StartTime and EndTime) or ({$end_time} between StartTime and EndTime))")) continue;
			$sql_to.=($i?',':'')."({$v}, {$start_time}, {$end_time}, {$Price}, {$BuyerCount}, {$TotalCount}, {$c['time']})"; 
			++$i;
		}
		$sql_to && db::query($sql.$sql_to);
		manage::operation_log('添加团购');
		ly200::e_json('', 1);
	}
	
	public static function tuan_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$TId=(int)$p_TId;
		$p_PromotionTime=@explode('/', $p_PromotionTime);
		$StartTime=@strtotime($p_PromotionTime[0]);
		$EndTime=@strtotime($p_PromotionTime[1]);
		
		$p_BuyerCount>$p_TotalCount && $p_TotalCount=$p_BuyerCount;
		
		if(db::get_row_count('sales_tuan', "ProId='$p_ProId' and (({$StartTime} between StartTime and EndTime) or ({$EndTime} between StartTime and EndTime)) and TId!='$TId'")) ly200::e_json(manage::get_language('sales.time_tips'));
		
		$data=array(
			'ProId'		=>	(int)$p_ProId,
			'StartTime'	=>	$StartTime,
			'EndTime'	=>	$EndTime,
			'Price'		=>	(float)$p_Price,
			'BuyerCount'=>	(int)$p_BuyerCount,
			'TotalCount'=>	(int)$p_TotalCount
		);
		
		db::update('sales_tuan', "TId='$TId'", $data);
		manage::operation_log('修改团购');
		
		ly200::e_json('', 1);
	}

	public static function tuan_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$TId_ary = $p_TId;
		$PromotionTime=@explode('/', $p_PromotionTime);
		$start_time=@strtotime($PromotionTime[0]);
		$end_time=@strtotime($PromotionTime[1]);
		foreach((array)$TId_ary as $k=>$v){
			$v=(int)$v;
			$Price=(float)$p_Price[$k];
			$BuyerCount=(int)$p_BuyerCount[$k];
			$TotalCount=(int)$p_TotalCount[$k];
			$BuyerCount>$TotalCount && $TotalCount=$BuyerCount;
			if(!$Price || !$BuyerCount || !$TotalCount){
				ly200::e_json(manage::get_language('sales.tuan.tips'), 0);
			}
			$data = array(
				'Price' 		=> $Price,
				'BuyerCount' 	=> $BuyerCount,
				'TotalCount' 	=> $TotalCount,
				'StartTime' 	=> $start_time,
				'EndTime' 		=> $end_time,
				'AccTime'		=> $c['time'],
				);
			db::update('sales_tuan',"TId='{$v}'",$data);
		}
		manage::operation_log('批量修改团购');
		ly200::e_json('', 1);
	}
	
	public static function tuan_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('sales_tuan', "TId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('团购修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function tuan_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_TId=(int)$g_TId;
		
		db::delete('sales_tuan', "TId='$g_TId'");
		manage::operation_log('删除团购');
		ly200::e_json('', 1);
	}
	
	public static function tuan_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="TId in(".str_replace('-', ',', $g_group_id).")";
		db::delete('sales_tuan', $del_where);
		manage::operation_log('批量删除团购');
		ly200::e_json('', 1);
	}
	//团购管理 End
	
	//组合购买管理 Start
	public static function package_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PId=(int)$p_PId;
		$p_ProId=(int)$p_ProId;
		
		if(!$p_ProId) ly200::e_json(manage::get_language('sales.main_proid_tips'));
		if(!$p_PackageProId) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		
		$PackageData='';
		$package_ary=explode('|', substr($p_PackageProId, 1, -1));
		$package_ary[]=$p_ProId;
		foreach($package_ary as $k=>$v){
			if(${'p_Attr_'.$v}) $PackageData[$v]=${'p_Attr_'.$v};
		}
		$PackageData=addslashes(str::json_data(str::str_code($PackageData, 'stripslashes')));
		
		$data=array(
			'ProId'				=>	$p_ProId,
			'PackageProId'		=>	$p_PackageProId,
			'Data'				=>	$PackageData,
			'ReverseAssociate'	=>	(int)$p_ReverseAssociate,
			'Type'				=>	0,
			'Name'				=>	$p_Name,
			'IsAttr'			=>	(int)$p_IsAttr
		);
		
		if($PId){
			db::update('sales_package', "PId='$PId'", $data);
			manage::operation_log('修改组合购买');
		}else{
			$data['AccTime']=$c['time'];
			db::insert('sales_package', $data);
			manage::operation_log('添加组合购买');
		}
		ly200::e_json('', 1);
	}
	
	public static function package_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_PId=(int)$g_PId;
		
		db::delete('sales_package', "PId='$g_PId' and Type=0");
		manage::operation_log('删除组合购买');
		js::location('./?m=sales&a=package');
	}
	
	public static function package_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_pid && js::location('./?m=sales&a=package');
		$del_where="PId in(".str_replace('-', ',', $g_group_pid).") and Type=0";
		db::delete('sales_package', $del_where);
		manage::operation_log('批量删除组合购买');
		js::location('./?m=sales&a=package');
	}
	//组合购买管理 End
	
	//组合促销管理 Start
	public static function promotion_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PId=(int)$p_PId;
		$p_ProId=(int)$p_ProId;
		
		if(!$p_ProId) ly200::e_json(manage::get_language('sales.main_proid_tips'));
		if(!$p_PackageProId) ly200::e_json(manage::get_language('sales.packageproid_tips'));
		
		$PackageData='';
		$package_ary=explode('|', substr($p_PackageProId, 1, -1));
		$package_ary[]=$p_ProId;
		foreach($package_ary as $k=>$v){
			if(${'p_Attr_'.$v}) $PackageData[$v]=${'p_Attr_'.$v};
		}
		$PackageData=addslashes(str::json_data(str::str_code($PackageData, 'stripslashes')));
		
		$data=array(
			'ProId'				=>	(int)$p_ProId,
			'PackageProId'		=>	$p_PackageProId,
			'Data'				=>	$PackageData,
			'ReverseAssociate'	=>	(int)$p_ReverseAssociate,
			'Type'				=>	1,
			'Name'				=>	$p_Name,
			'CurPrice'			=>	(float)$p_CurPrice
		);
		
		if($PId){
			db::update('sales_package', "PId='$PId'", $data);
			manage::operation_log('修改组合促销');
		}else{
			$data['AccTime']=$c['time'];
			db::insert('sales_package', $data);
			manage::operation_log('添加组合促销');
		}
		ly200::e_json('', 1);
	}
	
	public static function promotion_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_PId=(int)$g_PId;
		
		db::delete('sales_package', "PId='$g_PId' and Type=1");
		manage::operation_log('删除组合促销');
		js::location('./?m=sales&a=promotion');
	}
	
	public static function promotion_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_pid && js::location('./?m=sales&a=promotion');
		$del_where="PId in(".str_replace('-', ',', $g_group_pid).") and Type=1";
		db::delete('sales_package', $del_where);
		manage::operation_log('批量删除组合促销');
		js::location('./?m=sales&a=promotion');
	}
	//组合促销管理 End
	
	//优惠券管理 Start
	public static function coupon_send_type_update(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		db::update('sales_coupon', "CId='$p_CId'", array('IsSend'=>$p_value));
		ly200::e_json('', 1);
	}
	
	public static function coupon_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');

		$CId=$p_CId;
		$Prefix=$p_Prefix;
		$CouponWay=(int)$p_CouponWay;
		$CodeLen=(int)$p_CodeLen;
		$CodeLen<5 && $CodeLen=5;
		$CodeLen>15 && $CodeLen=15;
		
		$Qty=(int)$p_Qty;
		$c1=$p_c1?1:0; //字符集英文
		$c2=$p_c2?1:0; //字符集数字
		$CouponNumber=$p_CouponNumber;
		$Time_ary=@explode('/', $p_DeadLine);
		$StartTime=@strtotime($Time_ary[0]);
		$EndTime=@strtotime($Time_ary[1]);
		$CouponType=(int)$p_CouponType;
		$Discount=(int)($p_Discount?$p_Discount:100);
		$Discount=$Discount>100?100:$Discount;
		$Money=$p_Money?$p_Money:0;
		$UseCondition=$p_UseCondition?$p_UseCondition:0;
		$Qty=(int)($p_Qty?$p_Qty:1);
		//$IsUser=(int)$p_IsUser;
		//$UserId=(int)$p_UserId;
		$UseNum=(int)$p_UseNum;
		$UseNum<0 && $UseNum=0;
		

		//($IsUser==0 || $IsUser==2) && $UserId=0;
		
		//限制条件 和 应用范围
		$p_UserId=='|' && $p_UserId='';
		if($p_UserId){
			$UserId=explode('|', substr($p_UserId, 1, -1));
			count($UserId)==1 && $p_UserId=$UserId[0];//兼容之前的单独一个ID号
		}
		$p_LevelId=='|' && $p_LevelId='';
		$p_CateId=='|' && $p_CateId='';
		$p_ProId=='|' && $p_ProId='';
		$p_TagId=='|' && $p_TagId='';
		
		if($CouponType==1) !$Money && ly200::e_json(manage::get_language('sales.coupon.money_tips')); 
		$EndTime<=$StartTime && ly200::e_json(manage::get_language('sales.coupon.deadline_tips'));

		$CId && $CouponWay = db::get_value('sales_coupon', "CId='{$CId}'",'CouponWay');
		$CouponExt='';
		if($CouponWay){
			$CouponWayAry=array(
				'Prefix'	=>	$Prefix,
				'CodeLen'	=>	$CodeLen,
				'c1'		=>	$c1,
				'c2'		=>	$c2,
				);
			$CouponExt=addslashes(str::json_data(str::str_code($CouponWayAry, 'stripslashes')));
		}
		if($CId){//修改优惠券
			if(db::get_row_count('sales_coupon', "CouponNumber='{$CouponNumber}' and CId!='{$CId}'") && !$CouponWay) ly200::e_json(manage::get_language('sales.coupon.number_tips')); 
			$data=array(
				'CouponExt'			=>	$CouponExt,
				'CouponNumber'		=>	$CouponNumber,
				'Discount'			=>	$Discount,
				'Money'				=>	$Money,
				'CouponType'		=>	$CouponType,
				'UseCondition'		=>	$UseCondition,
				'StartTime'			=>	$StartTime,
				'EndTime'			=>	$EndTime,
				'UseNum'			=>	$UseNum,
				'UserId'			=>	$p_UserId,
				'LevelId'			=>	$p_LevelId,
				'CateId'			=>	$p_CateId,
				'ProId'				=>	$p_ProId,
				'TagId'				=>	$p_TagId
			);
			
			db::update('sales_coupon', "CId='$CId'", $data);
			manage::operation_log('修改优惠券');
		}else{//添加优惠券
			(!$c1 && !$c2) && ly200::e_json(manage::get_language('sales.coupon.less_char')); 
			(!$CodeLen || !$Qty) && ly200::e_json(manage::get_language('sales.coupon.notnull_tips'));
			//$IsUser==1 && !$UserId && ly200::e_json(manage::get_language('sales.coupon.user_tips'));
			
			$char1='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$char2='0123456789';
			$char='';
			$c1=='1' && $char.=$char1;
			$c2=='1' && $char.=$char2;

			$CouponWay && $Qty = 1;
			for($i=0; $i<$Qty; $i++){
				$CouponNumber=user::make_card($char,$Prefix,$CodeLen);
				$data=array(
					'CouponWay'		=>	$CouponWay,
					'CouponExt'		=>	$CouponExt,
					'CouponNumber'	=>	$CouponWay ? '' : $CouponNumber,
					'Discount'		=>	$Discount,
					'Money'			=>	$Money,
					'CouponType'	=>	$CouponType,
					'UseCondition'	=>	$UseCondition,
					'StartTime'		=>	$StartTime,
					'EndTime'		=>	$EndTime,
					'UseNum'		=>	$UseNum,
					'UserId'		=>	$p_UserId,
					'LevelId'		=>	$p_LevelId,
					'CateId'		=>	$p_CateId,
					'ProId'			=>	$p_ProId,
					'TagId'			=>	$p_TagId,
					'AccTime'		=>	$c['time']
				);
				db::insert('sales_coupon', $data);
				$CId=db::get_insert_id();
			}
			manage::operation_log('添加优惠券');
		}
		
		//发送绑定会员站内信通知
		$coupon_row=str::str_code(db::get_one('sales_coupon', "CId='$CId'"));
		if($coupon_row['UserId'] && $coupon_row['IsInbox']==0){
			$title=addslashes("Congratulations, you've won the coupon: {$coupon_row['CouponNumber']}");
			$_UserId=$coupon_row['UserId'];
			!strstr($_UserId, '|') && $_UserId='|'.$_UserId.'|';
			$data=array(
				'UserId'	=>	$_UserId,
				'Type'		=>	1,
				'Subject'	=>	$title,
				'Content'	=>	$title,
				'IsRead'	=>	'|0|',
				'AccTime'	=>	$c['time']
			);
			db::insert('user_message', $data);
			db::update('sales_coupon', "CId='$CId'", array('IsInbox'=>1));
			manage::operation_log('发送优惠券站内信');
		}
		ly200::e_json('', 1);
	}
	
	public static function coupon_del(){
		global $c;
		$CId=(int)$_GET['CId'];
		db::delete('sales_coupon', "CId='$CId'");
		manage::operation_log('删除优惠券');
		js::location('./?m=sales&a=coupon');
	}
	
	public static function coupon_del_dat(){
		global $c;
		$GroupCId=$_GET['group_cid'];
		$GroupCId_ary=explode('-', $GroupCId);
		!$GroupCId && js::location('./?m=sales&a=coupon');
		foreach($GroupCId_ary as $k=>$v){
			db::delete('sales_coupon', "CId='$v'");
		}
		manage::operation_log('批量删除优惠券');
		js::location('./?m=sales&a=coupon');
	}
	
	public static function coupon_type_change(){ //修改优惠券送出状态
		global $c;
		$CId=(int)$_GET['CId'];
		$type=db::get_value('sales_coupon', "CId='$CId'", 'IsSend')==1?0:1;
		db::update('sales_coupon',"CId='$CId'", array('IsSend'=>$type));
		manage::operation_log('更新优惠券送出状态');
		js::location('./?m=sales&a=coupon');
	}
	
	public static function coupon_explode(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		//Add some data
		$where='CouponWay=0';
		$g_IsTime=(int)$g_IsTime;//时间限制
		$g_IsUser=(int)$g_IsUser;//绑定会员限制
		$g_CouponType=(int)$g_CouponType;
		$g_Status=(int)$g_Status;//状态限制
		if($g_IsTime){
			$Time_ary=@explode('/', $g_DeadLine);
			$StartTime=@strtotime($Time_ary[0]);
			$EndTime=@strtotime($Time_ary[1]);
			$where.=" and StartTime>={$StartTime} and EndTime<={$EndTime}";
		}
		$g_IsUser && $where.=" and UserId!=0";
		if($g_CouponType==1){
			$g_Discount=(int)$g_Discount>100?100:(int)$g_Discount;
			$where.=" and CouponType=0";
			$g_Discount && $where.=" and Discount={$g_Discount}";
		}elseif($g_CouponType==2){
			$g_Money=$g_Money?$g_Money:0;
			$where.=" and CouponType=1";
			$g_Money && $where.=" and Money={$g_Money}";
		}
		switch($g_Status){//根据状态筛选优惠券
			case 1:
				$where .= " and StartTime<={$c['time']} and EndTime>={$c['time']}";
				break;
			case 2:
				$where .= " and UseNum>0 and BeUseTimes>=UseNum";
				break;
			case 3:
				$where .= " and StartTime>{$c['time']}";
				break;
			case 4:
				$where .= " and EndTime<{$c['time']}";
				break;
		}
		$save_dir='/tmp/';//临时储存目录
		file::mk_dir($save_dir);
		
		$coupon_row=str::str_code(db::get_all('sales_coupon', $where, '*', 'AccTime desc'));
		$objPHPExcel=new PHPExcel();
		 
		//Set properties 
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
		$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("Test result file");
		
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $c['manage']['lang_pack']['sales']['coupon']['code']);
		
		$i=2;
		foreach($coupon_row as $v){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['CouponNumber']);
			++$i;
		}
		
		//设置列的宽度  
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		
		//Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Simple');
		
		//Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		//Save Excel 2007 file
		$ExcelName='coupon_'.str::rand_code();
		$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($c['root_path']."{$save_dir}{$ExcelName}.xls");
		file::down_file("{$save_dir}{$ExcelName}.xls");
		file::del_file("{$save_dir}{$ExcelName}.xls");
		unset($c, $objPHPExcel, $objWriter, $coupon_row);
		exit;
		//ly200::e_json('');
	}
	
	public static function coupon_range_tools(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Html='';
		$count=20;//查询总数上限
		$p_Search=trim($p_Search);
		$p_IsCate=(int)$p_IsCate;
		$p_CateId=(int)$p_CateId;
		$p_IsUser=(int)$p_IsUser;
		$lang=$c['manage']['web_lang'];
		$lang_all=$c['manage']['lang_pack']['global']['all'];
		$lang_return=$c['manage']['lang_pack']['global']['return'];
		$DataType='';
		$DataBackPro=$DataId=0;
		if($p_Type=='products_category'){
			//产品分类
			if($p_Search){//搜索产品分类列表
				$search_row=db::get_limit('products_category', "Category{$lang} like '%$p_Search%'", "CateId, Category{$lang}", $c['my_order'].'CateId asc', 0, $count);
				foreach($search_row as $k=>$v){
					$Html.='<div class="item" data-id="'.$v['CateId'].'" data-type="products_category"><i class="icon icon_products_category"></i><span>'.$v['Category'.$lang].'</span></div>';
				}
			}elseif($p_CateId){//下一级产品分类列表
				$_UId=db::get_value('products_category', "CateId='{$p_CateId}'", 'UId');
				$UpCateId=category::get_FCateId_by_UId($_UId);//上一级分类ID
				$UId=category::get_UId_by_CateId($p_CateId);
				$category_row=db::get_limit('products_category', "UId='{$UId}'", "CateId, Category{$lang}, SubCateCount", $c['my_order'].'CateId asc', 0, $count);
				foreach($category_row as $k=>$v){
					$Html.='<div class="item" data-id="'.$v['CateId'].'" data-type="products_category"><i class="icon icon_products_category"></i><span>'.$v['Category'.$lang].'</span>'.($v['SubCateCount']?'<div class="children"><em></em></div>':'').'</div>';
				}
				$DataBackPro=1;
				$DataId=$UpCateId;
			}elseif($p_IsCate){//一级分类
				$default_row=db::get_limit('products_category', 'UId="0,"', "CateId, Category{$lang}, SubCateCount", $c['my_order'].'CateId asc', 0, $count);
				foreach($default_row as $k=>$v){
					$Html.='<div class="item" data-id="'.$v['CateId'].'" data-type="products_category"><i class="icon icon_products_category"></i><span>'.$v['Category'.$lang].'</span>'.($v['SubCateCount']?'<div class="children"><em></em>':'').'</div></div>';
				}
			}else{//默认显示
				$Html.='<div class="item" data-individual="1" data-type="products"><i class="icon icon_products"></i><span>'.$c['manage']['lang_pack']['sales']['coupon']['individual_pro'].'</span><div class="children"><em></em></div></div>';//产品
				$Html.='<div class="item" data-individual="1" data-type="tags"><i class="icon icon_tags"></i><span>'.$c['manage']['lang_pack']['sales']['coupon']['individual_tag'].'</span><div class="children"><em></em></div></div>';//标签
				$Html.='<div class="item" data-individual="1" data-type="products_category"><i class="icon icon_products_category"></i><span>'.$c['manage']['lang_pack']['global']['category'].'</span><div class="children"><em></em></div></div>';//分类
			}
			$DataType='products_category';
		}elseif($p_Type=='products'){
			//产品
			$default_row=db::get_limit('products', "Name{$lang} like '%$p_Search%'", "ProId, Name{$lang}", $c['my_order'].'ProId desc', 0, $count);
			foreach($default_row as $k=>$v){
				$Html.='<div class="item" data-id="'.$v['ProId'].'" data-type="products"><i class="icon icon_products"></i><span>'.$v['Name'.$lang].'</span></div>';
			}
			$DataType='products_category';
		}elseif($p_Type=='tags'){
			//标签
			$default_row=db::get_limit('products_tags', "Name{$lang} like '%$p_Search%'", "TId, Name{$lang}", $c['my_order'].'TId desc', 0, $count);
			foreach($default_row as $k=>$v){
				$Html.='<div class="item" data-id="'.$v['TId'].'" data-type="tags"><i class="icon icon_tags"></i><span>'.$v['Name'.$lang].'</span></div>';
			}
			$DataType='products_category';
		}elseif($p_Type=='level'){
			//会员等级
			$default_row=db::get_limit('user_level', "Name{$lang} like '%$p_Search%'", "LId, Name{$lang}", 'LId asc', 0, $count);
			$Html.='<div class="item" data-id="-1" data-type="level"><i class="icon icon_level"></i><span>'.$lang_all.$c['manage']['lang_pack']['sales']['coupon']['individual_level'].'</span></div>';//等级
			foreach($default_row as $k=>$v){
				$Html.='<div class="item" data-id="'.$v['LId'].'" data-type="level"><i class="icon icon_level"></i><span>'.$v['Name'.$lang].'</span></div>';
			}
			$DataType='user';
		}else{
			//会员
			if($p_Search || $p_IsUser){//搜索会员列表
				$user_row=db::get_limit('user', "Email like '%$p_Search%'", 'UserId, Email', 'UserId desc', 0, $count);
				$Html.='<div class="item" data-id="-1" data-type="user"><i class="icon icon_user"></i><span>'.$lang_all.$c['manage']['lang_pack']['sales']['coupon']['individual_user'].'</span></div>';//会员
				foreach($user_row as $k=>$v){
					$Html.='<div class="item" data-id="'.$v['UserId'].'" data-type="user"><i class="icon icon_user"></i><span>'.$v['Email'].'</span></div>';
				}
			}else{//默认显示
				$Html.='<div class="item" data-individual="1" data-type="user"><i class="icon icon_user"></i><span>'.$c['manage']['lang_pack']['sales']['coupon']['individual_user'].'</span><div class="children"><em></em></div></div>';//会员
				$Html.='<div class="item" data-individual="1" data-type="level"><i class="icon icon_level"></i><span>'.$c['manage']['lang_pack']['sales']['coupon']['individual_level'].'</span><div class="children"><em></em></div></div>';//等级
			}
			$DataType='user';
		}
		ly200::e_json(array('Html'=>$Html, 'Back'=>array('data-type'=>$DataType, 'data-id'=>$DataId, 'data-back-pro'=>$DataBackPro)), 1);
	}
	//优惠券管理 End

	//全场满减 Start
	public static function discount(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$Time_ary=@explode('/', $p_DeadLine);
		$StartTime=@strtotime($Time_ary[0]);
		$EndTime=@strtotime($Time_ary[1]);
		
		$Discount=(int)($p_Discount?$p_Discount:100);
		$Discount=$Discount>100?100:$Discount;
		$Discount=$Discount<0?0:$Discount;
		
		//批发价
		$sale_ary=array();
		$error=0;
		foreach((array)$p_UseCondition as $k=>$v){
			$UseCondition=(float)$p_UseCondition[$k];
			$Discount=(int)($p_Discount[$k]?$p_Discount[$k]:100);
			$Discount=$Discount>100?100:$Discount;
			$Discount=$Discount<0?0:$Discount;
			$Money=(float)$p_Money[$k];
			$sale_ary[$UseCondition]=array($Discount, $Money);
			if($p_Type==1 && $Money<=0) $error=1;
		}
		ksort($sale_ary);//从小到大排序，防止故意乱填
		
		$data=array(
			'IsUsed'		=>	((int)$p_IsUsed==1?1:0),
			'Type'			=>	((int)$p_Type==1?1:0),
			'Data'			=>	$sale_ary,
			'StartTime'		=>	$StartTime,
			'EndTime'		=>	$EndTime
		);
		//if($error) ly200::e_json(manage::get_language('sales.coupon.money_tips')); 
		$EndTime<=$StartTime && ly200::e_json(manage::get_language('sales.coupon.deadline_tips'));
		
		$ValueData=str::json_data($data);
		$w="GroupId='cart' and Variable='discount'";
		if((int)db::get_row_count('config', $w)){//更新
			db::update('config', $w, array('Value'=>$ValueData));
		}else{//新增
			db::insert('config', array(
					'GroupId'	=>	'cart',
					'Variable'	=>	'discount',
					'Value'		=>	$ValueData
				)
			);
		}
		manage::operation_log('更新全场满减设置');
		ly200::e_json('', 1);
	}
	//全场满减 End
}
?>