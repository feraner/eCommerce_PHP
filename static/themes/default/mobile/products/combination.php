<?php !isset($c) && exit();?>
<?php
$proid_ary=array();
foreach((array)$group_promotion_ary as $v){
	foreach($v['data'] as $key=>$val){
		$proid_ary[]=$val['ProId'];
		$pakeageId=@explode('|', trim($val['PackageProId'], '|'));
		$proid_ary=@array_merge($proid_ary, (array)$pakeageId);
	}
}
$proid_list=@implode(',', $proid_ary);
!$proid_list && $proid_list="-1";

//所有产品属性
$parent_ary=$c_all_attr_ary=$c_all_value_ary=$c_selected_ary=$vid_data_ary=$combinatin_ary=$ext_ary=array();
$arrtid_list='-1';
$cate_row=db::get_all('products_category', "CateId in(select CateId from products where ProId in($proid_list))", 'UId, AttrId, CateId');
foreach($cate_row as $v){
	if($v['CateId']){
		$_attr_id=(int)$v['AttrId'];
		if($v['UId']!='0,'){
			$TopCateId=category::get_top_CateId_by_UId($v['UId']);//寻找顶级分类
			$TopCateId && $_attr_id=(int)db::get_value('products_category', "CateId='$TopCateId'", 'AttrId');
		}
		$arrtid_list.=",{$_attr_id}";
	}
}

$cart_attr_row=str::str_code(db::get_all('products_attribute', "CartAttr=1 and (ParentId in($arrtid_list))", "AttrId, Name{$c['lang']}, ParentId", $c['my_order'].'AttrId asc')); //所有购物车属性
$_attribute_value_where='-1';
foreach((array)$cart_attr_row as $v){
	$parent_ary[$v['ParentId']][]=$v['AttrId'];
	$c_all_attr_ary[$v['AttrId']]=$v;
	$_attribute_value_where.=",{$v['AttrId']}";
}
$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in($_attribute_value_where)", '*', $c['my_order'].'VId asc')); //所有属性选项
foreach($value_row as $v){
	$c_all_value_ary[$v['AttrId']][$v['VId']]=$v;
	$vid_data_ary[$v['VId']]=$v;
}
$selected_row=db::get_all('products_selected_attribute', "IsUsed=1 and AttrId in($_attribute_value_where, 0)", 'SeleteId, ProId, AttrId, VId, OvId', 'SeleteId asc');
foreach($selected_row as $v){
	if($v['AttrId']==0 && $v['VId']==0 && $v['OvId']>=0){//记录勾选属性ID 发货地
		$c_selected_ary[$v['ProId']]['Overseas'][]=$v['OvId'];
	}else{
		$c_selected_ary[$v['ProId']]['Id'][$v['AttrId']][]=$v['VId'];
	}
}
//属性组合数据 Start
$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId in($proid_list)", '*', 'CId asc'));
foreach($combinatin_row as $v){
	$combinatin_ary[$v['ProId']][$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
	$key=str_replace('|', '_', substr($v['Combination'], 1, -1));
	$key.=($key?'_':'').'Ov:'.$v['OvId'];
	$ext_ary[$v['ProId']][$key]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
}
//属性组合数据 End
?>
<section id="detail_sale_layer" class="prod_layer">
	<nav class="layer_head ui_border_b">
		<a class="layer_back" href="javascript:;"><em><i></i></em></a>
		<div class="layer_title"></div>
		<div class="detail_sale_menu box_select">
			<select name="Promotion" id="promotion_menu">
				<?php
				//选项栏
				$i=0;
				foreach((array)$group_promotion_ary as $key=>$val){
					foreach((array)$val['data'] as $k=>$v){
						if(!$v) continue;
						$name=$v['Name'];
						if(!$name) $name=(int)$v['Type']?$c['lang_pack']['products']['sales']:$c['lang_pack']['products']['group'];
				?>
				<option value="" data="<?=(int)$v['Type']?'promotion':'purchase';?>" data-id="<?=$i;?>"><?=$name;?></option>
				<?php
						++$i;
					}
				}?>
			</select>
		</div>
	</nav>
	<div class="layer_body sale_box">
		<div class="gp_list">
			<?php
			$i=0;
			foreach((array)$group_promotion_ary as $key=>$val){
				foreach((array)$val['data'] as $k=>$v){
					if(!$v) continue;
					$not_prod=0;
					$type=(int)$v['Type'];
					$v['ReverseAssociate']==1 && $v['PackageProId']=str_replace("|{$ProId}|", "|{$v['ProId']}|", $v['PackageProId']);
					$pid_ary=@array_filter(@explode('|', $v['PackageProId']));
					$length=count($pid_ary);
					$data_ary=str::json_data(htmlspecialchars_decode($v['Data']), 'decode');
			?>
					<div class="widget promotion_body hide<?=(int)$type?' gp_list_promotion':' gp_list_purchase';?>" data-id="<?=$i;?>">
						<div class="master">
							<div class="prod_img fl"><a class="pic_box" href="javascript:;"><img src="<?=ly200::get_size_img($products_row['PicPath_0'], '240x240');?>" title="<?=$Name;?>" alt="<?=$Name;?>" /><span></span></a></div>
							<div class="prod_info">
								<div class="prod_name"><a href="javascript:;" title="<?=$Name;?>"><?=$Name;?></a></div>
								<?php if($type){?>
									<div class="prod_qty"><?=$c['lang_pack']['products']['qty'].': '.$MOQ;?></div>
									<dl class="attribute">
										<?php
										if(!(int)$v['IsAttr'] && $data_ary[$ProId]){
											foreach($data_ary[$ProId] as $k2=>$v2){
												if(!$v2) continue;
												if($k2=='Overseas'){//发货地
													$OvId=str_replace('Ov:', '', $v2);
													if((int)$c['config']['global']['Overseas']==0){//关闭海外仓功能，不显示
														$OvId!=1 && $not_prod+=1;//发货地不是中国，不能购买
														continue;
													}
													echo '<dd>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</dd>';
												}else{
													echo '<dd>'.$c_all_attr_ary[$vid_data_ary[$v2]['AttrId']]['Name'.$c['lang']].': '.$vid_data_ary[$v2]['Value'.$c['lang']].'</dd>';
												}
											}
											echo '<input type="hidden" class="master_attr_hide" value="'.htmlspecialchars(str::json_data($data_ary[$ProId])).'" />';
										}?>
									</dl>
								<?php }else{?>
									<div class="prod_price"><input type="checkbox" id="group_<?=$ProId;?>" onclick="return false;" oldprice="<?=cart::iconv_price($MOQ*$oldPrice, 2, '', 0);?>" curprice="<?=cart::iconv_price($MOQ*$ItemPrice, 2, '', 0);?>" proid="<?=$ProId;?>" checked /></div>
								<?php }?>
							</div>
						</div>
						<div class="suits">
							<ul>
								<?php
								foreach((array)$pid_ary as $v2){
									$row=$val['pro'][$v2];
									$url=ly200::get_url($row, 'products');
									$img=ly200::get_size_img($row['PicPath_0'], '240x240');
									$name=$row['Name'.$c['lang']];
									$_moq=$row['MOQ'];
									$_moq<1 && $_moq=1;
									$oldprice=$row['Price_0'];
									$cprice=$price=cart::products_add_to_cart_price($row, $_moq);
									if($row['Stock']<$_moq || $row['Stock']<1 || $row['SoldOut'] || ($row['IsSoldOut'] && ($row['SStartTime']>$c['time'] || $c['time']>$row['SEndTime'])) || in_array($row['CateId'], $c['procate_soldout'])){
										$not_prod+=1;
										continue;//总库存为空
									}
									$OvId=1;
									$PropertyPrice=0;
									if(!(int)$v['IsAttr']){//产品关联属性
										if($data_ary[$row['ProId']]){//有属性数据
											$_dara_ary=array();//临时储存
											foreach((array)$data_ary[$row['ProId']] as $k3=>$v3){
												if($k3=='Overseas'){//发货地
													$OvId=str_replace('Ov:', '', $v3);
												}else{
													$_dara_ary[$k3]=$v3;
												}
											}
											sort($_dara_ary);
											$attr_name='|'.implode('|', $_dara_ary).'|';
											if((int)$c['config']['products_show']['Config']['stock'] && (int)$row['IsCombination'] && !$combinatin_ary[$row['ProId']][$attr_name][$OvId][1]){
												$not_prod+=1;
												continue;//属性库存为空
											}
											if((int)$row['IsCombination']){//开启规格组合
												if((int)$combinatin_ary[$row['ProId']][$attr_name][$OvId][4]){//加价
													$PropertyPrice+=$combinatin_ary[$row['ProId']][$attr_name][$OvId][0];
												}else{//单价
													$cprice=$price=$combinatin_ary[$row['ProId']][$attr_name][$OvId][0];
												}
											}else{//关闭规格组合
												foreach($data_ary[$row['ProId']] as $k3=>$v3){
													if($combinatin_ary[$row['ProId']]["|{$v3}|"][$OvId]){
														$PropertyPrice+=$combinatin_ary[$row['ProId']]["|{$v3}|"][$OvId][0];//固定是加价
													}
												}
											}
										}else{//没有属性数据
											if(count($c_selected_ary[$row['ProId']]['Id'])>0){//产品是有关联属性数据，证明属性已经丢失了
												$not_prod+=1;
												continue;//属性库存为空
											}
										}
									}
									//价格计算
									$IsSeck=$is_promotion=0;
									$seck_row=str::str_code(db::get_one('sales_seckill', "ProId='$v2' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
									if($seck_row){//秒杀产品
										$price=$cprice=$seck_row['Price']+$PropertyPrice;
										$IsSeck=1;
									}else{//普通产品
										$price+=$PropertyPrice;
										$cprice+=$PropertyPrice;
										$is_promotion=((int)$row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime'])?1:0;
										if($is_promotion && $row['PromotionType']){//促销折扣
											$price=$price*($row['PromotionDiscount']/100);
										}
									}
									$_price=cart::iconv_price($price, 2, '', 0);
									$cprice=cart::iconv_price($cprice, 2, '', 0);
									$oldprice=cart::iconv_price($oldprice, 2, '', 0);
								?>
								<li class="clean ui_border_b<?=$k2+1==$length?' last':''?>">
									<div class="check fr">
										<?php if((int)$type==0){?><em class="btn_checkbox FontBgColor"></em><?php }?>
										<input type="checkbox" name="select" id="group_<?=$row['ProId'];?>" oldprice="<?=$_moq*$oldprice;?>" curprice="<?=$_moq*$_price;?>" price="<?=$cprice;?>" attrprice="0" moq="<?=$_moq;?>" proid="<?=$row['ProId'];?>" sales="<?=$is_promotion?1:0;?>" salesPrice="<?=($is_promotion && !$row['PromotionType'])?$row['PromotionPrice']:'';?>" discount="<?=($is_promotion && $row['PromotionType'])?$row['PromotionDiscount']:'';?>" isSeckill="<?=$IsSeck;?>" />
									</div>
									<div class="prod_img fl"><a class="pic_box" href="<?=$url;?>" target="_blank"><img src="<?=$img;?>" title="<?=$name;?>" alt="<?=$name;?>" /><span></span></a></div>
									<div class="prod_info">
										<div class="prod_name"><a href="<?=$url;?>" target="_blank" title="<?=$name;?>"><?=$name;?></a></div>
										<?php if(!$type){?>
											<div class="prod_price clearfix">
												<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Currency'].' '.$_SESSION['Currency']['Symbol'];?></em>
												<span class="price_data PriceColor" data="<?=$price;?>"><?=cart::currency_format($_price, 0, $_SESSION['Currency']['Currency']);?></span>
											</div>
										<?php }?>
										<div class="prod_qty"><?=$c['lang_pack']['products']['qty'].': '.$_moq;?></div>
										<dl class="attribute" data-combination="<?=(int)$row['IsCombination'];?>">
											<?php
											if(!(int)$v['IsAttr'] && $data_ary[$row['ProId']]){ //只显示属性名称
												foreach($data_ary[$row['ProId']] as $k3=>$v3){
													if(!$v3) continue;
													if($k3=='Overseas'){//发货地
														if((int)$c['config']['global']['Overseas']==0) continue;
														$OvId=str_replace('Ov:', '', $v3);
														echo '<dd>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</dd>';
													}else{
														echo '<dd>'.$c_all_attr_ary[$vid_data_ary[$v3]['AttrId']]['Name'.$c['lang']].': '.$vid_data_ary[$v3]['Value'.$c['lang']].'</dd>';
													}
												}
												echo '<input type="hidden" class="attr_hide" value="'.htmlspecialchars(str::json_data($data_ary[$row['ProId']])).'" />';
											}else{ //开启前台勾选产品属性
												foreach((array)$parent_ary[$row['AttrId']] as $v3){
													if(!$c_selected_ary[$row['ProId']]['Id'][$v3]) continue;
											?>
												<dd>
													<div class="box_select">
														<select name="id[<?=$v3;?>]" id="attr_<?=$v3;?>" attr="<?=$v3;?>">
															<option value=""><?=str_replace('%name%', $c_all_attr_ary[$v3]['Name'.$c['lang']], $c['lang_pack']['products']['select']);?></option>
															<?php
															foreach((array)$c_all_value_ary[$v3] as $k4=>$v4){
																if(!in_array($v4['VId'], $c_selected_ary[$row['ProId']]['Id'][$v3])) continue;
																$value=$combinatin_ary[$row['ProId']]["|{$v4['VId']}|"][0];
																$c_price=(float)$value[0];
																$c_qty=(int)$value[1];
																$c_weight=(float)$value[2];
																$c_sku=$value[3];
																$c_increase=(int)$value[4];
															?>
															<option value="<?=$v4['VId'];?>" data="<?=htmlspecialchars('{"Price":'.$c_price.',"Qty":'.$c_qty.',"Weight":'.$c_weight.',"SKU":'.$c_sku.',"IsIncrease":'.$c_increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && (int)$row['IsCombination'] && $value && $c_qty<1)?' class="hide hide_fixed" disabled':'';?>><?=$v4['Value'.$c['lang']].(!(int)$row['IsCombination'] && $c_price>0?' (+'.cart::iconv_price($c_price).')':'');?></option>
															<?php }?>
														</select>
													</div>
												</dd>
												<?php }?>
												<?php
												if((int)$row['IsCombination'] && $isHaveOversea){
												?>
												<dd style="display:<?=count($c_selected_ary[$row['ProId']]['Overseas'])>1?'block':'none';?>;">
													<div class="box_select">
														<select name="id[Overseas]" id="attr_Overseas" attr="Overseas">
															<option value=""><?=str_replace('%name%', $c['lang_pack']['products']['shipsFrom'], $c['lang_pack']['products']['select']);?></option>
															<?php
															foreach($c['config']['Overseas'] as $k3=>$v3){
																if($v3['OvId']>1 && !in_array($v3['OvId'], $c_selected_ary[$row['ProId']]['Overseas'])) continue;
																$Ovid='Ov:'.$v3['OvId'];
																$value=$combinatin_ary[$row['ProId']]["|{$v4['VId']}|"][$v3['OvId']];
																$c_price=(float)$value[0];
																$c_qty=(int)$value[1];
																$c_weight=(float)$value[2];
																$c_sku=$value[3];
																$c_increase=(int)$value[4];
															?>
															<option value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$c_price.',"Qty":'.$c_qty.',"Weight":'.$c_weight.',"SKU":'.$c_sku.',"IsIncrease":'.$c_increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && (int)$row['IsCombination'] && $value && $c_qty<1)?' class="hide hide_fixed" disabled':'';?>><?=$c['config']['Overseas'][$v3['OvId']]['Name'.$c['lang']].(!(int)$row['IsCombination'] && $c_price>0?' (+'.cart::iconv_price($c_price).')':'');?></option>
															<?php }?>
														</select>
													</div>
												</dd>	
												<?php }?>
												<input type="hidden" id="attr_hide_<?=$row['ProId'];?>" class="attr_hide" value="" />
												<input type="hidden" id="ext_attr_<?=$row['ProId'];?>" class="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary[$row['ProId']]));?>" />
											<?php }?>
										</dl>
									</div>
								</li>
								<?php }?>
							</ul>
							<div class="not_prod_number" value="<?=$not_prod;?>"></div>
						</div>
						<div class="info">
							<div class="prod_name"><?=str_replace('%packageLen%', ($type?$length:'0'), $c['lang_pack']['products']['packageLen']);?></div>
							<div class="prod_price clearfix">
								<strong class="group_curprice"><em class="currency_data PriceColor"><?=$_SESSION['Currency']['Currency'].' '.$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$type?$v['CurPrice']:$MOQ*$ItemPrice;?>"><?=cart::iconv_price(($type?$v['CurPrice']:$MOQ*$ItemPrice), 2);?></span></strong>
								<div class="group_oldprice clearfix"><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$type?$MOQ*$ItemPrice:$MOQ*$oldPrice;?>"><?=cart::iconv_price(($type?$MOQ*$ItemPrice:$MOQ*$oldPrice), 2);?></span></del></div>
								<div class="group_saveprice clearfix"><?=$c['lang_pack']['products']['save'];?> <em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$MOQ*($oldPrice-$ItemPrice);?>"><?=cart::iconv_price($MOQ*($oldPrice-$ItemPrice), 2);?></span></div>
							</div>
							<?php if(!$is_stockout){?><input type="button" value="<?=$c['lang_pack']['products']['buyNow'];?>" class="gp_btn" /><?php }?>
							<input type="hidden" name="PId" value="<?=$v['PId'];?>" />
						</div>
					</div>
			<?php
					++$i;
				}
			}?>
		</div>
	</div>
</section>