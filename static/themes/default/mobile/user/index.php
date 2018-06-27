<?php !isset($c) && exit();?>
<?php
$login_module_ary=array('index', 'order', 'favorite', 'coupon', 'address', 'setting', 'password','inbox'); //需要登录的模块列表
$un_login_module_ary=array('login', 'binding', 'forgot'); //不需要登录的模块列表 'login',

if((int)$_SESSION['User']['UserId']){ //已登录
	$module_ary=$login_module_ary;
	$module_ary[]='address';
}else{ //未登录
	in_array($a, $login_module_ary) && js::location("/account/login.html?&JumpUrl=".urlencode($_GET['JumpUrl']));
	$module_ary=$un_login_module_ary; //重置模块列表
}
!in_array($a, $module_ary) && $a=$module_ary[0];
if((int)$_SESSION['User']['UserId']){
	$user_row=str::str_code(db::get_one('user', $c['where']['user']));
}
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta();?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}css/user.css","{$c['mobile']['tpl_dir']}js/user.js");?>
<style>header{ display:none;}</style>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<?php include("{$c['mobile']['theme_path']}user/module/include/header.php");?>
<div class="wrapper">
	<?php include("{$c['mobile']['theme_path']}user/module/{$a}.php");?>
</div>
</body>
</html>