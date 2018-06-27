<?php !isset($c) && exit();?>
<?php
$set_ary=array();
$set_row=db::get_all('config', "GroupId='mobile'");
foreach($set_row as $v){
	$set_ary[$v['Variable']]=$v['Value'];
}
?>
<?=ly200::load_static('/static/js/plugin/jscolor/jscolor.js');?>
<script language="javascript">$(document).ready(function(){mobile_obj.header_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.mobile.header/}</h1>
</div>
<div id="mobile_header" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="rows">
			<label>{/mobile.icon/}</label>
			<span class="input clean">
				<div class="fl headicon">
					<div class="img <?=!$set_ary['HeadIcon']?'on':'';?>" data-icon="0"><img src="/static/manage/images/mobile/white_icon.png" /></div>
					{/mobile.color.0/}
				</div>
				<div class="fl headicon">
					<div class="img <?=$set_ary['HeadIcon']?'on':'';?>" data-icon="1"><img src="/static/manage/images/mobile/gray_icon.png" /></div>
					{/mobile.color.1/}
				</div>
				<input type="hidden" name="icon" value="<?=$set_ary['HeadIcon']?>" />
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.bg_color/}</label>
			<span class="input"><input type="text" id="bg_color" class="form_input color" name="bg_color" size="6" value="<?=$set_ary['HeadBg']?$set_ary['HeadBg']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.fixed/}</label>
			<span class="input">
				<div class="switchery<?=$set_ary['HeadFixed']?' checked':'';?>">
					<input type="checkbox" name="fixed" value="1"<?=$set_ary['HeadFixed']?' checked':'';?>>
					<div class="switchery_toggler"></div>
					<div class="switchery_inner">
						<div class="switchery_state_on"></div>
						<div class="switchery_state_off"></div>
					</div>
				</div>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="LogoPath" value="<?=$set_ary['LogoPath'];?>" />
		<input type="hidden" name="do_action" value="mobile.header_edit_init">
	</form>
</div>