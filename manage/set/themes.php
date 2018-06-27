<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'themes'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“风格”页面
	$c['manage']['do']='products_list';
}

echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<div class="r_nav">
	<h1>{/module.set.themes.module_name/}</h1>
	<?php
	if($c['manage']['do']=='nav' && $c['manage']['page']=='index'){
		$permit_ary=array(
			'add'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'nav', 'p'=>'add')),
			'edit'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'nav', 'p'=>'edit')),
			'del'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'nav', 'p'=>'del'))
		);
	?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php
	}elseif($c['manage']['do']=='footer_nav' && $c['manage']['page']=='index'){
		$permit_ary=array(
			'add'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'footer_nav', 'p'=>'add')),
			'edit'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'footer_nav', 'p'=>'edit')),
			'del'	=>	manage::check_permit('set', 0, array('a'=>'themes', 'd'=>'footer_nav', 'p'=>'del'))
		);
	?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['set']['themes']['menu'] as $k=>$v){
			if(!manage::check_permit('set', 0, array('a'=>'themes', 'd'=>$v))){
				if($v=='themes' && $c['manage']['do']=='themes') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
		?>
		<dt></dt>
		<dd><a href="./?m=set&a=themes&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.set.themes.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=set&a=themes&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="themes" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='products_list'){
		//列表管理
		$list_row=db::get_value('config_module', "Themes='{$c['manage']['web_themes']}'", 'ListData');
		$list_data=str::json_data($list_row, 'decode');
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.themes_products_list_edit_init()});</script>
		<div id="themes_products_list">
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd">{/module.set.themes.products_list/}</h3>
				<div class="rows">
					<label>{/themes.products_list.left_right/}</label>
					<span class="input">
						<div class="switchery<?=$list_data['IsColumn']?' checked':'';?>">
							<input type="checkbox" name="IsColumn" value="1"<?=$list_data['IsColumn']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/themes.products_list.column_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/themes.products_list.screen/}</label>
					<span class="input narrow_list">
						<span class="choice_btn<?=$list_data['Narrow']==0?' current':'';?>"><b>{/global.close/}</b><input type="radio" name="Narrow" class="hide"<?=$list_data['Narrow']==0?' checked':'';?> value="0" /></span>
						<span class="choice_btn<?=$list_data['Narrow']==1?' current':'';?>"><b>{/themes.products_list.leftbar/}</b><input type="radio" name="Narrow" class="hide"<?=$list_data['Narrow']==1?' checked':'';?> value="1" /></span>
						<span class="choice_btn<?=$list_data['Narrow']==2?' current':'';?>"><b>{/themes.products_list.rightbar/}</b><input type="radio" name="Narrow" class="hide"<?=$list_data['Narrow']==2?' checked':'';?> value="2" /></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows" style="display:<?=$list_data['IsColumn']?'':'none';?>;">
					<label>{/themes.products_list.leftbar_box/}</label>
					<span class="input">
						<?php
						foreach((array)$list_data['IsLeftbar'] as $k=>$v){
						?>
							{/themes.left_bar.<?=$k;?>/} <div class="switchery<?=$v?' checked':'';?>">
								<input type="checkbox" name="Leftbar_<?=$k;?>" value="1"<?=$v?' checked':'';?> />
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						<?php }?>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/themes.products_list.list_rank/}</label>
					<span class="input order_list">
						<select name="Order">
							<?php
							$order_ary=array('row_1'=>'5,10,15', 'row_2'=>'10,20,30', 'row_3'=>'12,24,48', 'row_4'=>'20,40,60', 'row_5'=>'20,40,60', 'column'=>'20,40,60');
							foreach($order_ary as $k=>$v){
								if(!is_file("{$c['root_path']}static/themes/default/products/list/{$k}.php")) continue; //文件不存在
							?>
								<option value="<?=$k;?>" number="<?=$v;?>"<?=$list_data['Order']==$k?' selected':'';?>>{/themes.products_list.order.<?=$k;?>/}</option>
							<?php }?>
						</select>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/themes.products_list.list_count/}</label>
					<span class="input" number="<?=$list_data['OrderNumber'];?>"></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/themes.products_list.effects_order/}</label>
					<span class="input effects_list">
						<select name="Effects">
							<?php for($i=0; $i<7; ++$i){?>
								<option value="<?=$i;?>" number="<?=$i;?>"<?=$list_data['Effects']==$i?' selected':'';?>>{/themes.products_list.effects_ary.<?=$i;?>/}</option>
							<?php }?>
						</select>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /><input type="button" name="reset" class="btn_cancel" value="{/global.reset/}" data="list" /></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="do_action" value="set.themes_products_list_edit" />
			</form>
		</div>
	<?php
	}elseif($c['manage']['do']=='products_detail'){
		//描述管理
		$detail_row=db::get_one('config_module', "Themes='{$c['manage']['web_themes']}'", 'Themes, DetailData');
		$data_ary=str::json_data($detail_row['DetailData'], 'decode');
		//$nav_row=db::get_value('config', "GroupId='global' and Variable='ProDetail'", 'Value');
		$row=db::get_all('config', "GroupId='global' and (Variable='ProDetail' or Variable='Share')", 'Variable, Value');
		foreach($row as $v){
			$nav_data[$v['Variable']]=str::json_data($v['Value'], 'decode');
		}
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.themes_products_detail_edit_init()});</script>
		<div id="themes_products_detail">
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd">{/module.set.themes.themes/}</h3>
				<div class="themes">
					<?php
					$i=0;
					foreach((array)$data_ary as $k=>$v){
						if(!is_file("{$c['root_path']}static/themes/default/products/detail/{$k}.php")) continue; //文件不存在
						$img="/static/manage/images/set/module/s_detail_{$k}.jpg";
					?>
						<div class="item fl<?=$v?' current':'';?>" detail-id="<?=$k;?>">
							<div class="img"><img src="<?=$img;?>" title="{/sales.holiday.click/}<?=$i;?>" /><div class="img_mask"></div></div>
							<div class="info"><span><?=($i++)+1;?></span><div class="btn fr"><a class="view" href="<?=str_replace('s_', '', $img);?>" title="{/global.preview/}" target="_blank"><img src="/static/manage/images/sales/search.png" align="absmiddle" /></a></div></div>
						</div>
					<?php }?>
				</div>
				<div class="blank20"></div>
				
				<div class="rows_hd_blank"></div>
				<h3 class="rows_hd">{/module.set.module_name/}</h3>
				<div class="rows">
					<label>{/themes.products_detail.share/}</label>
					<span class="input" id="share_list">
						<?php
						$share_ary=array('facebook', 'google', 'twitter', 'vk', 'linkedin', 'googleplus', 'digg', 'reddit', 'stumbleupon', 'delicious', 'pinterest');
						$nav_data['Share'] && $share_ary=$nav_data['Share'];
						foreach((array)$share_ary as $k=>$v){
							echo '<div class="share_btn fl share_'.$v.'" title="'.$v.'"><input type="hidden" name="Share[]" value="'.$v.'" /></div>';
						}
						?>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/themes.products_detail.help/}</label>
					<span class="input">
						<div class="tab_box">
							<?=manage::html_tab_button('border');?>
							<div class="blank9"></div>
							<?php
							$len=count($nav_data['ProDetail']);
							foreach($c['manage']['config']['Language'] as $k=>$v){
							?>
								<div class="tab_txt tab_txt_<?=$k;?>">
									<?php
									if($len){
										foreach((array)$nav_data['ProDetail'] as $kk=>$vv){
									?>
										<div class="help_item">
											<span class="price_input not_input"><b>{/global.name/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Name_<?=$v;?>[]" value="<?=$vv["Name_{$v}"];?>" class="form_input input_name" size="18" maxlength="100" /></span>
											<span class="price_input not_input long"><b>{/themes.nav.url/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Url[]" value="<?=$vv["Url"];?>" class="form_input input_url" size="23" maxlength="200"<?=$k?' disabled':'';?> /></span>
											<b>{/themes.nav.target/}</b>
											<div class="switchery<?=$vv['NewTarget']?' checked':'';?>">
												<input type="checkbox" name="NewTarget[]" value="1"<?=$vv['NewTarget']?' checked':'';?><?=$k?' disabled':'';?>>
												<div class="switchery_toggler"></div>
												<div class="switchery_inner">
													<div class="switchery_state_on"></div>
													<div class="switchery_state_off"></div>
												</div>
											</div>
											<a href="javascript:;" class="btn_option del">-</a>
											<a href="javascript:;" class="btn_option add"<?=$kk<$len-1?' style="display:none;"':'';?>>+</a>
										</div>
									<?php
										}
									}else{
									?>
										<div class="help_item">
											<span class="price_input not_input" style="display:none;"><b>{/global.name/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Name_<?=$v;?>[]" value="<?=$vv["Name_{$v}"];?>" class="form_input input_name" size="18" maxlength="100" disabled /></span>
											<span class="price_input not_input long" style="display:none;"><b>{/themes.nav.url/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="Url[]" value="<?=$vv["Url"];?>" class="form_input input_url" size="23" maxlength="200"<?=$k?' disabled':'';?> disabled /></span>
											<b style="display:none;">{/themes.nav.target/}</b>
											<div class="switchery" style="display:none;">
												<input type="checkbox" name="NewTarget[]" value="1" disabled>
												<div class="switchery_toggler"></div>
												<div class="switchery_inner">
													<div class="switchery_state_on"></div>
													<div class="switchery_state_off"></div>
												</div>
											</div>
											<a href="javascript:;" class="btn_option del" style="display:none;">-</a>
											<a href="javascript:;" class="btn_option add">+</a>
										</div>
									<?php }?>
								</div>
							<?php }?>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="do_action" value="set.themes_products_detail_nav_edit" />
			</form>
		</div>
	<?php
	}elseif($c['manage']['do']=='nav'){
		//导航管理
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='NavData'", 'Value');
		if(!$nav_row){
			$nav_row=db::get_value('config_module', "IsDefault=1", 'NavData');
			db::get_row_count('config', "GroupId='themes' and Variable='NavData'")?db::update('config', "GroupId='themes' and Variable='NavData'", array('Value'=>addslashes(stripslashes($nav_row)))):db::insert('config', array('GroupId'=>'themes','Variable'=>'NavData','Value'=>addslashes(stripslashes($nav_row))));
		}
		$nav_data=str::json_data(htmlspecialchars_decode($nav_row), 'decode');
		$page_category=str::str_code(db::get_all('article_category', 'UId="0,"', '*', 'CateId asc'));
		//排序数组
		$my_order_ary=array();
		foreach((array)$nav_data as $k=>$v){
			$my_order_ary[$k+1]=$k+1;
		}
		//获取类别列表
		$PageCateAry=$InfoCateAry=$ProdCateAry=$category_ary=array();
		$page_cate_ary=str::str_code(db::get_all('article_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		$info_cate_ary=str::str_code(db::get_all('info_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		$products_cate_ary=str::str_code(db::get_all('products_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		foreach((array)$page_cate_ary as $v) $category_ary['Page'][$v['CateId']]=$v;
		foreach((array)$info_cate_ary as $v) $category_ary['Info'][$v['CateId']]=$v;
		foreach((array)$products_cate_ary as $v) $category_ary['Cate'][$v['CateId']]=$v;
		unset($art_cate_ary, $info_cate_ary, $products_cate_ary);
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.themes_nav_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" data="<?=htmlspecialchars($nav_row);?>">
			<thead>
				<tr>
					<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td>
					<?php if($permit_ary['edit']){?>
						<td width="4%" nowrap="nowrap">{/global.my_order/}</td>
					<?php }?>
					<td width="62%" nowrap="nowrap">{/global.title/}</td>
					<td width="10%" nowrap="nowrap">{/themes.nav.down/}</td>
					<td width="10%" nowrap="nowrap">{/themes.nav.target/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach((array)$nav_data as $k=>$v){
					$Name=(isset($v['Custom']) && $v['Custom'])?$v['Name'.$c['manage']['web_lang']]:$c['nav_cfg'][$v['Nav']]['name'.$c['manage']['web_lang']];
					if(isset($v['Page']) && $v['Page']){
						$Name=$category_ary['Page'][$v['Page']]['Category'.$c['manage']['web_lang']]." ({/page.page.page/})";
					}elseif(isset($v['Info']) && $v['Info']){
						$Name=$category_ary['Info'][$v['Info']]['Category'.$c['manage']['web_lang']]." ({/news.news.news/})";
					}elseif(isset($v['Cate']) && $v['Cate']){
						$Name=$category_ary['Cate'][$v['Cate']]['Category'.$c['manage']['web_lang']]." ({/products.product/})";
					}
					$id=$k+1;
				?>
					<tr data-id="<?=$k;?>">
						<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$k;?>" /></td>
						<?php if($permit_ary['edit']){?>
							<td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td>
						<?php }?>
						<td><?=$Name;?></td>
						<td nowrap="nowrap"><?=$v['Down']?"{/global.n_y.1/}":'';?></td>
						<td nowrap="nowrap"><?=$v['NewTarget']?"{/global.n_y.1/}":'';?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$id;?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.themes_nav_del&Type=nav&Id=<?=$id;?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 头部导航编辑 Start *****************************/?>
		<div class="pop_form box_nav_edit">
			<form id="edit_form" class="themes_nav_form">
				<div class="t"><h1><span></span>{/module.set.themes.nav/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.title/}</label>
						<span class="input">
							<select name="Nav">
								<option value="-1" down="0">{/themes.nav.custom/}</option>
								<?php
								foreach($c['nav_cfg'] as $k=>$v){
									if($v['FunVersion'] && $c['FunVersion']<$v['FunVersion']) continue;
								?>
									<option value="<?=$k;?>" down="<?=$v['down'];?>"><?=$v['name'.$c['manage']['web_lang']];?></option>
								<?php }?>
							</select>
							<div class="nav_oth">
								<select name="Page">
								<?php foreach($page_category as $k=>$v){?>
									<option value="<?=$v['CateId'];?>"><?=$v['Category'.$c['manage']['web_lang']];?></option>
								<?php }?>
								</select>
							</div>
							<div class="nav_oth"><?=category::ouput_Category_to_Select('Info', '', 'info_category', 'UId="0,"', 'Dept<=2', '', $c['nav_cfg'][2]['name'.$c['manage']['web_lang']]);?></div>
							<div class="nav_oth"><?=category::ouput_Category_to_Select('Cate', '', 'products_category', 'UId="0,"', 'Dept<=2', '', $c['nav_cfg'][3]['name'.$c['manage']['web_lang']]);?></div>
							<div class="nav_oth"><?=manage::form_edit('', 'text', 'Name', 25, 100);?></div>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.url/}</label>
						<span class="input"><input name="Url" type="text" value="" class="form_input" size="45" maxlength="200" /></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.down/}</label>
						<span class="input"><select name="Down"><option value="0">{/global.n_y.0/}</option><?php if($c['nav_cfg'][$row['Nav']]['down']){?><option value="1">{/global.n_y.1/}</option><?php }?></select></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.down_width/}</label>
						<span class="input">
							<select name="DownWidth">
								<?php for($i=0; $i<3; ++$i){?>
									<option value="<?=$i;?>">{/themes.nav.down_width_ary.<?=$i;?>/}</option>
								<?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.target/}</label>
						<span class="input"><select name="NewTarget"><option value="0">{/global.n_y_ary.0/}</option><option value="1">{/global.n_y_ary.1/}</option></select></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" id="Id" name="Id" value="" />
					<input type="hidden" name="do_action" value="set.themes_nav_edit" />
					<input type="hidden" name="Type" value="nav" />
				</div>
				<div class="button">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" />
					<input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" />
				</div>
			</form>
		</div>
		<?php /***************************** 头部导航编辑 End *****************************/?>
	<?php
	}elseif($c['manage']['do']=='footer_nav'){
		//底部管理
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='FooterData'", 'Value');
		if(!$nav_row){
			$nav_row=db::get_value('config_module', "IsDefault=1", 'FooterData');
			db::get_row_count('config', "GroupId='themes' and Variable='FooterData'")?db::update('config', "GroupId='themes' and Variable='FooterData'", array('Value'=>addslashes(stripslashes($nav_row)))):db::insert('config', array('GroupId'=>'themes','Variable'=>'FooterData','Value'=>addslashes(stripslashes($nav_row))));
		}
		$nav_data=str::json_data(htmlspecialchars_decode($nav_row), 'decode');
		$page_category=str::str_code(db::get_all('article_category', 'UId="0,"', '*', 'CateId asc'));
		//排序数组
		$my_order_ary=array();
		foreach((array)$nav_data as $k=>$v){
			$my_order_ary[$k+1]=$k+1;
		}
		//获取类别列表
		$PageCateAry=$InfoCateAry=$ProdCateAry=$category_ary=array();
		$page_cate_ary=str::str_code(db::get_all('article_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		$info_cate_ary=str::str_code(db::get_all('info_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		$products_cate_ary=str::str_code(db::get_all('products_category', '1', 'CateId, Category'.$c['manage']['web_lang']));
		foreach((array)$page_cate_ary as $v) $category_ary['Page'][$v['CateId']]=$v;
		foreach((array)$info_cate_ary as $v) $category_ary['Info'][$v['CateId']]=$v;
		foreach((array)$products_cate_ary as $v) $category_ary['Cate'][$v['CateId']]=$v;
		unset($art_cate_ary, $info_cate_ary, $products_cate_ary);
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.themes_footer_nav_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" data="<?=htmlspecialchars($nav_row);?>">
			<thead>
				<tr>
					<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td>
					<?php if($permit_ary['edit']){?>
						<td width="4%" nowrap="nowrap">{/global.my_order/}</td>
					<?php }?>
					<td width="62%" nowrap="nowrap">{/global.title/}</td>
					<td width="10%" nowrap="nowrap">{/themes.nav.target/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="5%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach((array)$nav_data as $k=>$v){
					$Name=(isset($v['Custom']) && $v['Custom'])?$v['Name'.$c['manage']['web_lang']]:$c['nav_cfg'][$v['Nav']]['name'.$c['manage']['web_lang']];
					if(isset($v['Page']) && $v['Page']){
						$Name=$category_ary['Page'][$v['Page']]['Category'.$c['manage']['web_lang']]." ({/page.page.page/})";
					}elseif(isset($v['Info']) && $v['Info']){
						$Name=$category_ary['Info'][$v['Info']]['Category'.$c['manage']['web_lang']]." ({/news.news.news/})";
					}elseif(isset($v['Cate']) && $v['Cate']){
						$Name=$category_ary['Cate'][$v['Cate']]['Category'.$c['manage']['web_lang']]." ({/products.product/})";
					}
					$id=$k+1;
				?>
					<tr data-id="<?=$k;?>">
						<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$k;?>" /></td>
						<?php if($permit_ary['edit']){?>
							<td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td>
						<?php }?>
						<td><?=$Name;?></td>
						<td nowrap="nowrap"><?=$v['NewTarget']?"{/global.n_y.1/}":'';?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$id;?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.themes_nav_del&Type=footer_nav&Id=<?=$id;?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 底部导航编辑 Start *****************************/?>
		<div class="pop_form box_nav_edit">
			<form id="edit_form" class="themes_nav_form">
				<div class="t"><h1><span></span>{/module.set.themes.footer_nav/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.title/}</label>
						<span class="input">
							<select name="Nav">
								<option value="-1" down="0">{/themes.nav.custom/}</option>
								<?php
								foreach($c['nav_cfg'] as $k=>$v){
									if($v['FunVersion'] && $c['FunVersion']<$v['FunVersion']) continue;
								?>
									<option value="<?=$k;?>" down="<?=$v['down'];?>"><?=$v['name'.$c['manage']['web_lang']];?></option>
								<?php }?>
							</select>
							<div class="nav_oth">
								<select name="Page">
								<?php foreach($page_category as $k=>$v){?>
									<option value="<?=$v['CateId'];?>"><?=$v['Category'.$c['manage']['web_lang']];?></option>
								<?php }?>
								</select>
							</div>
							<div class="nav_oth"><?=category::ouput_Category_to_Select('Info', '', 'info_category', 'UId="0,"', 'Dept<=2', '', $c['nav_cfg'][2]['name'.$c['manage']['web_lang']]);?></div>
							<div class="nav_oth"><?=category::ouput_Category_to_Select('Cate', '', 'products_category', 'UId="0,"', 'Dept<=2', '', $c['nav_cfg'][3]['name'.$c['manage']['web_lang']]);?></div>
							<div class="nav_oth"><?=manage::form_edit('', 'text', 'Name', 25, 100);?></div>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.url/}</label>
						<span class="input"><input name="Url" type="text" value="" class="form_input" size="45" maxlength="200" /></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.down_width/}</label>
						<span class="input">
							<select name="DownWidth">
								<?php for($i=0; $i<3; ++$i){?>
								<option value="<?=$i;?>">{/themes.nav.down_width_ary.<?=$i;?>/}</option>
								<?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/themes.nav.target/}</label>
						<span class="input"><select name="NewTarget"><option value="0">{/global.n_y_ary.0/}</option><option value="1" >{/global.n_y_ary.1/}</option></select></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" id="Id" name="Id" value="" />
					<input type="hidden" name="do_action" value="set.themes_nav_edit" />
					<input type="hidden" name="Type" value="footer_nav" />
				</div>
				<div class="button">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" />
					<input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" />
				</div>
			</form>
		</div>
		<?php /***************************** 底部导航编辑 End *****************************/?>
	<?php
	}elseif($c['manage']['do']=='style'){
		//色调管理
		echo ly200::load_static('/static/js/plugin/jscolor/jscolor.js');
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.themes_style_edit_init()});</script>
		<div id="themes_style">
			<form id="edit_form" class="r_con_form" theme="<?=$c['manage']['web_themes'];?>">
				<?php
				$style_row=db::get_value('config_module', "Themes='{$c['manage']['web_themes']}'", 'StyleData');
				$data_ary=str::json_data($style_row, 'decode');
				echo ly200::set_custom_style();
				include("{$c['root_path']}/static/themes/{$c['manage']['web_themes']}/inc/themes_style.php");
				?>
				<div class="rows btn_box">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /><input type="button" name="reset" class="btn_cancel" value="{/global.reset/}" data="style" /></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="do_action" value="set.themes_style_edit" />
			</form>
		</div>
	<?php }?>
</div>