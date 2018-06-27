<?php !isset($c) && exit();?>
<?php
manage::check_permit('content', 1, array('a'=>'news'));//检查权限

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['content']['news']['menu'] as $k=>$v){
	if(!manage::check_permit('content', 0, array('a'=>'news', 'd'=>$v))){
		if($v=='news' && $c['manage']['do']=='index') $out=1;
		continue;
	}else{
		$v=='news' && $v='index';
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=content&a=news&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

if($c['manage']['do']=='index' || $c['manage']['do']=='edit' || $c['manage']['do']=='category' || $c['manage']['do']=='category_edit'){
	$cate_ary=str::str_code(db::get_all('info_category', '1', '*', $c['my_order'].'CateId asc'));//获取类别列表
	$all_cate_ary=$category_ary=array();
	foreach((array)$cate_ary as $v){
		$category_ary[$v['CateId']]=$v;
		$all_cate_ary[$v['UId']][]=$v;
	}
	$category_count=count($category_ary);
	unset($cate_ary);
	
	$CateId=(int)$_GET['CateId'];
	if($CateId){
		$category_row=str::str_code(db::get_one('info_category', "CateId='$CateId'"));
		!$category_row && js::location('./?m=content&a=news');
		$UId=$category_row['UId'];
		$UId!='0,' && $TopCateId=category::get_top_CateId_by_UId($UId);
		$column=$category_row['Category'.$c['manage']['web_lang']];
	}
	
	$Keyword=$_GET['Keyword'];
	$ParentId=(int)$_GET['ParentId'];
}

$permit_ary=array(
	'add'		=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'category', 'p'=>'add')),
	'edit'		=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'category', 'p'=>'edit')),
	'del'		=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'category', 'p'=>'del')),
	'news_add'	=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'news', 'p'=>'add')),
	'news_edit'	=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'news', 'p'=>'edit')),
	'news_del'	=>	manage::check_permit('content', 0, array('a'=>'news', 'd'=>'news', 'p'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.content.news.module_name/}</h1>
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
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'info_category', 'UId="0,"', 1, '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="content" />
				<input type="hidden" name="a" value="news" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['news_add']){?><li><a class="tip_ico_down add" href="./?m=content&a=news&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['news_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
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
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'info_category', 'UId="0,"', 'Dept<2', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="content" />
				<input type="hidden" name="a" value="news" />
				<input type="hidden" name="d" value="category" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=content&a=news&d=category_edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php if(manage::check_permit('content', 0, array('a'=>'news', 'd'=>'category'))){?>
			<dt></dt>
			<dd><a href="./?m=content&a=news&d=category"<?=($c['manage']['do']=='category' || $c['manage']['do']=='category_edit')?' class="current"':'';?>>{/global.category/}<?=$column && $c['manage']['do']=='category'?" ($column)":'';?></a></dd>
		<?php }?>
		<?php if(manage::check_permit('content', 0, array('a'=>'news', 'd'=>'news'))){?>
			<dt></dt>
			<dd><a href="./?m=content&a=news"<?=($c['manage']['do']=='index' || $c['manage']['do']=='edit')?' class="current"':'';?>>{/news.news.news/}<?=$column && $c['manage']['do']=='index'?" ($column)":'';?></a></dd>
		<?php }?>
	</dl>
</div>
<div id="news" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//文章列表
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.news_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['news_edit'] || $permit_ary['news_del']){?>
						<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td>
					<?php }?>
					<td width="41%" nowrap="nowrap">{/global.title/}</td>
					<td width="31%" nowrap="nowrap">{/global.category/}{/global.subjection/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
					<td width="9%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['news_edit'] || $permit_ary['news_del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$where='1';//条件
				$page_count=20;//显示数量
				$Keyword && $where.=" and Title{$c['manage']['web_lang']} like '%$Keyword%'";
				$CateId && $where.=" and CateId in(select CateId from info_category where UId like '".category::get_UId_by_CateId($CateId, 'info_category')."%') or CateId='{$CateId}'";
				$news_row=str::str_code(db::get_limit_page('info', $where, '*', $c['my_order'].'InfoId desc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach((array)$news_row[0] as $v){
					$title=$v['Title'.$c['manage']['web_lang']];
					$url=ly200::get_url($v, 'info', $c['manage']['web_lang']);
				?>
					<tr>
						<?php if($permit_ary['news_edit'] || $permit_ary['news_del']){?>
							<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['InfoId'];?>" /></td>
						<?php }?>
						<td><a href="<?=$url;?>" title="<?=$title;?>" target="_blank"><?=$title;?></a><?=$v['IsIndex']?'&nbsp;&nbsp;<span class="fc_red">{/products.products.is_index/}</span>':'';?></td>
						<td class="category_select" cateid="<?=$v['CateId'];?>">
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
						<td nowrap="nowrap"><?=$v['EditTime']?date('Y-m-d', $v['EditTime']):'N/A';?></td>
						<td nowrap="nowrap"<?=$permit_ary['news_edit']?' class="myorder_select" data-num="'.$v['MyOrder'].'"':'';?>><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
						<?php if($permit_ary['news_edit'] || $permit_ary['news_del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['news_edit']){?><a class="tip_ico tip_min_ico" href="./?m=content&a=news&d=edit&InfoId=<?=$v['InfoId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['news_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=content.news_del&InfoId=<?=$v['InfoId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($news_row[1], $news_row[2], $news_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
	<?php
	}elseif($c['manage']['do']=='edit'){
		//文章编辑
		$InfoId=(int)$_GET['InfoId'];
		if($InfoId){
			$news_row=str::str_code(db::get_one('info', "InfoId='$InfoId'"));
			$news_content_row=str::str_code(db::get_one('info_content', "InfoId='$InfoId'"));
		}
		echo ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.news_edit_init()});</script>
		<form id="edit_form" class="r_con_form wrap_content">
			<h3 class="rows_hd"><?=$InfoId?'{/global.edit/}':'{/global.add/}';?>{/news.news.news/}</h3>
			<div class="rows">
				<label>{/news.title/}</label>
				<span class="input"><?=manage::form_edit($news_row, 'text', 'Title', 53, 150, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/news.classify/}</label>
				<span class="input"><?=category::ouput_Category_to_Select('CateId', $news_row['CateId'], 'info_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/news.news.external_links/}</label>
				<span class="input"><input name="Url" value="<?=$news_row['Url'];?>" type="text" class="form_input" size="53" maxlength="150" /><span class="tool_tips_ico" content="{/news.news.links_notes/}"></span></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/page.page.custom_url/}</label>
				<span class="input">
					<span class="price_input"><b>/info/<div class="arrow"><em></em><i></i></div></b><input name="PageUrl" value="<?=$news_row['PageUrl'];?>" type="text" class="form_input" size="53" maxlength="150" /><b class="last">.html</b></span><span class="tool_tips_ico" content="{/page.page.custom_url_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.picture/}</label>
				<span class="input upload_file upload_pic">
					<div class="img">
						<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '100*100');?>" /></div>
						<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '100*100');?>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>{/products.products.briefdescription/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>">
							<span class='price_input lang_input price_textarea long_textarea'><textarea name='BriefDescription_<?=$v;?>'><?=$news_row["BriefDescription_{$v}"];?></textarea></span>
						</div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.time/}</label>
				<span class="input"><input name="EditTime" value="<?=date('Y-m-d',($news_row['EditTime']?$news_row['EditTime']:$c['time']));?>" type="text" class="form_input" size="12" readonly></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.products.other/}{/products.products.attributes/}</label>
				<span class="input">
					<span class="choice_btn<?=$news_row['IsIndex']?' current':'';?> mar_r_0">{/products.products.is_index/}<input type="checkbox" value="1" name="IsIndex"<?=$news_row['IsIndex']?' checked':'';?> /></span><span class="tool_tips_ico" content="{/news.news.index_notes/}"></span>&nbsp;&nbsp;&nbsp;&nbsp;
					{/products.myorder/}:<?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $news_row['MyOrder']);?><span class="tool_tips_ico" content="{/news.news.myorder_notes/}"></span>
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
							<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($news_row["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
							<div class="blank9"></div>
							<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($news_row["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
							<div class="blank9"></div>
							<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$news_row["SeoDescription_{$v}"];?></textarea></span>
						</div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>{/news.news.description/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("Content_{$v}", $news_content_row["Content_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=news" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="InfoId" name="InfoId" value="<?=$InfoId;?>" />
			<input type="hidden" name="PicPath" value="<?=$news_row['PicPath'];?>" save="<?=is_file($c['root_path'].$news_row['PicPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="content.news_edit" />
		</form>
	<?php
	}elseif($c['manage']['do']=='category'){
		//文章分类
		echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.news_category_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
					<?php }?>
					<td width="21%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
					<td width="65%" nowrap="nowrap">{/global.sub_category/}</td>
					<td width="5%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach((array)$all_cate_ary['0,'] as $v){
					$Name=$v['Category'.$c['manage']['web_lang']];
				?>
					<tr>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /></td>
						<?php }?>
						<td><?=$Name;?></td>
						<td class="attr_list">
							<dl class="attr_box hide"></dl><?php /* 不要删掉 是用来处理兼容的 */?>
							<?php
							foreach((array)$all_cate_ary["{$v['UId']}{$v['CateId']},"] as $vv){
								if($Keyword && stripos($vv['Category'.$c['manage']['web_lang']], $Keyword)===false) continue;
							?>
								<dl class="attr_box" cateid="<?=$vv['CateId'];?>">
									<dd class="attr_ico"></dd>
									<dd class="attr_txt"><?=$vv['Category'.$c['manage']['web_lang']];?></dd>
									<?php if($permit_ary['edit'] || $permit_ary['del']){?>
										<dd class="attr_menu">
											<?php if($permit_ary['edit']){?><a class="edit" href="./?m=content&a=news&d=category_edit&CateId=<?=$vv['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
											<?php if($permit_ary['del']){?><a class="del" href="./?do_action=content.news_category_del&CateId=<?=$vv['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
										</dd>
									<?php }?>
								</dl>
							<?php }?>
							<?php if($permit_ary['add']){?><div class="attr_add"><a class="add" href="./?m=content&a=news&d=category_edit">+</a></div><?php }?>
						</td>
						<td nowrap="nowrap"<?=$permit_ary['edit']?' class="myorder_select" data-num="'.$v['MyOrder'].'"':'';?>><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="./?m=content&a=news&d=category_edit&CateId=<?=$v['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=content.news_category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
		<?php /***************************** 文章分类编辑 Start *****************************/?>
		<div class="pop_form box_news_edit">
			<form id="edit_form" class="w_750">
				<div class="t"><h1></h1><h2>×</h2></div>
				<div class="r_con_form"></div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<?php /***************************** 文章分类编辑 End *****************************/?>
	<?php
	}elseif($c['manage']['do']=='category_edit'){
		//文章分类编辑
	?>
		<div class="title hide"><?=$CateId?'{/global.edit/}':'{/global.add/}';?>{/news.classify/}</div>
		<div class="rows">
			<label>{/global.category/}{/global.name/}</label>
			<span class="input"><?=manage::form_edit($category_row, 'text', 'Category', 35, 50, 'notnull');?></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/news.news_category.children/}</label>
			<span class="input">
				<?php
				$now_dept=$category_row['Dept']+2-(db::get_max('info_category', "UId like '{$category_row['UId']}{$category_row['CateId']},%'", 'Dept'));
				$ext_where="CateId!='{$category_row['CateId']}' and Dept<".($category_row['SubCateCount']?$now_dept:2);
				echo category::ouput_Category_to_Select('UnderTheCateId', ($ParentId?$ParentId:category::get_CateId_by_UId($category_row['UId'])), 'info_category', "UId='0,' and $ext_where", $ext_where, '', '{/global.select_index/}');
				?>
			</span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="CateId" value="<?=$CateId;?>" />
		<input type="hidden" name="do_action" value="content.news_category_edit" />
	<?php }?>
</div>