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
        
        <?=str_replace('%oid%', '<a href="{OrderUrl}" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">{OrderNum}</a>', $c['lang_pack_email']['shippedInfo']);?><br /><br />
		
		<?=$c['lang_pack_email']['shippingMethod'];?>: {ShippingName} ({ShippingBrief})<br />
		<?=$c['lang_pack_email']['trackingNumber'];?>: {TrackingNumber} ({ShippingTime})
		
		<br /><a class="query" href="{QueryUrl}" target="_blank"><?=$c['lang_pack_email']['query'];?></a>
		
		<br /><br />
        
        {OrderDetail}
        <br />
        <?=$c['lang_pack_email']['sincerely'];?>,<br /><br />
        
        <?=str_replace('%domain%', '{Domain}', $c['lang_pack_email']['customer']);?>
	</div>
	<?php include('inc/footer.php');?>
</div>
<?php
$mail_contents=ob_get_contents();
ob_end_clean();
?>