<?php !isset($c) && exit();?>
<?php
if((int)$_SESSION['User']['UserId']){
	//已登录
	if($c['FunVersion']>=1){
		//会员站内信未读数量统计
		$user_msg_row=str::str_code(db::get_all('user_message', "(UserId like '%|{$_SESSION['User']['UserId']}|%' or UserId='-1') and Type=1", '*', 'MId desc'));
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
	<div class="FontColor fl"><?=$c['lang_pack']['welcome'];?>&nbsp;</div>
	<dl class="fl">
		<dt><a rel="nofollow" href="/account/" class="FontColor"><span><?=($_SESSION['User']['FirstName'] || $_SESSION['User']['LastName'])?$_UserName:$_SESSION['User']['Email'];?></span><?=($user_msg_len || $user_prod_msg || $user_order_msg)?'<b></b>':'';?></a></dt>
		<dd class="user">
			<a rel="nofollow" href="/account/orders/"><?=$c['lang_pack']['my_orders'];?><?=$user_order_msg?'<b class="inbox_tips">'.$user_order_msg.'</b>':'';?></a>
			<a rel="nofollow" href="/account/favorite/"><?=$c['lang_pack']['my_fav'];?></a>
			<a rel="nofollow" href="/account/coupon/"><?=$c['lang_pack']['my_coupon'];?></a>
			<?php if($c['FunVersion']>=1){?>
            <a rel="nofollow" href="/account/inbox/"><?=$c['lang_pack']['my_inbox'];?><?=$user_msg_len?'<b class="inbox_tips">'.$user_msg_len.'</b>':'';?></a>
            <a rel="nofollow" href="/account/products/"><?=$c['lang_pack']['my_qa'];?><?=$user_prod_msg?'<b class="inbox_tips">'.$user_prod_msg.'</b>':'';?></a>
			<?php }?>
			<a rel="nofollow" href="/account/logout.html"><?=$c['lang_pack']['sign_out'];?></a>
		</dd>
	</dl>
<?php
}else{
	//未登录
	$sign_count=0;
	foreach($c['config']['Platform'] as $v){
		$v['SignIn']['IsUsed']==1 && $sign_count+=1;
	}
	?>
		<dl>
			<dt<?=($c['FunVersion']>=1 && $sign_count>0)?'':' class="not_dd"';?>><a rel="nofollow" href="javascript:;" class="SignInButton FontColor"><?=$c['lang_pack']['sign_in'];?></a> <?=$c['lang_pack']['or'];?> <a rel="nofollow" href="/account/sign-up.html" class="FontColor"><?=$c['lang_pack']['join_free'];?></a></dt>
			<?php if($c['FunVersion']>=1 && $sign_count>0){?>
			<dd class="login">
				<?php
				/*
				if((int)$c['config']['Platform']['Facebook']['SignIn']['IsUsed']){
					$data=$c['config']['Platform']['Facebook']['SignIn']['Data'];
				*/
				$facebook_data=$c['config']['Platform']['Facebook']['SignIn'];
				if((int)$facebook_data['IsUsed'] && $facebook_data['Data']['appId']){
					echo ly200::load_static('/static/js/oauth/facebook.js');
				?>
					<div id="fb_button" scope="public_profile, email" onclick="checkLoginState();" class="fb_button" appid="<?=$facebook_data['Data']['appId'];?>">
						<i></i>
						<span>Log In with Facebook</span>
						<em></em>
					</div>
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
					<span class="twitter_button" id="twitter_btn" key="<?=base64_encode(~$twitter_data['Data']['CONSUMER_KEY']);?>" secret="<?=base64_encode(~$twitter_data['Data']['CONSUMER_SECRET']);?>" callback="<?=urlencode(ly200::get_domain().$c['config']['Platform']['Twitter']['ReturnUrl']);?>">
                    	<span class="icon"></span>
                    	<span class="text">Login In with Twitter</span>
                    </span>
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
					<div id="google_btn" class="google_button" clientid="<?=$google_data['Data']['clientid'];?>">
						<span class="icon"></span>
						<span class="button_text">Login with Google +</span>
					</div>
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
					<div id="paypalLogin" appid="<?=$paypal_data['Data']['client_id'];?>" u="<?=htmlspecialchars_decode(rtrim($_domain, '/').$c['config']['Platform']['Paypal']['ReturnUrl']);?>"></div>
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
                <div id="vk_button" class="vk_button" apiid="<?=$vk_data['Data']['apiId']?>">
                    <span class="icon"></span>
                    <span class="button_text">Login with VK</span>
                </div>
                <?php }?>
			</dd>
		<?php }?>
	</dl>
	<script type="text/javascript">
	$(document).ready(function(){
		user_obj.sign_in_init();
		<?php
		//默认打开登录框
		if((int)$c['config']['global']['UserLogin'] && !$_SESSION['User']['UserLoginTime']){
			echo "$('.SignInButton').click();";
			$_SESSION['User']['UserLoginTime']=$c['time'];
		}
		?>
	});
	</script>
<?php }?>