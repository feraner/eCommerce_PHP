<?php !isset($c) && exit();?>
<?php
$d_ary=array('list', 'view', 'cancel', 'editPay');
$d=$_GET['d'];
!in_array($d, $d_ary) && $d=$d_ary[0];

?>
<script type="text/javascript">$(function(){user_obj.user_order()});</script>
<div id="user">
	<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a><em><i></i></em><a href="/account/orders/">'.$c['lang_pack']['my_orders'].'</a>');?>
    <?php
	if($d=='list'){
		$query_string=ly200::get_query_string(ly200::query_string('m, a, p, page'));
		$row_count=5;
		$order_row=str::str_code(db::get_limit_page('orders', $c['where']['user'], '*', 'OrderId desc', 0, $row_count));
	?>
		<div id="orderlist" class="user_order" data-number="0" data-page="1" data-total="<?=$order_row[3];?>">
			<div class="divide_5px"></div>
		</div>
    <?php
	}elseif($d=='view'){
		$OId=$_GET['OId'];
		$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and UserId='{$_SESSION['User']['UserId']}'"));
		!$orders_row && js::location('/account/orders/');
		$isFee=($orders_row['OrderStatus']>=4 && $orders_row['OrderStatus']!=7)?1:0;
		$total_price=orders::orders_price($orders_row, $isFee, 1);
		$isFee && $HandingFee=$total_price-orders::orders_price($orders_row, 0, 1);
		
		$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
		$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");
		
		$orders_log_row=db::get_all('orders_log', "OrderId='{$orders_row['OrderId']}'");
		$payment_row=db::get_one('payment', "PId='{$orders_row['PId']}' and IsUsed=1", 'IsOnline, Method');
		$IsWaitingPayment=($orders_row['OrderStatus']==1 || $orders_row['OrderStatus']==3)?1:0;
		
		//订单产品信息
		$OvId=0;
		$oversea_id_ary=array();
		$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='{$orders_row['OrderId']}'", 'o.*, o.SKU as OrderSKU, p.Prefix, p.Number, p.PicPath_0', 'o.OvId asc, o.LId asc');
		foreach($order_list_row as $k=>$v){
			$OvId=$v['OvId'];
			!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
		}
		sort($oversea_id_ary); //排列正序
		
		//发货方式(多个发货地)
		$shipping_ary=array(
			'ShippingOvExpress'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvExpress']), 'decode'),
			'ShippingOvSId'				=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvSId']), 'decode'),
			'ShippingOvType'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvType']), 'decode'),
			'ShippingOvInsurance'		=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurance']), 'decode'),
			'ShippingOvPrice'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvPrice']), 'decode'),
			'ShippingOvInsurancePrice'	=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvInsurancePrice']), 'decode')
		);
		
		//发货信息
		$received=$shipped_count=0;
		if(!$orders_row['TrackingNumber'] && $orders_row['ShippingTime']==0 && !$orders_row['Remarks']){ //多个发货地
			$shipped_ary=array(
				'Express'			=>	str::json_data(htmlspecialchars_decode($orders_row['ShippingOvExpress']), 'decode'),
				'TrackingNumber'	=>	str::json_data(htmlspecialchars_decode($orders_row['OvTrackingNumber']), 'decode'),
				'ShippingTime'		=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingTime']), 'decode'),
				'Remarks'			=>	str::json_data(htmlspecialchars_decode($orders_row['OvRemarks']), 'decode'),
				'Status'			=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingStatus']), 'decode')
			);
			foreach($shipped_ary['Express'] as $k=>$v){
				(int)$shipped_ary['Status'][$k]==1 && $received+=1;
				$shipped_count+=1;
			}
		}else{ //单个发货地
			$shipped_ary=array(
				'Express'			=>	array($OvId=>$orders_row['ShippingExpress']),
				'TrackingNumber'	=>	array($OvId=>$orders_row['TrackingNumber']),
				'ShippingTime'		=>	array($OvId=>$orders_row['ShippingTime']),
				'Remarks'			=>	array($OvId=>$orders_row['Remarks']),
				'Status'			=>	array($OvId=>($orders_row['TrackingNumber']?1:0))
			);
			(int)$shipped_ary['Status']==1 && $received+=1;
			$shipped_count+=1;
		}
		$orders_waybill_row=db::get_all('orders_waybill', "OrderId='{$orders_row['OrderId']}'");
		foreach($orders_waybill_row as $v){
			(int)$v['Status']==1 && $received+=1;
			$shipped_count+=1;
		}
		
		//所有产品属性
		$attribute_cart_ary=$vid_data_ary=array();
		$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
		foreach($attribute_row as $v){
			$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]);
		}
		$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
		foreach($value_row as $v){
			$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"];
		}
	?>
		<div class="divide_8px ui_border_b"></div>
		<div class="order_detail">
			<div class="detail_box ui_border_b">
				<div class="txt">
					<div class="blank10"></div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['shippedto'];?>:</strong>
						<p><?=$orders_row['ShippingFirstName'].' '.$orders_row['ShippingLastName'];?> (<?=$orders_row['ShippingAddressLine1'].', '.$orders_row['ShippingCity'].', '.$orders_row['ShippingZipCode'].' - '.$orders_row['ShippingState'].', '.$orders_row['ShippingCountry'];?>)</p>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['method'];?>:</strong>
						<p>
							<?php
							if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']==''){ //多个发货地
								foreach($oversea_id_ary as $k=>$v){
									echo $c['config']['Overseas'][$v]['Name'.$c['lang']].': '.$shipping_ary['ShippingOvExpress'][$v].'<br />';
								}
							}else{ //单个发货地
								echo (int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?><?=$shipping_row['Brief']?" ({$shipping_row['Brief']})":'';
							}
							?>
						</p>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['billedto'];?>:</strong>
						<p><?=$orders_row['BillFirstName'].' '.$orders_row['BillLastName'];?> (<?=$orders_row['BillAddressLine1'].', '.$orders_row['BillCity'].', '.$orders_row['BillZipCode'].' - '.$orders_row['BillState'].', '.$orders_row['BillCountry'];?>)</p>
					</div>
					<?php if($orders_row['OrderStatus']>4){?><script type="text/javascript" src="//www.17track.net/externalcall.js"></script><?php }?>
					<?php
					if($orders_row['OrderStatus']>4){
					?>
						<div class="rows clean">
							<strong><?=$c['lang_pack']['user']['trackNo'];?>:</strong>
							<p>
								<?php
								foreach($oversea_id_ary as $k=>$v){
									if(!$shipped_ary['TrackingNumber'][$v]) continue;
								?>
									<strong><?=$c['config']['Overseas'][$v]['Name'.$c['lang']];?>:</strong><span id="<?=$OId.$v;?>" class="track"><?=$shipped_ary['TrackingNumber'][$v];?></span>
									<script type="text/javascript">
									YQV5.trackSingleF1({
										YQ_ElementId:"<?=$OId.$v;?>",	//必须，指定悬浮位置的元素ID。
										YQ_Width:600,	//可选，指定查询结果宽度，最小宽度为600px，默认撑满容器。
										YQ_Height:400,	//可选，指定查询结果高度，最大高度为800px，默认撑满容器。
										YQ_Lang:"en",	//可选，指定UI语言，默认根据浏览器自动识别。
										YQ_Num:"<?=$shipped_ary['TrackingNumber'][$v];?>"	//必须，指定要查询的单号。
									});
									</script>
								<?php }?>
							</p>
						</div>
					<?php }?>
				</div>
			</div>
			<div class="divide_8px"></div>
			<div class="detail_box ui_border_b">
				<div class="title clean"><div class="ui_border_b"><?=$c['lang_pack']['mobile']['order'];?></div></div>
				<div class="txt">
					<div class="rows clean">
						<strong><?=$c['lang_pack']['user']['status'];?>:</strong>
						<span><?=$c['lang_pack']['user']['OrderStatusAry'][$orders_row['OrderStatus']];?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['number'];?>:</strong>
						<span><?=$OId;?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['user']['date'];?>:</strong>
						<span><?=$orders_row['OrderTime']?date('F d, Y', $orders_row['OrderTime']):'N/A';?></span>
					</div>
					<?php if($orders_row['OrderStatus']==7){?>
						<div class="rows clean">
							<strong><?=$c['lang_pack']['mobile']['cancel_reason'];?>:</strong>
							<span><?=$orders_row['CancelReason'];?></span>
						</div>
					<?php }?>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['cart']['payment'];?>:</strong>
						<span><?=$orders_row['PaymentMethod'];?></span>
					</div>
				</div>
			</div>
			<div class="divide_8px"></div>
			<div class="detail_box ui_border_b">
				<div class="title clean"><div class="ui_border_b"><?=$c['lang_pack']['mobile']['summary'];?></div></div>
				<div class="txt detail_prolist">
					<?php
					$pro_ary=$order_list_ary=$pro_qty_ary=$waybill_ary=array();
					$_OvId=0;
					foreach($order_list_row as $v){
						$pro_ary[$v['LId']]=$v; //订单产品信息
						$order_list_ary[$v['OvId']]['00'][]=$pro_ary[$v['LId']]; //总信息
						$pro_qty_ary[$v['LId']]=$v['Qty'];
						$_OvId=$v['OvId'];
					}
					$orders_waybill_row=db::get_all('orders_waybill', "OrderId='{$orders_row['OrderId']}'");
					foreach($orders_waybill_row as $v){
						$i=0;
						$waybill_ary[$v['Number']]=$v;
						$ProInfo=str::json_data(htmlspecialchars_decode($v['ProInfo']), 'decode');
						foreach($ProInfo as $k2=>$v2){ //$k2==LId $v2==QTY
							$ovid=$pro_ary[$k2]['OvId'];
							$order_list_ary[$ovid][$v['Number']][$i]=$pro_ary[$k2];
							$order_list_ary[$ovid][$v['Number']][$i]['Qty']=$v2;
							$pro_qty_ary[$k2]-=$v2;
							++$i;
						}
					}
					//发货信息
					if(!$orders_row['TrackingNumber'] && $orders_row['ShippingTime']==0 && !$orders_row['Remarks']){ //多个发货地
						$ship_ary=array(
							'ShippingExpress'		=>	$shipping_ary['ShippingOvExpress'],
							'ShippingMethodSId'		=>	$shipping_ary['ShippingOvSId'],
							'ShippingType'			=>	$shipping_ary['ShippingOvType'],
							'ShippingInsurance'		=>	$shipping_ary['ShippingOvInsurance'],
							'ShippingPrice'			=>	$shipping_ary['ShippingOvPrice'],
							'ShippingInsurancePrice'=>	$shipping_ary['ShippingOvInsurancePrice'],
							'TrackingNumber'		=>	str::json_data(htmlspecialchars_decode($orders_row['OvTrackingNumber']), 'decode'),
							'ShippingTime'			=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingTime']), 'decode'),
							'Remarks'				=>	str::json_data(htmlspecialchars_decode($orders_row['OvRemarks']), 'decode'),
							'Status'				=>	str::json_data(htmlspecialchars_decode($orders_row['OvShippingStatus']), 'decode')
						);
					}else{ //单个发货地
						$ship_ary=array(
							'ShippingExpress'		=>	array($_OvId=>$orders_row['ShippingExpress']),
							'ShippingMethodSId'		=>	array($_OvId=>(int)$orders_row['ShippingMethodSId']),
							'ShippingType'			=>	array($_OvId=>$orders_row['ShippingType']),
							'ShippingInsurance'		=>	array($_OvId=>(int)$orders_row['ShippingInsurance']),
							'ShippingPrice'			=>	array($_OvId=>(float)$orders_row['ShippingPrice']),
							'ShippingInsurancePrice'=>	array($_OvId=>(float)$orders_row['ShippingInsurancePrice']),
							'TrackingNumber'		=>	array($_OvId=>$orders_row['TrackingNumber']),
							'ShippingTime'			=>	array($_OvId=>$orders_row['ShippingTime']),
							'Remarks'				=>	array($_OvId=>$orders_row['Remarks']),
							'Status'				=>	array($_OvId=>($orders_row['TrackingNumber']?1:0))
						);
					}
					$subtotal=0;
					foreach($order_list_ary as $OvId=>$row){
					?>
						<div class="waybill_products_list">
							<?php
							foreach($row as $key=>$val){
								$total=$amount=$quantity=0;
								if((int)$c['config']['global']['Overseas']==1){
									//需要开启海外仓功能才能显示
									$status=4;
								?>
									<div class="row_hd">
										<strong><?=$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']];?></strong>
										<span>
											<?=$c['lang_pack']['user']['status'].': <i>'.$c['lang_pack']['user']['OrderStatusAry'][$status].'</i>';?>
											<?php if($key=='00' && $ship_ary['TrackingNumber'][$OvId]){ $status=5;?>
												<?='( '.$c['lang_pack']['user']['trackNo'].': '.$ship_ary['TrackingNumber'][$OvId].' )';?>
											<?php }elseif($key!='00' && $waybill_ary[$key]['TrackingNumber']){ $status=5;?>
												<?='( '.$c['lang_pack']['user']['trackNo'].': '.$waybill_ary[$key]['TrackingNumber'].' )';?>
											<?php }?>
										</span>
										<?=$ship_ary['Remarks'][$OvId]?"<span>{$c['lang_pack']['cart']['remark']}: {$ship_ary['Remarks'][$OvId]}</span>":'';?>
									</div>
								<?php
								}
								foreach((array)$val as $v){
									if($key=='00'){
										$qty=(int)$pro_qty_ary[$v['LId']];
									}else{
										$qty=(int)$v['Qty'];
									}
									if($qty<1) continue;
									if($v['BuyType']==4){
										//组合促销产品
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
										<div class="prod_box clean">
											<h4 class="fl">[ <?=$c['lang_pack']['cart']['package']?> ] <?=$package_row['Name'];?></h4>
											<div class="fr"><?=cart::iconv_price($v['Price'], 0, $orders_row['Currency']);?></div>
											<div class="clear"></div>
											<?php
											foreach((array)$products_row as $k2=>$v2){
												$name=$v2['Name'.$c['lang']];
												$number=$v2['Prefix'].$v2['Number'];
												$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
												$url=ly200::get_url($v2, 'products');
												$subtotal+=$qty;
											?>
											<div class="item package clean ui_border_b<?=$k2?'':' first';?>">
												<div class="img fl"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
												<div class="info">
													<div class="name"><a href="<?=$url;?>"><?=$name;?></a></div>
													<?php if($number){?><div class="number"><?=$number;?></div><?php }?>
													<?php
													if($k2==0){
														foreach((array)$attr as $k3=>$v3){
															if($k3=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
															echo '<div class="attr clean">'.($k3=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k3).': &nbsp;'.$v3.'</div>';
														}
														if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
															echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</div>';
														}
													}elseif($data_ary[$v2['ProId']]){
														$OvId=0;
														foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
															if($k3=='Overseas'){ //发货地
																$OvId=str_replace('Ov:', '', $v3);
																if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
																echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</div>';
															}else{
																echo '<div class="attr clean">'.$attribute_ary[$k3][1].': &nbsp;'.$vid_data_ary[$k3][$v3].'</div>';
															}
														}
														if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
															echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</div>';
														}
													}?>
												</div>
												<div class="value">
													<div class="qty">x<?=$qty;?></div>
												</div>
											</div>
											<?php }?>
										</div>
								<?php
									}else{
										$name=$v['Name'];
										$number=$v['Prefix'].$v['Number'];
										$attr=str::json_data($v['Property'], 'decode');
										$url=$c['mobile_url'].ly200::get_url($v, 'products');
										$subtotal+=$qty;
										$price=$v['Price']+$v['PropertyPrice'];
										$v['Discount']<100 && $price*=$v['Discount']/100;
								?>
										<div class="item clean ui_border_b">
											<div class="img fl"><a href="<?=$url;?>"><img src="<?=$v['PicPath'];?>" alt="<?=$name;?>" /></a></div>
											<div class="info">
												<div class="name"><a href="<?=$url;?>"><?=$name;?></a></div>
												<?php if($number){?><div class="number"><?=$number;?></div><?php }?>
												<?php
												if(count($attr)){
													foreach($attr as $k=>$z){
														if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
														echo '<div class="attr clean">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': &nbsp;'.$z.'</div>';
													}
												}
												if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
													echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</div>';
												}?>
											</div>
											<div class="value">
												<div class="price"><?=cart::iconv_price($price, 0, $orders_row['Currency']);?></div>
												<div class="qty">x<?=$qty;?></div>
											</div>
										</div>
								<?php
									}
								}?>
							<?php }?>
						</div>
					<?php }?>
				</div>
				<div class="detail_summary">
					<div class="clean">
						<div class="key"><?=$c['lang_pack']['mobile']['total_qty'];?>:</div>
						<div class="value"><?=$subtotal;?></div>
					</div>
					<div class="clean">
						<div class="key"><?=$c['lang_pack']['mobile']['subtotal'];?>:</div>
						<div class="value"><?=cart::iconv_price($orders_row['ProductPrice'], 0, $orders_row['Currency']);?></div>
					</div>
					<div class="clean">
						<div class="key"><?=$c['lang_pack']['mobile']['ship_and_ins'];?>:</div>
						<div class="value"><?=cart::iconv_price($orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice'], 0, $orders_row['Currency']);?></div>
					</div>
					<?php if($isFee && $HandingFee>0){?>
						<div class="clean">
							<div class="key"><?=$c['lang_pack']['mobile']['hand_fee'];?>:</div>
							<div class="value"><?=cart::iconv_price($HandingFee, 0, $orders_row['Currency']);?></div>
						</div>
					<?php }?>
					<?php
					if($orders_row['CouponCode'] && ($orders_row['CouponPrice']>0 || $orders_row['CouponDiscount']>0)){
						$discountPrice=$orders_row['CouponPrice']>0?$orders_row['CouponPrice']:$orders_row['ProductPrice']*$orders_row['CouponDiscount'];
					?>
						<div class="clean">
							<div class="key"><?=$c['lang_pack']['mobile']['coupon_save'];?> (-):</div>
							<div class="value"><?=cart::iconv_price($discountPrice, 0, $orders_row['Currency']);?></div>
						</div>
					<?php }?>
					<div class="clean">
						<div class="key"><?=$c['lang_pack']['mobile']['total'];?>:</div>
						<div class="value total"><?=cart::iconv_price($total_price, 0, $orders_row['Currency']);?></div>
					</div>
				</div>
			</div>
			<?php if($IsWaitingPayment){?>
				<div class="detail_button">
					<a href="/cart/complete/<?=$orders_row['OId']?>.html" class="btn_global btn_payment BuyNowBgColor"><?=$c['lang_pack']['mobile']['com_your_pay'];?></a>
					<a href="/account/orders/view<?=$orders_row['OId']?>.html?d=editPay" class="btn_global btn_payment BuyNowBgColor" rel="nofollow"><?=$c['lang_pack']['user']['editPay'];?></a>
					<a href="/account/orders/cancel<?=$orders_row['OId']?>.html" class="btn_global btn_delete"><?=$c['lang_pack']['mobile']['del_order'];?></a>
					<div class="blank30"></div>
				</div>
			<?php }else{?>
				<div class="blank30"></div>
			<?php }?>
		</div>
    <?php
	}elseif($d=='cancel'){
		$OId=$_GET['OId'];
		$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3,7)"));// and OrderStatus=7
		!$orders_row && js::location('/account/orders/');
		$total_price=orders::orders_price($orders_row);
	?>
		<div class="order_detail order_cancel">
			<div class="detail_box">
				<div class="title clean icon_order_detail_info"><div class="ui_border_b"><?=$c['lang_pack']['mobile']['order_info'];?></div></div>
				<div class="txt">
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['number'];?>:</strong>
						<span><?=$OId;?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['user']['date'];?>:</strong>
						<span><?=$orders_row['OrderTime']?date('F d, Y', $orders_row['OrderTime']):'N/A';?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['total_price'];?>:</strong>
						<span><?=$orders_row['Currency'].' '.cart::iconv_price($total_price, 0, $orders_row['Currency']);?></span>
					</div>
					<?php if($orders_row['OrderStatus']==7){?>
						<div class="rows clean">
							<strong><?=$c['lang_pack']['mobile']['cancel_reason'];?>:</strong>
							<span><?=$orders_row['CancelReason'];?></span>
						</div>
						<div class="blank15"></div>
						<a href="/account/orders/view<?=$OId?>.html" class="btn_global m_form_back"><?=$c['lang_pack']['mobile']['back'];?></a>
					<?php }else{?>
						<form id="cancelForm" method="post" action="/account/">
							<textarea name="CancelReason" class="box_input box_textarea m_form_area" placeholder="<?=$c['lang_pack']['mobile']['message'];?>..."></textarea>
							<div class="btn_global m_form_button BuyNowBgColor"><?=$c['lang_pack']['mobile']['cancel'];?></div>
							<a href="javascript:history.go(-1);" class="btn_global m_form_back"><?=$c['lang_pack']['mobile']['back'];?></a>
							<input type="hidden" name="OId" value="<?=$OId;?>" />
							<input type="hidden" name="do_action" value="user.cancel_order" />
						</form>
					<?php }?>
				</div>
			</div>
		</div>
		<div class="blank15"></div>
	<?php
	}elseif($d=='editPay'){
		$OId=$_GET['OId'];
		$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3,7)"));// and OrderStatus=7
		!$orders_row && js::location('/account/orders/');
		$total_price=orders::orders_price($orders_row);
		$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', $c['my_order'].'IsOnline desc,PId asc');
	?>
		<div class="order_detail order_cancel">
			<div class="detail_box">
				<div class="title clean icon_order_detail_info"><div class="ui_border_b"><?=$c['lang_pack']['mobile']['order_info'];?></div></div>
				<div class="txt">
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['number'];?>:</strong>
						<span><?=$OId;?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['user']['date'];?>:</strong>
						<span><?=$orders_row['OrderTime']?date('F d, Y', $orders_row['OrderTime']):'N/A';?></span>
					</div>
					<div class="rows clean">
						<strong><?=$c['lang_pack']['mobile']['total_price'];?>:</strong>
						<span class="total_price"><?=$orders_row['Currency'].cart::iconv_price(0, 1, $orders_row['Currency']).' '.cart::currency_format($total_price, 0, $orders_row['Currency']);?></span>
					</div>
						<div class="blank15"></div>
						<form name="pay_edit_form" method="post" action="/account/">
							<div class="box_select">
								<select name="PId">
									<?php
									foreach($payment_row as $v){
										if($v['MaxPrice']>0?($total_price<$v['MinPrice'] || $total_price>$v['MaxPrice']):($total_price<$v['MinPrice'])) continue;
									?>
										<option value="<?=$v['PId'];?>" fee="<?=$v['AdditionalFee'];?>" affix="<?=cart::iconv_price($v['AffixPrice'], 2, $orders_row['Currency']);?>"<?=$orders_row['PId']==$v['PId']?' selected':'';?>><?=$v['Name'.$c['lang']];?></option>
									<?php }?>
								</select>
							</div>
							<div id="pay_button" class="btn_global m_form_button BuyNowBgColor"><?=$c['lang_pack']['mobile']['submit'];?></div>
							<a href="javascript:history.go(-1);" class="btn_global m_form_back"><?=$c['lang_pack']['mobile']['back'];?></a>
							<input type="hidden" name="OId" value="<?=$OId;?>" />
							<input type="hidden" name="TotalPrice" value="<?=$total_price;?>" />
							<input type="hidden" name="BackLocation" value="/account/orders/view<?=$OId?>.html" />
							<input type="hidden" name="Symbols" value="<?=cart::iconv_price($total_price, 1, $orders_row['Currency']);?>" currency="<?=$orders_row['Currency'];?>" />
						</form>
				</div>
			</div>
		</div>
		<div class="blank15"></div>
    <?php }?>
</div>