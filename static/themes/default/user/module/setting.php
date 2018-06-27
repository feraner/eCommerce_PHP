<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$reg_ary=str::json_data(htmlspecialchars_decode(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value')), 'decode');
$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
$other_ary=@str::json_data(htmlspecialchars_decode($user_row['Other']), 'decode');

echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min_en.js', '/static/js/plugin/daterangepicker/daterangepicker_en.js');
?>
<div id="user_heading" class="user_heading_setting">
	<h2>
		<?=$c['lang_pack']['user']['settingTitle'];?>
	</h2>
</div>
<div id="user_setting_container">
	<div class="setting_box clearfix">
		<div class="setting_title"><?=$c['lang_pack']['user']['changeProfile'];?></div>
		<form action="/" method="post" class="setting_form" id="frm_profile">
			<div class="rows">
				<div class="form_box clean">
					<div class="box">
						<label class="input_box<?=$user_row['FirstName']!=''?' filled':'';?>">
							<span class="input_box_label"><?=$c['lang_pack']['user']['firstname'];?></span>
							<input type="text" class="input_box_txt" name="FirstName" value="<?=$user_row['FirstName'];?>" placeholder="<?=$c['lang_pack']['user']['firstname'];?>" size="40" maxlength="40" notnull />
						</label>
						<p class="error"></p>
					</div>
					<div class="box">
						<label class="input_box<?=$user_row['LastName']!=''?' filled':'';?>">
							<span class="input_box_label"><?=$c['lang_pack']['user']['lastname'];?></span>
							<input type="text" class="input_box_txt" name="LastName" value="<?=$user_row['LastName'];?>" placeholder="<?=$c['lang_pack']['user']['lastname'];?>" size="40" maxlength="40" notnull />
						</label>
						<p class="error"></p>
					</div>
				</div>
			</div>
			<?php
			foreach((array)$reg_ary as $k=>$v){
				if($k=='Name'||$k=='Email'||$k=='Gender'||$k=='Phone'||$k=='Shipping'||$k=='Address'||$k=='Code'||$k=='Country'||!$v[0]) continue;
				$k=='Birthday' && !$user_row['Birthday'] && $user_row['Birthday']=date('m/d/Y', $c['time']);
			?>
				<div class="rows">
					<div class="input clean">
						<?=user::user_new_reg_edit($k, $c['lang_pack']['user'][$k], $v[1], 'form_input', $user_row);?>
						<p class="error"></p>
					</div>
				</div>
			<?php }?>
			<?php
			foreach((array)$set_row as $k=>$v){
			?>
				<div class="rows">
					<div class="input clean">
						<?php
						if($v['TypeId']){
							echo '<div class="box_select">'.ly200::form_select(explode("\r\n", $v['Option'.$c['lang']]), "Other[{$v['SetId']}]", $other_ary[$v['SetId']], '', '', 'Please select...').'</div>';
						}else{
							$row['Other['.$v['SetId'].']']=$other_ary[$v['SetId']];
							echo user::new_form_edit($row, 'text', "Other[{$v['SetId']}]", $v['Name'.$c['lang']], 30, 50, 'class="form_input"');
						}
						?>
						<p class="error"></p>
					</div>
				</div>
			<?php }?>
			<div class="setting_button"><button type="submit" class="btn_submit"><?=$c['lang_pack']['user']['save'];?></button></div>
			<input type="hidden" name="do_action" value="user.mod_profile" />
		</form>
	</div>
    <div class="setting_box clearfix">
		<div class="setting_title"><?=$c['lang_pack']['user']['changeEmail'];?></div>
		<form action="/" method="post" class="setting_form" id="frm_email">
			<div class="rows">
				<div class="input clean">
					<label class="input_box">
						<span class="input_box_label"><?=$c['lang_pack']['user']['existingPWD'];?></span>
						<input type="password" class="input_box_txt" name="ExtPassword" size="40" placeholder="<?=$c['lang_pack']['user']['existingPWD'];?>" notnull />
					</label>
					<p class="error"></p>
				</div>
			</div>
			<div class="rows">
				<div class="input clean">
					<label class="input_box<?=$user_row['Email']!=''?' filled':'';?>">
						<span class="input_box_label"><?=$c['lang_pack']['user']['newAddress'];?></span>
						<input type="text" class="input_box_txt" name="NewEmail" value="<?=$user_row['Email'];?>" size="40" maxlength="100" format="Email" placeholder="<?=$c['lang_pack']['user']['newAddress'];?>" notnull />
					</label>
					<p class="error"></p>
				</div>
			</div>
			<div class="setting_button"><button type="submit" class="btn_submit"><?=$c['lang_pack']['user']['save'];?></button></div>
			<input type="hidden" name="do_action" value="user.mod_email" />
		</form>
	</div>
    <div class="setting_box clearfix">
		<div class="setting_title"><?=$c['lang_pack']['user']['changePWD'];?></div>
		<form action="/" method="post" class="setting_form" id="frm_password">
			<div class="rows">
				<div class="input clean">
					<label class="input_box">
						<span class="input_box_label"><?=$c['lang_pack']['user']['existingPWD'];?></span>
						<input type="password" class="input_box_txt" name="ExtPassword" size="40" placeholder="<?=$c['lang_pack']['user']['existingPWD'];?>" notnull />
					</label>
					<p class="error"></p>
				</div>
			</div>
			<div class="rows">
				<div class="input clean">
					<label class="input_box">
						<span class="input_box_label"><?=$c['lang_pack']['user']['newPWD'];?></span>
						<input type="password" class="input_box_txt" name="NewPassword" size="40" placeholder="<?=$c['lang_pack']['user']['newPWD'];?>" notnull />
					</label>
					<p class="error"></p>
				</div>
			</div>
			<div class="rows">
				<div class="input clean">
					<label class="input_box">
						<span class="input_box_label"><?=$c['lang_pack']['user']['rePWD'];?></span>
						<input type="password" class="input_box_txt" name="NewPassword2" size="40" placeholder="<?=$c['lang_pack']['user']['rePWD'];?>" notnull />
					</label>
					<p class="error"></p>
				</div>
			</div>
			<div class="setting_button"><button type="submit" class="btn_submit"><?=$c['lang_pack']['user']['save'];?></button></div>
			<input type="hidden" name="do_action" value="user.mod_password" />
		</form>
	</div>
</div>
<script type="text/javascript">
	var frm_profile = $('#frm_profile');
	frm_profile.find('button:submit').click(function(){
		if(global_obj.check_form(frm_profile.find('*[notnull]'))){return false;};
	});
	
	var frm_email = $('#frm_email');
	frm_email.find('button:submit').click(function(){
		if(global_obj.check_form(frm_email.find('*[notnull]'), frm_email.find('*[format]'), 0, 1)){return false;};
	});
	
	var frm_password = $('#frm_password');
	frm_password.find('button:submit').click(function(){
		if(global_obj.check_form(frm_password.find('*[notnull]'))){return false;};
	});
	
	(function(){
		$('form#frm_profile input[name=Birthday]').daterangepicker({
			showDropdowns:true,
			singleDatePicker:true,
			timePicker:false,
			format:'MM/DD/YYYY'
		});
	})();
</script>