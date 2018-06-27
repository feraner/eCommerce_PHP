<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$userType=(int)$_GET['userType'];
if($userType){
	$UserId=(int)$_GET['UserId'];
	$user_row=str::str_code(db::get_one('user', "UserId='{$UserId}'"));
	$email_ary=user::check_email($user_row['Email']);
	
	if($userType==2 && $user_row && !(int)$user_row['Status']){//邮件验证成功
		$data=array();
		$_SESSION['User']=$user_row;
		$_SESSION['User']['UserId']=$UserId;
		
		$user_row['Status']=$data['Status']=1;
		cart::login_update_cart();
		//更新会员等级
		$LId=(int)db::get_value('user_level', 'IsUsed=1 and FullPrice<=0', 'LId');
		if($LId){
			$data['Level']=$LId;
			$_SESSION['User']['Level']=$LId;
		}
		db::update('user', "UserId='$UserId'", $data);
		user::operation_log($UserId, '会员注册');
		include($c['static_path'].'/inc/mail/create_account.php');
		ly200::sendmail($user_row['Email'], 'Welcome to '.ly200::get_domain(0), $mail_contents);
	}
}else{
	$jumpUrl=$_POST['jumpUrl']?$_POST['jumpUrl']:$_GET['jumpUrl'];
	!$jumpUrl && $jumpUrl=$_SERVER['HTTP_REFERER'];	//进入登录页面之前的页面
	$jumpUrl=str_replace('%ap;', '&', $jumpUrl);
	(int)$_GET['AddToCart'] && $jumpUrl.='?AddToCart=1';
	if($_GET['Attr']){
		$jumpUrl.='&Attr='.htmlspecialchars(str_replace('\\', '', $_GET['Attr']));
	}
	if($jumpUrl){
		$_SESSION['Ueeshop']['LoginReturnUrl']=stripslashes($jumpUrl);
	}else{
		unset($_SESSION['Ueeshop']['LoginReturnUrl']);
	}
	$reg_ary=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
	
	$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<script type="text/javascript">
$(document).ready(function(){
	user_obj.sign_in_init();
	user_obj.sign_up_init();
});
<?php
if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'] && $userType==2 && $user_row && !(int)$user_row['Status']){//邮件验证流程结束，会员帐号可以正常使用
	//When a registration form is completed, such as signup for a service.
?>
	<!-- Facebook Pixel Code -->
	fbq('track', 'CompleteRegistration',{
		value:'0.00',
		currency:'<?=$_SESSION['Currency']['Currency'];?>'
	});
	<!-- End Facebook Pixel Code -->
<?php }?>
</script>
</head>

<body>
<div id="customer">
	<?php include('include/header.php');?>
    <div id="signup">
		<?php
		if($userType){
		?>
			<div class="verification_box">
				<?php
				if((int)$user_row['Status']){//已经通过审核
				?>
					<p class="verification_title"><i></i><?=str_replace('%name%', (($user_row['FirstName'] || $user_row['LastName'])?$user_row['FirstName'].' '.$user_row['LastName']:$email_ary['account']), $c['lang_pack']['user']['varComTitle']);?></p>
					<dl class="verification_info">
						<dt><?=$c['lang_pack']['user']['varComInfo'];?>?</dt>
						<dd><a class="guide_btn FontColor" href="/"><?=$c['lang_pack']['user']['homePage'];?></a>|<a class="guide_btn FontColor" href="/account/"><?=$c['lang_pack']['my_account'];?></a></dd>
					</dl>
				<?php
				}else{//尚未通过审核
				?>
					<p class="verification_title"><i></i><?=str_replace('%domain%', ly200::get_domain(0), $c['lang_pack']['user']['verTitle']);?></p>
					<dl class="verification_info">
						<dt><?=str_replace('%name%', (($user_row['FirstName'] || $user_row['LastName'])?$user_row['FirstName'].' '.$user_row['LastName']:$email_ary['account']), $c['lang_pack']['user']['verInfo']);?></dt>
						<dd>
							<p><?=$c['lang_pack']['user']['varInfo_0'];?>:<strong><?=$user_row['Email'];?></strong>.</p>
							<p><?=$c['lang_pack']['user']['varInfo_1'];?></p>
							<p class="btn_list">
								<a class="verify_now_btn FontBgColor" href="<?=$email_ary['url'];?>" target="_blank"><?=$c['lang_pack']['user']['verifyNow'];?></a>
								<a id="send_email_btn" class="FontColor" href="javascript:;" email="<?=$user_row['Email'];?>" uid="<?=$UserId;?>"><?=$c['lang_pack']['user']['resendEmail'];?></a>
							</p>
						</dd>
						<dt><?=$c['lang_pack']['user']['varInfo_2'];?>:</dt>
						<dd><a class="guide_btn FontColor" href="/help/"><?=$c['lang_pack']['user']['newBuyerGuide'];?></a>|<a class="guide_btn FontColor" href="/help/"><?=$c['lang_pack']['user']['purchaseFlow'];?></a></dd>
						<dt><?=$c['lang_pack']['user']['varInfo_3'];?>:</dt>
						<dd><a class="guide_btn FontColor" href="/"><?=$c['lang_pack']['user']['returnPage'];?></a>|<a class="guide_btn FontColor" href="/account/"><?=$c['lang_pack']['my_account'];?></a></dd>
					</dl>
				<?php }?>
			</div>
		<?php
		}else{
			echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min_en.js', '/static/js/plugin/daterangepicker/daterangepicker_en.js');
		?>
			<form class="register fl">
				<h3 class="title"><?=$c['lang_pack']['user']['register'];?></h3>
				<div class="clear"></div>
				<div id="error_register_box" class="error_note_box"></div>
				<div class="clear"></div>
				<?php
				if($reg_ary['Name'][0]){
				?>
					<div class="row fl">
						<label for="FirstName"><?=$c['lang_pack']['user']['firstname'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
						<input name="FirstName" id="FirstName" class="lib_txt" type="text" size="30" maxlength="20"<?=$reg_ary['Name'][1]?' notnull':'';?> />
					</div>
					<div class="row fl">
						<label for="LastName"><?=$c['lang_pack']['user']['lastname'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
						<input name="LastName" id="LastName" class="lib_txt" type="text" size="30" maxlength="20"<?=$reg_ary['Name'][1]?' notnull':'';?> />
					</div>
					<div class="clear"></div>
				<?php }?>
				<div class="row">
					<label for="Email"><?=$c['lang_pack']['user']['email'];?> <span class="fc_red">*</span></label>
					<input name="Email" id="Email" class="lib_txt lib_input" type="text" maxlength="100" format="Email" notnull />
					<p class="on_error"><?=$c['lang_pack']['user']['incorrect'];?></p>
				</div>
				<div class="row">
					<label for="Password"><?=$c['lang_pack']['user']['createPWD'];?> <span class="fc_red">*</span></label>
					<input name="Password" id="Password" class="lib_txt lib_input" type="password" notnull />
				</div>
				<div class="row">
					<label for="Password2"><?=$c['lang_pack']['user']['ConfirmPWD'];?> <span class="fc_red">*</span></label>
					<input name="Password2" id="Password2" class="lib_txt lib_input" type="password" notnull />
					<p class="on_error"><?=$c['lang_pack']['user']['tryAgain'];?></p>
				</div>
                <?php if($reg_ary['Country'][0]){//国家?>
				<div class="row">
					<label for="Country"><?=$c['lang_pack']['user']['country'];?> <?=$reg_ary['Country'][1]?'<span class="fc_red">*</span>':'';?></label>
					<select name="country_id" <?=$reg_ary['Country'][1]?'notnull':'';?>>
                    	<option value="">--<?=$c['lang_pack']['plesaeSelect'];?>--</option>
						<?php
						$country_row=str::str_code(db::get_all('country', 'IsUsed=1', 'CId, Country, CountryData, IsDefault', 'Country asc'));
						foreach($country_row as $v){
							$name=$v['Country'];
							if($c['lang']!='_en'){
								$country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode');
								$name=$country_data[substr($c['lang'], 1)];
							}
						?>
						<option value="<?=$v['CId'];?>"<?=$v['IsDefault']?' selected="selected"':'';?>><?=$name;?></option>
						<?php }?>
					</select>
				</div>
                <div class="clear"></div>
                <?php }?>
				<?php
				$value_ary=array();
				foreach((array)$reg_ary as $k=>$v){
					if($k=='Name' || $k=='Email' || $k=='Code' || $k=='Country' || !$v[0]) continue;
					$k=='Birthday' && $value_ary['Birthday']=date('m/d/Y', $c['time']);
				?>
					<div class="row">
						<label for="<?=$k?>"><?=$c['lang_pack']['user'][$k].($v[1]?' <span class="fc_red">*</span>':'')?></label>
						<?=user::user_reg_edit($k, $v[1], 'lib_txt lib_input', $value_ary);?>
					</div>
				<?php
				}
				foreach((array)$set_row as $k=>$v){
				?>
					<div class="row">
						<label for="<?=$v['Name'.$c['lang']];?>"><?=$v['Name'.$c['lang']];?></label>
						<?php
						if($v['TypeId']){
							echo ly200::form_select(explode("\r\n", $v['Option'.$c['lang']]), "Other[{$v['SetId']}]", '', '', '', 'Please select...');
						}else{
							echo user::form_edit('', 'text', "Other[{$v['SetId']}]", 30, 50, 'class="lib_txt lib_input"');
						}
						?>
					</div>
				<?php }?>
				<?php if($reg_ary['Code'][0]){?>
					<div class="row">
						<label for="<?=$c['lang_pack']['user']['SecurityCode'];?>"><?=$c['lang_pack']['user']['SecurityCode'];?> <span class="fc_red">*</span></label>
						<input name="Code" id="Code" class="lib_txt fl" type="text" size="10" maxlength="4" notnull />&nbsp;&nbsp;&nbsp;<?=v_code::create('register');?>
					</div>
				<?php }?>
				<dl class="intro">
					<dt><?=$c['lang_pack']['user']['createInfo0'];?></dt>
					<dd><?=$c['lang_pack']['user']['createInfo1'];?></dd>
					<dd><?=str_replace('%SiteName%', $c['config']['global']['SiteName'], $c['lang_pack']['user']['createInfo2']);?></dd>
					<dd><?=str_replace('%SiteName%', $c['config']['global']['SiteName'], $c['lang_pack']['user']['createInfo3']);?></dd>
				</dl>
				<div class="row"><button class="signbtn signup form_button_bg" type="submit"><?=$c['lang_pack']['user']['createAccount'];?></button></div>
				<input type="hidden" name="jumpUrl" value="<?=$jumpUrl;?>" />
				<input type="hidden" name="do_action" value="user.register" />
				<?php if((int)$_GET['AddToCart']){?><div id="addtocart_button"></div><?php }?>
			</form>
			<script>
				(function(){
					$('form.register input[name=Birthday]').daterangepicker({
						showDropdowns:true,
						singleDatePicker:true,
						timePicker:false,
						format:'MM/DD/YYYY'
					});
				})();
			</script>
			<?php include('include/rightside.php');?>
		<?php }?>
        <div class="blank20"></div>
    </div>
	<?php include('include/footer.php');?>
	<?php /*if($jumpUrl){ //自动弹出登录框?>
		<script>
			(function(){
				user_obj.set_form_sign_in();
			})();
		</script>
	<?php }*/?>
</div>
</body>
</html>
