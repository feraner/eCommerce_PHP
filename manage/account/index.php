<?php !isset($c) && exit();?>
<?php
/* 国家简写 */
$country_row=str::str_code(db::get_all('country', 1, 'CId, CountryData, Acronym', 'CId asc'));
?>
<?=ly200::load_static('/static/js/plugin/jqvmap/jquery.vmap.js', '/static/js/plugin/jqvmap/maps/jquery.vmap.world.js', '/static/js/plugin/jqvmap/jqvmap.css');?>
<script type="text/javascript">
	var country_acronym_data={<?php foreach($country_row as $k=>$v){ $country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode'); echo "'{$country_data['zh-cn']}':'".strtolower($v['Acronym'])."',";}?>};
	$(document).ready(function(){account_obj.index_init()});
</script>
<div id="account" class="r_con_wrap home_wrap">
	<div class="home_box home_traffic mr22">
		<div class="traffic_time">
			<?php for($i=0; $i<=5; ++$i){?>
				<a href="javascript:;">{/account.traffic_time.<?=$i;?>/}</a>
			<?php }?>
		</div>
		<div class="blank15"></div>
		<div class="box_container">
			<div class="traffic_count traffic_order"><div class="traffic"></div><h2></h2><span>{/account.statistics_name.0/}</span></div>
			<div class="traffic_count traffic_sales"><div class="traffic" title=""></div><h2></h2><span>{/account.statistics_name.1/}</span></div>
			<div class="traffic_count traffic_ip"><div class="traffic traffic_to"></div><h2></h2><span>{/account.statistics_name.2/}</span></div>
			<div class="traffic_count traffic_pv"><div class="traffic traffic_to"></div><h2></h2><span>{/account.statistics_name.3/}</span></div>
		</div>
	</div>
	<div class="home_box home_source">
		<div class="box_title">{/account.source/}</div>
		<div class="box_container data_list"></div>
	</div>
	<div class="clear"></div>
	<div class="home_circle home_order mr22">
		<div class="circle_title">{/account.new_order/}</div>
		<div class="circle_container">
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="15%" nowrap="nowrap">{/global.time/}</td>
						<td width="25%" nowrap="nowrap">{/orders.oid/}</td>
						<td width="25%" nowrap="nowrap">{/orders.total_price/}</td>
						<td width="25%" nowrap="nowrap">{/orders.orders_status/}</td>
						<td width="10%" nowrap="nowrap">{/global.operation/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$where='1';
					(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and ((o.SalesId>0 and o.SalesId='{$_SESSION['Manage']['UserId']}') or (o.SalesId=0 and u.SalesId='{$_SESSION['Manage']['UserId']}'))";//业务员账号过滤
					$order_row=str::str_code(db::get_limit('orders o left join user u on o.UserId=u.UserId', $where, 'o.*', 'o.OrderId desc', 0, 5));
					if($order_row){
						foreach($order_row as $v){
						?>
						<tr>
							<td nowrap="nowrap"><?=date('m/d/Y', $v['OrderTime']);?></td>
							<td nowrap="nowrap"><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>" title="<?=$v['OId'];?>" class="blue"><?=$v['OId'];?></a></td>
							<td nowrap="nowrap"><?=$c['manage']['currency_symbol'].sprintf('%01.2f', orders::orders_price($v, 0, 1));?></td>
							<td nowrap="nowrap"><?=str::str_color($c['orders']['status'][$v['OrderStatus']], $v['OrderStatus']);?></td>
							<td><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>" title="{/global.view/}"><img src="/static/ico/search.png" alt="{/global.view/}" /></a></td>
						</tr>
						<?php
						}
					}else{?>
						<tr><td class="no_list" colspan="5">{/account.error_tips/}</td></tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="home_circle home_map">
		<div class="circle_title">{/account.from/}</div>
		<div class="circle_container"><div id="world_map"></div></div>
	</div>
	<div class="clear"></div>
</div>