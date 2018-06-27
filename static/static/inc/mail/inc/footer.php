<?php !isset($c) && exit();?>
<div style="padding:20px 0; line-height:180%; font-family:Arial; font-size:12px; color:#000; border-top:1px solid #ccc; border-bottom:1px solid #ccc;">
	<?=str_replace('%domain%', ly200::get_domain(0), $c['lang_pack_email']['footer_0']);?><br />
	<?=str_replace('%domain%', '<a href="'.ly200::get_domain().'" target="_blank" style="font-family:Arial; font-size:12px; color:#1E5494; text-decoration:underline;">'.ly200::get_domain().'</a>', $c['lang_pack_email']['footer_1']);?>
	<?php
	$domain=ly200::get_domain();//网站域名
	$BottomContent=str::json_data(db::get_value('config', 'GroupId="email" and Variable="bottom"', 'Value'), 'decode');
	if($BottomContent['BottomContent_'.substr($c['lang'], 1)]){
		echo '<br /><br />'.preg_replace('/\/u_file\//i', $domain.'/u_file/', $BottomContent['BottomContent_'.substr($c['lang'], 1)]);
	}
	?>
</div>