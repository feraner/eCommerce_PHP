<?php !isset($c) && exit();?>
<?php
$error_ary=array();

//BuyNow数据
if($_GET['Data']){
	$Data=array();
	$data_ary=explode('&', rawurldecode(base64_decode($_GET['Data'])));//Buy Now参数，已通过加密
	foreach($data_ary as $v){
		$arr=explode('=', $v);
		$Data[$arr[0]]=$arr[1];
	}
	$CId=(int)$Data['CId'];
}

//Checkout数据
if($_GET['CId']){
	$CId=$_GET['CId'];
}

$CIdStr=$cCIdStr='';
if($CId){
	$CIdStr=' and CId in('.str_replace('.', ',', $CId).')';
	$cCIdStr=' and c.CId in('.str_replace('.', ',', $CId).')';
}

//购物车产品
$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', "c.{$c['where']['cart']}{$cCIdStr}", "c.*, c.Attr as CartAttr, p.Name{$c['lang']}, p.Prefix, p.Number, p.AttrId, p.Attr, p.IsCombination", 'c.CId desc');
!count($cart_row) && js::location("/cart/");
//产品总金额
$total_price=db::get_sum('shopping_cart', $c['where']['cart'].$CIdStr, '(Price+PropertyPrice)*Discount/100*Qty');
$iconv_total_price=cart::cart_total_price($cCIdStr, 1);
//产品总重量（自身+包装）
$total_weight=cart::cart_product_weight($cCIdStr, 2);

//检查产品资料是否完整
$oversea_id_ary=array();
foreach((array)$cart_row as $v){
	if($v['BuyType']!=4 && $v['CartAttr']!='[]' && $v['CartAttr']!='{}' && $v['CartAttr']!='{"Overseas":"Ov:1"}'){
		$IsError=0;
		$prod_selected_ary=$ext_ary=array();
		$AttrAry=@str::json_data(str::attr_decode($v['CartAttr']), 'decode');
		$prod_selected_row=db::get_all('products_selected_attribute', "ProId='{$v['ProId']}' and VId>0 and IsUsed=1");
		foreach((array)$prod_selected_row as $v2){
			$prod_selected_ary[]=$v2['VId'];
		}
		if((int)$v['IsCombination'] && db::get_row_count('products_selected_attribute_combination', "ProId='{$v['ProId']}'")){//开启规格组合
			$OvId=1;
			foreach($AttrAry as $k2=>$v2){
				if($k2=='Overseas'){//发货地
					$OvId=str_replace('Ov:', '', $v2);
					(int)$c['config']['global']['Overseas']==0 && $OvId!=1 && $OvId=0;//关闭海外仓功能，发货地不是China，不能购买
				}else{
					!in_array($v2, $prod_selected_ary) && $IsError=1;
					$ext_ary[]=$v2;
				}
			}
			sort($ext_ary); //从小到大排序
			$Combination='|'.implode('|', $ext_ary).'|';
			$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$v['ProId']}' and Combination='{$Combination}' and OvId='{$OvId}'"));
		}else{
			foreach((array)$AttrAry as $k2=>$v2){
				if($k2=='Overseas') continue;
				$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$v['ProId']}' and Combination='|{$v2}|' and OvId=1"));
				$row && $PropertyPrice+=(float)$row['Price'];//固定是加价
			}
			if($v['BuyType']==3 && !$AttrAry) $row=1;//放过组合购买
		}
		if(!$row || $IsError>0) $error_ary["{$v['ProId']}_{$v['CId']}"]=1;//检查此产品是否有选择购物车属性
	}
	!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
}

if((int)$_SESSION['User']['UserId']){ //会员收货地址信息
	//收货地址
	$address_row=str::str_code(db::get_all('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['cart']." and a.IsBillingAddress=0", 'a.*, c.Country, s.States as StateName', 'a.AccTime desc, a.AId desc'));
}elseif($_SESSION['Cart']['ShippingAddress']){ //非会员收货地址信息
	$address_ary=$_SESSION['Cart']['ShippingAddress'];
	$country_val=str::str_code(db::get_value('country', "CId='{$address_ary['CId']}'", 'Country'));
	$states_val=str::str_code(db::get_value('country_states', "SId='{$address_ary['SId']}'", 'States'));
	if($country_val || $states_val){
		$address_ary['Country']=$country_val;
		$address_ary['StateName']=$states_val;
	}
	$address_row[0]=$address_ary;
	unset($address_ary);
}
//付款方式
$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', $c['my_order'].'IsOnline desc,PId asc');

$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));

//会员优惠价 与 全场满减价 比较
$AfterPrice_0=$AfterPrice_1=0;
$user_discount=100;
if((int)$_SESSION['User']['UserId'] && (int)$_SESSION['User']['Level']){//会员优惠
	$user_discount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
	$user_discount=($user_discount>0 && $user_discount<100)?$user_discount:100;
	$AfterPrice_0=$iconv_total_price-($iconv_total_price*($user_discount/100));
}
if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){//全场满减
	foreach((array)$cutArr['Data'] as $k=>$v){
		if($total_price<$k) break;
		$AfterPrice_1=($cutArr['Type']==1?cart::iconv_price($v[1], 2, '', 0):($iconv_total_price*(100-$v[0])/100));
	}
}
if($AfterPrice_0==$AfterPrice_1){//当会员优惠价和全场满减价一致，默认只保留会员优惠价
	$AfterPrice_1=0;
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

if((int)$c['config']['global']['Overseas']==0 || count($c['config']['Overseas'])<2 || count($oversea_id_ary)<2){//关闭海外仓功能 或者 仅有一个海外仓选项
?>
	<style type="text/css">.information_shipping .shipping:first-child .title{display:none;}</style>
<?php 
}
$oversea_id_hidden=$oversea_id_ary;
$NotDefualtOvId=0;
if(!in_array(1, $oversea_id_ary)){//购物车没有默认海外仓追加隐藏选项
	$oversea_id_ary[]=1;
	$NotDefualtOvId=1;
}
sort($oversea_id_ary); //排列正序
$oversea_count=count($oversea_id_ary);
?>
<script type="text/javascript">
var address_perfect=<?=(!$address_row || ($address_row && (!$address_row[0]['FirstName'] || !$address_row[0]['LastName'] || !$address_row[0]['AddressLine1'] || !$address_row[0]['City'] || !$address_row[0]['CId'] || !$address_row[0]['ZipCode'] || !$address_row[0]['PhoneNumber'])))?1:0;?>;
var address_perfect_aid=<?=$address_row?(int)$address_row[0]['AId']:0;?>;
$(document).ready(function(){
	<?=$a=='buynow'?'cart_obj.list_init();':'';?>
	cart_obj.checkout_init();
	user_obj.sign_in_init();
	<?php if($_SESSION['Cart']['ShippingAddress']){?>
		cart_obj.cart_init.checkout_no_login(<?=str::json_data($_SESSION['Cart']['ShippingAddress']);?>);
	<?php }?>
});
$('html').loginOrVisitors('<?=$_SERVER['REQUEST_URI'];?>', 0, function(){
	ueeshop_config['_login']=1;
	return false;
});
</script>
<style type="text/css">
.information_shipping .shipping:hover .icon_shipping_title, .information_shipping .current .icon_shipping_title, .information_payment .icon_shipping_title{background-color:<?=$style_data['BuyNowBgColor'];?>;}
</style>
<div id="lib_cart" class="checkout_container<?=$a=='buynow'?' buynow_content':'';?>">
	<?php include('include/header.php');?>
	<div class="checkout_content">
		<?php if((int)$_SESSION['User']['UserId']==0){?>	
			<div class="information_box information_customer">
				<div class="box_title"><?=$c['lang_pack']['cart']['customerInfo'];?></div>
				<div class="box_content">
					<label class="input_box">
						<span class="input_box_label"><?=$c['lang_pack']['mobile']['enter_email'];?></span>
						<input type="text" class="input_box_txt elmbBlur" name="Email" placeholder="<?=$c['lang_pack']['mobile']['enter_email'];?>" maxlength="200" />
					</label>
					<p class="error"></p>
					<div class="information_login"><?=$c['lang_pack']['cart']['already'];?> <a href="javascript:;" class="SignInButton btn_signin"><?=$c['lang_pack']['cart']['login'];?></a></div>
					<input type="hidden" name="jumpUrl" value="<?=$_SERVER['REQUEST_URI'];?>" />
				</div>
			</div>
		<?php }?>
		<div class="information_box information_address">
			<div class="box_title"><?=$c['lang_pack']['cart']['shippingInfo'];?></div>
			<div class="box_content">
				<?php if((int)$_SESSION['User']['UserId']){?>
					<div class="address_button clearfix">
						<a href="javascript:;" class="btn_address_add" id="addAddress"><?=$c['lang_pack']['cart']['add'];?></a><i>|</i><a href="javascript:;" class="btn_address_more" id="moreAddress"><?=$c['lang_pack']['cart']['moreAddress'];?></a>
					</div>
				<?php }?>
				<div class="address_default item clearfix"></div>
				<div class="address_list clearfix">
					<?php
					if(count($address_row)>0){
						foreach((array)$address_row as $k=>$v){
					?>
						<div class="item<?=$k%2==0?' odd':'';?>">
							<input type="radio" name="shipping_address_id" id="address_<?=$v['AId'];?>" value="<?=$v['AId'];?>" data-cid="<?=$v['CId'];?>" />
							<p class="clearfix"><strong><?=$v['FirstName'].' '.$v['LastName'];?></strong><a href="javascript:;" class="edit_address_info"><?=$c['lang_pack']['cart']['edit'];?></a></p>
							<p class="address_line"><?=$v['AddressLine1'].' '.($v['AddressLine2']?$v['AddressLine2'].' ':'');?></p>
							<p><?=$v['City'].($v['StateName']?$v['StateName']:$v['State']).' '.$v['Country'].' ('.$v['ZipCode'].')';?></p>
							<p>+<?=$v['CountryCode'].' '.$v['PhoneNumber'];?></p>
						</div>
					<?php 
						}
					}else
					?>
				</div>
				<div id="addressInfo" style="display:none;"></div>
				<div id="ShipAddrFrom"><?php include('include/shippingAddress.php');?></div>
			</div>
		</div>
		<div class="information_box information_shipping">
			<div class="box_title<?=$oversea_count==1?' no_border':'';?>"><?=$c['lang_pack']['cart']['delivery'];?></div>
			<div class="box_content">
				<?php foreach($oversea_id_ary as $k=>$v){?>
					<div class="shipping<?=($NotDefualtOvId && (int)$v==1)?' hide':'';?>" data-id="<?=$v;?>">
						<div class="title">
							<strong><?=$c['lang_pack']['products']['shipsFrom'].' '.$c['config']['Overseas'][$v]['Name'.$c['lang']];?></strong>
							<div class="shipping_info"><span class="error"></span><span class="name"></span><span class="price"></span></div>
							<i class="icon_shipping_title"></i>
						</div>
						<div class="list">
							<ul class="shipping_method_list clearfix"></ul>
							<?php if($IsInsurance){?>
								<div class="insurance">
									<span class="name"><?=$c['lang_pack']['cart']['insurance'];?>:</span>
									<input type="checkbox" name="_shipping_method_insurance" class="shipping_insurance" value="1" checked />
									<label for="shipping_insurance"><?=$c['lang_pack']['cart']['add_insur'];?></label>
									<a href="javascript:;" class="delivery_ins" content="<?=$c['lang_pack']['cart']['tips_insur']?>"><?=$c['lang_pack']['cart']['why_insur'];?></a>
									<span class="price"><?=$_SESSION['Currency']['Symbol'];?><em></em></span>
								</div>
							<?php }?>
						</div>
					</div>
				<?php }?>
				<div class="tips"><?=$c['lang_pack']['cart']['arrive'];?></div>
				<div class="editor_txt"><?=str::str_code($c['config']['global']['ArrivalInfo']['ArrivalInfo'.$c['lang']], 'htmlspecialchars_decode');?></div>
			</div>
		</div>
		<div class="information_box information_payment">
			<div class="box_title"><?=$c['lang_pack']['cart']['paymethod'];?></div>
			<div class="box_content">
				<?php
				$payment_count=count($payment_row);
				$pages=ceil($payment_count/7);
				for($i=0; $i<$pages; ++$i){
				?>
					<div class="payment_list clearfix" style="display:<?=$i==0?'block':'none';?>;">
						<?php
						for($j=$i*7; $j<($i+1)*7; ++$j){
							if($j>=$payment_count) break;
						?>
							<div class="payment_row" value="<?=$payment_row[$j]['PId'];?>" min="<?=$payment_row[$j]['MinPrice'];?>" max="<?=$payment_row[$j]['MaxPrice'];?>">
								<div class="check">&nbsp;<input name="PId" type="radio" /></div>
								<div class="img"><img src="<?=$payment_row[$j]['LogoPath'];?>" alt="<?=$payment_row[$j]['Name'.$c['lang']];?>"><span></span></div>
								<em class="icon_dot"></em>
								<div class="clear"></div>
							</div>
						<?php }?>
						<?php if($i==0 && $payment_count>7){?><i class="icon_shipping_title"></i><?php }?>
					</div>
					<div class="payment_contents clearfix" style="display:<?=$i==0?'block':'none';?>;">
						<?php
						for($j=$i*7; $j<($i+1)*7; ++$j){
							if($j>=$payment_count) break;
						?>
							<div class="payment_note" data-id="<?=$payment_row[$j]['PId'];?>" data-fee="<?=$payment_row[$j]['AdditionalFee'];?>" data-affix="<?=cart::iconv_price($payment_row[$j]['AffixPrice'], 2, '', 0);?>">
								<div class="name"><?=$payment_row[$j]['Name'.$c['lang']];?></div>
								<?php if($payment_row[$j]['Description'.$c['lang']]){?><div class="ext_txt"><?=$payment_row[$j]['Description'.$c['lang']]?></div><?php }?>
							</div>
						<?php }?>
					</div>
				<?php }?>
			</div>
		</div>
		<div class="information_box information_products">
			<div class="box_title"><?=$c['lang_pack']['cart']['products'];?></div>
			<div class="box_content information_product cartFrom">
				<table class="itemFrom">
					<thead>
						<tr>
							<th class="item_select item_header hide">
								<em class="btn_checkbox FontBgColor<?=!count($error_ary)?' current':'';?>"></em>
								<input type="checkbox" name="select_all" value="" class="va_m"<?=!count($error_ary)?' checked':'';?> />
							</th>
							<th class="item_product item_header"><?=$c['lang_pack']['cart']['item'];?></th>
							<th class="item_price item_header"><?=$c['lang_pack']['cart']['price'];?></th>
							<th class="item_quantity item_header"><?=$c['lang_pack']['cart']['qty'];?></th>
							<th class="item_operate item_header"><?=$c['lang_pack']['cart']['amount'];?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$total_price=$s_total_price=$quantity=0;
						$cart_attr=$cart_attr_data=$cart_attr_value=array();
						foreach((array)$cart_row as $v){
							$is_error=$error_ary["{$v['ProId']}_{$v['CId']}"]!=''?1:0;
							if($is_error==1) continue;
							if($v['BuyType']==4){
								//组合促销
								$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
								if(!$package_row) continue;
								$attr=array();
								$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
								!$attr && $attr=str::json_data(htmlspecialchars_decode($v['Property']), 'decode');
								$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
								$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
								$pro_where=='' && $pro_where=0;
								$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
								$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
							}else{
								//普通产品
								$attr=array();
								$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
								!$attr && $attr=str::json_data(htmlspecialchars_decode($v['Property']), 'decode');
								$attr_len=count($attr);
								$oversea_len=db::get_row_count('products_selected_attribute', "ProId='{$v['ProId']}' and AttrId=0 and VId=0 and OvId>1 and IsUsed=1");
								(int)$c['config']['global']['Overseas']==0 && $attr_len==1 && $attr['Overseas'] && $attr_len-=1;
								$img=ly200::get_size_img($v['PicPath'], '240x240');
								$url=ly200::get_url($v, 'products');
							}
							$price=$v['Price']+$v['PropertyPrice'];
							$v['Discount']<100 && $price*=$v['Discount']/100;
							$s_total_price+=$price*$v['Qty'];
							$total_price+=cart::iconv_price($price, 2, '', 0)*$v['Qty'];
							$quantity+=$v['Qty'];
						?>
							<tr cid="<?=$v['CId'];?>"<?=$is_error==1?' class="error"':'';?>>
								<td class="prod_select hide">
									<em class="btn_checkbox FontBgColor current"></em>
									<input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m<?=$is_error==1?' null':'';?>"<?=$is_error==1?' disabled':'';?> checked />
								</td>
								<td class="prod_info_detail">
									<?php
									if($v['BuyType']==4){
										//组合促销
										echo '<strong>[ '.$c['lang_pack']['cart']['package'].' ] '.$package_row['Name'].'</strong>';
										foreach((array)$products_row as $k2=>$v2){
											$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
											$url=ly200::get_url($v2, 'products');
									?>
										<dl class="clearfix pro_list<?=$k2?'':' first';?>">
											<dt class="prod_pic"><a href="<?=$url;?>" title="<?=$v2['Name'.$c['lang']];?>" class="pic_box"><img src="<?=$img;?>" alt="<?=$v2['Name'.$c['lang']];?>" /><span></span></a></dt>
											<dd class="prod_info">
												<div class="invalid FontBgColor"><?=$c['lang_pack']['cart']['invalid'];?></div>
												<h4 class="prod_name"><a href="<?=$url;?>"><?=$v2['Name'.$c['lang']];?></a></h4>
												<?php
												if($k2==0){ //主产品
													echo '<div>';
													foreach((array)$attr as $k=>$z){
														if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
														echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
													}
													if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
														echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
													}
													echo '</div>';
												}elseif($data_ary[$v2['ProId']]){ //捆绑产品
													echo '<div>';
													$OvId=1;
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
													}
													echo '</div>';
												}?>
											</dd>
											<?=$k2?'<dd class="prod_dot"></dd>':'';?>
										</dl>
									<?php
										}
									}else{
										//普通产品
									?>
										<dl>
											<dt class="prod_pic"><a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>" class="pic_box"><img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span></a></dt>
											<dd class="prod_info">
												<div class="invalid FontBgColor"><?=$c['lang_pack']['cart']['invalid'];?></div>
												<h4 class="prod_name"><a href="<?=$url;?>"><?=$v['Name'.$c['lang']];?></a></h4>
												<p class="prod_number"><?=$v['Prefix'].$v['Number'];?></p>
												<?php
												if($attr_len){
													echo '<div>';
														foreach((array)$attr as $k=>$z){
															if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
															echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
														}
														if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
															echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
														}
													echo '</div>';
												}elseif((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
													echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
												}
												if($a=='buynow'){//BuyNow页面
													echo '<p class="remark">'.$c['lang_pack']['cart']['remark'].': <input type="text" name="Remark[]" value="" maxlength="200" cid="'.$v['CId'].'" proid="'.$v['ProId'].'" /></p>';
												}else{//Checkout页面
													echo '<p class="remark" style="display:'.($v['Remark']?'block':'none').';">'.$c['lang_pack']['cart']['remark'].': <span>'.htmlspecialchars($v['Remark']).'</span></p>';
												}?>
											</dd>
										</dl>
									<?php }?>
								</td>	
								<td class="prod_price"><p price="<?=$v['Price'];?>" discount="<?=$v['Discount'];?>"><?=cart::iconv_price($price);?></p></td>
								<td class="prod_quantity" start="<?=$v['StartFrom'];?>">
									<?php if($a=='buynow'){//BuyNow页面?>
										<?php if($v['BuyType']==4){?>
											<div class="quantity_box clearfix">
												<div class="cut">-</div>
												<div class="qty"><input type="text" name="Qty[]" value="<?=$v['Qty'];?>" maxlength="4" disabled /></div>
												<div class="add">+</div>
											</div>
										<?php }else{?>
											<div class="quantity_box clearfix">
												<div class="cut">-</div>
												<div class="qty"><input type="text" name="Qty[]" value="<?=$v['Qty'];?>" maxlength="4"<?=$is_error==1?' disabled':'';?> /></div>
												<div class="add">+</div>
											</div>
										<?php }?>
										<input type="hidden" name="S_Qty[]" value="<?=$v['Qty'];?>" />
										<input type="hidden" name="CId[]" value="<?=$v['CId'];?>" />
										<input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
									<?php }else{//Checkout页面?>
										<p><?=$v['Qty'];?></p>
									<?php }?>
								</td>
								<td class="prod_operate">
									<p price="<?=cart::iconv_price($price, 2, '', 0)*$v['Qty'];?>"><?=cart::iconv_price(0, 1).cart::currency_format(cart::iconv_price($price, 2, '', 0)*$v['Qty'], 0, $_SESSION['Currency']['Currency']);?></p>
								</td>
							</tr>
						<?php }?>
					</tbody>
				</table>
			</div>
			<div class="order_summary clearfix">
				<div class="coupon_box fl">
					<div class="code_input clearfix">
						<input type="text" name="couponCode" class="box_input" placeholder="<?=$c['lang_pack']['mobile']['apply'].' '.$c['lang_pack']['mobile']['coupon_code'];?>" autocomplete="off" /><div class="btn_coupon_submit btn_global sys_shadow_button FontBgColor" id="coupon_apply"><?=$c['lang_pack']['submit'];?></div>
					</div>
					<p class="code_error"><?=$c['lang_pack']['cart']['coupon_error'];?></p>
					<div class="code_valid clearfix" id="code_valid">
						<div class="code_valid_key"></div>
						<div class="code_valid_content"><?=$c['lang_pack']['mobile']['code'];?>: <strong></strong><br /><?=$c['lang_pack']['mobile']['exp_date'];?>: <strong></strong></div>
						<a href="javascript:;" class="btn_coupon_remove sys_shadow_button" id="removeCoupon">X</a>
					</div>
				</div>
				<div class="amount_box fr">
					<?php if($c['config']['global']['CartWeight']){?>
						<div class="rows clearfix">
							<div class="name"><?=$c['lang_pack']['cart']['weight'];?> (KG):</div>
							<div class="value" id="ot_weight"><?=sprintf('%01.3f', $total_weight);?></div>
						</div>
					<?php }?>
					<div class="rows clearfix">
						<div class="name"><?=$c['lang_pack']['cart']['subtotal'];?>:</div>
						<div class="value"><em><?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_subtotal"><?=cart::currency_format($iconv_total_price, 0, $_SESSION['Currency']['Currency']);?></span></div>
					</div>
					<?php if(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)){?>
						<div class="rows clearfix" id="MemberCharge">
							<div class="name"><?=$c['lang_pack']['cart']['user_save'];?>:</div>
							<div class="value"><em>- <?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_user"><?=cart::currency_format($AfterPrice_0, 0, $_SESSION['Currency']['Currency']);?></span></div>
						</div>
					<?php }?>
					<div class="rows clearfix" id="ShippingCharge">
						<div class="name"><?=$c['lang_pack']['cart']['shipcharge'];?>:</div>
						<div class="value"><em><?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_shipping"></span></div>
					</div>
					<div class="rows clearfix" id="ShippingInsuranceCombine">
						<div class="name"><?=$c['lang_pack']['cart']['ship_insur'];?>:</div>
						<div class="value"><em><?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_combine_shippnig_insurance"></span></div>
					</div>
					<div class="rows clearfix" id="CouponCharge">
						<div class="name"><?=$c['lang_pack']['cart']['code_save'];?>:</div>
						<div class="value"><em>- <?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_coupon"></span></div>
					</div>
					<?php if(($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)){?>
						<div class="rows clearfix" id="DiscountCharge">
							<div class="name"><?=$c['lang_pack']['cart']['save'];?>:</div>
							<div class="value"><em>- <?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_subtotal_discount"><?=cart::currency_format($AfterPrice_1, 0, $_SESSION['Currency']['Currency']);?></span></div>
						</div>
					<?php }?>
					<div class="rows clearfix" id="ServiceCharge">
						<div class="name"><?=$c['lang_pack']['cart']['fee'];?>:</div>
						<div class="value"><em><?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_fee" data-fee="" data-affix=""></span></div>
					</div>
					<div class="rows clearfix" id="TotalCharge">
						<div class="name"><?=$c['lang_pack']['cart']['grand'];?>:</div>
						<div class="value"><em><?=$_SESSION['Currency']['Symbol'];?></em><span id="ot_total"></span></div>
					</div>
					<form id="PlaceOrderFrom" method="post" action="/cart/" amountPrice="<?=$iconv_total_price;?>"<?=(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1))?' userPrice="'.$AfterPrice_0.'" userRatio="'.$user_discount.'"':' userPrice="0" userRatio="100"';?>>
						<input type="button" value="<?=$c['lang_pack']['mobile']['place_order'];?>" class="btn_place_order btn_global sys_shadow_button" id="orderFormSubmit" />
						<input type="hidden" name="order_coupon_code" value="<?=$_SESSION['Cart']['Coupon'];?>" cutprice="0" />
						<input type="hidden" name="order_discount_price" value="<?=($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)?$AfterPrice_1:0;?>" />
						<input type="hidden" name="order_shipping_address_aid" value="-1" />
						<input type="hidden" name="order_shipping_address_cid" value="-1" />
						<input type="hidden" name="order_shipping_method_sid" value="[]" />
						<input type="hidden" name="order_shipping_method_type" value="[]" />
						<input type="hidden" name="order_shipping_price" value="[]" />
						<input type="hidden" name="order_shipping_insurance" value="[]" price="[]" />
						<input type="hidden" name="order_shipping_oversea" value="<?=$oversea_id_hidden?implode(',', $oversea_id_hidden):'';?>" />
						<input type="hidden" name="order_payment_method_pid" value="-1" />
						<?php if($CId){?><input type="hidden" name="order_cid" value="<?=$CId;?>" /><?php }?>
					</form>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="payment_ready">
	<div class="load">
		<div class="load_payment"><div class="load_image"></div><div class="load_loader"></div></div>
	</div>
	<div class="info">
		<p><?=$c['lang_pack']['cart']['payment_tip_0'];?></p><p><?=$c['lang_pack']['cart']['payment_tip_1'];?></p>
	</div>
</div>