<?php !isset($c) && exit();?>
<?php
$c['FunVersion']<1 && js::location('/');
?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?php
echo ly200::seo_meta();
include("{$c['mobile']['theme_path']}inc/resource.php");
echo ly200::load_static("{$c['mobile']['tpl_dir']}tuan/css/style.css");
?>
<script type="text/javascript">$(document).ready(function(){tuan_obj.tuan_init()});</script>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<div id="tuan">
		<div class="tuan_head">
			<div class="tuan_title"></div>
			<div class="tuan_deals_menu box_select">
				<select id="group_menu">
					<option value="this"><?=$c['lang_pack']['groupThis'];?></option>
					<option value="previous"><?=$c['lang_pack']['groupPrevious'];?></option>
				</select>
			</div>
		</div>
		<div class="tuan_menu">
			<div class="category">
				<div class="category_fixed">
					<a href="javascript:;" data="0" title="<?=$c['lang_pack']['all_category'];?>" class="current"><?=$c['lang_pack']['all'];?></a>
					<?php
					$cate_row=str::str_code(db::get_all('products_category', 'UId="0," and IsSoldOut=0', "CateId, Category{$c['lang']}",  $c['my_order'].'CateId asc'));
					foreach((array)$cate_row as $k=>$v){
					?>
						<a href="javascript:;" data="<?=$v['CateId'];?>" title="<?=$v['Category'.$c['lang']];?>"><?=$v['Category'.$c['lang']];?></a>
					<?php }?>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="tuan_btn"><a href="javascript:;" rel="nofollow" class="btn_more"><span><?=$c['lang_pack']['show'];?></span><i></i></a></div>
		<div id="prolist"></div>
	</div>
</div>
<?php
echo ly200::load_static("{$c['mobile']['tpl_dir']}tuan/js/tuan.js");
include("{$c['mobile']['theme_path']}inc/footer.php");
?>
</body>
</html>