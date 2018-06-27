<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class extend_module{
	public static function seo_meta_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$alone_ary=array('home', 'tuan', 'seckill', 'blog', 'new', 'hot', 'best_deals', 'special_offer');
		$much_ary=array('article'=>array('Title', 'AId'), 'info_category'=>array('Category', 'CateId'), 'info'=>array('Title', 'InfoId'), 'products_category'=>array('Category', 'CateId'), 'products'=>array('Name', 'ProId'));
		if(in_array($p_Type, $alone_ary)){
			$Id=(int)$p_MId;
			if(!$Id){
				db::insert('meta', array('Type'=>$p_Type));
				$Id=db::get_insert_id();
			}
			manage::database_language_operation('meta', "MId='$Id'", array('SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		}else{
			$Id=(int)${'p_'.$much_ary[$p_Type][1]};
			manage::database_language_operation($p_Type=='products'?'products_seo':$p_Type, "{$much_ary[$p_Type][1]}='$Id'", array('SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2));
		}
		manage::operation_log('修改页面标题与标签管理');
		ly200::e_json('', 1);
	}
	
	public static function seo_third_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_TId=(int)$p_TId;
		$data=array(
			'Title'		=>	$p_Title,
			'Code'		=>	$p_Code,
			'CodeType'	=>	(int)$p_CodeType,
			'IsUsed'	=>	(int)$p_IsUsed,
			'IsMeta'	=>	(int)$p_IsMeta
		);
		if($p_TId){
			db::update('third', "TId='$p_TId'", $data);
			manage::operation_log('修改第三方代码');
		}else{
			$data['AccTime']=$c['time'];
			db::insert('third', $data);
			manage::operation_log('添加第三方代码');
		}
		ly200::e_json('', 1);
	}
	
	public static function seo_third_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `third` SET `MyOrder` = CASE `TId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `TId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量第三方代码排序');
		ly200::e_json('', 1);
	}
	
	public static function seo_third_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_TId=(int)$g_TId;
		db::delete('third', "TId='$g_TId'");
		manage::operation_log('删除第三方代码');
		ly200::e_json('', 1);
	}
	
	public static function seo_third_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="TId in(".str_replace('-', ',', $g_group_id).")";
		db::delete('third', $del_where);
		manage::operation_log('批量删除第三方代码');
		ly200::e_json('', 1);
	}
	
	public static function seo_sitemap_create(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		include("{$c['root_path']}/inc/class/sitemap/sitemap.inc.php");
		include("{$c['root_path']}/inc/class/sitemap/config.inc.php");
		include("{$c['root_path']}/inc/class/sitemap/url_factory.inc.php");
		$obj=new Sitemap();
		$xmlHtml='';
		//header('Content-type: text/xml');
		$xmlHtml.='<?xml version="1.0" encoding="UTF-8"?>';
		$xmlHtml.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
		//首页
		$xmlHtml.='<url>';
			$xmlHtml.='<loc>'.$obj->_escapeXML(SITE_DOMAIN).'</loc>';
			$xmlHtml.='<changefreq>weekly</changefreq>';
		$xmlHtml.='</url>';  
		//产品列表页
		$row=str::str_code(db::get_all('products_category', '1', '*', $c['my_order'].'CateId asc'));
		foreach($row as $v){
			$xmlHtml.='<url>';
				$xmlHtml.='<loc>'.$obj->_escapeXML(SITE_DOMAIN.ly200::get_url($v, 'products_category', $c['manage']['web_lang'])).'</loc>';
				$xmlHtml.='<changefreq>weekly</changefreq>';
			$xmlHtml.='</url>';
		}	  
		//产品详细页
		$row=str::str_code(db::get_limit('products', "1 and ((SoldOut=0 and IsSoldOut=0) or (SoldOut=0 and IsSoldOut=1 and {$c['time']} between SStartTime and SEndTime))", '*', $c['my_order'].'ProId desc', 0, 500));
		foreach($row as $v){
			$xmlHtml.='<url>';
				$xmlHtml.='<loc>'.$obj->_escapeXML(SITE_DOMAIN.ly200::get_url($v, 'products', $c['manage']['web_lang'])).'</loc>';
				$xmlHtml.='<changefreq>weekly</changefreq>';
			$xmlHtml.='</url>';
		}
		//信息页
		$row=str::str_code(db::get_all('article', '1', '*', $c['my_order'].'AId asc'));
		foreach($row as $v){
			$url=$v['Url']?$v['Url']:SITE_DOMAIN.ly200::get_url($v, 'article', $c['manage']['web_lang']);
			$xmlHtml.='<url>';
				$xmlHtml.='<loc>'.$obj->_escapeXML($url).'</loc>';
				$xmlHtml.='<changefreq>weekly</changefreq>';
			$xmlHtml.='</url>';
		}
		//文章页
		$row=str::str_code(db::get_all('info', '1', '*', $c['my_order'].'CateId asc, InfoId desc'));
		foreach($row as $v){
			$url=$v['Url']?$v['Url']:SITE_DOMAIN.ly200::get_url($v, 'info', $c['manage']['web_lang']);
			$xmlHtml.='<url>';
				$xmlHtml.='<loc>'.$obj->_escapeXML($url).'</loc>';
				$xmlHtml.='<changefreq>weekly</changefreq>';
			$xmlHtml.='</url>';
		}
		$xmlHtml.='</urlset>';
		file::write_file('/', 'sitemap.xml', $xmlHtml);
		manage::config_operaction(array('AccTime'=>$c['time']), 'sitemap');
		manage::operation_log('生成网站地图');
		unset($xmlHtml);
		ly200::e_json('', 1);
	}
	
	public static function blog_set(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$name=(array)$p_name;
		$link=(array)$p_link;
		$Nav=array();
		foreach($name as $k=>$v){//导航
			$v && $Nav[]=array($v, $link[$k]);
		}
		$Nav=addslashes(str::json_data(str::str_code($Nav, 'stripslashes')));
		$data=array(
			'Title'				=>	$p_Title,
			'BriefDescription'	=>	$p_BriefDescription,
			'NavData'			=>	$Nav,
			'Banner'			=>	$p_Banner
		);
		manage::config_operaction($data, 'blog');
		manage::operation_log('修改博客设置');
		ly200::e_json('', 1);
	}
	
	public static function blog_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		$p_CateId=(int)$p_CateId;
		$p_IsHot=(int)$p_IsHot;
		
		$Tags=explode('|',$p_Tag);
		$Tags=array_filter($Tags);
		$Tags=$Tags?'|'.implode('|',$Tags).'|':'';
		
		$data=array(
			'CateId'			=>	(int)$p_CateId,
			'Title'				=>	$p_Title,
			'PicPath'			=>	$p_PicPath,
			'Author'			=>	$p_Author,
			'SeoTitle'			=>	$p_SeoTitle,
			'SeoKeyword'		=>	$p_SeoKeyword,
			'SeoDescription'	=>	$p_SeoDescription,
			'BriefDescription'	=>	$p_BriefDescription,
			'IsHot'				=>	$p_IsHot,
			'Tag'				=>	$Tags,
			'AccTime'			=>	$c['time'],
		);
		if($p_AId){
			db::update('blog', "AId='$p_AId'", $data);
			db::update('blog_content', "AId='$p_AId'", array('Content'=>$p_Content));
			manage::operation_log('修改博客');
		}else{
			db::insert('blog', $data);
			$AId=db::get_insert_id();
			db::insert('blog_content', array('AId'=>$AId, 'Content'=>$p_Content));
			manage::operation_log('添加博客');
		}
		ly200::e_json('', 1);
	}
	
	public static function blog_edit_myorder(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		db::update('blog', "AId='{$p_Id}'", array('MyOrder'=>$p_Number));
		manage::operation_log('博客修改排序');
		ly200::e_json(manage::language($c['manage']['my_order'][$p_Number]), 1);
	}
	
	public static function blog_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_AId=(int)$g_AId;
		db::delete('blog', "AId='$g_AId'");
		db::delete('blog_content', "AId='$g_AId'");
		manage::operation_log('删除博客');
		ly200::e_json('', 1);
	}
	
	public static function blog_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="AId in(".str_replace('-',',',$g_group_id).")";
		db::delete('blog', $del_where);
		db::delete('blog_content', $del_where);
		manage::operation_log('批量删除博客');
		ly200::e_json('', 1);
	}
	
	public static function blog_category_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CateId=(int)$p_CateId;
		$p_UnderTheCateId=(int)$p_UnderTheCateId;
		if($p_UnderTheCateId==0){
			$UId='0,';
			$Dept=1;
		}else{
			$UId=category::get_UId_by_CateId($p_UnderTheCateId, 'blog_category');
			$Dept=substr_count($UId, ',');
		}
		$data=array(
			'UId'			=>	$UId,
			'Category_en'	=>	$p_Category_en,
			'Dept'			=>	$Dept
		);
		if($p_CateId){
			db::update('blog_category', "CateId='$p_CateId'", $data);
			manage::operation_log('修改博客分类');
		}else{
			db::insert('blog_category', $data);
			$p_CateId=db::get_insert_id();
			manage::operation_log('添加博客分类');
		}
		$UId!='0,' && $p_CateId=category::get_top_CateId_by_UId($UId);
		$statistic_where.=category::get_search_where_by_CateId($p_CateId, 'blog_category');
		category::category_subcate_statistic('blog_category', $statistic_where);
		ly200::e_json('', 1);
	}
	
	public static function blog_category_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `blog_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量博客分类排序');
		ly200::e_json('', 1);
	}
	
	public static function blog_category_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CateId=(int)$g_CateId;
		$row=str::str_code(db::get_one('blog_category', "CateId='$CateId'", 'UId'));
		$del_where=category::get_search_where_by_CateId($CateId, 'blog_category');
		db::delete('blog_category', $del_where);
		manage::operation_log('删除博客分类');
		if($row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($row['UId']);
			$statistic_where=category::get_search_where_by_CateId($CateId, 'blog_category');
			category::category_subcate_statistic('blog_category', $statistic_where);
		}
		ly200::e_json('', 1);
	}
	
	public static function blog_category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_all('blog_category', $del_where));
		db::delete('blog_category', $del_where);
		manage::operation_log('批量删除博客分类');
		foreach($row as $v){
			if($v['UId']!='0,'){
				$CateId=category::get_top_CateId_by_UId($v['UId']);
				$statistic_where=category::get_search_where_by_CateId($CateId, 'blog_category');
				category::category_subcate_statistic('blog_category', $statistic_where);
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function blog_review_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$RId=(int)$g_RId;
		$RId && db::delete('blog_review', "RId='$RId'");
		manage::operation_log('删除博客评论');
		ly200::e_json('', 1);
	}
	
	public static function blog_review_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="RId in(".str_replace('-',',',$g_group_id).")";
		db::delete('blog_review', $del_where);
		manage::operation_log('批量删除博客评论');
		ly200::e_json('', 1);
	}
	
	public static function blog_review_reply(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_RId=(int)$p_RId;
		if($p_Reply){//管理员回复
			db::update('blog_review', "RId='$p_RId'", array('Reply'=>$p_Reply));
			manage::operation_log('修改博客评论');
		}
		ly200::e_json('', 1);
	}
	
	public static function translate_set(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(db::get_row_count('config', "GroupId='translate' and Variable='IsTranslate'")){
			db::update('config', "GroupId='translate' and Variable='IsTranslate'", array('Value'=>(int)$p_key));
		}else{
			db::insert('config', array(
					'GroupId'	=>	'translate',
					'Variable'	=>	'IsTranslate',
					'Value'		=>	(int)$p_key
				)
			);
		}
		manage::operation_log('修改 Google 翻译设置');
		ly200::e_json('', 1);
	}
	
	public static function translate_lang_set(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(!db::get_row_count('config', "GroupId='translate' and Variable='TranLangs'")){
			db::insert('config', array(
					'GroupId'	=>	'translate',
					'Variable'	=>	'TranLangs',
					'Value'		=>	'[]'
				)
			);
		}
		$rows=db::get_value('config', "GroupId='translate' and Variable='TranLangs'", 'Value');
		$LangArr=str::json_data($rows, 'decode');
		if((int)$p_key==1){
			$LangArr[]=trim($p_lang);
		}else{
			foreach($LangArr as $k=>$v){
				if($v==@trim($p_lang)) unset($LangArr[$k]);
			}
		}
		db::update('config', "GroupId='translate' and Variable='TranLangs'", array('Value'=>str::json_data($LangArr)));
		ly200::e_json('', 1);
	}
	
	public static function analytics_set(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(db::get_row_count('config', "GroupId='GoogleAnalytics' and Variable='client_id'")){
			db::update('config', "GroupId='GoogleAnalytics' and Variable='client_id'", array('Value'=>$p_Value));
		}else{
			db::insert('config', array(
					'GroupId'	=>	'GoogleAnalytics',
					'Variable'	=>	'client_id',
					'Value'		=>	$p_Value
				)
			);
		}
		manage::operation_log('修改 Google Analytics 设置');
		ly200::e_json('', 1);
	}
		
	public static function search_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId = $p_SId;
		$Url = $p_Url;
		$data=array(
			'Url'		=>	$Url,
			'AccTime'	=>	$c['time'],
		);
		if($SId){
			db::update('popular_search', "SId='$SId'", $data);
			manage::operation_log('修改热门搜索');
		}else{
			db::insert('popular_search', $data);
			$SId=db::get_insert_id();
			manage::operation_log('添加热门搜索');
		}
		manage::database_language_operation('popular_search', "SId='$SId'", array('Name'=>0));
		ly200::e_json('', 1);
	}
	
	public static function search_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$SId=(int)$g_SId;
		$SId && db::delete('popular_search', "SId='{$SId}'");
		manage::operation_log('删除热门搜索');
		ly200::e_json('', 1);
	}
	
	public static function search_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_sid && ly200::e_json('');
		$del_where="SId in(".str_replace('-',',',$g_group_sid).")";
		db::delete('popular_search', $del_where);	
		manage::operation_log('批量热门搜索');
		ly200::e_json('', 1);
	}
	
	public static function search_logs_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_sid && ly200::e_json('');
		$del_where="LId in(".str_replace('-',',',$g_group_sid).")";
		db::delete('search_logs', $del_where);	
		manage::operation_log('批量删除搜索统计');
		ly200::e_json('', 1);
	}
}
?>