<?php !isset($c) && exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$c['lang_pack']['shield'];?></title>
<?=ly200::load_static('/static/css/global.css','/static/themes/default/css/global.css');?>
<style>#top_banner{display:none;}</style>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['static_path']}inc/header.php");?>
<div id="shield_page">
	<div id="shield_hd" class="wide">
		<div class="shield_sorry"><?=$c['lang_pack']['shieldSorry'];?></div>
		<p><?=$c['lang_pack']['shieldTitle_0'];?>: <a href="mailto:<?=$c['config']['global']['AdminEmail'];?>"><?=$c['config']['global']['AdminEmail'];?></a></p>
		<p><?=$c['lang_pack']['shieldTitle_1'];?>:</p>
	</div>
	<div id="shield_bd">
		<div class="shield_error">
			<div class="wide">
				<dl class="item_0 fl">
					<dt></dt>
					<dd><h3><?=$c['lang_pack']['shieldInfoHD_0'];?></h3><?=$c['lang_pack']['shieldInfoBD_0'];?></dd>
				</dl>
				<dl class="item_1 fl">
					<dt></dt>
					<dd><h3><?=$c['lang_pack']['shieldInfoHD_1'];?></h3><?=$c['lang_pack']['shieldInfoBD_1'];?></dd>
				</dl>
				<dl class="item_2 fl">
					<dt></dt>
					<dd><h3><?=$c['lang_pack']['shieldInfoHD_2'];?></h3><?=$c['lang_pack']['shieldInfoBD_2'];?></dd>
				</dl>
			</div>
		</div>
	</div>
</div>
</body>
</html>