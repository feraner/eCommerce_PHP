<?php !isset($c) && exit();?>
<?php
//模块设置
$cfg_module_row=db::get_value('config_module', "Themes='{$c['theme']}'", 'ListData');
$list_data=str::json_data($cfg_module_row, 'decode');
$cfg_module_ary=array();
foreach((array)$list_data as $k=>$v){
	$cfg_module_ary[$k]=$v;
}
substr_count($_SERVER['REQUEST_URI'], '/search/') && $cfg_module_ary['Narrow']=0;

$no_narrow_url='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, Ext, page, Narrow'));
$no_list_url='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, Ext, page, List'));
$no_sort_url='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, Ext, page, Sort'));
$no_price_url='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, Ext, page, Price'));
$no_page_url=ly200::get_query_string(ly200::query_string('m, a, CateId, page'));

$current_page='products';
$CateId=(int)$_GET['CateId'];
$Narrow=str::str_code($_GET['Narrow'], 'urlencode');
$PriceRange=str::str_code($_GET['Price']);
$Ext=(int)$_GET['Ext'];
$Sort=($_GET['Sort'] && $c['products_sort'][$_GET['Sort']])?$_GET['Sort']:'1a';

$Column=$c['nav_cfg'][3]['name'.$c['lang']];//Products
$narrow_len=2;//筛选显示行数
$page_count=(int)$cfg_module_ary['OrderNumber'];//显示数量

//产品筛选
$where='1';
if($CateId){
	$UId=category::get_UId_by_CateId($CateId);
	$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
	$category_row=db::get_one('products_category', "CateId='$CateId'");
	if(!$category_row){//分类不存在
		@header('HTTP/1.1 404');
		exit;
	}
	
	$Column=$category_row['Category'.$c['lang']];
	$category_description_row=str::str_code(db::get_one('products_category_description', "CateId='$CateId'", "Description{$c['lang']}"));
}
if($Ext){
	$Ext=($Ext<1 || $Ext>4)?1:$Ext;
	$where.=$c['where']['products_ext'][$Ext];
	switch($Ext){
		case 1: $Column=$c['nav_cfg'][8]['name'.$c['lang']];$current_page='new_arrival'; break;
		case 2: $Column=$c['nav_cfg'][9]['name'.$c['lang']];$current_page='hot_sales'; break;
		case 3: $Column=$c['nav_cfg'][10]['name'.$c['lang']];$current_page='best_deals'; break;
		case 4: $Column=$c['nav_cfg'][11]['name'.$c['lang']];$current_page='special_offer'; break;
	}
}

$screenAry=where::products($PriceRange, $Narrow);
$where.=$screenAry[0];//条件
$screenAry[1] && $Column=$screenAry[1];//标题
$price_range=$screenAry[2];//价格范围
$Narrow_ary=$screenAry[3];//筛选属性
$OrderBy=$screenAry[4];//条件排序

$page=(int)$_GET['page'];
$products_list_row=str::str_code(db::get_limit_page('products', $where.$c['where']['products'], '*', $OrderBy.$c['products_sort'][$Sort].$c['my_order'].'ProId desc', $page, $page_count));

//记录搜索痕迹
ly200::search_logs($products_list_row[1]);

(!$page || $page>$products_list_row[3]) && $page=1;
$Column=sprintf($Column, ($page_count*($page-1)+1), $page_count*($page-1)+count($products_list_row[0]), $products_list_row[1]);

//分类属性
$AttrId=$category_row['AttrId'];
if($category_row['UId']!='0,'){
	$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
	$SecCateId=category::get_FCateId_by_UId($category_row['UId']);
	$TopCategory_row=str::str_code(db::get_one('products_category', "CateId='$TopCateId'"));
	$AttrId=$TopCategory_row['AttrId'];
}
$UId_ary=@explode(',', $category_row['UId']);

if($AttrId){
	$attr_ary=$all_value_ary=$vid_name_ary=$attrid=array();
	$where='1'.where::equal(array('ParentId'=>$AttrId, 'Type'=>'1', 'CartAttr'=>'0'));
	$products_attr=str::str_code(db::get_all('products_attribute', $where, "AttrId, Name{$c['lang']}", $c['my_order'].'AttrId asc'));
	foreach((array)$products_attr as $v){
		$attr_ary[$v['AttrId']]=$v;
		$attrid[]=$v['AttrId'];
	}
	$attrid_list=@implode(',', $attrid);
	!$attrid_list && $attrid_list=0;
	$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){
		$all_value_ary[$v['AttrId']][$v['VId']]=$v;
		$vid_name_ary[$v['VId']]=$v['Value'.$c['lang']];
	}
}

//SEO
if(!$category_row){
	$spare_ary=array();
}elseif($category_row['UId']!='0,'){//非大类
	if($category_row['SubCateCount']){
		$subcate_row=db::get_limit('products_category', "UId like '{$category_row['UId']}{$CateId},%'", 'Category'.$c['lang'], $c['my_order'].'CateId asc', 0, 20);
		foreach((array)$subcate_row as $v) $subcateStr.=','.$v['Category'.$c['lang']];
	}
	$spare_ary=array(
		'SeoTitle'		=>	$category_row['Category'.$c['lang']],
		'SeoKeyword'	=>	$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']].$subcateStr,
		'SeoDescription'=>	$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']].$subcateStr
	);
}else{//大类
	$subcateStr='';
	$subcate_row=db::get_limit('products_category', "UId like '0,{$CateId},%'", 'Category'.$c['lang'], $c['my_order'].'CateId asc', 0, 20);
	foreach((array)$subcate_row as $v) $subcateStr.=','.$v['Category'.$c['lang']];
	$spare_ary=array(
		'SeoTitle'		=>	$category_row['Category'.$c['lang']],
		'SeoKeyword'	=>	$category_row['Category'.$c['lang']].$subcateStr,
		'SeoDescription'=>	$category_row['Category'.$c['lang']].$subcateStr
	);
}
if($Ext){
	$ExtTypeAry=array(1	=>'new', 2=>'hot', 3=>'best_deals', 4=>'special_offer');
	$seo_row=db::get_one('meta', "Type='{$ExtTypeAry[$Ext]}'");
	$spare_ary=array(
		'SeoTitle'		=>	$seo_row['SeoTitle'.$c['lang']],
		'SeoKeyword'	=>	$seo_row['SeoKeyword'.$c['lang']],
		'SeoDescription'=>	$seo_row['SeoDescription'.$c['lang']]
	);
}

include('static.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta($category_row, $spare_ary);?>
<?php include("{$c['static_path']}/inc/static.php");?>
<script type="text/javascript">$(function(){products_list_obj.init();});</script>
<?php
if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'] && substr_count($_SERVER['REQUEST_URI'], '/search/')){
	//Facebook Pixel
?>
	<!-- Facebook Pixel Code -->
	<script type="text/javascript">
	<!-- When a search is made, such as a product query. -->
	fbq('track', 'Search', {
		search_string: '<?=str::str_code(stripslashes($_GET['Keyword']));?>',//搜索关键词
		content_category: 'Product Search',//分类
		content_ids: ['0'],
		value: '0.00',//数值
		currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
	});
	</script>
	<!-- End Facebook Pixel Code -->
<?php }?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php
include("{$c['theme_path']}/inc/header.php");
?>
<div id="main" class="wide">
	<div id="location"><?=$c['lang_pack']['products']['position'];?>: <a href="/"><?=$c['lang_pack']['products']['home'];?></a><?=$CateId?ly200::get_web_position($category_row, 'products_category'):' &gt; '.$Column;?></div>
	<?php
	if($cfg_module_ary['IsColumn']){
	?>
	<div class="pro_left fl">
		<?php include("{$c['theme_path']}inc/products_left.php");?>
	</div>
	<?php }?>
	<div class="<?=$cfg_module_ary['IsColumn']?'pro_right fr':'pro_main';?>">
		<?php if($cfg_module_ary['IsColumn'] && $category_description_row['Description'.$c['lang']]){?><div id="category_brief" class="editor_txt"><?=htmlspecialchars_decode($category_description_row['Description'.$c['lang']]);?></div><?php }?>
		<div class="narrow_search">
			<div class="ns_title clearfix">
				<h2><?=$Column;?></h2>
				<?php if($Narrow || $price_range){?>
				<p>
					<?php foreach((array)$Narrow_ary as $v){?>
                        <a href="<?=ly200::get_narrow_url($no_narrow_url, $Narrow_ary, $v);?>" class="remove FontBgColor" title="Remove"><span><?=$vid_name_ary[$v];?></span><em></em></a>
					<?php }?>
					<?php if($price_range){?>
                        <a href="<?=$no_price_url;?>" class="remove FontBgColor" title="Remove"><span><i class="currency_data"></i><?=$price_range[0];?>-<i class="currency_data"></i><?=$price_range[1];?></span><em></em></a>
					<?php }?>
					<a href="<?='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, page, Price, Narrow'));?>" class="remove remove_all FontBgColor" title="Remove All"><span><?=$c['lang_pack']['remove_all'];?></span><em></em></a>
				</p>
				<?php }?>
			</div>
			<?php
			if($attr_ary && (!$cfg_module_ary['IsColumn'] || $cfg_module_ary['Narrow']==2)){
			?>
			<div class="ns_list">
				<?php
				$i=0;
				foreach((array)$attr_ary as $v){
				?>
					<dl class="clearfix"<?=($Narrow || $i<$narrow_len)?' overshow="false"':' overshow="true" style="display:none;"';?>>
						<dt><?=$v['Name'.$c['lang']];?>: </dt>
						<dd>
							<?php
							foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
								$url=ly200::get_narrow_url($no_narrow_url, $Narrow_ary, $k2);
								$num=ly200::get_narrow_pro_count($Narrow_ary, $k2, $CateId);
								if(!$num) continue;
							?>
							<a href="<?=$url;?>" hidefocus="true"<?=in_array($k2, $Narrow_ary)?' class="current"':'';?>>
								<em class="ns_icon_checkbox"></em>
								<span><?=$v2['Value'.$c['lang']];?><i>(<?=$num;?>)</i></span>
							</a>
							<?php }?>
						</dd>
					</dl>
				<?php
					++$i;
				}?>
				<div class="prop_more">
					<?php if(!$Narrow && count($attr_ary)>$narrow_len){?>
						<div id="more_prop" class="attr_extra"><b></b><?=$c['lang_pack']['more_options'];?></div>
						<div id="less_prop" class="attr_extra" style="display:none;"><b class="up"></b><?=$c['lang_pack']['hide'];?></div>
					<?php }?>
				</div>
			</div>
			<?php }?>
		</div>
		<div id="filter">
			<div class="prod_sort fl">
				<a<?=$Sort=='1d'?' class="cur"':'';?> href="<?=$no_sort_url.'&Sort=1d';?>"><em class="sort_icon_popular"></em><?=$c['lang_pack']['most_popular'];?></a>
				<a<?=$Sort=='2d'?' class="cur"':'';?> href="<?=$no_sort_url.'&Sort=2d';?>"><em class="sort_icon_sales"></em><?=$c['lang_pack']['sales'];?></a>
				<a<?=$Sort=='3d'?' class="cur"':'';?> href="<?=$no_sort_url.'&Sort=3d';?>"><em class="sort_icon_favorites"></em><?=$c['lang_pack']['fav'];?></a>
				<a<?=$Sort=='4d'?' class="cur"':'';?> href="<?=$no_sort_url.'&Sort=4d';?>"><em class="sort_icon_new"></em><?=$c['lang_pack']['new'];?></a>
				<a<?=($Sort=='5d' || $Sort=='5a')?' class="cur"':'';?> href="<?=$no_sort_url.'&Sort='.($Sort=='5a'?'5d':'5a');?>"><em class="sort_icon_price"></em><?=$c['lang_pack']['price'];?><i class="<?php if($Sort=='5d') echo 'sort_icon_arrow_down'; elseif($Sort=='5a') echo 'sort_icon_arrow_up'; else echo 'sort_icon_arrow';?>"></i></a>
			</div>
			<div class="prod_price fl">
				<span class="pp_inputbox"><em class="currency_data"></em><input type="text" id="minprice" class="min_box" autocomplete="off" value="<?=$price_range[0];?>"></span>
				<span class="pp_heng">-</span>
				<span class="pp_inputbox"><em class="currency_data"></em><input type="text" id="maxprice" class="max_box" autocomplete="off" value="<?=$price_range[1];?>"></span>
				<input type="button" id="submit_btn" class="pp_btn" value="Go" />
				<input type="hidden" class="no_price_url" value="<?=$no_price_url;?>" />
			</div>
			<div class="prod_menu fr">
				<?=ly200::turn_page_small_html($products_list_row[1], $products_list_row[2], $products_list_row[3], $no_page_url);?>
			</div>
		</div>
		<?php echo $products_page_contents;?>
	</div>
	<div class="blank25"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
<?php
//列表页特效跟随主色调
$cfg_module_style=db::get_value('config_module', 'IsDefault=1', 'StyleData');
$cfg_module_style=str::json_data($cfg_module_style, 'decode');
?>
<style>
.prod_list .prod_box.hover_1 .prod_box_pic{ border-color:<?=$cfg_module_style['FontColor']?>;}
.prod_list .prod_box.hover_1 .prod_box_info{ background:<?=$cfg_module_style['FontColor']?>;}
.prod_list .prod_box .prod_box_view{ background:<?=$cfg_module_style['FontColor']?>;}
.prod_list .prod_box.hover_1 .prod_box_view{ border-top:1px solid #fff;}
.prod_list .prod_box .prod_box_button{ border-top:1px #fff solid;}
.prod_list .prod_box .prod_box_button>div{ border-right:1px solid #fff;}
</style>
</body>
</html>