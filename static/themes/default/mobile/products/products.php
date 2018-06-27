<?php !isset($c) && exit();?>
<?php
$no_sort_url='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, Ext, page, Sort'));

$Column='';
//查询
$query_string=ly200::get_query_string(ly200::query_string('m, a, CateId, page'));
$page_count=20;
$page=(int)$_GET['page'];
$CateId=(int)$_GET['CateId'];
$Keyword=$_GET['Keyword'];
$Narrow=str::str_code($_GET['Narrow'], 'urlencode');
$Ext=(int)$_GET['Ext'];
$Sort=($_GET['Sort'] && $c['products_sort'][$_GET['Sort']])?$_GET['Sort']:'1a';

//产品筛选
$where=1;
if($CateId){
	$UId=category::get_UId_by_CateId($CateId);
	$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
	$category_row=db::get_one('products_category', "CateId='$CateId'");
	if(!$category_row){//分类不存在
		@header('HTTP/1.1 404');
		exit;
	}
	
	$Column=ly200::get_web_position($category_row, 'products_category', '', '<em><i></i></em>', 12);
	//SEO
	if($category_row['UId']!='0,'){//非大类
		if($category_row['SubCateCount']){
			$subcate_row=db::get_limit('products_category', "UId like '{$category_row['UId']}{$CateId},%'", 'Category'.$c['lang'], $c['my_order'].'CateId asc', 0, 20);
			foreach($subcate_row as $v) $subcateStr.=','.$v['Category'.$c['lang']];
		}
		$spare_ary=array(
			'SeoTitle'		=>	$category_row['Category'.$c['lang']],
			'SeoKeyword'	=>	$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']].$subcateStr,
			'SeoDescription'=>	$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']].$subcateStr
		);
	}else{//大类
		$subcateStr='';
		$subcate_row=db::get_limit('products_category', "UId like '0,{$CateId},%'", 'Category'.$c['lang'], $c['my_order'].'CateId asc', 0, 20);
		foreach($subcate_row as $v) $subcateStr.=','.$v['Category'.$c['lang']];
		$spare_ary=array(
			'SeoTitle'		=>	$category_row['Category'.$c['lang']],
			'SeoKeyword'	=>	$category_row['Category'.$c['lang']].$subcateStr,
			'SeoDescription'=>	$category_row['Category'.$c['lang']].$subcateStr
		);
	}
}else{
	$Column='<em><i></i></em><a href="javascript:;">'.$c['lang_pack']['mobile']['pro_list'].'</a>';
}
if($Keyword){
	$Column='<em><i></i></em><a href="javascript:;">'.str_replace('xxx', $Keyword, $c['lang_pack']['mobile']['key_count']).'</a>';
}
if($Ext){
	$Ext=($Ext<1 || $Ext>4)?1:$Ext;
	$where.=$c['where']['products_ext'][$Ext];
	switch($Ext){
		case 1: $Column='<em><i></i></em><a href="javascript">'.$c['nav_cfg'][8]['name'.$c['lang']].'</a>'; break;
		case 2: $Column='<em><i></i></em><a href="javascript">'.$c['nav_cfg'][9]['name'.$c['lang']].'</a>'; break;
		case 3: $Column='<em><i></i></em><a href="javascript">'.$c['nav_cfg'][10]['name'.$c['lang']].'</a>'; break;
		case 4: $Column='<em><i></i></em><a href="javascript">'.$c['nav_cfg'][11]['name'.$c['lang']].'</a>'; break;
	}
}

$screenAry=where::products('', $Narrow);
$where.=$screenAry[0];//条件
$Narrow_ary=$screenAry[3];//筛选属性
$OrderBy=$screenAry[4];//条件排序
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
	!$attrid_list && $attrid_list='0';
	$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){
		$all_value_ary[$v['AttrId']][$v['VId']]=$v;
		$vid_name_ary[$v['VId']]=$v['Value'.$c['lang']];
	}
}
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($category_row, $spare_ary);?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}products/{$c['mobile']['ListTpl']}/css/style.css");?>
<?php
if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'] && $_GET['Keyword']){
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

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<?=html::mobile_crumb($Column);?>
	<?php
	if($attr_ary){
	?>
	<div class="pop_up pop_up_right modal_side">
		<div class="pop_up_container clean">
			<div class="side_head ui_border_b">
				<a class="close side_close" href="javascript:;"><em><i></i></em></a>
				<div class="side_title"><?=$c['lang_pack']['mobile']['refine'];?></div>
			</div>
			<div class="clear"></div>
			<div class="menu_list">
				<?php
				$i=0;
				foreach((array)$attr_ary as $v){
					$current_ary=array();
					foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
						if(in_array($k2, $Narrow_ary)) $current_ary[$k2]=$v2['Value'.$c['lang']];
					}
				?>
					<div class="item son ui_border_b<?=count($current_ary)>0?' open':'';?>">
						<a href="javascript:;" title="<?=$v['Name'.$c['lang']];?>"><strong><?=$v['Name'.$c['lang']];?></strong></a>
						<div class="icon"><em><i></i></em></div>
						<div class="menu_son attr_son ui_border_t"<?=count($current_ary)>0?' style="display:block;"':'';?>>
							<?php
							foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
								$num=ly200::get_narrow_pro_count($Narrow_ary, $k2, $CateId);
								if(!$num) continue;
							?>
								<span id="<?=$k2;?>"<?=in_array($k2, $Narrow_ary)?' class=" current FontBgColor"':'';?>><strong><?=$v2['Value'.$c['lang']];?></strong><em></em></span>
							<?php }?>
						</div>
					</div>
				<?php }?>
			</div>
			<div class="menu_button clean">
				<button type="button" class="btn btn_global btn_default clear_all"><em></em><?=$c['lang_pack']['clear'];?></button>
				<button type="button" class="btn btn_global btn_primary refine_search FontBgColor"><em></em><?=$c['lang_pack']['mobile']['apply'];?></button>
			</div>
		</div>
	</div>
	<?php }?>
	<div class="pop_up pop_up_right sort_by_side">
		<div class="pop_up_container clean">
			<div class="side_head ui_border_b">
				<a class="close side_close" href="javascript:;"><em><i></i></em></a>
				<div class="side_title">Sort By</div>
			</div>
			<div class="clear"></div>
			<ul class="menu_list ui_border_b">
				<li class="sort_by_item"><a href="<?=$no_sort_url;?>"<?=$Sort=='1a'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['mobile']['default'];?><em></em></a></li>
				<li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=1d';?>"<?=$Sort=='1d'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['most_popular'];?><em></em></a></li>
				<li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=2d';?>"<?=$Sort=='2d'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['sales'];?><em></em></a></li>
				<?php /* ?><li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=3d';?>"<?=$Sort=='3d'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['fav'];?><em></em></a></li> <?php */ ?>
				<li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=4d';?>"<?=$Sort=='4d'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['new'];?><em></em></a></li>
				<li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=5a';?>"<?=$Sort=='5a'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['price'];?>↑<em></em></a></li>
				<li class="sort_by_item"><a href="<?=$no_sort_url.'&Sort=5d';?>"<?=$Sort=='5d'?' class="current FontBgColor"':'';?>><?=$c['lang_pack']['price'];?>↓<em></em></a></li>
			</ul>
		</div>
	</div>
	<div id="filter" class="clean ui_border_b">
		<?php
		switch($Sort){
			case '1d': $sort_by=$c['lang_pack']['most_popular']; break;
			case '2d': $sort_by=$c['lang_pack']['sales']; break;
			case '3d': $sort_by=$c['lang_pack']['fav']; break;
			case '4d': $sort_by=$c['lang_pack']['new']; break;
			case '5a': $sort_by=$c['lang_pack']['price'].'↑'; break;
			case '5d': $sort_by=$c['lang_pack']['price'].'↓'; break;
			default: $sort_by='Sort By'; break;
		}?>
		<ul class="prod_sort fl">
			<li class="dropdown"><a href="javascript:;" class="dropdown_toggle"><?=$sort_by;?><i class="icon_sort"></i><em></em></a></li>
			<?php if($attr_ary){?>
				<li class="dropdown"><a href="javascript:;" class="dropdown_modal"><?=$c['lang_pack']['mobile']['refine'];?><i class="icon_refine"></i></a></li>
			<?php }?>
		</ul>
	</div>
	<div id="pro_box" class="clean">
		<div id="prolist" class="over" data-pro="<?=htmlspecialchars(str::json_data($_GET));?>" data-number="0" data-page="1" data-total="<?=$products_list_row[3];?>" data-search="<?=substr_count($_SERVER['REQUEST_URI'], '/search/');?>" data-count="<?=$page_count;?>"></div>
		<div id="prolist_mask"></div>
	</div>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
<script type="text/javascript" src="<?="{$c['mobile']['tpl_dir']}js/products.js";?>"></script>
</body>
</html>