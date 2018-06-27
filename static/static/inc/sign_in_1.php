<?php !isset($c) && exit();?>
<?php
if((int)$_SESSION['User']['UserId']){
	//已登录
	if($c['FunVersion']>=1){
		//会员站内信未读数量统计
		$user_msg_row=str::str_code(db::get_all('user_message', "(UserId like '%|{$_SESSION['User']['UserId']}|%' or UserId='-1') and  Type=1", '*', 'MId desc'));
		$user_msg_len=0;
		foreach((array)$user_msg_row as $k=>$v){
			$is_read=0;
			if($v['IsRead']){
				$userid_ary=@array_flip(explode('|', $v['UserId']));
				$isread_ary=@explode('|', $v['IsRead']);
				$is_read=$isread_ary[$userid_ary[$_SESSION['User']['UserId']]];
				!$is_read && $user_msg_len+=1;
			}
		}
		$user_prod_msg=db::get_row_count('user_message',"UserId='{$_SESSION['User']['UserId']}' and Module='products' and IsReply=1");
		$user_order_msg=db::get_row_count('user_message',"UserId='{$_SESSION['User']['UserId']}' and Module='orders' and IsReply=1");
	}
	$_UserName=substr($c['lang'], 1)=='jp'?$_SESSION['User']['LastName'].' '.$_SESSION['User']['FirstName']:$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName'];
?>
<div class="global_account_sec">
	<div class="AccountButton_sec"><?=($_SESSION['User']['FirstName'] || $_SESSION['User']['LastName'])?$_UserName:$_SESSION['User']['Email'];?></div>
    <div class="account_container_sec">
    	<div class="account_box_sec">
            <div class="rows"><a rel="nofollow" href="/account/orders/"><?=$c['lang_pack']['my_orders'];?><?php if($user_order_msg){?><b class="FontBgColor"><?=$user_order_msg?></b><?php }?></a></div>
            <div class="rows"><a rel="nofollow" href="/account/favorite/"><?=$c['lang_pack']['my_fav'];?></a></div>
            <div class="rows"><a rel="nofollow" href="/account/coupon/"><?=$c['lang_pack']['my_coupon'];?></a></div>
            <?php if($c['FunVersion']>=1){?>
            <div class="rows"><a rel="nofollow" href="/account/inbox/"><?=$c['lang_pack']['my_inbox'];?><?php if($user_msg_len){?><b class="FontBgColor"><?=$user_msg_len?></b><?php }?></a></div>
			<div class="rows"><a rel="nofollow" href="/account/products/"><?=$c['lang_pack']['my_qa'];?><?php if($user_prod_msg){?><b class="FontBgColor"><?=$user_prod_msg?></b><?php }?></a></div>
            <?php }?>
            <div class="btn"><a class="FontBorderColor FontColor" rel="nofollow" href="/account/logout.html"><?=$c['lang_pack']['sign_out'];?></a></div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	var timer;
	$('.global_account_sec').hover(
		function(){
			clearTimeout(timer);
			$(this).find('.AccountButton_sec').addClass('cur');
			$(this).find('.account_container_sec').fadeIn();
		},
		function(){
			var _this=$(this);
			timer=setTimeout(function(){
				_this.find('.AccountButton_sec').removeClass('cur');
				_this.find('.account_container_sec').fadeOut();
			},500);
		}
	);
});
</script>
<?php
}else{
	//未登录
	$sign_count=0;
	foreach($c['config']['Platform'] as $v){
		$v['SignIn']['IsUsed']==1 && $sign_count+=1;
	}
?>
<div class="global_login_sec">
	<div class="SignInButton_sec"></div>
    <div class="signin_box_sec global_signin_module">
    	<div class="signin_container">
            <form class="signin_form" name="signin_form" method="post">
                <input name="Email" class="g_s_txt" type="text" maxlength="100" placeholder="Address" format="Email" notnull />
                <div class="blank20"></div>
                <input name="Password" class="g_s_txt" type="password" placeholder="Password" notnull />
                <button class="signin FontBgColor" type="submit">SIGN IN</button>
                <input type="hidden" name="do_action" value="user.login">
            </form>
            <div id="error_login_box" class="error_login_box"></div>
            <?php if($c['FunVersion']>=1 && $sign_count>0){?>
                <h4>Sign in with:</h4>
                <ul>
                    <?php
					/*
					if((int)$c['config']['Platform']['Facebook']['SignIn']['IsUsed']){
						$data=$c['config']['Platform']['Facebook']['SignIn']['Data'];
					*/
					$facebook_data=$c['config']['Platform']['Facebook']['SignIn'];
					if((int)$facebook_data['IsUsed'] && $facebook_data['Data']['appId']){
                        echo ly200::load_static('/static/js/oauth/facebook.js');
                    ?>
                        <li id="fb_button" scope="public_profile, email" onclick="checkLoginState();" appid="<?=$facebook_data['Data']['appId'];?>"></li>
                    <?php
                    }
					/*
					if((int)$c['config']['Platform']['Twitter']['SignIn']['IsUsed']){
						$data=$c['config']['Platform']['Twitter']['SignIn']['Data'];
					*/
					$twitter_data=$c['config']['Platform']['Twitter']['SignIn'];
					if((int)$twitter_data['IsUsed'] && $twitter_data['Data']['CONSUMER_KEY'] && $twitter_data['Data']['CONSUMER_SECRET']){
                        echo ly200::load_static('/static/js/oauth/twitter.js');
                    ?>
                        <li id="twitter_btn" key="<?=base64_encode(~$twitter_data['Data']['CONSUMER_KEY']);?>" secret="<?=base64_encode(~$twitter_data['Data']['CONSUMER_SECRET']);?>" callback="<?=urlencode(ly200::get_domain().$c['config']['Platform']['Twitter']['ReturnUrl']);?>"></li>
                    <?php
                    }
					/*
					if((int)$c['config']['Platform']['Google']['SignIn']['IsUsed']){
						$data=$c['config']['Platform']['Google']['SignIn']['Data'];
					*/
					$google_data=$c['config']['Platform']['Google']['SignIn'];
					if((int)$google_data['IsUsed'] && $google_data['Data']['clientid']){
                        echo ly200::load_static('/static/js/oauth/google.js');
                    ?>
                        <li id="google_login"><div id="google_btn" clientid="<?=$google_data['Data']['clientid'];?>">&nbsp;</div></li>
                    <?php
                    }
					/*
					if((int)$c['config']['Platform']['Paypal']['SignIn']['IsUsed']){
						$data=$c['config']['Platform']['Paypal']['SignIn']['Data'];
					*/
					$paypal_data=$c['config']['Platform']['Paypal']['SignIn'];
					if((int)$paypal_data['IsUsed'] && $paypal_data['Data']['client_id']){
                        echo ly200::load_static('/static/js/oauth/paypal/api.js');
                        $_domain=!$paypal_data['Data']['domain']?ly200::get_domain():$paypal_data['Data']['domain'];
                    ?>
                        <li id="paypalLogin" appid="<?=$paypal_data['Data']['client_id'];?>" u="<?=htmlspecialchars_decode(rtrim($_domain, '/').$c['config']['Platform']['Paypal']['ReturnUrl']);?>"></li>
                    <?php
					}
					/*
					if((int)$c['config']['Platform']['VK']['SignIn']['IsUsed']){
						echo ly200::load_static('/static/js/oauth/vk.js');
						$data=$c['config']['Platform']['VK']['SignIn']['Data'];
					*/
					$vk_data=$c['config']['Platform']['VK']['SignIn'];
					if((int)$vk_data['IsUsed'] && $vk_data['Data']['apiId']){
						echo ly200::load_static('/static/js/oauth/vk.js');
					?>    
                    	<li id="vk_button" apiid="<?=$vk_data['Data']['apiId']?>"></li>
                    <?php }?>
                </ul>
            <?php }?>
            <div class="blank20"></div>
            <a href="/account/sign-up.html" class="signup FontBorderColor FontColor">JOIN FREE</a>
            <a href="/account/forgot.html" class="forgot">Forgot your password?</a>
            <div class="clear"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	user_obj.sign_in_init();
	<?php
	//默认打开登录框
	if((int)$c['config']['global']['UserLogin'] && !$_SESSION['User']['UserLoginTime']){
		echo "user_obj.set_form_sign_in('', '', 1);";
		$_SESSION['User']['UserLoginTime']=$c['time'];
	}
	?>
	var timer;
	$('.global_login_sec').hover(
		function(){
			clearTimeout(timer);
			$(this).find('.SignInButton_sec').addClass('cur');
			$(this).find('.signin_box_sec').fadeIn();
		},
		function(){
			var _this=$(this);
			timer=setTimeout(function(){
				_this.find('.SignInButton_sec').removeClass('cur');
				_this.find('.signin_box_sec').fadeOut();
			},500);
		}
	);
});
</script>
<?php }?>