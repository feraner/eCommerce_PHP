<?php !isset($c) && exit();?>
<?php
$config_row=str::json_data(db::get_value('config', 'GroupId="email" and Variable="config"', 'Value'), 'decode');
?>
<script type="text/javascript">$(function(){email_obj.config_init();})</script>
<div class="r_nav">
	<h1>{/module.email.config/}</h1>
</div>
<div id="config" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="rows">
			<label>{/email.from_email/}</label>
			<span class="input"><input name="FromEmail" value="<?=$config_row['FromEmail'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/email.from_name/}</label>
			<span class="input"><input name="FromName" value="<?=$config_row['FromName'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows module1">
			<label>{/email.smtp/}</label>
			<span class="input"><input name="SmtpHost" value="<?=$config_row['SmtpHost'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows module1">
			<label>{/email.port/}</label>
			<span class="input"><input name="SmtpPort" value="<?=$config_row['SmtpPort'];?>" type="text" class="form_input" size="5" maxlength="5" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows module1">
			<label>{/email.email/}</label>
			<span class="input"><input name="SmtpUserName" value="<?=$config_row['SmtpUserName'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows module1">
			<label>{/email.password/}</label>
			<span class="input"><input name="SmtpPassword" value="<?=$config_row['SmtpPassword'];?>" type="password" class="form_input" size="30" maxlength="100" /></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="do_action" value="email.config" />
	</form>
</div>