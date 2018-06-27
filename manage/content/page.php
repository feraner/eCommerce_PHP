<?php !isset($c) && exit();?>
<?php
manage::check_permit('content', 1, array('a'=>'page'));//检查权限

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['content']['page']['menu'] as $k=>$v){
	if(!manage::check_permit('content', 0, array('a'=>'page', 'd'=>$v))){
		if($v=='page' && $c['manage']['do']=='index') $out=1;
		continue;
	}else{
		$v=='page' && $v='index';
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=content&a=page&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

if($c['manage']['do']=='index' || $c['manage']['do']=='edit' || $c['manage']['do']=='category'|| $c['manage']['do']=='category_edit'){
	$cate_ary=str::str_code(db::get_all('article_category', '1'));//获取类别列表
	$category_ary=array();
	foreach((array)$cate_ary as $v){
		$category_ary[$v['CateId']]=$v;
	}
	$category_count=count($category_ary);
	unset($cate_ary);
	
	$CateId=(int)$_GET['CateId'];
	if($CateId){
		$category_one=str::str_code(db::get_one('article_category', "CateId='$CateId'"));
		!$category_one && js::location('./?m=content&a=page');
		$UId=$category_one['UId'];
		$column=$category_one['Category'.$c['manage']['web_lang']];
	}
	
	$Keyword=str::str_code($_GET['Keyword']);
}

$permit_ary=array(
	'add'		=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'category', 'p'=>'add')),
	'edit'		=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'category', 'p'=>'edit')),
	'del'		=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'category', 'p'=>'del')),
	'page_add'	=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'page', 'p'=>'add')),
	'page_edit'	=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'page', 'p'=>'edit')),
	'page_del'	=>	manage::check_permit('content', 0, array('a'=>'page', 'd'=>'page', 'p'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.content.page.module_name/}</h1>
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
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'article_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="content" />
				<input type="hidden" name="a" value="page" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['page_add']){?><li><a class="tip_ico_down add" href="./?m=content&a=page&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($CateId!=1 && $permit_ary['page_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }elseif($c['manage']['do']=='category'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="clear"></div>
				<input type="hidden" name="m" value="content" />
				<input type="hidden" name="a" value="page" />
				<input type="hidden" name="d" value="category" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=content&a=page&d=category_edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php if(manage::check_permit('content', 0, array('a'=>'page', 'd'=>'category'))){?>
			<dt></dt>
			<dd><a href="./?m=content&a=page&d=category"<?=($c['manage']['do']=='category' || $c['manage']['do']=='category_edit')?' class="current"':'';?>>{/global.category/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('content', 0, array('a'=>'page', 'd'=>'page'))){?>
			<dt></dt>
			<dd><a href="./?m=content&a=page"<?=($c['manage']['do']=='index' || $c['manage']['do']=='edit')?' class="current"':'';?>>{/page.page.page/}<?=$column?" ($column)":'';?></a></dd>
		<?php }?>
	</dl>
</div>
<div id="page" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//单页列表
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.page_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($CateId!=1 && ($permit_ary['page_edit'] || $permit_ary['page_del'])){?>
						<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td>
					<?php }?>
					<td width="41%" nowrap="nowrap">{/global.title/}</td>
					<td width="41%" nowrap="nowrap">{/global.category/}{/global.subjection/}</td>
					<td width="9%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['page_edit'] || $permit_ary['page_del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$where='1';//条件
				$page_count=20;//显示数量
				$Keyword && $where.=" and Title{$c['manage']['web_lang']} like '%$Keyword%'";
				$CateId && $where.=" and CateId='$CateId'";
				$page_row=str::str_code(db::get_limit_page('article', $where, '*', 'CateId>1, '.$c['my_order'].'AId desc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach((array)$page_row[0] as $v){
					$title=$v['Title'.$c['manage']['web_lang']];
					$url=ly200::get_url($v, 'article', $c['manage']['web_lang']);
				?>
					<tr>
						<?php if($CateId!=1 && ($permit_ary['page_edit'] || $permit_ary['page_del'])){?>
							<td nowrap="nowrap"><?php if($v['CateId']!=1){?><input type="checkbox" name="select" value="<?=$v['AId'];?>" /><?php }?></td>
						<?php }?>
						<td><a href="<?=$v['CateId']==1?'javascript:;':$url;?>" title="<?=$title;?>" target="_blank"><?=$title;?></a></td>
						<td class="category_select" cateid="<?=$v['CateId'];?>">
							<?php
							$UId=$category_ary[$v['CateId']]['UId'];
							if($UId){
								$key_ary=@explode(',',$UId);
								array_shift($key_ary);
								array_pop($key_ary);
								foreach((array)$key_ary as $k2=>$v2){
									echo $category_ary[$v2]['Category'.$c['manage']['web_lang']];
								}
							}
							echo $category_ary[$v['CateId']]['Category'.$c['manage']['web_lang']];
							?>
						</td>
						<td nowrap="nowrap" class="myorder_select" data-num="<?=$v['MyOrder'];?>"><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
						<?php if($permit_ary['page_edit'] || $permit_ary['page_del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['page_edit']){?><a class="tip_ico tip_min_ico" href="./?m=content&a=page&d=edit&AId=<?=$v['AId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($v['CateId']!=1 && $permit_ary['page_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=content.page_del&AId=<?=$v['AId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($page_row[1], $page_row[2], $page_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
	<?php
	}elseif($c['manage']['do']=='edit'){
		//单页编辑
		$AId=(int)$_GET['AId'];
		if($AId){
			$page_row=str::str_code(db::get_one('article', "AId='$AId'"));
			$page_content_row=str::str_code(db::get_one('article_content', "AId='$AId'"));
		}
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');?>
		<script type="text/javascript">$(document).ready(function(){content_obj.page_edit_init()});</script>
		<form id="edit_form" class="r_con_form wrap_content">
			<h3 class="rows_hd"><?=$AId?'{/global.edit/}':'{/global.add/}';?>{/page.page.page/}</h3>
			<div class="rows">
				<label>{/page.title/}</label>
				<span class="input"><?=manage::form_edit($page_row, 'text', 'Title', 53, 150, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<?php if($page_row['CateId']!=1){?>
				<div class="rows">
					<label>{/page.classify/}</label>
					<span class="input"><?=category::ouput_Category_to_Select('CateId', $page_row['CateId'], 'article_category', 'UId="0," and CateId!=1', 1, 'notnull', '{/global.select_index/}');?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/page.page.external_links/}</label>
					<span class="input"><input name="Url" value="<?=$page_row['Url'];?>" type="text" class="form_input" size="53" maxlength="150" /><span class="tool_tips_ico" content="{/page.page.links_notes/}"></span></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/page.page.custom_url/}</label>
					<span class="input">
						<span class="price_input"><b>/art/<div class="arrow"><em></em><i></i></div></b><input name="PageUrl" value="<?=$page_row['PageUrl'];?>" type="text" class="form_input" size="53" maxlength="150" /><b class="last">.html</b></span><span class="tool_tips_ico" content="{/page.page.custom_url_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.myorder/}</label>
					<span class="input"><?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $page_row['MyOrder']);?><span class="tool_tips_ico" content="{/page.page.myorder_notes/}"></span></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.seo/}</label>
					<span class="input tab_box">
						<?=manage::html_tab_button('border');?>
						<div class="blank9"></div>
						<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
							<div class="tab_txt tab_txt_<?=$k;?>">
								<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($page_row["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
								<div class="blank9"></div>
								<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($page_row["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
								<div class="blank9"></div>
								<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$page_row["SeoDescription_{$v}"];?></textarea></span>
							</div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows tab_box">
				<label>{/page.page.description/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("Content_{$v}", $page_content_row["Content_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=page" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="AId" name="AId" value="<?=$AId;?>" />
			<input type="hidden" name="do_action" value="content.page_edit" />
			<?=$page_row['CateId']==1?'<input type="hidden" id="CateId" name="CateId" value="'.$page_row['CateId'].'" />':'';?>
		</form>
	<?php
	}elseif($c['manage']['do']=='category'){
		//单页分类列表
		echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.page_category_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
					<?php }?>
					<?php if($permit_ary['edit']){?>
						<td width="4%" nowrap="nowrap">{/global.my_order/}</td>
					<?php }?>
					<td width="87%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$where='1';//条件
				$Keyword && $where.=" and Category{$c['manage']['web_lang']} like '%$Keyword%'";
				$category_row=str::str_code(db::get_all('article_category', $where, '*', $c['my_order'].'CateId asc'));
				$i=1;
				foreach($category_row as $v){
				?>
					<tr cateid="<?=$v['CateId'];?>">
						<?php if($CateId!=1 && $CateId!=99 && ($permit_ary['edit'] || $permit_ary['del'])){?>
							<td nowrap="nowrap"><?php if($v['CateId']!=1 && $v['CateId']!=99){?><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /><?php }?></td>
						<?php }?>
						<?php if($permit_ary['edit']){?>
							<td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td>
						<?php }?>
						<td><?=$v['Category'.$c['manage']['web_lang']];?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=content&a=page&d=category_edit&CateId=<?=$v['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($v['CateId']!=1 && $v['CateId']!=99 && $permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=content.page_category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
	<?php
	}elseif($c['manage']['do']=='category_edit'){
		//单页分类编辑
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.page_category_edit_init()});</script>
		<form id="edit_form" class="r_con_form wrap_content">
			<h3 class="rows_hd"><?=$CateId?'{/global.edit/}':'{/global.add/}';?>{/page.classify/}</h3>
			<div class="rows">
				<label>{/page.title/}</label>
				<span class="input"><?=manage::form_edit($category_one, 'text', 'Category', 35, 50, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<?php
			/* 暂时屏蔽
			<div class="rows">
				<label>{/global.seo/}</label>
				<span class="input tab_box">
					<?=manage::html_tab_button('border');?>
					<div class="blank9"></div>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>">
							<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($category_one["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
							<div class="blank9"></div>
							<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($category_one["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
							<div class="blank9"></div>
							<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$category_one["SeoDescription_{$v}"];?></textarea></span>
						</div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			*/
			?>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=page&d=category" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="CateId" value="<?=$CateId;?>" />
			<input type="hidden" name="do_action" value="content.page_category_edit">
		</form>
	<?php }?>
</div>