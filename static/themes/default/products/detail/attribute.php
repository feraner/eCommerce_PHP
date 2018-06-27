<?php !isset($c) && exit();?>
<?php
$ext_ary=array();
$isHaveAttr=(int)($attr_ary['Cart'] && $products_row['AttrId']==($TopCategory_row?$TopCategory_row['AttrId']:$category_row['AttrId'])); //是否有规格属性

if($isHaveAttr || $isHaveOversea){
	$combinatin_ary=$all_value_ary=$attrid=array();
	foreach($attr_ary['Cart'] as $v){ $attrid[]=$v['AttrId']; }
	$attrid_list=implode(',', $attrid);
	!$attrid_list && $attrid_list=0;
	$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
	//属性组合数据 Start
	$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='{$ProId}'", '*', 'CId asc'));
	foreach($combinatin_row as $v){
		$combinatin_ary[$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
		$key=str_replace('|', '_', substr($v['Combination'], 1, -1));
		$v['OvId']<1 && $v['OvId']=1;
		$IsCombination==1 && $key.=($key?'_':'').'Ov:'.$v['OvId'];
		$ext_ary[$key]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
	}
	//属性组合数据 End
?>
	<ul class="widget attributes" default_selected="<?=(int)$c['config']['products_show']['Config']['selected'];?>" data-combination="<?=$IsCombination;?>" data-stock="<?=(int)$c['config']['products_show']['Config']['stock'];?>">
		<div class="attr_sure"><span class="attr_sure_choice"><?=$c['lang_pack']['products']['attributes_tips'];?></span><span class="attr_sure_close">X</span></div>
		<?php
		foreach((array)$attr_ary['Cart'] as $k=>$v){
			if(!$selected_ary['Id'][$v['AttrId']]) continue; //踢走
			if($c['config']['products_show']['Config']['attr']){
				$v['ColorAttr'] && count($color_picpath_ary)<count($selected_ary['Id'][$v['AttrId']]) && $color_attr_status=1; //图片总数量少于选项总数量，图片不给予显示
		?>
				<li class="attr_show" name="<?=$v['Name'.$c['lang']];?>">
					<h5><?=$v['Name'.$c['lang']];?>:</h5>
					<?php
					foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
						if(!in_array($k2, $selected_ary['Id'][$v['AttrId']])) continue; //踢走
						$value=$combinatin_ary["|{$k2}|"][1];
						$price=(float)$value[0];
						$qty=(int)$value[1];
						$weight=(float)$value[2];
						$sku=$value[3];
						$increase=(int)$value[4];
					?>
						<span value="<?=$v2['VId'];?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>" class="GoodBorderColor GoodBorderHoverColor<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' out_stock':'';?>" title="<?=($v2['Value'.$c['lang']]);?>">
							<em class="icon_selected"></em>
							<em class="icon_selected_bg GoodBorderBottomHoverColor"></em>
							<?php
							if($v['ColorAttr'] && !$color_attr_status){
								echo '<a class="attr_pic"><img src="'.$color_picpath_ary[$v2['VId']].'" alt="'.$v2['Value'.$c['lang']].'" /></a>';
							}else{
								echo $v2['Value'.$c['lang']];
							}
							?>
						</span>
					<?php }?>
					<input type="hidden" name="id[<?=$v['AttrId'];?>]" id="attr_<?=$v['AttrId'];?>" attr="<?=$v['AttrId'];?>" value="" class="attr_value<?=$v['ColorAttr']?' colorid':'';?>" />
				</li>
			<?php
			}else{
			?>
				<li name="<?=$v['Name'.$c['lang']];?>">
					<select name="id[<?=$v['AttrId'];?>]" id="attr_<?=$v['AttrId'];?>" attr="<?=$v['AttrId'];?>"<?=$v['ColorAttr']?' class="colorid"':'';?>>
						<option value=""><?=str_replace('%name%', $v['Name'.$c['lang']], $c['lang_pack']['products']['select']);?></option>
						<?php
						foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
							if(!in_array($k2, $selected_ary['Id'][$v['AttrId']])) continue; //踢走
							$value=$combinatin_ary["|{$k2}|"][1];
							$price=(float)$value[0];
							$qty=(int)$value[1];
							$weight=(float)$value[2];
							$sku=$value[3];
							$increase=(int)$value[4];
						?>
						<option value="<?=$v2['VId'];?>" data-title="<?=$v2['Value'.$c['lang']];?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?>><?=$v2['Value'.$c['lang']].' '.((!$IsCombination || $increase) && $price>0?' (+'.cart::iconv_price($price).')':'');?></option>
						<?php }?>
					</select>
				</li>
		<?php
			}
		}?>
		
		<?php
		if($isHaveOversea){
			if($c['config']['products_show']['Config']['attr']){
		?>
				<li class="attr_show" name="<?=$c['lang_pack']['products']['shipsFrom'];?>" style="display:<?=((int)$c['config']['global']['Overseas']==1 && count($selected_ary['Overseas'])>1 && $IsCombination==1)?'block':'none';?>;">
					<h5><?=$c['lang_pack']['products']['shipsFrom'];?>:</h5>
					<?php
					foreach($c['config']['Overseas'] as $k=>$v){
						$Ovid='Ov:'.$v['OvId'];
						if(!$selected_ary['Overseas'] && $v['OvId']>1) continue; //踢走
						if($selected_ary['Overseas'] && !in_array($v['OvId'], $selected_ary['Overseas'])) continue; //踢走
						$value=$combinatin_ary['||'][$v['OvId']];
						$price=(float)$value[0];
						$qty=(int)$value[1];
						$weight=(float)$value[2];
						$sku=$value[3];
						$increase=(int)$value[4];
					?>
						<span value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>" class="GoodBorderColor GoodBorderHoverColor<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' out_stock':'';?>" title="<?=($v['Name'.$c['lang']]);?>">
							<em class="icon_selected"></em>
							<em class="icon_selected_bg GoodBorderBottomHoverColor"></em>
							<?=$v['Name'.$c['lang']];?>
						</span>
					<?php }?>
					<input type="hidden" name="id[Overseas]" id="attr_Overseas" attr="Overseas" value="" class="attr_value" />
				</li>
		<?php }else{?>
				<li name="<?=$c['lang_pack']['products']['shipsFrom'];?>" style="display:<?=((int)$c['config']['global']['Overseas']==1 && count($selected_ary['Overseas'])>1 && $IsCombination==1)?'block':'none';?>;">
					<select name="id[Overseas]" id="attr_Overseas" attr="Overseas">
						<option value=""><?=str_replace('%name%', $c['lang_pack']['products']['shipsFrom'], $c['lang_pack']['products']['select']);?></option>
						<?php
						foreach($c['config']['Overseas'] as $k=>$v){
							$Ovid='Ov:'.$v['OvId'];
							if(!$selected_ary['Overseas'] && $v['OvId']>1) continue; //踢走
							if($selected_ary['Overseas'] && !in_array($v['OvId'], $selected_ary['Overseas'])) continue; //踢走
							$value=$combinatin_ary['||'][$v['OvId']];
							$price=(float)$value[0];
							$qty=(int)$value[1];
							$weight=(float)$value[2];
							$sku=$value[3];
							$increase=(int)$value[4];
						?>
						<option value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?>><?=$v['Name'.$c['lang']].' '.((!$IsCombination || $increase) && $price>0?' (+'.cart::iconv_price($price).')':'');?></option>
						<?php }?>
					</select>
				</li>
		<?php
			}
		}?>
	</ul>
<?php }?>