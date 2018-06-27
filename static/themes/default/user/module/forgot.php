<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$sign_row=str::str_code(db::get_all('sign_in', 'IsUsed=1'));
$sign_ary=array();
foreach((array)$sign_row as $v){
	$sign_ary[$v['Title']]=$v;
}

$email=$_GET['email'];
$expiry=$_GET['expiry'];
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
	user_obj.forgot_init();
});
</script>
</head>

<body>
<div id="customer">
	<?php include('include/header.php');?>
    <div id="signup">
        <form class="register fl">
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
                <a href="/" class="signbtn signup NavBgColor"><?=$c['lang_pack']['user']['continueShopping'];?></a>
            <?php }elseif($_GET['reset_success']==1){?>
            	<div class="forgot_tips">
                	<dl class="intro">
                        <dd><?=$c['lang_pack']['user']['successfully'];?></dd>
                    </dl>
                    <input type="button" value="<?=$c['lang_pack']['user']['signIndex'];?>" class="signbtn signup NavBgColor SignInButton" />
                </div>
            <?php }elseif($email=='' || $expiry==''){?>
                <div class="row">
                    <label for="Email"><?=$c['lang_pack']['user']['enterEmail'];?></label>
                    <input name="Email" id="Email" class="lib_txt" type="text" autocomplete="off" size="40" maxlength="100" format="Email" notnull />
                    <p class="on_error"><?=$c['lang_pack']['user']['enteredEmail'];?></p>
                </div>
                
                <dl class="intro">
                    <dd><?=$c['lang_pack']['user']['sentReset'];?></dd>
                    <dd><?=$c['lang_pack']['user']['contactServices'];?></dd>
                </dl>
                <div class="row"><button class="fotgotbtn signbtn signup form_button_bg FontBgColor FontBorderColor" type="button"><?=$c['lang_pack']['user']['sendEmail'];?></button></div>
                <input type="hidden" name="do_action" value="user.forgot" />
            <?php
            }else{
				!db::get_row_count('user_forgot', "EmailEncode='$email' and Expiry='$expiry' and IsReset=0") && ly200::js_location('/account/forgot.html');
			?>
                <div class="row">
                    <label for="Password"><?=$c['lang_pack']['user']['newPWD'];?></label>
                    <input name="Password" id="Password" class="lib_txt" autocomplete="off" type="password" size="40" notnull />
                </div>
                <div class="row">
                    <label for="Password2"><?=$c['lang_pack']['user']['ConfirmPWD'];?></label>
                    <input name="Password2" id="Password2" class="lib_txt" autocomplete="off" type="password" size="40" notnull />
                    <p class="on_error"><?=$c['lang_pack']['user']['matchPWD'];?></p>
                </div>
                <dl class="intro">
                    <dd><?=$c['lang_pack']['user']['enterPWD'];?></dd>
                </dl>
                <div class="row"><button class="signbtn signup form_button_bg NavBgColor resetbtn" type="button"><?=$c['lang_pack']['user']['submit'];?></button></div>
                <input type="hidden" name="email" value="<?=htmlspecialchars($email);?>" />
                <input type="hidden" name="expiry" value="<?=htmlspecialchars($expiry);?>" />
                <input type="hidden" name="do_action" value="user.reset_password" />
            <?php }?>
        </form>
        <?php include('include/rightside.php');?>
        <div class="blank20"></div>
    </div>
	<?php include('include/footer.php');?>
</div>
</body>
</html>
