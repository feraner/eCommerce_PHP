<?php !isset($c) && exit();?>
<?php
$article_ary=array();
$article_category_row=str::str_code(db::get_all('article_category', 'CateId not in(1, 99)', "CateId, Category{$c['lang']}", $c['my_order'].'CateId asc'));
$art_row=str::str_code(db::get_all('article', 'CateId not in(1, 99)', "AId, CateId, Title{$c['lang']}, Url", $c['my_order'].'AId desc'));
foreach((array)$art_row as $v){ $article_ary[$v['CateId']][$v['AId']]=$v; } //所有单页的信息

$current_page='article';
$PageUrl=str_replace('.html', '', $_GET['PageUrl']);
$AId=(int)$_GET['AId'];
if($PageUrl){ //通过自定义地址打开
	$article_row=str::str_code(db::get_one('article', "PageUrl='$PageUrl'"));
	$AId=$article_row['AId'];
}elseif($AId){ //通过AId打开
	$article_row=str::str_code(db::get_one('article', "AId='$AId'"));
}
if(!$article_row){ //丢失单页页面
	@header('HTTP/1.1 404');
	exit;
}
$CateId=(int)$article_row['CateId'];
if($CateId==99){ //自动跳转到帮助中心
	include($c['default_path'].'help.php');
	exit;
}
$CateId && $category_row=str::str_code(db::get_one('article_category', "CateId='$CateId'"));
$article_content_row=str::str_code(db::get_one('article_content', "AId='$AId'"));
$Title=$article_row['Title'.$c['lang']]; //标题
$seo_txt=$Title.','.$category_row['Category'.$c['lang']]; //SEO内容
$spare_ary=array('SeoTitle'=>$seo_txt, 'SeoKeyword'=>$seo_txt, 'SeoDescription'=>$seo_txt); //SEO
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
echo ly200::seo_meta($article_row, $spare_ary);
include("{$c['static_path']}/inc/static.php");
?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div class="blank20"></div>
	<div class="side_left fl">
		<div class="help_menu">
			<?php
			foreach((array)$article_category_row as $v){
			?>
				<div class="help_title"><?=$v['Category'.$c['lang']];?></div>
				<?php if($article_ary[$v['CateId']]){?>
					<ul class="help_list">
						<?php
						foreach((array)$article_ary[$v['CateId']] as $v2){
						?>
							<li><a<?=$AId==$v2['AId']?' class="current FontColor"':'';?> hidefocus="true" href="<?=ly200::get_url($v2, 'article');?>"><?=$v2['Title'.$c['lang']];?></a></li>
						<?php }?>
					</ul>
			<?php
				}
			}?>
		</div>
	</div>
	<div class="side_right fr right_main">
		<div class="main_title"><?=$Title;?></div>
		<div class="main_content editor_txt"><?=str_replace('%nbsp;', ' ', str::str_code($article_content_row['Content'.$c['lang']], 'htmlspecialchars_decode'));?></div>
	</div>
	<div class="blank25"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>