<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'holiday'));//检查权限


foreach($c['manage']['lang_pack']['sales']['holiday']['holiday_ary'] as $v){
	$category_ary[$v]=$v;
}
$c['default_path']=$c['root_path']."/static/themes/default/";
?>
<script type="text/javascript">var lang_str_obj={'currency':'<?=$c['manage']['currency_symbol'];?>', 'now_time':'<?=date('Y-m-d H:i', $c['time']);?>'};</script>
<div class="r_nav">
	<h1>{/module.sales.holiday/}</h1>
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
						<label>{/sales.status/}</label>
						<span class="input"><?=ly200::form_select($category_ary, 'Category', '', '', '', '{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
				</div>
				<input type="hidden" name="m" value="sales" />
				<input type="hidden" name="a" value="holiday" />
			</form>
		</div>
	<?php }?>
</div>
<div id="holiday" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$Name=$_GET['Name'];
		$Category=$_GET['Category'];
		$where='1';//条件
		$page_count=12;//显示数量
		$Category && $where.=" and Category='$Category'";
		$holiday_row=str::str_code(db::get_limit_page('sales_holiday', $where, '*', 'HId asc', (int)$_GET['page'], $page_count));
	?>
		<script type="text/javascript">
		$(document).ready(function(){
			sales_obj.package_frame_init();
			sales_obj.holiday_list_init();
		});
		</script>
		<div class="list_bd list_box">
			<?php
			foreach($holiday_row[0] as $k=>$v){
				$theme=$v['Number'];
			?>
			<div class="item fl<?=$v['IsUsed']?' current':'';?>" hid="<?=$v['HId'];?>">
				<div class="img"><img src="<?="/static/themes/default/holiday/{$theme}/images/cover.jpg";?>" title="{/sales.holiday.click/}<?=$v['Title'];?>" /></div>
				<div class="info"><span><?=$v['Title'];?></span><div class="btn fr"><a class="view" href="?m=sales&a=holiday&d=edit&theme=<?=$theme;?>" title="{/global.preview/}"><img src="/static/manage/images/sales/search.png" align="absmiddle" /></a><a class="edit" href="?m=sales&a=holiday&d=edit&theme=<?=$theme;?>" title="{/global.edit/}"><img src="/static/manage/images/sales/edit.png" align="absmiddle" /></a></div></div>
			</div>
			<?php }?>
		</div>
		<div id="turn_page"><?=manage::turn_page($holiday_row[1], $holiday_row[2], $holiday_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
	<?php
	}elseif($c['manage']['do']=='edit'){
		$theme=$_GET['theme'];
		$holiday_row=str::str_code(db::get_one('sales_holiday', "Number='$theme'"));
		$current_lang = $_GET['lang'] ? trim($_GET['lang']) : $c['manage']['config']['LanguageDefault'];
		$current_lang_ext = '_'.($_GET['lang'] ? trim($_GET['lang']) : $c['manage']['config']['LanguageDefault']);		
		if(!is_file("{$c['default_path']}holiday/$theme/themes{$current_lang_ext}.json")){
			@copy("{$c['default_path']}holiday/$theme/themes.json", "{$c['default_path']}holiday/$theme/themes{$current_lang_ext}.json");
		}
	?>
	<?=ly200::load_static("/static/themes/default/holiday/{$theme}/manage/template.css", '/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');?>
	<script type="text/javascript">var web_template_data=<?php include("{$c['default_path']}holiday/{$theme}/themes{$current_lang_ext}.json");?>; $(document).ready(function(){sales_obj.holiday_frame_init(); sales_obj.holiday_edit_init();}); window.onresize=sales_obj.holiday_frame_init;</script>
    <div class="">
		<div class="m_lefter fl">
			<?php include("{$c['default_path']}holiday/{$theme}/manage/template.php");?>
		</div>
		<div class="m_righter fl">
			<form id="holiday_form" class="r_con_form">
				<div class="rows_hd">
					{/sales.holiday.model_set/} 
					<?php if(count($c['manage']['config']['Language']>1)){ ?>
					<span class="input lang_box">
						<?php foreach($c['manage']['config']['Language'] as $k=>$v){ ?>
							<span href="<?=ly200::query_string('page').'&lang='.$v; ?>" data-lang="<?=$v; ?>" class="choice_btn lang <?=$v==$current_lang ? 'current' : '' ; ?>"><b><?=$c['manage']['lang_pack']['language'][$v]; ?></b>
								<input type="radio" name="Lang" <?=$v==$current_lang ? 'checked' : '' ; ?> value="<?=$v; ?>" />
							</span>
						<?php } ?>
                        <div class="clear"></div>
                    </span>
                    <?php } ?>
				</div>
				<div id="set_banner">
					<div class="rows" value="images" style="display:none;">
						<label>{/global.pic/}1</label>
						<span class="input">
							<span class="upload_file">
								<div class="no_input" value="title_list" style="display:none;">{/global.title/}: <input name="TitleList[]" value="" type="text" class="form_input" size="30" maxlength="50"></div>
								<div class="no_input" value="url_list" style="display:none;">{/sales.holiday.link/}: <input name="UrlList[]" value="" type="text" class="form_input" size="30" maxlength="200"></div>
								<span class="upload_picture">
									<div class="img">
										<div id="PicDetail_0" class="upload_box preview_pic"><input type="button" name="PicUpload" id="PicUpload_0" class="btn_ok upload_btn" value="{/global.upload_pic/}" tips="" /></div>
									</div>
									<a href="javascript:;" label="{/global.edit/}" class="tip_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
									<a href="javascript:;" label="{/global.del/}" class="tip_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
								</span>
							</span>
						</span>
						<input type="hidden" name="ImgPath[]" value="" />
						<input type="hidden" name="ImgPathHide[]" value="" />
						<div class="clear"></div>
					</div>
				</div>
				<div id="set_config">
					<div class="rows" value="title" style="display:none;">
						<label>{/global.title/}</label>
						<span class="input"><input name="Title" value="" type="text" class="form_input" size="30" maxlength="50"></span>
						<div class="clear"></div>
					</div>
					<div class="rows" value="images" style="display:none;">
						<label>{/global.pic/}</label>
						<span class="input upload_file upload_pic">
							<div class="img">
								<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
							</div>
							<a href="javascript:;" label="{/global.edit/}" class="tip_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<a href="javascript:;" label="{/global.del/}" class="tip_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" value="url" style="display:none;">
						<label>{/sales.holiday.link/}</label>
						<span class="input"><input name="Url" value="" type="text" class="form_input" size="30" maxlength="200"></span>
						<div class="clear"></div>
					</div>
					<div class="rows" value="products" style="display:none;">
						<label>{/sales.holiday.products_list/}</label>
						<span class="input"><input type="button" id="products_btn" class="btn_ok" num="0" max="0" value="{/sales.holiday.products_list/}"></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok" name="submit_button" value="{/global.edit/}" />
						<a href="javascript:;" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="Theme" id="theme_hide" value="<?=$theme;?>" />
				<input type="hidden" name="Number" id="number_hide" value="0" />
				<input type="hidden" name="ContentsType" id="type_hide" value="0" />
				<input type="hidden" name="PicPath" value="" />
				<input type="hidden" name="PicPathHide" value="" />
				<input type="hidden" name="HideLang" value="<?=$current_lang; ?>" />
				<input type="hidden" name="do_action" value="sales.holiday_edit">
			</form>
			
			<div class="blank25"></div>
			<form id="holiday_set_form" class="r_con_form" style="display:none;">
				<div class="rows_hd">{/sales.holiday.base_set/}</div>
				<div class="rows" value="images" style="display:block;">
					<label>{/set.config.logo/}</label>
					<span class="input upload_file upload_logo">
						<div class="img">
							<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '210*75');?>" /></div>
							<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '210*75');?>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.edit/}" /></span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="Theme" value="<?=$theme;?>" />
				<input type="hidden" name="LogoPath" value="<?=$holiday_row['LogoPath'];?>" save="<?=is_file($c['root_path'].$holiday_row['LogoPath'])?1:0;?>" />
				<input type="hidden" name="do_action" value="sales.holiday_set_edit">
			</form>
		</div>
		<div class="clear"></div>
	</div>
	<?php
	}elseif($c['manage']['do']=='products'){
		$current_lang = $_GET['lang'] ? trim($_GET['lang']) : $c['manage']['config']['LanguageDefault'];
		$current_lang_ext = '_'.($_GET['lang'] ? trim($_GET['lang']) : $c['manage']['config']['LanguageDefault']);		
		
		$theme=$_GET['theme'];
		$number=(int)$_GET['num'];
		$max=(int)$_GET['max'];
		$holiday_row=db::get_value('sales_holiday', "Number='$theme'", 'ProId');
		$holiday_obj=str::json_data($holiday_row, 'decode');
		$proid_ary=$holiday_obj[$number];
		$remove_pid = $where_remove_pid = '';
		foreach((array)$proid_ary as $k=>$v){
			$remove_pid .= ($k?'':',').$v.',';
			$where_remove_pid .= ($k?',':'').$v;
		}
		$where='1';
		$page_count=12;//显示数量
		$Name=$_GET['Name'];
		$CateId=(int)$_GET['CateId'];
		$Name && $where.=" and (Name{$c['manage']['web_lang']} like '%$Name%' or concat(Prefix, Number) like '%$Name%')";
		if($CateId){
			$UId=category::get_UId_by_CateId($CateId);
			$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
		}
		$p_remove_pid = $_POST['remove_pid'] ? $_POST['remove_pid'] : $_GET['remove_pid'];
		trim($p_remove_pid,',') && $package_where_ary = @explode(',', substr($p_remove_pid, 1, -1));
		if($package_where_ary){	
			$remove_pid = $where_remove_pid = @implode(',', $package_where_ary);
			$remove_pid = ','.trim($remove_pid,',').',';
		}
		$where_remove_pid && $where.=" and ProId not in ({$where_remove_pid})";
		$products_row=str::str_code(db::get_limit_page('products', $where." and ((SoldOut=0 and IsSoldOut=0) or (IsSoldOut=1 and SStartTime<{$c['time']} and {$c['time']}<SEndTime))", '*', $c['my_order'].'ProId desc', (int)$_GET['page'], $page_count));
		$pro_ary=array();
		$h_pro_where = '1';
		$where_remove_pid && $h_pro_where.=" and ProId not in ({$h_pro_where})";
		$pro_row=str::str_code(db::get_all('products', $h_pro_where, "ProId, Name{$c['manage']['web_lang']}, Number, Price_0, Price_1, PicPath_0", 'ProId desc'));
		foreach($pro_row as $k => $v) $pro_ary[$v['ProId']]=$v; 
	?>
	<?=ly200::load_static('/static/js/plugin/drag/drag.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
    <script type="text/javascript">var web_template_data=<?php include("{$c['default_path']}holiday/{$theme}/themes{$current_lang_ext}.json");?>; $(document).ready(function(){sales_obj.package_edit_init();});</script>
	<div class="list_box">
		<div class="lefter">
			<form id="tuan_form">
			<div class="p_title mar_t_0">{/sales.package.product_holiday_area/}</div>
			<div class="p_related_frame p_frame" max="<?=$max;?>">
				<?php
				if($proid_ary){
					$ProIdStr='|'.implode('|', $proid_ary).'|';
					foreach($proid_ary as $v){
						$proid=$v;
						$url=ly200::get_url($pro_ary[$proid], 'products');
						$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '168x168');
						$name=$pro_ary[$proid]['Name'.$c['manage']['web_lang']];
				?>
				<div id="related_product_<?=$proid;?>" class="p_related_item">
					<div class="p_related_img"><img src="<?=$img;?>"></div>
					<div class="p_related_info">
						<div class="related_list p_name"><span><?=$name;?></span></div>
						<div class="related_list"><span><?=cart::range_price($pro_ary[$proid], 1);?></span></div>
						<div class="related_list">{/products.product/}{/products.products.number/}: <span><?=$pro_ary[$proid]['Number'];?></span></div>
					</div>
					<div class="remove-item hand" type="related" del_num="<?=$proid;?>">X</div>
				</div>
				<?php
					}
				}else{
				?>
				<div class="p_related_notice">{/sales.package.holiday_notice/}</div>
				<?php }?>
			</div>
			<div class="related_bottom">
				<div class="related_btn">
					<input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=sales&a=holiday&d=edit&theme=<?=$theme;?>" class="btn_cancel fr">{/global.return/}</a>
				</div>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="Theme" value="<?=$theme;?>" />
			<input type="hidden" name="Number" value="<?=$number;?>" />
			<input type="hidden" name="ProId" id="proid_hide" value="" />
			<input type="hidden" name="ProIdAry" id="packageproid_hide" value="<?=$ProIdStr;?>" />
			<input type="hidden" name="do_action" value="sales.holiday_products_edit" />
			<input type="hidden" name="Type" id="type_hide" value="2" />
			<input type="hidden" name="IsMain" id="is_main" value="0" />
			</form>
		</div>
		<div class="list_box_righter">
			<div class="p_title">{/sales.package.product_list/}
				<div class="p_search">
					<form id="search_form">
						<input type="text" name="Name" class="form_input" search_input="1" value="" />
						<?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', '1', 'class="form_select"');?>
						<a href="javascript:;" class="btn_ok" id="search_btn">{/global.search/}</a>
						<input type="hidden" name="remove_pid" value="<?=$remove_pid ? $remove_pid : ','; ?>" />
						<input type="hidden" name="PId" value="<?=$PId;?>" /><div class="clear"></div>
					</form>
				</div>
				<a href="javascript:;" class="r_search_btn"></a>
			</div>
			<div class="product_frame p_frame">
				<?php
				foreach($products_row[0] as $k=>$v){
					$proid=$v['ProId'];
					$url=ly200::get_url($v, 'products');
					$img=ly200::get_size_img($v['PicPath_0'], '240x240');
					$name=$v['Name'.$c['manage']['web_lang']];
				?>
				<div id="product_item_<?=$proid;?>" class="product_item" pro_num="<?=$proid;?>">
					<div img_num="<?=$proid;?>" id="p_img_<?=$proid;?>" class="p_img"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
					<div class="p_info">
						<div class="p_list"><span><?=str::str_echo($name, 100, 0 ,'...');?></span></div>
						<div class="p_list"><span><?=cart::range_price($v, 1);?></span></div>
						<div class="p_list">{/products.product/}{/products.products.number/}: <span><?=$v['Number'];?></span></div>
					</div>
				</div>
				<?php }?>
				<div class="blank20"></div>
				<div class="blank20"></div>
				<div id="turn_page_oth" class="turn_page"><?=ly200::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}', 1);?></div>
				<div class="blank20"></div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<?php }?>
</div>