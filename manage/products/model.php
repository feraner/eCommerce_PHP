<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'model'));//检查权限

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['products']['model']['menu'] as $k=>$v){
	if(!manage::check_permit('products', 0, array('a'=>'model', 'd'=>$v))){
		if($v=='category' && $c['manage']['do']=='index') $out=1;
		continue;
	}else{
		$v=='category' && $v='index';
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=products&a=model&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

$Keyword=str::str_code($_GET['Keyword']);
$ParentId=(int)$_GET['ParentId'];
$AttrId=(int)$_GET['AttrId'];
$attr_row=str::str_code(db::get_one('products_attribute', "AttrId='$AttrId'"));

if($c['manage']['do']=='model' || $c['manage']['do']=='model_edit'){
	$cate_ary=str::str_code(db::get_all('products_attribute', 'ParentId=0', '*', $c['my_order'].'AttrId asc'));//获取类别列表
	$category_ary=array();
	foreach((array)$cate_ary as $v){
		$category_ary[$v['AttrId']]=$v;
	}
	$category_count=count($category_ary);
	unset($cate_ary);
	
	$column=$attr_row['Name'.$c['manage']['web_lang']];
}

$permit_ary=array(
	'add'		=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'category', 'p'=>'add')),
	'edit'		=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'category', 'p'=>'edit')),
	'del'		=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'category', 'p'=>'del')),
	'model_add'	=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'model', 'p'=>'add')),
	'model_edit'=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'model', 'p'=>'edit')),
	'model_del'	=>	manage::check_permit('products', 0, array('a'=>'model', 'd'=>'model', 'p'=>'del'))
);

echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<script type="text/javascript">$(function(){
	products_obj.model_init();
	products_obj.attr_init();
});</script>
<div class="r_nav">
	<h1>{/products.attribute/}</h1>
	<?php if($c['manage']['do']=='index'){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}" data-id="0"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part"></dl>
</div>
<div id="model" class="r_con_wrap">
	<?php
	//产品属性分类
	$all_attr_ary=array();
	$category_row=str::str_code(db::get_all('products_attribute', '1', '*', $c['my_order'].'AttrId asc'));
	foreach((array)$category_row as $v){
		foreach($c['manage']['config']['Language'] as $k2=>$v2){
			unset($v['Value_'.$v2]);
		}
		$all_attr_ary[$v['ParentId']][]=$v;
	}
	//产品属性选项
	$all_value_ary=array();
	$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc'));
	foreach((array)$value_row as $v){
		$all_value_ary[$v['AttrId']][]=$v;
	}
	?>
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
				<td width="21%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
				<td width="61%" nowrap="nowrap">{/products.model.attr_list/}</td>
				<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach((array)$all_attr_ary[0] as $v){
				$Name=$v['Name'.$c['manage']['web_lang']];
				if($Keyword && !stripos($Name, $Keyword)) continue;
			?>
				<tr data="<?=htmlspecialchars(str::json_data($v));?>" data-attr-id="<?=$v['AttrId'];?>">
					<?php if($permit_ary['del']){?><td nowrap><input type="checkbox" name="select" value="<?=$v['AttrId'];?>" class="va_m" /></td><?php }?>
					<td><a href="./?m=products&a=model&d=model&AttrId=<?=$v['AttrId'];?>" title="<?=$Name;?>"><?=$Name;?></a></td>
					<td class="attr_list">
						<?php
						foreach((array)$all_attr_ary[$v['AttrId']] as $vv){
						?>
							<dl class="attr_box" data="<?=htmlspecialchars(str::json_data($vv));?>" data-value="<?=htmlspecialchars(str::json_data($all_value_ary[$vv['AttrId']]));?>">
								<?php if($permit_ary['model_edit']){?>
									<dd class="attr_ico"></dd>
								<?php }?>
								<dd class="attr_txt"><?=$vv['Name'.$c['manage']['web_lang']];?></dd>
								<?php if($permit_ary['model_edit'] || $permit_ary['model_del']){?>
									<dd class="attr_menu">
										<?php if($permit_ary['model_edit']){?><a class="edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$vv['AttrId'];?>"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
										<?php if($permit_ary['model_del']){?><a class="del" href="./?do_action=products.model_del&AttrId=<?=$vv['AttrId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
									</dd>
								<?php }?>
							</dl>
						<?php }?>
						<?php if($permit_ary['model_add']){?><div class="attr_add"><a class="add" href="javascript:;" data-id="0">+</a></div><?php }?>
					</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td nowrap>
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['AttrId'];?>"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.model_del&AttrId=<?=$v['AttrId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
			<?php }?>
		</tbody>
	</table>
	<?php /***************************** 属性编辑 Start *****************************/?>
	<div class="pop_form box_category_edit">
		<form id="category_edit_form">
			<div class="t"><h1><span></span>{/global.category/}</h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/global.category/}{/global.name/}</label>
					<span class="input"><?=manage::form_edit('', 'text', 'Name', 35, 50, 'notnull');?></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="AttrId" value="" />
				<input type="hidden" name="do_action" value="products.model_edit" />
			</div>
			<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
		</form>
	</div>
	<div class="pop_form box_model_edit">
		<form id="edit_form">
			<div class="t"><h1><span></span>{/products.attribute/}</h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/products.title/}</label>
					<span class="input"><?=manage::form_edit('', 'text', 'Name', 35, 50, 'notnull');?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.model.children/}<font class="fc_red">*</font></label>
					<span class="input"></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.option/}</label>
					<span class="input fixed">
						<input type="checkbox" name="CartAttr" value="1" /> {/products.model.cart_attr/}<span class="tool_tips_ico" content="{/products.model.cart_attr_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
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
									<span class="price_input not_input"><b>{/global.name/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Value_<?=$v;?>[]" value="<?=$vv["Value_{$v}"];?>" class="form_input input_name" size="30" maxlength="100" /></span>
									<a href="javascript:;" class="btn_option del">-</a>
									<a href="javascript:;" class="btn_option add">+</a>
								</div>
							</div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="AttrId" value="" />
				<input type="hidden" name="do_action" value="products.model_attribute_edit" />
			</div>
			<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
		</form>
	</div>
	<?php /***************************** 属性编辑 End *****************************/?>
</div>