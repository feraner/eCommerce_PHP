<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'tuan'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('sales', 0, array('a'=>'tuan', 'd'=>'add')),
	'edit'	=>	manage::check_permit('sales', 0, array('a'=>'tuan', 'd'=>'edit')),
	'del'	=>	manage::check_permit('sales', 0, array('a'=>'tuan', 'd'=>'del'))
);
?>
<script type="text/javascript">var lang_str_obj={'currency':'<?=$c['manage']['currency_symbol'];?>', 'now_time':'<?=date('Y-m-d H:i', $c['time']);?>'};</script>
<div class="r_nav">
	<h1>{/module.sales.tuan/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$status_ary=array(
			0=>manage::language('{/sales.tuan.full/}'),//已满额
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
				<input type="hidden" name="a" value="tuan" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=sales&a=tuan&d=add" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
			<li><a class="tip_ico_down bat_close" href="javascript:;" label="{/products.products.batch_edit/}"></a></li>
		</ul>
	<?php }?>
</div>
<div id="sales" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//团购列表
	?>
		<script type="text/javascript">$(document).ready(function(){sales_obj.package_frame_init(); sales_obj.tuan_list_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="36%" nowrap="nowrap">{/sales.package.product_info/}</td>
					<td width="20%" nowrap="nowrap">{/sales.package.duration/}</td>
					<td width="5%" nowrap="nowrap">{/sales.package.tuan_price/}</td>
					<td width="5%" nowrap="nowrap">{/sales.package.buyer_count/}</td>
					<td width="5%" nowrap="nowrap">{/sales.package.total_count/}</td>
					<td width="5%" nowrap="nowrap">{/sales.coupon.status/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
					<td width="6%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i=1;
				$Keyword=str::str_code($_GET['Keyword']);
				$Status=(int)$_GET['Status'];
				
				$where='1';//条件
				$page_count=10;//显示数量
				$Keyword && $where.=" and ProId in(select ProId from products where Name{$c['manage']['web_lang']} like '%{$Keyword}%' or concat(Prefix, Number) like '%$Keyword%')";
				if($Status){
					if($Status==1){//售卖中
						$where.=" and StartTime<{$c['time']} and {$c['time']}<EndTime";
					}elseif($Status==2){//未开始
						$where.=" and StartTime>{$c['time']}";
					}else{//已结束
						$where.=" and EndTime<{$c['time']}";
					}
				}
				$tuan_row=str::str_code(db::get_limit_page('sales_tuan', $where, '*', $c['my_order'].'TId desc', (int)$_GET['page'], $page_count));
				
				$pro_where='ProId in(0';
				foreach($tuan_row[0] as $v) $pro_where.=",{$v['ProId']}";
				$pro_where.=')';
				$pro_ary=array();
				$pro_row=str::str_code(db::get_all('products', $pro_where, '*', 'ProId desc'));
				foreach($pro_row as $v) $pro_ary[$v['ProId']]=$v;
				
				foreach($tuan_row[0] as $v){
					$img=ly200::get_size_img($pro_ary[$v['ProId']]['PicPath_0'], '168x168');
					$name=$pro_ary[$v['ProId']]['Name'.$c['manage']['web_lang']];
					$tuan_ary=explode('|', substr($v['PackageProId'], 1, -1));
					$price=manage::range_price($pro_ary[$v['ProId']], 1);
					$biref=$pro_ary[$v['ProId']]['BriefDescription'.$c['manage']['web_lang']];
					
					if($v['BuyerCount']>=$v['TotalCount']){
						$s_key=0;//已满额
					}elseif($v['StartTime']<$c['time'] && $c['time']<$v['EndTime']){
						$s_key=1;//售卖中
					}elseif($v['StartTime']>$c['time']){
						$s_key=2;//未开始
					}else{
						$s_key=3;//已结束
					}
				?>
				<tr>
					<?php if($permit_ary['del']){?><td><input type="checkbox" name="select" value="<?=$v['TId'];?>" class="va_m" /></td><?php }?>
					<td class="left">
						<div class="p_row">
							<div class="p_img fl"><img src="<?=$img;?>" alt="<?=$name;?>" price="<?=$price;?>" number="<?=$pro_ary[$v['ProId']]['Number'];?>" biref="<?=$biref;?>" align="absmiddle" height="50" /></div>
							<div class="p_info">
								<div class="p_name"><?=$name;?></div>
								<p class="p_price">{/products.products.price/}: <?=$price;?></p>
							</div>
							<div class="clear"></div>
						</div>
					</td>
					<td><?=date('Y-m-d H:i:s', $v['StartTime']).' ~ '.date('Y-m-d H:i:s', $v['EndTime']);?></td>
					<td><?=$c['manage']['currency_symbol'].sprintf('%01.2f', $v['Price']);?></td>
					<td><?=$v['BuyerCount'];?></td>
					<td><?=$v['TotalCount'];?></td>
					<td nowrap="nowrap"><?=$status_ary[$s_key];?></td>
					<td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<td nowrap="nowrap"<?=$permit_ary['edit']?' class="myorder_select" data-num="'.$v['MyOrder'].'"':'';?>><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td>
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=sales&a=tuan&d=edit&TId=<?=$v['TId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=sales.tuan_del&TId=<?=$v['TId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($tuan_row[1], $tuan_row[2], $tuan_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
	<?php
		unset($tuan_row, $pro_row, $pro_ary);
	}elseif($c['manage']['do']=='add'){
		//团购产品添加
		$where='1';
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
			<div class="p_title mar_t_0">{/sales.package.product_tuan_area/}</div>
			<div class="rows">
				<span class="th">{/global.time/}: </span> <input name="PromotionTime" type="text" value="<?=date('Y-m-d H:i',time()).'/'.date('Y-m-d H:i',time()); ?>" class="start_time form_input" size="50" />
			</div>
			<div class="p_related_frame p_frame">
				<div class="p_related_notice">{/sales.package.tuan_notice/}</div>
			</div>
			<div class="related_bottom">
				<div class="related_btn">
					<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=sales&a=tuan" class="btn_cancel fr">{/global.return/}</a>
				</div>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="PId" value="<?=$PId;?>" />
			<input type="hidden" name="ProId" id="proid_hide" value="<?=$ProId;?>" />
			<input type="hidden" name="PackageProId" id="packageproid_hide" value="<?=$PackageProId;?>" />
			<input type="hidden" name="do_action" value="sales.tuan_add" />
			<input type="hidden" name="Type" id="type_hide" value="1" />
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
						<input type="hidden" name="PId" value="<?=$PId;?>" />
						<input type="hidden" name="remove_pid" value="<?=$remove_pid ? $remove_pid : ','; ?>" />
						<input type="hidden" name="m" value="sales" />
						<input type="hidden" name="a" value="tuan" />
						<input type="hidden" name="d" value="add" />
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
		unset($products_row);
	}elseif($c['manage']['do']=='batch_edit'){
		//团购产品添加
		$where='1';
		$id_list=str::ary_format($_GET['id_list'], 2, '', '-');
		$id_list && $where.=" and TId in ({$id_list})";
		$tuan_row=db::get_all('sales_tuan',$where,'*',$c['my_order'].'TId desc');
	?>
    <?=ly200::load_static('/static/js/plugin/drag/drag.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
    <script type="text/javascript">$(document).ready(function(){sales_obj.package_edit_init();});</script>
	<div class="list_box">
		<div class="lefter" style="width: 100%;">
			<form id="tuan_form">
			<div class="p_title mar_t_0">{/sales.package.product_tuan_area/}</div>
			<div class="rows">
				<span class="th">{/global.time/}: </span> <input name="PromotionTime" type="text" value="" notnull="" class="start_time form_input" size="50" />
			</div>
			<div class="p_related_frame p_frame">
				<?php foreach((array)$tuan_row as $v){ 
					$ProId = $v['ProId'];
					$pro_row = db::get_one('products',"ProId = '{$ProId}'");
					$name = $pro_row['Name'.$c['manage']['web_lang']];
					$img = ly200::get_size_img($pro_row['PicPath_0'],'240x240');
					?>
					<div id="related_product_<?=$ProId; ?>" class="p_related_item">
						<div class="p_related_img"><img src="<?=$img; ?>"></div>
						<div class="p_related_info">
							<div class="related_list p_name"> <span><?=$name; ?></span></div>
							<div class="related_list related_big_list">
								{/sales.package.tuan_price/}: <?=$c['manage']['currency_symbol']?><input name="Price[]" type="text" value="<?=$v['Price'];?>" class="form_input" size="5" maxlength="5" notnull="">&nbsp;&nbsp;&nbsp;&nbsp;
								{/sales.package.buyer_count/}: <input name="BuyerCount[]" type="text" value="<?=$v['BuyerCount'];?>" class="form_input" size="5" maxlength="10" notnull="">&nbsp;&nbsp;&nbsp;&nbsp;
								{/sales.package.total_count/}: <input name="TotalCount[]" type="text" value="<?=$v['TotalCount'];?>" class="form_input" size="5" maxlength="10" notnull="">
								<input type="hidden" name="TId[]" value="<?=$v['TId']; ?>" />
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="related_bottom">
				<div class="related_btn">
					<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=sales&a=tuan" class="btn_cancel fr">{/global.return/}</a>
				</div>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="sales.tuan_batch_edit" />
			<input type="hidden" name="Type" id="type_hide" value="1" />
			<input type="hidden" name="IsMain" id="is_main" value="0" />
			<input type="hidden" id="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" />
			</form>
		</div>
		<div class="clear"></div>
	</div>
    <?php
		unset($products_row);
	}else{
		//团购产品编辑
		$TId=(int)$_GET['TId'];
		$tuan_row=str::str_code(db::get_one('sales_tuan', "TId='$TId'"));
		$start_time=$tuan_row['StartTime'];
		$duration_time=ceil($tuan_row['EndTime']-$start_time)/3600;
		$ProId=$tuan_row['ProId'];
		$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
		$pro_img=ly200::get_size_img($pro_row['PicPath_0'], '168x168');
		$pro_name=$pro_row['Name'.$c['manage']['web_lang']];
	?>
	<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
	<script type="text/javascript">$(document).ready(function(){sales_obj.tuan_edit_init();});</script>
	<div class="blank9"></div>
	<div class="edit_bd list_box">
		<form id="tuan_edit_form" name="tuan_edit_form" class="r_con_form">
			<div class="rows_box">
				<div class="rows">
					<label>{/sales.package.product_info/}</label>
					<span class="input">
						<div class="p_row">
							<div class="p_img fl pic_box"><img src="<?=$pro_img;?>" alt="<?=$pro_name;?>" align="absmiddle" /><span></span></div>
							<div class="p_info">
								<div class="p_name">{/products.name/}: <?=$pro_name;?></div>
								<p>{/products.products.price/}: <?=$c['manage']['currency_symbol'].sprintf('%01.2f', $pro_row['Price_1']);?></p>
								<p>{/products.products.number/}: <?=$pro_row['Prefix'].$pro_row['Number'];?></p>
							</div>
							<div class="clear"></div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/sales.package.duration/}</label>
					<span class="input"><input name="PromotionTime" value="<?=date('Y-m-d H:i:s',($tuan_row['StartTime']?$tuan_row['StartTime']:$c['time'])).'/'.date('Y-m-d H:i:s', ($tuan_row['EndTime']?$tuan_row['EndTime']:$c['time']));?>" type="text" class="form_input" size="36" readonly></span>
				</div>
				<div class="rows">
					<label>{/sales.package.tuan_price/}</label>
					<span class="input">
						<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Price" value="<?=$tuan_row['Price'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" notnull /></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/sales.package.buyer_count/}</label>
					<span class="input"><input name="BuyerCount" type="text" value="<?=$tuan_row['BuyerCount'];?>" class="form_input" size="5" maxlength="5"></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/sales.package.total_count/}</label>
					<span class="input"><input name="TotalCount" type="text" value="<?=$tuan_row['TotalCount'];?>" class="form_input" size="5" maxlength="5"></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=sales&a=tuan" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
			</div>
			<input type="hidden" id="TId" name="TId" value="<?=$TId;?>" />
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" name="do_action" value="sales.tuan_edit" />
		</form>
	</div>
	<?php
    	unset($tuan_row, $pro_row);
	}
	?>
</div>