<?php !isset($c) && exit();?>
<script type="text/javascript">$(document).ready(function(){account_obj.login_init();});</script>
<style type="text/css">body,html{background:url(/static/manage/images/account/login_bg.jpg) no-repeat center 0; background-size:cover; overflow:hidden;}</style>
<?php 
$LogoPath=@is_file($c['root_path'].$c['manage']['config']['LogoPath'])?$c['manage']['config']['LogoPath']:'/static/manage/images/account/login_limg.png';
(int)$c['UeeshopAgentId']>1 && $LogoPath='http://a.vgcart.com/agent/?do_action=action.agent_logo&AgentId='.(int)$c['UeeshopAgentId'];	//代理商LOGO
?>
<div id="login">
	<div class="limg"><div class="pic_box"><img src="<?=$LogoPath;?>" /><span></span></div></div>
	<form>
		<h2>{/account.welcome/}</h2>
		<div class="f">
			<div class="input username"><input name="UserName" id="UserName" type="text" maxlength="50" value="" autocomplete="off" placeholder="{/account.username/}"></div>
			<div class="input password"><input name="Password" id="Password" type="password" maxlength="50" value="" autocomplete="off" placeholder="{/account.password/}"></div>
			<input type="submit" class="submit" value="{/account.login_btn/}">
		</div>
		<input type="hidden" name="do_action" value="account.login">
	</form>
</div>