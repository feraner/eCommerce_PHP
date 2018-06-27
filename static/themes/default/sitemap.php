<?php !isset($c) && exit();?>
<?php
$current_page='sitemap';
$prod_category_ary=$page_ary=$info_category_ary=array();

//products_category
$prod_category_row=str::str_code(db::get_all('products_category', '1', 'CateId,UId,Category'.$c['lang'], $c['my_order'].'CateId asc'));
foreach((array)$prod_category_row as $k=>$v){
	$prod_category_ary[$v['UId']][]=$v;
}

//article
$page_category_row=str::str_code(db::get_all('article_category', '1', 'CateId,UId,Category'.$c['lang'], $c['my_order'].'CateId asc'));
$page_row=str::str_code(db::get_all('article', '1', '*', $c['my_order'].'AId asc'));
foreach((array)$page_row as $k=>$v){
	$page_ary[$v['CateId']][]=$v;
}

//info
$info_category_row=str::str_code(db::get_all('info_category', '1', 'CateId,UId,Category'.$c['lang'], $c['my_order'].'CateId asc'));
foreach((array)$info_category_row as $k=>$v){
	$info_category_ary[$v['UId']][]=$v;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Sitemap</title>
<?php include("{$c['static_path']}/inc/static.php");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div class="blank20"></div>
	<div class="sitemap_box">
		<div class="sitemap_body clearfix">
			<?php foreach((array)$prod_category_ary['0,'] as $k=>$v){?>
			<dl>
				<dt><a href="<?=ly200::get_url($v, 'products_category');?>"><?=$v['Category'.$c['lang']];?></a></dt>
				<?php if((int)$v['SubCateCount']){?>
				<dd>
					<?php foreach((array)$prod_category_ary["0,{$v['CateId']},"] as $v2){?>
					<a href="<?=ly200::get_url($v2, 'products_category');?>"><?=$v2['Category'.$c['lang']];?></a>
					<?php }?>
				</dd>
				<?php }?>
			</dl>
			<?=($k+1)%5==0?'<div class="blank15"></div>':'';?>
			<?php }?>
		</div>
		
		<div class="sitemap_body clearfix">
			<?php foreach((array)$page_category_row as $k=>$v){?>
			<dl>
				<dt><?=$v['Category'.$c['lang']];?></dt>
				<?php if(count($page_ary[$v['CateId']])){?>
				<dd>
					<?php foreach((array)$page_ary[$v['CateId']] as $v2){?>
					<a href="<?=ly200::get_url($v2, 'article');?>"><?=$v2['Title'.$c['lang']];?></a>
					<?php }?>
				</dd>
				<?php }?>
			</dl>
			<?=($k+1)%5==0?'<div class="blank15"></div>':'';?>
			<?php }?>
		</div>
		
		<div class="sitemap_body clearfix">
			<?php foreach((array)$info_category_ary['0,'] as $k=>$v){?>
			<dl>
				<dt><a href="<?=ly200::get_url($v, 'info_category');?>"><?=$v['Category'.$c['lang']];?></a></dt>
				<?php if((int)$v['SubCateCount']){?>
				<dd>
					<?php foreach((array)$info_category_ary["0,{$v['CateId']},"] as $v2){?>
					<a href="<?=ly200::get_url($v2, 'info_category');?>"><?=$v2['Category'.$c['lang']];?></a>
					<?php }?>
				</dd>
				<?php }?>
			</dl>
			<?=($k+1)%5==0?'<div class="blank15"></div>':'';?>
			<?php }?>
		</div>
	</div>
	<div class="blank25"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>