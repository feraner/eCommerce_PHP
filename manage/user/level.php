<?php !isset($c) && exit();?>
<?php
manage::check_permit('user', 1, array('a'=>'level'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('user', 0, array('a'=>'level', 'd'=>'add')),
	'edit'	=>	manage::check_permit('user', 0, array('a'=>'level', 'd'=>'edit')),
	'del'	=>	manage::check_permit('user', 0, array('a'=>'level', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.user.level/}</h1>
	<?php if($c['manage']['do']=='index'){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=user&a=level&d=edit" label="{/global.add/}"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="level" class="r_con_wrap">
	<?php if($c['manage']['do']=='index'){?>
		<script type="text/javascript">$(document).ready(function(){user_obj.level_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="50%" nowrap="nowrap">{/global.name/}</td>
					<td width="23%" nowrap="nowrap">{/global.pic/}</td>
					<td width="22%" nowrap="nowrap">{/global.used/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$level_row=str::str_code(db::get_all('user_level', 1, '*', 'FullPrice desc'));
				foreach($level_row as $v){
				?>
					<tr>
						<td nowrap="nowrap"><?=$v['Name'.$c['manage']['web_lang']];?></td>
						<td nowrap="nowrap"><img src="<?=$v['PicPath'];?>" /></td>
						<td nowrap="nowrap">{/global.n_y_ary.<?=$v['IsUsed'];?>/}</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=user&a=level&d=edit&LId=<?=$v['LId'];?>" label="{/global.view/}"><img src="/static/ico/edit.png" alt="{/global.view/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=user.user_level_del&LId=<?=$v['LId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
	<?php
	}else{
		$LId=(int)$_GET['LId'];
		$LId && $level=str::str_code(db::get_one('user_level', "LId={$LId}"));
	?>
		<script type="text/javascript">$(document).ready(function(){user_obj.level_edit_init();});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/user.level.name/}</label>
				<span class="input"><?=manage::form_edit($level, 'text', 'Name', 30, 50, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.level.icon/}</label>
				<span class="input upload_file upload_pic">
					<div class="img">
						<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '16*16');?>" /></div>
						<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '16*16');?>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.used/}</label>
				<span class="input">
					<div class="switchery<?=(!$LId || $level['IsUsed'])?' checked':'';?>">
						<input type="checkbox" name="IsUsed" value="1"<?=(!$LId || $level['IsUsed'])?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/user.level.used_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.level.discount/}</label>
				<span class="input">
					<input name="Discount" value="<?=$level['Discount'];?>" type="text" class="form_input" size="3" maxlength="3" rel="amount" notnull /> %<span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/user.level.condition/}</label>
				<span class="input">
					{/user.level.full_price/}: <?=$c['manage']['currency_symbol']?>
					<input name="FullPrice" value="<?=$level['FullPrice'];?>" type="text" class="form_input" size="10" maxlength="10" rel="amount" notnull />
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=user&a=level" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="LId" value="<?=$LId;?>" />
			<input type="hidden" name="PicPath" value="<?=$level['PicPath'];?>" save="<?=is_file($c['root_path'].$level['PicPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="user.level_edit" />
		</form>
	<?php }?>
</div>