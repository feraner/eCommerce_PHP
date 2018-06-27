<?php !isset($c) && exit();?>
<style type="text/css">
.cart_step>div.current{background-color:<?=$style_data['FontColor'];?>;}
.cart_step>div.current>i{border-color:transparent transparent transparent <?=$style_data['FontColor'];?>;}

.itemFrom .prod_info_detail .prod_info .prod_attr .prod_attr_mod{background-color:<?=$style_data['FontColor'];?>;}
.itemFrom tbody tr:hover .prod_info_detail .prod_info .prod_attr{border-color:<?=$style_data['FontColor'];?>;}

.itemFrom .operate_box .operate_item .operate_txt{background-color:<?=$style_data['FontColor'];?>;}
.itemFrom .operate_box .operate_item:hover{background-color:<?=$style_data['FontColor'];?>;}

.itemFrom .operate_box .operate_remark .operate_txt{border-color:<?=$style_data['FontColor'];?>;}
.itemFrom .operate_box .operate_remark:hover{background-color:#fff; border-color:<?=$style_data['FontColor'];?>;}
.itemFrom .operate_box .operate_remark:hover>i{border-color:<?=$style_data['FontColor'];?>;}
</style>
<script type="text/javascript">
$(document).ready(function(){
	cart_obj.list_init();
});
</script>
<div id="lib_cart" class="wide">
	<?php
	$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', 'c.'.$c['where']['cart'], "c.*, p.Name{$c['lang']}, p.Prefix, p.Number", 'c.CId desc');
	if(count($cart_row)){
		//购物车列表产品
		$ProIdStr='0';
		$attribute_cart_ary=$vid_data_ary=$error_ary=$favourite_ary=array();
		$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
		foreach($attribute_row as $v){ $attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]); }
		$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc'));//属性选项
		foreach($value_row as $v){ $vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"]; }
		foreach((array)$cart_row as $v){//检查产品资料是否完整
			if($v['ProId'] && !$v['Name'.$c['lang']] && !$v['Number']){//产品资料丢失
				$error_ary["{$v['ProId']}_{$v['CId']}"]=1;
			}
			$ProIdStr.=",{$v['ProId']}";
		}
		$favourite_row=db::get_all('user_favorite', "UserId='{$_SESSION['User']['UserId']}' and ProId in($ProIdStr)", 'ProId');//收藏产品数据
		foreach((array)$favourite_row as $v){ $favourite_ary[$v['ProId']]=1; }
	?>
		<div class="cart_step clearfix">
			<?php for($i=0; $i<3; ++$i){?>
				<div class="step_<?=$i.($i==0?' current':'');?>"><?=($i>0?'<em></em>':'').'<b>'.($i+1).'</b>'.$c['lang_pack']['cart']['step_ary'][$i].($i<2?'<i></i>':'');?></div>
			<?php }?>
		</div>
		<div class="list_content clearfix">
			<form name="shopping_cart" class="cartFrom">
				<div class="list_information">
					<table class="itemFrom">
						<thead>
							<tr>
								<th class="item_select item_header">
									<em class="btn_checkbox FontBgColor<?=!count($error_ary)?' current':'';?>"></em>
									<input type="checkbox" name="select_all" value="" class="va_m"<?=!count($error_ary)?' checked':'';?> />
								</th>
								<th class="item_product item_header"><?=$c['lang_pack']['cart']['item'];?></th>
								<th class="item_price item_header"><?=$c['lang_pack']['cart']['price'];?></th>
								<th class="item_quantity item_header"><?=$c['lang_pack']['cart']['qty'];?></th>
								<th class="item_operate item_header"><?=$c['lang_pack']['mobile']['summary'];?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$total_price=$s_total_price=$quantity=0;
							$cart_attr=$cart_attr_data=$cart_attr_value=array();
							foreach((array)$cart_row as $v){
								$is_error=$error_ary["{$v['ProId']}_{$v['CId']}"]!=''?1:0;
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
									<td class="prod_select">
										<em class="btn_checkbox FontBgColor"></em>
										<input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m<?=$is_error==1?' null':'';?>"<?=$is_error==1?' disabled':'';?> />
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
														echo '<div class="prod_attr">';
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
													echo '<p class="remark" style="display:'.($v['Remark']?'block':'none').';">'.$c['lang_pack']['cart']['remark'].': <span>'.htmlspecialchars($v['Remark']).'</span></p>';
													?>
													<div class="prod_edit"></div>
												</dd>
											</dl>
										<?php }?>
									</td>	
									<td class="prod_price"><p price="<?=$v['Price'];?>" discount="<?=$v['Discount'];?>"><?=cart::iconv_price($price);?></p></td>
									<td class="prod_quantity" start="<?=$v['StartFrom'];?>">
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
									</td>
									<td class="prod_operate">
										<p price="<?=cart::iconv_price($price, 2, '', 0)*$v['Qty'];?>"><?=cart::iconv_price(0, 1).cart::currency_format(cart::iconv_price($price, 2, '', 0)*$v['Qty'], 0, $_SESSION['Currency']['Currency']);?></p>
										<div class="operate_box">
											<div class="operate_delete operate_item clearfix" data-url="/cart/remove_c<?=sprintf('%04d', $v['CId']);?>.html"></div>
											<?php if($is_error==0){?>
												<div class="operate_wish operate_item clearfix<?=$favourite_ary[$v['ProId']]==1?' current':'';?>" data-proid="<?=$v['ProId'];?>"></div>
												<div class="operate_edit operate_item clearfix"></div>
											<?php }?>
										</div>
									</td>
								</tr>
							<?php }?>
						</tbody>
						<tfoot>
							<tr>
								<td>&nbsp;</td>
								<td colspan="4">
									<div class="button_box fl">
										<a href="javascript:;" class="btn_remove btn_global sys_bg_button" rel="nofollow"><?=$c['lang_pack']['cart']['remove'];?></a>
										<?php if(count($error_ary)>0){?>|<a href="javascript:;" class="btn_remove_invalid" rel="nofollow"><?=$c['lang_pack']['cart']['re_invalid'];?></a><?php }?>
									</div>
									<div class="clear"></div>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
				<div class="list_summary">
					<div class="list_summary_title"><?=$c['lang_pack']['cart']['orderSummary'];?></div>
					<div class="product_price_container">
						<?php
						//会员优惠价 与 全场满减价 比较
						$cutprice=$AfterPrice_0=$AfterPrice_1=0;
						$user_discount=100;
						if((int)$_SESSION['User']['UserId'] && (int)$_SESSION['User']['Level']){
							$user_discount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
							$user_discount=($user_discount>0 && $user_discount<100)?$user_discount:100;
							$AfterPrice_0=$total_price-($total_price*($user_discount/100));
						}
						if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
							foreach((array)$cutArr['Data'] as $k=>$v){
								if($s_total_price<$k) break;
								$AfterPrice_1=($cutArr['Type']==1?cart::iconv_price($v[1], 2, '', 0):($total_price*(100-$v[0])/100));
							}
						}
						(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)) && $cutprice=$AfterPrice_0;//会员优惠价
						(($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)) && $cutprice=$AfterPrice_1;//全场满减价
						?>
						<div class="product_price_info total_box clearfix" style="display:<?=$cutprice?'block':'none';?>;">
							<div class="product_price_title"><?=$c['lang_pack']['cart']['total']?></div>
							<div class="product_price_value"><?=cart::iconv_price(0, 1).cart::currency_format($total_price, 0, $_SESSION['Currency']['Currency']);?></div>
						</div>
						<div class="product_price_info cutprice_box clearfix" style="display:<?=$cutprice?'block':'none';?>;"<?=$AfterPrice_0?' userPrice="'.$AfterPrice_0.'" userRatio="'.$user_discount.'"':' userPrice="0" userRatio="100"';?>>
							<div class="product_price_title"><?=$c['lang_pack']['user']['discount']?>(-)</div>
							<div class="product_price_value"><?=cart::iconv_price(0, 1).cart::currency_format($cutprice, 0, $_SESSION['Currency']['Currency']);?></div>
						</div>
						<div class="product_price_info product_total_price clearfix">
							<div class="product_price_title"><label><?=$c['lang_pack']['mobile']['total'];?></label><?php /* (<span class="product_count"><?=str_replace('%num%', $quantity, $c['lang_pack']['cart'][($quantity>1?'itemsCount':'itemCount')]);?></span>)*/?></div>
							<div class="product_price_value"><strong><?=cart::iconv_price(0, 1).cart::currency_format($total_price-$cutprice, 0, $_SESSION['Currency']['Currency']);?></strong></div>
						</div>
						<div class="button_info">
							<?php 
							if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
								foreach((array)$cutArr['Data'] as $k=>$v){
									if($s_total_price<$k){
										$show_price = $k;
										$show_coupon = $cutArr['Type']==1?($c['lang']=='_en'?cart::iconv_price($v[1]).' discount':'-'.cart::iconv_price($v[1])):$v[0].'% off';
										break;
									}
								}
							}
							$fullcoupon = cart::iconv_price($show_price-$s_total_price);
							?>
							<div class="c_tips tips_tit fullcoupon" style="display: <?=$show_price ? 'block' : 'none'; ?>;"><?=str_replace(array('%price%','%off%'), array($fullcoupon,$show_coupon), $c['lang_pack']['cart']['fulldiscount']); ?></div>
							<?php
							$difference=0;
							if((int)$c['config']['global']['LowConsumption']){
								$low_price=cart::iconv_price($c['config']['global']['LowPrice'], 2, '', 0);
								$low_total_price=$total_price-$cutprice;
								if($low_total_price<$low_price){//未达到最低消费金额
									$difference=$low_price-$low_total_price;
								}
							}
							?>
							<?php /*<div class="tips tips_tit" style="display:<?=$difference>0?'block':'none';?>;"><?=$c['lang_pack']['cart']['consump_tit']; ?></div>*/ ?>
							<div class="tips c_tips" style="display:<?=$difference>0?'block':'none';?>;">
								<?=str_replace(array('%price_0%', '%price_1%'), array('<span class="price_0">'.cart::iconv_price(0, 1).cart::currency_format($low_price, 0, $_SESSION['Currency']['Currency']).'</span>', '<span class="price_1">'.cart::iconv_price(0, 1).cart::currency_format($difference, 0, $_SESSION['Currency']['Currency']).'</span>'), $c['lang_pack']['cart']['consumption']);?>
							</div>
							<div class="clear"></div>
							<a href="javascript:;" class="btn_checkout btn_global sys_shadow_button" rel="nofollow"><?=$c['lang_pack']['checkout'];?></a>
							<?php if($a && (int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1")){?>
								<a href="javascript:;" class="btn_paypal_checkout btn_global sys_shadow_button" rel="nofollow"><?=$c['lang_pack']['checkout'];?></a>
								<script src="//www.paypalobjects.com/api/checkout.js" async></script>
							<?php }?>
							<a href="javascript:;" class="btn_continue" rel="nofollow"><?=$c['lang_pack']['cart']['continue'];?></a>
						</div>
					</div>
				</div>
				<input type="hidden" name="CartProductPrice" value="<?=$total_price;?>" />
				<input type="hidden" name="DiscountPrice" value="<?=$cutprice;?>" data-type="<?=$cutArr['Type'];?>" data-value="<?=($cutArr && $cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime'])?htmlspecialchars(str::json_data($cutArr['Data'])):'[]';?>" />
				<input type="hidden" value="<?=$low_price;?>" id="low_price_hidden" />
			</form>
		</div>
		<div class="clear"></div>
	<?php
	}else{
		//购物车为空
	?>
		<div class="cart_box cart_empty">
			<div class="contents clearfix">
				<h3><?=$c['lang_pack']['cart']['empty'];?></h3>
				<a href="/" class="btn_continue_shopping" title="<?=$c['lang_pack']['cart']['continue'];?>"><?=$c['lang_pack']['cart']['continue'];?></a>
			</div>
		</div>
	<?php
	}
	if($_COOKIE['history']){
		//历史浏览产品
		krsort($_COOKIE['history']);
	?>
		<div class="cart_box_divide"></div>
		<div class="cart_box cart_prod cart_history">
			<div class="title"><?=$c['lang_pack']['cart']['history'];?></div>
			<div class="contents clearfix">
				<?php
				$i=0;
				foreach((array)$_COOKIE['history'] as $v){
					if(is_file($c['root_path'].$v['PicPath']) && $i<5){
						$url=ly200::get_url($v, 'products');
						$img=ly200::get_size_img($v['PicPath'], '240x240');
						$name=stripslashes($v['Name']);
						$price=$v['Price'];
						$oldprice=$v['OldPrice'];
				?>
						<dl class="pro_item fl<?=$i%5==0?' first':'';?>">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
							<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="price">
								<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price;?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price, 2);?></span>
								<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$oldprice;?>"><?=cart::iconv_price($oldprice, 2);?></span></del><?php }?>
							</dd>
						</dl>
				<?php
						++$i;
					}
				}?>
			</div>
		</div>
	<?php }?>
	<div class="cart_box_divide"></div>
	<div class="cart_box cart_prod">
		<div class="title">
			<a href="javascript:;" data-type="0"><?=$c['lang_pack']['cart']['sLikeProd'];?></a>|<a href="javascript:;" data-type="1"><?=$c['lang_pack']['hot_sale'];?></a>|<a href="javascript:;"data-type="2"><?=$c['lang_pack']['new_arrival'];?></a>
		</div>
		<div class="contents clearfix">
			<?php 
			$w_ary=array('1', 'IsHot=1', 'IsNew=1');
			foreach((array)$w_ary as $key=>$val){
			?>
				<div class="pro_list clearfix">
					<?php
					$key==0 && $products_list_row=orders::you_may_also_like(0, 10);
					$key>0 && $products_list_row=str::str_code(db::get_limit('products', $val.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 10));
					foreach((array)$products_list_row as $k=>$v){
						$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
						$url=ly200::get_url($v, 'products');
						$img=ly200::get_size_img($v['PicPath_0'], '240x240');
						$name=$v['Name'.$c['lang']];
						$price_ary=cart::range_price_ext($v);
						$price_0=$v["Price_{$is_promition}"];
					?>
						<dl class="pro_item fl<?=$k%5==0?' first':'';?>">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
							<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="price">
								<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
								<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$price_0;?>"><?=cart::iconv_price($price_0, 2);?></span></del><?php }?>
							</dd>
						</dl>
					<?php }?>
				</div>
			<?php }?>
		</div>
	</div>
</div>
