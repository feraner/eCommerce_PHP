<?php !isset($c) && exit();?>
<script type="text/javascript">$(function (){user_obj.user_binding();});</script>
<div id="user">
	<div class="user_binding clean"><?=$c['lang_pack']['user']['bindingTitle'];?></div>
    <div class="user_login">
        <form name="" method="post" class="user_binding_form" id="binding_form">
            <div class="user_login_t"><?=$c['lang_pack']['mobile']['email'];?></div>
            <div class="user_input"><input type="email" name="Email" placeholder="mail@example.com" class="box_input" notnull /></div>
            <div class="user_login_t"><?=$c['lang_pack']['mobile']['password'];?></div>
            <div class="user_input"><input type="password" name="Password" autocomplete="off" placeholder="<?=$c['lang_pack']['mobile']['enter_psw'];?>" class="box_input" notnull /></div>
            
            <div class="user_login_btn">
            	<input type="hidden" name="jumpUrl" value="<?=$jumpUrl;?>" />
            	<input type="hidden" name="Type" value="<?=$_SESSION['Oauth']['User']['Type'];?>" />
            	<input type="hidden" name="do_action" value="user.user_oauth_binding" />
                <div class="btn_global btn_submit BuyNowBgColor"><?=$c['lang_pack']['mobile']['sign_in'];?></div>
            </div>
        </form>
    </div>
</div><!-- end of .wrapper -->