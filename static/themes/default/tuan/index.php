<?php !isset($c) && exit();?>
<?php
$c['FunVersion']<1 && js::location('/');

$current_page='tuan';
$module='default';
$ProId=(int)$_GET['ProId'];
$seo_row=str::str_code(db::get_one('meta', "Type='tuan'"));

ob_start();
if($ProId){
	include("{$module}/detail.php");
}else{
	include("{$module}/list.php");
}
$tuan_page_contents=ob_get_contents();
ob_end_clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
echo ly200::seo_meta($seo_row);
include("{$c['static_path']}/inc/static.php");
echo ly200::load_static("/static/themes/default/tuan/{$module}/css/tuan.css", "/static/themes/{$c['theme']}/css/tuan.css");
?>
<?php
if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'] && $ProId){
	//Facebook Pixel
?>
	<!-- Facebook Pixel Code -->
	<script type="text/javascript">
	<!-- When a page viewed such as landing on a product detail page. -->
	fbq('track', 'ViewContent', {
		content_type: 'product',//产品类型为产品
		content_ids: ['<?=$products_row['SKU']?$products_row['SKU']:$products_row['Prefix'].$products_row['Number'];?>'],//产品ID
		content_name: '<?=addslashes($Name);?>',//产品名称
		value: <?=cart::iconv_price($CurPrice, 2, '', 0);?>,//产品价格
		currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
	});
	
	<!-- When some adds a product to a shopping cart. -->
	$.fn.fbq_addtocart=function(){
		fbq('track', 'AddToCart', {
			content_type: 'product',//产品类型为产品
			content_ids: ['<?=$products_row['SKU']?$products_row['SKU']:$products_row['Prefix'].$products_row['Number'];?>'],//产品ID
			content_name: '<?=addslashes($Name);?>',//产品名称
			currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
		});
	}
	</script>
	<!-- End Facebook Pixel Code -->
<?php }?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div><?=$tuan_page_contents;?></div>
<?php
include("{$c['theme_path']}/inc/footer.php");
echo ly200::load_static("/static/themes/default/tuan/{$module}/js/tuan.js");
?>
</body>
</html>