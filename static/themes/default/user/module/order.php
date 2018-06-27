<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$d_ary=array('list', 'view', 'cancel', 'contact');
$d=$_GET['d'];
!in_array($d, $d_ary) && $d=$d_ary[0];
?>
<script type="text/javascript">$(document).ready(function(){user_obj.order_init()});</script>
<?php if($d=='list'){
	/*** 全部属性 ***/
	$vid_data_ary=array();
	$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
	foreach($attribute_row as $v){
		$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]);
	}
	$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){
		$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"];
	}
	$OrderStatus=(int)$_GET['OrderStatus'];//订单状态（搜索）
	$Review=(int)$_GET['Review'];
	$g_Page=(int)$_GET['page'];
	$g_Page<1 && $g_Page=1;
	$row_count=10;
	$select_order_row=str::str_code(db::get_limit_page('orders o left join user_message m on o.UserId=m.UserId and o.OId=m.Subject',  "o.{$c['where']['user']}".($OrderStatus?" and o.OrderStatus='$OrderStatus'":'').($Review?" and OrderId not in(select OrderId from products_review where UserId='{$_SESSION['User']['UserId']}' and {$c['time']}<(AccTime+{$c['orders']['review']})) and OrderStatus=6 and {$c['time']}<(o.PayTime+{$c['orders']['review']})":''), 'o.*,m.IsReply', 'o.OrderId desc', $g_Page, $row_count));
	$query_string=ly200::query_string('m,a,page');
	?>
	<div id="user_heading" class="fl">
		<h2>
			<?=$c['lang_pack']['my_orders'];?>
		</h2>
	</div>
	<div class="message_list fr">
		<a href="/account/orders/?OrderStatus=1" class="sys_bg_button m0 <?=$OrderStatus==1 ? 'cur' : ''; ?>">
			<?=$c['lang_pack']['user']['OrderStatusAry'][1];?>
			<?php if($num=db::get_row_count('orders', "UserId='{$_SESSION['User']['UserId']}' and OrderStatus=1")){
				echo '<span>'.$num.'</span>'; 
				} ?>
		</a>
		<a href="/account/orders/?OrderStatus=5" class="sys_bg_button m1 <?=$OrderStatus==5 ? 'cur' : ''; ?>">
			<?=$c['lang_pack']['user']['OrderStatusAry'][5];?>
			<?php if($num = db::get_row_count('orders', "UserId='{$_SESSION['User']['UserId']}' and OrderStatus=5")){
					echo '<span>'.$num.'</span>'; 
				} ?>
		</a>
		<a href="/account/orders/?Review=1" class="sys_bg_button m2 <?=$_GET['Review']==1 ? 'cur' : ''; ?>">
			<?=$c['lang_pack']['user']['awaiting_review']; ?>
			<?php 
				$revies_count =  db::get_row_count('orders o left join user_message m on o.UserId=m.UserId and o.OId=m.Subject',  "o.{$c['where']['user']} and OrderId not in(select OrderId from products_review where UserId='{$_SESSION['User']['UserId']}' and {$c['time']}<(AccTime+{$c['orders']['review']})) and OrderStatus=6 and {$c['time']}<(o.PayTime+{$c['orders']['review']})");
				echo $revies_count?'<span>'.$revies_count.'</span>':'';
			?>
		</a>
	</div>
	<div class="clear"></div>
	<div class="blank20"></div>
	<table class="order_table" style="margin-bottom: 5px;">
		<tr>
			<th><?=$c['lang_pack']['mobile']['order_info'];?></th>
			<th width="16%"><?=$c['lang_pack']['user']['grandTotal'];?></th>
			<th width="16%" class="order_status">
				<div class="user_action_down">
					<?=$c['lang_pack']['user']['orderStatus'];?>
					<i></i>
					<ul>
						<?php foreach($c['lang_pack']['user']['OrderStatusAry'] as $k=>$v){?>
							<li><a href="/account/orders/?OrderStatus=<?=$k;?>" status="<?=$k;?>"<?=$OrderStatus==$k?' class="current"':'';?>><?=$c['lang_pack']['user']['OrderStatusAry'][$k];?></a></li>
						<?php }?>
					</ul>
				</div>
			</th>
			<th width="16%"><?=$c['lang_pack']['user']['action'];?></th>
		</tr>
	</table>
	<?php
		foreach((array)$select_order_row[0] as $key=>$val){ 
			$isFee=($val['OrderStatus']>=4 && $val['OrderStatus']!=7)?1:0;
			$total_price=orders::orders_price($val, $isFee);
			$orders_products_list = db::get_all('orders_products_list',"OrderId='{$val['OrderId']}'");
			?>
			<table class="order_table">
				<tbody>	
					<tr class="list_oid">
						<td colspan="4">
							<?=date('M.d.Y',$val['OrderTime']); ?> &nbsp;&nbsp; <?=$c['lang_pack']['user']['orderNo']; ?> 
							<a href="/account/orders/view<?=$val['OId'];?>.html" title="<?=$val['OId']; ?>"><?=$val['OId']; ?></a>
						</td>
					</tr>
					<tr class="list_opl">
						<td>
							<?php 
							$hide_num=$hide_item_num=0;
							foreach((array)$orders_products_list as $k => $v){ 
								$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
								!$attr && $attr=str::json_data($v['Property'], 'decode');
								$products_row = array();
								if($v['BuyType']==4){
									$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
									if(!$package_row) continue;
									$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
									$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
									$pro_where=='' && $pro_where=0;
									$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
									$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
								}else{
									$products_row[]=$v;
								}
								?>
								<div class="list <?=$hide_num > 2 || $hide_item_num > 2 ? 'hide' : ''; ?>">
									<?php foreach((array)$products_row as $k1=>$v1){

										$name = $v1['Name']; 
										$img = @is_file($c['root_path'].$v1['PicPath'])?$v1['PicPath']:ly200::get_size_img(db::get_value('products',"ProId='{$v1['ProId']}'",'PicPath_0'), '240x240');
										$url = ly200::get_url($v1,'products');
										$sku = $v1['SKU'];
										if($v['BuyType']==4){
											$name=$v1['Name'.$c['lang']];
											$img=ly200::get_size_img($v1['PicPath_0'], '240x240');
											$sku=$v1['Prefix'].$v1['Number'];
										}
										$price=$v['Price']+$v['PropertyPrice'];
										$v['Discount']<100 && $price*=$v['Discount']/100;
										?>
										<div class="<?=$v['BuyType']==4 && $hide_item_num>2 ? 'hide' : ''; ?>">
											<?php if($k1){ ?><div class="br"></div><?php } ?>
											<a href="<?=$url; ?>" class="pic">
												<img src="<?=$img; ?>" alt="<?=$name; ?>" />
												<span></span>
											</a>
											<div class="desc">
												<p class="name"><?=$name; ?></p>
												<?php if($sku){ ?>
													<p class="sku"><?=$sku; ?></p>
												<?php } ?>
												<ul>
													<?php
													if($k1==0){
														foreach((array)$attr as $k2=>$v2){
															if($k2=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
															echo '<li>'.($k2=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k2).': '.$v2.'</li>';
														}
														if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
															echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</li>';
														}
													}elseif($data_ary[$v1['ProId']]){ ?>
														<?php
														$OvId=0;
														foreach((array)$data_ary[$v1['ProId']] as $k2=>$v2){
															if($k2=='Overseas'){ //发货地
																$OvId=str_replace('Ov:', '', $v2);
																if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
																echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</li>';
															}else{
																echo '<li>'.$attribute_ary[$k2][1].': '.$vid_data_ary[$k2][$v2].'</li>';
															}
														}
														if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
															echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</li>';
														}?>
													<?php }?>
												</ul>
											</div>
											<?php if($k1==0){?>
												<span class="p_price"><?=cart::iconv_price($price, 0, $val['Currency']);?></span>
												<span class="p_qty">x<?=$v['Qty']; ?></span>
											<?php }?>
											<div class="clear"></div>
										</div>
									<?php 
									$hide_item_num++;
									} ?>
								</div>
								<?php 
								$hide_num++;
							} ?>
						</td>
						<td width="16%" class="o_price"><?=cart::iconv_price(0, 1, $val['Currency']).cart::currency_format($total_price, 0, $val['Currency']);?></td>
						<td width="16%" class="o_status">
							<?=$c['lang_pack']['user']['OrderStatusAry'][$val['OrderStatus']];?>
						</td>
						<td width="16%" class="options">
							<?php if($val['OrderStatus']==1){ ?>
								<a href="/cart/complete/<?=$val['OId'];?>.html" oid="<?=$val['OId'];?>" pid="<?=$val['PId'];?>" class="pay_now edit_pay_btn"><?=$c['lang_pack']['cart']['paynow'];?></a>
							<?php } ?>
							<div class="user_action_down">
								<a href="/account/orders/view<?=$val['OId'];?>.html" class="sys_bg_button"><?=$c['lang_pack']['user']['view_more']; ?></a>
								<i></i>
								<?=$c['FunVersion']&&$val['IsReply']?'<em></em>':''?>
								<ul>
									<?php if($val['OrderStatus']==5){ ?><li><a href="javascript:;" class="confirm_receiving" oid="<?=$val['OId'];?>"><?=$c['lang_pack']['user']['receiving'];?></a></li><?php } ?>	
									<li><a href="/account/print/<?=$val['OId'];?>.html" target="_blank"><?=$c['lang_pack']['user']['printOrder'];?></a></li>
									<?php if($c['FunVersion']){?><li><a href="/account/orders/contact<?=$val['OId'];?>.html"><?=$c['lang_pack']['contact'];?><?=$val['IsReply']?'<b>1</b>':''?></a></li><?php }?>
									<?php if($val['OrderStatus']==1 || $val['OrderStatus']==3){ ?><li><a href="/account/orders/cancel<?=$val['OId'];?>.html"><?=$c['lang_pack']['mobile']['cancel_order'];?></a></li><?php } ?>
								</ul>
							</div>
						</td>
					</tr>
				</tbody>
				<?php if($hide_num > 3 || $hide_item_num > 3){ ?>
					<tfoot>
						<tr>
							<td colspan="4"> <a href="javascript:;" class="see_more sys_bg_button"><?=$c['lang_pack']['more']; ?></a> </td>
						</tr>
					</tfoot>
				<?php } ?>
		</table>
	<?php } ?>
<div id="turn_page"><?=ly200::turn_page_html($select_order_row[1], $select_order_row[2], $select_order_row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>
<?php
}elseif($d=='cancel'){
	$OId=$_GET['OId'];
	$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and UserId='{$_SESSION['User']['UserId']}' and OrderStatus in(1,2,3,7)"));// and OrderStatus=7
	!$orders_row && js::location('/account/orders/');
	$total_price=orders::orders_price($orders_row);
	?>
	<div class="order_body">
		<div class="order_cancel_info">
			<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['my_orders'];?></a>
		    <div class="blank20"></div>
			<div id="lib_user_products">	
	           	<div class="order_con">
			    	<span class="or_name"><?=$c['lang_pack']['user']['orderNo'];?></span> : <?=$OId;?>	        
			    	<span class="or_date"><?=$orders_row['OrderTime']?date('F d, Y', $orders_row['OrderTime']):'N/A';?></span>
			    </div>
			</div>
		    <div class="blank15"></div>
		    <div class="blank20"></div>
			<form id="cancelForm" class="user_form" method="post" action="/">
				<div class="reply_tips"><?=$c['lang_pack']['user']['cancelReason'];?></div>
				<div class="rows">
					<span class="input"><textarea name="CancelReason" placeholder="<?=$orders_row['CancelReason']; ?>" class="form_area form_text" cols="60" rows="3" notnull=""><?=$orders_row['CancelReason']; ?></textarea></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
		            <div class="submit">
		            	<input type="submit" class="submit_btn" name="submit_button" value="<?=$c['lang_pack']['user']['cancelOrder'];?>">
		            </div>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="OId" value="<?=$OId;?>" />
				<input type="hidden" name="do_action" value="user.cancel_order" />
			</form>
		</div>
	    <?php if($orders_row['OrderStatus']==7){?>
		<div class="order_cancel_view">
			<h3><?=$c['lang_pack']['user']['successDel'];?></h3>
			<p><?=$c['lang_pack']['user']['backReturn'];?></p>
		</div>
	    <?php }?>
	</div>
<?php
}elseif($d=='contact'){
	echo ly200::load_static('/static/js/plugin/lightbox/js/lightbox.min.js','/static/js/plugin/lightbox/css/lightbox.min.css');
	$OId=(int)$_GET['OId'];
	$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and UserId='{$_SESSION['User']['UserId']}'"));
	!$orders_row && js::location('/account/orders/');
	$row=str::str_code(db::get_one('user_message', "UserId='{$_SESSION['User']['UserId']}' and Module='orders' and Subject='$OId'"));
	if($row){
		if($row['IsReply']) db::update('user_message',"MId='{$row['MId']}'",array('IsReply'=>0));
		$reply_row=str::str_code(db::get_all('user_message_reply', "MId='{$row['MId']}'"));
	}
	?>
	<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['orderTitle'];?></a>
	<div class="blank20"></div>
	<div id="lib_user_products">
		<div class="order_con">
	    	<span class="or_name"><?=$c['lang_pack']['user']['orderNo'];?></span> : 
	        <?=$OId?>
	        <span class="or_date"><?=date('M d, Y',$orders_row['OrderTime'])?></span>
	    </div>
	    <div class="blank15"></div>
	    <?php if($row){?>
	    <div class="content_box">
	        <div class="item">
	            <div class="item_img"><img src="/static/themes/default/images/user/icon_faq.png" /></div>
	            <div class="item_con">
	                <div class="item_txt"><?=nl2br($row['Content'])?></div>
	                <?php if($row['PicPath']){?><a class="light_box_pic" target="_blank" href="<?=$row['PicPath']?>"><img src="<?=$row['PicPath']?>" /></a><?php }?>
	                <span></span>
	            </div>
	            <div class="clear"></div>
	        </div>
	        <?php foreach((array)$reply_row as $k => $v){?>
	        <div class="item <?=$v['UserId']?'':'mine'?>">
	        	<?php if($k==count($reply_row)-1){?><div id="View"></div><?php }?>
	            <div class="item_date"><?=date('Y-m-d H:i',$v['AccTime'])?></div>
	            <div class="clear"></div>
	            <div class="item_img"><img src="/static/themes/default/images/user/<?=$v['UserId']?'icon_faq':'icon_reply'?>.png" /></div>
	            <div class="item_con">
	                <div class="item_txt"><?=nl2br($v['Content'])?></div>
	                <?php if($v['PicPath']){?><a class="light_box_pic" target="_blank" href="<?=$v['PicPath']?>"><img src="<?=$v['PicPath']?>" /></a><?php }?>
	                <span></span>
	            </div>
	            <div class="clear"></div>
	        </div>
	        <?php }?>
	        <div id="View"></div>
	    </div>
	    <?php }?>
	    <div class="blank20"></div>
	    <form id="reply_form" class="reply_form user_form" method="post"  enctype="multipart/form-data">
	        <div class="reply_tips"><?=$c['lang_pack']['products']['reply'].' '.$c['lang_pack']['user']['content']?></div>
	        <div class="rows">
				<label><?=$c['lang_pack']['user']['content'];?>:</label>
				<span class="input"><textarea name="Content" placeholder="<?=$c['lang_pack']['user']['content'];?>" class="form_text" notnull=""></textarea></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><?=$c['lang_pack']['user']['image'];?>:</label>
				<div class="input upload_box">
	                <input class="upload_file" id="upload_file" type="file" name="PicPath" onchange="loadImg(this);" accept="image/gif,image/jpeg,image/png">
	                <div id="pic_show" class="pic_box"></div>
	            </div>
	            <div class="submit">
	            	<input type="submit" class="submit_btn" name="submit_button" value="<?=$c['lang_pack']['user']['submit'];?>" />
	            </div>
				<div class="clear"></div>
			</div>
	        <input type="hidden" name="MId" value="<?=$row['MId']?>" />
	        <input type="hidden" name="UserId" value="<?=$_SESSION['User']['UserId']?>" />
	        <input type="hidden" name="OId" value="<?=$OId?>" />
	        <input type="hidden" name="JumpUrl" value="/account/orders/contact<?=$OId?>.html" />
	        <input type="hidden" name="do_action" value="user.reply_inbox" />
	    </form>
	</div>
<?php
}else{
	$OId=$_GET['OId'];
	$orders_row=str::str_code(db::get_one('orders', "OId='$OId' and UserId='{$_SESSION['User']['UserId']}'"));
	!$orders_row && js::location('/account/orders/');
	$isFee=($orders_row['OrderStatus']>=4 && $orders_row['OrderStatus']!=7)?1:0;
	$total_price=orders::orders_price($orders_row, $isFee);
	$isFee && $HandingFee=$total_price-orders::orders_price($orders_row);

	$ProductPrice=orders::orders_product_price($orders_row, 1); //产品总价
	$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));
	
	$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
	$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");
	
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
	
	$orders_log_row=db::get_all('orders_log', "OrderId='{$orders_row['OrderId']}'");
	$payment_row=db::get_one('payment', "PId='{$orders_row['PId']}' and IsUsed=1", 'IsOnline, Method, Attribute');
	$account=str::json_data($payment_row['Attribute'], 'decode');
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
<div class="order_body">
	<div class="order_base">
		<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['order_details'];?></a>
		<h3 class="title"><span class="fr"><?=$c['lang_pack']['user']['OrderStatusAry'][$orders_row['OrderStatus']];?></span><?='No.'.$OId; ?></h3>
		<div class="status_box">
			<?php 
				$status_ary = array();
				$end_status = 6;
				$orders_row['OrderStatus'] == 7 && $end_status = 7;
				$status_ary[] = 1;	
				for($i=2;$i<=$end_status;$i++){
					if($orders_row['OrderStatus'] >=4 && $i==3) continue;
					if($i>3 || db::get_row_count('orders_log',"OrderId='{$orders_row['OrderId']}' and OrderStatus='{$i}' and OrderStatus <= '{$orders_row['OrderStatus']}'")){
						$status_ary[]=$i;
					}
				}
				$next = 0;
				foreach((array)$status_ary as $k=>$v){ 
					$time=db::get_value('orders_log',"OrderId='{$orders_row['OrderId']}' and OrderStatus='{$v}' and OrderStatus <= '{$orders_row['OrderStatus']}'",'AccTime','LId desc');
					if($orders_row['OrderStatus']>=$v && $time){
						$cur = 1;
						$next++;
					}else{
						$cur = 0;
					}
					?>
					<div class="item <?=$cur ? ' cur' : ''; ?> <?=$k==$next ? ' next' : '' ; ?>" style="width:<?=100/count($status_ary); ?>%;">
						<p class="status"><?=$c['lang_pack']['user']['OrderStatusAry'][$v];?></p>
						<div class="bg"></div>
						<div class="line <?=$k==0 ? ' fir' : ''; ?><?=$k==count($status_ary)-1 ? ' last' : ''; ?>">
							<?php if($time){ ?>
								<?=date('d/m/Y',$time); ?>
							<?php } ?>
						</div>
					</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<h3 class="title"><?=$c['lang_pack']['mobile']['order_info'];?></h3>
		<div class="order_base_div">
			<table class="order_base_table" cellpadding="3">
				<!-- <tr><th><?=$c['lang_pack']['user']['orderNumber'];?>:</th><td><?=$OId;?></td></tr> -->
				<tr class="tr"><th><?=$c['lang_pack']['user']['orderDate'];?>:</th><td width="70%"><?=$orders_row['OrderTime']?date('F d, Y', $orders_row['OrderTime']):'N/A';?></td></tr>
				<tr class="tr">
					<th><?=$c['lang_pack']['user']['paymentMethod'];?>:</th>
					<td>
						<?=$orders_row['PaymentMethod'];?>
					</td>
				</tr>
				<?php if($orders_row['OrderStatus']==7){?>
					<tr class="tr"><th><?=$c['lang_pack']['mobile']['cancel_reason'];?>:</th><td><strong><?=$orders_row['CancelReason'];?></strong></td></tr>
				<?php }?>
				<?php if($c['config']['global']['CartWeight']){?><tr class="tr"><th><?=$c['lang_pack']['user']['total_weight'];?>:</th><td><?=$orders_row['TotalWeight'];?> KG</td></tr><?php }?>
				<?php
				if($orders_row['ShippingExpress']=='' && $orders_row['ShippingMethodSId']==0 && $orders_row['ShippingMethodType']==''){ //多个发货地
					foreach($oversea_id_ary as $k=>$v){
				?>
						<tr class="tr"><th><?=$k?'':$c['lang_pack']['user']['shippingMethod'].':';?></th><td><strong><?=$c['config']['Overseas'][$v]['Name'.$c['lang']];?>:</strong><?=$shipping_ary['ShippingOvExpress'][$v];?></td></tr>
				<?php
					}
				}else{ //单个发货地
				?>
					<tr class="tr"><th><?=$c['lang_pack']['user']['shippingMethod'].':';?></th><td><?=(int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?><?=$shipping_row['Brief']?" ({$shipping_row['Brief']})":'';?></td></tr>
				<?php }?>
				<tr class="tr"><th><?=$c['lang_pack']['user']['shippedTo'];?>:</th><td><strong><?=$shipto_ary['FirstName'].' '.$shipto_ary['LastName'];?></strong> <br /> <?=$shipto_ary['AddressLine1'].', '.$shipto_ary['City'].', '.$shipto_ary['ZipCode'].' - '.$shipto_ary['State'].', '.$shipto_ary['Country'];?> <br /> <?=$shipto_ary['CountryCode'].' '.$shipto_ary['PhoneNumber'];?></td></tr>
				<tr class="tr"><th><?=$c['lang_pack']['user']['billedTo'];?>:</th><td><strong><?=$orders_row['BillFirstName'].' '.$orders_row['BillLastName'];?></strong> <br /> <?=$orders_row['BillAddressLine1'].', '.$orders_row['BillCity'].', '.$orders_row['BillZipCode'].' - '.$orders_row['BillState'].', '.$orders_row['BillCountry'];?> <br /> <?=$orders_row['BillCountryCode'].' '.$orders_row['BillPhoneNumber'];?></td></tr>
				<?php if($orders_row['OrderStatus']>4){?><script type="text/javascript" src="//www.17track.net/externalcall.js"></script><?php }?>
				<?php
				if($orders_row['OrderStatus']>4){
					foreach($oversea_id_ary as $k=>$v){
						if(!$shipped_ary['TrackingNumber'][$v]) continue;
						$UILang=substr($c['lang'], 1);//UI语言
						$UILang=str_replace(array('jp','zh_tw'), array('ja','zh-tw'), $UILang);//日文、繁体中文
						?>
						<tr class="tr"><th><?=$k?'':$c['lang_pack']['user']['trackNo'].':';?></th>
							<td>
								<strong><?=$c['config']['Overseas'][$v]['Name'.$c['lang']];?>:</strong>
								<span id="<?=$OId.$v;?>" class="query"><?=$shipped_ary['TrackingNumber'][$v];?></span>
								<?php if($shipped_ary['TrackingNumber'][$v] && $shipping_cfg['Query']){?>&nbsp;&nbsp;&nbsp;<a class="query" href="<?=$shipping_cfg['Query'];?>" target="_blank"><?=$c['lang_pack']['user']['query'];?></a><?php }?><br />
								<?php if($shipped_ary['Remarks'][$v]){?><?=$c['lang_pack']['user']['contents'].': '.$shipped_ary['Remarks'][$v];?><br><?php }?>
							</td>
						</tr>
						<script type="text/javascript">
						YQV5.trackSingleF1({
							YQ_ElementId:"<?=$OId.$v;?>",	//必须，指定悬浮位置的元素ID。
							YQ_Width:600,	//可选，指定查询结果宽度，最小宽度为600px，默认撑满容器。
							YQ_Height:400,	//可选，指定查询结果高度，最大高度为800px，默认撑满容器。
							YQ_Lang:"<?=$UILang;?>",	//可选，指定UI语言，默认根据浏览器自动识别。
							YQ_Num:"<?=$shipped_ary['TrackingNumber'][$v];?>"	//必须，指定要查询的单号。
						});
						</script>
				<?php
					}
				}?>
				<?php if(!(int)$payment_row['IsOnline'] || $IsWaitingPayment && $orders_row['OrderStatus']>1){?>
		            <?php 
						$orders_payment_info=db::get_one('orders_payment_info', "OrderId='{$orders_row['OrderId']}'", "*", "InfoId desc");
						?>
					<tr class="tr">
						<th><?=$c['lang_pack']['user']['paymentInfo'];?>:</th>
						<td>
							<?=$c['lang_pack']['user']['senderName'];?>: <?=$orders_payment_info['FirstName'].' '.$orders_payment_info['LastName'];?><br />
							<?=$payment_row['Method']=='MoneyGram'?$c['lang_pack']['user']['referenceNumber']:$c['lang_pack']['user']['MTCN'];?>: <?=$orders_payment_info['MTCNNumber'];?><br />
							<?=$c['lang_pack']['user']['sentMoney'];?>: <?=$orders_payment_info['Currency'].' '.sprintf('%01.2f', $orders_payment_info['SentMoney']);?> <br />
			                <?php if($orders_payment_info['Country']){?>
			                	<?=$c['lang_pack']['user']['country'];?>: <?=$orders_payment_info['Country'];?>
			                <?php }?>
						</td>
					</tr>
		        <?php } ?>
			</table>
			<div class="grand_total grand_total_chang_pay">
				<table cellpadding="0" cellspacing="0">
					<tbody>
						<tr><th></th><td></td></tr>
						<tr><th><?=$c['lang_pack']['user']['subtotal'];?>:<em><?=$orders_row['Currency'];?></em></th><td><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($ProductPrice, 0, $orders_row['Currency']);?></td></tr>
						<tr><th><?=$IsInsurance ? $c['lang_pack']['user']['insurance'] : $c['lang_pack']['cart']['shipcharge'] ;?>:<em><?=$orders_row['Currency'];?></em></th><td><?=cart::iconv_price($orders_row['ShippingPrice']+$orders_row['ShippingInsurancePrice'], 0, $orders_row['Currency']);?></td></tr>
		                <?php if($isFee && $HandingFee>0){?>
							<tr><th><?=$c['lang_pack']['user']['handingFee'];?>:<em><?=$orders_row['Currency'];?></em></th><td><?=cart::iconv_price($HandingFee, 0, $orders_row['Currency']);?></td></tr>
		                <?php }?>
						<?php
						if($orders_row['Discount']>0){ //优惠折扣 折扣形式
							$DiscountPrice=$ProductPrice-$ProductPrice*((100-$orders_row['Discount'])/100);
						?>
							<tr>
								<th>(-) <?=$c['lang_pack']['user']['discount'];?>:<em><?=$orders_row['Currency'];?></em></th>
								<td><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($DiscountPrice, 0, $orders_row['Currency']);?></td>
							</tr>
						<?php }?>
						<?php
						if($orders_row['DiscountPrice']>0){ //优惠折扣 金额形式
						?>
							<tr>
								<th>(-) <?=$c['lang_pack']['user']['discount'];?>:<em><?=$orders_row['Currency'];?></em></th>
								<td><?=cart::iconv_price($orders_row['DiscountPrice'], 0, $orders_row['Currency']);?></td>
							</tr>
						<?php }?>
						<?php
						if($orders_row['UserDiscount']>0){ //会员折扣
							$UserPrice=$ProductPrice-$ProductPrice*($orders_row['UserDiscount']/100);
						?>
							<tr>
								<th>(-) <?=$c['lang_pack']['cart']['user_save'];?>:<em><?=$orders_row['Currency'];?></em></th>
								<td><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($UserPrice, 0, $orders_row['Currency']);?></td>
							</tr>	
						<?php }?>
		                <?php
						if($orders_row['CouponCode'] && ($orders_row['CouponPrice']>0 || $orders_row['CouponDiscount']>0)){ //优惠券折扣
							$discountPrice=$orders_row['CouponPrice']>0?$orders_row['CouponPrice']:$orders_row['ProductPrice']*$orders_row['CouponDiscount'];
						?>
							<tr>
								<th>(-) <?=$c['lang_pack']['user']['couponSavings'];?>:<em><?=$orders_row['Currency'];?></em></th>
								<td><?=cart::iconv_price($discountPrice, 0, $orders_row['Currency']);?></td>
							</tr>
		                <?php }?>
						<tr><th></th><td></td></tr>
					</tbody>
					<tfoot>
						<tr>
							<th width="100%" class="totalprod"><?=$c['lang_pack']['user']['grandTotal'];?>:<em><?=$orders_row['Currency'];?></em></th>
		                    <td class="totalPrice"><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($total_price, 0, $orders_row['Currency']);?></td>
						</tr>
						<tr>
							<td colspan="2">
								<?php if($orders_row['OrderStatus']==1 || $orders_row['OrderStatus']==3){ ?>
									<a href="/cart/complete/<?=$OId;?>.html" oid="<?=$OId;?>" pid="<?=$orders_row['PId'];?>" class="pay_now edit_pay_btn"><?=$c['lang_pack']['cart']['paynow']; ?></a>
								<?php } ?>
								<?php if($orders_row['OrderStatus']==5){ ?>
									<a href="javascript:;" class="pay_now confirm_receiving" oid="<?=$OId;?>"><?=$c['lang_pack']['user']['receiving'];?></a>
								<?php } ?>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<div class="clear"></div>
		</div>
        
        <div class="clear"></div>
		<?php
		if(@substr_count($orders_row['PaymentMethod'], 'Paypal')){
		//if($orders_row['PId']==2){ //Paypal快捷支付
		?>
			<form id="paypal_checkout_form" name="paypal_checkout_form" method="POST" action="/cart/complete/<?=$OId;?>.html" class="hide">
				<input id="paypal_payment_option" value="paypal_express" type="radio" name="paymentMethod" title="PayPal Checkout" class="radio" style="display:none;" checked />
				<input type="submit" value="Submit" id="paypal_checkout_button" />
			</form>
			<script src="//www.paypalobjects.com/api/checkout.js" async></script>
			<script>
			window.paypalCheckoutReady=function(){
				paypal.checkout.setup("<?=$account['Account'];?>", {
					button:"paypal_checkout_button",
					environment:"production",
					condition:function(){
						return document.getElementById("paypal_payment_option").checked === true;
					}
				});
			};
			</script>
		<?php }?>
	</div>
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
		<div class="order_menu order_shipped_info">
			<h3 class="title"><?=$c['lang_pack']['user']['shipped_info']; ?></h3>
			<table cellpadding="0" cellspacing="0" width="100%" class="row_table">
				<thead>
					<tr>
						<th width="25">&nbsp;</th>
						<th><?=$c['lang_pack']['user']['trackNo'];?></th>
						<th><?=$c['lang_pack']['user']['status'];?></th>
						<th><?=$c['lang_pack']['user']['shippedTime'];?></th>
						<th><?=$c['lang_pack']['mobile']['notice'];?></th>
						<th><?=$c['lang_pack']['user']['tracking'];?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($shipped_ary as $k=>$v){
						$tracking_list=array();
						$api_row=db::get_one('shipping_api', "1 and AId in(select IsAPI from shipping where IsAPI>0 and SId='{$v['ShippingSId']}')");
					?>
					<tr>
						<td>&nbsp;</td>
						<td><?=$v['TrackingNumber'];?></td>
						<td><?=$c['lang_pack']['user']['OrderStatusAry'][($v['Status']==1?5:4)];?></td>
						<td><?=$v['ShippingTime']?date('F d, Y', $v['ShippingTime']):'N/A';?></td>
						<td><?=$v['Remarks'];?></td>
						<td>
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
									}else echo $c['lang_pack']['mobile']['no_data'];
								}else echo $c['lang_pack']['mobile']['no_data'];
							}else echo $c['lang_pack']['mobile']['no_data'];
							?>
						</td>
					</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	<?php }?>
	<div class="blank20"></div>
	<div class="order_menu order_summary">
		<h3 class="title"><?=$c['lang_pack']['user']['orderSummary'];?></h3>
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
		//检查此会员一个月内的产品评论订单ID
		$reviewAry=array();
		$review_orders_row=db::get_all('products_review', $c['where']['user']." and ({$c['time']}-{$c['orders']['review']})<AccTime", 'ProId, OrderId');
		foreach((array)$review_orders_row as $k=>$v){
			$reviewAry[$v['ProId']][]=$v['OrderId'];
		}
		$subtotal=0;
		?>
		<table cellpadding="0" cellspacing="0" width="100%" class="row_table">
			<thead>
				<tr>
					<th><?=$c['lang_pack']['user']['item'];?></th>
					<th class="pro_price"><?=$c['lang_pack']['user']['price'];?></th>
					<th class="pro_qty"><?=$c['lang_pack']['user']['qty'];?></th>
					<th class="pro_amount"><?=$c['lang_pack']['user']['amount'];?></th>
				</tr>
			</thead>
		</table>	
		<?php 
		$row_hd_num=0;
		foreach($order_list_ary as $OvId=>$row){
		?>
			<div class="waybill_products_list">
				<?php
				foreach($row as $key=>$val){
					$total=$amount=$quantity=0;
				?>
					<?php
					if((int)$c['config']['global']['Overseas']==1 && count($order_list_ary)>1){
						//需要开启海外仓功能才能显示
						$status=4;
					?>
						<div class="row_hd <?=$row_hd_num==0 && $key==0 ? 'fir' : ''; ?>">
							<strong><?=$c['lang_pack']['products']['shipsFrom'].': <i>'.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</i>';?></strong>
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
					<?php }?>
					<table cellpadding="0" cellspacing="0" width="100%" class="row_table">
						<tbody>
							<?php
							foreach((array)$val as $v){
								if($key=='00'){
									$qty=(int)$pro_qty_ary[$v['LId']];
								}else{
									$qty=(int)$v['Qty'];
								}
								if($qty<1) continue;
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
									$amount+=($v['Price']*$qty);
							?>
								<tr>
									<td class="pro_list">
										<h4>[ <?=$c['lang_pack']['cart']['package']?> ] <?=$package_row['Name'];?></h4>
										<?php
										foreach((array)$products_row as $k2=>$v2){
											$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
											$url=ly200::get_url($v2, 'products');
										?>
										<dl class="clearfix plist<?=$k2?'':' first';?>">
											<dt><a href="<?=$url;?>" title="<?=$v2['Name'.$c['lang']];?>" target="_blank"><img src="<?=$img;?>" alt="<?=$v2['Name'.$c['lang']];?>"></a></dt>
											<dd>
												<h5><a href="<?=$url;?>" title="<?=$v2['Name'.$c['lang']];?>" target="_blank"><?=$v2['Name'.$c['lang']];?></a></h5>
												<?=$v2['Number']!=''?'<p class="pro_attr">'.$v2['Prefix'].$v2['Number'].'</p>':'';?>
												<?php if($k2==0){?>
													<div>
														<?php
														foreach((array)$attr as $k=>$z){
															if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
															echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
														}
														if((int)$c['config']['global']['Overseas']==0 && $v['OvId']==1){
															echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
														}?>
													</div>
												<?php }elseif($data_ary[$v2['ProId']]){?>
													<div>
														<?php
														$OvId=0;
														foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
															if($k3=='Overseas'){ //发货地
																$OvId=str_replace('Ov:', '', $v3);
																if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
																echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</p>';
															}else{
																echo '<p class="attr_'.$k3.'">'.$attribute_ary[$k3][1].': '.$vid_data_ary[$k3][$v3].'</p>';
															}
														}
														if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
															echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</p>';
														}?>
													</div>
												<?php }?>
												<?php if($c['config']['products_show']['Config']['review'] && $orders_row['OrderStatus']==6 && $c['time']<$review_time && !@in_array($orders_row['OrderId'], $reviewAry[$v2['ProId']])){?><p><a class="order_btn" href="<?=ly200::get_url($v2, 'write_review')?>?OId=<?=$orders_row['OId'];?>" target="_blank"><?=$c['lang_pack']['user']['writeReview'];?></a></p><?php }?>
											</dd>
											<?=$k2?'<dd class="list_dot"></dd>':'';?>
										</dl>
										<?php 
											$total+=$qty;
										}
										?>
										<?php if($v['Remark']){?><dl><dd><p class="remark"><?=$c['lang_pack']['cart']['remark'];?>: <?=$v['Remark'];?></p></dd></dl><?php }?>
									</td>
									<td class="pro_price">
										<p>
											<?=cart::iconv_price($v['Price'], 0, $orders_row['Currency']);?>
										</p>
									</td>
									<td class="pro_qty"><span class="pro_qty">x<?=$qty;?></span></td>
									<td class="pro_amount"><span><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format(cart::iconv_price($v['Price'], 2, $orders_row['Currency'], 0)*$qty, 0, $_SESSION['Currency']['Currency']);?></span></td>
								</tr>
							<?php
								}else{
									$v['Name'.$c['lang']]=$v['Name'];
									$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
									!$attr && $attr=str::json_data($v['Property'], 'decode');
									$price=$v['Price']+$v['PropertyPrice'];
									$v['Discount']<100 && $price*=$v['Discount']/100;
									$amount+=($price*$qty);
									$url=ly200::get_url($v, 'products');
									$name=$v['Name'];
									$review_time=($orders_row['PayTime']?$orders_row['PayTime']:$orders_row['OrderTime'])+$c['orders']['review'];
									$img=@is_file($c['root_path'].$v['PicPath'])?$v['PicPath']:ly200::get_size_img($v['PicPath_0'], '240x240');
							?>
								<tr>
									<td class="pro_list">
										<dl class="clearfix">
											<dt><a href="<?=$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" title="<?=$name;?>" alt="<?=$name;?>"></a></dt>
											<dd>
												<h4><a href="<?=$url;?>" title="<?=$name;?>" target="_blank"><?=$name;?></a></h4>
												<?=$v['Number']!=''?'<p class="pro_attr">'.$v['Prefix'].$v['Number'].'</p>':'';?>
												<?php
												foreach((array)$attr as $k=>$v2){
													if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
													echo '<p class="pro_attr">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$v2.'</p>';
												}
												if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
													echo '<p class="pro_attr">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
												}?>
												<?php if($v['Remark']){?><p class="remark"><?=$c['lang_pack']['cart']['remark'];?>: <?=$v['Remark'];?></p><?php }?>
												<?php if($c['config']['products_show']['Config']['review'] && $orders_row['OrderStatus']==6 && $c['time']<$review_time && !@in_array($orders_row['OrderId'], $reviewAry[$v['ProId']])){?><p><a class="order_btn" href="<?=ly200::get_url($v, 'write_review')?>?OId=<?=$orders_row['OId'];?>" target="_blank"><?=$c['lang_pack']['user']['writeReview'];?></a></p><?php }?>
											</dd>
										</dl>
									</td>
									<td class="pro_price">
										<p>
											<?=cart::iconv_price($price, 0, $orders_row['Currency']);?>
										</p>
									</td>
									<td class="pro_qty"><?=$qty;?></td>
									<td class="pro_amount"><span><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format(cart::iconv_price($price, 2, $orders_row['Currency'], 0)*$qty, 0, $_SESSION['Currency']['Currency']);?></span></td>
								</tr>
							<?php
									$total+=$qty;
								}
							}
							$subtotal+=$total;
							unset($reviewAry);
							?>
						</tbody>
						<?php /*
						<tfoot>
							<tr>
								<td></td>
								<td class="pro_price"><p><?=$total;?></p></td>
								<td><?=cart::iconv_price($amount, 0, $orders_row['Currency']);?></td>
							</tr>
						</tfoot>*/ ?>
					</table>
				<?php }?>
			</div>
		<?php 
		$row_hd_num++;
		}?>
	</div>
</div>
<?php }?>