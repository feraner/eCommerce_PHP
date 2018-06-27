<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'tags'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('products', 0, array('a'=>'tags', 'd'=>'add')),
	'edit'	=>	manage::check_permit('products', 0, array('a'=>'tags', 'd'=>'edit')),
	'del'	=>	manage::check_permit('products', 0, array('a'=>'tags', 'd'=>'del'))
);

?>
<div class="r_nav">
    <h1>{/products.tags.tags/}</h1>
    <?php if($c['manage']['do']=='index'){?>
        <div class="search_form">
            <form method="get" action="?">
                <div class="k_input">
                    <input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
                    <input type="button" value="" class="more" />
                </div>
                <input type="submit" class="search_btn" value="{/global.search/}" />
                <input type="hidden" name="m" value="products" />
                <input type="hidden" name="a" value="tags" />
            </form>
        </div>
        <ul class="ico">
            <?php if($permit_ary['add']){?><li><a id="tags_edit" class="tip_ico_down add" href="javascript:;" label="{/global.add/}"></a></li><?php }?>
            <?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
        </ul>
    <?php }?>
</div>
<div id="tags" class="r_con_wrap">
	<?php if($c['manage']['do']=='index'){?>
	<script type="text/javascript">$(function(){products_obj.tags_init()});</script>
    <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
            <tr>
                <?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
                <td width="83%" nowrap="nowrap">{/products.tags.name/}</td>
                <?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
            </tr>
        </thead>
        <tbody>
            <?php
            $where=1;
            $Keyword=str::str_code($_GET['Keyword']);
            $Keyword && $where.=" and Name{$c['manage']['web_lang']} like '%$Keyword%'";
            $tags_row=str::str_code(db::get_all('products_tags', $where, '*', 'TId desc'));
            foreach($tags_row as $v){
            ?>
                <tr data="<?=htmlspecialchars(str::json_data($v));?>" tid="<?=$v['TId']?>">
                    <?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['TId'];?>" class="va_m" /></td><?php }?>
                    
                    <td nowrap="nowrap"><?=$v['Name'.$c['manage']['web_lang']];?></td>
                    <?php if($permit_ary['edit'] || $permit_ary['del']){?>
                        <td nowrap="nowrap">
                            <?php if($permit_ary['edit']){?>
                            <a class="tip_ico tip_min_ico tags_name_edit" href="javascript:;" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<a class="tip_ico tip_min_ico" href="./?m=products&a=tags&d=set&TId=<?=$v['TId']?>" label="{/global.set/}"><img src="/static/ico/set.png" align="absmiddle" /></a>
							<?php }?>
                            <?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.tags_del&TId=<?=$v['TId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
                        </td>
                    <?php }?>
                </tr>
            <?php }?>
        </tbody>
    </table>
    <div class="pop_form box_tags_edit">
        <form id="tags_edit_form">
            <div class="t"><h1><span>{/global.add/}</span>{/module.products.tags/}</h1><h2>×</h2></div>
            <div class="r_con_form">
                <div class="rows">
                    <label>{/products.title/}</label>
                    <span class="input"><?=manage::form_edit('', 'text', 'Name', 53, 150, 'notnull');?></span>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="TId" value="0" />
                <input type="hidden" name="do_action" value="products.tags_edit" />
            </div>
            <div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
        </form>
    </div>
    <?php
		}else{
			$TId=(int)$_GET['TId'];
			$tags_row=str::str_code(db::get_one('products_tags',"TId='$TId'"));
			//标签产品
			$tags_products_row=str::str_code(db::get_all('products',"Tags like '%|$TId|%'",'*',$c['my_order'].'ProId desc'));
			$tags_prod_id=array();
			foreach((array)$tags_products_row as $v){
				$tags_prod_id[]=$v['ProId'];
			}
			$tags_ary=$tags_prod_id?'|'.implode('|',$tags_prod_id).'|':'';
			//产品列表
			$where="(Tags is null or Tags='' or Tags not like '%|$TId|%')";
			$page_count=10;//显示数量
			$Name=str::str_code($_GET['Name']);
			$CateId=(int)$_GET['CateId'];
			$Name && $where.=" and (Name{$c['manage']['web_lang']} like '%$Name%' or concat(Prefix, Number) like '%$Name%')";
			if($CateId){
				$UId=category::get_UId_by_CateId($CateId);
				$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
			}
			$products_row=str::str_code(db::get_limit_page('products', $where, '*', $c['my_order'].'ProId desc', (int)$_GET['page'], $page_count));
	?>
    <script type="text/javascript">$(function(){products_obj.tags_set()});</script>
    <div class="list_box">
        <div class="lefter">
            <form id="tags_form">
                <div class="p_title mar_t_0"><?=$tags_row['Name'.$c['manage']['web_lang']];?></div>
                <div class="p_related_frame p_frame">
                	<?php
						if($tags_products_row){
							foreach($tags_products_row as $v){
								$proid=$v['ProId'];
								$url=ly200::get_url($v, 'products');
								$img=ly200::get_size_img($v['PicPath_0'], '240x240');
								$name=$v['Name'.$c['manage']['web_lang']];
					?>
                        <div id="product_item_<?=$proid;?>" class="product_item lefter_product_item" proid="<?=$proid?>">
                            <div class="p_img"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
                            <div class="p_info">
                                <div class="p_list">{/products.product/}{/products.name/}: <span><?=mb_substr($name, 0, 50);?>...</span></div>
                                <div class="p_list">{/products.product/}{/products.products.price/}: <span><?=cart::range_price($v, 1);?></span></div>
                                <div class="p_list">{/products.product/}{/products.products.number/}: <span><?=$v['Prefix'].$v['Number'];?></span></div>
                            </div>
                        </div>
					<?php
							}
						}else{
							echo '<div class="p_related_notice">'.str_replace('%name%',$tags_row['Name'.$c['manage']['web_lang']],$c['manage']['lang_pack']['products']['tags']['tags_notice']).'</div>';
						}
					?>
                </div>
                <div class="related_bottom">
                    <div class="related_btn">
                        <input type="submit" class="btn_ok submit_btn fr" name="submit_button" value="{/global.submit/}" />
                        <a href="?m=products&a=tags" class="btn_cancel fr">{/global.return/}</a>
                    </div>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="TId" value="<?=$TId?>" />
                <input type="hidden" name="ProId" id="proid_hide" value="<?=$tags_ary?>" />
                <input type="hidden" name="ProIdOld" value="<?=$tags_ary?>" />
                <input type="hidden" name="do_action" value="products.tags_set" />
            </form>
        </div>
        <div class="list_box_righter">
            <div class="p_title">{/products.product/}{/global.search/}</div>
            <div class="p_search">
                <form id="search_form">
                    <input type="text" name="Name" class="form_input" search_input="1" value="" />
                    <?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', '1', 'class="form_select"','{/global.select_index/}');?>
                    <a href="javascript:;" class="btn_ok" id="search_btn">{/global.search/}</a>
                    <input type="hidden" name="m" value="products" />
                    <input type="hidden" name="a" value="tags" />
                    <input type="hidden" name="d" value="set" />
                    <input type="hidden" name="TId" value="<?=(int)$_GET['TId']; ?>" />
                    <div class="clear"></div>
                </form>
            </div>
            <div class="p_title">{/sales.package.product_list/}<div id="turn_page_oth" class="turn_page fr"><?=ly200::turn_page($products_row[1], $products_row[2], $products_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}', 1);?></div></div>
            <div class="product_frame p_frame">
                <?php
                foreach($products_row[0] as $k=>$v){
                    $proid=$v['ProId'];
                    $url=ly200::get_url($v, 'products');
                    $img=ly200::get_size_img($v['PicPath_0'], '240x240');
                    $name=$v['Name'.$c['manage']['web_lang']];
                ?>
                <div id="product_item_<?=$proid;?>" class="product_item" proid="<?=$proid?>">
                    <div class="p_img"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
                    <div class="p_info">
                        <div class="p_list">{/products.product/}{/products.name/}: <span><?=mb_substr($name, 0, 50);?>...</span></div>
                        <div class="p_list">{/products.product/}{/products.products.price/}: <span><?=cart::range_price($v, 1);?></span></div>
                        <div class="p_list">{/products.product/}{/products.products.number/}: <span><?=$v['Prefix'].$v['Number'];?></span></div>
                    </div>
                </div>
                <?php }?>
                <div class="blank20"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <?php }?>
</div>