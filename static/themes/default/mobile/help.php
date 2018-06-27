<?php !isset($c) && exit();?>
<?php
$PageUrl=str_replace('.html', '', $_GET['PageUrl']);
$AId=(int)$_GET['AId'];

$where='1 and CateId=99';
if($PageUrl){ //通过自定义地址打开
	$where.=" and PageUrl='$PageUrl'";
}elseif($AId){ //通过AId打开
	$where.=" and AId='$AId'";
}
$article_row=str::str_code(db::get_one('article', $where, '*', $c['my_order'].'AId asc'));
if(!$article_row){ //丢失帮助中心页面
	@header('HTTP/1.1 404');
	exit;
}
$AId=$article_row['AId'];
$Title=$article_row['Title'.$c['lang']]; //标题
$article_content_row=str::str_code(db::get_one('article_content', "AId='$AId'")); //文章内容
$CateId=(int)$article_row['CateId'];
$CateId && $category_row=str::str_code(db::get_one('article_category', "CateId='$CateId'"));
$seo_txt=$Title.','.$category_row['Category'.$c['lang']]; //SEO内容
$spare_ary=array('SeoTitle'=>$seo_txt, 'SeoKeyword'=>$seo_txt, 'SeoDescription'=>$seo_txt); //SEO
?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($article_row, $spare_ary);?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
    <div class="page_title"><?=$Title;?></div>
    <div class="art_content"><?=str_replace('%nbsp;', ' ', htmlspecialchars_decode($article_content_row['Content'.$c['lang']]));?></div>
	<div class="divide_8px"></div>
	<aside class="art_menu">
		<?php
		$article_row=str::str_code(db::get_all('article', 'CateId=99', "CateId, AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'AId asc'));
		foreach($article_row as $k=>$v){
		?>
    		<a href="<?=ly200::get_url($v, 'article');?>"><strong><?=$v['Title'.$c['lang']];?></strong><em><i></i></em></a>
		<?php }?>
    </aside>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
</body>
</html>