<?php !isset($c) && exit();?>
<?php
$date=(int)$_GET['date'];
$Keyword=$_GET['Keyword'];
$CateId=(int)$_GET['CateId'];
$Tags=$_GET['Tags'];

//SEO
if((int)$CateId){
	$category=str::str_code(db::get_value('blog_category', "CateId='$CateId'", 'Category_en'));
	if(!$category){//分类不存在
		@header('HTTP/1.1 404');
		exit;
	}

	$seo_row=array(
		'SeoTitle'.$c['lang']		=>	$category,
		'SeoKeyword'.$c['lang']		=>	$category,
		'SeoDescription'.$c['lang']	=>	$category
	);
}elseif((int)$date){
	$spare_ary=array(
		'SeoTitle'		=>	date('F Y', $date),
		'SeoKeyword'	=>	date('F Y', $date),
		'SeoDescription'=>	date('F Y', $date)
	);
}else{
	$seo_row=str::str_code(db::get_one('meta', "Type='blog'"));
	$spare_ary=array(
		'SeoTitle'		=>	'Blog',
		'SeoKeyword'	=>	'Blog',
		'SeoDescription'=>	'Blog'
	);
}
?>
<!doctype html>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($seo_row, $spare_ary);?>
<?php include("inc/static.php");?>
<script>
var date='<?=$date?>';
var Keyword='<?=$Keyword?>';
var CateId='<?=$CateId?>';
var Tags='<?=$Tags?>';
</script>
</head>
<body>
<?php include('inc/header.php');?>
<div class="spacing"></div>
<div id="banner" class="wrap">
	<?php if(is_file($c['root_path'].$blog_set_row['Banner'])){?><img src="<?=$blog_set_row['Banner'];?>" /><?php }?>
</div>
<div class="spacing"></div>
<div class="main_con wrap">
	<div class="leftbar fl">
    	<div id="blog_list"></div>
        <a class="container blog_list_more" href="javascript:;">Read More</a>
    </div>
    <div class="rightbar fr"><?php include('inc/right_side.php');?></div>
    <div class="clear"></div>
</div>
<?php include('inc/footer.php');?>
</body>
</html>