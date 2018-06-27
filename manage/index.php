<?php
include('../inc/global.php');
include('static/inc/init.php');
ob_start();

$order_wait_payment_count=(int)db::get_row_count('orders', 'OrderStatus=1');
$order_wait_delivery_count=(int)db::get_row_count('orders', 'OrderStatus=4');
$prod_warning_count=(int)db::get_row_count('products', 'Stock<=WarnStock');
$inbox_noread_count=(int)db::get_row_count('user_message', 'Type=0 and IsRead=0');
$error_count=$order_wait_payment_count+$order_wait_delivery_count+$prod_warning_count+$inbox_noread_count;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="robots" content="noindex,nofollow">
<meta name="renderer" content="webkit">
<link rel="shortcut icon" href="<?=$c['manage']['config']['IcoPath'];?>" />
<title>{/frame.system_name/}</title>
<?=ly200::load_static('/static/css/global.css', '/static/manage/css/frame.css', '/static/manage/css/animate.css');?>
<?=ly200::load_static('/static/js/jquery-1.7.2.min.js', "/static/js/lang/{$c['manage']['config']['ManageLanguage']}.js", '/static/js/global.js', '/static/manage/js/frame.js');?>
<?=ly200::load_static("/static/manage/css/{$c['manage']['module']}.css", "/static/manage/js/{$c['manage']['module']}.js"); ?>
<?=ly200::load_static('/static/js/plugin/tool_tips/tool_tips.js', '/static/js/plugin/jscrollpane/jquery.mousewheel.js', '/static/js/plugin/jscrollpane/jquery.jscrollpane.js', '/static/js/plugin/jscrollpane/jquery.jscrollpane.css');?>
<style type="text/css">body,html,h1,h2,h3,h4,h5,h6,input,select,textarea{<?=$c['manage']['config']['ManageLanguage']=='zh-cn'?'font-family:"微软雅黑"':'font-size:12px';?>;}</style>
<script language="javascript">
var session_id='<?=session_id();?>';
var ueeshop_config={"curDate":"<?=date('Y/m/d H:i:s', $c['time']);?>","lang":"<?=substr($c['manage']['web_lang'], 1);?>","manage_language":"<?=$c['manage']['config']['ManageLanguage'];?>","currency":"<?=$c['manage']['currency_symbol'];?>","currSymbol":"<?=$c['manage']['currency_symbol'];?>","language":<?=str::json_data($c['manage']['config']['Language']);?>}
$(document).ready(function(){
	frame_obj.page_init();
	frame_obj.windows_init();
	<?php
	if((int)$c['manage']['config']['PromptSteps']){
		db::update('config', "GroupId='global' and Variable='PromptSteps'", array('Value'=>0));
		echo 'frame_obj.prompt_steps();';
	}
	?>
});
</script>
</head>

<body class="<?=$c['manage']['config']['ManageLanguage'];?>">
<?php
if($c['manage']['action']=='login'){
	include('account/login.php');
}elseif($c['manage']['iframe']==1){	//弹窗
	include("{$c['manage']['module']}/{$c['manage']['action']}.php");
}else{
	$LogoPath=@is_file($c['root_path'].$c['manage']['config']['LogoPath'])?$c['manage']['config']['LogoPath']:'/static/manage/images/frame/logo.png';
	(int)$c['UeeshopAgentId'] && $LogoPath='http://a.vgcart.com/agent/?do_action=action.agent_logo&AgentId='.(int)$c['UeeshopAgentId'];	//代理商LOGO
?>
	<div id="header">
		<div class="logo pic_box"><a href="./"><img src="<?=$LogoPath;?>" /></a><span></span></div>
		<ul class="menu">
			<li class="ico_0 uee_info">
				<a href="javascript:;"><?php if(manage::check_permit('orders') && manage::check_permit('orders', 0, array('a'=>'orders'))){?><i><?=$error_count<99?$error_count:'99+';?></i><?php }?></a>
				<dl class="fadeInDown animate">
					<?php if(manage::check_permit('orders') && manage::check_permit('orders', 0, array('a'=>'orders'))){?>
						<dt><a href="./?m=orders&a=orders&OrderStatus=1"><span>{/account.wait_payment_order/}</span><i><?=$order_wait_payment_count;?></i></a></dt>
						<dt><a href="./?m=orders&a=orders&OrderStatus=4"><span>{/account.wait_delivery_order/}</span><i><?=$order_wait_delivery_count;?></i></a></dt>
					<?php }?>
					<dt><a href="./?m=products&a=products&Other=8"><span>{/account.stock_warning/}</span><i><?=$prod_warning_count;?></i></a></dt>
					<?php if((int)$c['FunVersion'] && manage::check_permit('user') && manage::check_permit('user', 0, array('a'=>'inbox'))){?><dt><a href="./?m=user&a=inbox&d=products"><span>{/module.user.inbox.module_name/}</span><i><?=$inbox_noread_count;?></i></a></dt><?php }?>
					<?php if(manage::check_permit('manage') && manage::check_permit('manage', 0, array('a'=>'manage'))){?><dt><a href="./?m=manage&d=manage">{/module.manage.manage/}</a></dt><?php }?>
					<?php if(manage::check_permit('manage') && manage::check_permit('manage', 0, array('a'=>'manage_logs'))){?><dt><a href="./?m=manage&d=manage_logs">{/module.manage.manage_logs/}</a></dt><?php }?>
					<?php if(!$c['UeeshopAgentId']){?><dt><a href="javascript:;" class="user_service">{/account.customer_service/}</a></dt><?php }?>
					<dt><a href="javascript:;" class="user_backup">{/account.backup_time/}</a></dt>
					<dt class="dot"></dt>
				</dl>
			</li>
			<li class="ico_1 <?=!in_array($c['manage']['module'], array('mobile', 'email', 'mta', 'manage'))?'cur':'';?>"><a href="./"><em class="fadeInUp animate">{/frame.pc/}</em></a></li>
			<?php if((int)$c['FunVersion'] || (!(int)$c['FunVersion'] && !(int)$c['NewFunVersion'])){?><li class="ico_2 <?=$c['manage']['module']=='mobile'?'cur':'';?>"><a href="./?m=mobile"><em class="fadeInUp animate">{/frame.mobile/}</em></a></li><?php }?>
			<?php if(manage::check_permit('email')){?><li class="ico_3 <?=$c['manage']['module']=='email'?'cur':'';?>"><a href="./?m=email"><em class="fadeInUp animate">{/frame.email/}</em></a></li><?php }?>
			<?php if(manage::check_permit('mta')){?><li class="ico_4 <?=$c['manage']['module']=='mta'?'cur':'';?>"><a href="./?m=mta"><em class="fadeInUp animate">{/frame.mta/}</em></a></li><?php }?>
			<?php if(!(int)$c['UeeshopAgentId'] && manage::check_permit('manage') && manage::check_permit('manage', 0, array('a'=>'course')) && $c['FunVersion']!=100){?><li class="ico_5 <?=($c['manage']['module']=='manage' && $c['manage']['do']=='course')?'cur':'';?>"><a href="http://help.ueeshop.com/mall/" target="_blank"><em class="fadeInUp animate">{/frame.course/}</em></a></li><?php }?>
			<li class="ico_6"><a href="<?=ly200::get_domain();?>" target="_blank"><em class="fadeInUp animate">{/frame.home/}</em></a></li>
			<li class="ico_7"><a href="javascript:;" class="clear_cache"><em class="fadeInUp animate">{/frame.clear_cache/}</em></a></li>
			<li class="ico_8"><a href="./?do_action=account.logout"><em class="fadeInUp animate">{/account.logout/}</em></a></li>
		</ul>
	</div>
	<div id="main">
		<div class="menu">
			<div class="menu_ico">
				<?php
				$menu_id=in_array($c['manage']['module'], array('email', 'mta', 'mobile', 'manage'))?$c['manage']['module']:'pc';
				$un_menu_ary=array(
					0=>array(array('account'), array('set.authorization', 'extend.blog', 'products.business', 'products.aliexpress', 'products.sync', 'user.inbox', 'user.message', 'email.send', 'sales.tuan', 'sales.seckill', 'sales.holiday', 'sales.package', 'sales.promotion', 'sales.coupon', 'sales.discount')),//标准版
					1=>array(array('account'), array('sales.tuan', 'sales.seckill', 'sales.holiday', 'extend.blog')),//高级版
					2=>array(array('account'), array()),//专业版
					100=>array(array('account'), array('set.themes', 'content.set', 'products.aliexpress', 'orders.check', 'sales.holiday', 'extend.analytics'))//定制版
				);
				!$c['FunVersion'] && $c['NewFunVersion']==2 && $un_menu_ary[0][1]=array_merge($un_menu_ary[0][1], array('products.upload', 'products.upload_new'));
				$c['FunVersion']<2 && $c['NewFunVersion']>2 && $un_menu_ary[1][1]=array_merge($un_menu_ary[1][1], array('set.authorization', 'products.sync'));
				
				foreach($c['manage']['permit'][$menu_id] as $k=>$v){
					if(in_array($k, $un_menu_ary[$c['FunVersion']][0]) || !manage::check_permit($k)) continue;
					
					$host_ary=explode('.', $_SERVER['HTTP_HOST']);
					$mLink=($k=='mpreview'?' src="http://m.'.(in_array(reset($host_ary), ly200::subdomain_list())?implode('.', array_slice($host_ary, 1)):$_SERVER['HTTP_HOST']).'"':'');
				?>
					<div class="ico ico_<?=$k;?> <?=$c['manage']['module']==$k?'cur':'';?>"><a href="<?=$k=='mpreview'?'javascript:;':"./?m={$k}";?>"<?=$mLink;?>>{/module.<?=$k;?>.module_name/}</a><em></em></div>
				<?php }?>
			</div>
			<?php if(!in_array($c['manage']['module'], array('account', 'email', 'mobile', 'manage')) && @!in_array($c['manage']['module'], $un_menu_ary[$c['FunVersion']][0])){?>
				<div class="menu_list">
					<dl>
						<?php
						foreach($c['manage']['permit'][$menu_id][$c['manage']['module']] as $k=>$v){
							if((isset($v['menu']) && in_array($c['manage']['module'].'.'.$k.'.'.$v[0], $un_menu_ary[$c['FunVersion']][1])) || in_array($c['manage']['module'].'.'.$k, $un_menu_ary[$c['FunVersion']][1]) || !manage::check_permit($c['manage']['module'], 0, array('a'=>$k))) continue;
							if($c['manage']['module']=='products' && $k=='upload') continue; //踢走旧版批量上传
							if($c['manage']['module']=='extend' && $k=='analytics' && !db::get_value('config', "GroupId='GoogleAnalytics' and Variable='client_id'", 'value')) continue; //取消Google流量统计功能
							$action_ary=@explode('.', $c['manage']['action']);
						?>
							<dt class="<?=((reset($action_ary)==$k && !is_numeric($k)) || $c['manage']['action']==$v)?'cur':'';?>"><?=isset($v['menu'])?'<a href="./?m='.$c['manage']['module'].'&a='.$k.'"><span>{/module.'.$c['manage']['module'].'.'.$k.'.module_name/}</span></a>':'<a href="./?m='.$c['manage']['module'].'&a='.$k.'"><span>{/module.'.$c['manage']['module'].'.'.$k.'/}</span></a>';?></dt>
						<?php }?>
					</dl>
				</div>
				<div class="menu_button"><a href="javascript:;"><i></i></a></div>
			<?php }else{?>
				<div class="menu_button"></div>
			<?php }?>
		</div>
		<div id="righter" class="righter">
			<?php
				$permit_0=$permit_1=0;
				@in_array($c['manage']['module'], $un_menu_ary[$c['FunVersion']][0]) && $c['manage']['module']!='account' && $permit_0=1;
				@in_array("{$c['manage']['module']}.{$c['manage']['action']}", $un_menu_ary[$c['FunVersion']][1]) && $permit_1=1;
				if($permit_0 || $permit_1){
					echo '{/manage.manage.no_permit/}';
				}else{
					include("{$c['manage']['module']}/{$c['manage']['action']}.php");
				}
			?>
        </div>
		<div class="clear"></div>
	</div>
<?php
}
$html=ob_get_contents();
ob_end_clean();
echo manage::language($html);
?>
</body>
</html>