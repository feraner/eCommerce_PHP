<?php !isset($c) && exit();?>
<?php
$set_ary=array();
$set_row=db::get_all('config', "GroupId='mobile'");
foreach($set_row as $v){
	$set_ary[$v['Variable']]=$v['Value'];
}
?>
<?=ly200::load_static('/static/js/plugin/jscolor/jscolor.js');?>
<script language="javascript">$(document).ready(function(){mobile_obj.footer_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.mobile.footer/}</h1>
</div>
<div id="mobile_footer" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="rows">
			<label>{/global.preview/}</label>
			<span class="input clean"><div class="fl preview" id="foot_preview">{/mobile.font_model/}</div></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.font_color/}</label>
			<span class="input">
				<input type="text" id="font_color" class="form_input color" name="font_color" size="6" value="<?=$set_ary['FootFont']?$set_ary['FootFont']:'#000'?>" autocomplete="off"/>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.bg_color/}</label>
			<span class="input"><input type="text" id="bg_color" class="form_input color" name="bg_color" size="6" value="<?=$set_ary['FootBg']?$set_ary['FootBg']:'#000'?>" autocomplete="off"/></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/mobile.nav/}</label>
			<span class="input"><input type="button" value="{/global.add/}{/mobile.link/}" id="addLink" class="btn_ok" data-name="{/global.name/}" data-link="{/mobile.link/}" /></span>
			<div class="clear"></div>
		</div>
		<div id="Linkrow">
			<?php
			$FootNav=str::json_data(htmlspecialchars_decode($set_ary['FootNav']), 'decode');
			foreach((array)$FootNav as $key=>$value){
			?>
				<div class="rows">
					<label>{/global.name/}</label>
					<span class="input">
						<?php
						foreach($c['manage']['config']['Language'] as $k=>$v){
						?>
							<span class='price_input'><b>{/language.<?=$v;?>/}<div class='arrow'><em></em><i></i></div></b><input type='text' name='Name_<?=$v;?>[]' value='<?=$value['Name_'.$v];?>' notnull="" class='form_input' size='41' maxlength='20'></span>
							<div class="blank6"></div>
						<?php }?>
						{/mobile.link/}:<input type="text" notnull="" size="40" maxlength="150" class="form_input" value="<?=$value['Url'];?>" name="Url[]"> <a class="del" href="javascript:void(0);"><img src="/static/ico/del.png" /></a>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="do_action" value="mobile.footer_edit_init">
	</form>
	<div id="cus_html">
		<div class="rows">
			<label>{/global.name/}</label>
			<span class="input">
				<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
					<span class='price_input'><b>{/language.<?=$v;?>/}<div class='arrow'><em></em><i></i></div></b><input type='text' name='Name_<?=$v;?>[]' value='' notnull="" class='form_input' size='41' maxlength='20'></span>
					<div class="blank6"></div>
				<?php }?>
				{/mobile.link/}:<input type="text" notnull="" size="40" maxlength="150" class="form_input" value="" name="Url[]"> <a class="del" href="javascript:void(0);"><img src="/static/ico/del.png" /></a>
			</span>
			<div class="clear"></div>
		</div>
	</div>
</div>