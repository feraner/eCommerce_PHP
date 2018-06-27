<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'business'));//检查权限

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['products']['business']['menu'] as $k=>$v){
	if(!manage::check_permit('products', 0, array('a'=>'business', 'd'=>$v))){
		if($v=='business' && $c['manage']['do']=='index') $out=1;
		continue;
	}else{
		$v=='business' && $v='index';
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=products&a=business&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

$cate_ary=str::str_code(db::get_all('business_category', '1'));//获取类别列表
$category_ary=array();
foreach((array)$cate_ary as $v){
	$category_ary[$v['CateId']]=$v;
}
$category_count=count($category_ary);
unset($cate_ary);

$CateId=(int)$_GET['CateId'];
if($CateId){
	$category_one=str::str_code(db::get_one('business_category', "CateId='$CateId'"));
	$UId=$category_one['UId'];
	if($c['manage']['do']=='index'){
		$column=$category_one['Category'];
	}
}

$permit_ary=array(
	'add'		=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'business', 'p'=>'add')),
	'edit'		=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'business', 'p'=>'edit')),
	'del'		=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'business', 'p'=>'del')),
	'cate_add'	=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'category', 'p'=>'add')),
	'cate_edit'	=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'category', 'p'=>'edit')),
	'cate_del'	=>	manage::check_permit('products', 0, array('a'=>'business', 'd'=>'category', 'p'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.products.business.module_name/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='index'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'business_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="products" />
				<input type="hidden" name="a" value="business" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=products&a=business&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['edit']){?><li><a class="tip_ico_down order" href="javascript:;" label="{/global.my_order/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }elseif($c['manage']['do']=='category'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'business_category', 'UId="0,"', 'Dept<2', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="products" />
				<input type="hidden" name="a" value="business" />
				<input type="hidden" name="d" value="category" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['cate_add']){?><li><a class="tip_ico_down add" href="./?m=products&a=business&d=category_edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['cate_edit']){?><li><a class="tip_ico_down order" href="javascript:;" label="{/global.my_order/}"></a></li><?php }?>
			<?php if($permit_ary['cate_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php if(manage::check_permit('products', 0, array('a'=>'business', 'd'=>'category'))){?>
			<dt></dt>
			<dd><a href="./?m=products&a=business&d=category"<?=($c['manage']['do']=='category' || $c['manage']['do']=='category_edit')?' class="current"':'';?>>{/global.category/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('products', 0, array('a'=>'business', 'd'=>'business'))){?>
			<dt></dt>
			<dd><a href="./?m=products&a=business"<?=($c['manage']['do']=='index' || $c['manage']['do']=='edit')?' class="current"':'';?>>{/module.products.business.module_name/}<?=$column?" ($column)":'';?></a></dd>
		<?php }?>
	</dl>
</div>
<div id="business" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.business_init()});</script>
		<?php if($permit_ary['edit']){?>
			<div class="r_con_column">
				{/business.business.business/}{/global.used/}&nbsp;&nbsp;
				<div class="switchery <?=(int)db::get_value('config', 'GroupId="business"', 'Value')?' checked':'';?>">
					<div class="switchery_toggler"></div>
					<div class="switchery_inner">
						<div class="switchery_state_on"></div>
						<div class="switchery_state_off"></div>
					</div>
				</div>
			</div>
		<?php }?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td><?php }?>
					<td width="27%" nowrap="nowrap">{/global.title/}</td>
					<td width="27%" nowrap="nowrap">{/global.category/}{/global.subjection/}</td>
					<td width="27%" nowrap="nowrap">{/business.business.url/}</td>
					<td width="9%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=str::str_code($_GET['Keyword']);
				$where='1';//条件
				$page_count=20;//显示数量
				$Keyword && $where.=" and Name like '%$Keyword%'";
				$CateId && $where.=" and CateId='$CateId'";
				$business_row=str::str_code(db::get_limit_page('business', $where, '*', $c['my_order'].'BId desc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach((array)$business_row[0] as $v){
				?>
					<tr>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['BId'];?>" /></td><?php }?>
						<td><?=$v['Name'];?></td>
						<td class="category_select" cateid="<?=$v['CateId'];?>">
							<?php
							$UId=$category_ary[$v['CateId']]['UId'];
							if($UId){
								$key_ary=@explode(',',$UId);
								array_shift($key_ary);
								array_pop($key_ary);
								foreach((array)$key_ary as $k2=>$v2){
									echo $category_ary[$v2]['Category'].'->';
								}
							}
							echo $category_ary[$v['CateId']]['Category'];
							?>
						</td>
						<td nowrap="nowrap"><?=$v['Url']?'<a href="'.$v['Url'].'" target="_blank">'.$v['Url'].'</a>':'';?></td>
						<td nowrap="nowrap" class="myorder"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", $v['MyOrder']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=business&d=edit&BId=<?=$v['BId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.business_del&BId=<?=$v['BId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($business_row[1], $business_row[2], $business_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}elseif($c['manage']['do']=='edit'){
		//供应商编辑页
		$BId=(int)$_GET['BId'];
		if($BId){
			$business_one=db::get_one('business', "BId='$BId'");
		}
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.business_edit_init()});</script>
		<form id="edit_form" class="r_con_form wrap_content">
			<h3 class="rows_hd"><?=$BId?'{/global.edit/}':'{/global.add/}';?>{/business.business.business/}</h3>
			<div class="rows">
				<label>{/products.name/}</label>
				<span class="input"><input type='text' name='Name' value='<?=$business_one['Name'];?>' class='form_input' size='53' maxlength='150' notnull></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.url/}</label>
				<span class="input"><input type='text' name='Url' value='<?=$business_one['Url'];?>' class='form_input' size='50' maxlength='150' notnull></span>
				<div class="clear"></div>
			</div>
			<?php if($BId){?>
				<div class="rows">
					<label>{/manage.manage.create_time/}</label>
					<span class="input"><?=date('Y-m-d H:i:s',$business_one['AccTime']);?></span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label>{/business.business.classify/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateId', $business_one['CateId'], 'business_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.address/}</label>
				<span class="input"><input type='text' name='Address' value='<?=$business_one['Address'];?>' class='form_input' size='50' maxlength='150' notnull></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.remark/}</label>
				<span class="input"><input type='text' name='Remark' value='<?=$business_one['Remark'];?>' class='form_input' size='50' maxlength='150'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.qualification/}</label>
				<span class="input upload_file upload_img">
					<div class="img">
						<div id="ImgDetail" class="upload_box preview_pic"><input type="button" id="ImgUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.voucher/}</label>
				<span class="input upload_file upload_pic">
					<div class="img">
						<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.entity/}</label>
				<span class="input"><input type='text' name='Entity' value='<?=$business_one['Entity'];?>' class='form_input' size='50' maxlength='150'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.contacts/}</label>
				<span class="input"><input type='text' name='Contacts' value='<?=$business_one['Contacts'];?>' class='form_input' size='50' maxlength='150'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.phone/}</label>
				<span class="input"><input type='text' name='Phone' value='<?=$business_one['Phone'];?>' class='form_input' size='50' maxlength='30'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.telephone/}</label>
				<span class="input"><input type='text' name='Telephone' value='<?=$business_one['Telephone'];?>' class='form_input' size='50' maxlength='30'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.fax/}</label>
				<span class="input"><input type='text' name='Fax' value='<?=$business_one['Fax'];?>' class='form_input' size='50' maxlength='30'></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.qq/}</label>
				<span class="input"><input type='text' name='QQ' value='<?=$business_one['QQ'];?>' class='form_input' size='50' maxlength='20' rel="amount"></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" /><a href="./?m=products&a=business" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="BId" value="<?=$BId;?>" />
			<input type="hidden" name="ImgPath" value="<?=$business_one['ImgPath'];?>" save="<?=is_file($c['root_path'].$business_one['ImgPath'])?1:0;?>" />
			<input type="hidden" name="PicPath" value="<?=$business_one['PicPath'];?>" save="<?=is_file($c['root_path'].$business_one['PicPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="products.business_edit" />
		</form>
	<?php
	}elseif($c['manage']['do']=='category'){
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.business_category_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['cate_del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="41%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
					<td width="41%" nowrap="nowrap">{/global.category/}{/global.subjection/}</td>
					<td width="9%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['cate_edit'] || $permit_ary['cate_del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$where='1';//条件
				$page_count=50;//显示数量
				if($CateId){
					$where.=" and UId='{$UId}{$CateId},'";
				}
				$Keyword=str::str_code($_GET['Keyword']);
				$Keyword && $where.=" and Category like '%$Keyword%'";
				$category_row=str::str_code(db::get_limit_page('business_category', $where, '*', $c['my_order'].'CateId asc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach($category_row[0] as $v){
				?>
					<tr>
						<?php if($permit_ary['cate_del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /></td><?php }?>
						<td><?=$v['Category'];?></td>
						<td class="category_select" cateid="<?=$v['CateId'];?>">
							<?php
							$UId=$category_ary[$v['CateId']]['UId'];
							if($UId){
								if($UId=='0,'){
									echo '--';
								}else{
									$key_ary=@explode(',',$UId);
									array_shift($key_ary);
									array_pop($key_ary);
									foreach((array)$key_ary as $k2=>$v2){
										echo $category_ary[$v2]['Category'];
									}
								}
							}
							?>
						</td>
						<td nowrap="nowrap" class="myorder"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", $v['MyOrder']);?></td>
						<?php if($permit_ary['cate_edit'] || $permit_ary['cate_del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['cate_edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=business&d=category_edit&CateId=<?=$v['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['cate_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.business_category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($category_row[1], $category_row[2], $category_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
	<?php
	}elseif($c['manage']['do']=='category_edit'){
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.business_category_edit_init()});</script>
		<form id="edit_form" class="r_con_form wrap_content">
			<h3 class="rows_hd"><?=$CateId?'{/global.edit/}':'{/global.add/}';?>{/business.business.classify/}</h3>
			<div class="rows">
				<label>{/business.business.classify/}</label>
				<span class="input"><input name="Category" value="<?=$category_one['Category'];?>" type="text" class="form_input" maxlength="100" size="50" notnull> <font class="fc_red">*</font></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/business.business.children/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('UnderTheCateId', category::get_CateId_by_UId($category_one['UId']), 'business_category', 'UId="0,"', "CateId!='{$category_one['CateId']}' and Dept<2", '', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /><a href="./?m=products&a=business&d=category" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="CateId" value="<?=$CateId;?>" />
			<input type="hidden" name="do_action" value="products.business_category_edit" />
		</form>
	<?php }?>
</div>