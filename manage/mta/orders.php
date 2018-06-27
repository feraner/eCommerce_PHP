<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/highcharts/highcharts.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">$(document).ready(mta_obj.orders_init);</script>
<div id="mta" class="r_con_wrap">
	<div class="box nav">
		<dl class="time">
			<dt>{/global.time/}:</dt>
			<?php
			foreach(array(0,-1,-7,-30) as $k=>$v){
			?>
				<dd><a href="javascript:void(0);" rel="<?=$v;?>" class="<?=$v==-30?'cur':'';?>">{/mta.time_ary.<?=$k;?>/}</a></dd>
			<?php }?>
		</dl>
		<ul>
			<li><input type="text" name="TimeS" value="" readonly class="form_input" notnull /></li>
			<li class="compared_input"><input type="text" name="TimeE" value="" readonly class="form_input" /></li>
			<li><input type="submit" class="btn_ok" value="{/global.view/}" /></li>
			<li class="compared"></li>
			<li class="compared_txt">{/mta.compared/}</li>
		</ul>
		<dl class="terminal">
			<dt>{/mta.terminal/}:</dt>
			<?php
			foreach(array(0,1,2) as $v){
			?>
				<dd><a href="javascript:void(0);" rel="<?=$v;?>" class="<?=$v==0?'cur':'';?>">{/mta.terminal_ary.<?=$v;?>/}</a></dd>
			<?php }?>
		</dl>
	</div>
	<ul class="box data_list order_data_list">
		<li>
			<div>
				<h1>{/mta.order.order_price/}</h1>
				<h2><span class="order_price">0</span></h2>
                <h3 class="compare compare_order_price">0</h3>
                <div class="blank12"></div>
                <dl>
                	<dt><span>{/mta.order.order_unit_price/} :&nbsp;</span><br /><span class="compare">{/mta.compared/} :&nbsp;</span></dt>
                    <dd><span class="order_unit_price">0</span><br /><span class="compare compare_order_unit_price">0</span></dd>
                </dl>
                <div class="clear"></div>
                <dl>
                	<dt><span>{/mta.order.order_count/} :&nbsp;</span><br /><span class="compare">{/mta.compared/} :&nbsp;</span></dt>
                    <dd><span class="order_count">0</span><br /><span class="compare compare_order_count">0</span></dd>
                </dl>
                <div class="clear"></div>
			</div>
		</li>
		<li>
			<div>
				<h1>{/mta.order.customer_price/}</h1>
				<h2><span class="customer_price">0</span></h2>
                <h3 class="compare compare_customer_price">0</h3>
                <div class="blank12"></div>
                <dl>
                	<dt><span>{/mta.order.order_customer/} :&nbsp;</span><br /><span class="compare">{/mta.compared/} :&nbsp;</span></dt>
                    <dd><span class="order_customer">0</span><br /><span class="compare compare_order_customer">0</span></dd>
                </dl>
                <div class="clear"></div>
			</div>
		</li>
		<li>
			<div>
				<h1>{/mta.ratio/}</h1>
				<h2><span class="ratio">0</span></h2>
                <h3 class="compare compare_ratio"></h3>
                <div class="blank12"></div>
                <dl>
                	<dt><span>{/mta.order.visit_customer/} :&nbsp;</span><br /><span class="compare">{/mta.compared/} :&nbsp;</span></dt>
                    <dd><span class="visit_customer">0</span><br /><span class="compare compare_visit_customer">0</span></dd>
                </dl>
                <div class="clear"></div>
			</div>
		</li>
		<li>
			<div>
				<h1>{/mta.order.discount_price/}</h1>
				<h2><span class="discount_price">0</span></h2>
                <h3 class="compare compare_discount_price">0</h3>
                <div class="blank12"></div>
                <dl>
                	<dt><span>{/mta.order.use_times/} :&nbsp;</span><br /><span class="compare">{/mta.compared/} :&nbsp;</span></dt>
                    <dd><span class="use_times">0</span><br /><span class="compare compare_use_times">0</span></dd>
                </dl>
                <div class="clear"></div>
			</div>
		</li>
	</ul>
	<div class="box charts orders_charts"></div>
	<div class="box detail country">
    	<h1>{/mta.country/}</h1>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table">
			<thead>
				<tr>
					<td width="25%" nowrap="nowrap">{/mta.country/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.amount/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_count/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_unit_price/}</td>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<div class="box detail referrer hide">
    	<h1>{/mta.referrer/}</h1>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table">
			<thead>
				<tr>
					<td width="25%" nowrap="nowrap">{/mta.referrer/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.amount/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_count/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_unit_price/}</td>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<div class="box detail payment">
    	<h1>{/mta.order.payment/}</h1>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table">
			<thead>
				<tr>
					<td width="25%" nowrap="nowrap">{/mta.order.payment/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.amount/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_count/}</td>
					<td width="25%" nowrap="nowrap">{/mta.order.order_unit_price/}</td>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>