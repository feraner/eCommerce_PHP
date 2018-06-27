<?php !isset($c) && exit();?>
<?php
$saveId=(int)$CateId?((int)$TopCateId?(int)$TopCateId:$CateId):0;
if(!file::check_cache("catalog-{$saveId}.html")){
	ob_start();
	
	if(!$allcate_ary){
		$c_where="IsSoldOut=0";
		if($saveId) $c_where.=" and UId like '0,{$saveId},%'";
		else $c_where.=" and UId='0,'";
        $allcate_row=str::str_code(db::get_all('products_category', $c_where, '*',  $c['my_order'].'CateId asc'));
        $allcate_ary=array();
        foreach((array)$allcate_row as $k=>$v){
            $allcate_ary[$v['UId']][]=$v;
        }
    }
?>
	<div class="side_category">
		<?php
		if($CateId){
			$row=$TopCategory_row?$TopCategory_row:$category_row;
		?>
            <div class="cate_title"><?=$row['Category'.$c['lang']];?></div>
            <dl class="cate_menu">
                <?php
                $all_uid=$row['UId'].($TopCateId?$TopCateId:$CateId).',';
                foreach((array)$allcate_ary[$all_uid] as $v){
                    $vCateId=$v['CateId'];
                ?>
                <dd class="first">
                    <a class="catalog_<?=$vCateId;?>" href="<?=ly200::get_url($v, 'products_category');?>"><?=$v['Category'.$c['lang']];?></a>
                    <?php if((int)$v['SubCateCount']){?>
                    <dl class="catalog_<?=$vCateId;?> hide">
                        <?php
                        foreach((array)$allcate_ary["{$all_uid}{$vCateId},"] as $v2){
                            $v2CateId=$v2['CateId'];
                        ?>
                        	<dd><a class="catalog_<?=$v2CateId?>" href="<?=ly200::get_url($v2, 'products_category');?>"><?=$v2['Category'.$c['lang']];?></a></dd>
                        <?php }?>
                    </dl>
                    <?php }?>
                </dd>
                <?php }?>
            </dl>
		<?php
		}else{
		?>
            <div class="cate_title"><?=$c['lang_pack']['related_category'];?></div>
            <dl class="cate_menu">
                <?php foreach((array)$allcate_ary['0,'] as $v){?>
                	<dd class="first"><a href="<?=ly200::get_url($v, 'products_category');?>"><?=$v['Category'.$c['lang']];?></a></dd>
                <?php }?>
            </dl>
		<?php }?>
	</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), "catalog-{$saveId}.html", $cache_contents);
}
include(ly200::get_cache_path($c['theme'])."catalog-{$saveId}.html");
?>
<?php if($CateId){?>
<script type="text/javascript">
$('dl.catalog_<?=$CateId;?>, dl.catalog_<?=(int)$SecCateId;?>').addClass('show').removeClass('hide');
$('a.catalog_<?=$CateId;?>, a.catalog_<?=(int)$SecCateId;?>').addClass('current');
</script>
<?php }?>