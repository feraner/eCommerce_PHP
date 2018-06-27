<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'products'));//检查权限

$permit_ary=array(
	'add'		=>	manage::check_permit('products', 0, array('a'=>'products', 'd'=>'add')),
	'edit'		=>	manage::check_permit('products', 0, array('a'=>'products', 'd'=>'edit')),
	'copy'		=>	manage::check_permit('products', 0, array('a'=>'products', 'd'=>'copy')),
	'del'		=>	manage::check_permit('products', 0, array('a'=>'products', 'd'=>'del')),
	'export'	=>	manage::check_permit('products', 0, array('a'=>'products', 'd'=>'export'))
);
?>
<div class="r_nav">
	<h1>{/module.products.products/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){//产品列表
		$prod_show=array();
		$cfg_row=str::str_code(db::get_all('config', 'GroupId="products_show"'));
		foreach($cfg_row as $v){
			$prod_show[$v['Variable']]=$v['Value'];
		}
		$used_row=str::json_data(htmlspecialchars_decode($prod_show['Config']), 'decode');
		
		//获取类别列表
		$cate_ary=str::str_code(db::get_all('products_category', '1', '*'));
		$category_ary=array();
		foreach((array)$cate_ary as $v){
			$category_ary[$v['CateId']]=$v;
		}
		$category_count=count($category_ary);
		unset($cate_ary);
		
		//获取所属供应商
		$business_row=db::get_all('business', 1, 'BId, Name', 'BId desc');
		$business_ary=array();
		foreach($business_row as $v){
			$business_ary[$v['BId']]=$v;
		}
		unset($business_row);
		
		//产品列表
		$Keyword=str::str_code($_GET['Keyword']);
		$CateId=(int)$_GET['CateId'];
		$Other=(int)$_GET['Other'];
		
		$where='1';//条件
		$page_count=50;//显示数量
		//$Keyword && $where.=" and (Name{$c['manage']['web_lang']} like '%$Keyword%' or concat_ws('', Prefix, Number) like '%$Keyword%' or SKU like '%$Keyword%' or ProId in(select ProId from products_selected_attribute_combination where SKU like '%$Keyword%'))";
		$Keyword && $where.=" and (Name{$c['manage']['web_lang']} like '%$Keyword%' or concat_ws('', Prefix, Number) like '%$Keyword%' or SKU like '%$Keyword%')";
		if($CateId){
			$where.=" and (CateId in(select CateId from products_category where UId like '".category::get_UId_by_CateId($CateId)."%') or CateId='{$CateId}')";
			$category_one=str::str_code(db::get_one('products_category', "CateId='$CateId'"));
			$UId=$category_one['UId'];
			$UId!='0,' && $TopCateId=category::get_top_CateId_by_UId($UId);
		}
		if($Other){
			switch($Other){
				case 1: $where.=' and IsNew=1'; break;
				case 2: $where.=' and IsHot=1'; break;
				case 3: $where.=' and IsBestDeals=1'; break;
				case 4: $where.=' and IsIndex=1'; break;
				case 5: $where.=" and (Stock>MOQ and Stock>0 and ((SoldOut=0 and IsSoldOut=0) or (SoldOut=0 and IsSoldOut=1 and SStartTime<{$c['time']} and {$c['time']}<SEndTime)))"; break;
				case 6: $where.=" and (IsSoldOut=1 and SStartTime>{$c['time']})"; break;
				case 7: $where.=" and (SoldOut=1 or (IsSoldOut=1 and SEndTime<{$c['time']}))"; break;
				case 8: $where.=" and Stock<=WarnStock"; break;
				case 9: $where.=" and Stock<MOQ && Stock<=0"; break;
			}
		}
		$products_row=str::str_code(db::get_limit_page('products', $where, '*', ((int)$used_row['manage_myorder']?$c['my_order']:'').'ProId desc', (int)$_GET['page'], $page_count));
		
		$column_row=db::get_value('config', "GroupId='custom_column' and Variable='Products'", 'Value');
		$custom_ary=str::json_data($column_row, 'decode');
		$column_fixed_ary=array('picture', 'name', 'classify', 'products.price', 'products.other');
		$column_ary=array('picture', 'name', 'products.business', 'classify', 'products.price', 'products.other', 'products.weight', 'products.cubage', 'products.stock', 'products.edit_time');
	?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/products.products.other/}{/products.attribute/}</label>
						<span class="input">
							<select name="Other">
								<option value="0">{/global.select_index/}</option>
								<option value="1">{/products.products.is_new/}</option>
								<option value="2">{/products.products.is_hot/}</option>
								<option value="3">{/products.products.is_best_deals/}</option>
								<option value="4">{/products.products.is_index/}</option>
								<option value="5">{/products.products.sold_in/}</option>
								<option value="6">{/products.products.sold_in_time/}</option>
								<option value="7">{/products.products.sold_out/}</option>
								<option value="8">{/products.products.warn_stock/}</option>
								<option value="9">{/products.products.not_stock/}</option>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="products" />
				<input type="hidden" name="a" value="products" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=products&a=products&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
			<?php if($permit_ary['edit']){?>
				<li><a class="tip_ico_down sold_in" href="javascript:;" label="{/products.products.sold_in_bat/}"></a></li>
				<li><a class="tip_ico_down sold_out" href="javascript:;" label="{/products.products.sold_out_bat/}"></a></li>
				<?php /*?><li><a class="tip_ico_down bat_close" href="./?m=products&a=products&d=batch_edit" label="{/products.products.batch_edit/}"></a></li><?php */?>
				<li><a class="tip_ico_down bat_close" href="javascript:;" label="{/products.products.batch_edit/}"></a></li>
			<?php }?>
			<?php if($permit_ary['export']){?><li><a class="tip_ico_down explode" href="./?m=products&a=products&d=explode" label="{/global.explode/}"></a></li><?php }?>
			<li class="extend">
				<a href="javascript:;" label="{/global.custom_column/}"></a>
				<form>
					<?php
					foreach((array)$column_ary as $v){
						$checked=(in_array($v, $column_fixed_ary) || in_array($v, $custom_ary))?' checked':'';
						$disabled=in_array($v, $column_fixed_ary)?' disabled':'';
					?>
						<div class="item"><input type="checkbox" name="Custom[]" class="custom_list" value="<?=$v;?>"<?=$checked.$disabled;?> /> {/products.<?=$v?>/}</div>
					<?php }?>
					<div class="blank6"></div>
					<input type="submit" class="submit_btn" value="{/global.submit/}" />&nbsp;&nbsp;<input type="checkbox" name="custom_all" value="" class="va_m" /> {/global.select_all/}
					<input type="hidden" name="do_action" value="products.products_custom_column" />
				</form>
			</li>
		</ul>
        <?php if((int)$products_row[1]){?><dl class="edit_form_part"><dd>{/global.total/} <span><?=$products_row[1];?></span> {/global.item/}</dd></dl><?php }?>
	<?php }?>
	<?php if($c['manage']['do']=='edit'){ //产品编辑?>
		<dl class="edit_form_part">
			<?php
			$pro_menu_ary=array('basic_info', 'seo_info', 'sales_info', 'freight_info', 'platform_info');
			foreach($pro_menu_ary as $k=>$v){
			?>
			<dt></dt>
			<dd><a href="javascript:void(0);" data-name="<?=$v;?>">{/products.products.<?=$v;?>/}</a></dd>
			<?php }?>
		</dl>
	<?php }?>
    <?php if($c['manage']['do']=='batch_edit' && !$_GET['proid_list']){?>
        <dl class="edit_form_part">
            <dd><a href="?m=products&a=products&d=batch_edit&type=0" <?=$_GET['type']==0?'class="current"':''?>>{/products.batch.batch_price/}</a></dd>
            <dt></dt>
            <dd><a href="?m=products&a=products&d=batch_edit&type=1" <?=$_GET['type']==1?'class="current"':''?>>{/products.batch.batch_move_category/}</a></dd>
        </dl>
    <?php }?>
</div>
<div id="products" class="r_con_wrap">
	<?php if($c['manage']['do']=='index'){?>
		<script type="text/javascript">$(document).ready(function(){products_obj.products_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="8%" nowrap="nowrap">{/products.picture/}</td>
					<td width="30%" nowrap="nowrap">{/products.name/}</td>
					<?php if(in_array('products.business', $custom_ary)){?><td width="10%" nowrap="nowrap">{/products.products.business/}</td><?php }?>
					<td width="15%" nowrap="nowrap">{/products.classify/}</td>
					<td width="6%" nowrap="nowrap">{/products.products.price/}</td>
					<?php if(in_array('products.weight', $custom_ary)){?><td width="6%" nowrap="nowrap">{/products.products.weight/}</td><?php }?>
					<?php if(in_array('products.cubage', $custom_ary)){?><td width="6%" nowrap="nowrap">{/products.products.cubage/}</td><?php }?>
					<?php if(in_array('products.stock', $custom_ary)){?><td width="6%" nowrap="nowrap">{/products.products.stock/}</td><?php }?>
					<?php if(in_array('products.edit_time', $custom_ary)){?><td width="12%" nowrap="nowrap">{/products.products.edit_time/}</td><?php }?>
					<td width="8%" nowrap="nowrap">{/products.products.other/}{/products.attribute/}</td>
					<td width="5%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['copy'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i=1;
				foreach($products_row[0] as $v){
					$img=ly200::get_size_img($v['PicPath_0'], end($c['manage']['resize_ary']['products']));
					$name=htmlspecialchars_decode($v['Name'.$c['manage']['web_lang']]);
					$url=ly200::get_url($v, 'products', $c['manage']['web_lang']);
				?>
					<tr>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['ProId'];?>" class="va_m" /></td><?php }?>
						<td class="img"><a href="<?=$url;?>" target="_blank" class="pic_box"><img src="<?=$img;?>" /><span></span></a></td>
						<td><a href="<?=$url;?>" target="_blank"><?=$name;?></a><br /><span class="number">(<?=$v['Prefix'].$v['Number'];?>)</span></td>
						<?php if(in_array('products.business', $custom_ary)){?><td nowrap="nowrap"><?=$business_ary[$v['Business']]['Name'];?></td><?php }?>
						<td<?=$permit_ary['edit']?' class="category_select"':'';?> cateid="<?=$v['CateId'];?>">
							<?php
							$UId=$category_ary[$v['CateId']]['UId'];
							if($UId){
								$key_ary=@explode(',',$UId);
								array_shift($key_ary);
								array_pop($key_ary);
								foreach((array)$key_ary as $k2=>$v2){
									echo $category_ary[$v2]['Category'.$c['manage']['web_lang']].'->';
								}
							}
							echo $category_ary[$v['CateId']]['Category'.$c['manage']['web_lang']];
							?>
						</td>
						<td nowrap="nowrap"<?=$permit_ary['edit']?' class="price_input"':'';?>>
							<?php /*<div class="PurchasePrice" price="<?=sprintf('%01.2f', $v['PurchasePrice']);?>">{/products.products.price_ary.2/}:<?=$c['manage']['currency_symbol'];?><span><?=sprintf('%01.2f', $v['PurchasePrice']);?></span></div>*/?>
							<div class="Price_0" price="<?=sprintf('%01.2f', $v['Price_0']);?>">{/products.products.price_ary.0/}:<?=$c['manage']['currency_symbol'];?><span><?=sprintf('%01.2f', $v['Price_0']);?></span></div>
							<div class="Price_1" price="<?=sprintf('%01.2f', $v['Price_1']);?>">{/products.products.price_ary.1/}:<?=$c['manage']['currency_symbol'];?><span><?=sprintf('%01.2f', $v['Price_1']);?></span></div>
						</td>
						<?php if(in_array('products.weight', $custom_ary)){?><td nowrap="nowrap"><?=$v['Weight'];?> {/products.products.weight_unit/}</td><?php }?>
						<?php if(in_array('products.cubage', $custom_ary)){?><td nowrap="nowrap"><?=str_replace(',', 'x', $v['Cubage']);?></td><?php }?>
						<?php if(in_array('products.stock', $custom_ary)){?><td nowrap="nowrap" <?=$v['Stock']<=$v['WarnStock']?' class="fc_red"':'';?>><?=$v['Stock'];?></td><?php }?>
						<?php if(in_array('products.edit_time', $custom_ary)){?><td nowrap="nowrap"><?=$v['EditTime']?date('Y-m-d', $v['EditTime']):'N/A';?></td><?php }?>
						<td class="other">
							<?=$v['IsNew']?'<span class="other_box">{/products.products.is_new/}</span>':'';?>
							<?=$v['IsHot']?'<span class="other_box">{/products.products.is_hot/}</span>':'';?>
							<?=$v['IsBestDeals']?'<span class="other_box">{/products.products.is_best_deals/}</span>':'';?>
							<?php
							if($v['SoldOut'] || ($v['IsSoldOut'] && $c['time']>$v['SEndTime'])){ //下架
								echo '<span class="fc_red">{/products.products.sold_out/}</span>';
							}elseif($v['Stock']<$v['MOQ'] && !$v['Stock']){
								echo '<span class="fc_red">{/products.products.not_stock/}</span>';
							}elseif($v['IsSoldOut'] && $v['SStartTime']>$c['time']){
								echo sprintf(manage::language('{/products.products.sold_in_date/}'), ceil(($v['SStartTime']-$c['time'])/86400));
							}else{
								echo '<span class="other_box">{/products.products.sold_in/}</span>';
							}
							?>
							<?=$v['IsIndex']?'<span class="other_box">{/products.products.is_index/}</span>':'';?>
						</td>
						<td nowrap="nowrap"<?=$permit_ary['edit']?' class="myorder_select" data-num="'.$v['MyOrder'].'"':'';?>><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
						<?php if($permit_ary['edit'] || $permit_ary['copy'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=products&d=edit&ProId=<?=$v['ProId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['copy']){?><a class="tip_ico tip_min_ico copy" href="./?do_action=products.products_copy&ProId=<?=$v['ProId'];?>" label="{/global.copy/}"><img src="/static/ico/copy.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.products_del&ProId=<?=$v['ProId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<div id="category_select_hide" class="hide"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></div>
		<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
	<?php
	}elseif($c['manage']['do']=='explode'){
	?>
        <script type="text/javascript">$(document).ready(function(){products_obj.explode_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/products.classify/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<?php $pagenum_ary = array(50,100,150,200); ?>
			<div class="rows">
				<label>{/products.explode.expage/}</label>
				<span class="input">
					<select name="PageNum">
						<?php foreach((array)$pagenum_ary as $v){ ?>
							<option value="<?=$v; ?>" <?=$v==200 ? 'selected' : ''; ?> ><?=$v; ?></option>
						<?php } ?>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.my_order/}</label>
				<span class="input">
					<select name="MyOrder">
						<?php
						for($i=0; $i<3; ++$i){
						?>
						<option value="<?=$i;?>">{/products.explode.myorder.<?=$i;?>/}</option>
						<?php }?>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.language_list/}</label>
				<span class="input">
					<select name="Language">
						<?php foreach($c['manage']['web_lang_list'] as $v){?>
							<option value="<?=$v;?>">{/language.<?=$v;?>/}</option>
						<?php }?>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.explode/}" />
					<a href="./?m=products&a=products" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<div id="explode_progress"></div>
			<input type="hidden" name="do_action" value="products.products_explode" />
			<input type="hidden" name="Number" value="0" />
		</form>
	<?php
    	}elseif($c['manage']['do']=='batch_edit'){
			if($_GET['proid_list']){
	?>
		<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
		<script type="text/javascript">$(document).ready(function(){products_obj.batch_edit_init()});</script>
        <form name="batch_form">
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table r_con_form">
			<thead>
				<tr>
					<td width="8%" nowrap="nowrap">{/products.picture/}</td>
					<td width="15%" nowrap="nowrap">{/products.name/}</td>
					<td width="20%" nowrap="nowrap">{/products.products.price/}</td>
					<td width="25%" nowrap="nowrap">{/products.products.sale_status/}</td>
					<td width="20%" nowrap="nowrap">{/products.products.other/}{/products.attribute/}</td>
					<td width="12%" nowrap="nowrap">{/global.my_order/}</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$cfg_row=str::str_code(db::get_all('config', 'GroupId in("business", "products_show")'));
				foreach($cfg_row as $v){
					$cfg_ary[$v['GroupId']][$v['Variable']]=$v['Value'];
				}
				$used_row=str::json_data(htmlspecialchars_decode($cfg_ary['products_show']['Config']), 'decode');

				$proid_list=str_replace('-', ',', trim($_GET['proid_list']));
				$where="ProId in($proid_list)";
				$products_row=db::get_all('products', $where, '*', ((int)$used_row['manage_myorder']?$c['my_order']:'').'ProId desc');
				foreach($products_row as $v){
					$img=ly200::get_size_img($v['PicPath_0'], end($c['manage']['resize_ary']['products']));
					$name=htmlspecialchars_decode($v['Name'.$c['manage']['web_lang']]);
					$url=ly200::get_url($v, 'products', $c['manage']['web_lang']);
				?>
					<tr class="rows">
						<td class="img"><a href="<?=$url;?>" target="_blank" class="pic_box"><img src="<?=$img;?>" /><span></span></a></td>
						<td><a href="<?=$url;?>" target="_blank"><?=$name;?></a></td>
						<td nowrap="nowrap" class="input">
							<div class="blank6"></div>
                            <span class="price_input"><b>{/products.products.price_ary.0/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price_0[]" value="<?=$v['Price_0'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" <?=(int)$cfg_ary['products_show']['price']?'notnull':'';?> /></span> <?=(int)$cfg_ary['products_show']['price']?'<font class="fc_red">*</font>':'';?>
                            <div class="blank6"></div>
                            <span class="price_input"><b>{/products.products.price_ary.1/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price_1[]" value="<?=$v['Price_1'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" notnull /></span> <font class="fc_red">*</font>
							<div class="blank6"></div>
                            <span class="price_input"><b>{/products.products.price_ary.2/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="PurchasePrice[]" value="<?=$v['PurchasePrice'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
							<div class="blank6"></div>
						</td>
						<td nowrap="nowrap" class="input">
                            <div class="switchery<?=$v['SoldOut']?' checked':'';?>">
                                <input type="checkbox" name="SoldOut[<?=$v['ProId'];?>]" value="1" class="SoldOutInput"<?=$v['SoldOut']?' checked':'';?> />
                                <div class="switchery_toggler"></div>
                                <div class="switchery_inner">
                                    <div class="switchery_state_on"></div>
                                    <div class="switchery_state_off"></div>
                                </div>
                            </div>
                            {/products.products.sold_out/}<span class="tool_tips_ico" content="{/products.products.soldOut_notes/}"></span>
                            <div class="blank12"></div>
                            <div id="sold_out_div" style="display:<?=$v['SoldOut']?'none':'';?>;">
                                <div class="switchery<?=$v['IsSoldOut']?' checked':'';?>">
                                    <input type="checkbox" name="IsSoldOut[<?=$v['ProId'];?>]" value="1" class="IsSoldOutInput"<?=$v['IsSoldOut']?' checked':'';?> />
                                    <div class="switchery_toggler"></div>
                                    <div class="switchery_inner">
                                        <div class="switchery_state_on"></div>
                                        <div class="switchery_state_off"></div>
                                    </div>
                                </div>
                                {/products.products.sold_in_time/}<span class="tool_tips_ico" content="{/products.products.soldIn_notes/}"></span>
                                <div class="blank6"></div>
                                <span class="sold_in_time" style="display:<?=$v['IsSoldOut']?'':'none';?>;">{/products.products.sold_in/}{/global.time/}:<input name="SoldOutTime[]" value="<?=date('Y-m-d H:i:s',($v['SStartTime']?$v['SStartTime']:$c['time'])).'/'.date('Y-m-d H:i:s', ($v['SEndTime']?$v['SEndTime']:$c['time']));?>" type="text" class="form_input" size="42" readonly></span>
                            </div>
                        </td>
						<td class="other">
                        	<div class="switchery<?=$v['IsFreeShipping']?' checked':'';?>">
                                <input type="checkbox" value="1" name="IsFreeShipping[<?=$v['ProId'];?>]"<?=$v['IsFreeShipping']?' checked':'';?> />
                                <div class="switchery_toggler"></div>
                                <div class="switchery_inner">
                                    <div class="switchery_state_on"></div>
                                    <div class="switchery_state_off"></div>
                                </div>
                            </div>
                            {/products.products.free_shipping/}<span class="tool_tips_ico" content="{/products.products.free_shipping_notes/}"></span>
							<div class="blank9"></div>
                            <span class="choice_btn<?=$v['IsIndex']?' current':'';?> mar_r_0">{/products.products.is_index/}<input type="checkbox" value="1" name="IsIndex[<?=$v['ProId'];?>]"<?=$v['IsIndex']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.index_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="choice_btn<?=$v['IsNew']?' current':'';?> mar_r_0">{/products.products.is_new/}<input type="checkbox" value="1" name="IsNew[<?=$v['ProId'];?>]"<?=$v['IsNew']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.new_notes/}"></span>
                            <div class="blank12"></div>
                            <span class="choice_btn<?=$v['IsHot']?' current':'';?> mar_r_0">{/products.products.is_hot/}<input type="checkbox" value="1" name="IsHot[<?=$v['ProId'];?>]"<?=$v['IsHot']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.hot_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class="choice_btn<?=$v['IsBestDeals']?' current':'';?> mar_r_0">{/products.products.is_best_deals/}<input type="checkbox" value="1" name="IsBestDeals[<?=$v['ProId'];?>]"<?=$v['IsBestDeals']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.best_deals_notes/}"></span>
						</td>
						<td nowrap="nowrap"><?=ly200::form_select($c['manage']['my_order'], 'MyOrder[]', $v['MyOrder']);?><span class="tool_tips_ico" content="{/products.products.myorder_notes/}"></span></td>
                        <input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
					</tr>
				<?php }?>
			</tbody>
            <tfoot>
            	<tr>
                	<td colspan="6"><input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" /></td>
                </tr>
            </tfoot>
			<input type="hidden" name="do_action" value="products.products_new_batch_edit" />
		</table>
        </form>
        <?php
        	}else{
				if($_GET['type']){	//批量移动产品
		?>
		<script type="text/javascript">$(document).ready(function(){products_obj.batch_edit_init()});</script>
        <form id="edit_form" class="r_con_form" name="batch_form">
			<div class="rows">
				<label>{/products.batch.source/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.batch.target/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateIdTo', '', 'products_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=products&a=products" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="products.products_batch_move_category_edit" />
        </form>
        <?php }else{	//批量修改价格?>
        <script type="text/javascript">$(document).ready(function(){products_obj.batch_edit_init()});</script>
        <form id="edit_form" class="r_con_form" name="batch_form">
			<div class="rows">
				<label>{/products.classify/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
                <label>{/global.type/}</label>
                <span class="input type">
                    <span class="choice_btn current">{/products.batch.type_price/}<input type="radio" name="Type" value="0" checked /></span>
                    <span class="choice_btn">{/products.batch.type_rate/}<input type="radio" name="Type" value="1" /></span>
                </span>
                <div class="clear"></div>
            </div>
            <div class="choice_box">
                <div class="rows">
                    <label>{/products.batch.type_price/}<span class="tool_tips_ico" content="{/products.batch.type_price_tips/}"></span></label>
                    <span class="input">
						<span class="price_input"><b>{/products.products.price_ary.1/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price" value="0.00" type="text" class="form_input" size="5" maxlength="10" />	
                    </span>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows" style="display:none;">
                    <label>{/products.batch.type_rate/}<span class="tool_tips_ico" content="{/products.batch.type_rate_tips/}"></span></label>
                    <span class="input">
						<span class="price_input"><b>{/products.products.price_ary.1/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Rate" value="0" type="text" class="form_input" size="5" maxlength="10" /></span> %
                    </span>
                    <div class="clear"></div>
                </div>
            </div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=products&a=products" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="products.products_new_batch_edit" />
        </form>
        <?php
			}
		}
		?>
	<?php
	}else{
		//产品编辑
		$ProId=(int)$_GET['ProId'];
		$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
		$products_seo_row=str::str_code(db::get_one('products_seo', "ProId='$ProId'"));
		$products_description_row=str::str_code(db::get_one('products_description', "ProId='$ProId'"));
		
		$cfg_row=str::str_code(db::get_all('config', 'GroupId in("business", "products", "products_show")'));
		foreach($cfg_row as $v){
			$cfg_ary[$v['GroupId']][$v['Variable']]=$v['Value'];
		}
		$used_row=str::json_data(htmlspecialchars_decode($cfg_ary['products_show']['Config']), 'decode');
		
		$products_row['CateId'] && $uid=category::get_UId_by_CateId($products_row['CateId']);
		$uid!='0,' && $TopCateId=category::get_top_CateId_by_UId($uid);
		$products_category_row=str::str_code(db::get_one('products_category', "CateId='{$TopCateId}'"));
		$AttrId=$products_row['AttrId'];
		if($products_category_row['AttrId'] && $products_row['AttrId']!=$products_category_row['AttrId']){//产品属性作了更换，与数据记录的id不一致
			$AttrId=$products_category_row['AttrId'];
		}
		$products_attr_row=str::str_code(db::get_all('products_attribute', "ParentId='{$AttrId}'"));
		
		$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId='{$ProId}' and IsUsed=1", 'SeleteId, AttrId, VId, Value', 'SeleteId asc'));
		foreach($selected_row as $v){
			$v['VId'] && $selected_ary[$v['AttrId']][]=$v['VId']; //记录勾选属性ID
		}
		
		$combinatin_ary=array();
		$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='$ProId'", '*', 'CId asc')); //属性组合数据
		foreach($combinatin_row as $v){
			$k=str_replace('|', '_', substr($v['Combination'], 1, -1));
			$k.=($k?'_':'').'Ov:'.$v['OvId'];
			$combinatin_ary[$k]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
		}
		
		if(!$ProId && $cfg_ary['products_show']['favorite']){//自定义收藏数范围
			$range_ary=str::json_data(htmlspecialchars_decode($cfg_ary['products_show']['favorite']), 'decode');
			if(count($range_ary)==2 && (int)$range_ary[0]>0 && (int)$range_ary[1]>0) $products_row['FavoriteCount']=rand((int)$range_ary[0], (int)$range_ary[1]);
		}
		
		//产品单位
		$Unit='';
		$IsUnitShow=0;
		if($products_row['Unit']){
			$Unit=$products_row['Unit'];
			$IsUnitShow=1;
		}elseif($used_row['item']){//是否开启产品自定义单位
			$Unit=$cfg_ary['products_show']['item'];
			$IsUnitShow=1;
		}
		$unit_list=str::json_data(htmlspecialchars_decode($cfg_ary['products']['Unit']), 'decode');
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');?>
		<script type="text/javascript">
		$(document).ready(function(){
			products_obj.products_edit_init();
			products_obj.attr_init();
		});
		</script>
		<form id="edit_form" class="r_con_form">
			<?php /***************************** 基础信息 Start *****************************/?>
			<div class="pro_box pro_box_<?=$pro_menu_ary[0];?> current" data-name="<?=$pro_menu_ary[0];?>">
            	<h3 class="rows_hd">{/products.products.<?=$pro_menu_ary[0];?>/}</h3>
				<div class="rows">
					<label>{/products.name/}</label>
					<span class="input"><?=manage::form_edit($products_row, 'text', 'Name', 53, 150, 'notnull');?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.classify/}</label>
					<span class="input">
						<div class="classify fl"><?=category::ouput_Category_to_Select('CateId', $products_row['CateId'], 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></div>
						<input type="button" id="expand_btn" class="btn_ok expand_btn fl" value="{/global.add/}">
						<span class="tool_tips_ico fl" content="{/products.products.classify_notes/}"></span>
						<div class="expand_list">
							<?php
							if($products_row['ExtCateId']){
								$ext_ary=explode(',',substr($products_row['ExtCateId'], 1, -1));
								foreach((array)$ext_ary as $v){
									echo '<div>'.category::ouput_Category_to_Select('ExtCateId[]', $v, 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}').'<a class="close" href="javascript:;"><img src="/static/ico/no.png" /></a></div>';
								}
							}?>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<?php
				$IsNumShow=0;
				$Number=$products_row['Number'];
				$Prefix=$products_row['Prefix'];
				if($cfg_ary['products_show']['myorder'] && $used_row['myorder'] && !$ProId){//开启产品编号自动排序
					$max_num=(int)db::get_max('products', "Prefix='{$cfg_ary['products_show']['myorder']}' and Number!=''", 'ProId');
					$Number=$max_num+1;
					$Prefix=$cfg_ary['products_show']['myorder'];
					$IsNumShow=1;
				}
				if($Prefix) $IsNumShow=1;
				?>
				<div class="rows">
					<label>{/products.products.number/}</label>
					<span class="input"><?php if($IsNumShow){?><input name="Prefix" value="<?=$Prefix;?>" type="text" class="form_input" size="8" maxlength="15" notnull /> - <?php }?><input name="Number" value="<?=$Number;?>" type="text" class="form_input" size="53" maxlength="50" notnull /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.sku/}</label>
					<span class="input"><input name="SKU" value="<?=$products_row['SKU'];?>" type="text" class="form_input" size="53" maxlength="50" /></span>
					<div class="clear"></div>
				</div>
				<?php
				if($c['FunVersion']>=1 && (int)$cfg_ary['business']['IsUsed']){
					$business_cate_row=db::get_all('business_category', 1, '*', $c['my_order'].' CateId asc');
					$business_cate_ary=array();
					foreach($business_cate_row as $v){
						$business_cate_ary[$v['UId']][]=$v;
					}
					$business_row=db::get_all('business', 1, '*', 'BId desc');
					$business_ary=array();
					foreach($business_row as $v){
						$business_ary[$v['CateId']][]=$v;
					}
				?>
					<div class="rows">
						<label>{/products.products.business/}</label>
						<span class="input">
							<select name='Business'>
								<option value=''>{/global.select_index/}</option>
								<?php
								foreach((array)$business_cate_ary['0,'] as $v){
								?>
								<optgroup label="<?=$v['Category'];?>">
									<?php
									foreach((array)$business_ary[$v['CateId']] as $v2){
										echo "<option value='{$v2['BId']}'".($products_row['Business']==$v2['BId']?' selected':'').">{$v2['Name']}</option>";
									}?>
								</optgroup>
									<?php foreach((array)$business_cate_ary['0,'.$v['CateId'].','] as $v2){?>
									<optgroup label="<?=$v2['Category'];?>">
										<?php
										foreach((array)$business_ary[$v2['CateId']] as $v3){
											echo "<option value='{$v3['BId']}'".($products_row['Business']==$v3['BId']?' selected':'').">{$v3['Name']}</option>";
										}?>
									</optgroup>
									<?php }?>
								<?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				<?php }?>
				<div class="rows">
					<label>{/page.page.custom_url/}</label>
					<span class="input">
						<span class="price_input"><b>/<div class="arrow"><em></em><i></i></div></b><input name="PageUrl" value="<?=$products_row['PageUrl'];?>" type="text" class="form_input" size="41" maxlength="150" /><b class="last">.html</b></span><span class="tool_tips_ico" content="{/page.page.custom_url_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows attr_add">
					<label>{/products.product/}{/products.attribute/}</label>
					<span class="input">
						<a href="javascript:;" id="add_attribute" class="btn_ok add">+ {/global.add/}</a>
						<input type="hidden" name="AttrId" id="attribute_hide" value="<?=$AttrId;?>" />
					</span>
				</div>
				<div class="attribute_list"></div>
				<div class="clear"></div>
				<div class="rows"<?=$IsUnitShow?'':' style="display:none;"';?>>
					<label>{/products.unit/}</label>
					<span class="input input_unit">
						<input name="Unit" value="<?=$Unit;?>" type="text" class="form_input" size="10" maxlength="20" />
						<div class="unit_box" style="display:<?=count($unit_list)>1?'inline-block':'none';?>;">
							<div class="button"><a href="javascript:;" class="add_unit">+</a></div>
							<div class="list">
								<?php foreach($unit_list as $k=>$v){?>
									<div class="item" data-key="<?=$k;?>"><span><?=$v;?></span><em class="del">x</em></div>
								<?php }?>
							</div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.product/}{/products.picture/}</label>
					<span class="input">
						<span class="multi_img upload_file_multi" id="PicDetail">
							<?php
							for($i=0; $i<10; ++$i){
							?>
							<dl class="img" num="<?=$i;?>">
								<dt class="upload_box preview_pic">
									<input type="button" id="PicUpload_<?=$i;?>" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_tips/}, {/notes.pic_size_tips/}'), 5, '240*240');?>" />
									<input type="hidden" name="PicPath[]" value="<?=$products_row["PicPath_{$i}"];?>" data-value="<?=ly200::get_size_img($products_row["PicPath_{$i}"], '240x240');?>" save="<?=is_file($c['root_path'].$products_row["PicPath_{$i}"])?1:0;?>" />
								</dt>
								<dd class="pic_btn">
									<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
									<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
								</dd>
							</dl>
							<?php }?>
						</span>
						<div class="tips">{/notes.jpg_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '500*500');?></div>
						<div class="blank9"></div>
						<div class="switchery">
							<input type="checkbox" name="UpdateWater" value="1" />
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>{/products.products.update_water/}
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows tab_box">
					<label>{/products.products.briefdescription/}</label>
					<span class="input">
						<?=manage::html_tab_button();?>
						<?php
						foreach($c['manage']['config']['Language'] as $k=>$v){
							$value=str_replace('&nbsp;', ' ', htmlspecialchars_decode($products_row["BriefDescription_{$v}"]));
						?>
							<div class="tab_txt tab_txt_<?=$k;?>">
								<span class='price_input price_textarea long_textarea'><textarea name='BriefDescription_<?=$v;?>'><?=$value;?></textarea></span>
							</div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows tab_box">
					<label>{/products.products.description/}</label>
					<span class="input">
						<?=manage::html_tab_button();?>
						<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
							<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("Description_{$v}", $products_description_row["Description_{$v}"]);?></div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
				<?php
				$IsDesc=str::json_data(htmlspecialchars_decode($products_row['IsDesc']), 'decode');
				for($i=0; $i<3; ++$i){
				?>
				<div class="rows tab_box">
					<label>{/products.tab/}<?=$i+1;?></label>
					<span class="input">
						<div class="switchery<?=$IsDesc[$i]?' checked':'';?>">
							<input type="checkbox" name="IsDesc[<?=$i;?>]" value="1" class="desc_tab_btn"<?=$IsDesc[$i]?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<div class="desc_box <?=$IsDesc[$i]?'show':'hide';?>">
							<div class="blank15"></div>
							<?=manage::html_tab_button('border');?>
							<div class="blank9"></div>
							<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
								<div class="tab_txt tab_txt_<?=$k;?>">
									{/news.news.seo_title/}: <span class="price_input lang_input"><input type="text" name="TabName_<?=$i;?>_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($products_description_row["TabName_{$i}_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="50" /></span>
									<div class="blank15"></div>
									<?=manage::Editor("Tab_{$i}_{$v}", $products_description_row["Tab_{$i}_{$v}"]);?>
								</div>
							<?php }?>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<?php }?>
			</div>
			<?php /***************************** 基础信息 End *****************************/?>
			
			<?php /***************************** SEO Start *****************************/?>
			<div class="pro_box pro_box_<?=$pro_menu_ary[1];?>" data-name="<?=$pro_menu_ary[1];?>">
            	<h3 class="rows_hd">{/products.products.<?=$pro_menu_ary[1];?>/}</h3>
				<div class="rows">
					<label>{/global.seo/}</label>
					<span class="input tab_box">
						<?=manage::html_tab_button('border');?>
						<div class="blank9"></div>
						<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
							<div class="tab_txt tab_txt_<?=$k;?>">
								<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($products_seo_row["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
								<div class="blank9"></div>
								<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($products_seo_row["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
								<div class="blank9"></div>
								<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$products_seo_row["SeoDescription_{$v}"];?></textarea></span>
							</div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
                <?php
				//产品标签
				$tags_id_ary=explode('|',$products_row['Tags']);
				$tags_id_ary=array_filter($tags_id_ary);
				$tags_id_ary=implode(',',$tags_id_ary);
				$tags_id_ary && $tags_row=db::get_all('products_tags',"TId in ($tags_id_ary)");
				if($tags_row){
				?>
				<div class="rows tags_row">
					<label>{/products.tags.tags/}</label>
					<span class="input">
						<?php foreach($tags_row as $v){?>
                        <span class="item"><?=$v['Name'.$c['manage']['web_lang']]?></span>
                        <?php }?>
					</span>
					<div class="clear"></div>
				</div>
                <?php }?>
			</div>
			<?php /***************************** SEO End *****************************/?>
			
			<?php /***************************** 销售信息 Start *****************************/?>
			<div class="pro_box pro_box_<?=$pro_menu_ary[2];?>" data-name="<?=$pro_menu_ary[2];?>">
            	<h3 class="rows_hd">{/products.products.<?=$pro_menu_ary[2];?>/}</h3>
				<div class="rows">
					<label>{/products.products.price/}</label>
					<span class="input">
						<span class="price_input"><b>{/products.products.price_ary.0/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price_0" value="<?=$products_row['Price_0'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" <?=(int)$cfg_ary['products_show']['price']?'notnull':'';?> /></span> <?=(int)$cfg_ary['products_show']['price']?'<font class="fc_red">*</font>':'';?>&nbsp;&nbsp;
						<span class="price_input"><b>{/products.products.price_ary.1/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price_1" value="<?=$products_row['Price_1'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" notnull /></span> <font class="fc_red">*</font>&nbsp;&nbsp;
						<span class="price_input"><b>{/products.products.price_ary.2/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="PurchasePrice" value="<?=$products_row['PurchasePrice'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows" style="display:none;">
					<label>{/products.products.promotion/}</label>
					<span class="input">
						<div class="fl">
							<div class="switchery<?=$products_row['IsPromotion']?' checked':'';?>">
								<input type="checkbox" name="IsPromotion" value="1"<?=$products_row['IsPromotion']?' checked':'';?> />
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>&nbsp;&nbsp;&nbsp;
						</div>
						<div id="promotion_div" class="fl" style="display:<?=$products_row['IsPromotion']?'':'none';?>;">
							<input type="radio" name="PromotionType" value="0"<?=$products_row['PromotionType']==0?' checked':'';?> /> {/products.products.money/}&nbsp;&nbsp;<input type="radio" name="PromotionType" value="1"<?=$products_row['PromotionType']==1?' checked':'';?> /> {/products.products.discount/}
							<div class="blank6"></div>
							<div class="promotion_money" style="display:<?=$products_row['PromotionType']==0?'':'none';?>;">
								<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="PromotionPrice" value="<?=$products_row['PromotionPrice'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount"<?=$products_row['PromotionType']==1?' disabled':'';?> /></span><span class="tool_tips_ico" content="{/products.products.promotion_price_notes/}"></span>
							</div>
							<div class="promotion_discount" style="display:<?=$products_row['PromotionType']==1?'':'none';?>;">
								<span class="price_input"><input name="PromotionDiscount" value="<?=$products_row['PromotionDiscount']?$products_row['PromotionDiscount']:100;?>" type="text" class="form_input" maxlength="5" size="5"><b class="last">%</b></span><span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span>
							</div>
							<div class="blank6"></div>
							{/products.products.promotion/}{/global.time/}:<input name="PromotionTime" value="<?=date('Y-m-d H:i:s',($products_row['StartTime']?$products_row['StartTime']:$c['time'])).'/'.date('Y-m-d H:i:s', ($products_row['EndTime']?$products_row['EndTime']:$c['time']));?>" type="text" class="form_input" size="42" readonly>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.wholesale_price/}</label>
					<span class="input">
						<?php
						$json=htmlspecialchars_decode($products_row['Wholesale']);
						$wholesale_price=str::json_data($json, 'decode');
						?>
						<table border="0" cellspacing="0" cellpadding="3" id="wholesale_price_list" class="item_data_table">
							<tbody>
								<tr>
									<td><input type="button" id="add_wholesale" class="btn_ok" value="{/global.add/}"></td>
								</tr>
								<?php
								foreach((array)$wholesale_price as $k=>$v){
									$price=$products_row['Price_1']>0?sprintf('%01.5f', $v/$products_row['Price_1']):0;
									$Devide=(float)substr($price, 0, -2);
								?>
									<tr>
										<td>{/products.products.qty/}: <input type="text" name="Qty[]" value="<?=$k;?>" class="form_input" size="5" maxlength="5" rel="amount" /> {/products.products.price/}: <?=$c['manage']['currency_symbol']?><input type="text" name="Price[]" value="<?=sprintf('%01.2f', $v);?>" class="form_input" size="5" maxlength="10" rel="amount" /> <a class="w_del" href="javascript:;"><img src="/static/ico/del.png" hspace="5" /></a> {/products.products.discount/}: <span class="wholesale_discount"><?=$products_row['Price_1']>0?($Devide*100):0;?></span>% <span class="tool_tips_ico" content="{/products.products.wholesale_discount_notes/}"></span></td>
									</tr>
								<?php }?>
							</tbody>
						</table>
					</span>
					<div class="clear"></div>
				</div>
				
				<?php
				$combination_status=' style="display:'.($products_row['IsCombination']?'table-cell':'none').';"';
				?>
				<div class="rows attr_add">
					<label>{/products.model.cart_attr/}</label>
					<span class="input">
						<a href="javascript:;" id="add_attribute" class="btn_ok add" data-cart="1">+ {/global.add/}</a>
						<div class="box_combination">
							{/products.products.is_combination/}&nbsp;
							<div class="switchery<?=$products_row['IsCombination']?' checked':'';?>">
								<input type="checkbox" value="1" name="IsCombination"<?=$products_row['IsCombination']?' checked':'';?> />
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						</div>
						<input type="hidden" name="AttrId" id="attribute_hide" value="<?=$AttrId;?>" />
					</span>
				</div>
				<div class="attribute clean"></div>
				<div class="rows hide" id="attribute_ext_box">
					<label>{/products.attribute/}{/products.products.relation/}</label>
					<span class="input tab_box">
						<div class="tab_box_row<?=(int)$c['manage']['config']['Overseas']==1?' show':' hide';?>"></div>
						<div id="attribute_ext">
							<table border="0" cellpadding="5" cellspacing="0" class="relation_box">
								<thead>
									<tr>
										<td width="35%">{/products.attribute/}</td>
										<td width="15%"<?=$combination_status;?>>{/products.products.sku/}</td>
										<td width="4%"<?=$combination_status;?>>{/products.products.mark_up/}</td>
										<td width="10%"><?=$products_row['IsCombination']?'{/products.products.price/}':'{/products.products.mark_up/}';?>(<?=$c['manage']['currency_symbol'];?>)</td>
										<td width="10%"<?=$combination_status;?>>{/products.products.stock/}</td>
										<td width="10%"<?=$combination_status;?>>{/products.products.weight/}({/products.products.weight_unit/})</td>
										<td width="7%"<?=$combination_status;?>>{/global.batch/}</td>
									</tr>
								</thead>
							</table>
						</div>
						<div id="attribute_tmp" class="hide">
							<table class="column">
								<tbody id="AttrId_XXX">
									<tr>
										<td class="title">Column</td>
										<td class="title"<?=$combination_status;?>><a href="javascript:;" class="synchronize_btn" data-num="0">{/global.synchronize/}</a></td>
										<td class="title"<?=$combination_status;?>>
											<div class="switchery btn_increase_all">
												<input type="checkbox" value="1" name="IsIncrease" />
												<div class="switchery_toggler"></div>
												<div class="switchery_inner">
													<div class="switchery_state_on"></div>
													<div class="switchery_state_off"></div>
												</div>
											</div>
										</td>
										<td class="title"><a href="javascript:;" class="synchronize_btn" data-num="2">{/global.synchronize/}</a></td>
										<td class="title"<?=$combination_status;?>><a href="javascript:;" class="synchronize_btn" data-num="3">{/global.synchronize/}</a></td>
										<td class="title"<?=$combination_status;?>><a href="javascript:;" class="synchronize_btn" data-num="4">{/global.synchronize/}</a></td>
										<td class="title"<?=$combination_status;?>></td>
									</tr>
								</tbody>
							</table>
							<table>
								<tbody class="contents">
									<tr id="VId_XXX" attr_txt="">
										<td>Name</td>
										<td<?=$combination_status;?>><input type="text" name="AttrSKU[XXX]" value="u_v" class="form_input input_w sku_input" size="10" maxlength="50"></td>
										<td<?=$combination_status;?>>
											<div class="switchery">
												<input type="checkbox" value="1" name="AttrIsIncrease[XXX]" />
												<div class="switchery_toggler"></div>
												<div class="switchery_inner">
													<div class="switchery_state_on"></div>
													<div class="switchery_state_off"></div>
												</div>
											</div>
										</td>
										<td><input type="text" name="AttrPrice[XXX]" value="p_v" class="form_input input_w input_price" size="8" maxlength="8" rel="amount"></td>
										<td<?=$combination_status;?>><input type="text" name="AttrStock[XXX]" value="s_v" class="form_input input_w" size="5" maxlength="5" rel="amount"></td>
										<td<?=$combination_status;?>><input type="text" name="AttrWeight[XXX]" value="w_v" class="form_input input_w" size="5" maxlength="5" rel="amount"> </td>
										<td<?=$combination_status;?> class="batch_box"><a href="javascript:;" class="btn_batch">{/global.operation/}</a><div class="batch_edit" style="display:none;"></div></td>
									</tr>
								</tbody>
							</table>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				
				<div class="rows hide" id="color_box">
					<label>{/products.color/}{/products.products.relation/}</label>
					<span class="input">
						<table border="0" cellpadding="5" cellspacing="0" id="color_ext" class="relation_box">
							<thead>
								<tr>
									<td width="20%">{/products.color/}</td>
									<td width="80%">{/products.pic_upfile/}</td>
								</tr>
							</thead>
						</table>
						<div id="color_tmp" class="hide">
							<table><tbody class="contents"><tr id="ColorId_XXX"><td>Name</td><td class="spacing">Content</td></tr></tbody></table>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				
				<div class="rows">
					<label>{/products.products.moq/}</label>
					<span class="input">
						<span class="price_input"><input name="MOQ" value="<?=(int)$products_row['MOQ'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /><?php if($Unit){?><b class="last"><?=$Unit;?></b><?php }?></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.maxoq/}</label>
					<span class="input">
						<span class="price_input"><input name="MaxOQ" value="<?=(int)$products_row['MaxOQ'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /><?php if($Unit){?><b class="last"><?=$Unit;?></b><?php }?></span>
						<span class="tool_tips_ico" content="{/products.products.maxoq_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.product_sales/}</label>
					<span class="input">
						<span class="price_input"><input name="Sales" value="<?=(int)$products_row['Sales'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /><?php if($Unit){?><b class="last"><?=$Unit;?></b><?php }?></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.total_stock/}</label>
					<span class="input">
						<span class="price_input"><input name="Stock" value="<?=(int)$products_row['Stock'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /><?php if($Unit){?><b class="last"><?=$Unit;?></b><?php }?></span>
						<span class="tool_tips_ico" content="{/products.products.pro_stock_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.warn_stock/}</label>
					<span class="input">
						<span class="price_input"><input name="WarnStock" value="<?=(int)$products_row['WarnStock'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /><?php if($Unit){?><b class="last"><?=$Unit;?></b><?php }?></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.stock_out_status/}<span class="tool_tips_ico" content="{/products.products.stock_notes/}"></span></label>
					<span class="input valign">
						<span class="choice_btn<?=(int)$products_row['StockOut']==0?' current':'';?>">{/products.products.stock_out/}<input type="radio" name="StockOut" value="0"<?=(int)$products_row['StockOut']==0?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.stock_notes_0/}"></span>&nbsp;&nbsp;&nbsp;
						<span class="choice_btn<?=(int)$products_row['StockOut']==1?' current':'';?>">{/products.products.arrival_notice/}<input type="radio" name="StockOut" value="1"<?=(int)$products_row['StockOut']==1?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.stock_notes_1/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.sale_status/}</label>
					<span class="input">
						<div class="switchery<?=$products_row['SoldOut']?' checked':'';?>">
							<input type="checkbox" value="1" name="SoldOut"<?=$products_row['SoldOut']?' checked':'';?> />
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						{/products.products.sold_out/}<span class="tool_tips_ico" content="{/products.products.soldOut_notes/}"></span>&nbsp;&nbsp;&nbsp;
						<div id="sold_out_div" style="display:<?=$products_row['SoldOut']?'none':'';?>;">
							<div class="switchery<?=$products_row['IsSoldOut']?' checked':'';?>">
								<input type="checkbox" name="IsSoldOut" value="1"<?=$products_row['IsSoldOut']?' checked':'';?> />
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							{/products.products.sold_in_time/}<span class="tool_tips_ico" content="{/products.products.soldIn_notes/}"></span>&nbsp;&nbsp;&nbsp;
							<span class="sold_in_time" style="display:<?=$products_row['IsSoldOut']?'':'none';?>;">{/products.products.sold_in/}{/global.time/}:<input name="SoldOutTime" value="<?=date('Y-m-d H:i:s',($products_row['SStartTime']?$products_row['SStartTime']:$c['time'])).'/'.date('Y-m-d H:i:s', ($products_row['SEndTime']?$products_row['SEndTime']:$c['time']));?>" type="text" class="form_input" size="42" readonly></span>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.default_review/}</label>
					<span class="input">
						<div class="switchery<?=$products_row['IsDefaultReview']?' checked':'';?>">
							<input type="checkbox" name="IsDefaultReview" value="1"<?=$products_row['IsDefaultReview']?' checked':'';?> />
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<div id="default_review_div" style="display:<?=$products_row['IsDefaultReview']?'':'none';?>;">
							{/products.products.default_review_rating/}:<input name="DefaultReviewRating" value="<?=(float)$products_row['DefaultReviewRating'];?>" type="text" class="form_input" size="5" maxlength="3" rel="amount" />&nbsp;&nbsp;&nbsp;&nbsp;
							{/products.products.default_review_count/}:<input name="DefaultReviewTotalRating" value="<?=(int)$products_row['DefaultReviewTotalRating'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" />
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.favorite/}</label>
					<span class="input"><input name="FavoriteCount" value="<?=(int)$products_row['FavoriteCount'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.other/}{/products.products.attributes/}</label>
					<span class="input other_btns">
						<span class="choice_btn<?=$products_row['IsIndex']?' current':'';?> mar_r_0">{/products.products.is_index/}<input type="checkbox" value="1" name="IsIndex"<?=$products_row['IsIndex']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.index_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
						<span class="choice_btn<?=$products_row['IsNew']?' current':'';?> mar_r_0">{/products.products.is_new/}<input type="checkbox" value="1" name="IsNew"<?=$products_row['IsNew']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.new_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
						<span class="choice_btn<?=$products_row['IsHot']?' current':'';?> mar_r_0">{/products.products.is_hot/}<input type="checkbox" value="1" name="IsHot"<?=$products_row['IsHot']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.hot_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
						<span class="choice_btn<?=$products_row['IsBestDeals']?' current':'';?> mar_r_0">{/products.products.is_best_deals/}<input type="checkbox" value="1" name="IsBestDeals"<?=$products_row['IsBestDeals']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/products.products.best_deals_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
						{/products.myorder/}:<?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $products_row['MyOrder']);?><span class="tool_tips_ico" content="{/products.products.myorder_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
			</div>
			<?php /***************************** 销售信息 End *****************************/?>
			
			<?php /***************************** 物流 Start *****************************/?>
			<div class="pro_box pro_box_<?=$pro_menu_ary[3];?>" data-name="<?=$pro_menu_ary[3];?>">
            	<h3 class="rows_hd">{/products.products.<?=$pro_menu_ary[3];?>/}</h3>
				<div class="rows">
					<label>{/products.products.free_shipping/}</label>
					<span class="input">
						<div class="switchery<?=$products_row['IsFreeShipping']?' checked':'';?>">
							<input type="checkbox" value="1" name="IsFreeShipping"<?=$products_row['IsFreeShipping']?' checked':'';?> />
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/products.products.free_shipping_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.weight/}</label>
					<span class="input">
						<span class="price_input"><input name="Weight" value="<?=$products_row['Weight'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">{/products.products.weight_unit/}</b></span>
						<?php
						if((int)db::get_value('config', 'GroupId="products" and Variable="IsPacking"', 'value')){
						?>
							<div class="blank9"></div>
							<?php
							$packing_start='<span class="price_input"><input name="PackingStart" value="'.(int)$products_row['PackingStart'].'" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">'.$c['manage']['lang_pack']['global']['item'].'</b></span>';
							echo str_replace('%input%', $packing_start, $c['manage']['lang_pack']['products']['products']['packing_start']).'<div class="blank9"></div>';
							$packing_qty='<span class="price_input"><input name="PackingQty" value="'.(int)$products_row['PackingQty'].'" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">'.$c['manage']['lang_pack']['global']['item'].'</b></span>';
							$packing_weight='<span class="price_input"><input name="PackingWeight" value="'.(float)$products_row['PackingWeight'].'" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">'.$c['manage']['lang_pack']['shipping']['shipping']['unit'].'</b></span>';
							echo str_replace(array('%qty%', '%weight%'), array($packing_qty, $packing_weight), $c['manage']['lang_pack']['products']['products']['packing_weight']);
							?>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<?php
					$Cubage=explode(',', $products_row['Cubage']);
					?>
					<label>{/products.products.cubage/}</label>
					<span class="input">
						<span class="price_input"><b>{/products.products.long/}<div class="arrow"><em></em><i></i></div></b><input name="Cubage[0]" value="<?=(float)$Cubage[0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">{/products.products.cubage_unit/}</b></span>&nbsp;&nbsp;&nbsp;
						<span class="price_input"><b>{/products.products.width/}<div class="arrow"><em></em><i></i></div></b><input name="Cubage[1]" value="<?=(float)$Cubage[1];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">{/products.products.cubage_unit/}</b></span>&nbsp;&nbsp;&nbsp;
						<span class="price_input"><b>{/products.products.height/}<div class="arrow"><em></em><i></i></div></b><input name="Cubage[2]" value="<?=(float)$Cubage[2];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">{/products.products.cubage_unit/}</b></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.products.volume_weight/}</label>
					<span class="input">
						<div class="switchery<?=$products_row['IsVolumeWeight']?' checked':'';?>">
							<input type="checkbox" value="1" name="IsVolumeWeight"<?=$products_row['IsVolumeWeight']?' checked':'';?> />
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/products.products.volume_weight_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
			</div>
			<?php /***************************** 物流 End *****************************/?>
			
			<?php /***************************** 平台导流 Start *****************************/?>
            <?php
			$platform_ary=array('amazon','aliexpress','wish','ebay','alibaba');
			$platform=str::json_data(str::str_code($products_row['Platform'],'htmlspecialchars_decode'),'decode');
			?>
			<div class="pro_box pro_box_<?=$pro_menu_ary[4];?>" data-name="<?=$pro_menu_ary[4];?>">
            	<h3 class="rows_hd">{/products.products.<?=$pro_menu_ary[4];?>/}</h3>
				<div class="rows">
					<label>{/products.products.platform/}</label>
					<span class="input platform_box">
						<?php foreach($platform_ary as $k=>$v){?>
                            <div class="item <?=$platform[$v]?'item_cur':''?>"><span>{/products.products.platform_name.<?=$k?>/}</span></div>
                        <?php }?>
					</span>
                    <div class="clear"></div>
				</div>
                <?php foreach($platform_ary as $k => $v){?>
                <div class="<?=$v?> platform_rows" <?=$platform[$v]?'style="display:block;"':''?>>
                    <div class="rows">
                        <label>{/products.products.platform_name.<?=$k?>/}</label>
                        <span class="input tab_box">
                        	<?=manage::html_tab_button();?>
                            <div class="clear"></div>
							<?php foreach($c['manage']['config']['Language'] as $k1=>$v1){?>
								<?php
                                    !$platform[$v] && $platform[$v][]=array();	//初始化值
                                    foreach((array)$platform[$v] as $k2 => $v2){
                                ?>
                                <div class="lang_item tab_txt tab_txt_<?=$k1?>">
                                    <div class="url">
                                        <span class="price_input">
                                            <b>{/products.products.links/}</b>
                                            <input type="text" name="Platform_<?=$k?>_Url_<?=$v1?>[]" value="<?=$v2['Url_'.$v1];?>" class="form_input" size="35"/>
                                        </span>
                                    </div>
                                </div>
                                <?php }?>
                            <?php }?>
                        </span>
                        <div class="clear"></div>
                    </div>
                </div>
                <?php }?>
            </div>
			<?php /***************************** 平台导流 End *****************************/?>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<input type="button" class="btn_ok drafts_btn" value="{/products.products.save_drafts/}" style="display:none;" />
					<a href="<?=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'./?m=products&a=products';?>" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="all_attr" value="" />
			<input type="hidden" id="check_attr" value="" />
			<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($combinatin_ary));?>" />
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" name="do_action" value="products.products_edit" />
			<input type="hidden" id="back_action" name="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" />
			<input type="hidden" id="IsOverseas" value="<?=(int)$c['manage']['config']['Overseas'];?>" />
			<?php if($products_row['AttrId']){?><input type="hidden" id="attribute_save_<?=$products_row['AttrId'];?>" value="<?=htmlspecialchars(str::json_data($selected_ary));?>" /><?php }?>
			<input type="hidden" id="save_drafts" name="SaveDrafts" value="0" />
		</form>
		<?php /***************************** 属性编辑 Start *****************************/?>
		<div class="pop_form box_model_edit">
			<form id="model_edit_form" data-cart="0">
				<div class="t"><h1><span></span>{/products.attribute/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/products.title/}</label>
						<span class="input"><?=manage::form_edit('', 'text', 'Name', 35, 50, 'notnull');?></span>
						<div class="clear"></div>
					</div>
					<div class="rows" style="display:none;">
						<label>{/products.model.children/}<font class="fc_red">*</font></label>
						<span class="input"></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/global.option/}</label>
						<span class="input fixed">
							<span id="cart_attr_box">
								<input type="checkbox" name="CartAttr" value="1" /> {/products.model.cart_attr/}<span class="tool_tips_ico" content="{/products.model.cart_attr_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
							</span>
							<span id="color_attr_box" style="display:none;">
								<input type="checkbox" name="ColorAttr" value="1" /> {/products.model.color_attr/}<span class="tool_tips_ico" content="{/products.model.color_attr_notes/}"></span>
							</span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows input_type" style="display:none;">
						<label>{/products.model.input_type/}</label>
						<span class="input line_h_28">
							<?php
							for($i=0; $i<2; $i++){
							?>
								<input type="radio" name="Type" value="<?=$i;?>" />{/products.model.input_type_ary.<?=$i;?>/}<span class="tool_tips_ico" content="{/products.model.input_type_notes_<?=$i;?>/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
							<?php }?>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows tab_box" style="display:none;">
						<label>{/products.model.select_list/}</label>
						<span class="input">
							<?=manage::html_tab_button('border');?>
							<div class="blank9"></div>
							<?php
							foreach($c['manage']['config']['Language'] as $k=>$v){
							?>
								<div class="tab_txt tab_txt_<?=$k;?>" lang="<?=$v;?>">
									<div class="attr_item">
										<span class="price_input not_input"><b>{/global.name/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Value_<?=$v;?>[]" value="<?=$vv["Value_{$v}"];?>" class="form_input input_name" size="30" maxlength="100" notnull disabled /></span>
										<a href="javascript:;" class="btn_option del">-</a>
										<a href="javascript:;" class="btn_option add">+</a>
									</div>
								</div>
							<?php }?>
						</span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="AttrId" value="" />
					<input type="hidden" name="ParentId" value="" />
					<input type="hidden" name="CateId" value="" />
					<input type="hidden" name="Position" value="products" />
					<input type="hidden" name="do_action" value="products.model_attribute_edit" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<div class="pop_form box_attribute_edit">
			<form id="attribute_edit_form" data-cart="0">
				<div class="t"><h1>{/global.add/}{/products.model.customize_attr/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/products.attribute/}</label>
						<span class="input attribute_name"></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/global.name/}</label>
						<span class="input"><?=manage::form_edit('', 'text', 'Value', 35, 200, 'notnull');?></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="AttrId" value="" />
					<input type="hidden" name="do_action" value="products.model_attribute_value_edit" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<div id="box_batch_edit">
			<em></em><i></i>
			<div class="rows">
				<label>{/products.products.price/}</label>
				<span class="input" data-name="BatchPrice"></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.products.stock/}</label>
				<span class="input" data-name="BatchStock"></span>
				<div class="clear"></div>
			</div>
			<div class="button"><input type="button" class="btn btn_batch_submit" value="{/global.save/}" /><input type="button" class="btn btn_batch_cancel" value="{/global.cancel/}" /></div>
		</div>
		<?php /***************************** 属性编辑 End *****************************/?>
		<script type="text/javascript">products_obj.products_edit_category_select('<?=$TopCateId;?>', '<?=$ProId;?>');</script>
	<?php }?>
</div>