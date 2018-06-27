<?php !isset($c) && exit();?>
<?php
ob_start();
$c['lang_pack_email']=include($c['root_path'].'/static/static/inc/mail/lang/'.$tpl_lang.'.php');//加载语言包
?>
<div style="width:700px; margin:10px auto;">
	<?php include('inc/header.php');?>
	<div style="font-family:Arial; padding:15px 0; line-height:150%; min-height:100px; _height:100px; color:#333; font-size:12px;">
		<?=str_replace('%name%', '<strong>{UserName}</strong>', $c['lang_pack_email']['dear']);?>:<br /><br />

        <?=str_replace('%domain%', '<a href="{FullDomain}" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">{Domain}</a>', $c['lang_pack_email']['not_reply']);?><br /><br />
        <?=str_replace('%domain%', '{Domain}', $c['lang_pack_email']['thanks']);?><br /><br />
        
        <?=$c['lang_pack_email']['createTitle'];?>:<br />
        -------------------------------------------------------------------------------------------<br />
        <div style="height:24px; line-height:24px; clear:both;">
            <div style="float:left; width:92px;"><?=$c['lang_pack_email']['yUsername'];?></div>
            <div style="float:left; width:400px;">: {UserName}</div>
        </div>
        <div style="height:24px; line-height:24px; clear:both;">
            <div style="float:left; width:92px;"><?=$c['lang_pack_email']['yEmail'];?></div>
            <div style="float:left; width:400px;">: {Email}</div>
        </div>
        <div style="height:24px; line-height:24px; clear:both;">
            <div style="float:left; width:92px;"><?=$c['lang_pack_email']['yPassword'];?></div>
            <div style="float:left; width:400px;">: {Password}</div>
        </div><br /><br />
        
        <?=$c['lang_pack_email']['copy_paste'];?>:<br />
        <a href="{FullDomain}" target="_blank" style="font-family:Arial; color:#1E5494; text-decoration:underline; font-size:12px;"><strong>{FullDomain}</strong></a><br /><br />
        
        <?=$c['lang_pack_email']['sincerely'];?>,<br /><br />
        
        <?=str_replace('%domain%', '{Domain}', $c['lang_pack_email']['customer']);?>
	</div>
	<?php include('inc/footer.php');?>
</div>
<?php
$mail_contents=ob_get_contents();
ob_end_clean();
?>