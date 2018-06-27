<?php !isset($c) && exit();?>
<?php
$order_row=str::str_code(db::get_limit('orders', 'OrderStatus in(4,5,6)', 'OId, ShippingFirstName, OrderStatus', 'OrderId desc', 0, 20));
?>
<div class="order_live">
	<div class="order_live_hd"><?=$c['lang_pack']['order_live'];?></div>
	<div class="order_live_bd">
		<ul class="order_live_scroll">
			<?php
			foreach($order_row as $v){
			?>
			<li><b><?=$v['ShippingFirstName'];?></b><br />&nbsp;&nbsp;&nbsp;<?=$c['lang_pack']['user']['orderNo'].': '.$v['OId'];?><br />&nbsp;&nbsp;&nbsp;<?=$c['lang_pack']['user']['status'].': '.$c['lang_pack']['user']['OrderStatusAry'][$v['OrderStatus']];?></li>
			<?php }?>
		</ul>
	</div>
</div>