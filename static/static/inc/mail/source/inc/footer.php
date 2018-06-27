<?php !isset($c) && exit();?>
<div style="padding:20px 0; line-height:180%; font-family:Arial; font-size:12px; color:#000; border-top:1px solid #ccc; border-bottom:1px solid #ccc;">
	<?=str_replace('%domain%', '{Domain}', $c['lang_pack_email']['footer_0']);?><br />
	<?=str_replace('%domain%', '<a href="{FullDomain}" target="_blank" style="font-family:Arial; font-size:12px; color:#1E5494; text-decoration:underline;">{FullDomain}</a>', $c['lang_pack_email']['footer_1']);?>
    <?php
	$domain='{FullDomain}';//网站域名
	$BottomContent=str::json_data(db::get_value('config', 'GroupId="email" and Variable="bottom"', 'Value'), 'decode');
	if($BottomContent['BottomContent_'.$c['manage']['config']['LanguageDefault']]){
		echo '<br /><br />'.preg_replace('/\/u_file\//i', $domain.'/u_file/', $BottomContent['BottomContent_'.$c['manage']['config']['LanguageDefault']]);
	}
	?>
</div>