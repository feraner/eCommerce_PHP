<?php !isset($c) && exit();?>
<?php
$login_module_ary=array('index', 'order', 'review', 'favorite', 'coupon', 'address', 'setting', 'message', 'print', 'products');	//需要登录的模块列表
if($c['FunVersion']>=1) $login_module_ary[]='inbox';
$un_login_module_ary=array('register', 'binding', 'forgot');	//不需要登录的模块列表 'login',

if((int)$_SESSION['User']['UserId']){	//已登录
	$module_ary=$login_module_ary;
}else{	//未登录
	in_array($a, $login_module_ary) && js::location("/account/login.html?&JumpUrl=".urlencode($_GET['JumpUrl']));
	$module_ary=$un_login_module_ary;	//重置模块列表
}
!in_array($a, $module_ary) && $a=$module_ary[0];

ob_start();
if((int)$_SESSION['User']['UserId'] && $a!='print'){	//已登录的，架构会员中心页面内容排版
	$UserId=$_SESSION['User']['UserId'];
	$user_row=str::str_code(db::get_one('user', $c['where']['user']));
	$user_msg_row=str::str_code(db::get_all('user_message', "(UserId like '%|{$UserId}|%' or UserId='-1') and Module='others' and Type=1", '*', 'MId desc'));
	$user_msg_len=0;
	foreach((array)$user_msg_row as $k=>$v){
		$is_read=0;
		if($v['IsRead']){
			$userid_ary=@array_flip(explode('|', $v['UserId']));
			$isread_ary=@explode('|', $v['IsRead']);
			$is_read=$isread_ary[$userid_ary[$UserId]];
			!$is_read && $user_msg_len+=1;
		}
	}
	$user_prod_row=db::get_row_count('user_message', "{$c['where']['user']} and Module='products' and IsReply=1", '*', 'MId desc');
	$user_order_row=db::get_row_count('user_message', "{$c['where']['user']} and Module='orders' and IsReply=1", '*', 'MId desc');
?>
    <div id="lib_user" class="clearfix">
		<?php include('module/crumb.php');?>
        <?php include('module/menu.php');?>
        <div id="lib_user_main">
            <?php include("module/{$a}.php");?>
        </div>
    </div>
<?php 
}else{
	include("module/{$a}.php");
}
$user_page_contents=ob_get_contents();
ob_end_clean();

@in_array($a, $un_login_module_ary) && exit($user_page_contents);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static("/static/themes/{$c['theme']}/css/user.css");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide"><?=$user_page_contents;?></div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>