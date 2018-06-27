<?php !isset($c) && exit();?>
<?php
manage::check_permit('content', 1, array('a'=>'partner'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('content', 0, array('a'=>'partner', 'd'=>'add')),
	'edit'	=>	manage::check_permit('content', 0, array('a'=>'partner', 'd'=>'edit')),
	'del'	=>	manage::check_permit('content', 0, array('a'=>'partner', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.content.partner/}</h1>
	<?php if($c['manage']['do']=='index'){?>
		<div class="turn_page"></div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=content&a=partner&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="partner" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.partner_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
					<?php }?>
					<td width="16%" nowrap="nowrap">{/global.name/}</td>
					<td width="9%" nowrap="nowrap">{/global.pic/}</td>
					<td width="18%" nowrap="nowrap">{/partner.url/}</td>
					<td width="10%" nowrap="nowrap">{/global.used/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$partner_row=str::str_code(db::get_limit_page('partners', '1', '*', $c['my_order'].'PId desc', (int)$_GET['page'], 20));
				foreach($partner_row[0] as $v){
				?>
					<tr pid="<?=$v['PId'];?>">
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['PId'];?>" class="va_m" /></td>
						<?php }?>
						<td nowrap="nowrap"><?=$v['Name'.$c['manage']['web_lang']];?></td>
						<td nowrap="nowrap" class="img"><a href="<?=$v['PicPath'];?>" title="<?=$v['Name'.$c['manage']['web_lang']];?>" target="_blank"><img class="photo" src="<?=$v['PicPath'];?>" alt="<?=$v['Name'.$c['manage']['web_lang']];?>" align="absmiddle" /></a></td>
						<td nowrap="nowrap"><a href="<?=$v['Url'];?>" target="_blank"><?=$v['Url'];?></a></td>
						<td nowrap="nowrap" class="used_checkbox">
							<?php if($permit_ary['edit']){?>
								<div class="switchery<?=(int)$v['IsUsed']==1?' checked':'';?>">
									<div class="switchery_toggler"></div>
									<div class="switchery_inner">
										<div class="switchery_state_on"></div>
										<div class="switchery_state_off"></div>
									</div>
								</div>
							<?php
							}else{
								echo (int)$v['IsUsed']==1?'{/global.n_y.1/}':'';
							}
							?>
						</td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=content&a=partner&d=edit&PId=<?=$v['PId'];?>" label="{/global.view/}"><img src="/static/ico/edit.png" alt="{/global.view/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=content.partner_del&PId=<?=$v['PId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($partner_row[1], $partner_row[2], $partner_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}else{
		$PId=(int)$_GET['PId'];
		$PId && $partner_row=str::str_code(db::get_one('partners', "PId='$PId'"));
	?>
    	<script type="text/javascript">$(document).ready(function(){content_obj.partner_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/global.name/}</label>
				<span class="input"><?=manage::form_edit($partner_row, 'text', 'Name', 50, 150, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.pic/}</label>
				<span class="input upload_file upload_pic">
					<div class="img">
						<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '100*100');?>" /></div>
						<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '200*80');?>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/partner.url/}</label>
				<span class="input"><input name="Url" value="<?=$partner_row['Url'];?>" type="text" class="form_input" size="50" maxlength="200" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.other/}</label>
				<span class="input">
					<?php if((int)$PId){?>
						{/global.used/}: <div class="switchery<?=(int)$partner_row['IsUsed']?' checked':'';?>">
							<input type="checkbox" name="IsUsed" value="1"<?=(int)$partner_row['IsUsed']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div><span class="tool_tips_ico" content="{/partner.used_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
					<?php }?>
					{/partner.myorder/}: <?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $partner_row['MyOrder']);?><span class="tool_tips_ico" content="{/partner.myorder_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=partner" class="btn_cancel mar_l_10">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="PId" name="PId" value="<?=$PId;?>" />
			<input type="hidden" name="PicPath" value="<?=$partner_row['PicPath'];?>" save="<?=is_file($c['root_path'].$partner_row['PicPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="content.partner_edit" />
		</form>
	<?php }?>
</div>