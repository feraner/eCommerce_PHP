<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'waybill'));//检查权限

$all_currency_ary=array();
$currency_row=db::get_all('currency', '1', 'Currency, Symbol');
foreach($currency_row as $k=>$v){
	$all_currency_ary[$v['Currency']]=$v;
}

//发货地
$overseas_ary=array();
$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
foreach($overseas_row as $v){
	$overseas_ary[$v['OvId']]=$v;
}

$permit_ary=array(
	'edit'	=>	manage::check_permit('orders', 0, array('a'=>'waybill', 'd'=>'edit'))
);
?>
<div class="r_nav">
	<h1>{/module.orders.waybill/}</h1>
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
	?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="<?=$_GET['Keyword'];?>" class="form_input" size="15" autocomplete="off" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="clear"></div>
				<input type="hidden" name="m" value="orders" />
				<input type="hidden" name="a" value="waybill" />
			</form>
		</div>
	<?php }?>
</div>
<div id="orders" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$OrderStatus=(int)$_GET['OrderStatus'];//订单状态（搜索）
		$query_string=ly200::query_string('OrderStatus');
	?>
		<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
    	<script type="text/javascript">$(document).ready(function(){orders_obj.waybill_init()});</script>
		<div class="r_con_column">
			<dl class="orders_status_list">
				<dd><a href="./?m=orders&a=waybill" status="0"<?=$OrderStatus==0?' class="current"':'';?>>All</a></dd>
				<?php
				foreach($c['orders']['status'] as $k=>$v){
					if($k<4 || $k==7) continue;
				?>
					<dt></dt><dd><a href="./?OrderStatus=<?=$k;?>&<?=$query_string;?>" status="<?=$k;?>"<?=$OrderStatus==$k?' class="current"':'';?>><?=$c['orders']['status'][$k];?></a></dd>
				<?php }?>
			</dl>
		</div>
		<div id="waybill_box">
			<?php
			$Keyword=str::str_code($_GET['Keyword']);
			$page_count=10;//显示数量
			$where='o.OrderStatus>3 and o.OrderStatus<7';//条件
			$OrderStatus && $where.=" and o.OrderStatus='$OrderStatus'";
			(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and ((o.SalesId>0 and o.SalesId='{$_SESSION['Manage']['UserId']}') or (o.SalesId=0 and u.SalesId='{$_SESSION['Manage']['UserId']}'))";//业务员账号过滤
			if($Keyword){ //搜索模式
				$where.=" and (o.OId like '%$Keyword%' or concat(o.OId, '', w.Number) like '%$Keyword%' or o.TrackingNumber like '%$Keyword%' or w.TrackingNumber like '%$Keyword%')";
				//$orders_row=str::str_code(db::get_limit_page('orders o left join orders_waybill w on o.OrderId=w.OrderId', $where." group by o.OrderId", "o.*, concat(o.OId, '', w.Number) as Num, w.WId , w.Number, w.ProInfo, w.Status, w.TrackingNumber as WTrackingNumber, w.ShippingTime as WShippingTime, w.Remarks as WRemarks", 'o.OrderId desc', (int)$_GET['page'], $page_count));
				$orders_row=str::str_code(db::get_limit_page('orders o left join orders_waybill w on o.OrderId=w.OrderId left join user u on o.UserId=u.UserId', $where." group by o.OrderId", "o.*, concat(o.OId, '', w.Number) as Num, w.WId , w.Number, w.ProInfo, w.Status, w.TrackingNumber as WTrackingNumber, w.ShippingTime as WShippingTime, w.Remarks as WRemarks, u.SalesId", 'o.OrderId desc', (int)$_GET['page'], $page_count));
			}else{ //全部显示
				$orders_row=str::str_code(db::get_limit_page('orders o left join user u on o.UserId=u.UserId', $where, 'o.*, u.SalesId', 'o.OrderId desc', (int)$_GET['page'], $page_count));
			}
			$query_string=ly200::query_string(array('m', 'a', 'd'));
			$i=1;
			
			$orderid_where='0';
			foreach($orders_row[0] as $v){ $orderid_where.=','.$v['OrderId']; }
			$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId in($orderid_where)", 'o.*, o.SKU as OrderSKU, p.Prefix, p.Number, p.SKU, p.PicPath_0', 'o.OvId asc, o.LId asc');
			$BId='0';
			$pro_ary=$order_list_ary=$pro_qty_ary=$waybill_ary=$OvId_ary=array();
			foreach($order_list_row as $v){
				$pro_ary[$v['LId']]=$v; //订单产品信息
				$order_list_ary[$v['OrderId']][$v['OvId']]['00'][]=$pro_ary[$v['LId']]; //总信息
				$pro_qty_ary[$v['LId']]=$v['Qty'];
				$OvId_ary[$v['OrderId']]=$v['OvId'];
			}
			$orders_waybill_row=db::get_all('orders_waybill', "OrderId in($orderid_where)");
			foreach($orders_waybill_row as $v){
				$i=0;
				$waybill_ary[$v['OrderId']][$v['Number']]=$v;
				$ProInfo=str::json_data(htmlspecialchars_decode($v['ProInfo']), 'decode');
				foreach($ProInfo as $k2=>$v2){ //$k2==LId $v2==QTY
					$ovid=$pro_ary[$k2]['OvId'];
					$order_list_ary[$v['OrderId']][$ovid][$v['Number']][$i]=$pro_ary[$k2];
					$order_list_ary[$v['OrderId']][$ovid][$v['Number']][$i]['Qty']=$v2;
					$pro_qty_ary[$k2]-=$v2;
					++$i;
				}
			}
			$orders_ary=array();
			foreach($orders_row[0] as $v){
				$orders_ary[$v['OrderId']]=$v;
				//发货信息
				if(!$v['TrackingNumber'] && $v['ShippingTime']==0 && !$v['Remarks']){ //多个发货地
					$ship_ary[$v['OrderId']]=array(
						'ShippingExpress'		=>	str::json_data(htmlspecialchars_decode($v['ShippingOvExpress']), 'decode'),
						'ShippingMethodSId'		=>	str::json_data(htmlspecialchars_decode($v['ShippingOvSId']), 'decode'),
						'ShippingType'			=>	str::json_data(htmlspecialchars_decode($v['ShippingOvType']), 'decode'),
						'ShippingInsurance'		=>	str::json_data(htmlspecialchars_decode($v['ShippingOvInsurance']), 'decode'),
						'ShippingPrice'			=>	str::json_data(htmlspecialchars_decode($v['ShippingOvPrice']), 'decode'),
						'ShippingInsurancePrice'=>	str::json_data(htmlspecialchars_decode($v['ShippingOvInsurancePrice']), 'decode'),
						'TrackingNumber'		=>	str::json_data(htmlspecialchars_decode($v['OvTrackingNumber']), 'decode'),
						'ShippingTime'			=>	str::json_data(htmlspecialchars_decode($v['OvShippingTime']), 'decode'),
						'Remarks'				=>	str::json_data(htmlspecialchars_decode($v['OvRemarks']), 'decode')
					);
				}else{ //单个发货地
					$OvId=$OvId_ary[$v['OrderId']];
					$ship_ary[$v['OrderId']]=array(
						'ShippingExpress'		=>	array($OvId=>$v['ShippingExpress']),
						'ShippingMethodSId'		=>	array($OvId=>(int)$v['ShippingMethodSId']),
						'ShippingType'			=>	array($OvId=>$v['ShippingType']),
						'ShippingInsurance'		=>	array($OvId=>(int)$v['ShippingInsurance']),
						'ShippingPrice'			=>	array($OvId=>(float)$v['ShippingPrice']),
						'ShippingInsurancePrice'=>	array($OvId=>(float)$v['ShippingInsurancePrice']),
						'TrackingNumber'		=>	array($OvId=>$v['TrackingNumber']),
						'ShippingTime'			=>	array($OvId=>$v['ShippingTime']),
						'Remarks'				=>	array($OvId=>$v['Remarks'])
					);
				}
			}
			krsort($order_list_ary);//倒序
			/******************************************* 产品列表 Start *******************************************/
			foreach((array)$order_list_ary as $OrderId=>$obj){
				foreach($obj as $OvId=>$row){
			?>
					<div class="waybill_products_list">
						<?php
						foreach($row as $key=>$val){
							$amount=$quantity=$is_edit=0;
						?>
							<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" data-id="<?=$OvId;?>" number="<?=$key;?>" orderid="<?=$OrderId;?>">
								<thead>
									<tr>
										<td nowrap="nowrap" colspan="7" class="ship_info">
											<h2><?=$orders_ary[$OrderId]['OId'].($key!='00'?$key:'');?></h2>
											<h3>{/shipping.area.ships_from/}: <?=$overseas_ary[$OvId]['Name'.$c['manage']['web_lang']];?></h3>
											<?php if($key=='00' && $ship_ary[$OrderId]['TrackingNumber'][$OvId]){?>
												<p class="show">{/orders.shipping.track_no/}: <?=$ship_ary[$OrderId]['TrackingNumber'][$OvId];?></p>
											<?php }elseif($key!='00' && $waybill_ary[$OrderId][$key]['TrackingNumber']){?>
												<p class="show">{/orders.shipping.track_no/}: <?=$waybill_ary[$OrderId][$key]['TrackingNumber'];?></p>
											<?php }else{?>
												<p>{/orders.shipping.track_no/}: <input type="text" name="TrackingNumber" maxlength="40" size="18" notnull />&nbsp;&nbsp;&nbsp;&nbsp;{/orders.shipping.ship/}{/orders.time/}: <input type="text" name="ShippingTime" maxlength="10" size="10" readonly value="<?=@date('Y-m-d', $c['time']);?>" />&nbsp;&nbsp;&nbsp;&nbsp;{/orders.payment.contents/}: <input type="text" name="Remarks" maxlength="255" size="30" value="" /><input type="button" class="btn_ok shipped_submit_btn" value="{/global.submit/}" /></p>
											<?php $is_edit=1; }?>
										</td>
									</tr>
									<tr>
										<td width="5%" nowrap="nowrap">{/global.serial/}</td>
										<td width="40%" nowrap="nowrap">{/products.product/}</td>
										<td width="10%" nowrap="nowrap">{/products.products.price/}</td>
										<?php if($is_edit){?><td width="10%" nowrap="nowrap">{/orders.product.waybill_qty/}</td><?php }?>
										<td width="10%" nowrap="nowrap">{/orders.quantity/}</td>
										<td width="10%" nowrap="nowrap" class="last">{/orders.amount/}</td>
									</tr>
								</thead>
								<tbody>
									<?php
									$i=1;
									foreach($val as $k=>$v){
										if($key=='00'){
											$qty=(int)$pro_qty_ary[$v['LId']];
										}else{
											$qty=(int)$v['Qty'];
										}
										if($qty<1) continue;
										$v['Name'.$c['manage']['web_lang']]=$v['Name'];
										$price=$v['Price']+$v['PropertyPrice'];
										$v['Discount']<100 && $price*=$v['Discount']/100;
										$price=(float)substr(sprintf('%01.3f', $price), 0, -1);
										$amount+=($price*$qty);
										if($k==3) echo '</tbody><tbody class="more_product">';
									?>
										<tr>
											<td><?=$i;?></td>
											<td>
												<?php 
												if($v['BuyType']==4){
													$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
													if(!$package_row) continue;
													$attr=array();
													$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
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
														$quantity+=$qty;
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
																	foreach((array)$attr as $k3=>$z){
																		if($k3=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
																		echo '<p class="attr_'.$k3.'">'.($k3=='Overseas'?'{/shipping.area.ships_from/}':$k3).': '.$z.'</p>';
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
																	}?>
																</div>
															<?php }?>
														</dd>
													</dl>
													<div class="blank6"></div>
													<?php }?>
													<?php if($v['Remark']){?><dl><dd><p class="remark">{/orders.remark/}: <?=$v['Remark'];?></p></dd></dl><?php }?>
												<?php 
												}else{
													$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
													$url=ly200::get_url($v, 'products', $c['manage']['web_lang']);
													$quantity+=$qty;
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
															foreach((array)$attr as $k3=>$z){
																if($k3=='Overseas' && ((int)$c['manage']['config']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
																echo '<p>'.($k3=='Overseas'?'{/shipping.area.ships_from/}':$k3).': '.$z.'</p>';
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
											<td><?=$Symbol.sprintf('%01.2f', $price);?></td>
											<?php if($is_edit){?><td nowrap="nowrap"><?php if($key=='00'){?><input type="text" name="Waybill[]" value="0" size="4" maxlength="8" class="part_input" data-max="<?=$qty;?>" data-lid="<?=$v['LId'];?>" /><?php }?></td><?php }?>
											<td><?=$qty;?></td>
											<td class="last"><?=$Symbol.sprintf('%01.2f', ($price*$qty));?></td>
										</tr>
									<?php
										++$i;
									}?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="<?=$is_edit?'4':'3';?>">
											<?php if($is_edit){?><input type="button" class="btn_ok waybill_submit_btn" value="{/orders.product.<?=$key=='00'?'part':'merge';?>/}" /><?php }?>
											<?php if($k>2){?><a href="javascript:;" class="btn_ok btn_more"><?=$c['manage']['lang_pack']['global']['open'];?></a>&nbsp;&nbsp;&nbsp;<?php }?>
										</td>
										<td><?=$quantity;?></td>
										<td><?=$Symbol.sprintf('%01.2f', $amount);?></td>
									</tr>
								</tfoot>
							</table>
							<div class="divide"></div>
						<?php }?>
					</div>
			<?php
				}
			}?>
			<?php /******************************************* 产品列表 End *******************************************/?>
		</div>
		<div id="turn_page"><?=manage::turn_page($orders_row[1], $orders_row[2], $orders_row[3], '?'.ly200::query_string('page').'&page=');?></div>
    <?php
		//销毁变量
		unset($orders_row, $order_list_row);
	}?>
</div>