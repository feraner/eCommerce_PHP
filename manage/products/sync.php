<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'sync'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){
	$c['manage']['do']='aliexpress';
}elseif($c['manage']['do']=='local'){
	@header("Location: ./?m=products&a=products&Other=7");
	exit;
}

if($c['FunVersion']<2 && !substr_count($c['manage']['do'], 'aliexpress')){
	manage::no_permit(1);
}
if($c['manage']['do']=='aliexpress' || $c['manage']['do']=='aliexpress_products_edit'){//速卖通
	$Account=$_SESSION['Manage']['Aliexpress']['Token']['Account'];
	(!$Account || !db::get_row_count('authorization', "Platform='aliexpress' and  Account='{$Account}'")) && $Account=aliexpress::set_default_authorization();
	
	$category_data=$grouplist_data=array();
	if($Account){
		$category_row=db::get_all('products_aliexpress_category', "isleaf=1 and AliexpressCateId in (select categoryId from products_aliexpress where Account='$Account' group by categoryId)", 'AliexpressCateId,AliexpressUId,AliexpressCategory', 'AliexpressUId asc');
		foreach($category_row as $v){$category_data[$v['AliexpressCateId']]=$v;}
		
		$group_row=db::get_all('products_aliexpress_grouplist', "AliexpressGroupId in (select GroupId from products_aliexpress where Account='$Account' group by GroupId)");
		foreach($group_row as $v){$grouplist_data[$v['AliexpressGroupId']]=$v;}
	}
		
	$Keyword=str::str_code($_GET['Keyword']);
	$categoryId=(int)$_GET['categoryId'];
	$GroupId=(int)$_GET['GroupId'];
	$category_select_html=ly200::form_select($category_data, 'categoryId', $categoryId, 'AliexpressCategory', 'AliexpressCateId', '{/global.select_index/}');

	$shop_row=db::get_all('authorization', "Platform='aliexpress'", "AId,Account,Name");
}elseif($c['manage']['do']=='amazon' || $c['manage']['do']=='amazon_products_edit'){//亚马逊
	$Account=$_SESSION['Manage']['Amazon']['Account'];
	
	$Keyword=str::str_code($_GET['Keyword']);
	$shop_row=db::get_all('authorization', "Platform='amazon'", "AId,Account,Name");
	
	if(!$Account || !db::get_row_count('authorization', "Platform='amazon' and  Account='{$Account}'")){
		$_SESSION['Manage']['Amazon']=$shop_row[0];
		$Account=$shop_row[0]['Account'];
	}
}

$permit_ary=array(
	'add'		=>	manage::check_permit('products', 0, array('a'=>'sync', 'd'=>'add')),
	'edit'		=>	manage::check_permit('products', 0, array('a'=>'sync', 'd'=>'edit')),
	'del'		=>	manage::check_permit('products', 0, array('a'=>'sync', 'd'=>'del')),
	'sync'		=>	manage::check_permit('products', 0, array('a'=>'sync', 'd'=>'sync'))
);
?>
<div class="r_nav">
	<h1>{/module.products.sync/}</h1>
	<div class="turn_page"></div>
    <?php if($c['manage']['do']=='aliexpress' || $c['manage']['do']=='amazon'){?>
        <div class="search_form">
            <form method="get" action="?">
                <div class="k_input">
                    <input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
                    <input type="button" value="" class="more" />
                </div>
                <input type="submit" class="search_btn" value="{/global.search/}" />
                <?php if($c['manage']['do']=='aliexpress'){?>
                <div class="ext">
                    <div class="rows">
                        <label>{/products.classify/}</label>
                        <span class="input"><?=$category_select_html;?></span>
                        <div class="clear"></div>
                    </div>
                </div>
                <?php }?>
                <div class="clear"></div>
                <input type="hidden" name="m" value="products" />
                <input type="hidden" name="a" value="sync" />
            </form>
        </div>
		<ul class="ico">
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
    <?php }?>
    <dl class="edit_form_part">
        <?php
        $open_ary=array();
        foreach($c['manage']['permit']['pc']['set']['authorization']['menu'] as $k=>$v){
			if(($c['FunVersion']<2 && $v!='aliexpress') || $v=='open') continue;
        ?>
            <dt></dt>
            <dd><a href="./?m=products&a=sync&d=<?=$v;?>"<?=substr_count($c['manage']['do'], $v)?' class="current"':'';?>>{/module.set.authorization.<?=$v;?>/}</a></dd>
        <?php }?>
        <dt></dt>
        <dd><a href="./?m=products&a=sync&d=local"<?=$c['manage']['do']=='local'?' class="current"':'';?>>{/products.sync.local/}</a></dd>
    </dl>
</div>
<div id="products" class="r_con_wrap r_con_sync">
	<?=ly200::load_static('/static/manage/js/sync.js');?>
	<?php if($c['manage']['do']=='aliexpress'){?>
		<script type="text/javascript">$(document).ready(function(){sync_obj.sync_init();sync_obj.aliexpress_init();});</script>
    	<div class="account_list">
        	<div class="rows">
            	<label>{/products.sync.account/}:</label>
                <span>
                	<?php foreach($shop_row as $v){?><a href="javascript:;" data-id="<?=$v['AId'];?>"<?=$Account==$v['Account']?' class="cur"':'';?>><?=$v['Name'];?></a><?php }?>
                </span>
                <div class="clear"></div>
            </div>
        </div>
        <div class="dashboard">
			<?php if(@count($shop_row)){?>
                <div class="div-btn fl">
                    <button type="button" class="btn_ok" name="aliexpress_product_list_sync">{/products.sync.sync_products/}</button>
                </div>
                <div class="div-btn fl">
                    <button type="button" class="btn_ok" name="aliexpress_product_list_post">{/products.sync.copy/}<span></span></button>
                    <ul id="batchPost">
                        <li class="local">{/products.sync.copy_to_local/}</li>
                    </ul>
                </div>
			<?php }else{?>
                <div class="div-btn fl"><a href="/manage/?m=set&a=authorization&d=aliexpress" class="btn_ok">{/set.authorization.add_authorization_tips/}</a></div>
			<?php }?>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="1%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="6%" nowrap="nowrap">{/products.picture/}</td>
					<td width="25%" nowrap="nowrap">{/products.name/}/{/products.product/} ID</td>
					<td width="15%" nowrap="nowrap">{/products.classify/}/{/products.sync.group/}</td>
					<td width="10%" nowrap="nowrap">{/products.products.price/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				//产品列表				
				$where="Account='$Account'";//条件
				$page_count=50;//显示数量
				$Keyword && $where.=" and Subject like '%$Keyword%'";
				$categoryId && $where.=" and categoryId='$categoryId'";
				$GroupId && $where.=" and GroupId='$GroupId'";

				$products_row=str::str_code(db::get_limit_page('products_aliexpress', $where, '*', 'ProId desc', (int)$_GET['page'], $page_count));// 'productId desc'

				$i=1;
				foreach($products_row[0] as $v){
					$img=ly200::get_size_img($v['PicPath_0'], end($c['manage']['resize_ary']['products']));
					$name=$v['Subject'];
				?>
					<tr>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['ProId'];?>" class="va_m" /></td><?php }?>
						<td class="img"><a class="pic_box"><img src="<?=$img;?>" /><span></span></a></td>
						<td><?=$name;?><br /><a href="https://www.aliexpress.com/item/info/<?=$v['productId'];?>.html" target="_blank"><?=$v['productId'];?></a></td>
						<td><?=$category_data[$v['categoryId']]['AliexpressCategory'];?><br /><span class="fc_grey"><?=$grouplist_data[$v['GroupId']]['AliexpressGroupName'];?></span></td>
						<td nowrap="nowrap"><?=$v['currencyCode'];?> <span><?=sprintf('%01.2f', $v['productPrice']);?></span></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=sync&d=aliexpress_products_edit&ProId=<?=$v['ProId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.aliexpress_products_del&ProId=<?=$v['ProId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
        <div class="pop_form box_aliexpress_sync">
            <form id="aliexpress_sync_form">
                <div class="t"><h1><span></span>{/products.sync.sync_products/}</h1><h2>×</h2></div>
                <div class="r_con_form">
                    <div class="rows">
                        <label>{/products.product/}{/products.sync.group/}:</label>
                        <span class="input">
                            <?php 
								$group_row=str::str_code(db::get_all('products_aliexpress_grouplist', "Account='{$Account}'"));
								foreach($group_row as $v){
									if($v['UpperAliexpressGroupId']){
										$group_data[$v['UpperAliexpressGroupId']]['childGroup'][]=$v;
									}else{
										$group_data[$v['AliexpressGroupId']]=$v;
									}
								}
								$groupData='';
								foreach((array)$group_data as $v){
									if(@count((array)$v['childGroup'])){
										$groupData.="<optgroup label=\"{$v['AliexpressGroupName']}\">";
										foreach((array)$v['childGroup'] as $val){
											$groupData.="<option value=\"{$val['AliexpressGroupId']}\">{$val['AliexpressGroupName']}</option>";
										}
										$groupData.="</optgroup>";
									}else{
										$groupData.="<option value=\"{$v['AliexpressGroupId']}\">{$v['AliexpressGroupName']}</option>";
									}
								}
							?>
                        	<select name="GroupId">
                            	<option value="">{/products.sync.all_products/}</option>
                                <?=$groupData;?>
                            </select>
                        </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label>{/products.product/}{/global.status/}:</label>
                        <span class="input">
                        	<select name="productStatusType">
                            <?php foreach($c['manage']['sync_ary']['aliexpress'] as $v){?>
                            	<option value="<?=$v;?>">{/products.sync.status.<?=$v;?>/}</option>
                            <?php }?>
                            </select>
                        </span>
                        <div class="clear"></div>
                    </div>
                </div>
				<div class="button"><button type="button" class="btn_ok" name="submit_button">{/products.sync.start/}</button></div>
            </form>
        </div>
		<div class="pop_form copy_products_box">
			<form id="edit_form" class="w_750">
				<div class="t"><h1>{/products.sync.copy_to_local/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/products.products_category.category/}</label>
						<span class="input tab_box">
							<?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="button"><input type="button" class="btn_ok" name="submit_button" value="{/global.confirm/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
	<?php
	}elseif($c['manage']['do']=='aliexpress_products_edit'){
		$unitAry=array(
			100000000	=>	array('cn'=>'袋', 'en'=>'bag/bags'),
			100000001	=>	array('cn'=>'桶', 'en'=>'barrel/barrels'),
			100000002	=>	array('cn'=>'蒲式耳', 'en'=>'bushel/bushels'),
			100078580	=>	array('cn'=>'箱', 'en'=>'carton'),
			100078581	=>	array('cn'=>'厘米', 'en'=>'centimeter'),
			100000003	=>	array('cn'=>'立方米', 'en'=>'cubic meter'),
			100000004	=>	array('cn'=>'打', 'en'=>'dozen'),
			100078584	=>	array('cn'=>'英尺', 'en'=>'feet'),
			100000005	=>	array('cn'=>'加仑', 'en'=>'gallon'),
			100000006	=>	array('cn'=>'克', 'en'=>'gram'),
			100078587	=>	array('cn'=>'英寸', 'en'=>'inch'),
			100000007	=>	array('cn'=>'千克', 'en'=>'kilogram'),
			100078589	=>	array('cn'=>'千升', 'en'=>'kiloliter'),
			100000008	=>	array('cn'=>'千米', 'en'=>'kilometer'),
			100078559	=>	array('cn'=>'升', 'en'=>'liter/liters'),
			100000009	=>	array('cn'=>'英吨', 'en'=>'long ton'),
			100000010	=>	array('cn'=>'米', 'en'=>'meter'),
			100000011	=>	array('cn'=>'公吨', 'en'=>'metric ton'),
			100078560	=>	array('cn'=>'毫克', 'en'=>'milligram'),
			100078596	=>	array('cn'=>'毫升', 'en'=>'milliliter'),
			100078597	=>	array('cn'=>'毫米', 'en'=>'millimeter'),
			100000012	=>	array('cn'=>'盎司', 'en'=>'ounce'),
			100000014	=>	array('cn'=>'包', 'en'=>'pack/packs'),
			100000013	=>	array('cn'=>'双', 'en'=>'pair'),
			100000015	=>	array('cn'=>'件/个', 'en'=>'piece/pieces'),
			100000016	=>	array('cn'=>'磅', 'en'=>'pound'),
			100078603	=>	array('cn'=>'夸脱', 'en'=>'quart'),
			100000017	=>	array('cn'=>'套', 'en'=>'set/sets'),
			100000018	=>	array('cn'=>'美吨', 'en'=>'short ton'),
			100078606	=>	array('cn'=>'平方英尺', 'en'=>'square feet'),
			100078607	=>	array('cn'=>'平方英寸', 'en'=>'square inch'),
			100000019	=>	array('cn'=>'平方米', 'en'=>'square meter'),
			100078609	=>	array('cn'=>'平方码', 'en'=>'square yard'),
			100000020	=>	array('cn'=>'吨', 'en'=>'ton'),
			100078558	=>	array('cn'=>'码', 'en'=>'yard/yards')
		);

		//产品编辑
		$ProId=(int)$_GET['ProId'];
		$property_data=$property_ary=$sku_data=$attribute_data=$sku_property_data=$sku_selected_data=$attributeAry=$group_data=array();
		$products_row=str::str_code(db::get_one('products_aliexpress', "ProId='$ProId'"));
		$productId=(int)$products_row['productId'];
		!$products_row['ProId'] && js::location('./?m=products&a=sync');
		$products_description_row=str::str_code(db::get_one('products_aliexpress_description', "productId='$productId'"));
		$sku_data=str::json_data(str::str_code($products_row['aeopAeProductSKUs'], 'htmlspecialchars_decode'), 'decode');
		
		$AliexpressLang=@substr($c['manage']['config']['ManageLanguage'],0,2);
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');?>
        <script type="text/javascript">$(document).ready(function(){sync_obj.aliexpress_edit_init();});</script>
		<form id="edit_form" class="r_con_form">
			<?php /***************************** 基本信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.base/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.shopName/}</label>
				<span class="input"><?=ly200::form_select($shop_row, 'Account', $products_row['Account'], 'Name', 'Account');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.classify/}</label>
				<span class="input">
					<?=ly200::form_select($category_data, 'categoryId', $products_row['categoryId'], 'AliexpressCategory');?>
                    <div class="clear"></div>
                    <div class="category_list"></div>
                </span>
				<div class="blank12"></div>
			</div>
			<?php /***************************** 基本信息 End *****************************/?>

			<?php /***************************** 属性信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.attr/}</h3>
			<div class="rows">
				<label>{/products.sync.productAttr/}</label>
				<span class="input">
                	<div class="property-table">
                    	<div class="property-form"></div>
                        <div class="custom-property hide"></div>
                    </div>
                </span>
				<div class="blank12"></div>
			</div>
			<?php /***************************** 属性信息 End *****************************/?>
			
			<?php /***************************** 产品信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.goods/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.name/}</label>
				<span class="input"><input name="Subject" value="<?=$products_row['Subject'];?>" type="text" class="form_input" size="100" maxlength="128" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.product/}{/products.sync.group/}</label>
				<span id="GroupList" class="input" data-value="<?=$products_row['GroupId'];?>"><select name="GroupId"></select> &nbsp;&nbsp; <a href="javascript:;" class="group sync green">{/global.synchronize/}</a></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.product/}{/products.picture/}</label>
				<span class="input">
					<span class="multi_img upload_file_multi" id="PicDetail">
						<?php
						for($i=0; $i<6; ++$i){
						?>
						<dl class="img" num="<?=$i;?>">
							<dt class="upload_box preview_pic">
								<input type="button" id="PicUpload_<?=$i;?>" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_tips/}, {/notes.pic_size_tips/}'), 6, '800*800');?>" />
								<input type="hidden" name="PicPath[]" value="<?=$products_row["PicPath_{$i}"];?>" data-value="<?=ly200::get_size_img($products_row["PicPath_{$i}"], '240x240');?>" save="<?=is_file($c['root_path'].$products_row["PicPath_{$i}"])?1:0;?>" />
							</dt>
							<dd class="pic_btn">
								<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
								<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
							</dd>
						</dl>
						<?php }?>
					</span>
					<div class="tips"><?=sprintf(manage::language('{/notes.pic_size_tips/}'), '800*800');?></div>
					<div class="clear"></div>
				</span>
				<div class="blank30"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.unit/}</label>
				<span class="input">
                    <select name="productUnit" data-unit="<?=$unitAry[$products_row['productUnit']][($AliexpressLang=='en'?'en':'cn')];?>" notnull>
                        <?php 
                            foreach($unitAry as $k=>$v){
								$groupSelected=$k==$products_row['productUnit']?' selected':'';
								$dataUnit=@reset(explode('/', $AliexpressLang=='en'?$v['en']:$v['cn']));
								echo "<option value=\"{$k}\"{$groupSelected} data-unit=\"{$dataUnit}\">{$v['cn']} ({$v['en']})</option>";
                            }
                        ?>
                    </select>
                </span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.salesMethod/}</label>
				<span class="input">
                    <label><input type="radio" name="packageType" value="0"<?=!(int)$products_row['packageType']?' checked':'';?> /> {/products.sync.salesByPiece/}</label>
                    <label><input type="radio" name="packageType" value="1"<?=(int)$products_row['packageType']?' checked':'';?> /> {/products.sync.salesByPack/}</label> &nbsp; 
                    <span class="salesByPack <?=(int)$products_row['packageType']?'show':'hide';?>">{/products.sync.salesByPack/} <input name="lotNum" value="<?=$products_row['lotNum'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /> <span class="pUnit"></span></span>
                </span>
				<div class="clear"></div>
			</div>
            
            
            
            <div id="sku" class="attribute" attrid="">
            	
                
                
                <div class="rows hide" id="attribute_ext_box">
                    <label></label>
                    <span class="input">
                        <table border="0" cellpadding="5" cellspacing="0" id="attribute_ext" class="relation_box">
                            <thead>
                                <tr>
                                    <td width="35%">{/products.attribute/}</td>
                                    <td width="11%">{/products.sync.productPrice/}</td>
                                    <td width="12%">{/products.products.stock/}</td>
                                    <td width="15%">{/products.sync.skucode/}</td>
                                </tr>
                            </thead>
                        </table>
                        <div id="attribute_tmp" class="hide">
                            <table class="column">
                                <tbody id="AttrId_XXX">
                                    <tr>
                                        <td class="title">Column</td>
                                        <td class="title"><a href="javascript:;" class="synchronize_btn" data-num="0">{/global.synchronize/}</a></td>
                                        <td class="title"><a href="javascript:;" class="synchronize_btn" data-num="1">{/global.synchronize/}</a></td>
                                        <td class="title"><a href="javascript:;" class="synchronize_btn" data-num="2">{/global.synchronize/}</a></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                                <tbody class="contents">
                                    <tr id="VId_XXX" attr_txt="">
                                        <td>Name</td>
                                        <td><input type="text" name="skuPrice[XXX]" value="p_v" class="form_input input_w" size="5" maxlength="6" rel="amount" notnull /></td>
                                        <td><input type="text" name="skuStock[XXX]" value="s_v" class="form_input input_w" size="5" maxlength="6" rel="digital" notnull /></td>
                                        <td><input type="text" name="skuCode[XXX]" value="u_v" class="form_input input_w sku_input" size="10" maxlength="30" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="custom_tmp" class="hide">
                            <table>
                            	<tbody class="contents_all">
                                	<tr id="CustomAll_XXX">
                                    	<td>Name</td>
                                        <td class="spacing"><input type="text" name="CustomName[XXX]" value="c_n" class="form_input input_w sku_input" size="10" maxlength="20"></td>
                                        <td class="spacing">Content</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                            	<tbody class="contents_input">
                                	<tr id="CustomInput_XXX">
                                    	<td>Name</td>
                                        <td class="spacing"><input type="text" name="CustomName[XXX]" value="c_n" class="form_input input_w sku_input" size="10" maxlength="20"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table>
                            	<tbody class="contents_image">
                                	<tr id="CustomImage_XXX">
                                    	<td>Name</td>
                                        <td class="spacing">Content</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </span>
                    <div class="clear"></div>
                </div>
                
                
                
                
                <div class="rows form-post-list">
                    <label><font class="fc_red">*</font> {/products.sync.productPrice/}</label>
                    <span class="input"><?=$products_row['currencyCode'];?> <input name="productPrice" value="<?=$products_row['productPrice'];?>" type="text" class="form_input" size="20" maxlength="6" rel="amount" /> / <span class="pUnit"></span></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>{/products.products.wholesale_price/}</label>
                    <span class="input">
                        <label><input type="checkbox" name="IsWholeSale" value="1"<?=((int)$products_row['bulkOrder'] && (int)$products_row['bulkDiscount'])?' checked':'';?> /> {/products.sync.support/}</label>
                        <div class="clear"></div>
                        <div class="WholeSaleBox<?=((int)$products_row['bulkOrder'] && (int)$products_row['bulkDiscount'])?'':' hide';?>">
                            {/products.sync.wholesale_before/}<input type="text" name="bulkOrder" value="<?=$products_row['bulkOrder']?$products_row['bulkOrder']:''?>" class="form_input" size="5" maxlength="6" rel="digital" /> <span class="pUnit"></span>
                            {/products.sync.wholesale_middle/}<input type="text" name="bulkDiscount" value="<?=$products_row['bulkDiscount']?$products_row['bulkDiscount']:''?>" class="form_input" size="5" maxlength="2" rel="digital" />
                            {/products.sync.wholesale_after/}
                        </div>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows form-post-list">
                    <label><font class="fc_red">*</font> {/products.products.stock/}</label>
                    <span class="input"><input name="ipmSkuStock" value="<?=$sku_data[0]['ipmSkuStock'];?>" type="text" class="form_input" size="20" maxlength="6" rel="digital" /></span>
                    <div class="clear"></div>
                </div>
                <div class="rows form-post-list">
                    <label>{/products.sync.skucode/}</label>
                    <span class="input"><input name="ipmSkuCode" value="<?=$sku_data[0]['skuCode'];?>" type="text" class="form_input" size="20" maxlength="30" /></span>
                    <div class="clear"></div>
                </div>
            </div>
            
            
            
			<div class="rows">
				<label>{/products.sync.reduceStrategy/}</label>
				<span class="input">
                    <label><input type="radio" name="reduceStrategy" value="place_order_withhold"<?=$products_row['reduceStrategy']=='place_order_withhold'?' checked':'';?> /> {/products.sync.placeReduce/}</label>
                    <label><input type="radio" name="reduceStrategy" value="payment_success_deduct"<?=($products_row['reduceStrategy']=='payment_success_deduct'||$products_row['reduceStrategy']=='')?' checked':'';?> /> {/products.sync.paymentReduce/}</label>
                </span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.delivery/}</label>
				<span class="input"><input name="deliveryTime" value="<?=$products_row['deliveryTime'];?>" type="text" class="form_input" size="5" maxlength="1" rel="digital" notnull /> {/products.sync.date/} {/products.sync.deliveryTips/}</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.validPeriod/}</label>
				<span class="input">
                	<label><input type="radio" name="wsValidNum" value="14"<?=$products_row['wsValidNum']==14?' checked':'';?> /> 14 {/products.sync.date/}</label>
                	<label><input type="radio" name="wsValidNum" value="30"<?=($products_row['wsValidNum']==30||!$products_row['wsValidNum'])?' checked':'';?> /> 30 {/products.sync.date/}</label>
                </span>
				<div class="blank30"></div>
			</div>
			<?php /***************************** 产品信息 End *****************************/?>
						
			<?php /***************************** 文字描述 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.descInfo/}</h3>
			<div class="rows">
				<label>{/products.sync.pcDetail/}</label>
				<span class="input"><?=manage::Editor("Detail", $products_description_row["Detail"]);?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.sync.mobileDetail/}</label>
				<span class="input">
                	<div class="turn_on_mobile_detail"><input type="checkbox" name="IsMobileDetail" value="1"<?=(int)$products_description_row["mobileDetail"]?' checked="checked"':'';?> /> {/global.turn_on/} &nbsp;&nbsp; <a href="javascript:;" class="sync green">{/global.key_sync/}</a></div>
					<div class="mobile_detail <?=$products_description_row["mobileDetail"]==''?'hide':'';?>"><?=manage::Editor("mobileDetail", $products_description_row["mobileDetail"]);?></div>
				</span>
				<div class="blank12"></div>
			</div>
			<?php /***************************** 文字描述 End *****************************/?>

			<?php /***************************** 包装信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.packageInfo/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.packageWeight/}</label>
				<span class="input">
					<input name="grossWeight" value="<?=$products_row['grossWeight'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" notnull /> {/products.sync.WeightUnit/}/<span class="pUnit"></span> &nbsp;&nbsp; 
                    <input type="checkbox" name="isPackSell" value="1" /> {/products.sync.customWeight/}
                    <div class="clear"></div>
                    <div class="custom_weight hide">
                        <div><?=sprintf($c['manage']['lang_pack']['products']['sync']['baseUnit'], ($products_row['baseUnit']?$products_row['baseUnit']:''));?></div>
                        <div><?=sprintf($c['manage']['lang_pack']['products']['sync']['addUnit'], ($products_row['addUnit']?$products_row['addUnit']:''), ($products_row['addWeight']>0?$products_row['addWeight']:''));?></div>
                    </div>
                </span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.packageSize/}</label>
				<span class="input">
					<input name="packageLength" value="<?=$products_row['packageLength'];?>" type="text" class="form_input" size="5" maxlength="3" rel="digital" notnull /> &nbsp; X &nbsp; 
                    <input name="packageWidth" value="<?=$products_row['packageWidth'];?>" type="text" class="form_input" size="5" maxlength="3" rel="digital" notnull /> &nbsp; X &nbsp; 
                    <input name="packageHeight" value="<?=$products_row['packageHeight'];?>" type="text" class="form_input" size="5" maxlength="3" rel="digital" notnull /> {/products.sync.sizeTips/}
                </span>
				<div class="blank12"></div>
			</div>
			<?php /***************************** 包装信息 End *****************************/?>

			<?php /***************************** 模板信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.templateInfo/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.freightTemp/}</label>
				<span id="freightTemp" class="input" data-value="<?=$products_row['freightTemplateId'];?>"> &nbsp;&nbsp; <a href="javascript:;" class="freight sync green">{/global.synchronize/}</a></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.serviceTemp/}</label>
				<span id="serviceTemp" class="input" data-value="<?=$products_row['promiseTemplateId'];?>"> &nbsp;&nbsp; <a href="javascript:;" class="service sync green">{/global.synchronize/}</a></span>
				<div class="blank30"></div>
			</div>
			<?php /*?><div class="rows">
				<label>{/products.sync.sizechartTemp/}</label>
				<span class="input">

                </span>
				<div class="clear"></div>
			</div><?php */?>
			<?php /***************************** 模板信息 End *****************************/?>
            <div class="blank30"></div>
            
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="<?=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'./?m=products&a=snyc';?>" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="all_attr" value="" />
			<input type="hidden" id="check_attr" value="" />
			<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($combinatin_ary));?>" />
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" id="productId" name="productId" value="<?=$productId;?>" />
			<input type="hidden" name="do_action" value="products.sync_aliexpress_products_edit" />
			<input type="hidden" id="back_action" name="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" />
		</form>
	<?php }elseif($c['manage']['do']=='amazon'){?>
		<script type="text/javascript">$(document).ready(function(){sync_obj.sync_init();sync_obj.amazon_init();});</script>
    	<div class="account_list">
        	<div class="rows">
            	<label>{/products.sync.account/}:</label>
                <span>
                	<?php foreach($shop_row as $v){?><a href="javascript:;" data-id="<?=$v['AId'];?>"<?=$Account==$v['Account']?' class="cur"':'';?>><?=$v['Name'];?></a><?php }?>
                </span>
                <div class="clear"></div>
            </div>
        </div>
        <div class="dashboard">
			<?php if(@count($shop_row)){?>
                <div class="div-btn fl">
                    <button type="button" class="btn_ok" name="amazon_product_list_sync">{/products.sync.sync_products/}</button>
                </div>
                <div class="div-btn fl">
                    <button type="button" class="btn_ok" name="amazon_product_list_post">{/products.sync.copy/}<span></span></button>
                    <ul id="batchPost">
                        <li class="local">{/products.sync.copy_to_local/}</li>
                    </ul>
                </div>
			<?php }else{?>
                <div class="div-btn fl"><a href="/manage/?m=set&a=authorization&d=amazon" class="btn_ok">{/set.authorization.add_authorization_tips/}</a></div>
            <?php }?>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="1%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="6%" nowrap="nowrap">{/products.picture/}</td>
					<td width="35%" nowrap="nowrap">{/products.name/} / {/products.sync.amazon.asin/}</td>
					<td width="15%" nowrap="nowrap">{/products.sync.amazon.sku/} / {/products.products.number/}</td>
					<td width="10%" nowrap="nowrap">{/products.products.price/}</td>
					<td width="15%" nowrap="nowrap">{/products.products.stock/}</td>
					<td width="10%" nowrap="nowrap">{/set.authorization.marketplace/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				//产品列表				
				$where="MerchantId='$Account'";//条件
				$page_count=50;//显示数量
				$Keyword && $where.=" and item-name like '%$Keyword%'";

				$products_row=str::str_code(db::get_limit_page('products_amazon', $where, '*', 'ProId desc', (int)$_GET['page'], $page_count));// 'productId desc'

				$i=1;
				foreach($products_row[0] as $v){
					$img=ly200::get_size_img($v['image-url'], end($c['manage']['resize_ary']['products']));
					!$img && $img='/static/manage/images/frame/no-image-sm.gif';
					$name=$v['item-name'];
				?>
					<tr>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['ProId'];?>" class="va_m" /></td><?php }?>
						<td class="img"><a class="pic_box"><img src="<?=$img;?>" /><span></span></a></td>
						<td><?=$name;?><br /><a href="<?=$c['manage']['sync_ary']['amazon'][$v['Marketplace']][1];?>dp/<?=$v['asin1'];?>" target="_blank"><?=$v['asin1'];?></a></td>
						<td><?=$v['seller-sku'];?><br /><span class="fc_grey"><?=$v['listing-id'];?></span></td>
						<td nowrap="nowrap"><?=$v['currencyCode'];?> <span><?=sprintf('%01.2f', $v['price']);?></span></td>
						<td nowrap="nowrap"><?=$v['quantity'];?></td>
						<td>{/set.authorization.<?=$v['Marketplace'];?>/}</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=sync&d=amazon_products_edit&ProId=<?=$v['ProId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.amazon_products_del&ProId=<?=$v['ProId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?></div>
		<div class="pop_form copy_products_box">
			<form id="edit_form" class="w_750">
				<div class="t"><h1>{/products.sync.copy_to_local/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/products.products_category.category/}</label>
						<span class="input tab_box">
							<?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="button"><input type="button" class="btn_ok" name="submit_button" value="{/global.confirm/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
	<?php
	}elseif($c['manage']['do']=='amazon_products_edit'){
		//产品编辑
		$ProId=(int)$_GET['ProId'];
		$products_row=str::str_code(db::get_one('products_amazon', "ProId='$ProId'"));
		$productId=$products_row['product-id'];
		!$products_row['ProId'] && js::back();

		$feature_data=str::json_data(str::str_code($products_row['Feature'], 'htmlspecialchars_decode'), 'decode');
		$listprice_data=str::json_data(str::str_code($products_row['ListPrice'], 'htmlspecialchars_decode'), 'decode');
		$item_data=str::json_data(str::str_code($products_row['ItemDimensions'], 'htmlspecialchars_decode'), 'decode');
		$pakeage_data=str::json_data(str::str_code($products_row['PackageDimensions'], 'htmlspecialchars_decode'), 'decode');
		
		$img=ly200::get_size_img($products_row['image-url'], '240x240');
		$defaultCurrency=db::get_value('currency', 'IsUsed=1 and ManageDefault=1', 'Currency');
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');?>
        <script type="text/javascript">$(document).ready(function(){sync_obj.amazon_edit_init();});</script>
		<form id="edit_form" class="r_con_form">
			<?php /***************************** 基本信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.shop_info/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.sync.shopName/}</label>
				<span class="input"><?=ly200::form_select($shop_row, 'Account', $products_row['Account'], 'Name', 'Account');?></span>
				<div class="clear"></div>
			</div>
			<?php /***************************** 基本信息 End *****************************/?>

			<?php /***************************** 产品信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.goods/}</h3>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.name/}</label>
				<span class="input"><input name="item-name" value="<?=$products_row['item-name'];?>" type="text" class="form_input" size="100" maxlength="128" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.sync.amazon.asin/}</label>
				<span class="input"><input name="seller-sku" value="<?=$products_row['asin1'];?>" type="text" class="form_input" size="53" maxlength="50" readonly /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.sync.amazon.sku/}</label>
				<span class="input"><input name="seller-sku" value="<?=$products_row['seller-sku'];?>" type="text" class="form_input" size="53" maxlength="50" readonly /></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
                <label>{/products.products.number/}</label>
                <span class="input"><input name="listing-id" value="<?=$products_row['listing-id'];?>" type="text" class="form_input" size="53" maxlength="50" readonly /></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label><font class="fc_red">*</font> {/products.products.price/}</label>
                <span class="input">
                	<input type="text" name="price" value="<?=sprintf('%01.2f', $products_row['price']);?>" class="form_input" size="10" maxlength="10" rel="amount" notnull />&nbsp;&nbsp;
                    <span class="price_input"><b>{/products.products.price_ary.0/} <?=$listprice_data['CurrencyCode']?><div class="arrow"><em></em><i></i></div></b><input name="ListPrice[Amount]" value="<?=sprintf('%01.2f', $listprice_data['Amount']);?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
                    <input type="hidden" name="ListPrice[CurrencyCode]" value="<?=$listprice_data['CurrencyCode']?$listprice_data['CurrencyCode']:$defaultCurrency;?>" />
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label><font class="fc_red">*</font> {/products.products.qty/}</label>
                <span class="input"><input name="quantity" value="<?=(int)$products_row['quantity'];?>" type="text" class="form_input" size="10" maxlength="10" rel="digital" notnull /> ({/products.products.stock/})</span>
                <div class="clear"></div>
            </div>
			<div class="rows">
				<label><font class="fc_red">*</font> {/products.product/}{/products.picture/}</label>
                <span class="input upload_file upload_image">
                    <div class="img">
                        <div id="ImageDetail" class="upload_box preview_pic"><input type="button" id="ImageUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '800*800');?>" style="display:none;" /></div>
						<?php /*?>{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '800*800');?><?php */?>
                    </div>
                </span>
				<div class="clear"></div>
			</div>
			<?php /***************************** 产品信息 End *****************************/?>

			<?php /***************************** 包装信息 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.packageInfo/}</h3>
            <div class="rows">
                <label>{/products.products.cubage/}</label>
                <span class="input">
                    <span class="price_input"><b>{/products.products.long/}<div class="arrow"><em></em><i></i></div></b><input name="ItemDimensions[Length][0]" value="<?=$item_data['Length'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$item_data['Length'][1];?></b></span>&nbsp;&nbsp;&nbsp;
                    <input type="hidden" name="ItemDimensions[Length][1]" value="<?=$item_data['Length'][1]?$item_data['Length'][1]:'inches';?>" />
                    <span class="price_input"><b>{/products.products.width/}<div class="arrow"><em></em><i></i></div></b><input name="ItemDimensions[Width][0]" value="<?=$item_data['Width'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$item_data['Width'][1];?></b></span>&nbsp;&nbsp;&nbsp;
                    <input type="hidden" name="ItemDimensions[Width][1]" value="<?=$item_data['Width'][1]?$item_data['Width'][1]:'inches';?>" />
                    <span class="price_input"><b>{/products.products.height/}<div class="arrow"><em></em><i></i></div></b><input name="ItemDimensions[Height][0]" value="<?=$item_data['Height'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$item_data['Height'][1];?></b></span>
                    <input type="hidden" name="ItemDimensions[Height][1]" value="<?=$item_data['Height'][1]?$item_data['Height'][1]:'inches';?>" />
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/products.sync.packageWeight/}</label>
                <span class="input">
                    <span class="price_input"><b>{/products.products.weight/}<div class="arrow"><em></em><i></i></div></b><input name="PackageDimensions[Weight][0]" value="<?=$pakeage_data['Weight'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$pakeage_data['Weight'][1];?></b></span>
                    <input type="hidden" name="PackageDimensions[Weight][1]" value="<?=$pakeage_data['Weight'][1]?$pakeage_data['Weight'][1]:'pounds';?>" />
                </span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/products.sync.packageSize/}</label>
                <span class="input">
                    <span class="price_input"><b>{/products.products.long/}<div class="arrow"><em></em><i></i></div></b><input name="PackageDimensions[Length][0]" value="<?=$pakeage_data['Length'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$pakeage_data['Length'][1];?></b></span>&nbsp;&nbsp;&nbsp;
                    <input type="hidden" name="PackageDimensions[Length][1]" value="<?=$pakeage_data['Length'][1]?$pakeage_data['Length'][1]:'inches';?>" />
                    <span class="price_input"><b>{/products.products.width/}<div class="arrow"><em></em><i></i></div></b><input name="PackageDimensions[Width][0]" value="<?=$pakeage_data['Width'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$pakeage_data['Width'][1];?></b></span>&nbsp;&nbsp;&nbsp;
                    <input type="hidden" name="PackageDimensions[Width][1]" value="<?=$pakeage_data['Width'][1]?$pakeage_data['Width'][1]:'inches';?>" />
                    <span class="price_input"><b>{/products.products.height/}<div class="arrow"><em></em><i></i></div></b><input name="PackageDimensions[Height][0]" value="<?=$pakeage_data['Height'][0];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last"><?=$pakeage_data['Height'][1];?></b></span>
                    <input type="hidden" name="PackageDimensions[Height][1]" value="<?=$pakeage_data['Height'][1]?$pakeage_data['Height'][1]:'inches';?>" />
                </span>
                <div class="clear"></div>
            </div>
			<?php /***************************** 包装信息 End *****************************/?>
						
			<?php /***************************** 文字描述 Start *****************************/?>
			<h3 class="rows_hd">{/products.sync.descInfo/}</h3>
            <?php for($i=0; $i<5; $i++){?>
            <div class="rows">
                <label><?=!$i?'{/products.sync.amazon.feature/}':'';?></label>
                <span class="input"><input name="Feature[]" value="<?=$feature_data[$i];?>" title="<?=$feature_data[$i];?>" type="text" class="form_input" size="100" maxlength="200" /></span>
                <div class="clear"></div>
            </div>
            <?php }?>
            
			<div class="rows">
				<label>{/products.products.description/}</label>
				<span class="input"><?=manage::Editor("item-description", $products_row["item-description"]);?></span>
				<div class="clear"></div>
			</div>
			<?php /***************************** 文字描述 End *****************************/?>

			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" /><?php /*?><?php */?>
					<a href="<?=$_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'./?m=products&a=snyc&d=amazon';?>" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="ImagePath" name="ImagePath" value="<?=$img;?>" save="<?=is_file($c['root_path'].$img)?1:0;?>" />
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" name="do_action" value="products.sync_amazon_products_edit" />
			<input type="hidden" id="back_action" name="back_action" value="<?=$_SERVER['HTTP_REFERER'];?>" /><?php /*?><?php */?>
		</form>
	<?php }else{?>
    
    <?php }?>
    <div class="pop_form sync_progress">
        <div class="tips_contents"></div>
    </div>
</div>