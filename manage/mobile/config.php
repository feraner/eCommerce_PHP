<?php !isset($c) && exit();?>
<?php
$set_ary=array();
$set_row=db::get_all('config', "GroupId='mobile'");
foreach($set_row as $v){
	$set_ary[$v['Variable']]=$v['Value'];
}
?>
<?=ly200::load_static('/static/js/plugin/jscolor/jscolor.js');?>
<script language="javascript">$(document).ready(function(){mobile_obj.config_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.mobile.config/}</h1>
</div>
<div id="mobile_config" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="rows">
			<label>{/set.config.logo/}</label>
			<span class="input upload_file">
				<div>
					<input type="button" id="LogoUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '154*39');?>" />
					{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '154*39');?>
				</div>
				<div class="img" id="LogoDetail"></div>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.btn/}{/global.preview/}</label>
			<span class="input clean"><div class="fl preview" id="btn_preview">{/mobile.font_model/}</div></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.btn/}{/mobile.font_color/}</label>
			<span class="input"><input type="text" id="btn_color" class="form_input color" name="btn_color" size="6" value="<?=$set_ary['BtnColor']?$set_ary['BtnColor']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.btn/}{/mobile.bg_color/}</label>
			<span class="input"><input type="text" id="btn_bg" class="form_input color" name="btn_bg" size="6" value="<?=$set_ary['BtnBg']?$set_ary['BtnBg']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.cbtn/}{/global.preview/}</label>
			<span class="input clean"><div class="fl preview" id="cart_btn_preview">{/mobile.font_model/}</div></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.cbtn/}{/mobile.font_color/}</label>
			<span class="input"><input type="text" id="cart_btn_color" name="cart_btn_color" class="form_input color" size="6" value="<?=$set_ary['CBtnColor']?$set_ary['CBtnColor']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.cbtn/}{/mobile.bg_color/}</label>
			<span class="input"><input type="text" id="cart_btn_bg" name="cart_btn_bg" class="form_input color" size="6" value="<?=$set_ary['CBtnBg']?$set_ary['CBtnBg']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="LogoPath" value="<?=$set_ary['LogoPath'];?>" />
		<input type="hidden" name="do_action" value="mobile.config_edit">
	</form>
</div>