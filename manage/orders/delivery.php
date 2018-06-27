<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'delivery'));//检查权限

$all_currency_ary=array();
$currency_row=db::get_all('currency', '1', 'Currency, Symbol');
foreach($currency_row as $k=>$v){
	$all_currency_ary[$v['Currency']]=$v;
}
?>
<div class="r_nav">
	<h1>{/module.orders.delivery/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$no_sort_url='?'.ly200::get_query_string(ly200::query_string('page'));
	?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off"  />
					<input type="submit" class="search_btn" value="{/global.search/}" />
				</div>
				<div class="more"><div class="more_ico"></div></div>
				<div class="ext">
					<div class="rows">
						<label>{/orders.orders_status/}</label>
						<span class="input"><?=ly200::form_select($c['orders']['status'], 'OrderStatus', '', '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="orders" />
				<input type="hidden" name="a" value="delivery" />
			</form>
		</div>
	<?php }?>
</div>
<div id="orders" class="r_con_wrap">
	<script type="text/javascript">$(document).ready(function(){orders_obj.delivery_init()});</script>
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="12%" nowrap="nowrap">{/orders.oid/}</td>
				<td width="10%" nowrap="nowrap">{/orders.shipping.track_no/}</td>
				<td width="6%" nowrap="nowrap">{/orders.info.ship_info/}</td>
				<td width="10%" nowrap="nowrap">{/orders.orders_status/}</td>
				<td width="10%" nowrap="nowrap">{/set.country.country/}</td>
				<td width="10%" nowrap="nowrap">{/orders.info.delivery_time/}</a></td>
				<td width="15%" nowrap="nowrap">{/orders.info.receipt_time/}</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$g_Page=(int)$_GET['page'];
			$g_Page<1 && $g_Page=1;
			$Keyword=str::str_code($_GET['Keyword']);
			$ShippingSId=(int)$_GET['ShippingSId'];
			$where='o.OrderStatus>4 and o.OrderStatus!=7';//条件
			$page_count=25;//显示数量
			$Keyword && $where.=" and (o.OId like '%$Keyword%' or o.Email like '%$Keyword%' or concat(o.ShippingFirstName, ' ', o.ShippingLastName) like '%$Keyword%')";
			$ShippingCId && $where.=" and o.ShippingCId='$ShippingCId'";
			$OrderStatus && $where.=" and o.OrderStatus='$OrderStatus'";
			$delivery_row=str::str_code(db::get_limit_page('orders o', $where, "o.*, (select AccTime from orders_log where OrderId=o.OrderId and OrderStatus=5 order by AccTime desc limit 1) as DeliveryTime, (select AccTime from orders_log where OrderId=o.OrderId and OrderStatus=6 order by AccTime desc limit 1) as ReceiptTime", 'DeliveryTime desc', $g_Page, $page_count));
			$query_string=ly200::query_string(array('m', 'a', 'd'));
			$i=1;
			foreach($delivery_row[0] as $v){
			?>
				<tr>
					<td nowrap="nowrap"><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>&query_string=<?=urlencode($query_string);?>" title="<?=$v['OId'];?>" class="green"><?=$v['OId'];?></a></td>
					<td nowrap="nowrap"><?=$v['TrackingNumber'];?></td>
					<td nowrap="nowrap"><?=$v['ShippingExpress'];?></td>
					<td nowrap="nowrap"><?=str::str_color($c['orders']['status'][$v['OrderStatus']], $v['OrderStatus']);?></td>
					<td nowrap="nowrap"><?=$v['ShippingCountry'];?></td>
					<td nowrap="nowrap"><?=$v['DeliveryTime']?date('Y-m-d H:i:s', $v['DeliveryTime']):'N/A';?></td>
					<td nowrap="nowrap"><?=$v['ReceiptTime']?date('Y-m-d H:i:s', $v['ReceiptTime']):'N/A';?></td>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<div id="turn_page"><?=manage::turn_page($delivery_row[1], $delivery_row[2], $delivery_row[3], '?'.ly200::query_string('page').'&page=');?></div>	
</div>