<?php !isset($c) && exit();?>
<?php
manage::check_permit('extend', 1, array('a'=>'blog'));//检查权限

$Keyword=$_GET['Keyword'];
if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“风格”页面
	$c['manage']['do']='set';
}
if($c['manage']['do']=='blog' || $c['manage']['do']=='category'){
	$cate_ary=str::str_code(db::get_all('blog_category','1','*'));
	$category_ary=array();
	foreach((array)$cate_ary as $v){
		$category_ary[$v['CateId']]=$v;
	}
	$category_count=count($category_ary);
	unset($cate_ary);
	
	$CateId=(int)$_GET['CateId'];
	if($CateId){
		$category_row=str::str_code(db::get_one('blog_category', "CateId='$CateId'"));
		!$category_row && js::location('./?m=extend&a=blog&d=blog');
		$UId=$category_row['UId'];
		$UId!='0,' && $TopCateId=category::get_top_CateId_by_UId($UId);
		$column=$category_row['Category'.$c['manage']['web_lang']];
	}
}

$permit_ary=array(
	'blog_add'		=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'blog', 'p'=>'add')),
	'blog_edit'		=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'blog', 'p'=>'edit')),
	'blog_del'		=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'blog', 'p'=>'del')),
	'category_add'	=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'category', 'p'=>'add')),
	'category_edit'	=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'category', 'p'=>'edit')),
	'category_del'	=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'category', 'p'=>'del')),
	'review_edit'	=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'review', 'p'=>'edit')),
	'review_del'	=>	manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>'review', 'p'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.extend.blog.module_name/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']!='set'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<?php if($c['manage']['do']=='blog'){?>
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'blog_category', 'UId="0,"');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<?php }?>
				<div class="clear"></div>
				<input type="hidden" name="m" value="extend" />
				<input type="hidden" name="a" value="blog" />
				<input type="hidden" name="d" value="<?=$c['manage']['do'];?>" />
				<input type="hidden" name="p" value="<?=$c['manage']['page'];?>" />
			</form>
		</div>
		<ul class="ico">
			<?php if($c['manage']['do']=='blog' || $c['manage']['do']=='category'){?>
				<?php if($permit_ary[$c['manage']['do'].'_add']){?><li><a class="tip_ico_down add" href="./?m=extend&a=blog&d=<?=$c['manage']['do'];?>&p=edit" label="{/global.add/}"></a></li><?php }?>
			<?php }?>
			<?php if($permit_ary[$c['manage']['do'].'_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['extend']['blog']['menu'] as $k=>$v){
			if(!manage::check_permit('extend', 0, array('a'=>'blog', 'd'=>$v))){
				if($v=='set' && $c['manage']['do']=='set') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
		?>
		<dt></dt>
		<dd><a href="./?m=extend&a=blog&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.extend.blog.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=extend&a=blog&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="blog" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='set'){
		//博客设置
		$set_ary=array();
		$set_row=db::get_all('config', "GroupId='blog'");
		foreach($set_row as $v){
			$set_ary[$v['Variable']]=$v['Value'];
		}
	?>
		<script type="text/javascript">$(function(){extend_obj.blog_set_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/blog.blog.title/}</label>
				<span class="input"><textarea name="Title"><?=$set_ary['Title'];?></textarea></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/blog.blog.brief/}</label>
				<span class="input"><textarea name="BriefDescription"><?=$set_ary['BriefDescription'];?></textarea></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/blog.blog.nav/}</label>
				<span class="input">
					<div class="clean"><input type="button" class="btn_ok addNav" value="{/global.add/}"></div>
					<div class="blank6"></div>
					<div data-name="{/blog.blog.name/}" data-link="{/blog.blog.link/}" class="blog_nav">
						<?php
						$Nav=(array)str::json_data(htmlspecialchars_decode($set_ary['NavData']), 'decode');
						foreach($Nav as $k=>$v){
						?>
							<div>
								{/blog.blog.name/}:<input type="text" name="name[]" class="form_input" value="<?=$v[0];?>" size="10" maxlength="30" />
								{/blog.blog.link/}:<input type="text" name="link[]" class="form_input" value="<?=$v[1];?>" size="30" max="150" /><a href="javascript:void(0);"><img hspace="5" src="/static/ico/del.png"></a>
								<div class="blank6"></div>
							</div>
						<?php }?>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/blog.blog.ad/}</label>
				<span class="input upload_file upload_ad">
					<div class="img">
						<div id="AdDetail" class="upload_box preview_pic"><input type="button" id="AdUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
						{/blog.blog.adsize/}
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="Banner" value="<?=$set_ary['Banner'];?>" save="<?=is_file($c['root_path'].$set_ary['Banner'])?1:0;?>" />
			<input type="hidden" name="do_action" value="extend.blog_set" />
		</form>
	<?php
	}elseif($c['manage']['do']=='blog'){
		//博客管理
		if($c['manage']['page']=='index'){
			//博客列表
	?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_init()});</script>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<?php if($permit_ary['blog_del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td><?php }?>
						<td width="41%" nowrap="nowrap">{/global.title/}</td>
						<td width="41%" nowrap="nowrap">{/global.category/}{/global.subjection/}</td>
						<td width="9%" nowrap="nowrap">{/global.my_order/}</td>
						<?php if($permit_ary['blog_edit'] || $permit_ary['blog_del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
					</tr>
				</thead>
				<tbody>
					<?php
					$where='1';//条件
					$page_count=20;//显示数量
					$CateId && $where.=' and '.category::get_search_where_by_CateId($CateId, 'blog_category');
					$Keyword && $where.=" and Title like '%$Keyword%'";
					$blog_row=str::str_code(db::get_limit_page('blog', $where, '*', $c['my_order'].'AId desc', (int)$_GET['page'], $page_count));
					$i=1;
					foreach((array)$blog_row[0] as $v){
						$title=$v['Title'];
						$url=ly200::get_url($v, 'blog');
					?>
						<tr>
							<?php if($permit_ary['blog_del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['AId'];?>" /></td><?php }?>
							<td><a href="<?=$url;?>" title="<?=$title;?>" target="_blank"><?=$title;?></a><?=(int)$v['IsHot']?'&nbsp;&nbsp;<span class="fc_red">{/blog.blog.is_hot/}</span>':'';?></td>
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
							<td nowrap="nowrap"<?=$permit_ary['blog_edit']?' class="myorder_select" data-num="'.$v['MyOrder'].'"':'';?>><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
							<?php if($permit_ary['blog_edit'] || $permit_ary['blog_del']){?>
								<td nowrap="nowrap">
									<?php if($permit_ary['blog_edit']){?><a class="tip_ico tip_min_ico" href="./?m=extend&a=blog&d=blog&p=edit&AId=<?=$v['AId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
									<?php if($permit_ary['blog_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=extend.blog_del&AId=<?=$v['AId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
								</td>
							<?php }?>
						</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($blog_row[1], $blog_row[2], $blog_row[3], '?'.ly200::query_string('page').'&page=');?></div>
			<div id="myorder_select_hide" class="hide"><?=ly200::form_select($c['manage']['my_order'], "MyOrder[]", '');?></div>
	<?php
		}else{
			//博客编辑
			$AId=(int)$_GET['AId'];
			$blog_row=str::str_code(db::get_one('blog', "AId='$AId'"));
			$blog_content_row=str::str_code(db::get_one('blog_content', "AId='$AId'"));
	?>
			<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_edit_init()});</script>
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd"><?=$AId?'{/global.edit/}':'{/global.add/}';?>{/blog.blog.blog/}</h3>
				<div class="rows">
					<label>{/blog.title/}</label>
					<span class="input"><input name="Title" value="<?=$blog_row['Title'];?>" type="text" class="form_input" maxlength="150" size="53" notnull></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.classify/}</label>
					<span class="input">
						<select name="CateId" notnull="">
                            <option value="">--{/global.select_index/}--</option>
                            <?php
                            $blog_category_row = db::get_all('blog_category', '1', 'CateId, Category_en', $c['my_order'].'CateId asc');
                            foreach ($blog_category_row as $k=>$v){?>
                            <option value="<?=$v['CateId'];?>" <?=$blog_row['CateId']==$v['CateId']?'selected="selected"':'';?>><?=$v['Category_en']?></option>
                            <?php }?>
                        </select>
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
				<div class="rows">
					<label>{/blog.blog.author/}</label>
					<span class="input"><input name="Author" value="<?=$blog_row['Author'];?>" type="text" class="form_input" maxlength="150" size="53" notnull></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.seo_title/}</label>
					<span class="input"><input name="SeoTitle" value="<?=$blog_row['SeoTitle'];?>" type="text" class="form_input" maxlength="150" size="53" notnull></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.seo_keyword/}</label>
					<span class="input"><input name="SeoKeyword" value="<?=$blog_row['SeoKeyword'];?>" type="text" class="form_input" maxlength="150" size="53" notnull></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.seo_brief/}</label>
					<span class="input"><textarea name="SeoDescription"><?=$blog_row['SeoDescription'];?></textarea></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.brief/}</label>
					<span class="input"><textarea name="BriefDescription"><?=$blog_row['BriefDescription'];?></textarea></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.other/}</label>
					<span class="input"><input name="IsHot" value="1" type="checkbox" <?=$blog_row['IsHot']?'checked="checked"':'';?> /> {/blog.blog.is_hot/}</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.tag/}</label>
					<span class="input"><input name="Tag" value="<?=substr($blog_row['Tag'],1,-1);?>" type="text" class="form_input" maxlength="150" size="53"> <span class="tool_tips_ico" content="{/blog.blog.tips/}"></span></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.description/}</label>
					<span class="input"><?=manage::Editor('Content', $blog_content_row['Content']);?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=extend&a=blog&d=blog" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" id="AId" name="AId" value="<?=$AId;?>" />
                <input type="hidden" name="PicPath" value="<?=$blog_row['PicPath'];?>" save="<?=is_file($c['root_path'].$blog_row['PicPath'])?1:0;?>" />
				<input type="hidden" name="do_action" value="extend.blog_edit" />
			</form>
		<?php }?>
	<?php
	}elseif($c['manage']['do']=='category'){
		//博客分类
		if($c['manage']['page']=='index'){
			//博客分类列表
			echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_category_init()});</script>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<?php if($permit_ary['category_del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
						<?php if($permit_ary['category_edit']){?><td width="4%" nowrap="nowrap">{/global.my_order/}</td><?php }?>
						<td width="87%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
						<?php if($permit_ary['category_edit'] || $permit_ary['category_del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
					</tr>
				</thead>
				<tbody>
					<?php
					$where='1';//条件
					$Keyword && $where.=" and Category_en like '%$Keyword%'";
					$category_row=str::str_code(db::get_all('blog_category', $where, '*', $c['my_order'].'CateId desc'));
					$i=1;
					foreach($category_row as $v){
					?>
						<tr cateid="<?=$v['CateId'];?>">
							<?php if($permit_ary['category_del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /></td><?php }?>
							<?php if($permit_ary['category_edit']){?><td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td><?php }?>
							<td><?=$v['Category_en'];?></td>
							<?php if($permit_ary['category_edit'] || $permit_ary['category_del']){?>
								<td nowrap="nowrap">
									<?php if($permit_ary['category_edit']){?><a class="tip_ico tip_min_ico" href="./?m=extend&a=blog&d=category&p=edit&CateId=<?=$v['CateId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
									<?php if($permit_ary['category_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=extend.blog_category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
								</td>
							<?php }?>
						</tr>
					<?php }?>
				</tbody>
			</table>
		<?php
		}else{
			//博客分类编辑
		?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_category_edit_init()});</script>
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd"><?=$CateId?'{/global.edit/}':'{/global.add/}';?>{/blog.classify/}</h3>
				<div class="rows">
					<label>{/blog.title/}</label>
					<span class="input"><input name="Category_en" value="<?=$category_row['Category_en'];?>" type="text" class="form_input" maxlength="100" size="35" notnull> <font class="fc_red">*</font></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=extend&a=blog&d=category" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="CateId" value="<?=$CateId;?>" />
				<input type="hidden" name="do_action" value="extend.blog_category_edit">
			</form>
		<?php }?>
	<?php
	}elseif($c['manage']['do']=='review'){
		//博客评论
		if($c['manage']['page']=='index'){
			//博客评论列表
	?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_review_init()});</script>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<?php if($permit_ary['review_del']){?><td width="4%"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
						<td width="25%" nowrap="nowrap">{/blog.blog.title/}</td>
						<td width="18%" nowrap="nowrap">{/blog.blog.fullname/}</td>
						<td width="18%" nowrap="nowrap">{/blog.blog.email/}</td>
						<td width="5%" nowrap="nowrap">{/blog.review.is_reply/}</td>
						<td width="15%" nowrap="nowrap">{/global.time/}</td>
						<?php if($permit_ary['review_edit'] || $permit_ary['review_del']){?><td width="8%" class="last" nowrap="nowrap">{/global.operation/}</td><?php }?>
					</tr>
				</thead>
				<tbody>
					<?php
					$i=1;
					$page_count=20;
					$where='1';
					$Keyword && $where.=" and (Email like '%$Keyword%' or Name like '%$Keyword%')";
					if($Keyword){
						$blog_id_str = '(0';
						$blog_id_row=db::get_all('blog', "Title like '%$Keyword%'", 'AId');
						foreach((array)$blog_id_row as $k => $v){
							$blog_id_str.=','.$v['AId'];
						}
						$blog_id_str.=')';
						$where.="or AId in $blog_id_str";
					}
					$review_row=str::str_code(db::get_limit_page('blog_review', $where, '*', 'RId desc', (int)$_GET['page'], $page_count));
					foreach($review_row[0] as $v){
						$blog_row=db::get_one('blog', "AId='{$v['AId']}'");;
					?>
						<tr>
							<?php if($permit_ary['review_del']){?><td><input type="checkbox" name="select" value="<?=$v['RId'];?>" class="va_m" /></td><?php }?>
							<td><a href="<?=ly200::get_url($blog_row, 'blog')?>" target="_blank"><?=$blog_row['Title'];?></a></td>
							<td><?=$v['Name'];?></td>
							<td><?=$v['Email'];?></td>
							<td><?=$v['Reply']?'{/global.n_y.1/}':'{/global.n_y.0/}';?></td>
							<td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
							<?php if($permit_ary['review_edit'] || $permit_ary['review_del']){?>
								<td class="last">
									<?php if($permit_ary['review_edit']){?><a class="tip_ico tip_min_ico" href="./?m=extend&a=blog&d=review&p=view&RId=<?=$v['RId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
									<?php if($permit_ary['review_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=extend.blog_review_del&RId=<?=$v['RId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
								</td>
							<?php }?>
						</tr>
					<?php }?>
				</tbody>
			</table>
			<div id="turn_page"><?=manage::turn_page($review_row[1], $review_row[2], $review_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<?php
		}else{
			//博客评论编辑
			$RId=(int)$_GET['RId'];
			$review_row=str::str_code(db::get_one('blog_review', "RId='$RId'"));
			!$review_row && js::location('./?m=extend&a=blog&d=review');
			$blog_row=db::get_one('blog', "AId='{$review_row['AId']}'");
		?>
			<script type="text/javascript">$(document).ready(function(){extend_obj.blog_review_reply_init()});</script>
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd">{/global.view/}{/module.extend.blog.review/}</h3>
				<div class="rows">
					<label>{/blog.blog.title/}</label>
					<span class="input"><a href="<?=ly200::get_url($blog_row, 'blog')?>" target="_blank"><?=$blog_row['Title'];?></a></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.fullname/}</label>
					<span class="input"><?=$review_row['Name'];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.email/}</label>
					<span class="input"><?=$review_row['Email'];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/global.time/}</label>
					<span class="input"><?=date('Y-m-d H:i:s', $review_row['AccTime']);?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/blog.blog.content/}</label>
					<span class="input"><?=$review_row['Content'];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.review.reply/}</label>
					<span class="input">
						<textarea class="default" name="Reply"><?=$review_row['Reply'];?></textarea>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=extend&a=blog&d=review" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" id="RId" name="RId" value="<?=$RId;?>" />
				<input type="hidden" name="do_action" value="extend.blog_review_reply" />
			</form>
	<?php
		}
	}?>
</div>