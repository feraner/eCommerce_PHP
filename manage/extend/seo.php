<?php !isset($c) && exit();?>
<?php
manage::check_permit('extend', 1, array('a'=>'seo'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“收件箱”页面
	$c['manage']['do']='meta';
}
if($c['manage']['do']=='meta' || $c['manage']['do']=='meta_list' || $c['manage']['do']=='meta_edit'){
	$meta_ary=array(
		'home'				=>	1,
		'products'			=>	array('Name', 'ProId'),
		'products_category'	=>	array('Category', 'CateId'),
		'tuan'				=>	1,
		'seckill'			=>	1,
		'new'				=>	1,
		'hot'				=>	1,
		'best_deals'		=>	1,
		'special_offer'		=>	1,
		'info'				=>	array('Title', 'InfoId'),
		'info_category'		=>	array('Category', 'CateId'),
		'article'			=>	array('Title', 'AId'),
		'blog'				=>	1
	);
	$alone_ary=array('home', 'tuan', 'seckill', 'new', 'hot', 'best_deals', 'special_offer', 'blog');
}

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['extend']['seo']['menu'] as $k=>$v){
	if(!manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>$v))){
		if($v=='meta' && $c['manage']['do']=='meta') $out=1;
	}else{
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=extend&a=seo&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

$permit_ary=array(
	'meta_edit'		=>	manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'meta', 'p'=>'edit')),
	'third_add'		=>	manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third', 'p'=>'add')),
	'third_edit'	=>	manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third', 'p'=>'edit')),
	'third_del'		=>	manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third', 'p'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.extend.seo.module_name/}</h1>
	<div class="turn_page"></div>
		<?php if($c['manage']['do']=='third'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="clear"></div>
				<input type="hidden" name="m" value="extend" />
				<input type="hidden" name="a" value="seo" />
				<input type="hidden" name="d" value="third" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['third_add']){?><li><a class="tip_ico_down add" href="./?m=extend&a=seo&d=third_edit" label="{/global.add/}" data-id="0"></a></li><?php }?>
			<?php if($permit_ary['third_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
		<?php }?>
	<dl class="edit_form_part">
		<?php if(manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'meta'))){?>
			<dt></dt>
			<dd><a href="./?m=extend&a=seo&d=meta"<?=($c['manage']['do']=='meta' || $c['manage']['do']=='meta_list' || $c['manage']['do']=='meta_edit')?' class="current"':'';?>>{/module.extend.seo.meta/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third'))){?>
			<dt></dt>
			<dd><a href="./?m=extend&a=seo&d=third"<?=($c['manage']['do']=='third' || $c['manage']['do']=='third_edit')?' class="current"':'';?>>{/module.extend.seo.third/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'sitemap'))){?>
			<dt></dt>
			<dd><a href="./?m=extend&a=seo&d=sitemap"<?=($c['manage']['do']=='sitemap')?' class="current"':'';?>>{/module.extend.seo.sitemap/}</a></dd>
		<?php }?>
	</dl>
</div>
<div id="seo" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='meta'){
		//Meta标签列表
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.seo_meta_init()});</script>
	    <?php
		foreach($meta_ary as $k=>$v){
			if($c['FunVersion']<2 && in_array($k, array('seckill', 'tuan', 'blog'))) continue;//高级版以下踢走
			if($v==1){
				$row=str::str_code(db::get_one('meta', "Type='$k'"));
				$row['Column']='MId';
		?>
                <div class="meta_box">
                    <div class="meta_head">
                        <div class="meta_title">{/seo.meta.<?=$k;?>/}</div>
                        <div class="meta_more"><i></i></div>
                    </div>
                    <div class="meta_body">
                        <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
                            <thead>
                                <tr>
                                    <td width="30%" nowrap="nowrap">{/global.title/}</td>
                                    <td width="30%" nowrap="nowrap">{/global.keyword/}</td>
                                    <td width="26%" nowrap="nowrap">{/global.depict/}</td>
                                    <?php if($permit_ary['meta_edit']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data="<?=htmlspecialchars(str::json_data($row));?>">
                                    <td nowrap="nowrap"><?=str::str_echo($row['SeoTitle'.$c['manage']['web_lang']], 60, 0, '...');?></td>
                                    <td nowrap="nowrap"><?=str::str_echo($row['SeoKeyword'.$c['manage']['web_lang']], 60, 0, '...');?></td>
                                    <td nowrap="nowrap"><?=str::str_echo($row['SeoDescription'.$c['manage']['web_lang']], 60, 0, '...');?></td>
                                    <?php if($permit_ary['meta_edit']){?><td nowrap="nowrap"><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-type="<?=$k;?>" data-id="<?=$row['MId'];?>" data-title="{/seo.meta.<?=$k;?>/}"><img src="/static/ico/edit.png" align="absmiddle" /></a></td><?php }?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
			<?php }else{?>
                <div class="meta_box">
                    <div class="meta_head">
                        <div class="meta_title">{/seo.meta.<?=$k;?>/}</div>
                        <div class="meta_more"><i></i></div>
						<a class="more" href="./?m=extend&a=seo&d=meta_list&Type=<?=$k;?>"></a>
                    </div>
                </div>
		<?php
			}
		}?>
	<?php
	}elseif($c['manage']['do']=='meta_list'){
		//Meta标签分类列表
		$Type=$_GET['Type'];
		$CateId=(int)$_GET['CateId'];
		$page=(int)$_GET['page']?(int)$_GET['page']:1;
		$page_count=30;//显示数量
		$is_category=($Type=='info_category' || $Type=='products_category');
		$where='1';
		if($is_category){
			if($CateId){
				$category_one=str::str_code(db::get_one($Type, "CateId='$CateId'"));
				$UId=$category_one['UId'];
				$where.=" and UId='{$UId}{$CateId},'";
			}else{
				$where.=" and UId='0,'";
			}
		}
		if($Type=='products'){
			$pro_row=str::str_code(db::get_limit_page("$Type", $where, "ProId,Name{$c['manage']['web_lang']}", "ProId desc", $page, $page_count));
			$ary=array();
			$proid_list="-1";
			foreach($pro_row[0] as $v){
				$proid_list.=",{$v['ProId']}";
				$ary[$v['ProId']]=$v["Name{$c['manage']['web_lang']}"];
			}
			$seo_row=str::str_code(db::get_all('products_seo', "ProId in($proid_list)", '*', 'ProId desc'));
			foreach($seo_row as $k=>$v){
				$seo_row[$k]["Name{$c['manage']['web_lang']}"]=$ary[$v['ProId']];
			}
			$meta_row=array(
				$seo_row,
				$pro_row[1],
				$pro_row[2],
				$pro_row[3],
				$pro_row[4]
			);
		}else{
			$meta_row=str::str_code(db::get_limit_page($Type, $where, '*', "{$meta_ary[$Type][1]} desc", (int)$_GET['page'], $page_count));
		}
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.seo_meta_init()});</script>
		<div class="list_hd">
			<a href="./?m=extend&a=seo&d=meta">{/module.extend.seo.meta/}</a> &gt; 
            <a href="./?m=extend&a=seo&d=meta_list&Type=<?=$Type;?>">{/seo.meta.<?=$Type;?>/}</a> &gt; 
            <?php
            if($CateId){
                $str='';
                $uid=trim($UId, ',');
                if($uid=='0'){
                    $str.="<a href='./?m=extend&a=seo.meta&d=list&Type={$Type}&CateId={$CateId}'>".$category_one['Category'.$c['manage']['web_lang']]."</a>";
                }else{
                    $all_row=str::str_code(db::get_all($Type, "CateId in($uid)", '*', $c['my_order'].'Dept asc'));	
                    $i=0;
                    foreach((array)$all_row as $v){
                        $str.="<a href='./?m=extend&a=seo.meta&d=list&Type={$Type}&CateId={$v['CateId']}'>".$v['Category'.$c['manage']['web_lang']]."</a> &gt; ";
                        $i+=1;
                    }
                    $str.="<a href='./?m=extend&a=seo.meta&d=list&Type={$Type}&CateId={$CateId}'>".$category_one['Category'.$c['manage']['web_lang']]."</a>";
                }
                echo $str;
            }?>
        </div>
        <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
            <thead>
                <tr>
                    <td width="20%" nowrap="nowrap">{/seo.page/}</td>
                    <td width="20%" nowrap="nowrap">{/global.title/}</td>
                    <td width="20%" nowrap="nowrap">{/global.keyword/}</td>
                    <td width="30%" nowrap="nowrap">{/global.depict/}</td>
                    <?php if($permit_ary['meta_edit']){?><td width="6%" nowrap="nowrap">{/global.operation/}</td><?php }?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($meta_row[0] as $v){
					$v['Column']=$meta_ary[$Type][1];
                ?>
                    <tr data="<?=htmlspecialchars(str::json_data($v));?>">
                        <td><?=($is_category && $v['SubCateCount'])?"<a href='./?m=extend&a=seo&d=meta_list&Type={$Type}&CateId={$v[$meta_ary[$Type][1]]}'>{$v[$meta_ary[$Type][0].$c['manage']['web_lang']]}</a>":$v[$meta_ary[$Type][0].$c['manage']['web_lang']];?></td>
                        <td><?=$v['SeoTitle'.$c['manage']['web_lang']];?></td>
                        <td><?=$v['SeoKeyword'.$c['manage']['web_lang']];?></td>
                        <td><?=$v['SeoDescription'.$c['manage']['web_lang']];?></td>
                        <?php if($permit_ary['meta_edit']){?><td><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-type="<?=$Type;?>" data-id="<?=$v[$meta_ary[$Type][1]];?>"><img src="/static/ico/edit.png" align="absmiddle" /></a></td><?php }?>
                    </tr>
                <?php }?>
            </tbody>
        </table>
		<div id="turn_page"><?=manage::turn_page($meta_row[1], $meta_row[2], $meta_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
	<?php
	}elseif($c['manage']['do']=='third'){
		//第三方代码列表
		echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.seo_third_init()});</script>
        <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
            <thead>
                <tr>
                    <?php if($permit_ary['third_del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<?php if($permit_ary['third_edit']){?><td width="4%" nowrap="nowrap">{/global.my_order/}</td><?php }?>
                    <td width="32%" nowrap="nowrap">{/global.title/}</td>
                    <td width="15%" nowrap="nowrap">{/seo.third.code_type/}</td>
                    <td width="10%" nowrap="nowrap">{/seo.third.is_meta/}</td>
                    <td width="10%" nowrap="nowrap">{/global.used/}</td>
                    <td width="15%" nowrap="nowrap">{/global.time/}</td>
                    <?php if($permit_ary['third_edit'] || $permit_ary['third_del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
                </tr>
            </thead>
            <tbody>
                <?php
                $Keyword=str::str_code($_GET['Keyword']);
                $where='1';//条件
                $Keyword && $where.=" and Title like '%$Keyword%'";
                $third_row=str::str_code(db::get_all('third', $where, '*', $c['my_order'].'TId desc'));
                foreach($third_row as $v){
                ?>
                    <tr tid="<?=$v['TId'];?>">
                        <?php if($permit_ary['third_del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['TId'];?>" class="va_m" /></td><?php }?>
						<?php if($permit_ary['third_edit']){?><td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td><?php }?>
                        <td nowrap="nowrap"><?=$v['Title'];?></td>
                        <td><?=$c['manage']['lang_pack']['seo']['third']['code_type_ary'][$v['CodeType']];?></td>
                        <td nowrap="nowrap"><?=$v['IsMeta']?"{/global.n_y.1/}":"{/global.n_y.0/}";?></td>
                        <td nowrap="nowrap"><?=$v['IsUsed']?"{/global.n_y.1/}":"{/global.n_y.0/}";?></td>
                        <td nowrap="nowrap"><?=date('Y-m-d', $v['AccTime']);?></td>
						<?php if($permit_ary['third_edit'] || $permit_ary['third_del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['third_edit']){?><a class="tip_ico tip_min_ico" href="./?m=extend&a=seo&d=third_edit&TId=<?=$v['TId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['third_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=extend.seo_third_del&TId=<?=$v['TId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
                    </tr>
                <?php }?>
            </tbody>
        </table>
	<?php
	}elseif($c['manage']['do']=='third_edit'){
		//第三方代码编辑
		$TId=(int)$_GET['TId'];
		$third_row=str::str_code(db::get_one('third', "TId='$TId'"));
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.seo_third_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
            <div class="rows">
                <label>{/global.title/}</label>
                <span class="input"><input name="Title" value="<?=$third_row['Title'];?>" type="text" class="form_input" size="53" maxlength="100" notnull /></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/seo.code/}</label>
                <span class="input"><textarea name='Code' notnull><?=$third_row['Code'];?></textarea></span>
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/seo.third.code_type/}:</label>
                <span class="input"><?=ly200::form_select($c['manage']['lang_pack']['seo']['third']['code_type_ary'], 'CodeType', (int)$third_row['CodeType']);?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/global.used/}</label>
                <span class="input">
                    <div class="switchery<?=$third_row['IsUsed']==1?' checked':'';?>">
                        <input type="checkbox" name="IsUsed" value="1"<?=$third_row['IsUsed']==1?' checked':'';?>>
                        <div class="switchery_toggler"></div>
                        <div class="switchery_inner">
                            <div class="switchery_state_on"></div>
                            <div class="switchery_state_off"></div>
                        </div>
                    </div>
                    <span class="tool_tips_ico" content="{/seo.third.used_notes/}"></span>
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/seo.third.is_meta/}</label>
                <span class="input">
                    <div class="switchery<?=$third_row['IsMeta']==1?' checked':'';?>">
                        <input type="checkbox" name="IsMeta" value="1"<?=$third_row['IsMeta']==1?' checked':'';?>>
                        <div class="switchery_toggler"></div>
                        <div class="switchery_inner">
                            <div class="switchery_state_on"></div>
                            <div class="switchery_state_off"></div>
                        </div>
                    </div>
                    <span class="tool_tips_ico" content="{/seo.third.meta_notes/}"></span>
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label></label>
                <span class="input">
					<?php if(($TId && manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third', 'p'=>'edit'))) || (!$TId && manage::check_permit('extend', 0, array('a'=>'seo', 'd'=>'third', 'p'=>'add')))){?>
                    	<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<?php }?>
                    <a href="./?m=extend&a=seo&d=third" class="btn_cancel">{/global.return/}</a>
                </span>
                <div class="clear"></div>
            </div>
			<input type="hidden" id="TId" name="TId" value="<?=$TId;?>" />
			<input type="hidden" name="do_action" value="extend.seo_third_edit" />
		</form>
	<?php
	}elseif($c['manage']['do']=='sitemap'){
		//网站地图
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.seo_sitemap_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd">{/module.extend.seo.sitemap/}</h3>
			<div class="rows">
				<label>{/seo.info.last_gTime/}</label>
				<span class="input"><?=date('Y-m-d H:i:s', db::get_value('config', "GroupId='sitemap' and Variable='AccTime'", 'Value'));?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/module.content.module_name/}</label>
				<span class="input"><textarea class="large" disabled readonly><?=@file_get_contents($c['root_path'].'/sitemap.xml');?></textarea></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/seo.info.generate/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="extend.seo_sitemap_create">
		</form>
	<?php }?>
	
	<?php /***************************** SEO编辑 Start *****************************/?>
	<?php
	/*$Type=$_GET['Type'];
	$MId=(int)$_GET['MId'];
	if($meta_ary[$Type]==1){
		$seo_row=$meta_row=str::str_code(db::get_one('meta', "MId='$MId'"));
	}else{
		$seo_row=$meta_row=str::str_code(db::get_one($Type, "{$meta_ary[$Type][1]}='$MId'"));
		if($Type=='products'){
			$seo_row=str::str_code(db::get_one('products_seo', "{$meta_ary[$Type][1]}='$MId'"));
		}
	}*/
	?>
	<?php if($c['manage']['do']=='meta' || $c['manage']['do']=='meta_list'){?>
		<div class="pop_form box_seo_edit">
			<form id="edit_form" class="w_750">
				<div class="t"><h1>{/global.edit/}{/seo.info.meta/} (<span>{/seo.meta.<?=$Type;?>/}</span>)</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.seo/}</label>
						<span class="input tab_box">
							<?=manage::html_tab_button('border');?>
							<div class="blank9"></div>
							<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
								<div class="tab_txt tab_txt_<?=$k;?>">
									<span class="price_input lang_input"><b>{/news.news.seo_title/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoTitle_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($seo_row["SeoTitle_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="150" /></span>
									<div class="blank9"></div>
									<span class="price_input lang_input"><b>{/news.news.seo_keyword/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SeoKeyword_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($seo_row["SeoKeyword_{$v}"]), ENT_QUOTES);?>" class="form_input" size="35" maxlength="255" /></span>
									<div class="blank9"></div>
									<span class='price_input lang_input price_textarea'><b>{/news.news.seo_brief/}<div class='arrow'><em></em><i></i></div></b><textarea name='SeoDescription_<?=$v;?>'><?=$seo_row["SeoDescription_{$v}"];?></textarea></span>
								</div>
							<?php }?>
						</span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="MId" value="" />
					<input type="hidden" name="Type" value="" />
					<input type="hidden" name="" value="" class="hide_name" />
					<input type="hidden" name="do_action" value="extend.seo_meta_edit" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
	<?php }?>
	<?php /***************************** SEO编辑 End *****************************/?>
</div>