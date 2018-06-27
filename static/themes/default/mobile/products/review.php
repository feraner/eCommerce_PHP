<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$pro_url=ly200::get_url($products_row, 'products');
$Name=$products_row['Name_en'];
$Number=$products_row['Prefix'].$products_row['Number'];
$price_ary=cart::range_price_ext($products_row);
$CateId=(int)$products_row['CateId'];
$category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));

$is_review=db::get_row_count('products_review', "ProId='$ProId'")?false:true;

$Rating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewRating'])?(int)$products_row['DefaultReviewRating']:(int)$products_row['Rating'];
$TotalRating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewTotalRating'])?$products_row['DefaultReviewTotalRating']:$products_row['TotalRating'];
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta();?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}css/goods.css", '/static/js/plugin/lightbox/css/lightbox.min.css');?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<div class="goods review_goods prod_layer">
		<nav class="layer_head ui_border_b"><a class="layer_back" href="javascript:history.go(-1);"><em><i></i></em></a><div class="layer_title"><?=$c['lang_pack']['mobile']['reviews'];?> (<?=$TotalRating;?>)</div></nav>
	</div>
	<div class="prod_info_divide"></div>
	<?php include('review_box.php');?>
</div>
<?=ly200::load_static('/static/js/plugin/lightbox/js/lightbox.min.js');?>
</body>
</html>