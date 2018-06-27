<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'sales'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('sales', 0, array('a'=>'sales', 'd'=>'add')),
	'edit'	=>	manage::check_permit('sales', 0, array('a'=>'sales', 'd'=>'edit')),
	'del'	=>	manage::check_permit('sales', 0, array('a'=>'sales', 'd'=>'del'))
);
?>
<script type="text/javascript">var lang_str_obj={'currency':'<?=$c['manage']['currency_symbol'];?>', 'now_time':'<?=date('Y-m-d H:i', $c['time']);?>'};</script>
<div class="r_nav">
	<h1>{/module.sales.sales/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$status_ary=array(
			1=>manage::language('{/sales.tuan.normal/}'),//售卖中
			2=>manage::language('{/sales.tuan.not_start/}'),//未开始
			3=>manage::language('{/sales.tuan.expire/}'),//已结束
		);
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
						<label>{/sales.status/}</label>
						<span class="input"><?=ly200::form_select($status_ary, 'Status', '', '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="sales" />
				<input type="hidden" name="a" value="sales" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=sales&a=sales&d=add" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
				<li><a class="tip_ico_down bat_close" href="javascript:;" label="{/products.products.batch_edit/}"></a></li>
		</ul>
	<?php }?>
</div>
<div id="sales" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//促销列表
	?>
		<script type="text/javascript">$(document).ready(function(){sales_obj.sales_list_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="40%" nowrap="nowrap">{/sales.package.product_info/}</td>
					<td width="10%" nowrap="nowrap">{/sales.tuan.type/}</td>
					<td width="20%" nowrap="nowrap">{/sales.package.duration/}</td>
					<td width="10%" nowrap="nowrap">{/sales.coupon.status/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=str::str_code($_GET['Keyword']);
				$Status=(int)$_GET['Status'];
				$where='IsPromotion=1';//条件
				$page_count=30;//显示数量
				$Keyword && $where.=" and (Name{$c['manage']['web_lang']} like '%$Keyword%' or concat(Prefix, Number) like '%$Keyword%')";
				if($Status){
					if($Status==1){//售卖中
						$where.=" and StartTime<{$c['time']} and {$c['time']}<EndTime";
					}elseif($Status==2){//未开始
						$where.=" and StartTime>{$c['time']}";
					}else{//已结束
						$where.=" and EndTime<{$c['time']}";
					}
				}
				$sales_row=str::str_code(db::get_limit_page('products', $where, '*', 'EndTime desc, '.$c['my_order'].'ProId desc', (int)$_GET['page'], $page_count));
				
				$i=1;
				foreach($sales_row[0] as $v){
					$img=ly200::get_size_img($v['PicPath_0'], '168x168');
					$name=$v['Name'.$c['manage']['web_lang']];
					$price=manage::range_price($v, 1);
					$biref=$v['BriefDescription'.$c['manage']['web_lang']];
					
					if($v['StartTime']<$c['time'] && $c['time']<$v['EndTime']){
						$s_key=1;//售卖中
					}elseif($v['StartTime']>$c['time']){
						$s_key=2;//未开始
					}else{
						$s_key=3;//已结束
					}
				?>
				<tr>
					<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['ProId'];?>" class="va_m" /></td><?php }?>
					<td class="left">
						<div class="p_row">
							<div class="p_img fl"><img src="<?=$img;?>" alt="<?=$name;?>" price="<?=$price;?>" number="<?=$v['Prefix'].$v['Number'];?>" biref="<?=$biref;?>" align="absmiddle" height="50" /></div>
							<div class="p_info">
								<div class="p_name"><?=$name;?></div>
								<p>{/products.products.price/}: <?=$price;?></p>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<td nowrap="nowrap"><?=$v['PromotionType']==0?'{/products.products.money/}('.$c['manage']['currency_symbol'].$v['PromotionPrice'].')':'{/products.products.discount/}('.$v['PromotionDiscount'].'%)';?></td>
					<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['StartTime']).' ~ '.date('Y-m-d H:i:s', $v['EndTime']);?></td>
					<td nowrap="nowrap"><?=$status_ary[$s_key];?></td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td nowrap="nowrap">
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=sales&a=sales&d=edit&ProId=<?=$v['ProId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=sales.sales_del&ProId=<?=$v['ProId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($sales_row[1], $sales_row[2], $sales_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}elseif($c['manage']['do']=='add'){
		//促销产品添加
		$where='IsPromotion=0';
		$page_count=12;//显示数量
		$Name=str::str_code($_GET['Name']);
		$CateId=(int)$_GET['CateId'];
		$Name && $where.=" and (Name{$c['manage']['web_lang']} like '%$Name%' or concat(Prefix, Number) like '%$Name%')";
		if($CateId){
			$UId=category::get_UId_by_CateId($CateId);
			$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
		}
		$p_remove_pid = $_POST['remove_pid'] ? $_POST['remove_pid'] : $_GET['remove_pid'];
		trim($p_remove_pid,',') && $package_where_ary = @explode(',', substr($p_remove_pid, 1, -1));
		if($package_where_ary){	
			$remove_pid = $where_remove_pid = @implode(',', $package_where_ary);
			$remove_pid = ','.trim($remove_pid,',').',';
		}
		$where_remove_pid && $where.=" and ProId not in ({$where_remove_pid})";
		$products_row=str::str_code(db::get_limit_page('products', $where." and ((SoldOut=0 and IsSoldOut=0) or (IsSoldOut=1 and SStartTime<{$c['time']} and {$c['time']}<SEndTime))", '*', $c['my_order'].'ProId desc', (int)$_GET['page'], $page_count));
		?>
		<?=ly200::load_static('/static/js/plugin/drag/drag.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
		<script type="text/javascript">$(document).ready(function(){sales_obj.package_edit_init();});</script>
		<div class="list_box">
			<div class="lefter">
				<form id="tuan_form">
					<div class="p_title mar_t_0">{/sales.package.product_sales_area/}</div>
					<div class="rows">
						<span class="th">{/global.time/}: </span> <input name="PromotionTime" type="text" value="<?=date('Y-m-d H:i',time()).'/'.date('Y-m-d H:i',time()); ?>" class="start_time form_input" size="50" />
					</div>
					<div class="p_related_frame p_frame">
						<div class="p_related_notice">{/sales.package.sales_notice/}</div>
					</div>
					<div class="related_bottom">
						<div class="related_btn">
							<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
							<a href="./?m=sales&a=sales" class="btn_cancel fr">{/global.return/}</a>
						</div>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="PId" value="" />
					<input type="hidden" name="ProId" id="proid_hide" value="" />
					<input type="hidden" name="PackageProId" id="packageproid_hide" value="" />
					<input type="hidden" name="do_action" value="sales.sales_add" />
					<input type="hidden" name="Type" id="type_hide" value="3" />
					<input type="hidden" name="IsMain" id="is_main" value="0" />
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
							<input type="hidden" name="remove_pid" value="<?=$remove_pid ? $remove_pid : ','; ?>" />
							<input type="hidden" name="m" value="sales" />
							<input type="hidden" name="a" value="sales" />
							<input type="hidden" name="d" value="add" />
							<input type="hidden" name="PId" value="<?=$PId;?>" />
							<div class="clear"></div>
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
					<div id="product_item_<?=$proid;?>" class="product_item" pro_num="<?=$proid;?>">
						<div img_num="<?=$proid;?>" id="p_img_<?=$proid;?>" class="p_img"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
						<div class="p_info">
							<div class="p_list"><span><?=str::str_echo($name, 100, 0,'...');?></span></div>
							<div class="p_list p_price"><span><?=manage::range_price($v, 1);?></span></div>
							<div class="p_list">{/products.product/}{/products.products.number/}: <span><?=$v['Prefix'].$v['Number'];?></span></div>
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
    <?php
	}elseif($c['manage']['do']=='batch_edit'){
		//促销产品添加
		$where='1';
		$id_list=str::ary_format($_GET['id_list'], 2, '', '-');
		$id_list && $where.=" and ProId in ({$id_list})";
		$sales_row=db::get_all('products',$where,'*',$c['my_order'].'ProId desc');
		?>
		<?=ly200::load_static('/static/js/plugin/drag/drag.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
		<script type="text/javascript">$(document).ready(function(){sales_obj.package_edit_init();});</script>
		<div class="list_box">
			<div class="lefter" style="width: 100%;">
				<form id="tuan_form">
					<div class="p_title mar_t_0">{/sales.package.product_sales_area/}</div>
					<div class="rows">
						<span class="th">{/global.time/}: </span> <input name="PromotionTime" type="text" value="" class="start_time form_input" size="50" notnull="" />
					</div>
					<div class="p_related_frame p_frame">
						<?php foreach((array)$sales_row as $v){ 
							$ProId = $v['ProId'];
							$name = $v['Name'.$c['manage']['web_lang']];
							$img = ly200::get_size_img($v['PicPath_0'],'240x240');
							?>
							<div id="related_product_<?=$ProId; ?>" class="p_related_item">
								<div class="p_related_img"><img src="<?=$img; ?>"></div>
								<div class="p_related_info">
									<div class="related_list p_name"> <span><?=$name; ?></span></div>
									<div class="related_list related_big_list">
										<input type="radio" name="PromotionType[<?=$ProId; ?>]" value="0" class="promotion_type" <?=$v['PromotionType']==0?' checked':'';?> style=""> 
										{/products.products.money/}: <?=$c['manage']['currency_symbol']?><input name="PromotionPrice[]" type="text" value="<?=$v['PromotionPrice'];?>" class="form_input" size="5" maxlength="10" <?=$v['PromotionType']==0?'notnull="notnull"':'';?> >&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="PromotionType[<?=$ProId; ?>]" value="1" class="promotion_type" <?=$v['PromotionType']==1?' checked':'';?> style=""> 
										{/products.products.discount/}: <input name="PromotionDiscount[]" type="text" value="<?=$v['PromotionDiscount']?$v['PromotionDiscount']:100;?>" class="form_input null" size="5" maxlength="5" <?=$v['PromotionType']==1?'notnull="notnull"':'';?> >%
										<input type="hidden" name="ProId[]" value="<?=$ProId; ?>" />
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<div class="related_bottom">
						<div class="related_btn">
							<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
							<a href="./?m=sales&a=sales" class="btn_cancel fr">{/global.return/}</a>
						</div>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="do_action" value="sales.sales_batch_edit" />
					<input type="hidden" name="Type" id="type_hide" value="3" />
					<input type="hidden" name="IsMain" id="is_main" value="0" />
					<input type="hidden" id="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" />
				</form>
			</div>
			<div class="clear"></div>
		</div>
    <?php
	}else{
		//促销产品编辑
		$ProId=(int)$_GET['ProId'];
		$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
		$pro_img=ly200::get_size_img($products_row['PicPath_0'], '168x168');
		$pro_name=$products_row['Name'.$c['manage']['web_lang']];
		$pro_price=manage::range_price($products_row, 1);
	?>
	<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
	<script type="text/javascript">$(document).ready(function(){sales_obj.sales_edit_init();});</script>
	<div class="blank9"></div>
	<div class="edit_bd list_box">
		<form id="sales_edit_form" name="sales_edit_form" class="r_con_form">
			<div class="rows_box">
				<div class="rows">
					<label>{/sales.package.product_info/}</label>
					<span class="input">
						<div class="p_row">
							<div class="p_img fl pic_box"><img src="<?=$pro_img;?>" alt="<?=$pro_name;?>" align="absmiddle" /><span></span></div>
							<div class="p_info">
								<div class="p_name">{/products.name/}: <?=$pro_name;?></div>
								<p>{/products.products.price/}: <?=$pro_price;?></p>
								<p>{/products.products.number/}: <?=$products_row['Prefix'].$products_row['Number'];?></p>
							</div>
							<div class="clear"></div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/module.sales.sales/}</label>
					<span class="input">
						<input type="radio" name="PromotionType" value="0"<?=$products_row['PromotionType']==0?' checked':'';?> /> {/products.products.money/}&nbsp;&nbsp;<input type="radio" name="PromotionType" value="1"<?=$products_row['PromotionType']==1?' checked':'';?> /> {/products.products.discount/}
						<div class="blank6"></div>
						<div class="promotion_money" style="display:<?=$products_row['PromotionType']==0?'':'none';?>;">
							<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="PromotionPrice" value="<?=$products_row['PromotionPrice'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount"<?=$products_row['PromotionType']==1?' disabled':'';?> /></span><span class="tool_tips_ico" content="{/products.products.promotion_price_notes/}"></span>
						</div>
						<div class="promotion_discount" style="display:<?=$products_row['PromotionType']==1?'':'none';?>;">
							<span class="price_input"><input name="PromotionDiscount" value="<?=$products_row['PromotionDiscount']?$products_row['PromotionDiscount']:100;?>" type="text" class="form_input" maxlength="5" size="5"><b class="last">%</b></span><span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span>
						</div>
						<div class="blank6"></div>
						{/products.products.promotion/}{/global.time/}: <input name="PromotionTime" value="<?=date('Y-m-d H:i:s',($products_row['StartTime']?$products_row['StartTime']:$c['time'])).'/'.date('Y-m-d H:i:s', ($products_row['EndTime']?$products_row['EndTime']:$c['time']));?>" type="text" class="form_input" size="42" readonly>
					</span>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=sales&a=sales" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
			</div>
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" name="do_action" value="sales.sales_edit" />
		</form>
	</div>
	<?php 
		unset($products_row, $pro_row);
	}
	?>
</div>