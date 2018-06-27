<?php !isset($c) && exit();?>
<?php
$where=1;
$CateId=(int)$_GET['CateId'];
$Column='Product List';
if ($CateId){
	$UId=category::get_UId_by_CateId($CateId);
	$UId && $where.=" and UId='$UId'";
	$category_row=db::get_one('products_category', "CateId='$CateId'");
	$Column='<a href="/catalog/">Product</a>';
	$Column.=ly200::get_web_position($category_row, 'products_category');
}
$allcate_row=str::str_code(db::get_all('products_category', $where, "CateId,UId,Category{$c['lang']},PicPath",  $c['my_order'].'CateId asc'));
$allcate_ary=array();
foreach($allcate_row as $k=>$v){
	$allcate_ary[$v['UId']][]=$v;
}
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta();?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}css/catalog.css", "{$c['mobile']['tpl_dir']}js/catalog.js");?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<div class="page_title">
    	<div class="pos clean"><div class="fl column"><?=$Column;?></div></div>
    </div>
	<div class="category_list">
    	<div class="item">
        	<div class="cate_1 clean">
                <div class="img fl pic_box"><img src="/static/themes/default/mobile/images/cate_pic.png"><span></span></div>
                <div class="name fr"><a href="/products/"><?=$c['lang_pack']['mobile']['view_all'];?></a></div>
            </div>
        </div>
        <?php foreach((array)$allcate_ary[$UId?$UId:'0,'] as $k=>$v){?>
        <div class="item">
        	<div class="cate_1 clean <?=$allcate_ary["{$v['UId']}{$v['CateId']},"]?'lower':'';?>">
                <div class="img fl pic_box"><?php if(is_file($c['root_path'].$v['PicPath'])){?><img src="<?=$v['PicPath'];?>"><?php }?><span></span></div>
                <div class="name fr">
                	<?php if(!$allcate_ary["{$v['UId']}{$v['CateId']},"]){?>
                    	<a href="<?=ly200::get_url($v, 'products_category')?>"><?=$v['Category'.$c['lang']];?></a>
                    <?php }else{?>
                    	<?=$v['Category'.$c['lang']];?>
                    <?php }?>
                </div>
            </div>
            <div class="cate_2">
            	<?php
				if($allcate_ary["{$v['UId']}{$v['CateId']},"]){
					foreach((array)$allcate_ary["{$v['UId']}{$v['CateId']},"] as $kk=>$vv){
						if($allcate_ary["{$vv['UId']}{$vv['CateId']},"]){//是否有三级
							$url="/catalog/c_{$vv['CateId']}/";
						}else{
							$url=ly200::get_url($vv, 'products_category');
						}
					?>
					<a href="<?=$url;?>" class="i"><?=$vv['Category'.$c['lang']];?></a>
				<?php }
				}?>
            </div>
        </div>
        <?php }?>
    </div>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
</body>
</html>