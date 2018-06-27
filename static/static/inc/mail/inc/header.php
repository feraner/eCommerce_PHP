<?php !isset($c) && exit();?>
<?php
if($c['config']['global']['LogoPath']){
	$LogoPath=$c['config']['global']['LogoPath'];
}else{
	$LogoPath=db::get_value('config', "GroupId='global' and Variable='LogoPath'", 'Value');
}
?>
<table width="700" border="0" cellspacing="0" cellpadding="0" style="border-bottom:2px solid #999;">
	<tr>
		<td width="350" style="padding-bottom:8px;"><a href="<?=ly200::get_domain();?>" target="_blank"><img src="<?=ly200::get_domain().$LogoPath;?>" style="max-width:350px;" border="0" /></a></td>
		<td width="350" align="right" valign="bottom" style="padding-bottom:8px;">
			<div style="text-align:right; font-size:10px; font-family:Arial; color:#333; height:25px; width:100%;"><?=date('m/d/Y H:i:s', $c['time']);?></div>
			<a href="<?=ly200::get_domain();?>" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['home'];?></a>
			<a href="<?=ly200::get_domain().'/account/';?>" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['my_account'];?></a>
			<a href="<?=$c['config']['global']['ContactUrl']?$c['config']['global']['ContactUrl']:'javascript:;';?>" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['contactUs'];?></a>
		</td>
	</tr>
</table>