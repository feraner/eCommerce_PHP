<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'orders'));//检查权限
//货币汇率
$all_currency_ary=array();
$currency_row=db::get_all('currency', '1', 'Currency, Symbol');
foreach($currency_row as $k=>$v){
	$all_currency_ary[$v['Currency']]=$v;
}
//业务员
$sales_ary=array();
$manage_row=db::get_all('manage_sales');
foreach((array)$manage_row as $k=>$v){
	$sales_ary[$v['SalesId']]=$v;
}

$permit_ary=array(
	'edit'	=>	manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit')),
	'del'	=>	manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'del'))
);
?>
<script src="/static/js/plugin/file_upload/js/vendor/jquery.ui.widget.js"></script>
<script src="//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
<script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
<script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
<script src="//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.iframe-transport.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-process.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-image.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-audio.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-video.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-validate.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-ui.js"></script>
<!--[if (gte IE 8)&(lt IE 10)]><script src="/static/js/plugin/file_upload/js/cors/jquery.xdr-transport.js"></script><![endif]-->
<div class="r_nav">
	<h1>{/module.orders.orders/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$no_sort_url='?'.ly200::get_query_string(ly200::query_string('page, Sort'));
		$Sort=$_GET['Sort'];
		$sort_ary=array(
			'1a'	=>	'PayTime asc,',
			'1d'	=>	'PayTime desc,',
			'2a'	=>	'OrderTime asc,',
			'2d'	=>	'OrderTime desc,'
		);
		$column_row=db::get_value('config', "GroupId='custom_column' and Variable='Orders'", 'Value');
		$custom_ary=str::json_data($column_row, 'decode');
		$column_fixed_ary=array('orders.oid', 'global.email', 'orders.total_price', 'orders.orders_status', 'orders.info.payment_time', 'global.time');
		$column_ary=array('orders.oid', 'orders.source', 'orders.name', 'global.email', 'orders.info.product_price', 'orders.info.charges_insurance', 'orders.total_price', 'orders.info.weight', 'orders.orders_status', 'set.country.country', 'orders.info.payment_time', 'global.time');
		if($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)){//业务员
			$column_ary[]='user.sales';
		}
	?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="<?=$_GET['Keyword'];?>" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/orders.orders_status/}</label>
						<span class="input"><?=ly200::form_select($c['orders']['status'], 'OrderStatus', (int)$_GET['OrderStatus'], '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/set.country.country/}</label>
						<?php 
                        $country_row=str::str_code(db::get_all('country', "1", '*', 'Country asc'));
                        foreach($country_row as $v){$country_ary[$v['CId']]=$v['Country'];}
                        ?>
						<span class="input"><?=ly200::form_select($country_ary, 'CId', (int)$_GET['CId'], '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<?php
					if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && count($sales_ary) && (int)$_SESSION['Manage']['GroupId']!=3){//业务员
					?>
						<div class="rows">
							<label>{/manage.manage.permit_name.3/}</label>
							<span class="input"><?=ly200::form_select($sales_ary, 'SalesId', '', 'UserName', 'SalesId', '{/global.select_index/}');?></span>
							<div class="clear"></div>
						</div>
					<?php }?>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="orders" />
				<input type="hidden" name="a" value="orders" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
			<?php if($permit_ary['edit']){?>
				<li class="extend">
					<a href="javascript:;" label="{/global.custom_column/}"></a>
					<form>
						<?php
						foreach((array)$column_ary as $v){
							$checked=(in_array($v, $column_fixed_ary) || in_array($v, $custom_ary))?' checked':'';
							$disabled=in_array($v, $column_fixed_ary)?' disabled':'';
						?>
							<div class="item"><input type="checkbox" name="Custom[]" class="custom_list" value="<?=$v;?>"<?=$checked.$disabled;?> /> {/<?=$v?>/}</div>
						<?php }?>
						<div class="blank6"></div>
						<input type="submit" class="submit_btn" value="{/global.submit/}" />&nbsp;&nbsp;<input type="checkbox" name="custom_all" value="" class="va_m" /> {/global.select_all/}
						<input type="hidden" name="do_action" value="orders.orders_custom_column" />
					</form>
				</li>
			<?php }?>
		</ul>
	<?php }?>
</div>
<div id="orders" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$OrderStatus=(int)$_GET['OrderStatus'];//订单状态（搜索）
		$query_string=ly200::query_string('OrderStatus');
	?>
    	<script type="text/javascript">$(document).ready(function(){orders_obj.orders_init()});</script>
		<div class="r_con_column">
			<dl class="orders_status_list">
				<dd><a href="./?m=orders&a=orders" status="0"<?=$OrderStatus==0?' class="current"':'';?>>All</a></dd>
				<?php foreach($c['orders']['status'] as $k=>$v){?>
					<dt></dt><dd><a href="./?OrderStatus=<?=$k;?>&<?=$query_string;?>" status="<?=$k;?>"<?=$OrderStatus==$k?' class="current"':'';?>><?=$c['orders']['status'][$k];?></a></dd>
				<?php }?>
			</dl>
		</div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="3%" nowrap="nowrap"><input type="checkbox" name="select_all" /></td>
					<?php }?>
					<td width="12%" nowrap="nowrap">{/orders.oid/}</td>
					<?php if(in_array('orders.source', $custom_ary)){?><td width="8%" nowrap="nowrap">{/orders.source/}</td><?php }?>
					<?php if(in_array('orders.name', $custom_ary)){?><td width="8%" nowrap="nowrap">{/orders.name/}</td><?php }?>
					<td width="10%" nowrap="nowrap">{/global.email/}</td>
					<?php if(in_array('user.sales', $custom_ary)){?><td width="8%" nowrap="nowrap">{/manage.manage.permit_name.3/}</td><?php }?>
					<?php if(in_array('orders.info.product_price', $custom_ary)){?><td width="6%" nowrap="nowrap">{/orders.info.product_price/}</td><?php }?>
					<?php if(in_array('orders.info.charges_insurance', $custom_ary)){?><td width="6%" nowrap="nowrap">{/orders.info.charges_insurance/}</td><?php }?>
					<td width="6%" nowrap="nowrap">{/orders.total_price/}</td>
					<?php if(in_array('orders.info.weight', $custom_ary)){?><td width="6%" nowrap="nowrap">{/orders.info.weight/}</td><?php }?>
					<td width="10%" nowrap="nowrap">{/orders.orders_status/}</td>
					<?php if(in_array('set.country.country', $custom_ary)){?><td width="10%" nowrap="nowrap">{/set.country.country/}</td><?php }?>
					<td width="10%" nowrap="nowrap">
						<a href="<?=$no_sort_url.'&Sort='.($Sort=='1a'?'1d':'1a');?>">{/orders.info.payment_time/}<i class="<?php if($Sort=='1d') echo 'sort_icon_arrow_down'; elseif($Sort=='1a') echo 'sort_icon_arrow_up'; else echo 'sort_icon_arrow';?>"></i></a>
					</td>
					<td width="15%" nowrap="nowrap">
						<a href="<?=$no_sort_url.'&Sort='.($Sort=='2a'?'2d':'2a');?>">{/orders.info.order_time/}<i class="<?php if($Sort=='2d') echo 'sort_icon_arrow_down'; elseif($Sort=='2a') echo 'sort_icon_arrow_up'; else echo 'sort_icon_arrow';?>"></i></a>
					</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="8%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=str::str_code($_GET['Keyword']);
				$ShippingCId=(int)$_GET['CId'];
				$g_SalesId=(int)$_GET['SalesId'];
				$where='1';//条件
				$page_count=25;//显示数量
				/*** 通过产品搜索订单 ***/
				$Keyword && $orders_id_row = db::get_all('orders_products_list',"Name like '%$Keyword%'",'OrderId');
				$orders_id = '';
				foreach((array)$orders_id_row as $k => $v){
					$orders_id.=($k?',':'').$v['OrderId'];
				}
				/*** 通过产品搜索订单 ***/
				$Keyword && $where.=" and (o.OId like '%$Keyword%' or o.Email like '%$Keyword%' or concat(o.ShippingFirstName, ' ', o.ShippingLastName) like '%$Keyword%' or o.TrackingNumber like '%$Keyword%'".($orders_id?" or o.OrderId in ($orders_id)":'').')';
				$ShippingCId && $where.=" and o.ShippingCId='$ShippingCId'";
				$OrderStatus && $where.=" and o.OrderStatus='$OrderStatus'";
				$g_SalesId && $where.=" and ((o.SalesId>0 and o.SalesId='$g_SalesId') or (o.SalesId=0 and u.SalesId='$g_SalesId'))";
				(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and ((o.SalesId>0 and o.SalesId='{$_SESSION['Manage']['SalesId']}') or (o.SalesId=0 and u.SalesId='{$_SESSION['Manage']['SalesId']}'))";//业务员账号过滤
				$orders_row=str::str_code(db::get_limit_page('orders o left join user u on o.UserId=u.UserId', $where, 'o.*, o.SalesId as OSalesId, u.SalesId', $sort_ary[$Sort].'o.OrderId desc', (int)$_GET['page'], $page_count));
				$query_string=ly200::query_string(array('m', 'a', 'd'));
				$i=1;
				foreach($orders_row[0] as $v){
					$isFee=($v['OrderStatus']>=4 && $v['OrderStatus']!=7)?1:0;
					$total_price=orders::orders_price($v, $isFee, 1);
					$Symbol=$v['ManageCurrency']?$all_currency_ary[$v['ManageCurrency']]['Symbol']:$c['manage']['currency_symbol'];
					$SalesId=$v['OSalesId']?$v['OSalesId']:$v['SalesId'];
				?>
					<tr>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['OrderId'];?>" /></td>
						<?php }?>
						<td nowrap="nowrap"><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>&query_string=<?=urlencode($query_string);?>" title="<?=$v['OId'];?>" class="green"><?=$v['OId'];?></a></td>
						<?php if(in_array('orders.source', $custom_ary)){?><td nowrap="nowrap">{/orders.source_ary.<?=$v['Source'];?>/}</td><?php }?>
						<?php if(in_array('orders.name', $custom_ary)){?><td nowrap="nowrap"><?=$v['ShippingFirstName'].' '.$v['ShippingLastName'];?></td><?php }?>
						<td nowrap="nowrap"><a href="./?m=email&d=send&Email=<?=urlencode($v['Email'].'/'.$v['ShippingFirstName'].' '.$v['ShippingLastName']);?>" title="{/module.email.send/}" class="green"><?=$v['Email'];?></a></td>
						<?php if(in_array('user.sales', $custom_ary)){?>
							<td nowrap="nowrap"<?=($permit_ary['edit'] && !$v['UserId'] && (int)$_SESSION['Manage']['GroupId']!=3)?' class="sales_select"':'';?> data-id="<?=$SalesId;?>"><?=$SalesId?$sales_ary[$SalesId]['UserName']:'N/A';?></td>
						<?php }?>
						<?php if(in_array('orders.info.product_price', $custom_ary)){?><td nowrap="nowrap"><?=$Symbol.sprintf('%01.2f', $v['ProductPrice']);?></td><?php }?>
						<?php if(in_array('orders.info.charges_insurance', $custom_ary)){?><td nowrap="nowrap"><?=$Symbol.sprintf('%01.2f', $v['ShippingPrice']+$v['ShippingInsurancePrice']);?></td><?php }?>
						<td nowrap="nowrap"><?=$Symbol.sprintf('%01.2f', $total_price);?></td>
						<?php if(in_array('orders.info.weight', $custom_ary)){?><td nowrap="nowrap"><?=$v['TotalWeight'];?>{/orders.info.unit/}</td><?php }?>
						<td nowrap="nowrap"><?=str::str_color($c['orders']['status'][$v['OrderStatus']], $v['OrderStatus']);?></td>
						<?php if(in_array('set.country.country', $custom_ary)){?><td><?=$v['ShippingCountry'];?></td><?php }?>
						<td nowrap="nowrap"><?=$v['PayTime']?date('Y-m-d H:i:s', $v['PayTime']):'N/A';?></td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['OrderTime']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<a class="tip_ico tip_min_ico" href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>&query_string=<?=urlencode($query_string);?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a>
								<?php if($permit_ary['edit']){?>
									<a class="tip_ico tip_min_ico print" href="javascript:;" label="{/global.print/}" orderid="<?=$v['OrderId'];?>"><img src="/static/ico/print.png" alt="{/global.print/}" align="absmiddle" /></a>
									<a class="tip_ico tip_min_ico explode" href="./?do_action=orders.orders_explode_products&OrderId=<?=$v['OrderId'];?>" label="{/global.explode/}{/orders.orders/}"><img src="/static/ico/explode.png" alt="{/global.explode/}{/module.products.module_name/}" align="absmiddle" /></a>
								<?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=orders.orders_del&OrderId=<?=$v['OrderId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
        <div id="turn_page"><?=manage::turn_page($orders_row[1], $orders_row[2], $orders_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		<div id="sales_select_hide" class="hide"><?=ly200::form_select($sales_ary, 'SalesId', '', 'UserName', 'SalesId', '{/global.select_index/}');?></div>
	<?php
	}elseif($c['manage']['do']=='view'){
		$OrderId=(int)$_GET['OrderId'];
		$query_string=urldecode($_GET['query_string']);
		$orders_row=str::str_code(db::get_one('orders', "OrderId='$OrderId'"));
		!$orders_row && js::location('./?m=orders');
		$Symbol=$orders_row['ManageCurrency']?$all_currency_ary[$orders_row['ManageCurrency']]['Symbol']:$c['manage']['currency_symbol'];
		$total_price=orders::orders_price($orders_row, 1, 1);
		$HandingFee=$total_price-orders::orders_price($orders_row, 0, 1);
		$total_weight=$orders_row['TotalWeight'];
		
		$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
		$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");
		
		$paypal_address_row=str::str_code(db::get_one('orders_paypal_address_book', "OrderId='$OrderId'"));

		//订单地址json值
		$address_ary=array(
			'OrderId'			=>	$orders_row['OrderId'],
			'FirstName'			=>	$orders_row['ShippingFirstName'],
			'LastName'			=>	$orders_row['ShippingLastName'],
			'AddressLine1'		=>	$orders_row['ShippingAddressLine1'],
			'AddressLine2'		=>	$orders_row['ShippingAddressLine2'],
			'City'				=>	$orders_row['ShippingCity'],
			'State'				=>	$orders_row['ShippingState'],
			'SId'				=>	$orders_row['ShippingSId'],
			'Country'			=>	$orders_row['ShippingCountry'],
			'CId'				=>	$orders_row['ShippingCId'],
			'ZipCode'			=>	$orders_row['ShippingZipCode'],
			'CodeOption'		=>	$orders_row['ShippingCodeOption'],
			'CodeOptionId'		=>	$orders_row['ShippingCodeOptionId'],
			'TaxCode'			=>	$orders_row['ShippingTaxCode'],
			'CountryCode'		=>	$orders_row['ShippingCountryCode'],
			'PhoneNumber'		=>	$orders_row['ShippingPhoneNumber']
		);
		$address_json_data=str::json_data($address_ary);
		
		//订单价格json值
		$price_ary=array(
			'ProductPrice'		=>	$orders_row['ProductPrice'],
			'PayAdditionalFee'	=>	$orders_row['PayAdditionalFee'],
			'PayAdditionalAffix'=>	$orders_row['PayAdditionalAffix'],
			'Discount'			=>	$orders_row['Discount'],
			'DiscountPrice'		=>	$orders_row['DiscountPrice'],
			'CouponDiscount'	=>	$orders_row['CouponDiscount'],
			'CouponPrice'		=>	$orders_row['CouponPrice'],
			'UserDiscount'		=>	($orders_row['UserDiscount']>0 && $orders_row['UserDiscount']<100) ? $orders_row['UserDiscount'] : 100
		);
		$price_json_data=str::json_data($price_ary);
		
		//发货方式json值
		$shipping_ary=array(
			'OrderId'					=>	$orders_row['OrderId'],
			'ShippingMethodSId'			=>	$orders_row['ShippingMethodSId'],
			'ShippingPrice'				=>	$orders_row['ShippingPrice'],
			'ShippingInsurance'			=>	(int)$orders_row['ShippingInsurance'],
			'ShippingInsurancePrice'	=>	$orders_row['ShippingInsurancePrice'],
			'TotalWeight'				=>	$orders_row['TotalWeight'],
			'TotalVolume'				=>	$orders_row['TotalVolume'],
			'ShippingOvExpress'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvExpress']), 'decode'),
			'ShippingOvSId'				=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvSId']), 'decode'),
			'ShippingOvType'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvType']), 'decode'),
			'ShippingOvInsurance'		=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurance']), 'decode'),
			'ShippingOvPrice'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvPrice']), 'decode'),
			'ShippingOvInsurancePrice'	=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurancePrice']), 'decode')
		);
		$shipping_json_data=str::json_data($shipping_ary);

		$orders_log_row=db::get_all('orders_log', "OrderId='{$orders_row['OrderId']}'", '*', 'LId desc');	//订单日志
		
		$orders_remark_log_row=db::get_all('orders_remark_log', "OrderId='{$orders_row['OrderId']}'", '*', 'RId desc');	//订单备注日志
		
		//会员信息
		(int)$orders_row['UserId'] && $user_row=str::str_code(db::get_one('user', "UserId='{$orders_row['UserId']}'"));
		
		//所有产品属性
		$attribute_cart_ary=$vid_data_ary=array();
		$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['manage']['web_lang']}, ParentId, CartAttr, ColorAttr"));
		foreach($attribute_row as $v){
			$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['manage']['web_lang']}"]);
		}
		$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
		foreach($value_row as $v){
			$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['manage']['web_lang']}"];
		}
		
		//订单产品信息
		$oversea_id_ary=array();
		$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$OrderId'", 'o.*, o.SKU as OrderSKU, p.Prefix, p.Number, p.SKU, p.PicPath_0, p.Business', 'o.LId asc');
		foreach($order_list_row as $k=>$v){
			!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
		}
		sort($oversea_id_ary); //排列正序
		
		//发货地
		$overseas_ary=array();
		$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
		foreach($overseas_row as $v){
			$overseas_ary[$v['OvId']]=$v;
		}
	?>
		<?=ly200::load_static('/static/themes/default/css/user.css', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
		<script language="javascript">
			<?php if($orders_row['ManageCurrency']){?>ueeshop_config.currency='<?=$all_currency_ary[$orders_row['ManageCurrency']]['Symbol'];?>';<?php }?>
			$(document).ready(function(){orders_obj.orders_view();});
		</script>
        <h1><a href="" class="id">NO.#<?=$orders_row['OId'];?></a><span>-</span><a href="./?m=orders&a=orders&<?=$query_string;?>" class="green">{/global.return/}</a><span>-</span><a href="./?m=orders&a=orders&d=view&iframe=1&OrderId=<?=$OrderId;?>&do=print" class="green" target="_blank">{/global.print/}</a></h1>
        <div class="blank20"></div>
        <div class="time-line">
        	<div class="time-line-bg">
            	<div class="complete"></div>
                <ul class="round">
                    <li class="child_1"></li>
                    <li class="child_2"></li>
                    <li class="child_3"></li>
                    <li class="child_4"></li>
                    <li class="child_5"></li>
                    <li class="child_6"></li>
                    <li class="child_last"></li>
                </ul>
                <ul>
                    <?php 
					for($i=1, $len=count($c['orders']['status']);$i<=$len;$i++){
						$class=' class="child_'.($i==$len?'last':$i).'"';
						if($i==$orders_row['OrderStatus'])
							echo "<li{$class}><div class=\"finish\"><div class=\"num\">{$i}</div></div></li>";
						else if($i<$orders_row['OrderStatus'])
							echo "<li{$class}><div class=\"finish\"><div class=\"yes\"></div></div></li>";
						else
							echo "<li{$class}><div class=\"num\">{$i}</div></li>";
					}
					?>
                </ul>
            </div>
            <dl>
				<?php
				$len=end(array_keys($c['orders']['status']));
				foreach($c['orders']['status'] as $k=>$v){
					$class=' class="child_'.($k==$len?'last':$k).'"';
					echo '<dt'.$class.'>'.$v.'</dt>';
				}?>
			</dl>
            <?php 
			$StatusTimeArr=array();
			foreach($orders_log_row as $v){
				$StatusTimeArr[$v['OrderStatus']]=$v['AccTime'];
				$v['AccTime']?date('F d, Y', $v['AccTime']):'N/A';
				$v['AccTime']?date('h:i A', $v['AccTime']):'N/A';
			}?>
            <dl>
				<?php
				foreach($c['orders']['status'] as $k=>$v){
					$class=' class="child_'.($k==$len?'last':$k).'"';
					echo '<dd'.$class.'>'.($StatusTimeArr[$k]?date('Y-m-d H:i:s', $StatusTimeArr[$k]):'').'</dd>';
				}?>
            </dl>
        </div>
        <div class="blank20"></div>
        <input type="hidden" name="CurrencyCode" value="<?=$Symbol;?>" />
        <input type="hidden" name="CouponDiscount" value="<?=$orders_row['CouponDiscount'];?>" />
        <input type="hidden" name="CouponPrice" value="<?=$orders_row['CouponPrice'];?>" />
        <div class="baseinfo">
            <div class="shipping">
                <h4>
                	{/orders.info.address_info/}
                    <?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?><a href="javascript:;" class="green edit address">[{/global.mod/}]</a><?php }?>
                </h4>
                <ul class="border address_info">
					<?php
					if($paypal_address_row){
						if($paypal_address_row['IsUse']==1){//没使用
							echo '<li class="fixed"><input type="button" value="{/global.use/}" class="btn_ok btn_use" date-id="'.$orders_row['OrderId'].'" date-use="0" /></li>';
						}else{//已使用
							echo '<li class="fixed">{/global.in_use/}</li>';
						}
					}?>
                	<li><strong>{/orders.info.name/}: </strong><span><?=$orders_row['ShippingFirstName'].' '.$orders_row['ShippingLastName'];?></span></li>
                    <li class="clear"></li>
                	<li>
                    	<strong>{/orders.info.address/}: </strong>
                        <span style="width:285px;" title="<?=$orders_row['ShippingAddressLine1'].($orders_row['ShippingAddressLine2']?', '.$orders_row['ShippingAddressLine2']:'').', '.$orders_row['ShippingCity'].', '.$orders_row['ShippingState'].', '.$orders_row['ShippingCountry'].(($orders_row['ShippingCodeOption']&&$orders_row['ShippingTaxCode'])?'#'.$orders_row['ShippingCodeOption'].': '.$orders_row['ShippingTaxCode']:'');?>">
							<?=$orders_row['ShippingAddressLine1'].($orders_row['ShippingAddressLine2']?', '.$orders_row['ShippingAddressLine2']:'');?><br />
                            <?=$orders_row['ShippingCity'].', '.$orders_row['ShippingState'].', '.$orders_row['ShippingCountry'].(($orders_row['ShippingCodeOption']&&$orders_row['ShippingTaxCode'])?'#'.$orders_row['ShippingCodeOption'].': '.$orders_row['ShippingTaxCode']:'');?>
                        </span>
                    </li>
                    <li class="clear"></li>
					<li><strong>{/orders.address.zip/}: </strong><span><?=$orders_row['ShippingZipCode'];?></span></li>
                    <li class="clear"></li>
                	<li><strong>{/orders.info.phone/}: </strong><span><?=$orders_row['ShippingCountryCode'].'-'.$orders_row['ShippingPhoneNumber'];?></span></li>
                    <li class="clear"></li>
                </ul>
				<?php if($paypal_address_row){//Paypal收货地址信息?>
					<ul class="border paypal_address_info">
						<?php if($paypal_address_row['IsUse']==0){//没使用
							echo '<li class="fixed"><input type="button" value="{/global.use/}" class="btn_ok btn_use" date-id="'.$orders_row['OrderId'].'" date-use="1" /></li>';
						}else{//已使用
							echo '<li class="fixed">{/global.in_use/}</li>';
						}?>
						<li><strong>Paypal<b class="fc_red">({/orders.info.paypal_address/})</b>: </strong></li>
						<li><strong>{/orders.info.name/}: </strong><span><?=$paypal_address_row['FirstName'].' '.$paypal_address_row['LastName'];?></span></li>
						<li class="clear"></li>
						<li>
							<strong>{/orders.info.address/}: </strong>
							<span style="width:285px;" title="<?=$paypal_address_row['AddressLine1'].($paypal_address_row['AddressLine2']?', '.$paypal_address_row['AddressLine2']:'').', '.$paypal_address_row['City'].', '.$paypal_address_row['State'].', '.$paypal_address_row['Country'].(($paypal_address_row['CodeOption']&&$paypal_address_row['TaxCode'])?'#'.$paypal_address_row['CodeOption'].': '.$paypal_address_row['TaxCode']:'');?>">
								<?=$paypal_address_row['AddressLine1'].($paypal_address_row['AddressLine2']?', '.$paypal_address_row['AddressLine2']:'');?><br />
								<?=$paypal_address_row['City'].', '.$paypal_address_row['State'].', '.$paypal_address_row['Country'].(($paypal_address_row['CodeOption']&&$paypal_address_row['TaxCode'])?'#'.$paypal_address_row['CodeOption'].': '.$paypal_address_row['TaxCode']:'');?>
							</span>
						</li>
						<li class="clear"></li>
						<li><strong>{/orders.address.zip/}: </strong><span><?=$paypal_address_row['ZipCode'];?></span></li>
						<li class="clear"></li>
						<li><strong>{/orders.info.phone/}: </strong><span><?=$paypal_address_row['CountryCode'].'-'.$paypal_address_row['PhoneNumber'];?></span></li>
						<li class="clear"></li>
					</ul>
				<?php }?>
                <div class="blank6"></div>
                <h4 class="ship_title">
                	{/orders.info.ship_info/}
                    <?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?><a href="javascript:;" class="green edit ship">[{/global.mod/}]</a><?php }?>
                </h4>
                <ul class="shipping_info">
					<li><strong>{/orders.info.weight/}:</strong><span><?=$total_weight;?>{/orders.info.unit/}</span></li>
                    <li class="clear"></li>
					<?php
					if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']=='' && count($oversea_id_ary)>1){ //多个发货地
						foreach($oversea_id_ary as $k=>$v){
					?>
							<li data-name-id="<?=$v;?>"><strong><?=$overseas_ary[$v]['Name'.$c['manage']['web_lang']];?>:</strong><span class="shipping_name"><?=$shipping_ary['ShippingOvExpress'][$v];?></span></li>
							<li class="clear"></li>
							<li data-price-id="<?=$v;?>"><strong>&nbsp;&nbsp;&nbsp;&nbsp;{/orders.info.charges/}: </strong><span class="shipping_price"><?=$Symbol.sprintf('%01.2f', $shipping_ary['ShippingOvPrice'][$v]);?></span><strong>{/orders.info.insurance/}: </strong><span class="shipping_insurance"><?=$Symbol.sprintf('%01.2f', $shipping_ary['ShippingOvInsurancePrice'][$v]);?></span></li>
							<li class="clear"></li>
					<?php
						}
					}else{ //单个发货地
					?>
						<li><strong class="shipping_name"><?=(int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?></strong><span><?=$shipping_row['Brief'];?></span></li>
                    	<li class="clear"></li>
					<?php }?>
					<li><strong>{/orders.info.charges/}: </strong><span class="shipping_price"><?=$Symbol.sprintf('%01.2f', $orders_row['ShippingPrice']);?></span><strong>{/orders.info.insurance/}: </strong><span class="shipping_insurance"><?=$Symbol.sprintf('%01.2f', $orders_row['ShippingInsurancePrice']);?></span></li>
                    <li class="clear"></li>
                    <li><strong>{/orders.payment_method/}:</strong><span><?=$orders_row['PaymentMethod'];?></span></li>
                </ul>
            </div>
            <div class="orderinfo">
                <h4 class="order_title">
                	{/orders.info.order_info/}
					<?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?>
                    	<a href="javascript:;" class="green edit orders">[{/global.mod/}]</a>
						<a href="javascript:;" class="green edit remark_log">[{/orders.remark_log/}{/global.add/}]</a>
					<?php }?>
                </h4>
                <ul>
                	<li>
                    	<strong>{/orders.oid/}:</strong>
                        <span><?=$orders_row['OId'];?></span>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<strong>{/orders.username/}:</strong>
                        <span><?=(int)$orders_row['UserId']?"<a href='./?m=user&a=user&d=base_info&UserId={$orders_row['UserId']}' class='green' target='_blank'>{$orders_row['Email']}</a>":$orders_row['Email'];?>&nbsp;&nbsp;&nbsp;[<?=(int)$orders_row['UserId']?'{/orders.member/}':'{/orders.tourists/}';?>]</span>
                        <div class="clear"></div>
                    </li>
					<?php
					$SalesId=$orders_row['SalesId']?$orders_row['SalesId']:$user_row['SalesId'];
					if($SalesId){
					?>
						<li>
							<strong>{/manage.manage.permit_name.3/}:</strong>
							<span><?=$sales_ary[$SalesId]['UserName'];?></span>
							<div class="clear"></div>
						</li>
					<?php }?>
                	<li>
                    	<strong>{/orders.time/}:</strong>
                        <span><?=date('Y-m-d H:i:s', $orders_row['OrderTime']);?></span>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<strong>{/orders.orders_status/}:</strong>
                        <span id="order_status_module" status="<?=$orders_row['OrderStatus'];?>"><?=$c['orders']['status'][$orders_row['OrderStatus']];?><?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?><a href="javascript:;" class="green edit status">[{/global.mod/}]</a><?php }?></span>
                        <div class="clear"></div>
                    </li>
					<?php if($orders_row['OrderStatus']==7){?>
						<li>
							<strong>{/orders.info.cancel_reason/}:</strong>
							<span><?=$orders_row['CancelReason'];?></span>
							<div class="clear"></div>
						</li>
					<?php }?>
                	<li>
                    	<strong>{/orders.info.product_price/}:</strong>
                        <span id="orders_product_price"><?=$Symbol.sprintf('%01.2f', $orders_row['ProductPrice']);?></span>
                        <div class="clear"></div>
                    </li>
					<?php if($orders_row['Discount']>0){?>
						<li>
							<strong>(-) {/orders.discount/}:</strong>
							<span id="orders_discount"><?=$orders_row['Discount'];?>%</span>
							<div class="clear"></div>
						</li>
					<?php }?>
					<?php if($orders_row['DiscountPrice']>0){?>
						<li>
							<strong>(-) {/orders.discount/}:</strong>
							<span id="orders_discount_price"><?=$Symbol.sprintf('%01.2f', $orders_row['DiscountPrice']);?></span>
							<div class="clear"></div>
						</li>
					<?php }?>
					<li>
                    	<strong>(-) {/orders.user_discount/}:</strong>
                        <span id="orders_user_discount"><?=$orders_row['UserDiscount'];?>%</span>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<strong>{/orders.info.charges_insurance/}:</strong>
                        <span id="orders_shipping_price"><?=$Symbol.sprintf('%01.2f', $orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice']);?></span>
                        <div class="clear"></div>
                    </li>
                    <?php if($HandingFee>0){?>
                        <li>
                            <strong>{/orders.info.handing_fee/}:</strong>
                            <span id="orders_handing_fee"><?=$Symbol.sprintf('%01.2f', $HandingFee);?></span>
                            <div class="clear"></div>
                        </li>
                    <?php }?>
					<?php
					if($orders_row['CouponCode'] && ($orders_row['CouponPrice']>0 || $orders_row['CouponDiscount']>0)){
						if($orders_row['CouponPrice']>0){
							$discount_coupon=$Symbol.sprintf('%01.2f', $orders_row['CouponPrice']);
						}else{
							$discount_coupon=(100-$orders_row['CouponDiscount']*100).'%';
						}
						
					?>
                        <li>
                            <strong>(-) {/orders.info.coupon/}:</strong>
                            <span><em id="orders_coupon"><?=$discount_coupon;?></em>&nbsp;&nbsp;&nbsp;<?=$orders_row['CouponCode'];?></span>
                            <div class="clear"></div>
                        </li>
                    <?php }?>
                	<li>
                    	<strong>{/orders.total_price/}:</strong>
                        <span id="orders_total_price"><?=$Symbol.sprintf('%01.2f', $total_price);?></span>
                        <div class="clear"></div>
                    </li>
					<li>
                        <strong>{/orders.orders/}{/orders.payment.contents/}:</strong>
                        <span><font><?=str::format($orders_row['Note']);?></font></span>
						<div class="clear"></div>
                    </li>
					<?php if($orders_row['OrderStatus']>4 && $orders_row['TrackingNumber']){?>
						<?php if($orders_row['OrderStatus']!=7){?>
							<li>
								<strong>{/orders.shipping.track_no/}:</strong>
								<span id="tracking_number_module"><font><?=$orders_row['TrackingNumber'];?></font><?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?><a href="javascript:;" class="green edit track_no">[{/global.mod/}]</a><?php }?></span>
								<div class="clear"></div>
							</li>
                        <?php }?>
						<li>
							<strong>{/orders.payment.contents/}:</strong>
							<span id="remarks_module"><font><?=$orders_row['Remarks'];?></font><?php if(manage::check_permit('orders', 0, array('a'=>'orders', 'd'=>'edit'))){?><a href="javascript:;" class="green edit remarks">[{/global.mod/}]</a><?php }?></span>
							<div class="clear"></div>
						</li>
					<?php }?>
                </ul>
                <div class="clear"></div>
            </div>
        </div>
        <form name="form_address" class="form_address editAddr" data="<?=htmlspecialchars($address_json_data);?>">
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <tr>
                        <th><label>{/orders.address.name/}</label></th>
                        <td class="recipient">
                            <div><input type="text" name="FirstName" maxlength="32" class="elmbBlur" value="" /><p class="errorInfo"></p></div>
                            <div><input type="text" name="LastName" maxlength="32" class="elmbBlur" /><p class="errorInfo"></p></div>
                        </td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.address_line1/}</label></th>
                        <td><input type="text" name="AddressLine1" maxlength="100" class="elmbBlur" /><p class="errorInfo"></p></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.address_line2/}</label></th>
                        <td><input type="text" name="AddressLine2" maxlength="100" class="elmbBlur" /><p class="errorInfo"></p></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.city/}</label></th>
                        <td><input type="text" name="City" maxlength="30" class="elmbBlur" /><p class="errorInfo"></p></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.country/}</label></th>
                        <td>
                            <select name="country_id" id="country" placeholder="{/orders.address.select_country/}" style="display:none;" class="chzn-done">
                                <option value="-1"></option>
                                <optgroup label="---------">
                                    <?php 
									//$hot_country=str::str_code(db::get_all('country', "IsHot=1", '*', 'Country asc'));
                                    $country_row=str::str_code(db::get_all('country', "1", '*', 'Country asc'));
                                    foreach($country_row as $v){
										if($v['IsHot']!=1) continue;
                                    ?>
                                        <option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
                                    <?php }?>
                                </optgroup>
                                <optgroup label="---------">
                                    <?php 
                                    foreach($country_row as $v){
                                        ?>
                                        <option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
                                    <?php }?>
                                </optgroup>
                            </select>
                            <div id="country_chzn" class="chzn-container chzn-container-single" style="width:310px">
                                <a href="javascript:void(0)" class="chzn-single"><span>{/orders.address.select/}</span><div><b></b></div></a>
                                <div class="chzn-drop" style="left: -9000px; width: 308px;">
                                    <div class="chzn-search clearfix"><input type="text" autocomplete="off" class=""></div>
                                    <ul class="chzn-results">
                                        <li class="group-result active-result">---------</li>
                                        <?php foreach($country_row as $k=>$v){
												if($v['IsHot']!=1) continue;
											?>
                                            <li class="group-option active-result"><?=$v['Country'];?></li>
                                        <?php }?>
                                        <li class="group-result active-result">---------</li>
                                        <?php foreach($country_row as $k=>$v){?>
                                            <li class="group-option active-result"><?=$v['Country'];?></li>
                                        <?php }?>
                                    </ul>
                                </div>
                            </div>
                            <p class="errorInfo"></p>
                        </td>
                    </tr>
                    <tr id="taxCode" style="display: none;">
                        <th><label>{/orders.address.cpf_cnpj/}</label></th>
                        <td>
                            <select name="tax_code_type" class="taxCodeOption" id="taxCodeOption" disabled="">
                                <option value="1" selected="selected">{/orders.address.cpf/}</option>
                                <option value="2">{/orders.address.cnpj/}</option>
                            </select>
                            <input type="text" name="tax_code_value" id="taxCodeValue" maxlength="11" class="taxCodeValue elmbBlur" disabled="" />
                            <p class="errorInfo"></p>
                        </td>
                    </tr>
                    <tr id="tariffCode" style="display: none;">
                        <th><label>{/orders.address.personal_vatid/}</label></th>
                        <td>
                            <select name="tax_code_type" class="tariffCodeOption" id="tariffCodeOption" disabled="">
                                <option value="3" selected="selected">{/orders.address.personal/}</option>
                                <option value="4">{/orders.address.vatid/}</option>
                            </select>
                            <input type="text" name="tax_code_value" id="tariffCodeValue" maxlength="12" class="tariffCodeValue elmbBlur" disabled="" />
                            <p class="errorInfo"></p>
                        </td>
                    </tr>
                    <tr id="zoneId" style="">
                        <th><label>{/orders.address.state/}</label></th>
                        <td>
                            <select name="Province" placeholder="{/orders.address.select/}" class="chzn-done" style="display:none;"><option value="-1"></option></select>
                            <div class="chzn-container chzn-container-single" style="width:310px">
                                <a href="javascript:void(0)" class="chzn-single" tabindex="0"><span>{/orders.address.select/}</span><div><b></b></div></a>
                                <div class="chzn-drop" style="left: -9000px; width: 308px;">
                                    <div class="chzn-search clearfix"><input type="text" autocomplete="off" tabindex="-1" class=""></div>
                                    <ul class="chzn-results"></ul>
                                </div>
                            </div>
                            <p class="errorInfo"></p>
                        </td>
                    </tr>
                    <tr id="state" style="display: none;">
                        <th><label>{/orders.address.state/}</label></th>
                        <td><input type="text" name="State" maxlength="32" class="elmbBlur" disabled="" /></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.zip/}</label></th>
                        <td><input type="text" name="ZipCode" maxlength="10" class="elmbBlur" /><p class="errorInfo"></p></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.address.phone/}</label></th>
                        <td>
                            <input id="countryCode" class="left countryCode" name="CountryCode" type="text" value="+0000" readonly>
                            <div class="left editableSelect hasLayout">
                                <input type="text" name="PhoneNumber" class="phoneNum elmbBlur" maxlength="15" autocomplete="off" />
                                <p class="errorInfo"></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="useAddress" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button> </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <form name="form_shipping" class="form_shipping editAddr" data='<?=$shipping_json_data;?>'>
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
					<?php /*
                    <tr>
                        <th><label>{/orders.shipping.method/}</label></th>
                        <td>
                        	<select name="ShippingMethodSId" tips="{/orders.address.select/}">
                            	<option value="-1">{/orders.address.select/}</option>
                            </select><input type="hidden" name="ShippingMethodType" value="" />
                        </td>
                    </tr>
					*/?>
					<?php
					foreach($oversea_id_ary as $k=>$v){
					?>
						<tr data-id="<?=$v;?>">
							<th><label><?=count($oversea_id_ary)==1?'{/orders.shipping.method/}':$overseas_ary[$v]['Name'.$c['manage']['web_lang']];?></label></th>
							<td>
								<select name="ShippingMethodSId[<?=$v;?>]" class="shipping_method_sid" tips="{/orders.address.select/}">
									<option value="-1">{/orders.address.select/}</option>
								</select><input type="hidden" name="ShippingMethodType[<?=$v;?>]" value="" />
								<div class="clear"></div>&nbsp;{/orders.shipping.insurance/}: <input type="checkbox" name="ShippingInsurance[<?=$v;?>]" value="1" class="input_insurance" />&nbsp;&nbsp;&nbsp;{/orders.info.insurance/}: <span class="shipping_method_insurance"></span>
							</td>
						</tr>
					<?php }?>
					
                    <tr>
                        <th><label>{/orders.shipping.auto_mod_price/}</label></th>
                        <td><input type="checkbox" name="AutoModShippingPrice" value="1" checked="checked" /></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modShipping" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <form name="form_orders" class="form_orders editAddr" data='<?=$price_json_data;?>'>
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <tr>
                        <th><label>{/orders.oid/}</label></th>
                        <td><?=$orders_row['OId'];?></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.info.product_price/}</label></th>
                        <td><?=$Symbol;?> <span><input type="text" name="ProductPrice" maxlength="10" size="5" notnull /></span></td>
                    </tr>
                    <tr class="discount">
                        <th><label>{/orders.discount/}</label></th>
                        <td>- <span><input type="text" name="Discount" maxlength="5" /></span> %<span class="tool_tips_ico" content="{/orders.info.discount_notes/}"></span></td>
                    </tr>
					<tr class="discount_price">
                        <th><label>{/orders.discount/}</label></th>
                        <td>- <?=$c['manage']['currency_symbol'];?> <span><input type="text" name="DiscountPrice" maxlength="10" size="5" /></span></td>
                    </tr>
					<tr>
                        <th><label>{/orders.user_discount/}</label></th>
                        <td>- <span><input type="text" name="UserDiscount" maxlength="5" /></span> %<span class="tool_tips_ico" content="{/orders.info.discount_notes/}"></span></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.info.charges/}</label></th>
                        <td><?=$Symbol;?> <span><input type="text" name="ShippingPrice" maxlength="10" size="5" /></span></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.info.insurance/}</label></th>
                        <td><?=$Symbol;?> <span><input type="text" name="ShippingInsurancePrice" maxlength="5" size="5" /></span></td>
                    </tr>
					<tr class="coupon_discount">
                        <th><label>{/orders.info.coupon/}</label></th>
                        <td>- <span><input type="text" name="CouponDiscount" maxlength="5" /></span> %<span class="tool_tips_ico" content="{/orders.info.discount_notes/}"></span></td>
                    </tr>
					<tr class="coupon_price">
                        <th><label>{/orders.info.coupon/}</label></th>
                        <td>- <?=$c['manage']['currency_symbol'];?> <span><input type="text" name="CouponPrice" maxlength="10" size="5" /></span></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.addfee/}</label></th>
                        <td><input type="text" name="PayAdditionalFee" maxlength="5" /> %<br /><span id="orders_fee_value"></span></td>
                    </tr>
					<tr>
                        <th><label>{/orders.addfee_affix/}</label></th>
                        <td><?=$Symbol;?> <span><input type="text" name="PayAdditionalAffix" maxlength="5" size="5" /></span></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.total_price/}</label></th>
                        <td><?=$Symbol;?> <span id="orders_amount_value"></span></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modOrders" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <form name="form_status" class="form_status editAddr">
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <tr>
                        <th><label>{/orders.orders_status/}</label></th>
                        <td class="fc_red"><input type="radio" name="" value="<?=$orders_row['OrderStatus'];?>" disabled /> <?=$c['orders']['status'][$orders_row['OrderStatus']];?><input type="hidden" name="PassOrderStatus" value="<?=$orders_row['OrderStatus'];?>" /></td>
                    </tr>
                    <?php 
					$s=$c['manage']['mod_order_status'][$orders_row['OrderStatus']];
					foreach((array)$s as $k=>$v){
						?>
                        <tr>
                            <th><label>&nbsp;</label></th>
                            <td><input type="radio" name="OrderStatus" value="<?=$v;?>" <?=$k==0?'checked':'';?> /> <?=$c['orders']['status'][$v];?></td>
                        </tr>
                    <?php }?>
					
					<?php
					if($orders_row['OrderStatus']==4){
						if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']==''){ //多个发货地
							foreach($oversea_id_ary as $k=>$v){
					?>
								<tr>
									<td colspan="2" class="oversea_title"><?=$overseas_ary[$v]['Name'.$c['manage']['web_lang']];?></td>
								</tr>
								<tr>
									<th><label>{/orders.shipping.method/}</label></th>
									<td><?=$shipping_ary['ShippingOvExpress'][$v];?></td>
								</tr>
								<tr>
									<th><label>{/orders.shipping.track_no/}</label></th>
									<td><input type="text" name="OvTrackingNumber[<?=$v;?>]" maxlength="40" size="30" notnull="" /></td>
								</tr>
								<tr>
									<th><label>{/orders.shipping.ship/}{/orders.time/}</label></th>
									<td><input type="text" name="OvShippingTime[<?=$v;?>]" maxlength="10" size="10" readonly value="<?=@date('Y-m-d', $c['time']);?>" class="shipping_time" /></td>
								</tr>
								<tr>
									<th><label>{/orders.payment.contents/}</label></th>
									<td><input type="text" name="OvRemarks[<?=$v;?>]" maxlength="255" size="60" value="<?=$orders_row['Remarks'];?>" /></td>
								</tr>
					<?php
							}
						}else{ //单个发货地
					?>
							<tr>
								<th><label>{/orders.shipping.method/}</label></th>
								<td><?=$orders_row['ShippingExpress'];?></td>
							</tr>
							<tr>
								<th><label>{/orders.shipping.track_no/}</label></th>
								<td><input type="text" name="TrackingNumber" maxlength="40" size="30" notnull="" /></td>
							</tr>
							<tr>
								<th><label>{/orders.shipping.ship/}{/orders.time/}</label></th>
								<td><input type="text" name="ShippingTime" maxlength="10" size="10" readonly value="<?=@date('Y-m-d', $c['time']);?>" class="shipping_time" /></td>
							</tr>
							<tr>
								<th><label>{/orders.payment.contents/}</label></th>
								<td><input type="text" name="Remarks" maxlength="255" size="60" value="<?=$orders_row['Remarks'];?>" /></td>
							</tr>
					<?php
						}
					}?>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modStatus" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
		<form name="form_track_no" class="form_track_no editAddr">
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <?php if($orders_row['OrderStatus']>=4){?>
                    <tr>
                        <th><label>{/orders.shipping.method/}</label></th>
                        <td><?=$orders_row['ShippingExpress'];?></td>
                    </tr>
                    <tr>
                        <th><label>{/orders.shipping.track_no/}</label></th>
                        <td><input type="text" name="TrackingNumber" maxlength="40" size="30" value="<?=$orders_row['TrackingNumber'];?>" notnull="" /></td>
                    </tr>
                    <?php }?>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modTrackNo" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
		<form name="form_remarks" class="form_remarks editAddr">
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <tr>
                        <th><label>{/orders.payment.contents/}:</label></th>
                        <td><textarea name="Remarks" notnull="" style="width:300px; height:100px;"><?=$orders_row['Remarks'];?></textarea></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modRemarks" class="btn_ok" />{/global.save/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
		<form name="form_remark_log" class="form_remark_log editAddr">
        	<input type="hidden" name="OrderId" value="<?=$orders_row['OrderId'];?>" />
            <table class="tb-shippingAddr">
                <tbody>
                    <tr>
                        <th><label>{/orders.payment.contents/}:</label></th>
                        <td><textarea name="Log" notnull="" style="width:300px; height:100px;"></textarea></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td><button type="submit" id="modRemarkLog" class="btn_ok" />{/global.submit/}</button> <button type="button" class="btn_ok cancel" />{/global.cancel/}</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php unset($country_row, $shipping_row, $shipping_cfg);?>
        <?php 
		$IsOnline=(int)db::get_value('payment', "PId='{$orders_row['PId']}'", 'IsOnline');
		$payment_info=db::get_all('orders_payment_info', "OrderId='{$orders_row['OrderId']}'", '*', 'InfoId desc');
		if(!$IsOnline && count($payment_info)){
		?>
            <div class="blank20"></div>
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="orders_payment_info">
                <thead>
                    <tr>
                        <td width="16%" nowrap="nowrap">{/orders.payment.name/}</td>
                        <td width="12%" nowrap="nowrap">{/orders.payment.money/}</td>
                        <td width="12%" nowrap="nowrap">{/orders.payment.mtcn/}</td>
                        <td width="16%" nowrap="nowrap">{/set.country.country/}</td>
                        <td width="30%" nowrap="nowrap">{/orders.payment.contents/}</td>
                        <td width="14%" nowrap="nowrap" class="last">{/global.time/}</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payment_info as $k=>$v){?>
                    <tr>
                        <td><?=$v['FirstName'].' '.$v['LastName'];?></td>
                        <td><?=$v['Currency'].' '.$v['SentMoney'];?></td>
                        <td><?=$v['MTCNNumber'];?></td>
                        <td><?=$v['Country'];?></td>
                        <td><?=$v['Contents'];?></td>
                        <td class="last"><?=$v['AccTime']?date('F d, Y', $v['AccTime']):'N/A';?></td>
                    </tr>
                    <?php }?>
                </tbody>
            </table>
        <?php }?>
		<?php
		$shipped_ary=array();
		if(!$orders_row['TrackingNumber'] && $orders_row['ShippingTime']==0 && !$orders_row['Remarks']){ //多个发货地
			$TrackingNumber=str::json_data(htmlspecialchars_decode($orders_row['OvTrackingNumber']), 'decode');
			$ShippingTime=str::json_data(htmlspecialchars_decode($orders_row['OvShippingTime']), 'decode');
			$Remarks=str::json_data(htmlspecialchars_decode($orders_row['OvRemarks']), 'decode');
			$ShippingSId=str::json_data(htmlspecialchars_decode($orders_row['ShippingOvSId']), 'decode');
			foreach($TrackingNumber as $k=>$v){
				$shipped_ary[]=array(
					'TrackingNumber'	=>	$v,
					'ShippingTime'		=>	$ShippingTime[$k],
					'Remarks'			=>	$Remarks[$k],
					'ShippingSId'		=>	$ShippingSId[$k],
					'Status'			=>	$v['Status']
				);
			}
		}else{ //单个发货地
			$shipped_ary[]=array(
				'TrackingNumber'	=>	$orders_row['TrackingNumber'],
				'ShippingTime'		=>	$orders_row['ShippingTime'],
				'Remarks'			=>	$orders_row['Remarks'],
				'ShippingSId'		=>	$orders_row['ShippingMethodSId'],
				'Status'			=>	($orders_row['TrackingNumber'] && $orders_row['ShippingTime'])?1:0
			);
		}
		$orders_waybill_row=db::get_all('orders_waybill', "OrderId='{$orders_row['OrderId']}'");
		foreach($orders_waybill_row as $v){
			$shipped_ary[]=array(
				'TrackingNumber'	=>	$v['TrackingNumber'],
				'ShippingTime'		=>	$v['ShippingTime'],
				'Remarks'			=>	$v['Remarks'],
				'ShippingSId'		=>	0,
				'Status'			=>	$v['Status']
			);
		}
		if(count($shipped_ary)){
			$c['plugin']=new plugin('api');//插件类(API插件)
		?>
            <div class="blank20"></div>
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="orders_shipped_info">
                <thead>
                    <tr>
                        <td width="10%" nowrap="nowrap">{/orders.shipping.track_no/}</td>
                        <td width="10%" nowrap="nowrap">{/global.status/}</td>
                        <td width="15%" nowrap="nowrap">{/orders.shipping.ship/}{/orders.time/}</td>
                        <td width="15%" nowrap="nowrap"<?=$api_row?'':' class="last"';?>>{/orders.payment.contents/}</td>
						<td width="50%" nowrap="nowrap" class="last">{/orders.shipping.tracking/}</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
					foreach($shipped_ary as $k=>$v){
						$tracking_list=array();
						$api_row=db::get_one('shipping_api', "1 and AId in(select IsAPI from shipping where IsAPI>0 and SId='{$v['ShippingSId']}')");
					?>
                    <tr>
                        <td><?=$v['TrackingNumber'];?></td>
                        <td>{/orders.status.<?=$v['Status']==1?5:4;?>/}</td>
                        <td><?=$v['ShippingTime']?date('F d, Y', $v['ShippingTime']):'N/A';?></td>
                        <td><?=$v['Remarks'];?></td>
						<td class="last">
							<?php
							if($api_row){//API插件（查询发货轨迹）
								$ApiName=strtolower($api_row['Name']);
								$ApiName=='4px' && $ApiName='_4px';
								$Attribute=str::json_data($api_row['Attribute'], 'decode');
								if($c['plugin']->trigger($ApiName, '__config', 'cargo_tracking')=='enable'){//API插件是否存在
									$api_data=array(
										'OrderId'		=>	$orders_row['OrderId'],
										'TrackingNumber'=>	$v['TrackingNumber'],
										'account'		=>	$Attribute
									);
									$return=$c['plugin']->trigger($ApiName, 'cargo_tracking', $api_data);//调用API插件
									if($return){
										$tracking_list=str::json_data($return, 'decode');
										foreach($tracking_list as $v2){
											echo '<p>['.$v2['Date'].'] '.$v2['Address'].' '.$v2['Content'].'</p>';
										}
									}else echo '{/error.no_data/}';
								}else echo '{/error.no_data/}';
							}else echo '{/error.no_data/}';
							?>
						</td>
                    </tr>
                    <?php }?>
                </tbody>
            </table>
        <?php }?>
		<div class="blank20"></div>
        <?php $IsBusiness=(int)db::get_value('config', "GroupId='business' and Variable='IsUsed'", 'Value');?>
		<form id="orders_products_form">
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="orders_products_list">
				<thead>
					<tr>
						<td width="5%" nowrap="nowrap">{/global.serial/}</td>
						<td nowrap="nowrap">{/products.product/}</td>
						<?php if($IsBusiness==1){?><td nowrap="nowrap" style="width:150px;">{/business.business.business/}</td><?php }?>
						<td nowrap="nowrap" style="width:120px;">{/products.products.price/}</td>
						<td nowrap="nowrap" style="width:90px;">{/orders.quantity/}</td>
						<td nowrap="nowrap" style="width:100px;">{/orders.amount/}</td>
						<td nowrap="nowrap" style="width:40px;" class="last">{/global.operation/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
						$i=1;
						$BId='0';
						foreach($order_list_row as $v){$v['Business'] && $BId.=','.$v['Business'];}
						if($IsBusiness==1 && $BId!='0'){
							$business_row=str::str_code(db::get_all('business', "BId in($BId)", 'BId,Name,Url,Phone,Telephone,Address'));
							$BusinessAry=array();
							foreach($business_row as $k=>$v){$BusinessAry[$v['BId']]=$v;}
						}
						
						$amount=$quantity=0;
						foreach($order_list_row as $v){
							$v['Name'.$c['manage']['web_lang']]=$v['Name'];
							$price=$v['Price']+$v['PropertyPrice'];
							$v['Discount']<100 && $price*=$v['Discount']/100;
							$price=(float)substr(sprintf('%01.3f', $price), 0, -1);
							$amount+=($price*$v['Qty']);
					?>
						<tr>
							<td><?=$i++;?></td>
							<td>
								<?php 
								if($v['BuyType']==4){
									$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
									if(!$package_row) continue;
									$attr=array();
									$v['Property']!='' && $attr=str::json_data(str::attr_decode(stripslashes($v['Property'])), 'decode');
									$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
									$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
									$pro_where=='' && $pro_where=0;
									$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
									$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
								?>
									<h4>[ Sales ]</h4>
									<div class="blank6"></div>
									<?php
									foreach((array)$products_row as $k2=>$v2){
										$quantity+=$v['Qty'];
										$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
										$url=ly200::get_url($v2, 'products', $c['manage']['web_lang']);
									?>
									<dl>
										<dt><a href="<?=$url;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$v2['Name'.$c['manage']['web_lang']];?>" /></a></dt>
										<dd<?=$_GET['do']=='print'?' style="padding-right:0;"':'';?>>
											<h4><a href="<?=$url;?>" title="<?=$v2['Name'.$c['manage']['web_lang']]?>" class="green" target="_blank"><?=$v2['Name'.$c['manage']['web_lang']];?></a></h4>
											<?=$v2['Number']!=''?'<p>{/products.products.number/}: '.$v2['Prefix'].$v2['Number'].'</p>':'';?>
											<?=$v2['SKU']!=''?'<p>{/products.products.sku/}: '.$v2['SKU'].'</p>':'';?>
											<?php if($k2==0){?>
												<div>
													<?php
													foreach((array)$attr as $k=>$z){
														if($k=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
														echo '<p class="attr_'.$k.'">'.($k=='Overseas'?'{/shipping.area.ships_from/}':$k).': '.$z.'</p>';
													}
													if((int)$c['manage']['config']['Overseas']==1 && $v['OvId']==1){
														echo '<p class="attr_Overseas">{/shipping.area.ships_from/}: '.$overseas_ary[$v['OvId']]['Name'.$c['manage']['web_lang']].'</p>';
													}?>
												</div>
											<?php }elseif($data_ary[$v2['ProId']]){?>
												<div>
													<?php
													$OvId=0;
													foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
														if($k3=='Overseas'){ //发货地
															$OvId=str_replace('Ov:', '', $v3);
															if((int)$c['manage']['config']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
															echo '<p class="attr_Overseas">{/shipping.area.ships_from/}: '.$overseas_ary[$OvId]['Name'.$c['manage']['web_lang']].'</p>';
														}else{
															echo '<p class="attr_'.$k3.'">'.$attribute_ary[$k3][1].': '.$vid_data_ary[$k3][$v3].'</p>';
														}
													}
													if((int)$c['manage']['config']['Overseas']==1 && $OvId==1){
														echo '<p class="attr_Overseas">{/shipping.area.ships_from/}: '.$overseas_ary[$OvId]['Name'.$c['manage']['web_lang']].'</p>';
													}
													?>
												</div>
											<?php }?>
										</dd>
									</dl>
									<div class="blank6"></div>
									<?php }?>
									<?php if($v['Remark']){?><dl><dd><p class="remark">{/orders.remark/}: <?=$v['Remark'];?></p></dd></dl><?php }?>
								<?php 
								}else{
									$attr=str::json_data(str::attr_decode(stripslashes($v['Property'])), 'decode');
									!$attr && $attr=str::json_data($v['Property'], 'decode');
									$url=ly200::get_url($v, 'products', $c['manage']['web_lang']);
									$quantity+=$v['Qty'];
									$SKU=$v['OrderSKU']?$v['OrderSKU']:$v['SKU'];
									$img=@is_file($c['root_path'].$v['PicPath'])?$v['PicPath']:ly200::get_size_img($v['PicPath_0'], '240x240');
								?>
									<dl>
										<dt><a href="<?=$url;?>" target="_blank"><img src="<?=$img;?>" title="<?=$v['Name']?>" /></a></dt>
										<dd<?=$_GET['do']=='print'?' style="padding-right:0;"':'';?>>
											<h4><a href="<?=$url;?>" title="<?=$v['Name']?>" class="green" target="_blank"><?=$v['Name'];?></a></h4>
											<?php
											echo $v['Number']!=''?'<p>{/products.products.number/}: '.$v['Prefix'].$v['Number'].'</p>':'';
											echo $SKU!=''?'<p>{/products.products.sku/}: '.$SKU.'</p>':'';
											foreach((array)$attr as $k=>$vv){
												if($k=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
												echo '<p>'.($k=='Overseas'?'{/shipping.area.ships_from/}':$k).': '.$vv.'</p>';
											}
											if((int)$c['manage']['config']['Overseas']==1 && $v['OvId']==1){
												echo '<p>{/shipping.area.ships_from/}: '.$overseas_ary[$v['OvId']]['Name'.$c['manage']['web_lang']].'</p>';
											}
											echo $v['Remark']?'<p>{/orders.remark/}: '.$v['Remark'].'</p>':'';
											?>
										</dd>
									</dl>
								<?php }?>
							</td>
							<?php if($IsBusiness==1){
								$jsonData=str::json_data($BusinessAry[$v['Business']]);
								?>
								<td class="business" data="<?=$jsonData;?>" style="line-height:21px;">
									<?php if($v['Business']){?>
										<span>
											<a<?=$BusinessAry[$v['Business']]['Url']?' href="'.$BusinessAry[$v['Business']]['Url'].'" target="_blank"':' href="javascript:;"';?>><?=$BusinessAry[$v['Business']]['Name'];?></a><br />
											<div class="blank12"></div>
											{/user.reg_set.Phone/}: <?=$BusinessAry[$v['Business']]['Phone'];?><br />
											{/user.reg_set.Telephone/}: <?=$BusinessAry[$v['Business']]['Telephone'];?><br />
											{/user.reg_set.Address/}: <?=$BusinessAry[$v['Business']]['Address'];?>
										</span>
									<?php }?>
								</td>
							<?php }?>
							<td><span class="price_input"><b><?=$Symbol;?><div class="arrow"><em></em><i></i></div></b><input name="Price[]" value="<?=sprintf('%01.2f', $price);?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" notnull /></span></td>
							<td><input type="text" name="Qty[]" value="<?=$v['Qty'];?>" size="4" maxlength="8" class="part_input" notnull /><input type="hidden" name="LId[]" value="<?=$v['LId'];?>" /></td>
							<td><?=$Symbol.($price*$v['Qty']);?></td>
							<td class="last"><?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=orders.orders_prod_del&LId=<?=$v['LId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?></td>
						</tr>
					<?php }?>
					<tr>
						<td colspan="<?=$IsBusiness==1?4:3;?>">&nbsp;</td>
						<td><?=$quantity;?></td>
						<td><?=$Symbol.sprintf('%01.2f', $amount);?></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="<?=$IsBusiness==1?5:4;?>"></td>
						<td colspan="2"><input type="button" class="btn_ok btn_prod_mod" value="{/global.mod/}{/module.products.module_name/}" /></td>
					</tr>
				</tfoot>
			</table>
			<input type="hidden" name="OrderId" value="<?=$OrderId;?>" />
			<input type="hidden" name="do_action" value="orders.orders_prod_edit_edit" />
			<input type="hidden" id="back_action" value="?m=orders&a=orders&d=view&OrderId=<?=$OrderId;?>" />
		</form>
		<div class="blank20"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="orders_products_list">
			<thead>
				<tr>
					<td width="15%" nowrap="nowrap">{/global.date/}</td>
					<td width="15%" nowrap="nowrap">{/global.time/}</td>
					<td width="15%" nowrap="nowrap">{/orders.orders_status/}</td>
					<td width="15%" nowrap="nowrap">{/orders.operator/}</td>
					<td width="40%" nowrap="nowrap" class="last">{/global.depict/}</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach($orders_log_row as $k=>$v){?>
				<tr>
					<td><?=$v['AccTime']?date('F d, Y', $v['AccTime']):'N/A';?></td>
					<td><?=$v['AccTime']?date('h:i A', $v['AccTime']):'N/A';?></td>
					<td><?=$c['orders']['status'][$v['OrderStatus']];?></td>
					<td>
						<?php
						if($v['UserId'] && $v['IsAdmin']){
							echo '({/manage.manage.manager/})';
						/*}elseif($v['UserId'] && !$v['IsAdmin']){
							echo '({/orders.user/})';//'({/module.user.module_name/})';*/
						}else{
							echo '({/orders.user/})';//'({/user.tourist/})';
						}
						echo '&nbsp;'.$v['UserName'];
						?>
					</td>
					<td class="last"><?=$v['Log'];?></td>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div class="blank20"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="orders_remark_log_list">
			<thead>
				<tr>
					<td width="15%" nowrap="nowrap">{/global.date/}</td>
					<td width="15%" nowrap="nowrap">{/global.time/}</td>
					<td width="70%" nowrap="nowrap" class="last">{/orders.payment.contents/}</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach($orders_remark_log_row as $k=>$v){?>
				<tr>
					<td><?=$v['AccTime']?date('F d, Y', $v['AccTime']):'N/A';?></td>
					<td><?=$v['AccTime']?date('h:i A', $v['AccTime']):'N/A';?></td>
					<td class="last"><?=$v['Log'];?></td>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div class="blank20"></div>
    <?php
		if($_GET['do']=='print'){
			echo '<script>window.print();</script>';
		}
		//销毁变量
		unset($orders_row, $order_list_row);
	}elseif($c['manage']['do']=='print'){
		$Type=(int)$_GET['Type'];
		$print_row=str::str_code(db::get_all('config', "GroupId='print'"));
		$print_ary=array();
		foreach($print_row as $v){
			$print_ary[$v['Variable']]=$v['Value'];
		}
		$OrderId=(int)$_GET['OrderId'];
		$query_string=urldecode($_GET['query_string']);
		$orders_row=str::str_code(db::get_one('orders', "OrderId='$OrderId'"));
		$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$OrderId'", 'o.*, p.PicPath_0, p.Prefix, p.Number', 'o.LId asc');
		$sum_row=db::get_one('orders_products_list', "OrderId='$OrderId'", "sum(Qty) as TotalQty");
		
		$paypal_address_row=str::str_code(db::get_one('orders_paypal_address_book', "OrderId='{$orders_row['OrderId']}' and IsUse=1"));
		if($paypal_address_row){//Paypal账号收货地址
			$shipto_ary=array(
				'FirstName'			=>	$paypal_address_row['FirstName'],
				'LastName'			=>	$paypal_address_row['LastName'],
				'AddressLine1'		=>	$paypal_address_row['AddressLine1'],
				'AddressLine2'		=>	'',
				'City'				=>	$paypal_address_row['City'],
				'State'				=>	$paypal_address_row['State'],
				'SId'				=>	0,
				'Country'			=>	$paypal_address_row['Country'],
				'CId'				=>	0,
				'ZipCode'			=>	$paypal_address_row['ZipCode'],
				'CodeOption'		=>	'',
				'CodeOptionId'		=>	0,
				'TaxCode'			=>	'',
				'CountryCode'		=>	$paypal_address_row['CountryCode'],
				'PhoneNumber'		=>	$paypal_address_row['PhoneNumber']
			);
		}else{//会员账号收货地址
			$shipto_ary=array(
				'FirstName'			=>	$orders_row['ShippingFirstName'],
				'LastName'			=>	$orders_row['ShippingLastName'],
				'AddressLine1'		=>	$orders_row['ShippingAddressLine1'],
				'AddressLine2'		=>	$orders_row['ShippingAddressLine2'],
				'City'				=>	$orders_row['ShippingCity'],
				'State'				=>	$orders_row['ShippingState'],
				'SId'				=>	$orders_row['ShippingSId'],
				'Country'			=>	$orders_row['ShippingCountry'],
				'CId'				=>	$orders_row['ShippingCId'],
				'ZipCode'			=>	$orders_row['ShippingZipCode'],
				'CodeOption'		=>	$orders_row['ShippingCodeOption'],
				'CodeOptionId'		=>	$orders_row['ShippingCodeOptionId'],
				'TaxCode'			=>	$orders_row['ShippingTaxCode'],
				'CountryCode'		=>	$orders_row['ShippingCountryCode'],
				'PhoneNumber'		=>	$orders_row['ShippingPhoneNumber']
			);
		}
	?>
		<script language="javascript">
		$(document).ready(function(){
			var head='<html><head><title></title></head><body><object classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2" height="0" id="WebBrowser3" width="0" viewastext></object>';
			var foot='</body></html>';
			var content=$('#orders_print').html();
			document.body.innerHTML=head+content+foot;
			window.print();
		});</script>
		<div id="orders_print">
			<?php if($Type==0){//报关单?>
				<table class="print_table" width="100%" border="1" align="center" cellpadding="6" cellspacing="0">
					<tr>
						<td colspan="14" align="center">中华人民共和国海关出口货物报关单</td>
					</tr>
					<tr>
						<td colspan="10"></td>
						<td colspan="4" rowspan="2"><?=$orders_row['OId'];?></td>
					</tr>
					<tr>
						<td colspan="2">预录入编号：</td>
						<td colspan="3"></td>
						<td colspan="3">海关编号：</td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<td colspan="3" class="bd_b0">出口口岸</td>
						<td colspan="5" class="bd_b0">备案号</td>
						<td colspan="4" class="bd_b0">出口日期</td>
						<td colspan="2" class="bd_b0">申报日期</td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="5"></td>
						<td colspan="4"></td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<td colspan="3" class="bd_b0">经营单位</td>
						<td colspan="2" class="bd_b0">运输方式</td>
						<td colspan="4" class="bd_b0">运输工具名称</td>
						<td colspan="5" class="bd_b0">提运单号</td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="2"></td>
						<td colspan="4"></td>
						<td colspan="5"></td>
					</tr>
					<tr>
						<td colspan="3" class="bd_b0">发货单位</td>
						<td colspan="4" class="bd_b0">贸易方式</td>
						<td colspan="5" class="bd_b0">征免性质</td>
						<td colspan="2" class="bd_b0">结汇方式</td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="4"></td>
						<td colspan="5"></td>
						<td colspan="2"></td>
					</tr>
					<tr>
						<td colspan="2" class="bd_b0">许可证号</td>
						<td colspan="4" class="bd_b0">运抵国(地区)</td>
						<td colspan="4" class="bd_b0">指运港</td>
						<td colspan="4" class="bd_b0">境内货源地</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="4"><?=$orders_row['ShippingCountry'];?></td>
						<td colspan="4"></td>
						<td colspan="4"></td>
					</tr>
					<tr>
						<td colspan="2" class="bd_b0">批准文号</td>
						<td colspan="2" class="bd_b0">成交方式</td>
						<td colspan="3" class="bd_b0">运费</td>
						<td colspan="3" class="bd_b0">保费</td>
						<td colspan="4" class="bd_b0">杂费</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="2"></td>
						<td colspan="3"></td>
						<td colspan="3"></td>
						<td colspan="4"></td>
					</tr>
					<tr>
						<td colspan="2" class="bd_b0">合同协议号</td>
						<td colspan="2" class="bd_b0">件数</td>
						<td colspan="3" class="bd_b0">包装种类</td>
						<td colspan="4" class="bd_b0">毛重(公斤)</td>
						<td colspan="3" class="bd_b0">净重(公斤)</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="2"><?=$sum_row['TotalQty'];?></td>
						<td colspan="3"></td>
						<td colspan="4"></td>
						<td colspan="3"><?=$orders_row['TotalWeight'];?></td>
					</tr>
					<tr>
						<td colspan="2" class="bd_b0">集装箱号</td>
						<td colspan="8" class="bd_b0">随附单据</td>
						<td colspan="4" class="bd_b0">生产厂家</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="8"></td>
						<td colspan="4"></td>
					</tr>
					<tr>
						<td colspan="14" class="bd_b0">标记唛码及备注</td>
					</tr>
					<tr>
						<td colspan="14" class="h40 bd_t0"></td>
					</tr>
					<tr>
						<td class="bd_b0" align="center">项号 商品编号</td>
						<td colspan="2" class="bd_b0" align="center">商品名称、规格型号</td>
						<td colspan="2" class="bd_b0" align="center">数量及单位</td>
						<td colspan="5" class="bd_b0" align="center">最终目的国(地区)单价</td>
						<td colspan="3" class="bd_b0" align="center">总价</td>
						<td class="bd_b0" align="center">币制 征免</td>
					</tr>
					<?php
					$len=count($order_list_row);
					if($len){
						foreach($order_list_row as $k=>$v){
							$attr=str::json_data($v['Property'], 'decode');
							$url="/?a=goods&ProId={$v['ProId']}";
							$border=$k<$len?' class="bd_b0"':'';
					?>
					<tr>
						<td<?=$border;?>><?=$v['Prefix'].$v['Number'];?></td>
						<td colspan="2"<?=$border;?>><?=$v['Name'];?></td>
						<td colspan="2"<?=$border;?>><?=$v['Qty'];?></td>
						<td colspan="5"<?=$border;?>><?=$orders_row['ShippingCountry'].' '.$Symbol.sprintf('%01.2f', $v['PropertyPrice']);?></td>
						<td colspan="3"<?=$border;?>><?=$Symbol.sprintf('%01.2f', ($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty']);?></td>
						<td<?=$border;?>><?=$Symbol;?></td>
					</tr>
					<?php
						}
					}else{?>
					<tr>
						<td></td>
						<td colspan="2"></td>
						<td colspan="2"></td>
						<td colspan="5"></td>
						<td colspan="3"></td>
						<td></td>
					</tr>
					<?php }?>
					<tr>
						<td colspan="14" class="bd_b0">税费征收情况</td>
					</tr>
					<tr>
						<td colspan="14" class="h100 bd_t0"></td>
					</tr>
					<tr>
						<td class="bd_b0">录入员</td>
						<td class="bd_b0">录入单位</td>
						<td colspan="6" class="bd_b0">兹声明以上申报无讹并承担法律责任</td>
						<td colspan="6" class="bd_b0">海关审单批注及放行日期(签章)</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td colspan="6" class="bd_t0"></td>
						<td colspan="6" class="bd_t0"></td>
					</tr>
					<tr>
						<td colspan="8" class="bd_b0">报关员</td>
						<td colspan="3" class="bd_b0">审单</td>
						<td colspan="3" class="bd_b0">审价</td>
					</tr>
					<tr>
						<td colspan="8" class="bd_t0"></td>
						<td colspan="3" class="bd_t0"></td>
						<td colspan="3" class="bd_t0"></td>
					</tr>
					<tr>
						<td colspan="2" class="bd_b0">单位地址</td>
						<td colspan="6" class="bd_b0">申报单位(签章)</td>
						<td colspan="3" class="bd_b0">征税</td>
						<td colspan="3" class="bd_b0">统计</td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="6" class="bd_t0"></td>
						<td colspan="3" class="bd_t0"></td>
						<td colspan="3" class="bd_t0"></td>
					</tr>
					<tr>
						<td class="bd_b0">邮编</td>
						<td class="bd_b0">电话</td>
						<td colspan="6" class="bd_b0">填制日期</td>
						<td colspan="3" class="bd_b0">查验</td>
						<td colspan="3" class="bd_b0">放行</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
						<td colspan="6"></td>
						<td colspan="3" class="bd_t0"></td>
						<td colspan="3" class="bd_t0"></td>
					</tr>
					<tr>
						<th width="10%"></th>
						<th width="12.5%"></th>
						<th width="10%"></th>
						<th width="7.5%"></th>
						<th width="5%"></th>
						<th width="2.5%"></th>
						<th width="10%"></th>
						<th width="2.5%"></th>
						<th width="10%"></th>
						<th width="7.5%"></th>
						<th width="2.5%"></th>
						<th width="2.5%"></th>
						<th width="10%"></th>
						<th width="7.5%"></th>
					</tr>
				</table>
			<?php
			}else{//发票单
				$printLogo=db::get_value('config', "GroupId='print' and Variable='LogoPath'", 'Value');
			?>
				<?php if(is_file($c['root_path'].$printLogo)){?><div class="logo"><img src="<?=$printLogo;?>" /></div><?php }?>
				<table class="print_table" width="612" border="1" align="center" cellpadding="6" cellspacing="0" style="margin:0 auto;">
					<tr>
						<td colspan="5"><?=$print_ary['Compeny'];?></td>
						<td colspan="2" class="bd_b0">NO.:<?=$orders_row['OId'];?></td>
					</tr>
					<tr>
						<td colspan="5" class="bd_b0"><?=$print_ary['Address'];?></td>
						<td class="bd_b0">Date:</td>
						<td class="bd_b0"><?=@date('m/d/Y', $orders_row['OrderTime']);?></td>
					</tr>
					<tr>
						<td colspan="7" class="bd_b0">Email: <?=$print_ary['Email'];?>&nbsp;&nbsp;&nbsp;Tel: <?=$print_ary['Telephone'];?>&nbsp;&nbsp;&nbsp;Fax: <?=$print_ary['Fax'];?></td>
						<?php /*?><td class="bd_b0">HB/L NO:</td>
						<td class="bd_b0">JHA20131638</td><?php */?>
					</tr>
					<tr><td colspan="7" height="10">Ship Information</td></tr>
					<tr>
						<td class="bd_b0">Name:</td>
						<td colspan="3" class="bd_b0"><?=$shipto_ary['FirstName'].' '.$shipto_ary['LastName'];?></td>
						<td>TEL:</td>
						<td colspan="2"><?=$shipto_ary['CountryCode'].'-'.$shipto_ary['PhoneNumber'];?></td>
					</tr>
					<?php /*?><tr>
						<td class="bd_b0">ATTN: </td>
						<td colspan="6" class="bd_b0"></td>
					</tr><?php */?>
					<tr>
						<td>ADDR:</td>
						<td colspan="6"><?=$shipto_ary['AddressLine1'].($shipto_ary['AddressLine2']?', '.$shipto_ary['AddressLine2']:'').', '.$shipto_ary['City'].', '.$shipto_ary['State'].', '.$shipto_ary['ZipCode'].', '.$shipto_ary['Country'].(($shipto_ary['CodeOption']&&$shipto_ary['TaxCode'])?'#'.$orders_row['shipto_ary'].': '.$shipto_ary['TaxCode']:'');?></td>
					</tr>
					<?php /*?><tr>
						<td class="bd_b0">Destionation:</td>
						<td colspan="3" class="bd_b0"><?=$orders_row['ShippingCountry'];?></td>
						<td>Total:</td>
						<td colspan="2"><?=$Symbol.sprintf('%01.2f', orders::orders_price($orders_row, 0, 1));?></td>
					</tr><?php */?>
					<tr><td colspan="7" height="10"></td></tr>
					<tr>
						<td colspan="4" class="bd_b0" align="center">Item</td>
						<td class="bd_b0" align="center">Price</td>
						<td class="bd_b0" align="center">Quantity</td>
						<td class="bd_b0" align="center">Amount</td>
					</tr>
					<?php
					$total=0;
					$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='$OrderId'", 'o.*,p.PicPath_0,p.Number,p.Prefix', 'o.LId asc');
					$len=count($order_list_row);
					if($len){
						foreach($order_list_row as $k=>$v){
							$attr=str::json_data($v['Property'], 'decode');
							$url="/?a=goods&ProId={$v['ProId']}";
							$border=$k<$len?' class="bd_b0"':'';
							$total+=($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1)*$v['Qty'];
							$name=$v['Name'];
							$attr=str::json_data($v['Property'], 'decode');
							$SKU=$v['SKU'];
					?>
					<tr>
						<td colspan="4" class="bd_b0">
							<dl>
								<dt><img src="<?=$v['PicPath'];?>" title="<?=$name;?>" alt="<?=$name;?>"></dt>
								<dd>
									<h4><?=$name;?></h4>
                                    <?=$v['Number']!=''?'<p>Item No. '.$v['Prefix'].$v['Number'].'</p>':'';?>
									<?=$SKU!=''?'<p class="pro_attr">{/products.products.sku/}: '.$SKU.'</p>':'';?>
									<?php
									foreach((array)$attr as $k=>$v2){
										if($k=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
										echo '<p class="pro_attr">'.($k=='Overseas'?'{/shipping.area.ships_from/}':$k).': '.$v2.'</p>';
									}
									if((int)$c['manage']['config']['Overseas']==1 && $v['OvId']==1){
										echo '<p class="pro_attr">{/shipping.area.ships_from/}: '.$overseas_ary[$v['OvId']]['Name'.$c['manage']['web_lang']].'</p>';
									}
									?>
									<p><?=$v['Remark'];?></p>
								</dd>
							</dl>
						</td>
						<td class="bd_b0"><?=$Symbol.sprintf('%01.2f', $v['Price']+$v['PropertyPrice']);?></td>
						<td class="bd_b0"><?=$v['Qty']?></td>
						<td class="bd_b0"><?=$Symbol.sprintf('%01.2f', ($v['Price']+$v['PropertyPrice'])*$v['Qty']);?></td>
					</tr>
					<?php
						}
					}else{?>
					<tr>
						<td colspan="4" class="bd_b0"></td>
						<td class="bd_b0"></td>
						<td class="bd_b0"></td>
						<td class="bd_b0"></td>
					</tr>
					<?php }?>
					<tr>
						<td colspan="5"></td>
						<td>Subtotal:</td>
						<td><?=$Symbol.sprintf('%01.2f', $total);?></td>
					</tr>
					<?php /*?><tr>
						<td>ISSUED BY</td>
						<td>MKTG_09</td>
						<td>xxx</td>
						<td>xxx</td>
						<td>APPROVED BY</td>
						<td>xxx</td>
						<td>xxx</td>
					</tr><?php */?>
					<tr>
						<td colspan="7">"All transactions are subject to the Company＇s Standard Trading Conditions (copy is available upon request), which in certain circumstances, limit or exempt the Company＇s liability."</td>
					</tr>
					<?php /*?><tr>
						<td colspan="7">We will not take the Bank handling fee. The payment will match the Debit Note＇s amount. If not we have the rights not to realease the B/L.</td>
					</tr><?php */?>
					<tr>
						<th width="15%"></th>
						<th width="15%"></th>
						<th width="15%"></th>
						<th width="15%"></th>
						<th width="12%"></th>
						<th width="13%"></th>
						<th width="15%"></th>
					</tr>
				</table>
			<?php }?>
		</div>
	<?php }?>
</div>