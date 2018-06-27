<?php !isset($c) && exit();?>
<?php
manage::check_permit('extend', 1, array('a'=>'analytics'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“Client ID 设置”页面
	$c['manage']['do']='set';
}
?>
<div class="r_nav">
	<h1>{/module.extend.analytics.module_name/}</h1>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['extend']['analytics']['menu'] as $k=>$v){
			if(!manage::check_permit('extend', 0, array('a'=>'analytics', 'd'=>$v))){
				if($v=='set' && $c['manage']['do']=='set') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
		?>
		<dt></dt>
		<dd><a href="./?m=extend&a=analytics&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.extend.analytics.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=extend&a=analytics&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
		<dt></dt>
		<dd><a href="https://www.google.com/analytics/" target="_blank">{/analytics.detail/}</a></dd>
	</dl>
</div>
<div id="analytics" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='set'){
		//Client ID 设置
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.analytics_set_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/analytics.client_id/}</label>
				<span class="input"><input type="text" name="Value" class="form_input" value="<?=db::get_value('config', "GroupId='GoogleAnalytics' and Variable='client_id'", 'Value');?>" size="80" maxlength="100" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="extend.analytics_set" />
		</form>
	<?php
	}elseif($c['manage']['do']=='analytics'){
		//查看浏量统计
	?>
		<iframe frameborder="0" src="http://www.ueeshop.com/analytics/?v=<?=base64_encode(db::get_value('config', "GroupId='GoogleAnalytics' and Variable='client_id'", 'Value'));?>"></iframe>
	<?php }?>
</div>