<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'export'));//检查权限
$Time=$_GET['Time'];
if($Time!=''){
	$Time_ary=@explode(' - ', $Time);
	$StartTime=@strtotime($Time_ary[0].' 00:00:00');
	$EndTime=@strtotime($Time_ary[1].' 23:59:59');
}else{
	$month_time=3600*24*30;//30天内
	$StartTime=$c['time']-$month_time;
	$EndTime=$c['time'];
	$Time=@date('Y/m/d', $StartTime).' - '.@date('Y/m/d', $EndTime);
}
$Keyword=$_GET['Keyword'];
$Status=(int)$_GET['Status'];

//$where='1';//条件
$where='PaymentMethod like "Globebill%"'; //目前只有钱宝
$page_count=100;//显示数量
$Keyword && $where.=" and (OId like '%$Keyword%' or Email like '%$Keyword%' or concat(ShippingFirstName, ' ', ShippingLastName) like '%$Keyword%')";
$StartTime && $EndTime && $where.=" and OrderTime>{$StartTime} and OrderTime<{$EndTime}";
if($Status){
	$where.=" and OrderStatus='$Status'";
}else{
	$where.=" and OrderStatus in (1, 2, 3)";
}
$orders_row=str::str_code(db::get_limit_page('orders', $where, '*', 'OrderId desc', (int)$_GET['page'], $page_count));

echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<script type="text/javascript">$(document).ready(function(){orders_obj.orders_check_init()});</script>
<div class="r_nav">
	<h1>{/module.orders.check/}</h1>
</div>
<div id="orders" class="r_con_wrap">
	<form class="r_con_form" name="check_form">
		<div class="rows">
			<label>{/orders.payment_method/}</label>
			<span class="input orders_payment">
				<select name='Payment'>
					<option value='Globebill'>钱宝</option>
				</select>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/global.search/}</label>
			<span class="input orders_search">
				<input name="Keyword" value="<?=$Keyword;?>" type="text" class="form_input" size="23" />
				<input name="Time" value="<?=$Time;?>" type="text" class="form_input" size="23" readonly />
				<select name='Status'>
					<option value=''>{/global.select_index/}</option>
					<?php
					foreach($c['orders']['status'] as $k=>$v){
						if($k>3) continue;
					?>
						<option value='<?=$k;?>'><?=$v;?></option>
					<?php }?>
				</select>
				<input type="button" class="btn_ok" value="{/global.search/}" />
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/module.orders.module_name/}</label>
			<span class="input">
				<div id="explode_box">
					<ul class="orders_list">
						<?php
						foreach((array)$orders_row[0] as $v){
						?>
							<li><input type="checkbox" name="OrderId[]" class="fl" value="<?=$v['OrderId'];?>" /><span data="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>"><?=$v['OId'];?></span>, &nbsp;&nbsp;&nbsp;<?=$Symbol.sprintf('%01.2f', orders::orders_price($v, 0, 1)).', &nbsp;&nbsp;&nbsp;'.$v['ShippingFirstName'].' '.$v['ShippingLastName'].', &nbsp;&nbsp;&nbsp;'.$c['orders']['status'][$v['OrderStatus']];?></li>
						<?php }?>
					</ul>
				</div>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label></label>
			<span class="input">
				<a href="javascript:;" class="btn_ok btn_select_all">{/global.select_all/}</a>
				<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
			</span>
			<div class="clear"></div>
		</div>
		<div id="explode_progress_export" class="hide"></div>
		<input type="hidden" name="do_action" value="orders.orders_payment_check" />
		<input type="hidden" name="OrderIdStr" value="|" />
		<input type="hidden" name="selectAll" value="0" />
	</form>
</div>