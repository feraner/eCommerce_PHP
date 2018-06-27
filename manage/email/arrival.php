<?php !isset($c) && exit();?>
<div class="r_nav">
	<h1>{/module.email.arrival/}</h1>
	<div class="turn_page"></div>
</div>
<div id="arrival" class="r_con_wrap">
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="5%" nowrap="nowrap">{/global.serial/}</td>
				<td width="20%" nowrap="nowrap">{/products.product/}{/products.name/}</td>
				<td width="20%" nowrap="nowrap">{/global.email/}</td>
				<td width="15%" nowrap="nowrap">{/global.time/}</td>
				<td width="10%" nowrap="nowrap">{/email.send_status/}</td>
				<td width="15%" nowrap="nowrap">{/email.send_time/}</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			$arrival_row=str::str_code(db::get_limit_page('arrival_notice a left join products p on a.ProId=p.ProId left join user u on a.UserId=u.UserId', '1', "a.*, p.ProId, p.Name{$c['manage']['web_lang']}, u.Email", 'a.AId desc', (int)$_GET['page'], 20));
			foreach($arrival_row[0] as $v){
			?>
				<tr>
					<td nowrap="nowrap"><?=$newsletter_row[4]+$i++;?></td>
					<td><a href="<?=ly200::get_url($v, 'products');?>" target="_blank"><?=$v['Name'.$c['manage']['web_lang']];?></a></td>
					<td nowrap="nowrap"><a href="?m=email&a=send&email=<?=$v['Email'];?>"><?=$v['Email'];?></a></td>
					<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<td nowrap="nowrap"><?=$v['IsSend']?'<span class="fc_red">{/email.send_status_ary.1/}</span>':'{/email.send_status_ary.0/}';?></td>
					<td nowrap="nowrap"><?=$v['SendTime']?date('Y-m-d H:i:s', $v['SendTime']):'N/A';?></td>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<div id="turn_page"><?=manage::turn_page($arrival_row[1], $arrival_row[2], $arrival_row[3], '?'.ly200::query_string('page').'&page=');?></div>
</div>