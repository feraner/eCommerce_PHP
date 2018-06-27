<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'exchange'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('set', 0, array('a'=>'exchange', 'd'=>'add')),
	'edit'	=>	manage::check_permit('set', 0, array('a'=>'exchange', 'd'=>'edit')),
	'del'	=>	manage::check_permit('set', 0, array('a'=>'exchange', 'd'=>'del'))
);

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');
?>
<div class="r_nav">
	<h1>{/module.set.exchange/}</h1>
	<?php if($c['manage']['do']=='index' && $c['FunVersion']>=1){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}" data-id="0"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="exchange" class="r_con_wrap">
	<script type="text/javascript">$(document).ready(function(){set_obj.exchange_init()});</script>
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="6%" nowrap="nowrap">{/global.serial/}</td>
				<td width="8%" nowrap="nowrap">{/global.logo/}</td>
				<td width="8%" nowrap="nowrap">{/set.exchange.name/}</td>
				<td width="8%" nowrap="nowrap">{/set.exchange.symbol/}</td>
				<td width="10%" nowrap="nowrap">{/global.used/}</td>
				<td width="10%" nowrap="nowrap">{/set.exchange.default/}</td>
				<td width="15%" nowrap="nowrap">{/set.exchange.exchange_rate/}($)<span class="tool_tips_ico" content="{/set.exchange.default_rate_notes/}"></span></td>
				<td width="12%" nowrap="nowrap">{/set.exchange.manage_default/}</td>
				<td width="12%" nowrap="nowrap">{/set.exchange.now_rate/}<span class="tool_tips_ico" content="{/set.exchange.now_rate_notes/}"></span></td>
				<?php if(($c['FunVersion']>=1 && $permit_ary['edit']) || $permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
			$where='1';
			if($c['FunVersion']<1){
				$where.=' and Currency="USD"';
				db::update('currency', 'Currency!="USD"', array('IsUsed'=>0, 'IsDefault'=>0));
				db::update('currency', 'Currency="USD"', array('IsUsed'=>1, 'IsDefault'=>1));
			}
			$currency_row=str::str_code(db::get_all('currency', $where, '*', $c['my_order'].'CId asc'));
			foreach($currency_row as $k=>$v){
				$Default=(int)$v['IsDefault'];
				$Used=(int)$v['IsUsed'];
				$ManageDefault=(int)$v['ManageDefault'];
				$v['IsFlagPath']=($v['FlagPath'] && is_file($c['root_path'].$v['FlagPath']))?1:0; //判断国旗图片是否存在
			?>
				<tr cid="<?=$v['CId'];?>" data="<?=htmlspecialchars(str::json_data($v));?>">
					<td nowrap="nowrap"><?=$k+1;?></td>
					<td nowrap="nowrap" class="img"><img src="<?=$v['FlagPath'];?>" /></td>
					<td nowrap="nowrap"><?=$v['Currency'];?></td>
					<td nowrap="nowrap"><?=$v['Symbol'];?></td>
					<td class="used_checkbox">
						<?php if($permit_ary['edit']){?>
							<div class="switchery<?=$Used?' checked':'';?><?=$Default?' no_drop':'';?>">
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						<?php
						}else{
							echo $Used?'{/global.n_y.1/}':'';
						}
						?>
					</td>
					<td class="default_checkbox">
						<?php if($permit_ary['edit']){?>
							<div class="switchery<?=$Default?' checked':'';?><?=$Default?' no_drop':'';?>">
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						<?php
						}else{
							echo $Default?'{/global.n_y.1/}':'';
						}
						?>
					</td>
					<td nowrap="nowrap"><?=$v['ExchangeRate'];?></td>
					<td class="manage_default_checkbox">
						<?php if($permit_ary['edit']){?>
							<div class="switchery<?=$ManageDefault?' checked':'';?><?=$ManageDefault?' no_drop':'';?>">
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						<?php
						}else{
							echo $ManageDefault?'{/global.n_y.1/}':'';
						}
						?>
					</td>
					<td nowrap="nowrap"><?=$ManageDefault?1:$v['Rate'];?></td>
					<?php if(($c['FunVersion']>=1 && $permit_ary['edit']) || $permit_ary['del']){?>
						<td nowrap="nowrap">
							<?php if($c['FunVersion']>=1 && $permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['CId'];?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" /></a><?php }?>
							<?php if($v['IsFixed']==0 && $permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.exchange_del&CId=<?=$v['CId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<?php /***************************** 汇率编辑 Start *****************************/?>
	<div class="pop_form box_exchange_edit">
		<form id="edit_form">
			<div class="t"><h1><span></span>{/set.exchange.rate/}</h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/set.exchange.name/}</label>
					<span class="input"><input type="text" name="Currency" value="" class="form_input" size="20" maxlength="10" notnull /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/set.exchange.symbol/}</label>
					<span class="input"><input type="text" name="Symbol" value="" class="form_input" size="10" maxlength="10" notnull /> {/set.exchange.symbol_tops/}</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/set.exchange.rate/}(<?=$c['manage']['currency_symbol'];?>)</label>
					<span class="input"><input type="text" name="ExchangeRate" value="" class="form_input" size="10" maxlength="10" notnull rel="amount" /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.pic/}</label>
					<span class="input upload_file upload_flag">
						<div class="img">
							<div id="FlagDetail" class="upload_box preview_pic"><input type="button" id="FlagUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.used/}</label>
					<span class="input">
						<div class="switchery">
							<input type="checkbox" name="IsUsed" value="1">
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/set.exchange.used_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="CId" value="0" />
				<input type="hidden" name="FlagPath" value="" save="0" />
				<input type="hidden" name="do_action" value="set.exchange_edit" />
			</div>
			<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
		</form>
	</div>
	<?php /***************************** 汇率编辑 End *****************************/?>
</div>