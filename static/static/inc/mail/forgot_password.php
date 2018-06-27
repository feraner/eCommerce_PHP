<?php !isset($c) && exit();?>
<?php
$ForgotUrl=ly200::get_domain().'/account/forgot.html?&email='.urlencode($EmailEncode).'&expiry='.urlencode($Expiry);

$Template='forgot_password';//事件名称
$mail_data = array('ForgotUrl'=>$ForgotUrl);//传入模板的数据
include('inc/static.php');

if($mail_contents==''){//默认模板
	ob_start();
	$c['lang_pack_email']=include('lang/'.$mail_lang.'.php');//加载语言包
	$mail_title=ly200::system_email_tpl($c['lang_pack_email'][$Template], $mail_data);
?>
    <div style="width:700px; margin:10px auto;">
        <?php include('inc/header.php');?>
        <div style="font-family:Arial; padding:15px 0; line-height:150%; min-height:100px; _height:100px; color:#333; font-size:12px;">
            <strong><?=$c['lang_pack_email']['dearTo'];?></strong>:<br /><br />
    
            <?=str_replace('%domain%', '<a href="'.ly200::get_domain().'" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">'.ly200::get_domain(0).'</a>', $c['lang_pack_email']['not_reply_pwd']);?><br /><br />
            
            <strong><?=str_replace('%domain%', '<a href="'.ly200::get_domain().'" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">'.ly200::get_domain(0).'</a>', $c['lang_pack_email']['steps']);?>:</strong><br /><br />
            
            <div style="font-family:Arial; line-height:180%; padding-left:20px;">1)&nbsp;&nbsp;<?=$c['lang_pack_email']['pwdInfo_0'];?><br /><a href="<?=$ForgotUrl;?>" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;"><?=$ForgotUrl;?></a></div><br />
            <div style="font-family:Arial; line-height:180%; padding-left:20px;">2)&nbsp;&nbsp;<?=$c['lang_pack_email']['pwdInfo_1'];?></div><br />
            
            <?=$c['lang_pack_email']['queries'];?><br /><br />
            
            <?=$c['lang_pack_email']['sincerely'];?>,<br /><br />
            
            <?=str_replace('%domain%', ly200::get_domain(0), $c['lang_pack_email']['customer']);?>
        </div>
        <?php include('inc/footer.php');?>
    </div>
<?php
	$mail_contents=ob_get_contents();
	ob_end_clean();
}
?>