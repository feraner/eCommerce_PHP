<?php !isset($c) && exit();?>
<?php
$sign_count=0;
foreach($c['config']['Platform'] as $v){
	$v['SignIn']['IsUsed']==1 && $sign_count+=1;
}
?>
<div class="info fr">
	<div class="box"><a href="/" class="home"><?=$c['lang_pack']['user']['returnHome'];?></a></div>
    <div class="box member">
        <p><?=$c['lang_pack']['user']['already'];?></p>
        <div class="sign_btn"><a href="javascript:;" class="SignInButton signinbtn"><?=$c['lang_pack']['user']['signInNow'];?></a></div>
        <p class="forgot"><a href="/account/forgot.html" class="FontColor"><?=$c['lang_pack']['user']['forgotPWD'];?></a></p>
        <?php
		if($c['FunVersion']>=1 && $sign_count>0){
			if((int)$c['config']['Platform']['Facebook']['SignIn']['IsUsed']){
				echo ly200::load_static('/static/js/oauth/facebook.js');
				$data=$c['config']['Platform']['Facebook']['SignIn']['Data'];
        ?>
            <div id="fb_button" scope="public_profile, email" onclick="checkLoginState();" class="fb_button" appid="<?=$data['appId'];?>">
                <i></i><span>Log In with Facebook</span><em></em>
            </div>
        <?php
			}
			if((int)$c['config']['Platform']['Twitter']['SignIn']['IsUsed']){
				$data=$c['config']['Platform']['Twitter']['SignIn']['Data'];
				echo ly200::load_static('/static/js/oauth/twitter.js');
			?>
				<span class="twitter_button" id="twitter_btn" key="<?=base64_encode(~$data['CONSUMER_KEY']);?>" secret="<?=base64_encode(~$data['CONSUMER_SECRET']);?>" callback="<?=urlencode(ly200::get_domain().$c['config']['Platform']['Twitter']['ReturnUrl']);?>">
					<span class="icon"></span>
					<span class="text">Login In with Twitter</span>
				</span>
			<?php
			}
			if((int)$c['config']['Platform']['Google']['SignIn']['IsUsed']){
				echo ly200::load_static('/static/js/oauth/google.js');
				$data=$c['config']['Platform']['Google']['SignIn']['Data'];
        ?>
            <div id="google_btn" class="google_button" clientid="<?=$data['clientid'];?>"><span class="icon"></span><span class="button_text">Log In with Google +</span></div>
        <?php
			}
			if((int)$c['config']['Platform']['Paypal']['SignIn']['IsUsed']){
				echo ly200::load_static('/static/js/oauth/paypal/api.js');
				$data=$c['config']['Platform']['Paypal']['SignIn']['Data'];
				$_domain=!$data['domain']?ly200::get_domain():$data['domain'];
        ?>
			<div id="paypalLogin" appid="<?=$data['client_id'];?>" u="<?=htmlspecialchars_decode(trim($_domain,'/').$c['config']['Platform']['Paypal']['ReturnUrl']);?>"></div>
        <?php
			}
			if((int)$c['config']['Platform']['VK']['SignIn']['IsUsed']){
				echo ly200::load_static('/static/js/oauth/vk.js');
				$data=$c['config']['Platform']['VK']['SignIn']['Data'];
        ?>
            <div id="vk_button" class="vk_button" apiid="<?=$data['apiId']?>">
                <span class="icon"></span>
                <span class="button_text">Login with VK</span>
            </div>
        <?php
			}
		}?>
    </div>
    <?php
	$help_row=str::str_code(db::get_limit('article', 'CateId=99', "AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'AId desc', 0, 5));
	if(count($help_row)){
	?>
    <div class="box">
        <h3><?=str::str_code(db::get_value('article_category', 'CateId=99', "Category{$c['lang']}"));?></h3>
        <ul>
			<?php
			foreach((array)$help_row as $v){
			?>
            <li><a href="<?=ly200::get_url($v, 'article');?>" title="<?=$v['Title'.$c['lang']];?>" target="_blank"><?=$v['Title'.$c['lang']];?></a></li>
			<?php }?>
        </ul>
    </div>
	<?php }?>
</div>