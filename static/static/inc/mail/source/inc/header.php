<?php !isset($c) && exit();?>
<table width="700" border="0" cellspacing="0" cellpadding="0" style="border-bottom:2px solid #999;">
	<tr>
		<td width="350" style="padding-bottom:8px;"><a href="{FullDomain}" target="_blank">{Logo}</a></td>
		<td width="350" align="right" valign="bottom" style="padding-bottom:8px;">
			<div style="text-align:right; font-size:10px; font-family:Arial; color:#333; height:25px; width:100%;">{Time}</div>
			<a href="{FullDomain}" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['home'];?></a>
			<a href="{FullDomain}/account/" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['my_account'];?></a>
			<a href="<?=$c['config']['global']['ContactUrl']?$c['config']['global']['ContactUrl']:'javascript:;';?>" target="_blank" style="font-size:12px; margin-left:12px; text-decoration:underline; color:#1E5494; font-family:Verdana;"><?=$c['lang_pack_email']['contactUs'];?></a>
		</td>
	</tr>
</table>