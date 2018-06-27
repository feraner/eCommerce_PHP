<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'orders_print'));//检查权限

$config_row=str::str_code(db::get_all('config', "GroupId='print'"));
foreach($config_row as $v){
	$c['manage']['config'][$v['Variable']]=$v['Value'];
}
?>
<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/jquery-ui/jquery-ui.min.css', '/static/js/plugin/jquery-ui/jquery-ui.min.js');?>
<script language="javascript">$(document).ready(function(){set_obj.orders_print_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.set.orders_print/}</h1>
</div>
<div id="print" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="rows">
			<label>{/set.config.logo/}</label>
			<span class="input upload_file upload_logo">
				<div class="img">
					<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '210*75');?>" /></div>
					{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '210*75');?>
				</div>
				<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
				<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/user.reg_set.Company/}</label>
			<span class="input"><input name="Compeny" value="<?=$c['manage']['config']['Compeny'];?>" type="text" class="form_input" maxlength="100" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/user.reg_set.Address/}</label>
			<span class="input"><input name="Address" value="<?=$c['manage']['config']['Address'];?>" type="text" class="form_input" maxlength="100" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/user.reg_set.Email/}</label>
			<span class="input"><input name="Email" value="<?=$c['manage']['config']['Email'];?>" type="text" class="form_input" maxlength="150" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/user.reg_set.Telephone/}</label>
			<span class="input"><input name="Telephone" value="<?=$c['manage']['config']['Telephone'];?>" type="text" class="form_input" maxlength="20" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/user.reg_set.Fax/}</label>
			<span class="input"><input name="Fax" value="<?=$c['manage']['config']['Fax'];?>" type="text" class="form_input" maxlength="20" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="LogoPath" value="<?=$c['manage']['config']['LogoPath'];?>" save="<?=is_file($c['root_path'].$c['manage']['config']['LogoPath'])?1:0;?>" />
		<input type="hidden" name="do_action" value="set.orders_print_edit">
	</form>
</div>