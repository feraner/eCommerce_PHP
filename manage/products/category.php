<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'category'));//检查权限

$Keyword=$_GET['Keyword'];
$CateId=(int)$_GET['CateId'];

//获取类别列表
$cate_ary=str::str_code(db::get_all('products_category', '1'));
$category_ary=array();
foreach((array)$cate_ary as $v){
	$category_ary[$v['CateId']]=$v;
}
$category_count=count($category_ary);
unset($cate_ary);

if($CateId){
	$column='<a href="./?m=products&a=category">{/products.products_category.all_category/}</a>->';
	$category_row=db::get_one('products_category', "CateId='$CateId'");
	$category_description_row=str::str_code(db::get_one('products_category_description', "CateId='$CateId'"));
	$UId=$category_ary[$CateId]['UId'];
	if($UId){
		$key_ary=@explode(',',$UId);
		array_shift($key_ary);
		array_pop($key_ary);
		foreach((array)$key_ary as $k=>$v){
			$column.='<a href="./?m=products&a=category&CateId='.$v.'">'.$category_ary[$v]['Category'.$c['manage']['web_lang']].'</a>->';
		}
	}
	$column.=$category_ary[$CateId]['Category'.$c['manage']['web_lang']];
}else{
	$column='{/products.products_category.all_category/}';
}

$permit_ary=array(
	'add'	=>	manage::check_permit('products', 0, array('a'=>'category', 'd'=>'add')),
	'edit'	=>	manage::check_permit('products', 0, array('a'=>'category', 'd'=>'edit')),
	'del'	=>	manage::check_permit('products', 0, array('a'=>'category', 'd'=>'del'))
);

echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<script type="text/javascript">$(function(){products_obj.category_init()});</script>
<div class="r_nav">
	<h1>{/products.products_category.category/}</h1>
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
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 'Dept<3', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="products" />
				<input type="hidden" name="a" value="category" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=products&a=category&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<?php if($column){?>
		<dl class="edit_form_part">
			<dt></dt>
			<dd><?=$column?$column:'';?></dd>
		</dl>
	<?php }?>
</div>
<div id="category" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//产品分类列表
	?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<?php if($permit_ary['edit']){?><td width="4%" nowrap="nowrap">{/global.my_order/}</td><?php }?>
					<td width="4%" nowrap="nowrap">ID</td>
					<td width="83%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$where='1';//条件
				$Keyword && $where.=" and Category{$c['manage']['web_lang']} like '%$Keyword%'";
				if($CateId){
					$UId=category::get_UId_by_CateId($CateId);
					$where.=" and UId='{$UId}'";
				}else{
					$where.=' and UId="0,"';
				}
				$category_row=str::str_code(db::get_all('products_category', $where, '*', $c['my_order'].'CateId asc'));
				$i=1;
				foreach($category_row as $v){
				?>
					<tr cateid="<?=$v['CateId'];?>">
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /></td><?php }?>
						<?php if($permit_ary['edit']){?><td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td><?php }?>
						<td nowrap="nowrap"><?=$v['CateId'];?></td>
						<td><a href="<?=$v['SubCateCount']?"./?m=products&a=category&CateId={$v['CateId']}":'javascript:;';?>" title="<?=$v['Category'.$c['manage']['web_lang']];?>"><?=$v['Category'.$c['manage']['web_lang']];?></a> <?=$v['SubCateCount']?"({$v['SubCateCount']})":''?><?=$v['IsIndex']?'&nbsp;&nbsp;<span class="fc_red">{/products.products.is_index/}</span>':'';?><?=$v['IsSoldOut']?'&nbsp;&nbsp;<span class="fc_red">{/products.products.sold_out/}</span>':'';?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=category&d=edit&CateId=<?=$v['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
	<?php
	}elseif($c['manage']['do']=='edit'){
		//产品分类编辑
		$edit_ok=0;
		if(($CateId && manage::check_permit('products', 0, array('a'=>'category', 'd'=>'edit'))) || (!$CateId && manage::check_permit('products', 0, array('a'=>'category', 'd'=>'add')))) $edit_ok=1;//修改权限
		$category_description_row=db::get_one('products_category_description', "CateId='{$category_row['CateId']}'");
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');?>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd"><?=$CateId?'{/global.edit/}':'{/global.add/}';?>{/products.products_category.category/}</h3>
			<div class="rows">
				<label>{/products.title/}</label>
				<span class="input"><?=manage::form_edit($category_row, 'text', 'Category', 35, 150, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.products_category.children/}</label>
				<span class="input">
					<?php
					$now_dept=$category_row['Dept']+3-(db::get_max('products_category', "UId like '{$category_row['UId']}{$category_row['CateId']},%'", 'Dept'));
					$ext_where="CateId!='{$category_row['CateId']}' and Dept<".($category_row['SubCateCount']?$now_dept:3);
					echo category::ouput_Category_to_Select('UnderTheCateId', category::get_CateId_by_UId($category_row['UId']), 'products_category', "UId='0,' and $ext_where", $ext_where, '', '{/global.select_index/}');
					?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.picture/}</label>
				<span class="input upload_file upload_pic">
					<?php if($edit_ok){?>
						<div class="img">
							<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					<?php
					}else{
						if(is_file($c['root_path'].$category_row['PicPath'])) echo '<img src="'.$category_row['PicPath'].'" />';
					}
					?>
				</span>
				<div class="clear"></div>
			</div>
			<?php if(!$CateId || $category_row['Dept']==1){?>
				<div class="rows">
					<label>{/products.product/}{/products.attribute/}</label>
					<span class="input">
						<?=ly200::form_select(db::get_all('products_attribute','ParentId=0','*', $c['my_order'].'AttrId asc'), 'AttrId', $category_row['AttrId'], 'Name'.$c['manage']['web_lang'], 'AttrId', "{/global.select_index/}");?>
						<div class="attribute"></div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.isindex/}</label>
					<span class="input">
						<div class="switchery<?=(int)$category_row['IsIndex']?' checked':'';?>">
							<input type="checkbox" name="IsIndex" value="1"<?=(int)$category_row['IsIndex']?' checked':'';?>>
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
			<div class="rows">
				<label>{/products.products.sold_out/}</label>
				<span class="input">
					<div class="switchery<?=(int)$category_row['IsSoldOut']?' checked':'';?>">
						<input type="checkbox" name="IsSoldOut" value="1"<?=(int)$category_row['IsSoldOut']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>{/global.brief/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>">
							<span class='price_input lang_input price_textarea long_textarea'><textarea name='BriefDescription_<?=$v;?>'><?=$category_row["BriefDescription_{$v}"];?></textarea></span>
						</div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.seo/}</label>
				<span class="input tab_box">
					<?=manage::html_tab_button('border');?>
					<div class="blank9"></div>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>">
							<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($category_row["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
							<div class="blank9"></div>
							<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($category_row["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
							<div class="blank9"></div>
							<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$category_row["SeoDescription_{$v}"];?></textarea></span>
						</div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>{/products.products.description/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("Description_{$v}", $category_description_row["Description_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<?php
			$IsDesc=str::json_data(htmlspecialchars_decode($category_row['IsDesc']), 'decode');
			$page_row=str::str_code(db::get_limit('article', 'CateId=1', '*', 'AId asc', 0, 3));
			for($i=0; $i<3; ++$i){
			?>
			<div class="rows tab_box">
				<label><?=$page_row[$i]['Title'.$c['manage']['web_lang']]?></label><?php /*?>{/products.tab/}<?=$i+1;?><?php */?>
				<span class="input">
					<div class="switchery<?=$IsDesc[$i]?' checked':'';?>">
						<input type="checkbox" name="IsDesc[<?=$i;?>]" value="1" class="desc_tab_btn"<?=$IsDesc[$i]?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<div class="desc_box <?=$IsDesc[$i]?'show':'hide';?>">
						<div class="blank15"></div>
						<?=manage::html_tab_button('border');?>
						<div class="blank9"></div>
						<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
							<div class="tab_txt tab_txt_<?=$k;?>">
								{/news.news.seo_title/}: <span class="price_input lang_input"><input type="text" name="TabName_<?=$i;?>_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($category_description_row["TabName_{$i}_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="50" /></span>
								<div class="blank15"></div>
								<?=manage::Editor("Tab_{$i}_{$v}", $category_description_row["Tab_{$i}_{$v}"]);?>
							</div>
						<?php }?>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<?php }?>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="CateId" value="<?=$CateId;?>" />
			<input type="hidden" name="PicPath" value="<?=$category_row['PicPath'];?>" save="<?=is_file($c['root_path'].$category_row['PicPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="products.category_edit">
		</form>
	<?php }?>
</div>