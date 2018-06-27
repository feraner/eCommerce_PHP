<?php
manage::check_permit('products', 1, array('a'=>'overseas'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('products', 0, array('a'=>'overseas', 'd'=>'add')),
	'edit'	=>	manage::check_permit('products', 0, array('a'=>'overseas', 'd'=>'edit')),
	'del'	=>	manage::check_permit('products', 0, array('a'=>'overseas', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.products.overseas/}</h1>
	<?php if($c['manage']['do']=='index'){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}" data-id="-1"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="overseas" class="r_con_wrap">
	<script type="text/javascript">$(document).ready(function(){products_obj.overseas_init()});</script>
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="6%" nowrap="nowrap">{/global.serial/}</td>
				<td width="84%" nowrap="nowrap">{/global.name/}</td>
				<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
			$shipping_overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
			foreach($shipping_overseas_row as $k=>$v){
				if((int)$c['manage']['config']['Overseas']==0 && $v['OvId']>1) continue;
			?>
				<tr cid="<?=$v['OvId'];?>" data="<?=htmlspecialchars(str::json_data($v));?>">
					<td nowrap="nowrap"><?=$k+1;?></td>
					<td nowrap="nowrap"><?=$v['Name'.$c['manage']['web_lang']];?></td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td nowrap="nowrap">
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['OvId'];?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" /></a><?php }?>
							<?php if($v['OvId']>1 && $permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.shipping_overseas_del&OvId=<?=$v['OvId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<?php /***************************** 发货地编辑 Start *****************************/?>
	<div class="pop_form box_overseas_edit">
		<form id="edit_form">
			<div class="t"><h1><span></span>{/module.products.overseas/}</h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/global.name/}</label>
					<span class="input"><?=manage::form_edit('', 'text', 'Name', 53, 150, 'notnull');?></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="OvId" value="0" />
				<input type="hidden" name="do_action" value="set.shipping_overseas_edit" />
			</div>
			<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
		</form>
	</div>
	<?php /***************************** 发货地编辑 End *****************************/?>
</div>