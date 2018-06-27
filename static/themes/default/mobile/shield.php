<?php !isset($c) && exit();?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<title><?=$c['lang_pack']['shield'];?></title>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
</head>

<body>
<div id="shield_page">
	<div id="shield_hd" class="wrapper">
		<div class="shield_sorry"><?=$c['lang_pack']['shieldSorry'];?></div>
		<p><?=$c['lang_pack']['shieldTitle_0'];?>: <a href="mailto:<?=$c['config']['global']['AdminEmail'];?>"><?=$c['config']['global']['AdminEmail'];?></a></p>
		<p><?=$c['lang_pack']['shieldTitle_1'];?>:</p>
	</div>
	<div id="shield_bd">
		<div class="shield_error">
			<div class="wrapper">
				<dl class="item_0"><h3><?=$c['lang_pack']['shieldInfoHD_0'];?></h3><?=$c['lang_pack']['shieldInfoBD_0'];?></dl>
				<dl class="item_1"><h3><?=$c['lang_pack']['shieldInfoHD_1'];?></h3><?=$c['lang_pack']['shieldInfoBD_1'];?></dl>
				<dl class="item_2"><h3><?=$c['lang_pack']['shieldInfoHD_2'];?></h3><?=$c['lang_pack']['shieldInfoBD_2'];?></dl>
			</div>
		</div>
	</div>
</div>
</body>
</html>