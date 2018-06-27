<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'promotion'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('sales', 0, array('a'=>'promotion', 'd'=>'add')),
	'edit'	=>	manage::check_permit('sales', 0, array('a'=>'promotion', 'd'=>'edit')),
	'del'	=>	manage::check_permit('sales', 0, array('a'=>'promotion', 'd'=>'del'))
);
?>
<script type="text/javascript">var lang_str_obj={'currency':'<?=$c['manage']['currency_symbol'];?>', 'now_time':'<?=date('Y-m-d H:i', $c['time']);?>'};</script>
<div class="r_nav">
	<h1>{/module.sales.promotion/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='index'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<input type="hidden" name="m" value="sales" />
				<input type="hidden" name="a" value="promotion" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=sales&a=promotion&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="combination" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//组合促销列表
	?>
		<script type="text/javascript">$(document).ready(function(){sales_obj.package_frame_init(); sales_obj.promotion_list_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="56%" nowrap="nowrap">{/sales.package.product_info/}</td>
					<td width="10%" nowrap="nowrap">{/sales.package.promotion_price/}</td>
					<td width="10%" nowrap="nowrap">{/sales.package.reverse_associate/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				//获取类别列表
				$cate_ary=str::str_code(db::get_all('products_category','1','*'));
				$category_ary=array();
				foreach((array)$cate_ary as $v){
					$category_ary[$v['CateId']]=$v;
				}
				$category_count=count($category_ary);
				unset($cate_ary);
				
				//列表
				$Keyword=str::str_code($_GET['Keyword']);
				
				$where='Type=1';//条件
				$page_count=10;//显示数量
				$Keyword && $where.=" and ProId in(select ProId from products where Name{$c['manage']['web_lang']} like '%$Keyword%' or concat(Prefix, Number) like '%$Keyword%')";
				$promotion_row=str::str_code(db::get_limit_page('sales_package', $where, '*', 'PId desc', (int)$_GET['page'], $page_count));
				
				$pro_where='ProId in(0';
				foreach($promotion_row[0] as $v) $pro_where.=",{$v['ProId']}".str_replace('|', ',', substr($v['PackageProId'], 0, -1));
				$pro_where.=')';
				$pro_ary=array();
				$pro_row=str::str_code(db::get_all('products', $pro_where, '*', 'ProId desc'));
				foreach($pro_row as $v) $pro_ary[$v['ProId']]=$v;
				
				$i=1;
				foreach($promotion_row[0] as $v){
					$img=ly200::get_size_img($pro_ary[$v['ProId']]['PicPath_0'], '240x240');
					$name=$pro_ary[$v['ProId']]['Name'.$c['manage']['web_lang']];
					$promotion_ary=explode('|', substr($v['PackageProId'], 1, -1));
					$price=manage::range_price($pro_ary[$v['ProId']], 1);
					$biref=$pro_ary[$v['ProId']]['BriefDescription'.$c['manage']['web_lang']];
					$url=ly200::get_url($v, 'products', $c['manage']['web_lang']);
				?>
				<tr>
					<?php if($permit_ary['del']){?><td class="va_t"><input type="checkbox" name="select" value="<?=$v['PId'];?>" class="va_m" /></td><?php }?>
					<td class="left is_main">
						<div class="fz_14px p_maintit"><?=$v['Name'];?></div>
						<div class="p_row fl">
							<div class="p_img fl"><a href="<?=$url;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" price="<?=$price;?>" number="<?=$pro_ary[$v['ProId']]['Number'];?>" biref="<?=$biref;?>" align="absmiddle" height="50" /></a></div>
							<div class="p_info">
								<a class="p_name" href="<?=$url;?>" target="_blank"><?=$name;?></a>
							</div>
							<div class="clear"></div>
						</div>
						<ul class="p_list fl">
							<?php
							foreach((array)$promotion_ary as $v2){
								$img=ly200::get_size_img($pro_ary[$v2]['PicPath_0'], '240x240');
								$name=$pro_ary[$v2]['Name'.$c['manage']['web_lang']];
								$price=manage::range_price($pro_ary[$v2], 1);
								$biref=$pro_ary[$v2]['BriefDescription'.$c['manage']['web_lang']];
							?>
							<li>
								<div class="p_img fl"><a href="<?=$url;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" price="<?=$price;?>" number="<?=$pro_ary[$v['ProId']]['Number'];?>" biref="<?=$biref;?>" align="absmiddle" height="50" /></a></div>
								<a href="<?=ly200::get_url($pro_ary[$v2], 'products', $c['manage']['web_lang']);?>" target="_blank" img="<?=$img;?>" price="<?=$price;?>" number="<?=$pro_ary[$v2]['Number'];?>" biref="<?=$biref;?>"><?=$name;?></a>
								<div class="clear"></div>
							</li>
							<?php }?>
						</ul>
						<div class="clear"></div>
					</td>
					<td><?=$c['manage']['currency_symbol'].sprintf('%01.2f', $v['CurPrice']);?></td>
					<td><?=$v['ReverseAssociate']?'<span class="fc_red">{/global.n_y.1/}</span>':'{/global.n_y.0/}';?></td>
					<td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td>
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=sales&a=promotion&d=edit&PId=<?=$v['PId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=sales.promotion_del&PId=<?=$v['PId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($promotion_row[1], $promotion_row[2], $promotion_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
	<?php
	}else{
		//组合促销编辑
		$PId=(int)$_GET['PId'];
		$promotion_row=str::str_code(db::get_one('sales_package', "PId='$PId'"));
		$ProId=$promotion_row['ProId'];
		$PackageProId=$promotion_row['PackageProId'];
		
		$PackageProId && $data_ary=str::json_data(htmlspecialchars_decode($promotion_row['Data']), 'decode');
		if($PackageProId){
			$pro_ary=array();
			$pro_row=str::str_code(db::get_all('products', "ProId in({$ProId}, ".str_replace('|', ',', substr($PackageProId, 1, -1)).")", '*', 'ProId desc'));
			foreach($pro_row as $v) $pro_ary[$v['ProId']]=$v;
			$promotion_ary=explode('|', substr($PackageProId, 1, -1));
			array_unshift($promotion_ary,$ProId);
			$promotion_where_ary = $promotion_ary;
		}
		$p_remove_pid = $_POST['remove_pid'] ? $_POST['remove_pid'] : $_GET['remove_pid'];
		trim($p_remove_pid,',') && $promotion_where_ary = @explode(',', substr($p_remove_pid, 1, -1));
		if($promotion_where_ary){	
			$remove_pid = $where_remove_pid = @implode(',', $promotion_where_ary);
			$remove_pid = ','.trim($remove_pid,',').',';
		}
		
		$where='1';
		$page_count=12;//显示数量
		$Name=str::str_code($_GET['Name']);
		$CateId=(int)$_GET['CateId'];
		$Name && $where.=" and (Name{$c['manage']['web_lang']} like '%$Name%' or concat(Prefix, Number) like '%$Name%')";
		$where_remove_pid && $where.=" and ProId not in ({$where_remove_pid})";
		if($CateId){
			$UId=category::get_UId_by_CateId($CateId);
			$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
		}
		$products_row=str::str_code(db::get_limit_page('products', $where." and ((SoldOut=0 and IsSoldOut=0) or (IsSoldOut=1 and SStartTime<{$c['time']} and {$c['time']}<SEndTime))", '*', $c['my_order'].'ProId desc', (int)$_GET['page'], $page_count));
		$selected_where='ProId in(-1';
		foreach((array)$products_row[0] as $v){
			$selected_where.=",{$v['ProId']}";
		}
		foreach((array)$pro_row as $v){
			$selected_where.=",{$v['ProId']}";
		}
		$selected_where.=')';
		
		//列出所有购物车属性
		$parent_ary=$all_attr_ary=$all_value_ary=$vid_data_ary=$selected_ary=array();
		$cart_attr_row=str::str_code(db::get_all('products_attribute', "ParentId>0 and CartAttr=1", "AttrId, Name{$c['manage']['web_lang']}, ParentId", $c['my_order'].'AttrId asc')); //所有购物车属性
		$_attribute_value_where='-1';
		foreach((array)$cart_attr_row as $v){
			$parent_ary[$v['ParentId']][]=$v['AttrId'];
			$all_attr_ary[$v['AttrId']]=$v;
			$_attribute_value_where.=",{$v['AttrId']}";
		}
		$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in($_attribute_value_where)", '*', $c['my_order'].'VId asc')); //所有属性选项
		foreach($value_row as $v){
			$all_value_ary[$v['AttrId']][$v['VId']]=$v;
			$vid_data_ary[$v['VId']]=$v;
		}
		$selected_row=str::str_code(db::get_all('products_selected_attribute', "IsUsed=1 and {$selected_where} and AttrId in($_attribute_value_where, 0)", 'SeleteId, ProId, AttrId, VId, OvId', 'SeleteId asc'));
		foreach($selected_row as $v){
			$selected_ary[$v['ProId']]['Id'][$v['AttrId']][]=$v['VId'];
			$v['AttrId']==0 && $v['VId']==0 && $v['OvId']>=0 && $selected_ary[$v['ProId']]['Overseas'][]=$v['OvId']; //记录勾选属性ID 发货地
		}
		
		//发货地
		$overseas_ary=array();
		$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
		foreach($overseas_row as $v){
			$overseas_ary[$v['OvId']]=$v;
		}
	?>
    <?=ly200::load_static('/static/js/plugin/drag/drag.js');?>
    <script type="text/javascript">$(document).ready(function(){sales_obj.package_edit_init()});</script>
	<div class="list_box">
		<div class="lefter lefter_promotion">
			<form id="related_form">
			<div class="p_title">{/sales.package.edit_area/}</div>
			<div class="rows">
				<span class="th">{/user.info.subject/}:</span>
				<input name="Name" value="<?=$promotion_row['Name'];?>" type="text" class="name_input form_input" size="50" maxlength="150" notnull />
			</div>
			<div class="rows">
				<span class="th">{/sales.package.promotion_price/}(<?=$c['manage']['currency_symbol'];?>):</span>
				<input name="CurPrice" value="<?=(float)$promotion_row['CurPrice'];?>" type="text" class="price_input form_input" size="50" maxlength="10" rel="amount" notnull />
			</div>
			<div class="rows">
				<span class="th fl">&nbsp;</span>
				<span class="attr_box">
					<div class="related_attr">
						<div class="switchery<?=$package_row['ReverseAssociate']?' checked':'';?>">
							<input type="checkbox" name="ReverseAssociate" value="1"<?=$package_row['ReverseAssociate']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div> {/sales.package.reverse_associate/}
					</div>
					<div class="clear"></div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="p_related_frame p_main p_frame">
				<?php			
				if($PackageProId){				
					foreach((array)$promotion_ary as $v){
						$proid=$v;
						$row=$pro_ary[$proid];
						$url=ly200::get_url($row, 'products');
						$img=ly200::get_size_img($row['PicPath_0'], '240x240');
						$name=$row['Name'.$c['manage']['web_lang']];
						$attr_ext_ary=array();
						isset($data_ary[$proid]) && is_array($data_ary[$proid]) && $attr_ext_ary=$data_ary[$proid];
				?>
				<div id="related_product_<?=$proid;?>" class="p_related_item">
					<div class="main_products <?=$proid==$ProId ? 'p_checked' : ''; ?>" pro_num="<?=$proid; ?>"></div>
					<div class="p_related_img"><img src="<?=$img;?>"></div>
					<div class="p_related_info">
						<div class="related_list p_name"><span><?=$name;?> (<?=$row['Prefix'].$row['Number'];?>)</span></div>
						<div class="related_list p_price">{/products.product/}{/products.products.price/}: <span><?=manage::range_price($row, 1);?></span></div>
						<div class="related_list attr_list">
							<?php
							foreach((array)$parent_ary[$row['AttrId']] as $k2=>$v2){
								if(!$selected_ary[$proid]['Id'][$v2]) continue;
							?>
								<select name="Attr_<?=$proid;?>[<?=$v2;?>]" attr="<?=$v2;?>" notnull>
									<option value="">--<?=$all_attr_ary[$v2]['Name'.$c['manage']['web_lang']];?>--</option>
									<?php
									foreach((array)$all_value_ary[$v2] as $k3=>$v3){
										if(!in_array($k3, $selected_ary[$proid]['Id'][$v2])) continue;
									?>
										<option value="<?=$k3;?>"<?=in_array($k3, $attr_ext_ary)?' selected':'';?>><?=$vid_data_ary[$k3]['Value'.$c['manage']['web_lang']];?></option>
									<?php }?>
								</select>
							<?php }?>
							<?php
							if(count($overseas_ary)>0 && $selected_ary[$proid]['Overseas']){
							?>
								<select name="Attr_<?=$proid;?>[Overseas]" attr="Overseas" notnull<?=($c['manage']['config']['Overseas']==1 && $row['IsCombination'])?'':' class="hide"';?>>
									<?php
									foreach((array)$overseas_ary as $k2=>$v2){
										if(!in_array($v2['OvId'], $selected_ary[$proid]['Overseas'])) continue;
										$Ovid='Ov:'.$v2['OvId'];
									?>
										<option value="<?=$Ovid;?>"<?=in_array($Ovid, $attr_ext_ary)?' selected':'';?>><?=$overseas_ary[$v2['OvId']]['Name'.$c['manage']['web_lang']];?></option>
									<?php }?>
								</select>
							<?php }?>
						</div>
					</div>
					<div class="remove-item hand" type="<?=$proid==$ProId ? 'main' : 'related'; ?>" del_num="<?=$proid;?>">X</div>
				</div>
				<?php
					}
				}else{
				?>
				<div class="p_related_notice">{/sales.package.related_notice/}</div>
				<?php }?>
			</div>
			<div class="related_bottom">
				<div class="related_btn">
					<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=sales&a=promotion" class="btn_cancel fr">{/global.return/}</a>
				</div>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="PId" value="<?=$PId;?>" />
			<input type="hidden" name="ProId" id="proid_hide" value="<?=$ProId;?>" />
			<input type="hidden" name="PackageProId" id="packageproid_hide" value="<?=$PackageProId;?>" />
			<input type="hidden" name="do_action" value="sales.promotion_edit" />
			<input type="hidden" name="Type" id="type_hide" value="1" />
			<input type="hidden" name="IsMain" id="is_main" value="1" />
			<input type="hidden" id="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" />
			</form>
		</div>
		<div class="list_box_righter">
			<div class="p_title">{/sales.package.product_list/}
				<div class="p_search">
					<form id="search_form">
						<input type="text" name="Name" class="form_input" search_input="1" value="" />
						<?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', '1', 'class="form_select"','{/global.select_index/}');?>
						<a href="javascript:;" class="btn_ok" id="search_btn">{/global.search/}</a>
						<input type="hidden" name="PId" value="<?=$PId;?>" /><div class="clear"></div>
						<input type="hidden" name="remove_pid" value="<?=$remove_pid ? $remove_pid : ','; ?>" />
						<input type="hidden" name="m" value="sales" />
						<input type="hidden" name="a" value="promotion" />
						<input type="hidden" name="d" value="edit" />
					</form>
				</div>
				<a href="javascript:;" class="r_search_btn"></a>
			</div>
			<div class="product_frame p_frame">
				<?php
				foreach($products_row[0] as $k=>$v){
					$proid=$v['ProId'];
					$url=ly200::get_url($v, 'products');
					$img=ly200::get_size_img($v['PicPath_0'], '240x240');
					$name=$v['Name'.$c['manage']['web_lang']];
				?>
					<div id="product_item_<?=$proid;?>" class="product_item" pro_num="<?=$proid; ?>">
						<div img_num="<?=$proid;?>" id="p_img_<?=$proid;?>" class="p_img"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
						<div class="p_info">
							<div class="p_list"><span><?=str::str_echo($name, 100, 0 , '...');?> (<?=$v['Prefix'].$v['Number'];?>)</span></div>
							<div class="p_list p_price">{/products.product/}{/products.products.price/}: <span><?=manage::range_price($v, 1);?></span></div>
							<div class="p_list attr_list">
								<?php
								foreach((array)$parent_ary[$v['AttrId']] as $k2=>$v2){
									if(!$selected_ary[$proid]['Id'][$v2]) continue;
								?>
									<select name="Attr_<?=$proid;?>[<?=$v2;?>]" attr="<?=$v2;?>" notnull>
										<option value="">--<?=$all_attr_ary[$v2]['Name'.$c['manage']['web_lang']];?>--</option>
										<?php
										foreach((array)$all_value_ary[$v2] as $k3=>$v3){
											if(!in_array($k3, $selected_ary[$proid]['Id'][$v2])) continue;
										?>
											<option value="<?=$k3;?>"><?=$vid_data_ary[$k3]['Value'.$c['manage']['web_lang']];?></option>
										<?php }?>
									</select>
								<?php }?>
								<?php
								if(count($overseas_ary)>0 && $selected_ary[$proid]['Overseas']){
								?>
									<select name="Attr_<?=$proid;?>[Overseas]" attr="Overseas" notnull<?=($c['manage']['config']['Overseas']==1 && $v['IsCombination'])?'':' class="hide"';?>>
										<?php
										foreach((array)$overseas_ary as $k2=>$v2){
											if(!in_array($v2['OvId'], $selected_ary[$proid]['Overseas'])) continue;
											$Ovid='Ov:'.$v2['OvId'];
										?>
											<option value="<?=$Ovid;?>"><?=$overseas_ary[$v2['OvId']]['Name'.$c['manage']['web_lang']];?></option>
										<?php }?>
									</select>
								<?php }?>
							</div>
						</div>
					</div>
				<?php }?>
				<div class="blank20"></div>
				<div class="blank20"></div>
				<div id="turn_page_oth" class="turn_page"><?=ly200::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}', 1);?></div>
				<div class="blank20"></div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
    <?php }?>
</div>