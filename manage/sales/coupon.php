<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'coupon'));//检查权限

$permit_ary=array(
	'add'		=>	manage::check_permit('sales', 0, array('a'=>'coupon', 'd'=>'add')),
	'edit'		=>	manage::check_permit('sales', 0, array('a'=>'coupon', 'd'=>'edit')),
	'del'		=>	manage::check_permit('sales', 0, array('a'=>'coupon', 'd'=>'del')),
	'export'	=>	manage::check_permit('sales', 0, array('a'=>'coupon', 'd'=>'export'))
);
$CouponWay = (int)$_GET['CouponWay'];
?>
<div class="r_nav">
	<h1>{/module.sales.coupon/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$status_arr=array(
			1=>manage::language('{/sales.coupon.normal/}'),//正常
			2=>manage::language('{/sales.coupon.use_end/}'),//次数已用完
			3=>manage::language('{/sales.coupon.not_start/}'),//未开始
			4=>manage::language('{/sales.coupon.expire/}'),//已过期
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
						<label>{/sales.coupon.status/}</label>
						<span class="input"><?=ly200::form_select($status_arr, 'status', '', '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/sales.coupon.type/}</label>
						<span class="input"><select name="CouponType">
							<option value="-1">{/global.select_index/}</option>
							<option value="0">{/sales.coupon.discount/}</option>
							<option value="1">{/sales.coupon.over_money/}</option>
						</select></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/sales.coupon.bind/}</label>
						<span class="input"><select name="IsUser">
							<option value="-1">{/global.select_index/}</option>
							<option value="1">{/global.n_y.1/}</option>
							<option value="0">{/global.n_y.0/}</option>
						</select></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="sales" />
				<input type="hidden" name="a" value="coupon" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=sales&a=coupon&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
			<?php if($permit_ary['export']&&$CouponWay==0){?><li><a class="tip_ico_down explode" href="./?m=sales&a=coupon&d=coupon_explode" label="{/global.explode/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<dd><a href="./?m=sales&a=coupon&CouponWay=0"<?=$CouponWay==0?' class="current"':'';?>>{/sales.coupon.rele_type_0/}</a></dd>
		<dt></dt>
		<dd><a href="./?m=sales&a=coupon&CouponWay=1"<?=$CouponWay==1?' class="current"':'';?>>{/sales.coupon.rele_type_1/}</a></dd>
		<dt></dt>
		<dd><a href="./?m=sales&a=coupon&CouponWay=2"<?=$CouponWay==2?' class="current"':'';?>>{/sales.coupon.rele_type_2/}</a></dd>
	</dl>
</div>
<div id="coupon" class="r_con_wrap">
	<?php
    if($c['manage']['do']=='index'){
		$i=1;
		$where='CouponWay=0';
		$CouponWay && $where="CouponWay='{$CouponWay}'";
		$CouponType=(int)$_GET['CouponType'];
		$IsUser=(int)$_GET['IsUser'];//是否绑定会员了
		$Keyword=$_GET['Keyword'];
		$status=(int)$_GET['status'];//状态
		(isset($_GET['CouponType']) && $CouponType==0 || $CouponType==1) && $where.=" and CouponType='{$CouponType}'";
		(isset($_GET['IsUser']) && $IsUser==0) && $where.=' and UserId=0';
		$IsUser==1 && $where.=' and UserId!=0';
		$Keyword && $where.=" and CouponNumber='{$Keyword}'";
		switch ($status){//根据状态筛选优惠券
			case 1:
				$where.=" and StartTime<={$c['time']} and EndTime>={$c['time']}";
				break;
			case 2:
				$where.=" and UseNum>0 and BeUseTimes>=UseNum";
				break;
			case 3:
				$where.=" and StartTime>{$c['time']}";
				break;
			case 4:
				$where.=" and EndTime<{$c['time']}";
				break;
		}
		$coupon_row=str::str_code(db::get_limit_page('sales_coupon', $where, '*', 'AccTime desc', (int)$_GET['page'], 20));
	?>
    	<script type="text/javascript">$(document).ready(function(){sales_obj.coupon_list_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="3%"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="5%" nowrap="nowrap">{/global.serial/}</td>
					<?php if(!$CouponWay){ ?><td width="12%" nowrap="nowrap">{/sales.coupon.code/}</td><?php } ?>
					<td width="9%" nowrap="nowrap">{/sales.coupon.type/}</td>
					<td width="9%" nowrap="nowrap">{/sales.coupon.condition/}</td>
					<td width="12%" nowrap="nowrap">{/sales.coupon.deadline/}</td>
					<td width="10%" nowrap="nowrap">{/sales.coupon.last_time/}</td>
					<td width="10%" nowrap="nowrap">{/sales.coupon.acctime/}</td>
					<td width="5%" nowrap="nowrap">{/sales.coupon.bind/}</td>
					<td width="8%" nowrap="nowrap">{/sales.coupon.use/}{/sales.coupon.times/}</td>
					<td width="8%" nowrap="nowrap">{/sales.coupon.status/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" class="last" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($coupon_row[0] as $v){
					if($v['UseNum'] && $v['BeUseTimes']>=$v['UseNum']){
						$s_key=2;//次数已用完
					}elseif($v['StartTime']>$c['time']){
						$s_key=3;//未开始
					}elseif($v['EndTime']<$c['time']){
						$s_key=4;//已过期
					}else{
						$s_key=1;//正常
					}
				?>
					<tr>
						<?php if($permit_ary['del']){?><td><input type="checkbox" name="select" value="<?=$v['CId']?>" /></td><?php }?>
						<td><?=$coupon_row[4]+$i++;?></td>
						<?php if(!$CouponWay){ ?><td><?=$v['CouponNumber'];?></td><?php } ?>
						<td><?=$v['CouponType']==1?'{/sales.coupon.money/}('.$c['manage']['currency_symbol'].$v['Money'].')':'{/sales.coupon.discount/}('.$v['Discount'].'%)';?></td>
						<td><?=$v['UseCondition']!='0.00'?'{/sales.coupon.over/}'.$c['manage']['currency_symbol'].$v['UseCondition']:'{/sales.coupon.unlimit/}';?></td>
						<td><?=manage::time_between($v['StartTime'],$v['EndTime'])?></td>
						<td><?=$v['UsedTime']?date('Y-m-d H:i:s',$v['UsedTime']):'{/sales.coupon.not_use/}'?></td>
						<td><?=date('Y-m-d H:i:s',$v['AccTime'])?></td>
						<td><?=$v['UserId']?'{/global.n_y.1/}':'{/global.n_y.0/}'?></td>
						<td><?=$v['BeUseTimes'].' / '.($v['UseNum']==0?'{/sales.coupon.unlimit/}':$v['UseNum']);?></td>
						<td nowrap="nowrap"><?=$status_arr[$s_key];?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td class="last flh_180">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=sales&a=coupon&d=edit&CId=<?=$v['CId']?>&CouponWay=<?=$CouponWay; ?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=sales.coupon_del&CId=<?=$v['CId'];?>" rel="del" label="{/global.del/}"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
	    <div id="turn_page"><?=manage::turn_page($coupon_row[1], $coupon_row[2], $coupon_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
	<?php
	}elseif($c['manage']['do']=='edit'){
		//编辑页
		$CId=(int)$_GET['CId'];
		$CId && $coupon_row=str::str_code(db::get_one('sales_coupon', "CId='$CId'"));
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1', "LId, Name{$c['manage']['web_lang']}", 'FullPrice desc'));//会员等级
		$level_row[count($level_row)]=array('LId'=>0, 'Name'.$c['manage']['web_lang']=>'No level');
		
		$Keyword=$_GET['Keyword'];
		$where='1';//条件
		$Keyword && $where.=" and (FirstName like '%$Keyword%' or LastName like '%$Keyword%' or concat(FirstName, LastName) like '%$Keyword%' or Email like '%$Keyword%')";
		$page_count=100;//显示数量
		$user_row=str::str_code(db::get_limit_page('user', $where, '*', 'UserId desc', (int)$_GET['page'], $page_count));
		$lang=$c['manage']['web_lang'];
		$lang_all=$c['manage']['lang_pack']['global']['all'];
	?>
    	<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js', '/static/themes/default/css/user.css');?>
		<script language="javascript">$(function(){sales_obj.coupon_edit_init()});</script>
		<form id="coupon_form" class="r_con_form">
			<div class="rows" <?=$coupon_row ? 'style="display:none;"' : ''; ?>>
				<label>{/sales.coupon.rele_type/}</label>
				<span class="input">
					<input type="radio" name="CouponWay" value="0" <?=$coupon_row['CouponWay']==0?'checked="checked"':'';?> /> {/sales.coupon.rele_type_0/}
					<input type="radio" name="CouponWay" value="1" <?=$coupon_row['CouponWay']==1?'checked="checked"':'';?> /> {/sales.coupon.rele_type_1/}
					<?php if(!db::get_row_count('sales_coupon','CouponWay=2')){ ?>
						<input type="radio" name="CouponWay" value="2" <?=$coupon_row['CouponWay']==2?'checked="checked"':'';?> /> {/sales.coupon.rele_type_2/}
					<?php } ?>
				</span>
				<div class="clear"></div>
			</div>
			<?php if (!$coupon_row || $coupon_row['CouponWay']){
				$ExtAry = str::json_data(htmlspecialchars_decode($coupon_row['CouponExt']),'decode');
				?>
				<div class="rows">
					<label>{/sales.coupon.prefix/}</label>
					<span class="input">
						<input name="Prefix" value="<?=$ExtAry['Prefix']; ?>" type="text" class="form_input" maxlength="5" size="10" />
						<span class="tool_tips_ico" content="{/sales.coupon.prefix_tips/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/sales.coupon.code_len/}</label>
					<span class="input">
						<input name="CodeLen" value="<?=$ExtAry['CodeLen'] ? $ExtAry['CodeLen'] : '8'; ?>" type="text" class="form_input" maxlength="2" size="5" notnull /> <font class="fc_red">*</font>
						<span class="tool_tips_ico" content="{/sales.coupon.len_tips/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows couponway" <?=$coupon_row['CouponWay'] ? 'style="display:none;"' : ''; ?>>
					<label>{/sales.coupon.qty/}</label>
					<span class="input">
						<input name="Qty" value="1" type="text" class="form_input" maxlength="5" size="5" notnull /> <font class="fc_red">*</font>
						<span class="tool_tips_ico" content="{/sales.coupon.qty_tips/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/sales.coupon.chars/}</label>
					<span class="input">
						<input type="checkbox" name="c1" value="1" <?=($ExtAry['c1'] || !$coupon_row) ? 'checked="checked"' : ''; ?> /> {/sales.coupon.character/}<br>
						<input type="checkbox" name="c2" value="1" <?=($ExtAry['c2'] || !$coupon_row) ? 'checked="checked"' : ''; ?> /> {/sales.coupon.number/}
					</span>
					<div class="clear"></div>
				</div>
			<?php }else{?>
				<div class="rows">
					<label>{/sales.coupon.code/}</label>
					<span class="input">
						<input name="CouponNumber" value="<?=$coupon_row['CouponNumber'];?>" type="text" class="form_input" maxlength="20" size="20" notnull />
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label>{/sales.coupon.deadline/}</label>
				<span class="input">
					<input name="DeadLine" value="<?=($coupon_row['StartTime'] && $coupon_row['EndTime'])?date('Y-m-d H:i:s', $coupon_row['StartTime']).'/'.date('Y-m-d H:i:s', $coupon_row['EndTime']):'';?>" type="text" class="form_input" size="42" readonly notnull /> 
					<font class="fc_red">*</font>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.type/}</label>
				<span class="input">
					<input type="radio" name="CouponType" value="0" <?=$coupon_row['CouponType']==0?'checked="checked"':'';?> /> {/sales.coupon.discount/}
					<input type="radio" name="CouponType" value="1" <?=$coupon_row['CouponType']==1?'checked="checked"':'';?> /> {/sales.coupon.over_money/}
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows discount <?=$coupon_row['CouponType']==0?'':'none';?>">
				<label>{/sales.coupon.discount/}</label>
				<span class="input">
					<span class="price_input"><input name="Discount" value="<?=$coupon_row['Discount']?$coupon_row['Discount']:100;?>" type="text" class="form_input" maxlength="5" size="5" /><b class="last">%</b></span>
					<span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows money <?=$coupon_row['CouponType']==1?'':'none';?>">
				<label>{/sales.coupon.money/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Money" value="<?=$coupon_row['Money']?$coupon_row['Money']:0;?>" type="text" class="form_input" maxlength="5" size="5" /></span>
					<span class="tool_tips_ico" content="{/sales.coupon.money_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.condition/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="UseCondition" value="<?=$coupon_row['UseCondition']?$coupon_row['UseCondition']:0;?>" type="text" class="form_input" maxlength="10" size="5" /></span>
					<span class="tool_tips_ico" content="{/sales.coupon.condition_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<?php
			//限制条件
			$curStr='';
			$coupon_row['UserId'] && $curStr.=$c['manage']['lang_pack']['sales']['coupon']['individual_user'];
			$coupon_row['LevelId'] && $curStr.=($curStr?', ':'').$c['manage']['lang_pack']['sales']['coupon']['individual_level'];
			?>
			<div class="rows couponway" <?=$coupon_row['CouponWay'] ? 'style="display:none;"' : ''; ?>>
				<label>{/sales.coupon.limiting/}</label>
				<span class="input nohidden">
					<div class="range_tools range_user">
						<div class="range_search">
							<input name="SearchA" value="<?=$curStr;?>" type="text" class="form_input" size="28" maxlength="150" rel="amount" />
							<div class="range_search_fixed">
								<div class="arrow"><em></em><i></i></div>
								<div class="range_search_menu">
									<a href="javascript:;" class="btn_back" data-type="user" data-id="0" data-back-pro="0"><em></em><i></i></a>
									<span class="price_input"><input name="SearchUser" value="" type="text" class="form_input" size="13" maxlength="150" rel="amount" data-type="user" /><b class="last">{/global.search/}</b></span>
									<div class="range_search_skin"></div>
									<div class="range_search_list"></div>
								</div>
							</div>
						</div>
						<div class="current_list" data-type="user">
							<?php
							if($coupon_row['UserId']){
								$level_where_ary=$user_where_ary=array();
								$UserId=$coupon_row['UserId'];
								!strstr($UserId, '|') && $UserId='|'.$UserId.'|';
								$UserAry=explode('|', substr($UserId, 1, -1));
								foreach($UserAry as $v){ $where_ary[]=$v; }
								if($where_ary){
									if(in_array('-1', $where_ary)){//所有会员
										echo '<div class="item" data-id="-1"><i class="icon icon_user"></i><span>'.$lang_all.$c['manage']['lang_pack']['sales']['coupon']['individual_user'].'</span><em data-type="user">X</em></div>';
									}
									$user_row=db::get_all('user', 'UserId in('.implode(',', $where_ary).')', 'UserId, Email', 'UserId desc');
									foreach($user_row as $v){
										echo '<div class="item" data-id="'.$v['UserId'].'"><i class="icon icon_user"></i><span>'.$v['Email'].'</span><em data-type="user">X</em></div>';
									}
								}
							}?>
						</div>
						<div class="clear"></div>
						<div class="current_list" data-type="level">
							<?php
							if($coupon_row['LevelId']){
								$where_ary=array();
								$CateAry=explode('|', substr($coupon_row['LevelId'], 1, -1));
								foreach($CateAry as $v){ $where_ary[]=$v; }
								if($where_ary){
									if(in_array('-1', $where_ary)){//所有会员等级
										echo '<div class="item" data-id="-1"><i class="icon icon_level"></i><span>'.$lang_all.$c['manage']['lang_pack']['sales']['coupon']['individual_level'].'</span><em data-type="level">X</em></div>';
									}
									$category_row=db::get_all('user_level', 'LId in('.implode(',', $where_ary).')', "LId, Name{$lang}", 'LId asc');
									foreach($category_row as $v){
										echo '<div class="item" data-id="'.$v['LId'].'"><i class="icon icon_level"></i><span>'.$v['Name'.$lang].'</span><em data-type="level">X</em></div>';
									}
								}
							}?>
						</div>
					</div>
					<input type="hidden" name="UserId" value="<?=$UserId;?>" />
					<input type="hidden" name="LevelId" value="<?=$coupon_row['LevelId'];?>" />
				</span>
				<div class="clear"></div>
			</div>
			<?php
			//限制条件
			$curStr='';
			$coupon_row['CateId'] && $curStr.=$c['manage']['lang_pack']['global']['category'];
			$coupon_row['ProId'] && $curStr.=($curStr?', ':'').$c['manage']['lang_pack']['sales']['coupon']['individual_pro'];
			$coupon_row['TagId'] && $curStr.=($curStr?', ':'').$c['manage']['lang_pack']['sales']['coupon']['individual_tag'];
			?>
			<div class="rows">
				<label>{/sales.coupon.apply/}</label>
				<span class="input nohidden">
					<div class="range_tools range_apply">
						<div class="range_search">
							<input name="SearchB" value="<?=$curStr;?>" type="text" class="form_input" size="28" maxlength="150" rel="amount" />
							<div class="range_search_fixed">
								<div class="arrow"><em></em><i></i></div>
								<div class="range_search_menu">
									<a href="javascript:;" class="btn_back" data-type="" data-id="0" data-back-pro="0"><em></em><i></i></a>
									<span class="price_input"><input name="SearchApply" value="" type="text" class="form_input" size="13" maxlength="150" rel="amount" data-type="products_category" /><b class="last">{/global.search/}</b></span>
									<div class="range_search_skin"></div>
									<div class="range_search_list"></div>
								</div>
							</div>
						</div>
						<div class="current_list" data-type="products_category">
							<?php
							if($coupon_row['CateId']){
								$where_ary=array();
								$CateAry=explode('|', substr($coupon_row['CateId'], 1, -1));
								foreach($CateAry as $v){ $where_ary[]=$v; }
								if($where_ary){
									$category_row=db::get_all('products_category', 'CateId in('.implode(',', $where_ary).')', "CateId, Category{$lang}", $c['my_order'].'CateId asc');
									foreach($category_row as $v){
										echo '<div class="item" data-id="'.$v['CateId'].'"><i class="icon icon_products_category"></i><span>'.$v['Category'.$lang].'</span><em data-type="products_category">X</em></div>';
									}
								}
							}?>
						</div>
						<div class="clear"></div>
						<div class="current_list" data-type="products">
							<?php
							if($coupon_row['ProId']){
								$where_ary=array();
								$ProAry=explode('|', substr($coupon_row['ProId'], 1, -1));
								foreach($ProAry as $v){ $where_ary[]=$v; }
								if($where_ary){
									$products_row=db::get_all('products', 'ProId in('.implode(',', $where_ary).')', "ProId, Name{$lang}", $c['my_order'].'ProId desc');
									foreach($products_row as $v){
										echo '<div class="item" data-id="'.$v['ProId'].'"><i class="icon icon_products"></i><span>'.$v['Name'.$lang].'</span><em data-type="products">X</em></div>';
									}
								}
							}?>
						</div>
						<div class="clear"></div>
						<div class="current_list" data-type="tags">
							<?php
							if($coupon_row['TagId']){
								$where_ary=array();
								$TagAry=explode('|', substr($coupon_row['TagId'], 1, -1));
								foreach($TagAry as $v){ $where_ary[]=$v; }
								if($where_ary){
									$products_row=db::get_all('products_tags', 'TId in('.implode(',', $where_ary).')', "TId, Name{$lang}", $c['my_order'].'TId desc');
									foreach($products_row as $v){
										echo '<div class="item" data-id="'.$v['TId'].'"><i class="icon icon_tags"></i><span>'.$v['Name'.$lang].'</span><em data-type="tags">X</em></div>';
									}
								}
							}?>
						</div>
					</div>
					<input type="hidden" name="CateId" value="<?=$coupon_row['CateId'];?>" />
					<input type="hidden" name="ProId" value="<?=$coupon_row['ProId'];?>" />
					<input type="hidden" name="TagId" value="<?=$coupon_row['TagId'];?>" />
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.use/}{/sales.coupon.times/}</label>
				<span class="input">
					<input name="UseNum" value="<?=(int)$coupon_row['UseNum']<0?0:(int)$coupon_row['UseNum'];?>" type="text" class="form_input" maxlength="10" size="5" />
					<span class="tool_tips_ico" content="{/sales.coupon.use_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=sales&a=coupon" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="CId" value="<?=$CId;?>" />
			<input type="hidden" name="do_action" value="sales.coupon_edit" />
		</form>
    <?php
	}else{
	?>
		<?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
		<script language="javascript">$(document).ready(function(){sales_obj.coupon_explode_init();});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/sales.coupon.deadline/}</label>
				<span class="input">
					<input type="radio" name="IsTime" value="0" checked /> {/global.all/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="IsTime" value="1" />
					<input name="DeadLine" value="<?=date('Y-m-d H:i:s', $c['time']).'/'.date('Y-m-d H:i:s', $c['time']);?>" type="text" class="form_input" size="42" readonly notnull> 
					<font class="fc_red">*</font>
				</span>
				<div class="clear"></div>
			</div>      
			<div class="rows">
				<label>{/sales.coupon.bind/}</label>
				<span class="input nohidden">
					<input type="radio" name="IsUser" value="0" checked /> {/global.all/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="IsUser" value="1" /> {/sales.coupon.bind/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="IsUser" value="2" /> {/sales.coupon.unbind/}
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.type/}</label>
				<span class="input">
					<input type="radio" name="CouponType" value="0" checked /> {/global.all/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="CouponType" value="1" /> {/sales.coupon.discount/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="CouponType" value="2" /> {/sales.coupon.over_money/}
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows discount none">
				<label>{/sales.coupon.discount/}</label>
				<span class="input">
					<span class="price_input"><input name="Discount" value="100" type="text" class="form_input" maxlength="5" size="5" /><b class="last">%</b></span>
					<span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows money none">
				<label>{/sales.coupon.money/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Money" value="0" type="text" class="form_input" maxlength="5" size="5" /></span>
					<span class="tool_tips_ico" content="{/sales.coupon.money_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.condition/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="UseCondition" value="<?=$coupon_row['UseCondition']?$coupon_row['UseCondition']:0;?>" type="text" class="form_input" maxlength="10" size="5" /></span>
					<span class="tool_tips_ico" content="{/sales.coupon.condition_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.use/}{/sales.coupon.times/}</label>
				<span class="input">
					<input name="UseNum" value="<?=(int)$coupon_row['UseNum']<0?0:(int)$coupon_row['UseNum'];?>" type="text" class="form_input" maxlength="10" size="5" />
					<span class="tool_tips_ico" content="{/sales.coupon.use_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/sales.coupon.status/}</label>
				<span class="input">
					<input type="radio" name="Status" value="0" checked /> {/global.all/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="Status" value="1" /> {/sales.coupon.normal/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="Status" value="2" /> {/sales.coupon.use_end/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="Status" value="3" /> {/sales.coupon.not_start/}&nbsp;&nbsp;&nbsp;
					<input type="radio" name="Status" value="4" /> {/sales.coupon.expire/}
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.explode/}" />
					<a href="./?m=sales&a=coupon" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<div id="explode_progress"></div>
			<input type="hidden" name="do_action" value="sales.coupon_explode" />
		</form>
	<?php }?>
</div>