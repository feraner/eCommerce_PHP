<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class content_module{
	public static function page_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$AId=(int)$p_AId;
		$data=array(
			'CateId'	=>	(int)$p_CateId,
			'Url'		=>	$p_Url,
			'AccTime'	=>	$c['time'],
			'PageUrl'	=>	ly200::str_to_url($p_PageUrl),
			'MyOrder'	=>	(int)$p_MyOrder
		);
		if($AId){
			db::update('article', "AId='$AId'", $data);
			if(!db::get_row_count('article_content', "AId='$AId'")){
				db::insert('article_content', array('AId'=>$AId));
			}
			manage::operation_log('修改单页');
		}else{
			db::insert('article', $data);
			$AId=db::get_insert_id();
			db::insert('article_content', array('AId'=>$AId));
			manage::operation_log('添加单页');
		}
		manage::database_language_operation('article', "AId='$AId'", array('Title'=>1, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		manage::database_language_operation('article_content', "AId='$AId'", array('Content'=>3));
		ly200::e_json('', 1);
	}
	
	public static function page_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('article', "AId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('单页修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function page_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_AId=(int)$g_AId;
		db::delete('article', "AId='$g_AId'");
		db::delete('article_content', "AId='$g_AId'");
		manage::operation_log('删除单页');
		ly200::e_json('', 1);
	}
	
	public static function page_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="AId in(".str_replace('-', ',', $g_group_id).")";
		db::delete('article', $del_where);
		db::delete('article_content', $del_where);
		manage::operation_log('批量删除单页');
		ly200::e_json('', 1);
	}
	
	public static function page_category_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$data=array(
			'UId'	=>	'0,',
			'Dept'	=>	1
		);
		if($CateId){
			db::update('article_category', "CateId='$CateId'", $data);
			manage::operation_log('修改单页分类');
		}else{
			db::insert('article_category', $data);
			$CateId=db::get_insert_id();
			manage::operation_log('添加单页分类');
		}
		//manage::database_language_operation('article_category', "CateId='$CateId'", array('Category'=>1, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		manage::database_language_operation('article_category', "CateId='$CateId'", array('Category'=>1));
		ly200::e_json('', 1);
	}
	
	public static function page_category_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `article_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量单页分类排序');
		ly200::e_json('', 1);
	}
	
	public static function page_category_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_CateId=(int)$g_CateId;
		db::delete('article_category', "CateId='$g_CateId'");
		manage::operation_log('删除单页分类');
		ly200::e_json('', 1);
	}
	
	public static function page_category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_id).")";
		db::delete('article_category', $del_where);
		manage::operation_log('批量删除单页分类');
		ly200::e_json('', 1);
	}
	
	public static function news_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$InfoId=(int)$p_InfoId;
		$ImgPath=$p_PicPath;
		$data=array(
			'CateId'	=>	(int)$p_CateId,
			'Url'		=>	$p_Url,
			'PicPath'	=>	$ImgPath,
			'IsIndex'	=>	(int)$p_IsIndex,
			'EditTime'	=>	@strtotime($p_EditTime),
			'PageUrl'	=>	ly200::str_to_url($p_PageUrl),
			'MyOrder'	=>	(int)$p_MyOrder
		);
		if($InfoId){
			db::update('info', "InfoId='$InfoId'", $data);
			if(!db::get_row_count('info_content', "InfoId='$InfoId'")){
				db::insert('info_content', array('InfoId'=>$InfoId));
			}
			manage::operation_log('修改文章');
		}else{
			$data['AccTime'] = $c['time'];
			db::insert('info', $data);
			$InfoId=db::get_insert_id();
			db::insert('info_content', array('InfoId'=>$InfoId));
			manage::operation_log('添加文章');
		}
		manage::database_language_operation('info', "InfoId='$InfoId'", array('Title'=>1, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2, 'BriefDescription'=>2));
		manage::database_language_operation('info_content', "InfoId='$InfoId'", array('Content'=>3));
		ly200::e_json('', 1);
	}
	
	public static function news_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('info', "InfoId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('文章修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function news_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_InfoId=(int)$g_InfoId;
		db::delete('info', "InfoId='$g_InfoId'");
		db::delete('info_content', "InfoId='$g_InfoId'");
		manage::operation_log('删除文章');
		ly200::e_json('', 1);
	}
	
	public static function news_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="InfoId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_one('info', $del_where, 'PicPath'));
		db::delete('info', $del_where);
		db::delete('info_content', $del_where);
		manage::operation_log('批量删除文章');
		ly200::e_json('', 1);
	}
	
	public static function news_category_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$UnderTheCateId=(int)$p_UnderTheCateId;
		if($UnderTheCateId==0){
			$UId='0,';
			$Dept=1;
		}else{
			$UId=category::get_UId_by_CateId($UnderTheCateId, 'info_category');
			$Dept=substr_count($UId, ',');
		}
		$data=array(
			'UId'	=>	$UId,
			'Dept'	=>	$Dept
		);
		if($CateId){
			db::update('info_category', "CateId='$CateId'", $data);
			manage::operation_log('修改文章分类');
		}else{
			db::insert('info_category', $data);
			$CateId=db::get_insert_id();
			manage::operation_log('添加文章分类');
		}
		manage::database_language_operation('info_category', "CateId='$CateId'", array('Category'=>1));
		$UId!='0,' && $CateId=category::get_top_CateId_by_UId($UId);
		$statistic_where.=category::get_search_where_by_CateId($CateId, 'info_category');
		category::category_subcate_statistic('info_category', $statistic_where);
		ly200::e_json('', 1);
	}
	
	public static function news_category_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('info_category', "CateId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('文章分类修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function news_category_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `info_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量文章分类排序');
		ly200::e_json('', 1);
	}
	
	public static function news_category_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CateId=(int)$g_CateId;
		$row=str::str_code(db::get_one('info_category', "CateId='$CateId'", 'UId'));
		$del_where=category::get_search_where_by_CateId($CateId, 'info_category');
		db::delete('info_category', $del_where);
		if($row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($row['UId']);
			$statistic_where=category::get_search_where_by_CateId($CateId, 'info_category');
			category::category_subcate_statistic('info_category', $statistic_where);
		}
		manage::operation_log('删除文章分类');
		ly200::e_json('', 1);
	}
	
	public static function news_category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_all('business_category', $del_where));
		db::delete('info_category', $del_where);
		manage::operation_log('批量删除文章分类');
		foreach($row as $v){
			if($v['UId']!='0,'){
				$CateId=category::get_top_CateId_by_UId($v['UId']);
				$statistic_where=category::get_search_where_by_CateId($CateId, 'info_category');
				category::category_subcate_statistic('info_category', $statistic_where);
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function set_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		manage::config_operaction(array('Config'=>$p_Checked), 'content_show');
		manage::operation_log('修改内容显示设置');
		ly200::e_json('', 1);
	}
	
	public static function ad_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$AId=(int)$g_AId;
		db::delete('ad', "AId='$AId'");
		manage::operation_log('删除广告图片');
		ly200::e_json('', 1);
	}
	
	public static function ad_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PageName=$p_PageName;
		$AdPosition=$p_AdPosition;
		$AdType=(int)$p_AdType;
		$PicCount=(int)$p_PicCount;
		$Width=(int)$p_Width;
		$Height=(int)$p_Height;
		$version=(int)$p_version;//0 电脑版，1手机版
		$pagetype=(int)$p_pagetype;//0首页， 1列表页
		$data=array(
			'Themes'		=>	!$version?$c['manage']['web_themes']:'',
			'MThemesHome'	=>	$version?(!$pagetype?MHomeTpl:''):'',//手机首页
			'MThemesList'	=>	$version?($pagetype?MListTPL:''):'',//手机列表页
			'PageName'		=>	$PageName,
			'AdPosition'	=>	$AdPosition,
			'AdType'		=>	$AdType,
			'ShowType'		=>	$AdType==0?1:'',
			'PicCount'		=>	$PicCount,
			'Width'			=>	$Width,
			'Height'		=>	$Height,
		);
		db::insert('ad', $data);
		manage::operation_log('添加广告图片');
		ly200::e_json('', 1);
	}
	
	public static function ad_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		$p_PicCount=(int)$p_PicCount;
		$p_AdType=(int)$p_AdType;
		$p_ShowType=(int)$p_ShowType;
		
		if($p_AdType==0){//图片
			$Name=$Brief=$Url=$PicPath=array();
			$FormatAry=array();
			$save_dir=$c['manage']['upload_dir'].'photo/';
			foreach($c['manage']['web_lang_list'] as $v){
				for($i=0; $i<$p_PicCount; ++$i){
					$pic=$_POST['PicPath_'.$v][$i];
					if($pic && is_file($c['root_path'].$pic)){
						$pic=file::photo_tmp_upload($pic, $save_dir);
					}
					$FormatAry['Name'][$i][$v]=$_POST['Name_'.$v][$i];
					$FormatAry['Brief'][$i][$v]=$_POST['Brief_'.$v][$i];
					$FormatAry['Url'][$i][$v]=$_POST['Url_'.$v][$i];
					$FormatAry['PicPath'][$i][$v]=$pic;
				}
			}
			foreach($FormatAry as $k=>$v){
				for($i=0; $i<$p_PicCount; ++$i){
					${$k}[$i]=addslashes(str::json_data(str::str_code($v[$i], 'stripslashes')));
				}
			}
		}
		
		$data=array(
			'Name'			=>	$p_Name,
			'Contents'		=>	$p_Contents,
			'ShowType'		=>	$p_ShowType,
			'Name_0'		=>	$Name[0],
			'Name_1'		=>	$Name[1],
			'Name_2'		=>	$Name[2],
			'Name_3'		=>	$Name[3],
			'Name_4'		=>	$Name[4],
			'Brief_0'		=>	$Brief[0],
			'Brief_1'		=>	$Brief[1],
			'Brief_2'		=>	$Brief[2],
			'Brief_3'		=>	$Brief[3],
			'Brief_4'		=>	$Brief[4],
			'Url_0'			=>	$Url[0],
			'Url_1'			=>	$Url[1],
			'Url_2'			=>	$Url[2],
			'Url_3'			=>	$Url[3],
			'Url_4'			=>	$Url[4],
			'PicPath_0'		=>	$PicPath[0],
			'PicPath_1'		=>	$PicPath[1],
			'PicPath_2'		=>	$PicPath[2],
			'PicPath_3'		=>	$PicPath[3],
			'PicPath_4'		=>	$PicPath[4]
		);
		db::update('ad', "AId='$p_AId'", $data);
		ly200::e_json('', 1);
	}
	
	public static function partner_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$PId=(int)$p_PId;
		$Url=$p_Url;
		$MyOrder=$p_MyOrder;
		$PicPath=$p_PicPath;
		$data=array(
			'Url'		=>	$Url,
			'PicPath'	=>	$PicPath,
			'AccTime'	=>	$c['time'],
			'MyOrder'	=>	(int)$MyOrder
		);
		if($PId){
			$data['IsUsed']=(int)$p_IsUsed==1?1:0;
			db::update('partners', "PId='$PId'", $data);
			manage::operation_log('修改友情链接');
		}else{
			db::insert('partners', $data);
			$PId=db::get_insert_id();
			manage::operation_log('添加友情链接');
		}
		manage::database_language_operation('partners', "PId='$PId'", array('Name'=>1));
		ly200::e_json('', 1);
	}
	
	public static function partner_used(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'g');
		$PId=(int)$g_PId;
		$IsUsed=(int)$g_IsUsed==1?1:0;
		!$PId && ly200::e_json('请勿非法操作');
		db::update('partners', "PId='$PId'", array('IsUsed'=>$IsUsed));
		manage::operation_log(($IsUsed==1?'启用':'关闭').'友情链接');
		ly200::e_json('', 1);
	}
	
	public static function partner_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$PId=(int)$g_PId;
		db::delete('partners', "PId='$PId'");
		manage::operation_log('删除友情链接');
		ly200::e_json('', 1);
	}
	
	public static function partner_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_pid && ly200::e_json('');
		$del_where="PId in(".str_replace('-',',',$g_group_pid).")";
		db::delete('partners', $del_where);
		manage::operation_log('批量删除友情链接');
		ly200::e_json('', 1);
	}
}
?>