<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$reg_ary=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
$other_ary=@str::json_data(htmlspecialchars_decode($user_row['Other']), 'decode');

echo ly200::load_static("{$c['mobile']['tpl_dir']}js/user.js", '/static/js/plugin/mobile_time/mobile_time.min.css', "/static/js/plugin/mobile_time/mobile_time.min.js");
?>
<script type="text/javascript">
$(function(){
	user_obj.user_setting()
	
	var curr=new Date().getFullYear(), opt={};
	opt.date		= {preset:'date'};
	opt.datetime	= {preset:'datetime', minDate:new Date(2012,3,10,9,22), maxDate:new Date(2014,7,30,15,44), stepMinute:5};
	opt.time		= {preset:'time'};
	opt.tree_list	= {preset:'list', labels:['Region', 'Country', 'City']};
	opt.image_text	= {preset:'list', labels:['Cars']};
	opt.select		= {preset:'select'};
	$('input[name=Birthday]').scroller('destroy').scroller($.extend(opt['date'], {theme:'sense-ui', mode:'scroller', display:'modal', lang:''}));
});
</script>
<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a><em><i></i></em><a href="javascript:;">'.$c['lang_pack']['user']['settingTitle'].'</a>');?>
<div id="user" class="user_login">
	<div class="blank10"></div>
	<form action="/" method="post" class="user_login_form" id="reg_form">
		<div class="rows">
			<div class="form_name clean">
				<div class="box">
					<label class="field"><?=$c['lang_pack']['mobile']['first_name'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
					<input type="text" class="box_input" name="FirstName" value="<?=$user_row['FirstName'];?>" placeholder="<?=$c['lang_pack']['mobile']['your_fir_name'];?>" data-field="<?=$c['lang_pack']['mobile']['first_name'];?>" notnull /><p class="error"></p>
				</div>
				<div class="box">
					<label class="field"><?=$c['lang_pack']['mobile']['last_name'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
					<input type="text" class="box_input" name="LastName" value="<?=$user_row['LastName'];?>" placeholder="<?=$c['lang_pack']['mobile']['your_last_name'];?>" data-field="<?=$c['lang_pack']['mobile']['last_name'];?>" notnull /><p class="error"></p>
				</div>
			</div>
		</div>
		<?php
		foreach((array)$reg_ary as $k=>$v){
			if($k=='Name' || $k=='Email' || $k=='Age' || $k=='Code' || $k=='Country' || !$v[0]) continue;
			if($k=='Gender'){
		?>
			<div class="rows">
				<label class="field"><?=$k;?></label>
				<div class="input clean">
					<div class="box_select">
						<select name="<?=$k;?>">
							<?php foreach($c['gender'] as $k=>$v){?>
								<option value="<?=$k;?>"<?=$user_row['Gender']==$k?' selected':'';?>><?=$v;?></option>
							<?php }?>
						</select>
					</div>
				</div>
			</div>
		<?php
			}else{
		?>
			<div class="rows">
				<label class="field"><?=$k.($v[1]?' <span class="fc_red">*</span>':'');?></label>
				<div class="input clean"><input type="text" class="box_input" name="<?=$k;?>" value="<?=$user_row[$k];?>" placeholder="<?=$c['lang_pack']['mobile']['your'];?> <?=strtolower($k)?>" data-field="<?=$k;?>"<?=$v[1]?' notnull':'';?> /><p class="error"></p></div>
			</div>
		<?php
			}
		}
		foreach((array)$set_row as $k=>$v){
			if($v['TypeId']){
		?>
			<div class="rows">
				<label class="field"><?=$v['Name'.$c['lang']]?></label>
				<div class="input clean">
					<div class="box_select">
						<select name="Other[<?=$v['SetId'];?>]">
							<?php foreach((array)explode("\r\n", $v['Option'.$c['lang']]) as $k=>$v){?>
								<option value="<?=$k;?>"<?=$k==$other_ary[$v['SetId']]?' selected':'';?>><?=$v?></option>
							<?php }?>
						</select>
					</div>
				</div>
			</div>
		<?php
			}else{
		?>
			<div class="rows">
				<label class="field"><?=$v['Name'.$c['lang']];?></label>
				<div class="input clean"><input type="text" class="box_input" name="Other[<?=$v['SetId'];?>]" value="<?=$other_ary[$v['SetId']];?>" placeholder="<?=$v['Name'.$c['lang']];?>" /></div>
			</div>
		<?php
			}
		}?>
		<div class="user_login_btn">
			<div class="btn_global btn_sign_up btn_submit BuyNowBgColor"><?=$c['lang_pack']['mobile']['update_profile'];?></div>
			<a href="javascript:history.go(-1);" class="btn_global btn btn_back" id="btn_back"><?=$c['lang_pack']['mobile']['back'];?></a>
		</div>
		<div class="blank25"></div>
		<input type="hidden" name="ajax_submit" value="1" />
		<input type="hidden" name="do_action" value="user.mod_profile" />
	</form>
</div>