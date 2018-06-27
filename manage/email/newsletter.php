<?php !isset($c) && exit();?>
<script language="javascript">$(document).ready(function(){email_obj.newsletter_init()});</script>
<div class="r_nav">
	<h1>{/module.email.newsletter/}</h1>
	<div class="turn_page"></div>
</div>
<div id="newsletter" class="r_con_wrap">
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="10%" nowrap="nowrap">{/global.serial/}</td>
				<td width="50%" nowrap="nowrap">{/global.email/}</td>
				<td width="20%" nowrap="nowrap">{/global.time/}</td>
				<td width="10%" nowrap="nowrap">{/email.newsletter.status/}</td>
				<td width="10%" nowrap="nowrap">{/global.operation/}</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			$newsletter_row=db::get_limit_page('newsletter', '1', '*', 'NId desc', (int)$_GET['page'], 20);
			foreach($newsletter_row[0] as $v){
			?>
				<tr>
					<td nowrap="nowrap"><?=$newsletter_row[4]+$i++;?></td>
					<td nowrap="nowrap"><a href="?m=email&a=send&email=<?=$v['Email'];?>"><?=$v['Email'];?></a></td>
					<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<td nowrap="nowrap"><?=$v['IsUsed']?'{/global.n_y.1/}':'{/global.n_y.0/}';?></td>
					<td nowrap="nowrap">
						<?php if($v['IsUsed']==1){?>
						<a href="./?do_action=email.newsletter_status&NId=<?=$v['NId'];?>&Type=0" title="{/email.newsletter.cancel/}" class="del"><img src="/static/ico/no.png" align="absmiddle" /></a>&nbsp;&nbsp;
						<?php }else{?>
						<a href="./?do_action=email.newsletter_status&NId=<?=$v['NId'];?>&Type=1" title="{/email.newsletter.submit/}" class="del"><img src="/static/ico/yes.png" align="absmiddle" /></a>&nbsp;&nbsp;
						<?php }?>
						<a href="./?do_action=email.newsletter_del&NId=<?=$v['NId'];?>" title="{/global.del/}" class="del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</td>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<div id="turn_page"><?=manage::turn_page($newsletter_row[1], $newsletter_row[2], $newsletter_row[3], '?'.ly200::query_string('page').'&page=');?></div>
</div>