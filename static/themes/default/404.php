<?php !isset($c) && exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>404</title>
<?php include("{$c['static_path']}/inc/static.php");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="error_page">
	<div id="main" class="wide">
		<div class="error_logo sw"></div>
		<div class="error_warning sw"><?=$c['lang_pack']['errorWarning'];?></div>
		<div class="error_nav sw"><a href="javascript:;"><?=$c['lang_pack']['goBack'];?></a>|<a href="/"><?=$c['lang_pack']['homePage'];?></a>|<a href="<?=$c['config']['global']['ContactUrl']?$c['config']['global']['ContactUrl']:'javascript:;';?>"><?=$c['lang_pack']['contactUs'];?></a></div>
	</div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>