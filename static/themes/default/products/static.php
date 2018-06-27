<?php !isset($c) && exit();?>
<?php
//模块设置
$cfg_module_row=db::get_one('config_module', "Themes='{$c['theme']}'", 'ListData, DetailData');
$list_data=str::json_data($cfg_module_row['ListData'], 'decode');
$detail_data=str::json_data($cfg_module_row['DetailData'], 'decode');
$detail_module_ary=array_flip($detail_data);
$detail_data_current=$detail_module_ary[1];

ob_start();
if($a=='goods'){//产品详细页
	$default=$detail_data_current?$detail_data_current:'module_1';
	echo ly200::load_static("/static/themes/default/css/products/detail/{$default}.css");
	if($c['theme']!='default' && is_file($c['root_path']."/static/themes/{$c['theme']}/css/products/detail/{$default}.css")){echo ly200::load_static("/static/themes/{$c['theme']}/css/products/detail/{$default}.css");}
?>
	<div id="location"><?=$c['lang_pack']['products']['position'];?>: <a href="/"><?=$c['lang_pack']['products']['home'];?></a><?=ly200::get_web_position($category_row, 'products_category');?></div>
	<?php if(in_array($default, array('module_1', 'module_2', 'module_4', 'module_5', 'module_6', 'module_7'))){?>
		<div id="prod_detail">
			<?php include("detail/{$default}.php");?>
		</div>
	<?php }elseif($default=='module_3'){?>
		<div class="pro_left fl">
			<?php include("{$c['theme_path']}inc/products_left.php");?>
		</div>
		<div id="prod_detail" class="fr">
			<?php include("detail/{$default}.php");?>
		</div>
	<?php }?>
	<div class="blank12"></div>
	<iframe name="export_pdf" id="export_pdf" class="export_pdf" src="" style="width:0px; height:0px;"></iframe>
	<?php
	ly200::set_products_history($products_row, $CurPrice, $oldPrice);
	$view_num=count($_SESSION['Ueeshop']['ViewHistory']);
	if($view_num==0){
		$_SESSION['Ueeshop']['ViewHistory']=array($products_row['ProId']);
		db::query("update products set View=View+1 where ProId='{$products_row['ProId']}'");
	}else{
		if(!in_array($products_row['ProId'], $_SESSION['Ueeshop']['ViewHistory'])){
			$_SESSION['Ueeshop']['ViewHistory'][]=$products_row['ProId'];
			db::query("update products set View=View+1 where ProId='{$products_row['ProId']}'");
		}
	}
	?>
<?php
}else{//产品列表页
	$default=$list_data['Order']?$list_data['Order']:'row_4';
	include("list/{$default}.php");
	echo ly200::load_static("/static/themes/default/css/products/list/{$default}.css", "/static/js/plugin/products/list/{$default}.js");
}
$products_page_contents=ob_get_contents();
ob_end_clean();
?>