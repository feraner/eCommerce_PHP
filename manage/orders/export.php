<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'export'));//检查权限
$Time=$_GET['Time'];
if($Time!=''){
	$Time_ary=@explode('/', $Time);
	$StartTime=@strtotime($Time_ary[0]);
	$EndTime=@strtotime($Time_ary[1]);
}else{
	$month_time=3600*24*30;//30天内
	$StartTime=@strtotime(date('Y-m-d', $c['time']-$month_time).' 00:00:00');
	$EndTime=@strtotime(date('Y-m-d', $c['time']).' 23:59:59');
	$Time=@date('Y-m-d H:i', $StartTime).'/'.@date('Y-m-d H:i', $EndTime);
}
$Keyword=$_GET['Keyword'];
$Status=(int)$_GET['Status'];

$where='1';//条件
$page_count=2000;//显示数量
$Keyword && $where.=" and (o.OId like '%$Keyword%' or o.Email like '%$Keyword%' or concat(o.ShippingFirstName, ' ', o.ShippingLastName) like '%$Keyword%' or o.`ShippingAddressLine1` like '%$Keyword%' or o.`ShippingAddressLine2` like '%$Keyword%')";
if($StartTime && $EndTime){
	if((int)$_GET['TimeType']==1) $where.=" and o.PayTime>{$StartTime} and o.PayTime<{$EndTime}";
	elseif((int)$_GET['TimeType']==2) $where.=" and o.ShippingTime>{$StartTime} and o.ShippingTime<{$EndTime}";
	else $where.=" and o.OrderTime>{$StartTime} and o.OrderTime<{$EndTime}";
}
$Status && $where.=" and o.OrderStatus='$Status'";
(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and ((o.SalesId>0 and o.SalesId='{$_SESSION['Manage']['UserId']}') or (o.SalesId=0 and u.SalesId='{$_SESSION['Manage']['UserId']}'))";//业务员账号过滤
$orders_row=str::str_code(db::get_limit_page('orders o left join user u on o.UserId=u.UserId', $where, '*', 'o.OrderId desc', (int)$_GET['page'], $page_count));

//$menu_ary=array('A'=>'订单ID', 'B'=>'订单号', 'C'=>'邮箱', 'D'=>'产品总额', 'E'=>'运费', 'F'=>'保险费', 'G'=>'订单总额', 'H'=>'总重量', 'I'=>'总体积', 'J'=>'订单状态', 'K'=>'配送方式', 'L'=>'付款方式', 'M'=>'时间', 'N'=>'优惠券', 'O'=>'收货姓名', 'P'=>'收货地址', 'Q'=>'收货地址2', 'R'=>'收货国家', 'S'=>'收货省份', 'T'=>'收货城市', 'U'=>'收货邮编', 'V'=>'收货电话', 'W'=>'账单姓名', 'X'=>'账单地址', 'Y'=>'账单地址2', 'Z'=>'账单国家', 'AA'=>'账单省份', 'AB'=>'账单城市', 'AC'=>'账单邮编', 'AD'=>'账单电话', 'AE'=>'运单号', 'AF'=>'备注内容', 'AG'=>'产品名称', 'AH'=>'产品编号', 'AI'=>'产品数量', 'AJ'=>'产品SKU', 'AK'=>'产品属性');
$menu_ary=array('A'=>$c['manage']['lang_pack']['orders']['orders'].' ID', 'B'=>$c['manage']['lang_pack']['orders']['oid'], 'C'=>$c['manage']['lang_pack']['global']['email'], 'D'=>$c['manage']['lang_pack']['orders']['info']['product_price'], 'E'=>$c['manage']['lang_pack']['orders']['info']['charges'], 'F'=>$c['manage']['lang_pack']['orders']['info']['insurance'], 'G'=>$c['manage']['lang_pack']['orders']['total_price'], 'H'=>$c['manage']['lang_pack']['orders']['info']['weight'], 'I'=>$c['manage']['lang_pack']['orders']['info']['volume'], 'J'=>$c['manage']['lang_pack']['orders']['orders_status'], 'K'=>$c['manage']['lang_pack']['orders']['info']['ship_info'], 'L'=>$c['manage']['lang_pack']['orders']['payment_method'], 'M'=>$c['manage']['lang_pack']['orders']['time'], 'N'=>$c['manage']['lang_pack']['orders']['info']['coupon'], 'O'=>$c['manage']['lang_pack']['orders']['export']['shipname'], 'P'=>$c['manage']['lang_pack']['orders']['export']['shipaddress'], 'Q'=>$c['manage']['lang_pack']['orders']['export']['shipaddress2'], 'R'=>$c['manage']['lang_pack']['orders']['export']['shipcountry'], 'S'=>$c['manage']['lang_pack']['orders']['export']['shipstate'], 'T'=>$c['manage']['lang_pack']['orders']['export']['shipcity'], 'U'=>$c['manage']['lang_pack']['orders']['export']['shipzip'], 'V'=>$c['manage']['lang_pack']['orders']['export']['shipphone'], 'W'=>$c['manage']['lang_pack']['orders']['export']['billname'], 'X'=>$c['manage']['lang_pack']['orders']['export']['billaddress'], 'Y'=>$c['manage']['lang_pack']['orders']['export']['billaddress2'], 'Z'=>$c['manage']['lang_pack']['orders']['export']['billcountry'], 'AA'=>$c['manage']['lang_pack']['orders']['export']['billstate'], 'AB'=>$c['manage']['lang_pack']['orders']['export']['billcity'], 'AC'=>$c['manage']['lang_pack']['orders']['export']['billzip'], 'AD'=>$c['manage']['lang_pack']['orders']['export']['billphone'], 'AE'=>$c['manage']['lang_pack']['orders']['shipping']['track_no'], 'AF'=>$c['manage']['lang_pack']['orders']['payment']['contents'], 'AG'=>$c['manage']['lang_pack']['orders']['export']['proname'], 'AH'=>$c['manage']['lang_pack']['orders']['export']['pronumber'], 'AI'=>$c['manage']['lang_pack']['orders']['export']['proqty'], 'AJ'=>$c['manage']['lang_pack']['orders']['export']['prosku'], 'AK'=>$c['manage']['lang_pack']['orders']['export']['proattr']);
$menu_row=str::str_code(db::get_value('config', 'GroupId="orders_export" and Variable="Menu"', 'Value'));
$menu_value=str::json_data(htmlspecialchars_decode($menu_row), 'decode');
if(count($menu_ary)!=count($menu_value)){ //数量不一致
	foreach($menu_ary as $k=>$v){
		if(!$menu_value[$k]) $menu_value[$k]=0;
	}
}

echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<script type="text/javascript">
$(document).ready(function(){
	orders_obj.orders_explode_init();
	$('input[name=Time]').daterangepicker();
});
</script>
<div class="r_nav">
	<h1>{/module.orders.export/}</h1>
</div>
<div id="orders" class="r_con_wrap">
	<form class="r_con_form" name="export_form">
		<div class="export_left">
			<div class="rows">
				<label>{/global.search/}</label>
				<span class="input orders_search">
					<input name="Keyword" value="<?=$Keyword;?>" type="text" class="form_input" size="23" />
					<select name='TimeType'>
						<option value='0'>{/orders.info.order_time/}</option>
						<option value='1'>{/orders.info.payment_time/}</option>
						<option value='2'>{/orders.info.delivery_time/}</option>
					</select>
					<input name="Time" value="<?=$Time;?>" type="text" class="form_input" size="23" readonly />
					<select name='Status'>
						<option value=''>{/global.select_index/}</option>
						<?php foreach($c['orders']['status'] as $k=>$v){?>
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
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.explode/}" />
				</span>
				<div class="clear"></div>
			</div>
			<div id="explode_progress_export" class="hide"></div>
			<input type="hidden" name="do_action" value="orders.orders_explode" />
			<input type="hidden" name="OrderIdStr" value="|" />
			<input type="hidden" name="selectAll" value="0" />
			<input type="hidden" name="Number" value="0" />
		</div>
		<div class="export_right">
			<h3 class="rows_hd">{/global.set/}</h3>
			<ul class="export_menu">
				<?php
				foreach($menu_value as $k=>$v){
				?>
					<li>
						<input type="checkbox" name="Menu[]" value="<?=$k;?>"<?=$v?' checked':'';?> /> <label for="Menu"><?=$menu_ary[$k];?></label>
					</li>
				<?php }?>
			</ul>
			<div class="export_btn"><a href="javascript:;" class="btn_ok btn_save">{/global.save/}</a></div>
		</div>
		<div class="clear"></div>
	</form>
</div>