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
		
		<?=str_replace(array('%oid%', '%domain%'), array('<a href="{OrderUrl}" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">{OrderNum}</a>', '{Domain}'), $c['lang_pack_email']['createInfo']);?><br /><br />
		
		<strong><?=$c['lang_pack_email']['pleaseNote'];?>:</strong><br />
		<?=str_replace('%status%', '{OrderStatus}', $c['lang_pack_email']['createInfo_1']);?><br /><br />
		
		<?=str_replace(array('%total_price%', '%domain%', '%PaymentMethod%'), array('{OrderPrice}', '<a href="{OrderPaymentUrl}" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">our website</a>', '{OrderPaymentName}'), $c['lang_pack_email']['createInfo_2']);
		?>
		<br /><br />
		
		<a href="{OrderPaymentUrl}" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;"><?=$c['lang_pack_email']['createInfo_3'];?></a><br /><br />
		
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