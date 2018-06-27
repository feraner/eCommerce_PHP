<?php !isset($c) && exit();?>
<?php
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
	$reg_ary=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
	$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
	$jumpUrl=$_POST['jumpUrl']?$_POST['jumpUrl']:$_GET['jumpUrl'];
	$jumpUrl=='' && $jumpUrl=$_SERVER['HTTP_REFERER'];	//进入登录页面之前的页面
	if($jumpUrl){
		$_SESSION['Ueeshop']['LoginReturnUrl']=$jumpUrl;
	}else{
		$jumpUrl = '/';
		unset($_SESSION['Ueeshop']['LoginReturnUrl']);
	}
}
?>
<script type="text/javascript">$(function(){user_obj.user_login();});</script>
<div class="wrapper">
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
	<?php }else{ ?>
		<div class="user_login_tab clean">
	    	<div class="on fl"><?=$c['lang_pack']['sign_in'];?></div>
	    	<div class="fl"><?=$c['lang_pack']['join_free'];?></div>
	    </div>
	    <div class="user_login">
	        <form method="post" class="user_login_form" id="login_form">
				<div class="user_login_box">
					<div class="user_input user_email">
						<div class="ui_border_b"><input type="email" name="Email" placeholder="<?=$c['lang_pack']['mobile']['enter_email'];?>" notnull /></div>
					</div>
					<div class="user_input user_password">
						<div class="ui_border_b"><input type="password" name="Password" autocomplete="off" placeholder="<?=$c['lang_pack']['mobile']['enter_psw'];?>" notnull /></div>
					</div>
					<div class="user_login_btn">
						<div class="btn_global btn_submit BuyNowBgColor"><?=$c['lang_pack']['mobile']['sign_in'];?></div>
					</div>
					<div class="user_forgot">
						<a href="/account/forgot.html" rel="nofollow" class="forget_btn"><?=$c['lang_pack']['user']['forgotPWD'];?></a>
					</div>
					<?php 
					if($c['FunVersion']>=1){
						$sign_count=0;
						foreach($c['config']['Platform'] as $v){
							$v['SignIn']['IsUsed']==1 && $sign_count+=1;
						}
						if($sign_count>0){
							echo '<div class="oauth_title ui_border_b"><div class="float"><strong>'.$c['lang_pack']['mobile']['or_join_with'].'</strong></div></div>';
							echo '<div class="oauth_body">';
						}
						if((int)$c['config']['Platform']['Facebook']['SignIn']['IsUsed']){
							echo ly200::load_static('/static/js/oauth/facebook.js');
							$data=$c['config']['Platform']['Facebook']['SignIn']['Data'];
					?>
						<div id="fb_button" scope="public_profile, email" onclick="checkLoginState();" appid="<?=$data['appId'];?>" class="login_ex clean"><a href="javascript:;"><?=$c['lang_pack']['mobile']['sign_up_with'];?> Facebook</a></div>
						<?php 
						}
						if((int)$c['config']['Platform']['Google']['SignIn']['IsUsed']){
							echo ly200::load_static('/static/js/oauth/google.js');
							$data=$c['config']['Platform']['Google']['SignIn']['Data'];
						?>
						<div id="google_btn" clientid="<?=$data['clientid'];?>" class="login_ex clean"><a href="javascript:;"><?=$c['lang_pack']['mobile']['sign_up_with'];?> Google+</a></div>
						<?php
						}
						if((int)$c['config']['Platform']['Paypal']['SignIn']['IsUsed']){
	                        echo ly200::load_static('/static/js/oauth/paypal/api.js');
	                        $data=$c['config']['Platform']['Paypal']['SignIn']['Data'];
	                        $_domain=!$data['domain']?ly200::get_domain():$data['domain'];
						?>
	                	<div id="paypalLogin" appid="<?=$data['client_id']?>" class="login_ex clean" u="<?=htmlspecialchars_decode(rtrim($_domain, '/').$c['config']['Platform']['Paypal']['ReturnUrl']);?>"></div>
						<?php
						}
						if((int)$c['config']['Platform']['VK']['SignIn']['IsUsed']){
							echo ly200::load_static('/static/js/oauth/vk.js');
							$data=$c['config']['Platform']['VK']['SignIn']['Data'];
						?>
	                	<div id="vk_button" apiid="<?=$data['apiId']?>" class="login_ex clean"><a href="javascript:;"><?=$c['lang_pack']['mobile']['sign_up_with'];?> VK</a></div>
	                <?php 
						}
						if($sign_count>0) echo '</div>';
					}
					?>
					<div class="blank15"></div>
				</div>
				<input type="hidden" name="jumpUrl" value="<?=$jumpUrl;?>" />
	            <input type="hidden" name="do_action" value="user.login" />
	        </form>
	    </div>
	    <div class="user_login" style="display:none;">
	        <form action="?" method="post" class="user_login_form" id="reg_form">
				<?php
				if($reg_ary['Name'][0]){
				?>
	            <div class="rows">
					<div class="form_name clean">
						<div class="box">
							<label class="field"><?=$c['lang_pack']['mobile']['first_name'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
	                		<input type="text" class="box_input" name="FirstName" placeholder="<?=$c['lang_pack']['mobile']['your_fir_name'];?>" data-field="<?=$c['lang_pack']['mobile']['first_name'];?>"<?=$reg_ary['Name'][1]?' notnull':'';?> /><p class="error"></p>
						</div>
						<div class="box">
							<label class="field"><?=$c['lang_pack']['mobile']['last_name'];?><?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
	                		<input type="text" class="box_input" name="LastName" placeholder="<?=$c['lang_pack']['mobile']['your_last_name'];?>" data-field="<?=$c['lang_pack']['mobile']['last_name'];?>"<?=$reg_ary['Name'][1]?' notnull':'';?> /><p class="error"></p>
						</div>
					</div>
	            </div>
				<?php }?>
	            <div class="rows">
	                <label class="field"><?=$c['lang_pack']['mobile']['email'];?> <span class="fc_red">*</span></label>
	                <div class="input clean"><input type="email" class="box_input" name="Email" autocomplete="off" placeholder="you@domain.com" data-field="<?=$c['lang_pack']['mobile']['email'];?>" notnull /><p class="error"></p></div>
	            </div>
	            <div class="rows">
	                <label class="field"><?=$c['lang_pack']['mobile']['password'];?> <span class="fc_red">*</span></label>
	                <div class="input clean"><input type="password" class="box_input" name="Password" autocomplete="off" placeholder="<?=$c['lang_pack']['mobile']['at_6_char'];?>" data-field="<?=$c['lang_pack']['mobile']['password'];?>" notnull /><p class="error"></p></div>
	            </div>
	            <div class="rows">
	                <label class="field"><?=$c['lang_pack']['mobile']['confirm'];?> <span class="fc_red">*</span></label>
	                <div class="input clean"><input type="password" class="box_input" name="Password2" autocomplete="off" placeholder="<?=$c['lang_pack']['mobile']['confirm_pwd'];?>" data-field="<?=$c['lang_pack']['mobile']['confirm_pwd'];?>" notnull /><p class="error"></p></div>
	            </div>
	            <?php if($reg_ary['Country'][0]){//国家?>
				<div class="rows">
	                <label class="field"><?=$c['lang_pack']['user']['country'];?> <?=$reg_ary['Country'][1]?'<span class="fc_red">*</span>':'';?></label>
	                <div class="input clean">
						<div class="box_select">
							<select name="country_id" <?=$reg_ary['Country'][1]?'notnull':'';?>>
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
	                </div>
	            </div>
	            <?php }?>
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
									<?php foreach($c['gender'] as $k2=>$v2){?>
										<option value="<?=$k2;?>"><?=$v2;?></option>
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
	                    <div class="input clean"><input type="<?=$k=='Birthday'?'date':'text';?>" class="box_input" name="<?=$k;?>" placeholder="<?=$c['lang_pack']['mobile']['your'];?> <?=strtolower($k)?>" data-field="<?=$k;?>"<?=$v[1]?' notnull':'';?> /><p class="error"></p></div>
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
										<option value="<?=$k;?>"><?=$v?></option>
									<?php }?>
								</select>
							</div>
	                    </div>
	                </div>
	            <?php
					}else{?>
	                <div class="rows">
	                    <label class="field"><?=$v['Name'.$c['lang']];?></label>
	                    <div class="input clean"><input type="text" class="box_input" name="Other[<?=$v['SetId'];?>]" placeholder="<?=$v['Name'.$c['lang']];?>" /></div>
	                </div>
	            <?php
					}
				}?>
				<?php if($reg_ary['Code'][0]){?>
					<div class="rows">
						<div class="form_name form_code clean">
							<div id="demo_default" class="demos box">
								<label class="field"><?=$c['lang_pack']['user']['SecurityCode'];?> <span class="fc_red">*</span></label>
								<input  name="Code" class="box_input" type="text" size="10" maxlength="4" data-field="<?=$c['lang_pack']['user']['SecurityCode'];?>" notnull /><p class="error"></p>
							</div>
							<div class="box">
								<label class="field">&nbsp;</label>
								<?=v_code::create('register');?>
							</div>
						</div>
					</div>
				<?php }?>
	            <div class="user_login_btn">
	            	<input type="hidden" name="jumpUrl" value="<?=$jumpUrl;?>" />
	            	<input type="hidden" name="do_action" value="user.register" />
	                <div class="btn_global btn_sign_up btn_submit BuyNowBgColor"><?=$c['lang_pack']['mobile']['sign_up'];?></div>
	            </div>
				<div class="blank25"></div>
	        </form>
	    </div>
    <?php } ?>
</div><!-- end of .wrapper -->