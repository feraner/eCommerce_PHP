<?php !isset($c) && exit();?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta();?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}js/swipe.js", "{$c['mobile']['tpl_dir']}index/{$c['mobile']['HomeTpl']}/css/style.css", "{$c['mobile']['tpl_dir']}index/{$c['mobile']['HomeTpl']}/js/index.js");?>
</head>

<body>
<?php
include("{$c['mobile']['theme_path']}inc/header.php");
include("{$c['mobile']['theme_path']}index/{$c['mobile']['HomeTpl']}/template.php");
include("{$c['mobile']['theme_path']}inc/footer.php");
?>
</body>
</html>