<?php !isset($c) && exit();?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<title>404</title>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}js/404.js");?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div id="error_page" class="wrapper">
	<div class="error_404 FontColor">404</div>
    <div class="error_warning sw"><?=$c['lang_pack']['errorWarning'];?></div>
	<div class="error_nav sw"><a href="javascript:;"><?=$c['lang_pack']['goBack'];?></a>|<a href="/"><?=$c['lang_pack']['homePage'];?></a></div>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
</body>
</html>