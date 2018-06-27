<?php !isset($c) && exit();?>
<?php
$sign_row=str::str_code(db::get_all('sign_in', 'IsUsed=1'));
$sign_ary=array();
foreach((array)$sign_row as $v){
	$sign_ary[$v['Title']]=$v;
}

$email=$_GET['email'];
$expiry=$_GET['expiry'];
?>
<script type="text/javascript">$(function(){user_obj.forgot_init()});</script>
<div id="user">
	<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a>');?>
	<form class="form_forgot">
		<h3 class="title"><?=$c['lang_pack']['user']['resetPWD'];?></h3>
		<div class="clear"></div>
		<div id="error_register_box" class="error_note_box"></div>
		<div class="clear"></div>
		<?php if($_GET['forgot_success']==1){?>
			<dl class="intro">
				<dd><?=$c['lang_pack']['user']['sentEmail'];?></dd>
			</dl>
			<dl class="intro">
				<dt><?=$c['lang_pack']['user']['receivedEmail'];?></dt>
				<dd><?=$c['lang_pack']['user']['checkEmail'];?></dd>
			</dl>
			<div class="row user_login_btn"><a href="/" class="btn_global btn BuyNowBgColor"><?=$c['lang_pack']['user']['continueShopping'];?></a></div>
		<?php }elseif($_GET['reset_success']==1){?>
			<div class="forgot_tips">
				<dl class="intro">
					<dd><?=$c['lang_pack']['user']['successfully'];?></dd>
				</dl>
				<div class="row user_login_btn"><a href="/account/" class="btn_global btn SignInButton BuyNowBgColor"><?=$c['lang_pack']['user']['signIndex'];?></a></div>
			</div>
		<?php }elseif($email=='' || $expiry==''){?>
			<div class="row">
				<label for="Email"><?=$c['lang_pack']['user']['enterEmail'];?></label>
				<input name="Email" id="Email" class="box_input" type="text" autocomplete="off" size="40" maxlength="100" format="Email" notnull />
				<p class="on_error"><?=$c['lang_pack']['user']['enteredEmail'];?></p>
			</div>
			
			<dl class="intro">
				<dd><?=$c['lang_pack']['user']['sentReset'];?></dd>
				<dd><?=$c['lang_pack']['user']['contactServices'];?></dd>
			</dl>
			<div class="row user_login_btn"><button class="btn_global btn btn_fotgot BuyNowBgColor" type="button"><?=$c['lang_pack']['user']['sendEmail'];?></button></div>
			<input type="hidden" name="do_action" value="user.forgot" />
		<?php
		}else{
			//!db::get_row_count('user_forgot', "EmailEncode='$email' and Expiry='$expiry' and IsReset=0") && ly200::js_location('/account/forgot.html');
		?>
			<div class="row">
				<label for="Password"><?=$c['lang_pack']['user']['newPWD'];?></label>
				<input name="Password" id="Password" class="box_input" autocomplete="off" type="password" size="40" notnull />
				<p class="on_error"><?=$c['lang_pack']['mobile']['enter_psw'];?></p>
			</div>
			<div class="row">
				<label for="Password2"><?=$c['lang_pack']['user']['ConfirmPWD'];?></label>
				<input name="Password2" id="Password2" class="box_input" autocomplete="off" type="password" size="40" notnull />
				<p class="on_error"><?=$c['lang_pack']['user']['matchPWD'];?></p>
			</div>
			<dl class="intro">
				<dd><?=$c['lang_pack']['user']['enterPWD'];?></dd>
			</dl>
			<div class="row user_login_btn"><button class="btn_global btn btn_reset BuyNowBgColor" type="button"><?=$c['lang_pack']['user']['submit'];?></button></div>
			<input type="hidden" name="email" value="<?=htmlspecialchars($email);?>" />
			<input type="hidden" name="expiry" value="<?=htmlspecialchars($expiry);?>" />
			<input type="hidden" name="do_action" value="user.reset_password" />
		<?php }?>
	</form>
	<?php include('include/rightside.php');?>
	<div class="blank20"></div>
</div>