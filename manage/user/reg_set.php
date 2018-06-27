<?php !isset($c) && exit();?>
<?php
manage::check_permit('user', 1, array('a'=>'reg_set'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('user', 0, array('a'=>'reg_set', 'd'=>'add')),
	'edit'	=>	manage::check_permit('user', 0, array('a'=>'reg_set', 'd'=>'edit')),
	'del'	=>	manage::check_permit('user', 0, array('a'=>'reg_set', 'd'=>'del'))
);
?>
<div id="reg_set" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$row=str::str_code(db::get_all('user_reg_set', '1', '*', "{$c[my_order]} SetId asc"));
	?>
		<script language="javascript">$(function(){user_obj.reg_set_init()});</script>
		<div class="fixed fl">
			<div class="rows_hd">{/user.reg_set.default_set/}</div>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="55%" nowrap="nowrap">{/user.reg_set.name/}</td>
						<td width="45%" nowrap="nowrap">{/global.used/} / {/global.required/}</td>
					</tr>
				</thead>
				<tbody>
					<?php
					$RegSet=db::get_value('config', 'GroupId="user" and Variable="RegSet"', 'Value');
					$reg_ary=str::json_data($RegSet, 'decode');
					foreach($c['manage']['user_reg_field'] as $k=>$v){
						$status=$reg_ary[$k];
					?>
					<tr>
						<td nowrap="nowrap">{/user.reg_set.<?=$k;?>/}<?=!$v?'{/user.reg_set.fixed/}':'';?></td>
						<td nowrap="nowrap">
							<?php if($permit_ary['edit']){?>
								<div class="switchery<?=(($status[0] && $v) || !$v)?' checked':'';?><?=!$v?' no_drop':'';?>"<?=$v?" field='{$k}' status='{$status[0]}'":'';?>>
									<div class="switchery_toggler"></div>
									<div class="switchery_inner">
										<div class="switchery_state_on"></div>
										<div class="switchery_state_off"></div>
									</div>
								</div>&nbsp;&nbsp;
								<?php if($k!='Code'){?>
									<div class="switchery<?=(($status[1] && $v) || !$v)?' checked':'';?><?=(($v && !$status[0]) || !$v)?' no_drop':'';?>"<?=$v?" field='{$k}NotNull' status='{$status[1]}'":'';?>>
										<div class="switchery_toggler"></div>
										<div class="switchery_inner">
											<div class="switchery_state_on"></div>
											<div class="switchery_state_off"></div>
										</div>
									</div>
								<?php }?>
							<?php
							}else{
								echo ((($status[0] && $v) || !$v)?'{/global.n_y.1/}':'{/global.n_y.0/}').' / '.((($status[1] && $v) || !$v)?'{/global.n_y.1/}':'{/global.n_y.0/}');
							}
							?>
						</td>
					</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
		<div class="custom fl">
			<div class="rows_hd">{/user.reg_set.custom_events/}</div>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="25%" nowrap="nowrap">{/user.reg_set.name/}</td>
						<td width="15%" nowrap="nowrap">{/user.reg_set.type/}</td>
						<td width="25%" nowrap="nowrap">{/user.reg_set.option/}</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="15%" nowrap="nowrap">{/global.operation/}</td><?php }?>
					</tr>
				</thead>
				<tbody>
					<?php foreach($row as $v){?>
					<tr>
						<td><?=$v['Name'.$c['manage']['web_lang']];?></td>
						<td><?=$c['manage']['lang_pack']['user']['reg_set']['type_list'][$v['TypeId']];?></td>
						<td class="line_h_20"><?=str::format($v['Option'.$c['manage']['web_lang']]);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td>
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=user&a=reg_set&d=edit&SetId=<?=$v['SetId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=user.reg_set_del&SetId=<?=$v['SetId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
					<?php }?>
				</tbody>
			</table>
			<?php if($permit_ary['add']){?>
				<div class="blank15"></div>
				<div class="control_btn">
					<a href="./?m=user&a=reg_set&d=edit" class="btn_ok">{/global.add/}</a>
				</div>
			<?php }?>
		</div>
    <?php
    }elseif($d='edit'){
		$SetId=(int)$_GET['SetId'];
		$row=str::str_code(db::get_one('user_reg_set', "SetId={$SetId}"));
		$type_id=(int)$row['TypeId'];
	?>
		<script language="javascript">$(function(){user_obj.reg_set_edit_init(<?=$type_id;?>)});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows_hd"><?=$SetId?'{/global.edit/}':'{/global.add/}';?>{/user.reg_set.custom_events/}</div>
			<div class="rows">
				<label>{/user.reg_set.type/}</label>
				<span class="input"><?=str_replace('<select', '<select id="type_select"', ly200::form_select($c['manage']['lang_pack']['user']['reg_set']['type_list'], 'TypeId', $type_id));?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.reg_set.name/}</label>
				<span class="input"><?=manage::form_edit($row, 'text', 'Name', 30, 50, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows row_option" style="display:<?=$type_id==1?'':'none';?>;">
				<label>{/user.reg_set.option/}</label>
				<span class="input">
					<span class="fc_red">{/user.reg_set.option_tip/}</span>
					<div class="blank15"></div>
					<?=manage::form_edit($row, 'textarea', 'Option');?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=user&a=reg_set" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="SetId" value="<?=$SetId;?>" />
			<input type="hidden" name="do_action" value="user.reg_set_edit" />
		</form>
    <?php }?>
</div>