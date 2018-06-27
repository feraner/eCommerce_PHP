<?php !isset($c) && exit();?>
<?php
manage::check_permit('user', 1, array('a'=>'user'));//检查权限
//会员等级
$level_select_ary=$level_ary=array();
$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1'));
foreach((array)$level_row as $k=>$v){
	$level_ary[$v['LId']]=$v;
	$level_select_ary[]=$v;
}
//业务员
$sales_ary=array();
$manage_row=db::get_all('manage_sales');
foreach((array)$manage_row as $k=>$v){
	$sales_ary[$v['SalesId']]=$v;
}

$permit_ary=array(
	'add'		=>	manage::check_permit('user', 0, array('a'=>'user', 'd'=>'add')),
	'edit'		=>	manage::check_permit('user', 0, array('a'=>'user', 'd'=>'edit')),
	'del'		=>	manage::check_permit('user', 0, array('a'=>'user', 'd'=>'del')),
	'export'	=>	manage::check_permit('user', 0, array('a'=>'user', 'd'=>'export'))
);
?>
<div class="r_nav">
	<h1>{/module.user.user/}</h1>
	<div class="turn_page"></div>
	<?php
	if($c['manage']['do']=='index'){
		$column_row=db::get_value('config', "GroupId='custom_column' and Variable='User'", 'Value');
		$custom_ary=str::json_data($column_row, 'decode');
		$column_fixed_ary=array('user.name', 'user.email', 'user.level.level', 'user.reg_time', 'user.last_login_time', 'user.consumption_price');
		$column_ary=array('user.name', 'user.email', 'user.title', 'user.level.level', 'user.reg_time', 'user.reg_ip', 'user.last_login_time', 'user.last_login_ip', 'user.login_times', 'user.consumption_price');
		if($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)){//业务员
			$column_ary[]='user.sales';
		}
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
						<label>{/user.level.level/}</label>
						<span class="input"><?=ly200::form_select($level_select_ary, 'Level', '', 'Name'.$c['manage']['web_lang'], 'LId', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<?php
					if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && count($sales_ary) && (int)$_SESSION['Manage']['GroupId']!=3){//业务员
					?>
						<div class="rows">
							<label>{/manage.manage.permit_name.3/}</label>
							<span class="input"><?=ly200::form_select($sales_ary, 'SalesId', '', 'UserName', 'SalesId', '{/global.select_index/}');?></span>
							<div class="clear"></div>
						</div>
					<?php }?>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="user" />
				<input type="hidden" name="a" value="user" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=user&a=user&d=add" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['export']){?><li><a class="tip_ico_down explode" href="./?m=user&a=user&d=explode" label="{/global.explode/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
            <?php if($permit_ary['edit']){?><li><a class="tip_ico_down bat_close" href="javascript:;" label="{/products.products.batch_edit/}"></a></li><?php }?>
			<li class="extend">
				<a href="javascript:;" label="{/global.custom_column/}"></a>
				<form>
					<?php
					foreach((array)$column_ary as $v){
						$checked=(in_array($v, $column_fixed_ary) || in_array($v, $custom_ary))?' checked':'';
						$disabled=in_array($v, $column_fixed_ary)?' disabled':'';
					?>
						<input type="checkbox" name="Custom[]" class="custom_list" value="<?=$v;?>"<?=$checked.$disabled;?> /> {/<?=$v?>/}&nbsp;&nbsp;&nbsp;
					<?php }?>
					<div class="blank6"></div>
					<input type="submit" class="submit_btn" value="{/global.submit/}" />&nbsp;&nbsp;<input type="checkbox" name="custom_all" value="" class="va_m" /> {/global.select_all/}
					<input type="hidden" name="do_action" value="user.user_custom_column" />
				</form>
			</li>
		</ul>
	<?php }?>
    <?php if($c['manage']['do']=='batch_edit'){?>
		<dl class="edit_form_part">
			<dt></dt>
			<dd><a <?=$_GET['type']==0?'class="current"':''?> href="?<?=ly200::query_string('type')?>&type=0">{/user.level.level/}</a></dd>
            <?php if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && (int)$_SESSION['Manage']['GroupId']<3){?>
			<dt></dt>
			<dd><a <?=$_GET['type']==1?'class="current"':''?> href="?<?=ly200::query_string('type')?>&type=1">{/manage.manage.permit_name.3/}</a></dd>
            <?php }?>
		</dl>
    <?php }?>
</div>
<div id="user" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$no_sort_url='?'.ly200::get_query_string(ly200::query_string('page, Sort'));
		$Sort=$_GET['Sort'];
		$sort_ary=array(
			'1a'	=>	'Consumption asc,',
			'1d'	=>	'Consumption desc,'
		);
	?>
		<script type="text/javascript">$(document).ready(function(){user_obj.user_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="3%" nowrap="nowrap"><input type="checkbox" name="select_all" /></td><?php }?>
					<td width="20%" nowrap="nowrap">{/user.name/}</td>
					<td width="20%" nowrap="nowrap">{/user.email/}</td>
					<?php if(in_array('user.title', $custom_ary)){?><td width="5%" nowrap="nowrap">{/user.title/}</td><?php }?>
					<?php if(in_array('user.sales', $custom_ary)){?><td width="8%" nowrap="nowrap">{/manage.manage.permit_name.3/}</td><?php }?>
					<td width="5%" nowrap="nowrap">{/user.level.level/}</td>
					<td width="15%" nowrap="nowrap">{/user.reg_time/}</td>
					<?php if(in_array('user.reg_ip', $custom_ary)){?><td width="15%" nowrap="nowrap">{/user.reg_ip/}</td><?php }?>
					<td width="15%" nowrap="nowrap">{/user.last_login_time/}</td>
					<?php if(in_array('user.last_login_ip', $custom_ary)){?><td width="15%" nowrap="nowrap">{/user.last_login_ip/}</td><?php }?>
					<?php if(in_array('user.login_times', $custom_ary)){?><td width="10%" nowrap="nowrap">{/user.login_times/}</td><?php }?>
					<td width="10%" nowrap="nowrap">
						<a href="<?=$no_sort_url.'&Sort='.($Sort=='1a'?'1d':'1a');?>">{/user.consumption_price/}<i class="<?php if($Sort=='1d') echo 'sort_icon_arrow_down'; elseif($Sort=='1a') echo 'sort_icon_arrow_up'; else echo 'sort_icon_arrow';?>"></i></a>
					</td>
					<?php if($c['FunVersion']>=1 && $c['manage']['config']['UserStatus']){?><td width="10%" nowrap="nowrap">{/set.config.user_status/}</td><?php }?>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=str::str_code($_GET['Keyword']);
				$Level=(int)$_GET['Level'];
				$SalesId=(int)$_GET['SalesId'];
				$where='1';//条件
				$Keyword && $where.=" and (FirstName like '%$Keyword%' or LastName like '%$Keyword%' or concat(FirstName, LastName) like '%$Keyword%' or Email like '%$Keyword%' or Remark like '%$Keyword%')";
				$Level && $where.=" and Level='$Level'";
				$SalesId && $where.=" and SalesId='$SalesId'";
				(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and SalesId='{$_SESSION['Manage']['SalesId']}'";//业务员账号过滤
				$user_row=str::str_code(db::get_limit_page('user', $where, '*', $sort_ary[$Sort].'UserId desc', (int)$_GET['page'], 20));
				foreach($user_row[0] as $v){
					$level_img=$level_ary[$v['Level']]['PicPath'];
					$level_name=$level_ary[$v['Level']]['Name'.$c['manage']['web_lang']];
				?>
					<tr>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['UserId'];?>" /></td><?php }?>
						<td nowrap="nowrap"><a href="<?=$permit_ary['edit']?'./?m=user&a=user&d=base_info&UserId='.$v['UserId']:'javascript:;';?>"><?=$v['FirstName'].$v['LastName'];?></a></td>
						<td nowrap="nowrap"><?=$v['Email'];?></td>
						<?php if(in_array('user.title', $custom_ary)){?><td nowrap="nowrap"><?=$c['gender'][$v['Gender']];?></td><?php }?>
						<?php if(in_array('user.sales', $custom_ary)){?>
							<td nowrap="nowrap"<?=($permit_ary['edit'] && (int)$_SESSION['Manage']['GroupId']!=3)?' class="sales_select"':'';?> data-id="<?=$v['SalesId'];?>"><?=$v['SalesId']?$sales_ary[$v['SalesId']]['UserName']:'N/A';?></td>
						<?php }?>
						<td nowrap="nowrap" style="text-align:center;"><?=$v['Level']?"<img src='{$level_img}' alt='{$level_name}' title='{$level_name}' />":'';?></td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['RegTime']);?></td>
						<?php if(in_array('user.reg_ip', $custom_ary)){?><td nowrap="nowrap"><?=$v['RegIp'].'【'.ly200::ip($v['RegIp']).'】';?></td><?php }?>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['LastLoginTime']);?></td>
						<?php if(in_array('user.last_login_ip', $custom_ary)){?><td nowrap="nowrap"><?=$v['LastLoginIp'].'【'.ly200::ip($v['LastLoginIp']).'】';?></td><?php }?>
						<?php if(in_array('user.login_times', $custom_ary)){?><td nowrap="nowrap"><?=$v['LoginTimes'];?></td><?php }?>
						<td nowrap="nowrap"><?=$c['manage']['currency_symbol'].$v['Consumption'];?></td>
						<?php if($c['FunVersion']>=1 && $c['manage']['config']['UserStatus']){?><td nowrap="nowrap"><?=$v['Status']?'{/global.n_y.1/}':'{/global.n_y.0/}';?></td><?php }?>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=user&a=user&d=base_info&UserId=<?=$v['UserId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=user.user_del&UserId=<?=$v['UserId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($user_row[1], $user_row[2], $user_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		<div id="sales_select_hide" class="hide"><?=ly200::form_select($sales_ary, 'SalesId', '', 'UserName', 'SalesId', '{/global.select_index/}');?></div>
	<?php
	}elseif($c['manage']['do']=='add'){//添加会员
		$reg_ary=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
		$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
	?>
		<script type="text/javascript">$(document).ready(function(){user_obj.user_add_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/user.first_name/}<?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
				<span class="input"><input name="FirstName" id="FirstName" class="form_input" type="text" size="30" maxlength="20"<?=$reg_ary['Name'][1]?' notnull':'';?> /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.last_name/}<?=$reg_ary['Name'][1]?' <span class="fc_red">*</span>':'';?></label>
				<span class="input"><input name="LastName" id="LastName" class="form_input" type="text" size="30" maxlength="20"<?=$reg_ary['Name'][1]?' notnull':'';?> /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.email/} <span class="fc_red">*</span></label>
				<span class="input"><input name="Email" id="Email" class="form_input" type="text" size="40" maxlength="100" format="Email" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.password/} <span class="fc_red">*</span></label>
				<span class="input"><input name="Password" id="Password" class="form_input" type="password" size="40" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.info.confirm_password/} <span class="fc_red">*</span></label>
				<span class="input"><input name="Password2" id="Password2" class="form_input" type="password" size="40" notnull /></span>
				<div class="clear"></div>
			</div>
			<?php
			if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && (int)$_SESSION['Manage']['GroupId']<3){
			?>
				<div class="rows">
					<label>{/manage.manage.permit_name.3/}</label>
					<span class="input">
						<select name="SalesId">
							<option value="">{/global.select_index/}</option>
							<?php
							foreach((array)$manage_row as $k=>$v){
							?>
								<option value="<?=$v['SalesId'];?>"<?=$user['SalesId']==$v['SalesId']?' selected':'';?>><?=$v['UserName'];?></option>
							<?php }?>
						</select>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<?php
			foreach((array)$reg_ary as $k=>$v){
				if($k=='Name' || $k=='Email' || $k=='Code' || $k=='Country' || !$v[0]) continue;
			?>
				<div class="rows">
					<label>{/user.reg_set.<?=$k;?>/}<?=$v[1]?' <span class="fc_red">*</span>':'';?></label>
					<span class="input"><?=user::user_reg_edit($k, $v[1], 'form_input');?></span>
					<div class="clear"></div>
				</div>
			<?php
			}
			foreach((array)$set_row as $k=>$v){
			?>
				<div class="rows">
					<label><?=$v['Name'.$c['manage']['web_lang']];?></label>
					<span class="input">
						<?php
						if($v['TypeId']){
							echo ly200::form_select(explode("\r\n", $v['Option'.$c['manage']['web_lang']]), "Other[{$v['SetId']}]", '', '', '', 'Please select...');
						}else{
							echo user::form_edit('', 'text', "Other[{$v['SetId']}]", 30, 50, 'class="form_input"');
						}
						?>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=user&a=user" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="user.user_add" />
		</form>
	<?php
	}elseif($c['manage']['do']=='explode'){
		$where='1';//条件
		$page_count=100;//显示数量
		(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and SalesId='{$_SESSION['Manage']['SalesId']}'";//业务员账号过滤
		$user_row=str::str_code(db::get_limit_page('user', $where, '*', 'UserId desc', (int)$_GET['page'], $page_count));
	?>
		<script type="text/javascript">$(document).ready(function(){user_obj.user_explode_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/user.level.level/}</label>
				<span class="input user_choice">
					<div class="user_menu">
						<?php
						foreach((array)$level_row as $k=>$v){
						?>
							<span class="choice_btn"><b><?=$v['Name'.$c['manage']['web_lang']];?></b><input type="checkbox" name="Level[]" class="hide level_check"  value="<?=$v['LId'];?>" /></span>
						<?php }?>
					</div>
					<div id="list_box">
						<ul class="user_list">
							<?php
							foreach((array)$user_row[0] as $v){
							?>
								<li status="<?=(int)$v['Level'];?>"><input type="checkbox" name="UserId[]" class="fl" value="<?=$v['UserId'];?>" /><a href="javascript:void(0);"><span data="./?m=user&a=user&d=base_info&UserId=<?=$v['UserId'];?>"><?=$v['Email'];?></span>, &nbsp;&nbsp;&nbsp;<?=$v['FirstName'].' '.$v['LastName'].', &nbsp;&nbsp;&nbsp;'.$level_ary[$v['Level']]['Name'.$c['manage']['web_lang']];?></a></li>
							<?php }?>
						</ul>
						<div class="blank9"></div>
						<span class="choice_btn all_btn"><b>{/global.select_all/}</b><input type="checkbox" name="AllUser" id="all_user" class="hide level_check" value="1" /></span>
						<div class="blank9"></div>
						<div class="turn_page"><?=ly200::turn_page($user_row[1], $user_row[2], $user_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?> <input type="button" class="clear_check" value="{/user.clear_check/}" /></div>
					</div>
					<div class="blank12"></div>
					<span class="choice_btn explode_all_btn"><b>{/user.explode_all/}</b><input type="checkbox" name="explodeAll" class="hide" value="1" /></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.explode/}" />
					<a href="./?m=user&a=user" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<div id="explode_progress"></div>
			<input type="hidden" name="do_action" value="user.user_explode" />
			<input type="hidden" name="UserIdStr" value="|" />
			<input type="hidden" name="Number" value="0" />
		</form>
	<?php
		}elseif($c['manage']['do']=='batch_edit'){	//批量修改
	?>
        <script language="javascript">$(document).ready(function(){user_obj.batch_edit_init();});</script>
        <form id="edit_form" class="r_con_form">
        	<?php if($_GET['type']){	//业务员?>
                <div class="rows">
                    <label>{/manage.manage.permit_name.3/}</label>
                    <span class="input">
                        <select name="SalesId">
                            <option value="">{/global.select_index/}</option>
                            <?php
                            foreach((array)$manage_row as $k=>$v){
                            ?>
                                <option value="<?=$v['SalesId'];?>"><?=$v['UserName'];?></option>
                            <?php }?>
                        </select>
                    </span>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="type" value="1" />
                <?php /*?><?php }?><?php */?>
        	<?php }else{?>
                <div class="rows">
                    <label>{/user.level.level/}</label>
                    <span class="input">
                        <?=ly200::form_select($level_select_ary, 'Level', '', 'Name'.$c['manage']['web_lang'], 'LId', '{/global.select_index/}');?>
                    </span>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="type" value="0" />
            <?php }?>
            <div class="rows">
                <label></label>
                <span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
                <div class="clear"></div>
            </div>
            <?php
				$userid_list=explode('-',trim($_GET['userid_list']));
				foreach((array)$userid_list as $v){
			?>
            <input type="hidden" name="UserId[]" value="<?=$v;?>" />
            <?php }?>
            <input type="hidden" name="do_action" value="user.user_batch_edit" />
        </form>
	<?php
	}else{
		$UserId=(int)$_GET['UserId'];
		$user=str::str_code(db::get_one('user', "UserId={$UserId}"));
		$g_Page=(int)$_GET['page'];
		$g_Page<1 && $g_Page=1;
		if($c['manage']['do']=='base_info'){
			$RegSet=db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value');
			$set_ary=array();
			$set_row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]}SetId asc"));
			foreach((array)$set_row as $v){
				$set_ary[$v['SetId']]=$v;
				if($v['TypeId']){
					$set_ary[$v['SetId']]['Option']=explode("\r\n", $v['Option'.$c['manage']['web_lang']]);
				}
			}
		}elseif($c['manage']['do']=='order_info'){
			$row_count=20;
			$row=str::str_code(db::get_limit_page('orders', "UserId={$UserId}", '*', 'OrderId desc', $g_Page, $row_count));
		}elseif($c['manage']['do']=='address_info'){
			$ship_row=str::str_code(db::get_all('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.UserId={$UserId} and a.IsBillingAddress=0", 'a.*, c.Country, c.Code, s.States as StateName', 'a.AccTime desc, a.AId desc'));
			$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.UserId={$UserId} and a.IsBillingAddress=1", 'a.*, c.Country, c.Code, s.States as StateName'));
		}elseif($c['manage']['do']=='favorite_info'){
			$row_count=10;
			$row=str::str_code(db::get_limit_page('user_favorite u left join products p on u.ProId=p.ProId', "u.UserId={$UserId}", 'p.*, u.AccTime as AddTime', 'u.FId desc', $g_Page, $row_count));
			//获取类别列表
			$cate_ary=str::str_code(db::get_all('products_category','1','*'));
			$category_ary=array();
			foreach((array)$cate_ary as $v){
				$category_ary[$v['CateId']]=$v;
			}
			$category_count=count($category_ary);
			unset($cate_ary);
		}elseif($c['manage']['do']=='cart_info'){
			$row_count=10;
			$row=str::str_code(db::get_limit_page('shopping_cart c left join products p on c.ProId=p.ProId', "c.UserId={$UserId}", 'p.*, c.PicPath as CartPicPath, c.Price as CartPrice, c.Qty as CartQty, c.Property as CartProperty, c.PropertyPrice as CartPropertyPrice, c.Remark as CartRemark, c.AddTime as CartAddTime', 'c.CId desc', $g_Page, $row_count));
		}elseif($c['manage']['do']=='message_info'){
			$row_count=10;
			$row=str::str_code(db::get_limit_page('user_message', "((UserId like '%|{$UserId}|%' or UserId='-1') and Type=1) or (UserId='$UserId' and Type=0)", '*', 'MId desc', $g_Page, $row_count));
		}elseif($c['manage']['do']=='log_info'){
			$row_count=10;
			$row=str::str_code(db::get_limit_page('user_operation_log', "UserId='{$UserId}'", '*', 'LId desc', $g_Page, $row_count));
		}
	?>
		<ul class="module_title">
			<?php
			$d_ary=array('list', 'edit', 'base_info', 'order_info', 'address_info', 'favorite_info', 'cart_info', 'message_info', 'log_info', 'password_info', 'explode');
			if(!manage::check_permit('user', 0, array('a'=>'user', 'd'=>'edit'))) unset($d_ary[9]);
			for($i=1,$l=count($d_ary); $i<$l; ++$i){
			?>
				<li <?=$c['manage']['do']==$d_ary[$i]?'class="cur"':'';?>><a href="./?m=user&a=user&d=<?=$d_ary[$i];?>&UserId=<?=$UserId;?>">{/user.info.<?=$d_ary[$i];?>/}</a></li>
			<?php }?>
		</ul>
		<?php
		/******************** 基本信息 ********************/
		if($c['manage']['do']=='base_info'){
		?>
			<script language="javascript">$(document).ready(function(){user_obj.user_base_edit_init();});</script>
			<form id="edit_form" class="r_con_form">
				<div class="rows">
					<label>{/user.title/}</label>
					<span class="input"><?=$c['gender'][$user['Gender']];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/user.name/}</label>
					<span class="input"><?=$user['FirstName'].' '.$user['LastName'];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/user.email/}</label>
					<span class="input"><?=$user['Email'];?>&nbsp;&nbsp;&nbsp;<a href="./?m=email&d=send&Email=<?=urlencode($user['Email'].'/'.$user['FirstName'].' '.$user['LastName']);?>" title="{/module.email.send/}"><img src="/static/manage/images/frame/email.png" /></a></span>
					<div class="clear"></div>
				</div>
				<?php
				if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && (int)$_SESSION['Manage']['GroupId']<3){
				?>
					<div class="rows">
						<label>{/manage.manage.permit_name.3/}</label>
						<span class="input">
							<select name="SalesId">
								<option value="">{/global.select_index/}</option>
								<?php
								foreach((array)$manage_row as $k=>$v){
								?>
									<option value="<?=$v['SalesId'];?>"<?=$user['SalesId']==$v['SalesId']?' selected':'';?>><?=$v['UserName'];?></option>
								<?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				<?php }?>
				<div class="rows">
					<label>{/user.level.level/}</label>
					<span class="input">
						<?=ly200::form_select($level_select_ary, 'Level', $user['Level'], 'Name'.$c['manage']['web_lang'], 'LId', '{/global.select_index/}');?>
						&nbsp;&nbsp;{/user.info.is_fixed/}:
						<div class="switchery<?=$user['IsLocked']?' checked':'';?>">
							<input type="checkbox" name="IsLocked" value="1"<?=$user['IsLocked']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/user.info.is_fixed_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<?php if($c['FunVersion']>=1 && $c['manage']['config']['UserStatus']){?>
                    <div class="rows">
                        <label>{/set.config.user_status/}</label>
                        <span class="input">
                            <div class="switchery<?=$user['Status']?' checked':'';?>">
                                <input type="checkbox" name="Status" value="1"<?=$user['Status']?' checked':'';?>>
                                <div class="switchery_toggler"></div>
                                <div class="switchery_inner">
                                    <div class="switchery_state_on"></div>
                                    <div class="switchery_state_off"></div>
                                </div>
                            </div>
                        </span>
                        <div class="clear"></div>
                    </div>
				<?php }?>
				<?php
				$reg_ary=str::json_data($RegSet, 'decode');
				foreach((array)$reg_ary as $k=>$v){
					if($k=='Name' || $k=='Email' || !$v[0] || $k=='Country' || $k=='Code') continue;
				?>
					<div class="rows">
                        <label>{/user.reg_set.<?=$k;?>/}</label>
                        <span class="input"><?=user::user_reg_edit($k, $v[1], 'form_input', $user);?></span>
                        <div class="clear"></div>
                    </div>
				<?php }?>
				<?php if($user['Other']){?>
                    <div class="rows">
                        <label>{/user.other/}</label>
                        <span class="input">
                            <?php
                            $other_ary=str::json_data(htmlspecialchars_decode($user['Other']), 'decode');
                            foreach((array)$other_ary as $k=>$v){
                                if($set_ary[$k]['TypeId']){
                                    $v=$set_ary[$k]['Option'][$v];
                                }
                                echo "【".$set_ary[$k]['Name'.$c['manage']['web_lang']]."】 {$v}<div class='blank6'></div>";
                            }
                            ?>
                        </span>
                        <div class="clear"></div>
                    </div>
				<?php }?>
				<div class="rows">
					<label>{/user.consumption_price/}</label>
					<span class="input"><?=$c['manage']['currency_symbol'].$user['Consumption'];?></span>
					<div class="clear"></div>
				</div>
                <div class="rows">
					<label>{/user.remark/}</label>
					<span class="input"><textarea name='Remark'><?=$user['Remark'];?></textarea></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/user.reg_time/}</label>
					<span class="input"><?=date('Y-m-d H:i:s', $user['RegTime']).'<br />'.$user['RegIp'].'【'.ly200::ip($user['RegIp']).'】';?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/user.last_login_time/}</label>
					<span class="input"><?=date('Y-m-d H:i:s', $user['LastLoginTime']).'<br />'.$user['LastLoginIp'].'【'.ly200::ip($user['LastLoginIp']).'】';?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=user&a=user" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="UserId" value="<?=$UserId;?>" />
				<input type="hidden" name="do_action" value="user.user_base_info_edit" />
			</form>
		<?php
		/******************** 订单信息 ********************/
		}elseif($c['manage']['do']=='order_info'){
		?>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="15%" nowrap="nowrap">{/orders.oid/}</td>
						<td width="10%" nowrap="nowrap">{/orders.info.product_price/}</td>
						<td width="10%" nowrap="nowrap">{/orders.info.charges_insurance/}</td>
						<td width="10%" nowrap="nowrap">{/orders.total_price/}</td>
						<td width="14%" nowrap="nowrap">{/orders.orders_status/}</td>
						<td width="10%" nowrap="nowrap">{/global.time/}</td>
						<td width="6%" nowrap="nowrap" class="last">{/global.operation/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$i=1;
					foreach($row[0] as $v){
						$isFee=($v['OrderStatus']>=4 && $v['OrderStatus']!=7)?1:0;
						$total_price=orders::orders_price($v, $isFee, 1);
					?>
					<tr>
						<td><?=$v['OId'];?></td>
						<td><?=$c['manage']['currency_symbol'].sprintf('%01.2f', $v['ProductPrice']);?></td>
						<td><?=$c['manage']['currency_symbol'].sprintf('%01.2f', $v['ShippingPrice']+$v['ShippingInsurancePrice']);?></td>
						<td><?=$c['manage']['currency_symbol'].sprintf('%01.2f', $total_price);?></td>
						<td><?=$c['orders']['status'][$v['OrderStatus']];?></td>
						<td><?=$v['OrderTime']?date('Y-m-d', $v['OrderTime']):'N/A';?></td>
						<td class="last"><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>" title="{/global.view/}"><img src="/static/ico/search.png" align="absmiddle" /></a></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($row[1], $row[2], $row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		/******************** 地址薄 ********************/
		}elseif($c['manage']['do']=='address_info'){
		?>
			<div id="lib_user_address" class="index_pro_list clearfix">
				<div class="billing_addr">
					<h3 class="big">Your Billing Address</h3>
					<div class="clear"></div>
					<ul class="addr_item">
						<li><strong><?=$bill_row['FirstName'].' '.$bill_row['LastName'];?></strong></li>
						<li><?=$bill_row['AddressLine1'];?></li>
						<li><?=$bill_row['City'];?>, <?=($bill_row['StateName']?$bill_row['StateName']:$bill_row['State']);?>, <?=$bill_row['ZipCode'];?></li>
						<li><?=$bill_row['Country'];?></li>
						<li><span class="phone_icon">Phone:+<?=$bill_row['Code'].' '.$bill_row['PhoneNumber'];?></span></li>
					</ul>
				</div>
				<div class="shipping_addr">
					<h3 class="big">Your Shipping Address</h3>
					<div class="clearfix addr_list">
						<?php foreach($ship_row as $v){?>
							<ul class="addr_item fl">
								<li><strong><?=$v['FirstName'].' '.$v['LastName'];?></strong></li>
								<li><?=$v['AddressLine1'];?></li>
								<li><?=$v['City'];?>, <?=($v['StateName']?$v['StateName']:$v['State']);?>, <?=$v['ZipCode'];?></li>
								<li><?=$v['Country'];?></li>
								<li><span class="phone_icon">Phone:+<?=$v['Code'].' '.$v['PhoneNumber'];?></span></li>
							</ul>
						<?php }?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		<?php
		/******************** 收藏夹 ********************/
		}elseif($c['manage']['do']=='favorite_info'){
		?>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="12%" nowrap="nowrap">&nbsp;&nbsp;{/products.picture/}</td>
						<td width="22%" nowrap="nowrap">{/products.name/}</td>
						<td width="13%" nowrap="nowrap">{/products.products.number/}</td>
						<td width="17%" nowrap="nowrap">{/products.classify/}</td>
						<td width="15%" nowrap="nowrap" class="last">{/user.info.add_time/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$i=1;
					foreach($row[0] as $v){
					?>
					<tr>
						<td class="img pic_box"><img class="photo" src="<?=ly200::get_size_img($v['PicPath_0'], '168x168');?>" align="absmiddle" /><span></span></td>
						<td><?=$v['Name'.$c['manage']['web_lang']];?></td>
						<td><?=$v['Number'];?></td>
						<td>
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
						<td class="last"><?=$v['AddTime']?date('Y-m-d', $v['AddTime']):'N/A';?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($row[1], $row[2], $row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		/******************** 购物车 ********************/
		}elseif($c['manage']['do']=='cart_info'){
		?>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="12%" nowrap="nowrap">&nbsp;&nbsp;{/products.picture/}</td>
						<td width="25%" nowrap="nowrap">{/products.name/}</td>
						<td width="22%" nowrap="nowrap">{/products.attribute/}</td>
						<td width="8%" nowrap="nowrap">{/user.info.unit_pirce/}</td>
						<td width="8%" nowrap="nowrap">{/products.products.qty/}</td>
						<td width="10%" nowrap="nowrap">{/user.info.subtotal/}</td>
						<td width="15%" nowrap="nowrap" class="last">{/user.info.add_time/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$cart_attr=$cart_attr_data=array();
					$i=1;
					foreach($row[0] as $v){
						$qty=$v['CartQty'];
						$img=ly200::get_size_img($v['CartPicPath'], '240x240');
						$price=$v['CartPrice']+$v['CartPropertyPrice'];
						$total_price=$qty*$price;
						$attr = array();
						$v['CartProperty']!='' && $attr=str::json_data(htmlspecialchars_decode($v['CartProperty']), 'decode');
					?>
					<tr>
						<td class="img pic_box"><img class="photo" src="<?=$img;?>" align="absmiddle" /><span></span></td>
						<td style="text-align:left;">
							<div style="margin-bottom:5px;"><?=$v['Name'.$c['manage']['web_lang']];?></div>
							<?=$v['Number']!=''?'<div style="margin-bottom:5px;">{/products.products.number/}: '.$v['Prefix'].$v['Number'].'</div>':'';?>
							<?=$v['SKU']!=''?'<div style="margin-bottom:5px;">{/products.products.sku/}: '.$v['SKU'].'</div>':'';?>
						</td>
						<td><?php foreach((array)$attr as $k=>$z){?>
							<p><?=$k.': '.$z;?></p>
							<?php }?>
						</td>
						<td><?=$c['manage']['currency_symbol'].$price;?></td>
						<td><?=$qty;?></td>
						<td><?=$c['manage']['currency_symbol'].$total_price;?></td>
						<td class="last"><?=$v['CartAddTime']?date('Y-m-d', $v['CartAddTime']):'N/A';?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($row[1], $row[2], $row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		/******************** 站内信 ********************/
		}elseif($c['manage']['do']=='message_info'){
		?>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="50%" nowrap="nowrap">&nbsp;&nbsp;{/user.info.subject/}</td>
						<td width="20%" nowrap="nowrap" class="last">{/inbox.inbox.type/}</td>
						<td width="20%" nowrap="nowrap" class="last">{/user.info.add_time/}</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($row[0] as $v){ ?>
					<tr>
						<td><?=$v['Subject'];?></td>
						<td>{/inbox.inbox.type_ary.<?=$v['Type']?0:1;?>/}</td>
						<td class="last"><?=$v['AccTime']?date('Y-m-d', $v['AccTime']):'N/A';?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($row[1], $row[2], $row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		/******************** 操作记录 ********************/
		}elseif($c['manage']['do']=='log_info'){
		?>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="20%" nowrap="nowrap">{/user.log.title/}</td>
						<td width="20%" nowrap="nowrap">{/user.log.time/}</td>
						<td width="30%" nowrap="nowrap">{/user.log.content/}</td>
						<td width="30%" nowrap="nowrap" class="last">{/user.log.ip/}</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($row[0] as $v){?>
					<tr>
						<td><?=$v['Log'];?></td>
						<td><?=$v['AccTime']?date('Y-m-d H:i:s', $v['AccTime']):'N/A';?></td>
						<td class="left"><pre class="opt_log"><?=$v['Data'];?></pre></td>
						<td class="last"><?=$v['Ip'].'<br />【'.ly200::ip($v['Ip']).'】';?></td>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($row[1], $row[2], $row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		/******************** 修改密码 ********************/
		}elseif($c['manage']['do']=='password_info'){
		?>
			<script language="javascript">$(document).ready(function(){user_obj.user_password_edit_init();});</script>
			<form id="edit_form" class="r_con_form">
				<div class="rows">
					<label>{/user.info.new_password/}</label>
					<span class="input"><input type="password" name="NewPassword" class="form_input" size="25" maxlength="16" notnull /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/user.info.confirm_password/}</label>
					<span class="input"><input type="password" name="ReNewPassword" class="form_input" size="25" maxlength="16" notnull /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="UserId" value="<?=$UserId;?>" />
				<input type="hidden" name="do_action" value="user.user_password_edit" />
			</form>
        <?php }?>
    <?php }?> 
</div>