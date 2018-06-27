<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
?>
<script type="text/javascript">$(function(){user_obj.user_password()});</script>
<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a><em><i></i></em><a href="javascript:;">'.$c['lang_pack']['user']['password'].'</a>');?>
<div id="user" class="user_login">
	<div class="blank10"></div>
	<form action="?" method="post" class="user_login_form" id="reg_form">
		<div class="rows">
			<label class="field"><?=$c['lang_pack']['user']['curPWD'];?> <span class="fc_red">*</span></label>
			<div class="input clean"><input type="password" class="box_input" name="ExtPassword" autocomplete="off" placeholder="<?=$c['lang_pack']['user']['curPWD'];?>" data-field="<?=$c['lang_pack']['user']['curPWD'];?>" notnull /><p class="error"></p></div>
		</div>
		<div class="rows">
			<label class="field"><?=$c['lang_pack']['user']['newPWD'];?> <span class="fc_red">*</span></label>
			<div class="input clean"><input type="password" class="box_input" name="NewPassword" autocomplete="off" placeholder="<?=$c['lang_pack']['user']['newPWD'];?>" data-field="<?=$c['lang_pack']['user']['newPWD'];?>" notnull /><p class="error"></p></div>
		</div>
		<div class="rows">
			<label class="field"><?=$c['lang_pack']['user']['ConfirmPWD'];?> <span class="fc_red">*</span></label>
			<div class="input clean"><input type="password" class="box_input" name="NewPassword2" autocomplete="off" placeholder="<?=$c['lang_pack']['user']['ConfirmPWD'];?>" data-field="<?=$c['lang_pack']['user']['ConfirmPWD'];?>" notnull /><p class="error"></p></div>
		</div>
		<div class="user_login_btn">
			<div class="btn_global btn_sign_up btn_submit BuyNowBgColor"><?=$c['lang_pack']['user']['updatePWD'];?></div>
			<a href="javascript:history.go(-1);" class="btn_global btn btn_back" id="btn_back"><?=$c['lang_pack']['mobile']['back'];?></a>
		</div>
		<div class="blank25"></div>
		<input type="hidden" name="ajax_submit" value="1" />
		<input type="hidden" name="do_action" value="user.mod_password" />
	</form>
</div>