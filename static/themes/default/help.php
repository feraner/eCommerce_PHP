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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta($article_row, $spare_ary);?>
<?php include("{$c['static_path']}/inc/static.php");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div class="blank20"></div>
	<div class="side_left fl">
		<div class="help_menu">
			<div class="help_title"><?=$category_row['Category'.$c['lang']];?></div>
			<ul class="help_list">
				<?php
				$article_row=str::str_code(db::get_all('article', 'CateId=99', "CateId, AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'AId asc'));
				foreach((array)$article_row as $v){
				?>
					<li><a<?=$AId==$v['AId']?' class="current FontColor"':'';?> hidefocus="true" href="<?=ly200::get_url($v, 'article');?>"><?=$v['Title'.$c['lang']];?></a></li>
				<?php }?>
			</ul>
		</div>
	</div>
	<div class="side_right fr right_main">
		<div class="main_title"><?=$Title;?></div>
		<div class="main_content editor_txt"><?=htmlspecialchars_decode($article_content_row['Content'.$c['lang']]);?></div>
	</div>
	<div class="blank25"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>