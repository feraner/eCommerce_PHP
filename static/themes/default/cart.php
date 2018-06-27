<?php !isset($c) && exit();?>
<?php
include($c['root_path'].'/inc/lib/cart/index.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static('/static/themes/default/css/cart.css', "/static/themes/{$c['theme']}/css/cart.css", '/static/themes/default/js/cart.js');?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="w"><?=$cart_page_contents;?></div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>
