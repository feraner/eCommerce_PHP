<?php !isset($c) && exit();?>
<?php
$article_ary=array();
$article_category_row=str::str_code(db::get_all('article_category', 'CateId not in(1, 99)', "CateId, Category{$c['lang']}", $c['my_order'].'CateId asc'));
$art_row=str::str_code(db::get_all('article', 'CateId not in(1, 99)', "AId, CateId, Title{$c['lang']}, Url", $c['my_order'].'AId desc'));
foreach((array)$art_row as $v){ $article_ary[$v['CateId']][$v['AId']]=$v; } //所有单页的信息

$PageUrl=str_replace('.html', '', $_GET['PageUrl']);
$AId=(int)$_GET['AId'];
if($PageUrl){ //通过自定义地址打开
	$article_row=str::str_code(db::get_one('article', "PageUrl='$PageUrl'"));
	$AId=$article_row['AId'];
}elseif($AId){ //通过AId打开
	$article_row=str::str_code(db::get_one('article', "AId='$AId'"));
}
if(!$article_row){
	@header('HTTP/1.1 404');
	exit;
}
$CateId=(int)$article_row['CateId'];
if($CateId==99){ //自动跳转到帮助中心
	include($c['mobile']['theme_path'].'help.php');
	exit;
}
$CateId && $category_row=str::str_code(db::get_one('article_category', "CateId='$CateId'"));
$article_content_row=str::str_code(db::get_one('article_content', "AId='$AId'"));
$Title=$article_row['Title'.$c['lang']]; //标题
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
	<?=html::mobile_crumb('<em><i></i></em><a href="javascript:;">'.$Title.'</a>');?>
    <div class="art_content clean">
		<?=str_replace('%nbsp;', ' ', str::str_code($article_content_row['Content'.$c['lang']], 'htmlspecialchars_decode'));?>
	</div>
	<div class="divide_8px"></div>
	<aside class="art_menu">
		<?php foreach($article_ary[$CateId] as $k=>$v){?>
    		<a href="<?=ly200::get_url($v, 'article');?>"><strong><?=$v['Title'.$c['lang']];?></strong><em><i></i></em></a>
		<?php }?>
    </aside>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
</body>
</html>